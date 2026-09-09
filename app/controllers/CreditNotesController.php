<?php
class CreditNotesController extends Controller {

    public function index() {
        $db = (new Model())->getDb();

        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $search = $_GET['search'] ?? null;
        $taxAmount = $_GET['tax_amount'] ?? null;

        $sql = "SELECT cn.*, 
                       p.name AS party_name, 
                       p.business_name,
                       inv.invoice_number AS original_invoice_no,
                       COALESCE(cn.adjusted_amount, 0) AS adjusted_amount_val,
                       (cn.total_amount - COALESCE(cn.adjusted_amount, 0)) AS balance_amount
                FROM acc_credit_notes cn
                LEFT JOIN acc_parties p ON cn.party_id = p.party_id
                LEFT JOIN acc_invoices inv ON cn.original_invoice_id = inv.invoice_id
                WHERE 1=1";

        $params = [];
        if (!empty($fromDate)) {
            $sql .= " AND cn.credit_note_date >= ?";
            $params[] = $fromDate;
        }
        if (!empty($toDate)) {
            $sql .= " AND cn.credit_note_date <= ?";
            $params[] = $toDate;
        }
        if (!empty($search)) {
            $sql .= " AND (cn.credit_note_number LIKE ? OR p.name LIKE ? OR p.business_name LIKE ? OR inv.invoice_number LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
        }
        if ($taxAmount !== null && $taxAmount !== '') {
            $sql .= " AND cn.tax_amount = ?";
            $params[] = $taxAmount;
        }

        $sql .= " ORDER BY cn.credit_note_date DESC, cn.credit_note_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $creditNotes = $stmt->fetchAll();

        $totalCredit = 0;
        $totalAdjusted = 0;
        $totalBalance = 0;

        foreach ($creditNotes as $cn) {
            $totalCredit += (float)$cn['total_amount'];
            $totalAdjusted += (float)($cn['adjusted_amount_val'] ?? 0);
            $totalBalance += (float)$cn['balance_amount'];
        }

        $this->view('credit-notes/index', [
            'creditNotes' => $creditNotes,
            'summary' => [
                'totalCredit' => $totalCredit,
                'totalAdjusted' => $totalAdjusted,
                'totalBalance' => $totalBalance
            ],
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'search' => $search,
                'tax_amount' => $taxAmount
            ]
        ]);
    }

    public function exportCsv() {
        $db = (new Model())->getDb();
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $search = $_GET['search'] ?? null;

        $sql = "SELECT cn.*, p.name AS party_name, inv.invoice_number AS original_invoice_no,
                       (cn.total_amount - COALESCE(cn.adjusted_amount, 0)) AS balance_amount
                FROM acc_credit_notes cn
                LEFT JOIN acc_parties p ON cn.party_id = p.party_id
                LEFT JOIN acc_invoices inv ON cn.original_invoice_id = inv.invoice_id
                WHERE 1=1";

        $params = [];
        if (!empty($fromDate)) { $sql .= " AND cn.credit_note_date >= ?"; $params[] = $fromDate; }
        if (!empty($toDate)) { $sql .= " AND cn.credit_note_date <= ?"; $params[] = $toDate; }
        if (!empty($search)) {
            $sql .= " AND (cn.credit_note_number LIKE ? OR p.name LIKE ? OR inv.invoice_number LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term; $params[] = $term; $params[] = $term;
        }

        $sql .= " ORDER BY cn.credit_note_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $creditNotes = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=credit_notes_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Credit Note No', 'Party Name', 'Against Invoice', 'Total Amount', 'Adjusted Amount', 'Balance', 'Status']);

        foreach ($creditNotes as $cn) {
            fputcsv($output, [
                $cn['credit_note_date'],
                $cn['credit_note_number'],
                $cn['party_name'],
                $cn['original_invoice_no'] ?? 'N/A',
                $cn['total_amount'],
                $cn['adjusted_amount'] ?? 0.00,
                $cn['balance_amount'],
                $cn['status']
            ]);
        }
        fclose($output);
        exit;
    }

    public function create() {
        $invoiceModel = $this->model('Invoice');
        $db = (new Model())->getDb();

        // -------------------------------------------------------------
        // 1. HANDLE FORM SUBMISSION (POST)
        // -------------------------------------------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate quantities against each item's unit before touching the DB
                // (piece/unit-based items must be whole numbers; weight/KG-based units may be decimal).
                if (!empty($_POST['items'])) {
                    validate_items_quantities($_POST['items']);
                }

                $partyId = (int)($_POST['party_id'] ?? 0);
                $creditNoteNo = trim($_POST['credit_note_number'] ?? '');
                $creditNoteDate = $_POST['credit_note_date'] ?? date('Y-m-d');
                $originalInvoiceId = !empty($_POST['original_invoice_id']) ? (int)$_POST['original_invoice_id'] : null;
                $placeOfSupply = trim($_POST['place_of_supply'] ?? 'Kerala');
                $notes = trim($_POST['notes'] ?? '');

                if ($partyId <= 0) {
                    throw new Exception('Please select a valid party for this credit note.');
                }
                if ($creditNoteNo === '') {
                    throw new Exception('Credit note number is required.');
                }

                $companyState = $invoiceModel->getSetting('company_state', 'Kerala');
                $isInterstate = (strcasecmp(trim($companyState), trim($placeOfSupply)) !== 0) ? 1 : 0;

                // Recompute every item and every header total server-side from the
                // raw qty/rate/discount/tax-rate values instead of trusting the totals
                // posted by the browser - this is the source of the reported Credit
                // Note "calculation / validation" bugs (client-side rounding drift,
                // stale hidden fields, tampered totals, etc.).
                $taxableAmount = 0.00;
                $discountAmount = 0.00;
                $taxAmount = 0.00;
                $totalAmount = 0.00;
                $computedItems = [];

                foreach (($_POST['items'] ?? []) as $item) {
                    if (empty($item['item_name'])) continue;

                    $qty = (float)($item['quantity'] ?? 0);
                    $rate = (float)($item['unit_price'] ?? 0);
                    $unit = $item['unit'] ?? 'PCS';
                    $discPct = (float)($item['discount_percent'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 18);

                    if ($qty <= 0) {
                        throw new Exception('Item "' . htmlspecialchars($item['item_name']) . '" must have a quantity greater than zero.');
                    }
                    if ($rate < 0) {
                        throw new Exception('Item "' . htmlspecialchars($item['item_name']) . '" cannot have a negative rate.');
                    }

                    $base = $qty * $rate;
                    // A flat discount amount typed by the user takes precedence over
                    // the percentage field so reverse-edits stay accurate.
                    $itemDiscAmt = isset($item['discount_amount']) && (float)$item['discount_amount'] > 0
                        ? round((float)$item['discount_amount'], 2)
                        : round($base * ($discPct / 100), 2);
                    $itemDiscAmt = min($itemDiscAmt, $base);
                    $itemDiscPct = $base > 0 ? round(($itemDiscAmt / $base) * 100, 2) : 0;
                    $itemTaxable = round($base - $itemDiscAmt, 2);
                    $itemTax = round($itemTaxable * ($taxRate / 100), 2);
                    $itemTotal = round($itemTaxable + $itemTax, 2);

                    $taxableAmount += $itemTaxable;
                    $discountAmount += $itemDiscAmt;
                    $taxAmount += $itemTax;
                    $totalAmount += $itemTotal;

                    $computedItems[] = [
                        'item_name' => $item['item_name'], 'description' => $item['description'] ?? '',
                        'quantity' => $qty, 'unit' => $unit, 'unit_price' => $rate,
                        'discount_percent' => $itemDiscPct, 'discount_amount' => $itemDiscAmt,
                        'tax_rate' => $taxRate, 'tax_amount' => $itemTax, 'total_amount' => $itemTotal
                    ];
                }

                if (empty($computedItems)) {
                    throw new Exception('A credit note needs at least one line item.');
                }

                $roundOff = round($totalAmount) - $totalAmount;
                if (abs($roundOff) < 0.005) $roundOff = 0.00;
                $totalAmount = round($totalAmount + $roundOff, 2);
                $taxableAmount = round($taxableAmount, 2);
                $discountAmount = round($discountAmount, 2);
                $taxAmount = round($taxAmount, 2);

                $cgstAmount = $isInterstate ? 0.00 : round($taxAmount / 2, 2);
                $sgstAmount = $isInterstate ? 0.00 : round($taxAmount / 2, 2);
                $igstAmount = $isInterstate ? $taxAmount : 0.00;

                $db->beginTransaction();

                // Credit notes are created OPEN. They can only be settled through a Pay-Out:
                // either later, or immediately by linking existing unallocated Pay-Outs here.
                $linkedPayOuts = [];
                $adjustedAmount = 0.00;
                foreach (($_POST['linked_pay_out'] ?? []) as $payId => $amt) {
                    $amt = round((float)$amt, 2);
                    if ($amt <= 0) continue;
                    $linkedPayOuts[(int)$payId] = $amt;
                    $adjustedAmount += $amt;
                }
                if ($adjustedAmount > $totalAmount) {
                    $adjustedAmount = $totalAmount;
                }
                $status = ($totalAmount > 0 && $adjustedAmount >= $totalAmount - 0.005)
                    ? 'ADJUSTED'
                    : ($adjustedAmount > 0 ? 'PARTIALLY_ADJUSTED' : 'OPEN');

                // Guard against duplicate submission (form refresh / back-button re-post)
                $dupStmt = $db->prepare("SELECT credit_note_id FROM acc_credit_notes WHERE credit_note_number = ? LIMIT 1");
                $dupStmt->execute([$creditNoteNo]);
                if ($existingId = $dupStmt->fetchColumn()) {
                    $db->rollBack();
                    header('Location: ' . APP_URL . '/creditNotes/preview/' . $existingId);
                    exit;
                }

                // Insert into acc_credit_notes
                // NOTE: `credit_date` and `subtotal` are legacy NOT NULL columns
                // (no default) left over from an earlier schema revision that are
                // no longer referenced anywhere else in the app - they must still
                // be supplied or this INSERT fails under strict SQL mode.
                $stmt = $db->prepare("
                    INSERT INTO acc_credit_notes (
                        credit_note_number, party_id, original_invoice_id, credit_note_date, credit_date,
                        place_of_supply, is_interstate, subtotal, taxable_amount, discount_amount, tax_amount, 
                        cgst_amount, sgst_amount, igst_amount, round_off, total_amount, 
                        adjusted_amount, notes, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $creditNoteNo, $partyId, $originalInvoiceId, $creditNoteDate, $creditNoteDate,
                    $placeOfSupply, $isInterstate, $taxableAmount, $taxableAmount, $discountAmount, $taxAmount,
                    $cgstAmount, $sgstAmount, $igstAmount, $roundOff, $totalAmount,
                    $adjustedAmount, $notes, $status
                ]);
                $creditNoteId = $db->lastInsertId();

                // Insert line items (server-recomputed values, not the raw POST)
                $itemStmt = $db->prepare("
                    INSERT INTO acc_credit_note_items (
                        credit_note_id, item_name, description, quantity, unit, 
                        unit_price, discount_percent, discount_amount, tax_rate, 
                        tax_amount, total_amount
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($computedItems as $item) {
                    $itemStmt->execute([
                        $creditNoteId,
                        trim($item['item_name']),
                        trim($item['description']),
                        $item['quantity'],
                        $item['unit'],
                        $item['unit_price'],
                        $item['discount_percent'],
                        $item['discount_amount'],
                        $item['tax_rate'],
                        $item['tax_amount'],
                        $item['total_amount']
                    ]);
                }

                // Direct Payout: cash/bank paid out to the customer immediately at
                // credit-note creation time, mirroring the Invoice "direct payment" flow.
                $directPayoutTotal = 0.00;
                if (!empty($_POST['payouts'])) {
                    $payStmt = $db->prepare("INSERT INTO acc_payments 
                        (payment_type, party_id, bank_id, amount, payment_method, reference_number, payment_date, notes, status) 
                        VALUES ('PAY_OUT', ?, ?, ?, ?, ?, ?, ?, 'COMPLETED')");
                    $allocStmt = $db->prepare("INSERT INTO acc_payment_allocations 
                        (payment_id, invoice_id, credit_note_id, linked_payment_id, allocated_amount) 
                        VALUES (?, NULL, ?, NULL, ?)");

                    foreach ($_POST['payouts'] as $po) {
                        $poAmount = round((float)($po['amount'] ?? 0), 2);
                        if ($poAmount <= 0) continue;
                        // Never let direct payouts exceed the credit note's remaining value
                        $poAmount = min($poAmount, max(0, $totalAmount - $adjustedAmount - $directPayoutTotal));
                        if ($poAmount <= 0) continue;

                        $payStmt->execute([
                            $partyId,
                            !empty($po['bank_id']) ? (int)$po['bank_id'] : 1,
                            $poAmount,
                            $po['payment_method'] ?? 'Bank Account',
                            'DIRECT-PAY-' . $creditNoteNo,
                            $creditNoteDate,
                            'Direct payout on Credit Note ' . $creditNoteNo
                        ]);
                        $payoutId = $db->lastInsertId();
                        $allocStmt->execute([$payoutId, $creditNoteId, $poAmount]);
                        $directPayoutTotal += $poAmount;
                    }

                    if ($directPayoutTotal > 0) {
                        $adjustedAmount = round($adjustedAmount + $directPayoutTotal, 2);
                        $status = ($totalAmount > 0 && $adjustedAmount >= $totalAmount - 0.005)
                            ? 'ADJUSTED'
                            : ($adjustedAmount > 0 ? 'PARTIALLY_ADJUSTED' : 'OPEN');
                        $db->prepare("UPDATE acc_credit_notes SET adjusted_amount = ?, status = ? WHERE credit_note_id = ?")
                           ->execute([$adjustedAmount, $status, $creditNoteId]);
                    }
                }

                // Increment sequence counter in acc_settings
                $db->prepare("
                    UPDATE acc_settings 
                    SET setting_value = CAST(setting_value AS UNSIGNED) + 1 
                    WHERE setting_key = 'credit_note_next_number'
                ")->execute();

                // Record Pay-Out settlements against this credit note
                if (!empty($linkedPayOuts)) {
                    $allocStmt = $db->prepare("
                        INSERT INTO acc_payment_allocations (payment_id, credit_note_id, allocated_amount, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    $balStmt = $db->prepare("
                        SELECT p.amount - COALESCE((
                            SELECT SUM(pa.allocated_amount) FROM acc_payment_allocations pa WHERE pa.payment_id = p.payment_id
                        ), 0) AS balance_amount
                        FROM acc_payments p
                        WHERE p.payment_id = ? AND p.party_id = ? AND p.payment_type = 'PAY_OUT'
                    ");

                    $appliedTotal = 0.00;
                    foreach ($linkedPayOuts as $payId => $amt) {
                        // Never allocate more than the Pay-Out still has available (party-isolated)
                        $balStmt->execute([$payId, $partyId]);
                        $available = (float)$balStmt->fetchColumn();
                        $apply = min($amt, max(0, $available), max(0, $totalAmount - $appliedTotal));
                        if ($apply <= 0) continue;
                        $allocStmt->execute([$payId, $creditNoteId, round($apply, 2)]);
                        $appliedTotal += round($apply, 2);
                    }

                    if (abs($appliedTotal - $adjustedAmount) > 0.005) {
                        $adjustedAmount = $appliedTotal;
                        $status = ($totalAmount > 0 && $adjustedAmount >= $totalAmount - 0.005)
                            ? 'ADJUSTED'
                            : ($adjustedAmount > 0 ? 'PARTIALLY_ADJUSTED' : 'OPEN');
                        $upd = $db->prepare("UPDATE acc_credit_notes SET adjusted_amount = ?, status = ? WHERE credit_note_id = ?");
                        $upd->execute([$adjustedAmount, $status, $creditNoteId]);
                    }
                }

                $db->commit();

                header('Location: ' . APP_URL . '/creditNotes/preview/' . $creditNoteId);
                exit;

            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = $e->getMessage();
                $parties = $this->model('Party')->getAllParties();
                $itemsErr = $db->query("SELECT * FROM acc_items WHERE status = 1 ORDER BY name ASC")->fetchAll();
                $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
                $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
                $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
                $settings = $invoiceModel->getAllSettings();
                $this->view('credit-notes/create', [
                    'parties' => $parties, 'items' => $itemsErr, 'units' => $units, 'taxes' => $taxes,
                    'banks' => $banks, 'settings' => $settings,
                    'credit_note_number' => $_POST['credit_note_number'] ?? $invoiceModel->getNextDocumentNumber('CREDIT_NOTE'),
                    'error' => $error
                ]);
                exit;
            }
        }

        // -------------------------------------------------------------
        // 2. RENDER CREATE FORM (GET)
        // -------------------------------------------------------------
        $parties = $this->model('Party')->getAllParties();
        $items = $db->query("SELECT * FROM acc_items WHERE status = 1 ORDER BY name ASC")->fetchAll();
        $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
        $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $settings = $invoiceModel->getAllSettings();

        $generatedCreditNoteNo = $invoiceModel->getNextDocumentNumber('CREDIT_NOTE');

        $this->view('credit-notes/create', [
            'parties'            => $parties,
            'items'              => $items,
            'units'              => $units,
            'taxes'              => $taxes,
            'banks'              => $banks,
            'settings'           => $settings,
            'credit_note_number' => $generatedCreditNoteNo
        ]);
    }

    public function edit($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/creditNotes'); exit; }
        $db = (new Model())->getDb();
        $invoiceModel = $this->model('Invoice');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyState = $invoiceModel->getSetting('company_state', 'Kerala');
            $placeOfSupply = trim($_POST['place_of_supply'] ?? $companyState);
            $isInterstate = (strcasecmp(trim($companyState), $placeOfSupply) !== 0) ? 1 : 0;

            // Header-level fields only - see InvoicesController::edit() for the rationale.
            $stmt = $db->prepare("UPDATE acc_credit_notes SET party_id = ?, credit_note_date = ?, place_of_supply = ?, is_interstate = ?, notes = ?, reason = ? WHERE credit_note_id = ?");
            $stmt->execute([
                (int)$_POST['party_id'],
                $_POST['credit_note_date'],
                $placeOfSupply,
                $isInterstate,
                trim($_POST['notes'] ?? ''),
                trim($_POST['reason'] ?? ''),
                $id
            ]);
            $_SESSION['flash_success'] = 'Credit note details updated. Line items and amounts were left unchanged.';
            header('Location: ' . APP_URL . '/creditNotes/preview/' . $id);
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM acc_credit_notes WHERE credit_note_id = ?");
        $stmt->execute([$id]);
        $creditNote = $stmt->fetch();
        if (!$creditNote) { header('Location: ' . APP_URL . '/creditNotes'); exit; }

        $itemStmt = $db->prepare("SELECT * FROM acc_credit_note_items WHERE credit_note_id = ?");
        $itemStmt->execute([$id]);
        $creditNote['items'] = $itemStmt->fetchAll();

        $parties = $this->model('Party')->getAllParties();

        $linkCheck = $db->prepare("SELECT COUNT(*) FROM acc_payment_allocations WHERE credit_note_id = ?");
        $linkCheck->execute([$id]);
        $hasLinks = (int)$linkCheck->fetchColumn() > 0;

        $this->view('credit-notes/edit', ['creditNote' => $creditNote, 'parties' => $parties, 'hasLinks' => $hasLinks]);
    }

    public function delete($id = null) {
        if ($id) {
            $db = (new Model())->getDb();
            $linkCheck = $db->prepare("
                SELECT pay.reference_number
                FROM acc_payment_allocations pa
                JOIN acc_payments pay ON pa.payment_id = pay.payment_id
                WHERE pa.credit_note_id = ?
            ");
            $linkCheck->execute([$id]);
            $links = $linkCheck->fetchAll();

            if (!empty($links)) {
                $refs = array_map(function ($l) { return $l['reference_number']; }, $links);
                $_SESSION['flash_error'] = 'This credit note cannot be deleted because it has payout(s) linked to it: '
                    . implode(', ', array_unique($refs)) . '. Delete or unlink those payments first.';
            } else {
                try {
                    $db->beginTransaction();
                    $db->prepare("DELETE FROM acc_credit_note_items WHERE credit_note_id = ?")->execute([$id]);
                    $db->prepare("DELETE FROM acc_credit_notes WHERE credit_note_id = ?")->execute([$id]);
                    $db->commit();
                    $_SESSION['flash_success'] = 'Credit note deleted successfully.';
                } catch (Exception $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    $_SESSION['flash_error'] = 'Could not delete credit note: ' . $e->getMessage();
                }
            }
        }
        header('Location: ' . APP_URL . '/creditNotes');
        exit;
    }

    // -------------------------------------------------------------
    // 3. CREDIT NOTE PREVIEW ACTION
    // -------------------------------------------------------------
    public function preview($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/creditNotes');
            exit;
        }

        $db = (new Model())->getDb();
        $invoiceModel = $this->model('Invoice');

        // Fetch Credit Note Details
        $stmt = $db->prepare("
            SELECT cn.*, p.name AS party_name, p.business_name, p.address, p.gstin, p.phone, p.state AS party_state,
                   inv.invoice_number AS original_invoice_no,
                   inv.total_amount AS invoice_total_amount
            FROM acc_credit_notes cn
            LEFT JOIN acc_parties p ON cn.party_id = p.party_id
            LEFT JOIN acc_invoices inv ON cn.original_invoice_id = inv.invoice_id
            WHERE cn.credit_note_id = ?
        ");
        $stmt->execute([$id]);
        $creditNote = $stmt->fetch();

        if (!$creditNote) {
            header('Location: ' . APP_URL . '/creditNotes');
            exit;
        }

        // Fetch Credit Note Items
        $itemStmt = $db->prepare("SELECT * FROM acc_credit_note_items WHERE credit_note_id = ?");
        $itemStmt->execute([$id]);
        $items = $itemStmt->fetchAll();

        // Fetch Pay-Out settlement allocations (direct payout vs linked existing Pay-Out,
        // told apart the same way as Invoice preview: DIRECT-PAY- reference prefix)
        $allocStmt = $db->prepare("
            SELECT pa.*, pay.payment_type, pay.reference_number, pay.payment_method, pay.payment_date
            FROM acc_payment_allocations pa
            LEFT JOIN acc_payments pay ON pa.payment_id = pay.payment_id
            WHERE pa.credit_note_id = ?
        ");
        $allocStmt->execute([$id]);
        $allocations = $allocStmt->fetchAll();

        $settings = $invoiceModel->getAllSettings();

        $this->view('credit-notes/preview', [
            'creditNote'  => $creditNote,
            'items'       => $items,
            'allocations' => $allocations,
            'settings'    => $settings
        ]);
    }

    public function duplicate($creditNoteId = null) {
        if (!$creditNoteId) { header('Location: ' . APP_URL . '/creditNotes'); exit; }
        $db = (new Model())->getDb();
        $invoiceModel = $this->model('Invoice');

        $stmt = $db->prepare("SELECT * FROM acc_credit_notes WHERE credit_note_id = ?");
        $stmt->execute([$creditNoteId]);
        $original = $stmt->fetch();

        $parties = $this->model('Party')->getAllParties();
        $items = $db->query("SELECT * FROM acc_items WHERE status = 1 ORDER BY name ASC")->fetchAll();
        $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
        $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
        $banks = $db->query("SELECT * FROM acc_bank_accounts WHERE status = 1")->fetchAll();
        $settings = $invoiceModel->getAllSettings();

        $this->view('credit-notes/create', [
            'parties' => $parties, 'items' => $items, 'units' => $units, 'taxes' => $taxes,
            'banks' => $banks, 'settings' => $settings,
            'credit_note_number' => $invoiceModel->getNextDocumentNumber('CREDIT_NOTE'),
            'duplicateData' => $original
        ]);
    }

    // AJAX 1: Fetch all finalized invoices for a selected party
    public function getPartyInvoicesAjax($partyId) {
        $db = (new Model())->getDb();
        // Report the amount ACTUALLY received against each invoice (Pay-In allocations),
        // falling back to the stored paid/advance columns when no allocations exist.
        $stmt = $db->prepare("
            SELECT
                i.invoice_id,
                i.invoice_number,
                i.invoice_date,
                i.total_amount,
                GREATEST(
                    COALESCE((
                        SELECT SUM(pa.allocated_amount)
                        FROM acc_payment_allocations pa
                        INNER JOIN acc_payments p ON p.payment_id = pa.payment_id
                        WHERE pa.invoice_id = i.invoice_id
                          AND p.payment_type = 'PAY_IN'
                          AND (p.status != 'CANCELLED' OR p.status IS NULL)
                    ), 0),
                    COALESCE(i.paid_amount, 0) + COALESCE(i.advance_linked_amount, 0)
                ) AS paid_amount,
                GREATEST(0, i.total_amount - GREATEST(
                    COALESCE((
                        SELECT SUM(pa.allocated_amount)
                        FROM acc_payment_allocations pa
                        INNER JOIN acc_payments p ON p.payment_id = pa.payment_id
                        WHERE pa.invoice_id = i.invoice_id
                          AND p.payment_type = 'PAY_IN'
                          AND (p.status != 'CANCELLED' OR p.status IS NULL)
                    ), 0),
                    COALESCE(i.paid_amount, 0) + COALESCE(i.advance_linked_amount, 0)
                )) AS balance_amount
            FROM acc_invoices i
            WHERE i.party_id = ? AND i.status != 'CANCELLED'
            ORDER BY i.invoice_date DESC, i.invoice_id DESC
        ");
        $stmt->execute([$partyId]);
        $this->json($stmt->fetchAll());
    }

    // AJAX 2: Fetch invoice items to auto-populate the table
    public function getInvoiceItemsAjax($invoiceId) {
        $db = (new Model())->getDb();
        $stmt = $db->prepare("
            SELECT 
                ii.*,
                COALESCE(it.name, ii.item_name) AS item_name,
                COALESCE(it.tax_rate, ii.tax_rate, 18) AS tax_rate,
                COALESCE(it.unit, ii.unit, 'PCS') AS unit
            FROM acc_invoice_items ii
            LEFT JOIN acc_items it ON ii.item_id = it.item_id
            WHERE ii.invoice_id = ?
        ");
        $stmt->execute([$invoiceId]);
        $this->json($stmt->fetchAll());
    }

    // AJAX to fetch unallocated/unused Pay-Outs for the chosen party
    public function getPartyUnusedPayOutsAjax($partyId) {
    $db = (new Model())->getDb();
    
    // Fetch completed PAY_OUT payments for this party that still have unallocated balance
    $sql = "
        SELECT 
            p.payment_id,
            p.payment_date,
            p.reference_number AS pay_out_no,
            p.amount AS total_amount,
            (p.amount - COALESCE((SELECT SUM(pa.allocated_amount) FROM acc_payment_allocations pa WHERE pa.payment_id = p.payment_id), 0)) AS balance_amount
        FROM acc_payments p
        WHERE p.party_id = ? 
          AND p.payment_type = 'PAY_OUT' 
          AND (p.status != 'CANCELLED' OR p.status IS NULL)
        HAVING balance_amount > 0.005
        ORDER BY p.payment_date ASC
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$partyId]);
    $payOuts = $stmt->fetchAll();
    
    $this->json($payOuts);
}
}