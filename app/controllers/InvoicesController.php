<?php
class InvoicesController extends Controller {

    public function index() {
        $db = (new Model())->getDb();

        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $search = $_GET['search'] ?? null;
        $taxAmount = $_GET['tax_amount'] ?? null;

        $sql = "SELECT inv.*, 
                       p.name AS party_name, 
                       p.business_name,
                       COALESCE(inv.paid_amount, 0) AS direct_paid,
                       COALESCE(inv.advance_linked_amount, 0) AS advance_paid,
                       (COALESCE(inv.paid_amount, 0) + COALESCE(inv.advance_linked_amount, 0)) AS total_received,
                       GREATEST(0, ROUND(inv.total_amount - (COALESCE(inv.paid_amount, 0) + COALESCE(inv.advance_linked_amount, 0)), 2)) AS balance_amount
                FROM acc_invoices inv
                LEFT JOIN acc_parties p ON inv.party_id = p.party_id
                WHERE inv.status != 'CANCELLED'";

        $params = [];
        if (!empty($fromDate)) {
            $sql .= " AND inv.invoice_date >= ?";
            $params[] = $fromDate;
        }
        if (!empty($toDate)) {
            $sql .= " AND inv.invoice_date <= ?";
            $params[] = $toDate;
        }
        if (!empty($search)) {
            $sql .= " AND (inv.invoice_number LIKE ? OR p.name LIKE ? OR p.business_name LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term; $params[] = $term; $params[] = $term;
        }
        if ($taxAmount !== null && $taxAmount !== '') {
            $sql .= " AND inv.tax_amount = ?";
            $params[] = $taxAmount;
        }

        $sql .= " ORDER BY inv.invoice_date DESC, inv.invoice_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        $totalInvoiceAmount = 0;
        $totalReceivedAmount = 0;
        $totalBalanceAmount = 0;

        foreach ($invoices as $inv) {
            $totalInvoiceAmount += (float)$inv['total_amount'];
            $totalReceivedAmount += (float)$inv['total_received'];
            $totalBalanceAmount += (float)$inv['balance_amount'];
        }

        $this->view('invoices/index', [
            'invoices' => $invoices,
            'summary' => [
                'totalAmount' => $totalInvoiceAmount,
                'totalReceived' => $totalReceivedAmount,
                'totalBalance' => $totalBalanceAmount
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'search' => $search,
                'tax_amount' => $taxAmount
            ]
        ]);
    }

    public function create() {
        $invoiceModel = $this->model('Invoice');
        $db = (new Model())->getDb();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $invoiceId = $invoiceModel->createInvoice($_POST);
                header('Location: ' . APP_URL . '/invoices/preview/' . $invoiceId);
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
                $parties = $this->model('Party')->getAllParties();
                $itemsErr = $db->query("SELECT * FROM acc_items WHERE status = 1 ORDER BY name ASC")->fetchAll();
                $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
                $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
                $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
                $settings = $invoiceModel->getAllSettings();
                $this->view('invoices/create', [
                    'parties' => $parties, 'items' => $itemsErr, 'units' => $units, 'taxes' => $taxes,
                    'banks' => $banks, 'settings' => $settings,
                    'invoice_number' => $_POST['invoice_number'] ?? $invoiceModel->getNextDocumentNumber('INVOICE'),
                    'error' => $error
                ]);
                exit;
            }
        }

        $parties = $this->model('Party')->getAllParties();
        // Fixed: Use 'name' instead of 'item_name'
        $items = $db->query("SELECT * FROM acc_items WHERE status = 1 ORDER BY name ASC")->fetchAll();
        $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
        $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $settings = $invoiceModel->getAllSettings();
        $generatedInvoiceNo = $invoiceModel->getNextDocumentNumber('INVOICE');

        $this->view('invoices/create', [
            'parties' => $parties,
            'items' => $items,
            'units' => $units,
            'taxes' => $taxes,
            'banks' => $banks,
            'settings' => $settings,
            'invoice_number' => $generatedInvoiceNo
        ]);
    }

    public function edit($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/invoices'); exit; }
        $db = (new Model())->getDb();
        $invoiceModel = $this->model('Invoice');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyState = $invoiceModel->getSetting('company_state', 'Kerala');
            $placeOfSupply = trim($_POST['place_of_supply'] ?? $companyState);
            $isInterstate = (strcasecmp(trim($companyState), $placeOfSupply) !== 0) ? 1 : 0;

            // Only header-level fields are editable here (party, dates, place of
            // supply, notes). Line items and amounts are intentionally left
            // untouched to avoid silently corrupting a document that may already
            // have payments/allocations recorded against its current totals.
            $stmt = $db->prepare("UPDATE acc_invoices SET party_id = ?, invoice_date = ?, due_date = ?, place_of_supply = ?, is_interstate = ?, notes = ? WHERE invoice_id = ?");
            $stmt->execute([
                (int)$_POST['party_id'],
                $_POST['invoice_date'],
                $_POST['due_date'] ?: $_POST['invoice_date'],
                $placeOfSupply,
                $isInterstate,
                trim($_POST['notes'] ?? ''),
                $id
            ]);
            $_SESSION['flash_success'] = 'Invoice details updated. Line items and amounts were left unchanged — create a Credit Note to adjust the value of an already-issued invoice.';
            header('Location: ' . APP_URL . '/invoices/preview/' . $id);
            exit;
        }

        $invoice = $invoiceModel->getInvoiceById($id);
        if (!$invoice) { header('Location: ' . APP_URL . '/invoices'); exit; }
        $parties = $this->model('Party')->getAllParties();

        $linkCheck = $db->prepare("SELECT COUNT(*) FROM acc_payment_allocations WHERE invoice_id = ?");
        $linkCheck->execute([$id]);
        $hasLinks = (int)$linkCheck->fetchColumn() > 0;

        $this->view('invoices/edit', ['invoice' => $invoice, 'parties' => $parties, 'hasLinks' => $hasLinks]);
    }

    public function delete($id = null) {
        if ($id) {
            $db = (new Model())->getDb();
            $linkCheck = $db->prepare("
                SELECT pay.reference_number, pay.payment_type
                FROM acc_payment_allocations pa
                JOIN acc_payments pay ON pa.payment_id = pay.payment_id
                WHERE pa.invoice_id = ?
            ");
            $linkCheck->execute([$id]);
            $links = $linkCheck->fetchAll();

            if (!empty($links)) {
                $refs = array_map(function ($l) { return $l['reference_number']; }, $links);
                $_SESSION['flash_error'] = 'This invoice cannot be deleted because it has payment(s) linked to it: '
                    . implode(', ', array_unique($refs)) . '. Delete or unlink those payments first.';
            } else {
                try {
                    $db->beginTransaction();
                    $db->prepare("DELETE FROM acc_invoice_items WHERE invoice_id = ?")->execute([$id]);
                    $db->prepare("UPDATE acc_invoices SET status = 'CANCELLED' WHERE invoice_id = ?")->execute([$id]);
                    $db->commit();
                    $_SESSION['flash_success'] = 'Invoice cancelled successfully.';
                } catch (Exception $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    $_SESSION['flash_error'] = 'Could not delete invoice: ' . $e->getMessage();
                }
            }
        }
        header('Location: ' . APP_URL . '/invoices');
        exit;
    }

    public function preview($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/invoices');
            exit;
        }

        $db = (new Model())->getDb();
        $invoiceModel = $this->model('Invoice');

        // 1. Fetch Invoice + Party Details (including billing & shipping address)
        $stmt = $db->prepare("
            SELECT inv.*, 
                   p.name AS party_name, 
                   p.business_name, 
                   p.address, 
                   p.shipping_address, 
                   p.gstin, 
                   p.phone, 
                   p.state AS party_state
            FROM acc_invoices inv
            LEFT JOIN acc_parties p ON inv.party_id = p.party_id
            WHERE inv.invoice_id = ?
        ");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            die("Invoice not found.");
        }

        // 2. Fetch Line Items from acc_invoice_items
        $itemStmt = $db->prepare("SELECT * FROM acc_invoice_items WHERE invoice_id = ?");
        $itemStmt->execute([$id]);
        $items = $itemStmt->fetchAll();

        // 3. Fetch Linked Pay-Ins & Direct Payment Allocations
        $allocStmt = $db->prepare("
            SELECT pa.*, pay.payment_type, pay.reference_number, pay.payment_method, pay.payment_date
            FROM acc_payment_allocations pa
            LEFT JOIN acc_payments pay ON pa.payment_id = pay.payment_id
            WHERE pa.invoice_id = ?
        ");
        $allocStmt->execute([$id]);
        $allocations = $allocStmt->fetchAll();

        // 4. Fetch General Settings
        $settings = $invoiceModel->getAllSettings();

        // Pass variables to view matching both $items and $invoiceItems
        $this->view('invoices/preview', [
            'invoice'      => $invoice,
            'items'        => $items,
            'invoiceItems' => $items,
            'allocations'  => $allocations,
            'settings'     => $settings
        ]);
    }

    public function duplicate($invoiceId = null) {
        if (!$invoiceId) {
            header('Location: ' . APP_URL . '/invoices');
            exit;
        }
        $invoiceModel = $this->model('Invoice');
        $db = (new Model())->getDb();

        $original = $invoiceModel->getInvoiceById($invoiceId);
        $parties = $this->model('Party')->getAllParties();
        // Fixed: Use 'name' instead of 'item_name'
        $items = $db->query("SELECT * FROM acc_items WHERE status = 1 ORDER BY name ASC")->fetchAll();
        $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
        $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $settings = $invoiceModel->getAllSettings();
        $generatedInvoiceNo = $invoiceModel->getNextDocumentNumber('INVOICE');

        $this->view('invoices/create', [
            'parties' => $parties,
            'items' => $items,
            'units' => $units,
            'taxes' => $taxes,
            'banks' => $banks,
            'settings' => $settings,
            'invoice_number' => $generatedInvoiceNo,
            'duplicateData' => $original
        ]);
    }

    public function exportCsv() {
        $invoiceModel = $this->model('Invoice');
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $search = $_GET['search'] ?? null;

        $invoices = $invoiceModel->getInvoices($fromDate, $toDate, $search);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=tax_invoices_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Invoice No', 'Party Name', 'Place of Supply', 'Taxable Amount', 'Tax Amount', 'Total Amount', 'Paid/Linked', 'Balance Due', 'Status']);

        foreach ($invoices as $inv) {
            fputcsv($output, [
                $inv['invoice_date'],
                $inv['invoice_number'],
                $inv['party_name'],
                $inv['place_of_supply'],
                $inv['taxable_amount'],
                $inv['tax_amount'],
                $inv['total_amount'],
                $inv['paid_amount'] + $inv['advance_linked_amount'],
                $inv['balance_due'],
                $inv['status']
            ]);
        }
        fclose($output);
        exit;
    }

    public function getPartyAdvanceAjax($partyId = null) {
        if (!$partyId) {
            $this->json(['unallocated_amount' => 0]);
            return;
        }
        $amount = $this->model('Invoice')->getPartyUnallocatedBalance($partyId);
        $this->json(['unallocated_amount' => $amount]);
    }


    
    public function getPartyUnusedPayInsAjax($partyId = null) {
        if (!$partyId) {
            $this->json([]);
            return;
        }
        $db = (new Model())->getDb();
        $sql = "SELECT 
                    p.payment_id,
                    p.payment_date,
                    COALESCE(p.reference_number, CONCAT('REC-', p.payment_id)) AS pay_in_no,
                    p.amount AS total_amount,
                    (p.amount - COALESCE((SELECT SUM(pa.allocated_amount) FROM acc_payment_allocations pa WHERE pa.payment_id = p.payment_id), 0)) AS balance_amount
                FROM acc_payments p
                WHERE p.party_id = ? 
                  AND p.payment_type = 'PAY_IN'
                HAVING balance_amount > 0
                ORDER BY p.payment_date ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$partyId]);
        $this->json($stmt->fetchAll());
    }
}