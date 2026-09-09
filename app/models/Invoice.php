<?php
class Invoice extends Model {

    // Fetch custom setting with fallback
    public function getSetting($key, $default = '') {
        $stmt = $this->getDb()->prepare("SELECT setting_value FROM acc_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    public function getAllSettings() {
        $rows = $this->getDb()->query("SELECT setting_key, setting_value FROM acc_settings")->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    // Generate next sequential document number
    public function getNextDocumentNumber($type = 'INVOICE') {
        $prefixKey = ($type === 'INVOICE') ? 'invoice_prefix' : 'credit_note_prefix';
        $numberKey = ($type === 'INVOICE') ? 'invoice_next_number' : 'credit_note_next_number';

        $prefix = $this->getSetting($prefixKey, ($type === 'INVOICE' ? 'INV-' : 'CN-'));
        $nextNum = (int)$this->getSetting($numberKey, '1001');

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    // Increment document sequence number after save
    public function incrementDocumentNumber($type = 'INVOICE') {
        $numberKey = ($type === 'INVOICE') ? 'invoice_next_number' : 'credit_note_next_number';
        $nextNum = (int)$this->getSetting($numberKey, '1001') + 1;
        $stmt = $this->getDb()->prepare("UPDATE acc_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$nextNum, $numberKey]);
    }

    // Fetch filtered invoices for listing & summary
    public function getInvoices($fromDate = null, $toDate = null, $search = null) {
        $sql = "SELECT i.*, p.name AS party_name, p.business_name, p.phone, p.state AS party_state,
                       (i.total_amount - i.paid_amount - i.advance_linked_amount) AS balance_due,
                       (SELECT GROUP_CONCAT(DISTINCT ba.bank_name SEPARATOR ', ') 
                        FROM acc_payment_allocations pa 
                        JOIN acc_payments pay ON pa.payment_id = pay.payment_id 
                        LEFT JOIN acc_bank_accounts ba ON pay.bank_id = ba.bank_id 
                        WHERE pa.invoice_id = i.invoice_id) AS payment_methods
                FROM acc_invoices i
                LEFT JOIN acc_parties p ON i.party_id = p.party_id
                WHERE i.status != 'CANCELLED'";
        
        $params = [];
        if (!empty($fromDate)) {
            $sql .= " AND i.invoice_date >= ?";
            $params[] = $fromDate;
        }
        if (!empty($toDate)) {
            $sql .= " AND i.invoice_date <= ?";
            $params[] = $toDate;
        }
        if (!empty($search)) {
            $sql .= " AND (i.invoice_number LIKE ? OR p.name LIKE ? OR p.business_name LIKE ? OR p.phone LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
        }

        $sql .= " ORDER BY i.invoice_date DESC, i.invoice_id DESC";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Fetch complete invoice with line items, party data & payment history
    public function getInvoiceById($invoiceId) {
        $stmt = $this->getDb()->prepare("SELECT i.*, p.name AS party_name, p.business_name, p.email AS party_email, 
                                                p.phone AS party_phone, p.gstin AS party_gstin, p.pan AS party_pan,
                                                p.address AS party_address, p.shipping_address, p.state AS party_state, p.city, p.pincode
                                         FROM acc_invoices i
                                         LEFT JOIN acc_parties p ON i.party_id = p.party_id
                                         WHERE i.invoice_id = ?");
        $stmt->execute([$invoiceId]);
        $invoice = $stmt->fetch();

        if ($invoice) {
            $itemStmt = $this->getDb()->prepare("SELECT * FROM acc_invoice_items WHERE invoice_id = ?");
            $itemStmt->execute([$invoiceId]);
            $invoice['items'] = $itemStmt->fetchAll();

            $payStmt = $this->getDb()->prepare("SELECT pay.*, pa.allocated_amount, ba.bank_name, ba.account_number 
                                                FROM acc_payment_allocations pa
                                                JOIN acc_payments pay ON pa.payment_id = pay.payment_id
                                                LEFT JOIN acc_bank_accounts ba ON pay.bank_id = ba.bank_id
                                                WHERE pa.invoice_id = ?");
            $payStmt->execute([$invoiceId]);
            $invoice['payments'] = $payStmt->fetchAll();
        }

        return $invoice;
    }

    // Save Tax Invoice with full transactional integrity
    public function createInvoice($data) {
        $db = $this->getDb();
        // Validate quantities against each item's unit BEFORE opening a transaction
        // (piece/unit-based items must be whole numbers; weight/KG-based units may be decimal).
        if (!empty($data['items'])) {
            validate_items_quantities($data['items']);
        }

        $db->beginTransaction();

        try {
            $companyState = $this->getSetting('company_state', 'Kerala');
            $placeOfSupply = $data['place_of_supply'] ?? $companyState;
            $isInterstate = (strcasecmp(trim($companyState), trim($placeOfSupply)) !== 0) ? 1 : 0;

            $totalAmount = (float)($data['total_amount'] ?? 0.00);
            $roundOff = (float)($data['round_off'] ?? 0.00);

            // 1. Calculate Direct Payments Total (from Cash/Bank rows)
            $directPaid = 0.00;
            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $p) {
                    $directPaid += (float)($p['amount'] ?? 0);
                }
            }

            // 2. Calculate Linked Advance Pay-Ins Total
            $advanceLinked = 0.00;
            if (!empty($data['linked_pay_in'])) {
                foreach ($data['linked_pay_in'] as $pid => $amt) {
                    $advanceLinked += (float)$amt;
                }
            }

            $netPaid = $directPaid + $advanceLinked;

            $status = 'DUE';
            if ($netPaid >= $totalAmount && $totalAmount > 0) {
                $status = 'PAID';
            } elseif ($netPaid > 0) {
                $status = 'PARTIALLY_PAID';
            }

            // 3. Insert Invoice Master Record
            $stmt = $db->prepare("INSERT INTO acc_invoices 
                (invoice_number, party_id, invoice_date, due_date, place_of_supply, is_interstate,
                 taxable_amount, discount_amount, tax_amount, cgst_amount, sgst_amount, igst_amount, 
                 round_off, total_amount, paid_amount, advance_linked_amount, status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $data['invoice_number'],
                $data['party_id'],
                $data['invoice_date'],
                $data['due_date'] ?? $data['invoice_date'],
                $placeOfSupply,
                $isInterstate,
                $data['taxable_amount'] ?? 0.00,
                $data['discount_amount'] ?? 0.00,
                $data['tax_amount'] ?? 0.00,
                $data['cgst_amount'] ?? 0.00,
                $data['sgst_amount'] ?? 0.00,
                $data['igst_amount'] ?? 0.00,
                $roundOff,
                $totalAmount,
                $directPaid,
                $advanceLinked,
                $status,
                $data['notes'] ?? null
            ]);

            $invoiceId = $db->lastInsertId();

            // 4. Insert Line Items & Deduct Physical Stock (for Products only)
            if (!empty($data['items'])) {
                $itemStmt = $db->prepare("INSERT INTO acc_invoice_items 
                    (invoice_id, item_name, description, quantity, unit, unit_price, discount_percent, discount_amount, 
                     taxable_amount, tax_rate, tax_amount, cgst_rate, cgst_amount, sgst_rate, sgst_amount, igst_rate, igst_amount, total_amount) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stockStmt = $db->prepare("UPDATE acc_items 
                                           SET current_stock = current_stock - ? 
                                           WHERE name = ? AND item_type = 'PRODUCT'");

                foreach ($data['items'] as $item) {
                    if (empty($item['item_name'])) continue;

                    $qty = (float)($item['quantity'] ?? 1);

                    $itemStmt->execute([
                        $invoiceId,
                        $item['item_name'],
                        $item['description'] ?? null,
                        $qty,
                        $item['unit'] ?? 'PCS',
                        (float)$item['unit_price'],
                        (float)($item['discount_percent'] ?? 0.00),
                        (float)($item['discount_amount'] ?? 0.00),
                        (float)($item['taxable_amount'] ?? 0.00),
                        (float)$item['tax_rate'],
                        (float)$item['tax_amount'],
                        (float)($item['cgst_rate'] ?? 0.00),
                        (float)($item['cgst_amount'] ?? 0.00),
                        (float)($item['sgst_rate'] ?? 0.00),
                        (float)($item['sgst_amount'] ?? 0.00),
                        (float)($item['igst_rate'] ?? 0.00),
                        (float)($item['igst_amount'] ?? 0.00),
                        (float)$item['total_amount']
                    ]);

                    // Automatically update inventory stock balance
                    $stockStmt->execute([$qty, $item['item_name']]);
                }
            }

            // 5. Insert Direct Payment Allocations (Tagged as DIRECT-PAY to avoid confusing with advance receipts)
            if (!empty($data['payments'])) {
                $payStmt = $db->prepare("INSERT INTO acc_payments 
                    (payment_type, party_id, bank_id, amount, payment_method, reference_number, payment_date, notes, status) 
                    VALUES ('PAY_IN', ?, ?, ?, ?, ?, ?, ?, 'COMPLETED')");
                
                $allocStmt = $db->prepare("INSERT INTO acc_payment_allocations 
                    (payment_id, invoice_id, credit_note_id, linked_payment_id, allocated_amount) 
                    VALUES (?, ?, NULL, NULL, ?)");

                foreach ($data['payments'] as $p) {
                    $pAmount = (float)($p['amount'] ?? 0);
                    if ($pAmount > 0) {
                        $payStmt->execute([
                            $data['party_id'],
                            !empty($p['bank_id']) ? $p['bank_id'] : 1,
                            $pAmount,
                            $p['payment_method'] ?? 'Bank Account',
                            'DIRECT-PAY-' . $data['invoice_number'],
                            $data['invoice_date'],
                            'Direct payment on Invoice ' . $data['invoice_number']
                        ]);
                        $paymentId = $db->lastInsertId();
                        $allocStmt->execute([$paymentId, $invoiceId, $pAmount]);
                    }
                }
            }

            // 6. Record Linked Advance Pay-In Allocations (Existing Pay-Ins)
            if (!empty($data['linked_pay_in'])) {
                $allocStmt = $db->prepare("INSERT INTO acc_payment_allocations 
                    (payment_id, invoice_id, credit_note_id, linked_payment_id, allocated_amount) 
                    VALUES (?, ?, NULL, NULL, ?)");

                foreach ($data['linked_pay_in'] as $payId => $allocAmt) {
                    $allocAmt = (float)$allocAmt;
                    if ($allocAmt > 0) {
                        $allocStmt->execute([(int)$payId, $invoiceId, $allocAmt]);
                    }
                }
            }

            // 7. Increment Invoice sequence counter in settings
            $this->incrementDocumentNumber('INVOICE');

            $db->commit();
            return $invoiceId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // Get unallocated advance credit for a party
    public function getPartyUnallocatedBalance($partyId) {
        $db = $this->getDb();
        // Total Pay-In received minus total allocated to invoices
        $sql = "SELECT (
                    COALESCE((SELECT SUM(amount) FROM acc_payments WHERE party_id = ? AND payment_type = 'PAY_IN'), 0)
                    -
                    COALESCE((SELECT SUM(allocated_amount) FROM acc_payment_allocations pa JOIN acc_payments p ON pa.payment_id = p.payment_id WHERE p.party_id = ?), 0)
                ) AS unallocated_amount";
        $stmt = $db->prepare($sql);
        $stmt->execute([$partyId, $partyId]);
        $res = $stmt->fetch();
        return max(0, (float)($res['unallocated_amount'] ?? 0));
    }
}