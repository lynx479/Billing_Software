<?php
class Party extends Model {
    public function getAllParties() {
        return $this->getDb()->query("SELECT * FROM acc_parties WHERE (status = 1 OR status IS NULL) ORDER BY party_id DESC")->fetchAll();
    }

    public function getPartiesByType($type) {
        $stmt = $this->getDb()->prepare("SELECT * FROM acc_parties WHERE party_type = ? AND (status = 1 OR status IS NULL) ORDER BY party_id DESC");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function getCreatedParties() {
        return $this->getDb()->query("SELECT * FROM acc_parties 
            WHERE party_type IN ('CUSTOMER', 'VENDOR') 
            AND (status = 1 OR status IS NULL) 
            ORDER BY party_id DESC")->fetchAll();
    }

    public function getPartyById($id) {
        $stmt = $this->getDb()->prepare("SELECT * FROM acc_parties WHERE party_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getStates() {
        return $this->getDb()->query("SELECT * FROM acc_states ORDER BY state_code ASC")->fetchAll();
    }

    public function searchCreatedParties($query) {
        $term = '%' . trim($query) . '%';
        $stmt = $this->getDb()->prepare("SELECT * FROM acc_parties 
            WHERE party_type IN ('CUSTOMER', 'VENDOR') 
            AND (status = 1 OR status IS NULL) 
            AND (name LIKE ? OR business_name LIKE ? OR phone LIKE ? OR gstin LIKE ? OR pan LIKE ?) 
            ORDER BY party_id DESC LIMIT 15");
        $stmt->execute([$term, $term, $term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function createParty($data) {
        $shipping = !empty($data['same_as_billing']) ? $data['address'] : ($data['shipping_address'] ?? $data['address']);
        
        $stmt = $this->getDb()->prepare("INSERT INTO acc_parties 
            (party_type, name, business_name, email, phone, city, state, pincode, gstin, pan, address, shipping_address, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        return $stmt->execute([
            $data['party_type'],
            $data['name'],
            $data['business_name'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['city'] ?? null,
            $data['state'],
            $data['pincode'] ?? null,
            $data['gstin'] ?? null,
            $data['pan'] ?? null,
            $data['address'] ?? null,
            $shipping
        ]);
    }

    public function updateParty($id, $data) {
        $shipping = !empty($data['same_as_billing']) ? $data['address'] : ($data['shipping_address'] ?? $data['address']);
        
        $stmt = $this->getDb()->prepare("UPDATE acc_parties SET 
            party_type = ?, name = ?, business_name = ?, email = ?, phone = ?, 
            city = ?, state = ?, pincode = ?, gstin = ?, pan = ?, 
            address = ?, shipping_address = ? 
            WHERE party_id = ?");
        return $stmt->execute([
            $data['party_type'],
            $data['name'],
            $data['business_name'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['city'] ?? null,
            $data['state'],
            $data['pincode'] ?? null,
            $data['gstin'] ?? null,
            $data['pan'] ?? null,
            $data['address'] ?? null,
            $shipping,
            $id
        ]);
    }

    public function deleteParty($id) {
        $stmt = $this->getDb()->prepare("UPDATE acc_parties SET status = 0 WHERE party_id = ? AND party_type IN ('CUSTOMER', 'VENDOR')");
        return $stmt->execute([$id]);
    }

    public function getAllMarketplaceSellers() {
        $db = $this->getDb();
        $sql = "SELECT 
                    s.seller_id,
                    s.store_name,
                    s.owner_name,
                    s.gstin,
                    s.state,
                    COALESCE(SUM(o.gross_amount), 0) as total_amount
                FROM mock_sellers s
                LEFT JOIN mock_orders o ON s.seller_id = o.seller_id AND o.order_status = 'Completed'
                GROUP BY s.seller_id
                ORDER BY s.store_name ASC";
        return $db->query($sql)->fetchAll();
    }

    public function searchMarketplaceSellers($query) {
        $db = $this->getDb();
        $cleanQuery = trim($query);
        $prefixTerm = $cleanQuery . '%';          // Starts with (e.g. 'Rah%')
        $wildcardTerm = '% ' . $cleanQuery . '%';  // Starts with any word (e.g. '% Sharma%')

        $sql = "SELECT 
                    s.seller_id,
                    s.store_name,
                    s.owner_name,
                    s.gstin,
                    s.state,
                    COALESCE(SUM(o.gross_amount), 0) as total_amount
                FROM mock_sellers s
                LEFT JOIN mock_orders o ON s.seller_id = o.seller_id AND o.order_status = 'Completed'
                WHERE s.store_name LIKE ? 
                   OR s.store_name LIKE ?
                   OR s.owner_name LIKE ? 
                   OR s.owner_name LIKE ?
                   OR s.gstin LIKE ?
                GROUP BY s.seller_id
                ORDER BY 
                    CASE 
                        WHEN s.owner_name LIKE ? THEN 1
                        WHEN s.store_name LIKE ? THEN 2
                        ELSE 3
                    END,
                    s.store_name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $prefixTerm, $wildcardTerm,
            $prefixTerm, $wildcardTerm,
            $prefixTerm,
            $prefixTerm, $prefixTerm
        ]);
        return $stmt->fetchAll();
    }

    public function getSellerById($sellerId) {
        $stmt = $this->getDb()->prepare("SELECT * FROM mock_sellers WHERE seller_id = ?");
        $stmt->execute([$sellerId]);
        return $stmt->fetch();
    }

    public function getSellerTransactions($sellerId) {
        $sql = "SELECT 'Order Sale' AS tx_type, order_number AS ref_no, order_date AS tx_date, gross_amount AS total_amount, 0.00 AS balance_unused, order_status AS tx_status FROM mock_orders WHERE seller_id = ?
                UNION ALL
                SELECT 'Settlement' AS tx_type, settlement_reference AS ref_no, period_end AS tx_date, net_payable AS total_amount, 0.00 AS balance_unused, status AS tx_status FROM acc_settlements WHERE seller_id = ?
                ORDER BY tx_date DESC";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$sellerId, $sellerId]);
        return $stmt->fetchAll();
    }
    // 1. Fetch created parties with total invoiced/billed amount
    public function getCreatedPartiesWithTotals() {
        $db = $this->getDb();
        $sql = "SELECT 
                    p.*,
                    COALESCE(SUM(i.total_amount), 0) AS total_amount
                FROM acc_parties p
                LEFT JOIN acc_invoices i ON p.party_id = i.party_id AND i.status != 'CANCELLED'
                WHERE p.party_type IN ('CUSTOMER', 'VENDOR') 
                  AND (p.status = 1 OR p.status IS NULL)
                GROUP BY p.party_id
                ORDER BY p.name ASC";
        return $db->query($sql)->fetchAll();
    }

    // 2. Search created parties with prefix word matching
    public function searchCreatedPartiesAdvanced($query) {
        $db = $this->getDb();
        $cleanQuery = trim($query);
        $prefix = $cleanQuery . '%';
        $wordPrefix = '% ' . $cleanQuery . '%';

        $sql = "SELECT 
                    p.*,
                    COALESCE(SUM(i.total_amount), 0) AS total_amount
                FROM acc_parties p
                LEFT JOIN acc_invoices i ON p.party_id = i.party_id AND i.status != 'CANCELLED'
                WHERE p.party_type IN ('CUSTOMER', 'VENDOR') 
                  AND (p.status = 1 OR p.status IS NULL)
                  AND (
                      p.name LIKE ? OR p.name LIKE ?
                      OR p.business_name LIKE ? OR p.business_name LIKE ?
                      OR p.phone LIKE ? OR p.gstin LIKE ? OR p.pan LIKE ?
                  )
                GROUP BY p.party_id
                ORDER BY 
                    CASE 
                        WHEN p.name LIKE ? THEN 1
                        WHEN p.business_name LIKE ? THEN 2
                        ELSE 3
                    END,
                    p.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $prefix, $wordPrefix,
            $prefix, $wordPrefix,
            $prefix, $prefix, $prefix,
            $prefix, $prefix
        ]);
        return $stmt->fetchAll();
    }

    // 3. Fetch transaction rows (invoices & payments) for a created party
    public function getPartyTransactions($partyId) {
        $db = $this->getDb();
        $sql = "SELECT 
                    'Tax Invoice' AS tx_type,
                    invoice_number AS ref_no,
                    invoice_date AS tx_date,
                    total_amount AS total_amount,
                    (total_amount - paid_amount) AS balance_unused,
                    status AS tx_status
                FROM acc_invoices
                WHERE party_id = ?
                
                UNION ALL
                
                SELECT 
                    CASE WHEN payment_type = 'PAY_IN' THEN 'Pay In (Received)' ELSE 'Pay Out (Paid)' END AS tx_type,
                    COALESCE(reference_number, CONCAT('PAY-', payment_id)) AS ref_no,
                    payment_date AS tx_date,
                    amount AS total_amount,
                    0.00 AS balance_unused,
                    'Completed' AS tx_status
                FROM acc_payments
                WHERE party_id = ?
                
                ORDER BY tx_date DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$partyId, $partyId]);
        return $stmt->fetchAll();
    }
    // Cascade-delete party and all associated accounting records
    public function deletePartyWithTransactions($partyId) {
        $db = $this->getDb();
        $db->beginTransaction();

        try {
            // 1. Delete invoice items linked to this party's invoices
            $stmt = $db->prepare("DELETE ii FROM acc_invoice_items ii
                                  INNER JOIN acc_invoices i ON ii.invoice_id = i.invoice_id
                                  WHERE i.party_id = ?");
            $stmt->execute([$partyId]);

            // 2. Delete credit notes
            $stmt = $db->prepare("DELETE FROM acc_credit_notes WHERE party_id = ?");
            $stmt->execute([$partyId]);

            // 3. Delete invoices
            $stmt = $db->prepare("DELETE FROM acc_invoices WHERE party_id = ?");
            $stmt->execute([$partyId]);

            // 4. Delete payments (in & out)
            $stmt = $db->prepare("DELETE FROM acc_payments WHERE party_id = ?");
            $stmt->execute([$partyId]);

            // 5. Delete the party record
            $stmt = $db->prepare("DELETE FROM acc_parties WHERE party_id = ? AND party_type IN ('CUSTOMER', 'VENDOR')");
            $stmt->execute([$partyId]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}