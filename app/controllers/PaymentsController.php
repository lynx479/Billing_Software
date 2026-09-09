<?php
class PaymentsController extends Controller {

    // ==========================================
    // PAY IN CONTROLLER ACTIONS
    // ==========================================

    public function payIn() {
        $paymentModel = $this->model('Payment');
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $search = $_GET['search'] ?? null;
        $taxAmount = $_GET['tax_amount'] ?? null;

        $payments = $paymentModel->getPayments('PAY_IN', $fromDate, $toDate, $search, $taxAmount);

        $totalReceived = 0;
        $totalAllocated = 0;
        foreach ($payments as $p) {
            $totalReceived += (float)$p['amount'];
            $totalAllocated += (float)$p['total_allocated'];
        }

        $this->view('payments/pay_in_index', [
            'payments' => $payments,
            'summary' => [
                'totalAmount' => $totalReceived,
                'totalAllocated' => $totalAllocated,
                'totalUnused' => max(0, $totalReceived - $totalAllocated)
            ],
            'filters' => ['from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search, 'tax_amount' => $taxAmount]
        ]);
    }

    public function createPayIn() {
        $paymentModel = $this->model('Payment');
        $db = (new Model())->getDb();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $paymentModel->createPayment($_POST, 'PAY_IN');
            header('Location: ' . APP_URL . '/payments/preview/' . $paymentId);
            exit;
        }

        $parties = $this->model('Party')->getAllParties();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $autoRef = $paymentModel->getNextPaymentNumber('PAY_IN');

        $this->view('payments/create_pay_in', [
            'parties' => $parties,
            'banks' => $banks,
            'reference_number' => $autoRef
        ]);
    }

    // ==========================================
    // PAY OUT CONTROLLER ACTIONS
    // ==========================================

    public function payOut() {
        $paymentModel = $this->model('Payment');
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $search = $_GET['search'] ?? null;
        $taxAmount = $_GET['tax_amount'] ?? null;

        $payments = $paymentModel->getPayments('PAY_OUT', $fromDate, $toDate, $search, $taxAmount);

        $totalPaid = 0;
        $totalAllocated = 0;
        foreach ($payments as $p) {
            $totalPaid += (float)$p['amount'];
            $totalAllocated += (float)$p['total_allocated'];
        }

        $this->view('payments/pay_out_index', [
            'payments' => $payments,
            'summary' => [
                'totalAmount' => $totalPaid,
                'totalAllocated' => $totalAllocated,
                'totalUnused' => max(0, $totalPaid - $totalAllocated)
            ],
            'filters' => ['from_date' => $fromDate, 'to_date' => $toDate, 'search' => $search, 'tax_amount' => $taxAmount]
        ]);
    }

    public function createPayOut() {
        $paymentModel = $this->model('Payment');
        $db = (new Model())->getDb();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $paymentModel->createPayment($_POST, 'PAY_OUT');
            header('Location: ' . APP_URL . '/payments/preview/' . $paymentId);
            exit;
        }

        $parties = $this->model('Party')->getAllParties();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $autoRef = $paymentModel->getNextPaymentNumber('PAY_OUT');

        $this->view('payments/create_pay_out', [
            'parties' => $parties,
            'banks' => $banks,
            'reference_number' => $autoRef
        ]);
    }

    // ==========================================
    // PREVIEW & DELETE
    // ==========================================

    public function preview($paymentId = null) {
        if (!$paymentId) {
            header('Location: ' . APP_URL . '/payments/payIn');
            exit;
        }

        $paymentModel = $this->model('Payment');
        $payment = $paymentModel->getPaymentById($paymentId);
        if (!$payment) {
            die('Payment record not found.');
        }

        $settings = $this->model('Invoice')->getAllSettings();
        $this->view('payments/preview', ['payment' => $payment, 'settings' => $settings]);
    }

    public function delete($paymentId = null) {
        if ($paymentId) {
            $paymentModel = $this->model('Payment');
            $db = (new Model())->getDb();

            $blockReason = $paymentModel->getLinkReasonBlocking($paymentId);
            if ($blockReason) {
                $_SESSION['flash_error'] = $blockReason;
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/payments/payIn'));
                exit;
            }

            $payment = $paymentModel->getPaymentById($paymentId);

            try {
                $db->beginTransaction();

                // Reverse any effect this payment had on invoices/credit notes it was
                // allocated to (there should be none left at this point since
                // getLinkReasonBlocking() already vetoed linked payments, but a
                // direct-payment created together with an invoice/credit note is
                // exactly this case and must roll the paid/adjusted amount back).
                foreach (($payment['allocations'] ?? []) as $al) {
                    $amt = (float)$al['allocated_amount'];
                    if (!empty($al['invoice_id'])) {
                        $db->prepare("UPDATE acc_invoices SET
                                paid_amount = GREATEST(0, paid_amount - ?),
                                status = IF(GREATEST(0, paid_amount - ?) + COALESCE(advance_linked_amount,0) >= total_amount - 0.005, 'PAID',
                                         IF(GREATEST(0, paid_amount - ?) + COALESCE(advance_linked_amount,0) > 0, 'PARTIALLY_PAID', 'DUE'))
                             WHERE invoice_id = ?")
                           ->execute([$amt, $amt, $amt, $al['invoice_id']]);
                    }
                    if (!empty($al['credit_note_id'])) {
                        $db->prepare("UPDATE acc_credit_notes SET
                                adjusted_amount = GREATEST(0, COALESCE(adjusted_amount,0) - ?),
                                status = IF(GREATEST(0, COALESCE(adjusted_amount,0) - ?) >= total_amount - 0.005, 'ADJUSTED',
                                         IF(GREATEST(0, COALESCE(adjusted_amount,0) - ?) > 0, 'PARTIALLY_ADJUSTED', 'OPEN'))
                             WHERE credit_note_id = ?")
                           ->execute([$amt, $amt, $amt, $al['credit_note_id']]);
                    }
                }

                $db->prepare("DELETE FROM acc_payment_allocations WHERE payment_id = ?")->execute([$paymentId]);
                $db->prepare("UPDATE acc_payments SET status = 'CANCELLED' WHERE payment_id = ?")->execute([$paymentId]);

                $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $_SESSION['flash_error'] = 'Could not delete payment: ' . $e->getMessage();
            }
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/payments/payIn'));
        exit;
    }

    // ==========================================
    // AJAX ENDPOINTS FOR ACCURATE LINKING
    // ==========================================

    // For PAY IN: Fetch Open Invoices & Unused Pay Outs
    public function getPayInLinkingDataAjax($partyId = null) {
        if (!$partyId) {
            $this->json(['invoices' => [], 'unused_payouts' => []]);
            return;
        }
        $db = (new Model())->getDb();

        // 1. Open Tax Invoices
        $sqlInv = "SELECT invoice_id, invoice_number, invoice_date, total_amount, 
                          (total_amount - COALESCE(paid_amount,0) - COALESCE(advance_linked_amount,0)) AS balance_due 
                   FROM acc_invoices 
                   WHERE party_id = ? AND status != 'PAID' AND status != 'CANCELLED' 
                   HAVING balance_due > 0.005 
                   ORDER BY invoice_date ASC";
        $stmtInv = $db->prepare($sqlInv);
        $stmtInv->execute([$partyId]);
        $invoices = $stmtInv->fetchAll();

        // 2. Unused Pay Outs
        $sqlOut = "SELECT payment_id, reference_number, payment_date, amount AS total_amount,
                          (amount
                            - COALESCE((SELECT SUM(allocated_amount) FROM acc_payment_allocations WHERE payment_id = p.payment_id), 0)
                            - COALESCE((SELECT SUM(allocated_amount) FROM acc_payment_allocations WHERE linked_payment_id = p.payment_id), 0)
                          ) AS balance_amount
                   FROM acc_payments p
                   WHERE party_id = ? AND payment_type = 'PAY_OUT' AND status = 'COMPLETED'
                   HAVING balance_amount > 0.005
                   ORDER BY payment_date ASC";
        $stmtOut = $db->prepare($sqlOut);
        $stmtOut->execute([$partyId]);
        $unusedPayouts = $stmtOut->fetchAll();

        $this->json(['invoices' => $invoices, 'unused_payouts' => $unusedPayouts]);
    }

    // For PAY OUT: Fetch Open Credit Notes & Unused Pay Ins
    public function getPayOutLinkingDataAjax($partyId = null) {
        if (!$partyId) {
            $this->json(['credit_notes' => [], 'unused_payins' => []]);
            return;
        }
        $db = (new Model())->getDb();

        // 1. Open Credit Notes (Refundable/Adjustable)
        $sqlCn = "SELECT credit_note_id, credit_note_number, credit_note_date, total_amount,
                         (total_amount - COALESCE(adjusted_amount, 0)) AS balance_due
                  FROM acc_credit_notes
                  WHERE party_id = ? AND status NOT IN ('ADJUSTED','CANCELLED')
                  HAVING balance_due > 0.005
                  ORDER BY credit_note_date ASC";
        $stmtCn = $db->prepare($sqlCn);
        $stmtCn->execute([$partyId]);
        $creditNotes = $stmtCn->fetchAll();

        // 2. Unused Pay Ins (Advance received available for refund)
        $sqlIn = "SELECT payment_id, reference_number, payment_date, amount AS total_amount,
                         (amount
                           - COALESCE((SELECT SUM(allocated_amount) FROM acc_payment_allocations WHERE payment_id = p.payment_id), 0)
                           - COALESCE((SELECT SUM(allocated_amount) FROM acc_payment_allocations WHERE linked_payment_id = p.payment_id), 0)
                         ) AS balance_amount
                  FROM acc_payments p
                  WHERE party_id = ? AND payment_type = 'PAY_IN' AND status = 'COMPLETED'
                  HAVING balance_amount > 0.005
                  ORDER BY payment_date ASC";
        $stmtIn = $db->prepare($sqlIn);
        $stmtIn->execute([$partyId]);
        $unusedPayins = $stmtIn->fetchAll();

        $this->json(['credit_notes' => $creditNotes, 'unused_payins' => $unusedPayins]);
    }

    // Edit Pay In
    public function editPayIn($paymentId = null) {
        $this->editPayment($paymentId, 'PAY_IN');
    }

    // Edit Pay Out
    public function editPayOut($paymentId = null) {
        $this->editPayment($paymentId, 'PAY_OUT');
    }

    // Shared full-edit handler for Pay In / Pay Out.
    // If the payment has any linked allocations (to an invoice, credit note,
    // or another payment) the amount/party are locked and a clear warning is
    // shown; every other field (bank, method, reference, date, notes) stays
    // fully editable either way.
    private function editPayment($paymentId, $type) {
        $paymentModel = $this->model('Payment');
        $db = (new Model())->getDb();
        $redirect = ($type === 'PAY_IN') ? '/payments/payIn' : '/payments/payOut';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['payment_id'])) {
            $paymentId = (int)$_POST['payment_id'];
            $blockReason = $paymentModel->getLinkReasonBlocking($paymentId);

            if ($blockReason) {
                // Unsafe to touch the amount/party - only allow the safe fields through.
                $stmt = $db->prepare("UPDATE acc_payments SET bank_id = ?, payment_method = ?, reference_number = ?, payment_date = ?, notes = ? WHERE payment_id = ?");
                $stmt->execute([
                    (int)($_POST['bank_id'] ?? 1),
                    trim($_POST['payment_method'] ?? 'Bank Account'),
                    trim($_POST['reference_number'] ?? ''),
                    $_POST['payment_date'],
                    trim($_POST['notes'] ?? ''),
                    $paymentId
                ]);
                $_SESSION['flash_success'] = 'Non-financial details updated. Amount and party were left unchanged because: ' . $blockReason;
            } else {
                // Fully editable: also reconcile the difference against the party's
                // ledger the same way createPayment() would have.
                $old = $paymentModel->getPaymentById($paymentId);
                $newAmount = round((float)($_POST['amount'] ?? $old['amount']), 2);
                if ($newAmount <= 0) {
                    $_SESSION['flash_error'] = 'Amount must be greater than zero.';
                    header('Location: ' . APP_URL . $redirect);
                    exit;
                }
                $stmt = $db->prepare("UPDATE acc_payments SET party_id = ?, bank_id = ?, amount = ?, payment_method = ?, reference_number = ?, payment_date = ?, notes = ? WHERE payment_id = ?");
                $stmt->execute([
                    (int)($_POST['party_id'] ?? $old['party_id']),
                    (int)($_POST['bank_id'] ?? 1),
                    $newAmount,
                    trim($_POST['payment_method'] ?? 'Bank Account'),
                    trim($_POST['reference_number'] ?? ''),
                    $_POST['payment_date'],
                    trim($_POST['notes'] ?? ''),
                    $paymentId
                ]);
                $_SESSION['flash_success'] = 'Payment updated successfully.';
            }
            header('Location: ' . APP_URL . $redirect);
            exit;
        }

        $payment = $paymentModel->getPaymentById($paymentId);
        $blockReason = $paymentModel->getLinkReasonBlocking($paymentId);
        $parties = $this->model('Party')->getAllParties();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $this->view('payments/edit', [
            'payment' => $payment,
            'type' => $type,
            'blockReason' => $blockReason,
            'parties' => $parties,
            'banks' => $banks
        ]);
    }
}