<?php
class Report extends Model {
    public function getDashboardSummary() {
        $db = $this->getDb();

        $invStmt = $db->query("SELECT 
            COALESCE(SUM(total_amount), 0) as total_sales,
            COALESCE(SUM(paid_amount), 0) as total_received,
            COALESCE(SUM(tax_amount), 0) as total_tax
            FROM acc_invoices WHERE status != 'CANCELLED'");
        $invData = $invStmt->fetch();

        $settleStmt = $db->query("SELECT 
            COALESCE(SUM(net_payable), 0) as pending_settlements 
            FROM acc_settlements WHERE status = 'PENDING'");
        $settleData = $settleStmt->fetch();

        $commStmt = $db->query("SELECT 
            COALESCE(SUM(commission_amount), 0) as total_commission 
            FROM acc_settlements");
        $commData = $commStmt->fetch();

                return [
            'total_sales' => $invData['total_sales'],
            'total_tax' => $invData['total_tax'],
            'outstanding' => ($invData['total_sales'] - $invData['total_received']),
            'pending_settlements' => $settleData['pending_settlements'],
            'total_commission' => $commData['total_commission']
        ];
    }

    /**
     * Data for the Dashboard analytics charts: last 6 months of
     * Invoices/GSTR1-tax trend and Pay-In vs Pay-Out cashflow.
     * Returned as parallel-indexed arrays ready to json_encode() straight
     * into ApexCharts (already loaded app-wide via footer.php).
     */
       public function getDashboardCharts() {
        $db = $this->getDb();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-{$i} months"));
        }
        $monthLabels = array_map(function ($m) { return date('M Y', strtotime($m . '-01')); }, $months);

        // 1. Invoices Issued (Total Billed) + Tax Collected + Pending/Due Invoices
        $invStmt = $db->query("
            SELECT DATE_FORMAT(invoice_date, '%Y-%m') AS ym,
                   COUNT(*) AS invoice_count,
                   COALESCE(SUM(total_amount), 0) AS total_amount,
                   COALESCE(SUM(tax_amount), 0) AS tax_amount,
                   COALESCE(SUM(CASE WHEN status != 'CANCELLED' AND status != 'PAID' THEN (total_amount - COALESCE(paid_amount, 0)) ELSE 0 END), 0) AS pending_amount
            FROM acc_invoices
            WHERE status != 'CANCELLED' AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY ym
        ");
        $invByMonth = [];
        foreach ($invStmt->fetchAll() as $row) { 
            $invByMonth[$row['ym']] = $row; 
        }

        // 2. Pay-In vs Pay-Out, per month
        $payStmt = $db->query("
            SELECT DATE_FORMAT(payment_date, '%Y-%m') AS ym,
                   payment_type,
                   COALESCE(SUM(amount), 0) AS total_amount
                FROM acc_payments
            WHERE status != 'CANCELLED' AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY ym, payment_type
        ");
        $payInByMonth = [];
        $payOutByMonth = [];
        foreach ($payStmt->fetchAll() as $row) {
            if ($row['payment_type'] === 'PAY_IN') {
                $payInByMonth[$row['ym']] = (float)$row['total_amount'];
            } else {
                $payOutByMonth[$row['ym']] = (float)$row['total_amount'];
            }
        }

        // 3. Unsettled Credit Notes (OPEN and PARTIALLY_ADJUSTED remaining balance)
        $cnStmt = $db->query("
            SELECT DATE_FORMAT(credit_note_date, '%Y-%m') AS ym,
                   COALESCE(SUM(total_amount - COALESCE(adjusted_amount, 0)), 0) AS unsettled_amount
            FROM acc_credit_notes
            WHERE status IN ('OPEN', 'PARTIALLY_ADJUSTED') 
              AND credit_note_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY ym
        ");
        $cnByMonth = [];
        foreach ($cnStmt->fetchAll() as $row) {
            $cnByMonth[$row['ym']] = (float)$row['unsettled_amount'];
        }

        $invoiceCounts   = [];
        $invoiceTotals   = [];
        $pendingInvoices = [];
        $taxCollected    = [];
        $payIns          = [];
        $payOuts         = [];
        $creditUnsettled = [];

        foreach ($months as $ym) {
            $invoiceCounts[]   = (int)($invByMonth[$ym]['invoice_count'] ?? 0);
            $invoiceTotals[]   = round((float)($invByMonth[$ym]['total_amount'] ?? 0), 2);
            $pendingInvoices[] = round((float)($invByMonth[$ym]['pending_amount'] ?? 0), 2);
            $taxCollected[]    = round((float)($invByMonth[$ym]['tax_amount'] ?? 0), 2);
            $payIns[]          = round((float)($payInByMonth[$ym] ?? 0), 2);
            $payOuts[]         = round((float)($payOutByMonth[$ym] ?? 0), 2);
            $creditUnsettled[] = round((float)($cnByMonth[$ym] ?? 0), 2);
        }

        return [
            'labels'           => $monthLabels,
            'invoice_counts'   => $invoiceCounts,
            'invoice_totals'   => $invoiceTotals,
            'pending_invoices' => $pendingInvoices,
            'tax_collected'    => $taxCollected,
            'pay_ins'          => $payIns,
            'pay_outs'         => $payOuts,
            'credit_unsettled' => $creditUnsettled,
        ];
    }
}