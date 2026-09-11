<?php
class BankAccountsController extends Controller {
    public function index() {
    $db = (new Model())->getDb();

    $sql = "
        SELECT 
            b.*,
            b.current_balance AS opening_balance,
            b.current_balance
              + COALESCE(SUM(CASE WHEN p.payment_type = 'PAY_IN'  THEN p.amount ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN p.payment_type = 'PAY_OUT' THEN p.amount ELSE 0 END), 0)
            AS live_balance
        FROM acc_bank_accounts b
        LEFT JOIN acc_payments p 
               ON p.bank_id = b.bank_id 
              AND p.status != 'CANCELLED'
        WHERE b.status = 1
        GROUP BY b.bank_id
        ORDER BY b.bank_id DESC
    ";

    $accounts = $db->query($sql)->fetchAll();
    $this->view('bank-accounts/index', ['accounts' => $accounts]);
}

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("INSERT INTO acc_bank_accounts 
                (account_name, bank_name, account_number, ifsc_code, account_type, current_balance, status) 
                VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $_POST['account_name'],
                $_POST['bank_name'],
                $_POST['account_number'],
                $_POST['ifsc_code'],
                $_POST['account_type'] ?? 'CURRENT',
                $_POST['current_balance'] ?? 0.00
            ]);
        }
        header('Location: ' . APP_URL . '/bankAccounts');
        exit;
    }

    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bank_id'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_bank_accounts SET 
                account_name = ?, bank_name = ?, account_number = ?, ifsc_code = ?, account_type = ?, current_balance = ? 
                WHERE bank_id = ?");
            $stmt->execute([
                $_POST['account_name'],
                $_POST['bank_name'],
                $_POST['account_number'],
                $_POST['ifsc_code'],
                $_POST['account_type'],
                $_POST['current_balance'],
                $_POST['bank_id']
            ]);
        }
        header('Location: ' . APP_URL . '/bankAccounts');
        exit;
    }

        public function delete($id = null) {
    if (!$id) {
        header('Location: ' . APP_URL . '/bankAccounts');
        exit;
    }

    $db = (new Model())->getDb();

    // Refuse to deactivate if the account has any non-cancelled transactions
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM acc_payments 
        WHERE bank_id = ? AND status != 'CANCELLED'
    ");
    $stmt->execute([$id]);
    $txCount = (int)$stmt->fetchColumn();

    if ($txCount > 0) {
        $_SESSION['flash_error'] = "Cannot delete this bank account — it has {$txCount} transaction(s) linked to it. Delete or reassign those payments first.";
        header('Location: ' . APP_URL . '/bankAccounts');
        exit;
    }

    // Safe to deactivate
    $stmt = $db->prepare("UPDATE acc_bank_accounts SET status = 0 WHERE bank_id = ?");
    $stmt->execute([$id]);

    $_SESSION['flash_success'] = 'Bank account deleted successfully.';
    header('Location: ' . APP_URL . '/bankAccounts');
    exit;
}

    /**
     * Dedicated account page: shows every Pay-In / Pay-Out transaction that
     * belongs to this specific bank account, with running totals.
     */
    public function show($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/bankAccounts'); exit; }
        $db = (new Model())->getDb();

        $stmt = $db->prepare("SELECT * FROM acc_bank_accounts WHERE bank_id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch();
        if (!$account) { header('Location: ' . APP_URL . '/bankAccounts'); exit; }

        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;

        $sql = "SELECT p.*, pt.name AS party_name, pt.business_name
                FROM acc_payments p
                LEFT JOIN acc_parties pt ON p.party_id = pt.party_id
                WHERE p.bank_id = ? AND p.status != 'CANCELLED'";
        $params = [$id];
        if (!empty($fromDate)) { $sql .= " AND p.payment_date >= ?"; $params[] = $fromDate; }
        if (!empty($toDate)) { $sql .= " AND p.payment_date <= ?"; $params[] = $toDate; }
        $sql .= " ORDER BY p.payment_date ASC, p.payment_id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        // Running balance, oldest -> newest, then reverse for newest-first display
        $running = (float)$account['current_balance'];
        $totalIn = 0.00;
        $totalOut = 0.00;
        foreach ($transactions as &$t) {
            if ($t['payment_type'] === 'PAY_IN') {
                $running += (float)$t['amount'];
                $totalIn += (float)$t['amount'];
            } else {
                $running -= (float)$t['amount'];
                $totalOut += (float)$t['amount'];
            }
            $t['running_balance'] = $running;
        }
        unset($t);
        $transactions = array_reverse($transactions);

        $this->view('bank-accounts/show', [
            'account' => $account,
            'transactions' => $transactions,
            'summary' => ['totalIn' => $totalIn, 'totalOut' => $totalOut, 'netMovement' => $totalIn - $totalOut],
            'filters' => ['from_date' => $fromDate, 'to_date' => $toDate]
        ]);
    }

    /**
     * CSV export of one account's transaction history - reuses the same
     * fputcsv-to-php://output pattern as CreditNotesController::exportCsv().
     */
    public function exportTransactions($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/bankAccounts'); exit; }
        $db = (new Model())->getDb();

        $stmt = $db->prepare("SELECT account_name FROM acc_bank_accounts WHERE bank_id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch();

        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $sql = "SELECT p.*, pt.name AS party_name
                FROM acc_payments p
                LEFT JOIN acc_parties pt ON p.party_id = pt.party_id
                WHERE p.bank_id = ? AND p.status != 'CANCELLED'";
        $params = [$id];
        if (!empty($fromDate)) { $sql .= " AND p.payment_date >= ?"; $params[] = $fromDate; }
        if (!empty($toDate)) { $sql .= " AND p.payment_date <= ?"; $params[] = $toDate; }
        $sql .= " ORDER BY p.payment_date ASC, p.payment_id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=account_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $account['account_name'] ?? $id) . '_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Type', 'Reference', 'Party', 'Method', 'Amount']);
        foreach ($transactions as $t) {
            fputcsv($output, [
                $t['payment_date'],
                $t['payment_type'] === 'PAY_IN' ? 'Received' : 'Paid',
                $t['reference_number'],
                $t['party_name'],
                $t['payment_method'],
                $t['amount']
            ]);
        }
        fclose($output);
        exit;
    }
}