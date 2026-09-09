<?php
class Payment extends Model {

    public function getSetting($key, $default = '') {
        $stmt = $this->getDb()->prepare("SELECT setting_value FROM acc_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    public function getNextPaymentNumber($type = 'PAY_IN') {
        // Fetches directly from your customization settings table keys
        $prefixKey = ($type === 'PAY_IN') ? 'payin_prefix' : 'payout_prefix';
        $numberKey = ($type === 'PAY_IN') ? 'payin_next_number' : 'payout_next_number';

        $prefix = $this->getSetting($prefixKey, ($type === 'PAY_IN' ? 'REC-' : 'PAY-'));
        $nextNum = (int)$this->getSetting($numberKey, '1001');

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function incrementPaymentNumber($type = 'PAY_IN') {
        $numberKey = ($type === 'PAY_IN') ? 'payin_next_number' : 'payout_next_number';
        $nextNum = (int)$this->getSetting($numberKey, '1001') + 1;
        $stmt = $this->getDb()->prepare("UPDATE acc_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$nextNum, $numberKey]);
    }

    public function getPayments($type = 'PAY_IN', $fromDate = null, $toDate = null, $search = null, $taxAmount = null) {
        $sql = "SELECT p.*, pt.name AS party_name, pt.business_name, pt.phone,
                       ba.bank_name, ba.account_number,
                       COALESCE((SELECT SUM(pa.allocated_amount) FROM acc_payment_allocations pa WHERE pa.payment_id = p.payment_id), 0) AS total_allocated,
                       EXISTS(SELECT 1 FROM acc_payment_allocations pa2 WHERE pa2.payment_id = p.payment_id
                              OR pa2.linked_payment_id = p.payment_id) AS has_links
                FROM acc_payments p
                LEFT JOIN acc_parties pt ON p.party_id = pt.party_id
                LEFT JOIN acc_bank_accounts ba ON p.bank_id = ba.bank_id
                WHERE p.payment_type = ? AND p.status != 'CANCELLED'";
        
        $params = [$type];
        if (!empty($fromDate)) { $sql .= " AND p.payment_date >= ?"; $params[] = $fromDate; }
        if (!empty($toDate)) { $sql .= " AND p.payment_date <= ?"; $params[] = $toDate; }
        if (!empty($search)) {
            $sql .= " AND (p.reference_number LIKE ? OR pt.name LIKE ? OR pt.business_name LIKE ? OR pt.phone LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
        }
        if ($taxAmount !== null && $taxAmount !== '') {
            // Payments don't carry their own tax; this searches the tax amount of
            // whatever invoice/credit note the payment is allocated against.
            $sql .= " AND EXISTS (
                        SELECT 1 FROM acc_payment_allocations pa3
                        LEFT JOIN acc_invoices ai ON pa3.invoice_id = ai.invoice_id
                        LEFT JOIN acc_credit_notes ac ON pa3.credit_note_id = ac.credit_note_id
                        WHERE pa3.payment_id = p.payment_id
                          AND (ai.tax_amount = ? OR ac.tax_amount = ?)
                      )";
            $params[] = $taxAmount; $params[] = $taxAmount;
        }

        $sql .= " ORDER BY p.payment_date DESC, p.payment_id DESC";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPaymentById($paymentId) {
        $stmt = $this->getDb()->prepare("SELECT p.*, pt.name AS party_name, pt.business_name, pt.phone, pt.email, pt.address, pt.gstin,
                                                ba.bank_name, ba.account_number, ba.ifsc_code
                                         FROM acc_payments p
                                         LEFT JOIN acc_parties pt ON p.party_id = pt.party_id
                                         LEFT JOIN acc_bank_accounts ba ON p.bank_id = ba.bank_id
                                         WHERE p.payment_id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();

        if ($payment) {
            $allocStmt = $this->getDb()->prepare("
                SELECT pa.*, inv.invoice_number, cn.credit_note_number, lp.reference_number AS linked_ref_no
                FROM acc_payment_allocations pa
                LEFT JOIN acc_invoices inv ON pa.invoice_id = inv.invoice_id
                LEFT JOIN acc_credit_notes cn ON pa.credit_note_id = cn.credit_note_id
                LEFT JOIN acc_payments lp ON pa.linked_payment_id = lp.payment_id
                WHERE pa.payment_id = ?
            ");
            $allocStmt->execute([$paymentId]);
            $payment['allocations'] = $allocStmt->fetchAll();

            // Also detect if ANOTHER payment links back to this one
            // (linked_payment_id points at us), so edit/delete can warn correctly.
            $backLinkStmt = $this->getDb()->prepare("
                SELECT pa.*, lp.reference_number AS linked_ref_no
                FROM acc_payment_allocations pa
                JOIN acc_payments lp ON pa.payment_id = lp.payment_id
                WHERE pa.linked_payment_id = ?
            ");
            $backLinkStmt->execute([$paymentId]);
            $payment['linked_from'] = $backLinkStmt->fetchAll();
        }

        return $payment;
    }

    /**
     * Whether a payment is "unsafe" to edit/delete because another
     * transaction (invoice, credit note, or another payment) is linked to
     * it. Returns a human-readable reason string, or null if it is safe.
     */
    public function getLinkReasonBlocking($paymentId) {
        $payment = $this->getPaymentById($paymentId);
        if (!$payment) return null;

        $names = [];
        foreach (($payment['allocations'] ?? []) as $al) {
            if (!empty($al['invoice_number'])) $names[] = 'Invoice ' . $al['invoice_number'];
            if (!empty($al['credit_note_number'])) $names[] = 'Credit Note ' . $al['credit_note_number'];
            if (!empty($al['linked_ref_no'])) $names[] = 'Payment ' . $al['linked_ref_no'];
        }
        foreach (($payment['linked_from'] ?? []) as $al) {
            if (!empty($al['linked_ref_no'])) $names[] = 'Payment ' . $al['linked_ref_no'];
        }
        $names = array_values(array_unique($names));
        if (empty($names)) return null;

        return 'This payment is linked to ' . implode(', ', $names) . '. Unlink or delete those first, or only edit non-financial fields (bank, method, reference, date, notes).';
    }

    // Save Pay In or Pay Out with full splits & sanitized NULL foreign keys
    public function createPayment($data, $type = 'PAY_IN') {
        $db = $this->getDb();
        $db->beginTransaction();

        try {
            $partyId = $data['party_id'];
            $paymentDate = $data['payment_date'];
            $notes = $data['notes'] ?? '';
            $refNumber = $data['reference_number'];

            $firstBankId = !empty($data['payments'][1]['bank_id']) ? $data['payments'][1]['bank_id'] : 1;
            $firstMethod = !empty($data['payments'][1]['payment_method']) ? $data['payments'][1]['payment_method'] : 'Bank Transfer';
            $totalAmount = (float)$data['total_amount'];

            if (empty($partyId) || $totalAmount <= 0) {
                throw new Exception('A valid party and a payment amount greater than zero are required.');
            }

            // Prevent duplicate submissions (page refresh / back button re-post)
            $dupStmt = $db->prepare("SELECT payment_id FROM acc_payments WHERE reference_number = ? AND payment_type = ? LIMIT 1");
            $dupStmt->execute([$refNumber, $type]);
            if ($existingId = $dupStmt->fetchColumn()) {
                $db->rollBack();
                return $existingId;
            }

            // 1. Insert Master Record
            $stmt = $db->prepare("INSERT INTO acc_payments (payment_type, reference_number, party_id, bank_id, amount, payment_method, payment_date, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'COMPLETED')");
            $stmt->execute([$type, $refNumber, $partyId, $firstBankId, $totalAmount, $firstMethod, $paymentDate, $notes]);
            $paymentId = $db->lastInsertId();

            // 2. Insert Invoices Allocations (For Pay In)
            if (!empty($data['allocated_invoices'])) {
                $invAllocStmt = $db->prepare("INSERT INTO acc_payment_allocations (payment_id, invoice_id, credit_note_id, linked_payment_id, allocated_amount) VALUES (?, ?, NULL, NULL, ?)");
                // NOTE: status is assigned BEFORE paid_amount so the comparison uses the pre-update value
                // (MySQL evaluates SET expressions left to right) and includes any advance already linked.
                $invUpdateStmt = $db->prepare("UPDATE acc_invoices SET status = IF(paid_amount + COALESCE(advance_linked_amount,0) + ? >= total_amount - 0.005, 'PAID', 'PARTIALLY_PAID'), paid_amount = paid_amount + ? WHERE invoice_id = ?");

                foreach ($data['allocated_invoices'] as $invId => $allocAmt) {
                    $allocAmt = (float)$allocAmt;
                    $cleanInvId = (int)$invId;
                    if ($allocAmt > 0 && $cleanInvId > 0) {
                        $invAllocStmt->execute([$paymentId, $cleanInvId, $allocAmt]);
                        $invUpdateStmt->execute([$allocAmt, $allocAmt, $cleanInvId]);
                    }
                }
            }

            // 3. Insert Credit Notes Allocations (For Pay Out)
            if (!empty($data['allocated_credit_notes'])) {
                $cnAllocStmt = $db->prepare("INSERT INTO acc_payment_allocations (payment_id, invoice_id, credit_note_id, linked_payment_id, allocated_amount) VALUES (?, NULL, ?, NULL, ?)");
                $cnUpdateStmt = $db->prepare("UPDATE acc_credit_notes SET status = IF(COALESCE(adjusted_amount,0) + ? >= total_amount - 0.005, 'ADJUSTED', 'PARTIALLY_ADJUSTED'), adjusted_amount = COALESCE(adjusted_amount,0) + ? WHERE credit_note_id = ?");

                foreach ($data['allocated_credit_notes'] as $cnId => $allocAmt) {
                    $allocAmt = (float)$allocAmt;
                    $cleanCnId = (int)$cnId;
                    if ($allocAmt > 0 && $cleanCnId > 0) {
                        $cnAllocStmt->execute([$paymentId, $cleanCnId, $allocAmt]);
                        $cnUpdateStmt->execute([$allocAmt, $allocAmt, $cleanCnId]);
                    }
                }
            }

            // 4. Insert Unused Payment Linking (Pay In linked to Pay Out OR Pay Out linked to Pay In)
            if (!empty($data['linked_payment_alloc'])) {
                $payLinkStmt = $db->prepare("INSERT INTO acc_payment_allocations (payment_id, invoice_id, credit_note_id, linked_payment_id, allocated_amount) VALUES (?, NULL, NULL, ?, ?)");
                foreach ($data['linked_payment_alloc'] as $linkedId => $allocAmt) {
                    $allocAmt = (float)$allocAmt;
                    $cleanLinkedId = (int)$linkedId;
                    if ($allocAmt > 0 && $cleanLinkedId > 0) {
                        $payLinkStmt->execute([$paymentId, $cleanLinkedId, $allocAmt]);
                    }
                }
            }

            $this->incrementPaymentNumber($type);
            $db->commit();
            return $paymentId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}