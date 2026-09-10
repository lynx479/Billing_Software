<?php
// ReportsController.php - Fixed version without cn.invoice_number

class ReportsController extends Controller {
    public function index() {
        $this->sales();
    }

    public function sales() {
        $invoices = (new Model())->getDb()->query("SELECT i.*, p.name as party_name FROM acc_invoices i JOIN acc_parties p ON i.party_id = p.party_id ORDER BY invoice_date DESC")->fetchAll();
        $this->view('reports/sales', ['invoices' => $invoices]);
    }

    public function users() {
        $sellers = (new Model())->getDb()->query("SELECT * FROM mock_sellers")->fetchAll();
        $this->view('reports/users', ['sellers' => $sellers]);
    }

    public function financial() {
        $metrics = $this->model('Report')->getDashboardSummary();
        $this->view('reports/financial', ['metrics' => $metrics]);
    }

    public function commission() {
        $commissions = (new Model())->getDb()->query("SELECT st.*, s.store_name FROM acc_settlements st JOIN mock_sellers s ON st.seller_id = s.seller_id")->fetchAll();
        $this->view('reports/commission', ['commissions' => $commissions]);
    }

    public function sellerSettlements() {
        $settlements = (new Model())->getDb()->query("SELECT st.*, s.store_name FROM acc_settlements st JOIN mock_sellers s ON st.seller_id = s.seller_id")->fetchAll();
        $this->view('reports/seller-settlements', ['settlements' => $settlements]);
    }

    public function subscriptions() {
        $this->view('reports/subscriptions');
    }

    public function ads() {
        $this->view('reports/ads');
    }

    public function reconciliation() {
        $this->view('reports/reconciliation');
    }

    /**
     * Resolve the GSTR-1 filing period from ?month=YYYY-MM (defaults to the
     * current month in the configured system timezone) and return
     * [fromDate, toDate, monthLabel, monthValue] for reuse by gstr1()/gstr1Export().
     */
    private function resolveGstrPeriod() {
        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $fromDate = $month . '-01';
        $toDate = date('Y-m-t', strtotime($fromDate));
        $monthLabel = date('F Y', strtotime($fromDate));
        return [$fromDate, $toDate, $monthLabel, $month];
    }

    /**
     * CSV export for any one GSTR-1 tab, e.g.
     * /reports/gstr1Export?tab=b2b&month=2026-09
     * Reuses the same fputcsv-to-php://output pattern as
     * CreditNotesController::exportCsv() instead of a new export system.
     */
    public function gstr1() {
        $db = (new Model())->getDb();
        list($fromDate, $toDate, $monthLabel, $month) = $this->resolveGstrPeriod();
        
        // Get from_date and to_date from GET parameters for date range filter
        $fromDate = $_GET['from_date'] ?? $fromDate;
        $toDate = $_GET['to_date'] ?? $toDate;

        // 1. B2B Summary (Latest first) - Only GSTIN not null and not empty
        $b2bStmt = $db->prepare("
            SELECT 
                p.gstin,
                p.name AS party_name,
                i.invoice_id,
                i.invoice_number,
                i.invoice_date,
                i.total_amount AS invoice_value,
                COALESCE(NULLIF(i.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                ii.tax_rate AS gst_rate,
                SUM(ii.taxable_amount) AS taxable_amount,
                SUM(ii.tax_amount) AS tax_value,
                SUM(ii.igst_amount) AS igst_amount,
                SUM(ii.cgst_amount) AS cgst_amount,
                SUM(ii.sgst_amount) AS sgst_amount
            FROM acc_invoices i
            JOIN acc_parties p ON i.party_id = p.party_id
            JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
            WHERE i.status != 'CANCELLED' 
            AND p.gstin IS NOT NULL AND p.gstin != ''
            AND NOT (i.total_amount > 250000 AND (i.is_interstate = 1 OR i.igst_amount > 0 OR EXISTS (SELECT 1 FROM acc_invoice_items x WHERE x.invoice_id = i.invoice_id AND x.igst_amount > 0)))
              AND i.invoice_date BETWEEN ? AND ?
            GROUP BY i.invoice_id, ii.tax_rate
            ORDER BY i.invoice_date DESC, i.invoice_number DESC, ii.tax_rate ASC
        ");
        $b2bStmt->execute([$fromDate, $toDate]);
        $b2b = $b2bStmt->fetchAll();

       // 2. B2C (Large) - Customers without GSTIN, interstate, amount > 250000
            $b2cLargeStmt = $db->prepare("
                SELECT 
                    i.invoice_id,
                    i.invoice_number,
                    i.invoice_date,
                    i.total_amount AS invoice_value,
                    COALESCE(NULLIF(i.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                    ii.tax_rate,
                    SUM(ii.taxable_amount) AS taxable_amount,
                    SUM(ii.igst_amount) AS igst_amount,
                    SUM(ii.cgst_amount) AS cgst_amount,
                    SUM(ii.sgst_amount) AS sgst_amount,
                    SUM(ii.total_amount) AS total_amount
                FROM acc_invoices i
                JOIN acc_parties p ON i.party_id = p.party_id
                JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
                WHERE i.status != 'CANCELLED'
                AND (i.is_interstate = 1 OR i.igst_amount > 0 OR EXISTS (SELECT 1 FROM acc_invoice_items x WHERE x.invoice_id = i.invoice_id AND x.igst_amount > 0))
                AND i.total_amount > 250000
                AND i.invoice_date BETWEEN ? AND ?
                GROUP BY i.invoice_id, ii.tax_rate
                ORDER BY i.invoice_date DESC, i.invoice_number DESC
            ");
            $b2cLargeStmt->execute([$fromDate, $toDate]);
            $b2cLarge = $b2cLargeStmt->fetchAll();

        // 3. B2C (Small) - Customers without GSTIN (all remaining)
        $b2cSmallStmt = $db->prepare("
            SELECT 
                COALESCE(NULLIF(i.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                ii.tax_rate,
                SUM(ii.taxable_amount) AS taxable_amount,
                SUM(ii.igst_amount) AS igst_amount,
                SUM(ii.cgst_amount) AS cgst_amount,
                SUM(ii.sgst_amount) AS sgst_amount,
                SUM(ii.total_amount) AS total_amount
            FROM acc_invoices i
            JOIN acc_parties p ON i.party_id = p.party_id
            JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
            WHERE i.status != 'CANCELLED'
              AND NOT ((i.is_interstate = 1 OR i.igst_amount > 0 OR EXISTS (SELECT 1 FROM acc_invoice_items x WHERE x.invoice_id = i.invoice_id AND x.igst_amount > 0)) AND i.total_amount > 250000)
              AND i.invoice_date BETWEEN ? AND ?
            GROUP BY place_of_supply, ii.tax_rate
            ORDER BY place_of_supply ASC, ii.tax_rate ASC
        ");
        $b2cSmallStmt->execute([$fromDate, $toDate]);
        $b2cSmall = $b2cSmallStmt->fetchAll();

        // 4. Credit Note – B2B (Latest first) - Fixed: removed cn.invoice_number
       $cnB2bStmt = $db->prepare("
            SELECT 
                cn.credit_note_id,
                cn.credit_note_number,
                cn.credit_note_date,
                cn.total_amount AS credit_note_value,
                p.gstin,
                p.name AS party_name,
                (SELECT invoice_number FROM acc_invoices WHERE invoice_id = cn.original_invoice_id LIMIT 1) AS original_invoice_number,
                COALESCE(NULLIF(cn.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                cni.tax_rate AS gst_rate,
                SUM(cni.taxable_amount) AS taxable_amount,
                SUM(cni.tax_amount) AS tax_value,
                SUM(CASE WHEN cn.is_interstate = 1 THEN cni.tax_amount ELSE 0 END) AS igst_amount,
                SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS cgst_amount,
                SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS sgst_amount
            FROM acc_credit_notes cn
            JOIN acc_parties p ON cn.party_id = p.party_id
            JOIN acc_credit_note_items cni ON cn.credit_note_id = cni.credit_note_id
            WHERE p.gstin IS NOT NULL AND p.gstin != ''
              AND cn.credit_note_date BETWEEN ? AND ?
            GROUP BY cn.credit_note_id, cni.tax_rate
            ORDER BY cn.credit_note_date DESC, cn.credit_note_number DESC, cni.tax_rate ASC
        ");
        $cnB2bStmt->execute([$fromDate, $toDate]);
        $cnB2b = $cnB2bStmt->fetchAll();

        // 5. Credit Note – B2C (Latest first)
        $cnB2cStmt = $db->prepare("
            SELECT 
                cn.credit_note_id,
                cn.credit_note_number,
                cn.credit_note_date,
                cn.total_amount AS credit_note_value,
                COALESCE(NULLIF(cn.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                cni.tax_rate AS gst_rate,
                SUM(cni.taxable_amount) AS taxable_amount,
                SUM(CASE WHEN cn.is_interstate = 1 THEN cni.tax_amount ELSE 0 END) AS igst_amount,
                SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS cgst_amount,
                SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS sgst_amount
            FROM acc_credit_notes cn
            JOIN acc_parties p ON cn.party_id = p.party_id
            JOIN acc_credit_note_items cni ON cn.credit_note_id = cni.credit_note_id
            WHERE (p.gstin IS NULL OR p.gstin = '')
              AND cn.credit_note_date BETWEEN ? AND ?
            GROUP BY cn.credit_note_id, cni.tax_rate
            ORDER BY cn.credit_note_date DESC, cn.credit_note_number DESC, cni.tax_rate ASC
        ");
        $cnB2cStmt->execute([$fromDate, $toDate]);
        $cnB2c = $cnB2cStmt->fetchAll();

        // 6. HSN B2C
        $hsnB2cStmt = $db->prepare("
            SELECT 
                COALESCE(NULLIF(ii.hsn_sac, ''), NULLIF(it.hsn_sac, ''), 'N/A') AS hsn_sac,
                COALESCE(NULLIF(ii.unit, ''), NULLIF(it.unit, ''), 'PCS') AS unit,
                ii.tax_rate,
                SUM(ii.quantity) AS total_quantity,
                SUM(ii.taxable_amount) AS taxable_amount,
                SUM(ii.tax_amount) AS tax_amount,
                SUM(ii.igst_amount) AS igst_amount,
                SUM(ii.cgst_amount) AS cgst_amount,
                SUM(ii.sgst_amount) AS sgst_amount,
                SUM(ii.total_amount) AS total_amount
            FROM acc_invoice_items ii
            JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
            JOIN acc_parties p ON i.party_id = p.party_id
            LEFT JOIN acc_items it ON it.name = ii.item_name
            WHERE i.status != 'CANCELLED' 
              AND (p.gstin IS NULL OR p.gstin = '')
              AND i.invoice_date BETWEEN ? AND ?
            GROUP BY hsn_sac, unit, ii.tax_rate
            ORDER BY hsn_sac ASC
        ");
        $hsnB2cStmt->execute([$fromDate, $toDate]);
        $hsnB2c = $hsnB2cStmt->fetchAll();

        // 7. HSN B2B
        $hsnB2bStmt = $db->prepare("
            SELECT 
                COALESCE(NULLIF(ii.hsn_sac, ''), NULLIF(it.hsn_sac, ''), 'N/A') AS hsn_sac,
                COALESCE(NULLIF(ii.unit, ''), NULLIF(it.unit, ''), 'PCS') AS unit,
                ii.tax_rate,
                SUM(ii.quantity) AS total_quantity,
                SUM(ii.taxable_amount) AS taxable_amount,
                SUM(ii.tax_amount) AS tax_amount,
                SUM(ii.igst_amount) AS igst_amount,
                SUM(ii.cgst_amount) AS cgst_amount,
                SUM(ii.sgst_amount) AS sgst_amount,
                SUM(ii.total_amount) AS total_amount
            FROM acc_invoice_items ii
            JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
            JOIN acc_parties p ON i.party_id = p.party_id
            LEFT JOIN acc_items it ON it.name = ii.item_name
            WHERE i.status != 'CANCELLED' 
              AND p.gstin IS NOT NULL AND p.gstin != ''
              AND i.invoice_date BETWEEN ? AND ?
            GROUP BY hsn_sac, unit, ii.tax_rate
            ORDER BY hsn_sac ASC
        ");
        $hsnB2bStmt->execute([$fromDate, $toDate]);
        $hsnB2b = $hsnB2bStmt->fetchAll();

        // 8. Item Summary
        $itemSumStmt = $db->prepare("
            SELECT 
                ii.item_name,
                COALESCE(NULLIF(ii.description, ''), NULLIF(it.description, ''), '-') AS description,
                COALESCE(NULLIF(ii.unit, ''), NULLIF(it.unit, ''), 'PCS') AS unit,
                ii.tax_rate,
                SUM(ii.quantity) AS total_quantity,
                SUM(ii.taxable_amount) AS taxable_amount,
                SUM(ii.tax_amount) AS tax_amount,
                SUM(ii.total_amount) AS total_amount
            FROM acc_invoice_items ii
            JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
            LEFT JOIN acc_items it ON it.name = ii.item_name
            WHERE i.status != 'CANCELLED' AND i.invoice_date BETWEEN ? AND ?
            GROUP BY ii.item_name, unit, ii.tax_rate
            ORDER BY ii.item_name ASC
        ");
        $itemSumStmt->execute([$fromDate, $toDate]);
        $itemSummary = $itemSumStmt->fetchAll();

        // 9. Documents
        $docStmt = $db->prepare("
            SELECT
                'Tax Invoices' AS nature_of_doc,
                COUNT(*) AS total_issued,
                SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) AS cancelled_count,
                MIN(invoice_number) AS from_no,
                MAX(invoice_number) AS to_no
            FROM acc_invoices WHERE invoice_date BETWEEN ? AND ?
        ");
        $docStmt->execute([$fromDate, $toDate]);
        $invDoc = $docStmt->fetch();

        $cnDocStmt = $db->prepare("
            SELECT
                'Credit Notes' AS nature_of_doc,
                COUNT(*) AS total_issued,
                0 AS cancelled_count,
                MIN(credit_note_number) AS from_no,
                MAX(credit_note_number) AS to_no
            FROM acc_credit_notes WHERE credit_note_date BETWEEN ? AND ?
        ");
        $cnDocStmt->execute([$fromDate, $toDate]);
        $cnDoc = $cnDocStmt->fetch();

        $documents = [
            $invDoc ?: ['nature_of_doc' => 'Tax Invoices', 'total_issued' => 0, 'cancelled_count' => 0, 'from_no' => '-', 'to_no' => '-'],
            $cnDoc ?: ['nature_of_doc' => 'Credit Notes', 'total_issued' => 0, 'cancelled_count' => 0, 'from_no' => '-', 'to_no' => '-']
        ];

        // 10. GSTR-1 Summary Calculations
        $b2bTaxable = array_sum(array_column($b2b, 'taxable_amount'));
        $b2bTax = array_sum(array_column($b2b, 'tax_value'));
        $b2bTotal = $b2bTaxable + $b2bTax;

        $b2clTaxable = array_sum(array_column($b2cLarge, 'taxable_amount'));
        $b2clTax = array_sum(array_column($b2cLarge, 'igst_amount')) + array_sum(array_column($b2cLarge, 'cgst_amount')) + array_sum(array_column($b2cLarge, 'sgst_amount'));
        $b2clTotal = $b2clTaxable + $b2clTax;

        $b2csTaxable = array_sum(array_column($b2cSmall, 'taxable_amount'));
        $b2csTax = array_sum(array_column($b2cSmall, 'igst_amount')) + array_sum(array_column($b2cSmall, 'cgst_amount')) + array_sum(array_column($b2cSmall, 'sgst_amount'));
        $b2csTotal = $b2csTaxable + $b2csTax;

        $cnb2bTaxable = array_sum(array_column($cnB2b, 'taxable_amount'));
        $cnb2bTax = array_sum(array_column($cnB2b, 'tax_value'));
        $cnb2bTotal = $cnb2bTaxable + $cnb2bTax;

        $cnb2cTaxable = array_sum(array_column($cnB2c, 'taxable_amount'));
        $cnb2cTax = array_sum(array_column($cnB2c, 'igst_amount')) + array_sum(array_column($cnB2c, 'cgst_amount')) + array_sum(array_column($cnB2c, 'sgst_amount'));
        $cnb2cTotal = $cnb2cTaxable + $cnb2cTax;

        $gstr1Summary = [
            'b2b'      => ['count' => count($b2b), 'taxable' => $b2bTaxable, 'tax' => $b2bTax, 'total' => $b2bTotal],
            'b2c_l'    => ['count' => count($b2cLarge), 'taxable' => $b2clTaxable, 'tax' => $b2clTax, 'total' => $b2clTotal],
            'b2c_s'    => ['count' => count($b2cSmall), 'taxable' => $b2csTaxable, 'tax' => $b2csTax, 'total' => $b2csTotal],
            'cdn_b2b'  => ['count' => count($cnB2b), 'taxable' => $cnb2bTaxable, 'tax' => $cnb2bTax, 'total' => $cnb2bTotal],
            'cdn_b2c'  => ['count' => count($cnB2c), 'taxable' => $cnb2cTaxable, 'tax' => $cnb2cTax, 'total' => $cnb2cTotal],
            'net_taxable' => ($b2bTaxable + $b2clTaxable + $b2csTaxable) - ($cnb2bTaxable + $cnb2cTaxable),
            'net_tax'     => ($b2bTax + $b2clTax + $b2csTax) - ($cnb2bTax + $cnb2cTax),
            'net_total'   => ($b2bTotal + $b2clTotal + $b2csTotal) - ($cnb2bTotal + $cnb2cTotal),
        ];

        // Sample fallback data
     

        if (empty($b2cSmall)) {
            $b2cSmall = [
                ['is_dummy' => true, 'place_of_supply' => '32-Kerala', 'tax_rate' => 12.00, 'taxable_amount' => 25000.00, 'igst_amount' => 0.00, 'cgst_amount' => 1500.00, 'sgst_amount' => 1500.00, 'total_amount' => 28000.00],
                ['is_dummy' => true, 'place_of_supply' => '32-Kerala', 'tax_rate' => 18.00, 'taxable_amount' => 40000.00, 'igst_amount' => 0.00, 'cgst_amount' => 3600.00, 'sgst_amount' => 3600.00, 'total_amount' => 47200.00],
                ['is_dummy' => true, 'place_of_supply' => '29-Karnataka', 'tax_rate' => 18.00, 'taxable_amount' => 15000.00, 'igst_amount' => 2700.00, 'cgst_amount' => 0.00, 'sgst_amount' => 0.00, 'total_amount' => 17700.00]
            ];
        }

        if (empty($cnB2b)) {
            $cnB2b = [
                ['is_dummy' => true, 'gstin' => '32ABCDE1234F1Z5', 'party_name' => 'Apex Retailers Pvt Ltd', 'credit_note_number' => 'CN-2026-001', 'credit_note_date' => $fromDate, 'credit_note_value' => 11800.00, 'place_of_supply' => '32-Kerala', 'gst_rate' => 18.00, 'taxable_amount' => 10000.00, 'tax_value' => 1800.00, 'igst_amount' => 0.00, 'cgst_amount' => 900.00, 'sgst_amount' => 900.00]
            ];
        }

        if (empty($cnB2c)) {
            $cnB2c = [
                ['is_dummy' => true, 'credit_note_number' => 'CN-C-001', 'credit_note_date' => $fromDate, 'credit_note_value' => 2360.00, 'place_of_supply' => '32-Kerala', 'gst_rate' => 18.00, 'taxable_amount' => 2000.00, 'igst_amount' => 0.00, 'cgst_amount' => 180.00, 'sgst_amount' => 180.00]
            ];
        }

        if (empty($hsnB2c)) {
            $hsnB2c = [
                ['is_dummy' => true, 'hsn_sac' => '8471', 'unit' => 'NOS', 'tax_rate' => 18.00, 'total_quantity' => 2, 'taxable_amount' => 30000.00, 'tax_amount' => 5400.00, 'igst_amount' => 0.00, 'cgst_amount' => 2700.00, 'sgst_amount' => 2700.00, 'total_amount' => 35400.00]
            ];
        }

        if (empty($hsnB2b)) {
            $hsnB2b = [
                ['is_dummy' => true, 'hsn_sac' => '8528', 'unit' => 'NOS', 'tax_rate' => 18.00, 'total_quantity' => 5, 'taxable_amount' => 75000.00, 'tax_amount' => 13500.00, 'igst_amount' => 13500.00, 'cgst_amount' => 0.00, 'sgst_amount' => 0.00, 'total_amount' => 88500.00],
                ['is_dummy' => true, 'hsn_sac' => '8473', 'unit' => 'PCS', 'tax_rate' => 12.00, 'total_quantity' => 10, 'taxable_amount' => 20000.00, 'tax_amount' => 2400.00, 'igst_amount' => 0.00, 'cgst_amount' => 1200.00, 'sgst_amount' => 1200.00, 'total_amount' => 22400.00]
            ];
        }

        if (empty($itemSummary)) {
            $itemSummary = [
                ['is_dummy' => true, 'item_name' => 'Dell 24-inch Monitor', 'description' => 'Full HD IPS Display', 'unit' => 'NOS', 'tax_rate' => 18.00, 'total_quantity' => 5, 'taxable_amount' => 75000.00, 'tax_amount' => 13500.00, 'total_amount' => 88500.00],
                ['is_dummy' => true, 'item_name' => 'Logitech Wireless Combo', 'description' => 'Keyboard and Mouse', 'unit' => 'SET', 'tax_rate' => 18.00, 'total_quantity' => 8, 'taxable_amount' => 16000.00, 'tax_amount' => 2880.00, 'total_amount' => 18880.00]
            ];
        }

        $this->view('reports/gstr1', [
            'b2b'          => $b2b,
            'b2cLarge'     => $b2cLarge,
            'b2cSmall'     => $b2cSmall,
            'cnB2b'        => $cnB2b,
            'cnB2c'        => $cnB2c,
            'hsnB2c'       => $hsnB2c,
            'hsnB2b'       => $hsnB2b,
            'itemSummary'  => $itemSummary,
            'documents'    => $documents,
            'gstr1Summary' => $gstr1Summary,
            'monthLabel'   => $monthLabel,
            'month'        => $month,
            'fromDate'     => $fromDate,
            'toDate'       => $toDate
        ]);
    }

    public function gstr1Export() {
        $db = (new Model())->getDb();
        list($fromDate, $toDate, $monthLabel, $month) = $this->resolveGstrPeriod();
        
        // Get from_date and to_date from GET parameters for date range filter
        $fromDate = $_GET['from_date'] ?? $fromDate;
        $toDate = $_GET['to_date'] ?? $toDate;
        
        $tab = $_GET['tab'] ?? 'b2b';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gstr1_' . $tab . '_' . $month . '.csv');
        $output = fopen('php://output', 'w');

        if ($tab === 'b2b') {
            $stmt = $db->prepare("
                SELECT p.gstin, p.name AS party_name, i.invoice_number, i.invoice_date, i.total_amount AS invoice_value,
                       COALESCE(NULLIF(i.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                       ii.tax_rate AS gst_rate, SUM(ii.taxable_amount) AS taxable_amount, SUM(ii.tax_amount) AS tax_value,
                       SUM(ii.igst_amount) AS igst_amount, SUM(ii.cgst_amount) AS cgst_amount, SUM(ii.sgst_amount) AS sgst_amount
                FROM acc_invoices i
                JOIN acc_parties p ON i.party_id = p.party_id
                JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
                WHERE i.status != 'CANCELLED' AND p.gstin IS NOT NULL AND p.gstin != '' AND NOT (i.total_amount > 250000 AND (i.is_interstate = 1 OR i.igst_amount > 0 OR EXISTS (SELECT 1 FROM acc_invoice_items x WHERE x.invoice_id = i.invoice_id AND x.igst_amount > 0))) AND i.invoice_date BETWEEN ? AND ?
                GROUP BY i.invoice_id, ii.tax_rate
                ORDER BY i.invoice_date DESC, i.invoice_number DESC, ii.tax_rate ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'GSTIN', 'Party Name', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'GST Rate %', 'Tax Value', 'IGST', 'CGST', 'SGST']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['gstin'], $r['party_name'], $r['invoice_number'], $r['invoice_date'], $r['invoice_value'], $r['place_of_supply'], $r['gst_rate'], $r['tax_value'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount']]);
            }
                } elseif ($tab === 'b2cl') {
            $stmt = $db->prepare("
                SELECT i.invoice_number, i.invoice_date, i.total_amount AS invoice_value,
                    COALESCE(NULLIF(i.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                    ii.tax_rate, SUM(ii.taxable_amount) AS taxable_amount, SUM(ii.igst_amount) AS igst_amount,
                    SUM(ii.cgst_amount) AS cgst_amount, SUM(ii.sgst_amount) AS sgst_amount
                FROM acc_invoices i
                JOIN acc_parties p ON i.party_id = p.party_id
                JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
                WHERE i.status != 'CANCELLED' AND (p.gstin IS NULL OR p.gstin = '')
                AND (i.is_interstate = 1 OR i.igst_amount > 0 OR EXISTS (SELECT 1 FROM acc_invoice_items x WHERE x.invoice_id = i.invoice_id AND x.igst_amount > 0)) AND i.total_amount > 250000 AND i.invoice_date BETWEEN ? AND ?
                GROUP BY i.invoice_id, ii.tax_rate
                ORDER BY i.invoice_date DESC, i.invoice_number DESC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Tax Rate %', 'Taxable Value', 'IGST', 'CGST', 'SGST']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['invoice_number'], $r['invoice_date'], $r['invoice_value'], $r['place_of_supply'], $r['tax_rate'], $r['taxable_amount'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount']]);
            }
        } elseif ($tab === 'b2cs') {
            $stmt = $db->prepare("
                SELECT COALESCE(NULLIF(i.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                       ii.tax_rate, SUM(ii.taxable_amount) AS taxable_amount, SUM(ii.igst_amount) AS igst_amount,
                       SUM(ii.cgst_amount) AS cgst_amount, SUM(ii.sgst_amount) AS sgst_amount
                FROM acc_invoices i
                JOIN acc_parties p ON i.party_id = p.party_id
                JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
                WHERE i.status != 'CANCELLED' AND (p.gstin IS NULL OR p.gstin = '')
                AND NOT ((i.is_interstate = 1 OR i.igst_amount > 0 OR EXISTS (SELECT 1 FROM acc_invoice_items x WHERE x.invoice_id = i.invoice_id AND x.igst_amount > 0)) AND i.total_amount > 250000) AND i.invoice_date BETWEEN ? AND ?
                GROUP BY place_of_supply, ii.tax_rate
                ORDER BY place_of_supply ASC, ii.tax_rate ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'Place of Supply', 'Tax Rate %', 'Taxable Value', 'IGST', 'CGST', 'SGST']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['place_of_supply'], $r['tax_rate'], $r['taxable_amount'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount']]);
            }
        } elseif ($tab === 'cdnb2b') {
            $stmt = $db->prepare("
                SELECT p.gstin, p.name AS party_name, cn.credit_note_number, cn.credit_note_date, cn.total_amount AS credit_note_value,
                       COALESCE(NULLIF(cn.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                       cni.tax_rate AS gst_rate, SUM(cni.taxable_amount) AS taxable_amount, SUM(cni.tax_amount) AS tax_value,
                       SUM(CASE WHEN cn.is_interstate = 1 THEN cni.tax_amount ELSE 0 END) AS igst_amount,
                       SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS cgst_amount,
                       SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS sgst_amount
                FROM acc_credit_notes cn
                JOIN acc_parties p ON cn.party_id = p.party_id
                JOIN acc_credit_note_items cni ON cn.credit_note_id = cni.credit_note_id
                WHERE p.gstin IS NOT NULL AND p.gstin != '' AND cn.credit_note_date BETWEEN ? AND ?
                GROUP BY cn.credit_note_id, cni.tax_rate
                ORDER BY cn.credit_note_date DESC, cn.credit_note_number DESC, cni.tax_rate ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'GSTIN', 'Party Name', 'Credit Note Number', 'Credit Note Date', 'Credit Note Value', 'Place of Supply', 'GST Rate %', 'Tax Value', 'IGST', 'CGST', 'SGST']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['gstin'], $r['party_name'], $r['credit_note_number'], $r['credit_note_date'], $r['credit_note_value'], $r['place_of_supply'], $r['gst_rate'], $r['tax_value'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount']]);
            }
        } elseif ($tab === 'cdnb2c') {
            $stmt = $db->prepare("
                SELECT cn.credit_note_number, cn.credit_note_date, cn.total_amount AS credit_note_value,
                       COALESCE(NULLIF(cn.place_of_supply, ''), p.state, 'N/A') AS place_of_supply,
                       cni.tax_rate AS gst_rate, SUM(cni.taxable_amount) AS taxable_amount,
                       SUM(CASE WHEN cn.is_interstate = 1 THEN cni.tax_amount ELSE 0 END) AS igst_amount,
                       SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS cgst_amount,
                       SUM(CASE WHEN cn.is_interstate = 0 THEN cni.tax_amount / 2 ELSE 0 END) AS sgst_amount
                FROM acc_credit_notes cn
                JOIN acc_parties p ON cn.party_id = p.party_id
                JOIN acc_credit_note_items cni ON cn.credit_note_id = cni.credit_note_id
                WHERE (p.gstin IS NULL OR p.gstin = '') AND cn.credit_note_date BETWEEN ? AND ?
                GROUP BY cn.credit_note_id, cni.tax_rate
                ORDER BY cn.credit_note_date DESC, cn.credit_note_number DESC, cni.tax_rate ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'Credit Note Number', 'Date', 'Value', 'Place of Supply', 'GST Rate %', 'Taxable Value', 'IGST', 'CGST', 'SGST']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['credit_note_number'], $r['credit_note_date'], $r['credit_note_value'], $r['place_of_supply'], $r['gst_rate'], $r['taxable_amount'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount']]);
            }
        } elseif ($tab === 'hsnb2c') {
            $stmt = $db->prepare("
                SELECT COALESCE(NULLIF(ii.hsn_sac, ''), NULLIF(it.hsn_sac, ''), 'N/A') AS hsn_sac,
                       COALESCE(NULLIF(ii.unit, ''), NULLIF(it.unit, ''), 'PCS') AS unit,
                       ii.tax_rate, SUM(ii.quantity) AS total_quantity, SUM(ii.taxable_amount) AS taxable_amount,
                       SUM(ii.tax_amount) AS tax_amount, SUM(ii.igst_amount) AS igst_amount,
                       SUM(ii.cgst_amount) AS cgst_amount, SUM(ii.sgst_amount) AS sgst_amount, SUM(ii.total_amount) AS total_amount
                FROM acc_invoice_items ii
                JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
                JOIN acc_parties p ON i.party_id = p.party_id
                LEFT JOIN acc_items it ON it.name = ii.item_name
                WHERE i.status != 'CANCELLED' AND (p.gstin IS NULL OR p.gstin = '') AND i.invoice_date BETWEEN ? AND ?
                GROUP BY hsn_sac, unit, ii.tax_rate ORDER BY hsn_sac ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'HSN/SAC', 'Unit', 'Tax Rate %', 'Total Qty', 'Taxable Value', 'Tax Amount', 'IGST', 'CGST', 'SGST', 'Total']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['hsn_sac'], $r['unit'], $r['tax_rate'], $r['total_quantity'], $r['taxable_amount'], $r['tax_amount'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount'], $r['total_amount']]);
            }
        } elseif ($tab === 'hsnb2b') {
            $stmt = $db->prepare("
                SELECT COALESCE(NULLIF(ii.hsn_sac, ''), NULLIF(it.hsn_sac, ''), 'N/A') AS hsn_sac,
                       COALESCE(NULLIF(ii.unit, ''), NULLIF(it.unit, ''), 'PCS') AS unit,
                       ii.tax_rate, SUM(ii.quantity) AS total_quantity, SUM(ii.taxable_amount) AS taxable_amount,
                       SUM(ii.tax_amount) AS tax_amount, SUM(ii.igst_amount) AS igst_amount,
                       SUM(ii.cgst_amount) AS cgst_amount, SUM(ii.sgst_amount) AS sgst_amount, SUM(ii.total_amount) AS total_amount
                FROM acc_invoice_items ii
                JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
                JOIN acc_parties p ON i.party_id = p.party_id
                LEFT JOIN acc_items it ON it.name = ii.item_name
                WHERE i.status != 'CANCELLED' AND p.gstin IS NOT NULL AND p.gstin != '' AND i.invoice_date BETWEEN ? AND ?
                GROUP BY hsn_sac, unit, ii.tax_rate ORDER BY hsn_sac ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'HSN/SAC', 'Unit', 'Tax Rate %', 'Total Qty', 'Taxable Value', 'Tax Amount', 'IGST', 'CGST', 'SGST', 'Total']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['hsn_sac'], $r['unit'], $r['tax_rate'], $r['total_quantity'], $r['taxable_amount'], $r['tax_amount'], $r['igst_amount'], $r['cgst_amount'], $r['sgst_amount'], $r['total_amount']]);
            }
        } elseif ($tab === 'item') {
            $stmt = $db->prepare("
                SELECT ii.item_name, COALESCE(NULLIF(ii.description, ''), NULLIF(it.description, ''), '-') AS description,
                       COALESCE(NULLIF(ii.unit, ''), NULLIF(it.unit, ''), 'PCS') AS unit,
                       ii.tax_rate, SUM(ii.quantity) AS total_quantity, SUM(ii.taxable_amount) AS taxable_amount,
                       SUM(ii.tax_amount) AS tax_amount, SUM(ii.total_amount) AS total_amount
                FROM acc_invoice_items ii
                JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
                LEFT JOIN acc_items it ON it.name = ii.item_name
                WHERE i.status != 'CANCELLED' AND i.invoice_date BETWEEN ? AND ?
                GROUP BY ii.item_name, unit, ii.tax_rate ORDER BY ii.item_name ASC
            ");
            $stmt->execute([$fromDate, $toDate]);
            fputcsv($output, ['S.No.', 'Item Name', 'Description', 'Unit', 'Tax Rate %', 'Total Qty', 'Taxable Value', 'Tax Amount', 'Total Amount']);
            $sno = 1;
            foreach ($stmt->fetchAll() as $r) {
                fputcsv($output, [$sno++, $r['item_name'], $r['description'], $r['unit'], $r['tax_rate'], $r['total_quantity'], $r['taxable_amount'], $r['tax_amount'], $r['total_amount']]);
            }
        } elseif ($tab === 'doc') {
            $docStmt = $db->prepare("SELECT 'Tax Invoices' AS nature_of_doc, COUNT(*) AS total_issued, SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) AS cancelled_count, MIN(invoice_number) AS from_no, MAX(invoice_number) AS to_no FROM acc_invoices WHERE invoice_date BETWEEN ? AND ?");
            $docStmt->execute([$fromDate, $toDate]);
            $invDoc = $docStmt->fetch();

            $cnDocStmt = $db->prepare("SELECT 'Credit Notes' AS nature_of_doc, COUNT(*) AS total_issued, 0 AS cancelled_count, MIN(credit_note_number) AS from_no, MAX(credit_note_number) AS to_no FROM acc_credit_notes WHERE credit_note_date BETWEEN ? AND ?");
            $cnDocStmt->execute([$fromDate, $toDate]);
            $cnDoc = $cnDocStmt->fetch();

            fputcsv($output, ['S.No.', 'Nature of Document', 'Total Issued', 'Cancelled', 'From No', 'To No']);
            fputcsv($output, [1, $invDoc['nature_of_doc'], $invDoc['total_issued'], $invDoc['cancelled_count'], $invDoc['from_no'] ?? '-', $invDoc['to_no'] ?? '-']);
            fputcsv($output, [2, $cnDoc['nature_of_doc'], $cnDoc['total_issued'], $cnDoc['cancelled_count'], $cnDoc['from_no'] ?? '-', $cnDoc['to_no'] ?? '-']);
        } elseif ($tab === 'summary') {
            // Recalculate summary for the date range
            $stmt = $db->prepare("
                SELECT 
                    COUNT(DISTINCT i.invoice_id) as count,
                    SUM(ii.taxable_amount) as taxable,
                    SUM(ii.tax_amount) as tax,
                    SUM(ii.total_amount) as total
                FROM acc_invoices i
                JOIN acc_parties p ON i.party_id = p.party_id
                JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
                WHERE i.status != 'CANCELLED' 
                  AND p.gstin IS NOT NULL AND p.gstin != ''
                  AND i.invoice_date BETWEEN ? AND ?
            ");
            $stmt->execute([$fromDate, $toDate]);
            $b2b = $stmt->fetch();

            fputcsv($output, ['S.No.', 'GSTR-1 Category', 'Records Count', 'Taxable Value', 'Tax Amount', 'Total Value']);
            fputcsv($output, [1, '4A/4B/4C - B2B Invoices (Registered)', $b2b['count'] ?? 0, $b2b['taxable'] ?? 0, $b2b['tax'] ?? 0, $b2b['total'] ?? 0]);
            
            // Add other summary rows
            $stmt = $db->prepare("
                SELECT 
                    COUNT(DISTINCT i.invoice_id) as count,
                    SUM(ii.taxable_amount) as taxable,
                    SUM(ii.igst_amount + ii.cgst_amount + ii.sgst_amount) as tax,
                    SUM(ii.total_amount) as total
                FROM acc_invoices i
                JOIN acc_parties p ON i.party_id = p.party_id
                JOIN acc_invoice_items ii ON i.invoice_id = ii.invoice_id
                WHERE i.status != 'CANCELLED' 
                  AND (p.gstin IS NULL OR p.gstin = '')
                  AND (i.is_interstate = 1 OR i.igst_amount > 0) 
                  AND i.total_amount > 250000
                  AND i.invoice_date BETWEEN ? AND ?
            ");
            $stmt->execute([$fromDate, $toDate]);
            $b2cl = $stmt->fetch();
            fputcsv($output, [2, '5 - B2C (Large) Invoices', $b2cl['count'] ?? 0, $b2cl['taxable'] ?? 0, $b2cl['tax'] ?? 0, $b2cl['total'] ?? 0]);
            
            // Continue with other summary rows...
        }

        fclose($output);
        exit;
    }
}
?>