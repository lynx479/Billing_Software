<?php
class Settlement extends Model {
    public function generateSettlementForSeller($sellerId, $startDate, $endDate) {
        $db = $this->getDb();

        // 1. Fetch Gross Sales from Orders
        $stmt = $db->prepare("SELECT COALESCE(SUM(gross_amount), 0) as gross 
            FROM mock_orders 
            WHERE seller_id = ? AND order_status = 'Completed' 
            AND order_date BETWEEN ? AND ?");
        $stmt->execute([$sellerId, $startDate, $endDate]);
        $grossSales = (float)$stmt->fetchColumn();

        if ($grossSales <= 0) {
            return false;
        }

        // 2. Fetch Seller Commission
        $sellerStmt = $db->prepare("SELECT commission_rate FROM mock_sellers WHERE seller_id = ?");
        $sellerStmt->execute([$sellerId]);
        $commRate = (float)$sellerStmt->fetchColumn();

        $commissionAmount = ($grossSales * $commRate) / 100;
        $taxOnCommission = ($commissionAmount * 18) / 100; // 18% GST on marketplace fee
        $netPayable = $grossSales - ($commissionAmount + $taxOnCommission);

        $ref = 'SETTL-' . $sellerId . '-' . date('YmdHis');

        $ins = $db->prepare("INSERT INTO acc_settlements 
            (settlement_reference, seller_id, period_start, period_end, gross_sales, commission_amount, tax_on_commission, net_payable, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')");
        
        return $ins->execute([$ref, $sellerId, $startDate, $endDate, $grossSales, $commissionAmount, $taxOnCommission, $netPayable]);
    }

    public function getAllSettlements() {
        return $this->getDb()->query("SELECT st.*, s.store_name, s.owner_name 
            FROM acc_settlements st 
            JOIN mock_sellers s ON st.seller_id = s.seller_id 
            ORDER BY st.settlement_id DESC")->fetchAll();
    }
}