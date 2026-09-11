<?php
class ItemsController extends Controller {

    public function index() {
        $db = (new Model())->getDb();

       $sql = "SELECT i.*, 
                       c.category_name,
                   u.unit_name,
                   MAX(t.tax_name) AS tax_name,
                   (
                       SELECT COUNT(*)
                       FROM acc_invoice_items it
                       JOIN acc_invoices inv ON it.invoice_id = inv.invoice_id
                       WHERE (it.item_name = i.name OR it.item_name = i.sku)
                         AND inv.status != 'CANCELLED'
                   ) AS invoice_refs,
                   (
                       SELECT COUNT(*)
                       FROM acc_credit_note_items cni
                       JOIN acc_credit_notes cn ON cni.credit_note_id = cn.credit_note_id
                       WHERE (cni.item_name = i.name OR cni.item_name = i.sku)
                         AND cn.status != 'CANCELLED'
                   ) AS credit_refs
            FROM acc_items i
            LEFT JOIN acc_categories c ON i.category_id = c.category_id
            LEFT JOIN acc_units u ON i.unit = u.unit_symbol
            LEFT JOIN acc_taxes t ON i.tax_rate = t.tax_rate
            WHERE i.status = 1
            GROUP BY i.item_id
            ORDER BY i.name ASC";

        $items = $db->query($sql)->fetchAll();
        $categories = $db->query("SELECT * FROM acc_categories WHERE status = 1 ORDER BY category_name ASC")->fetchAll();
        $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_name ASC")->fetchAll();
        $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();

        $this->view('items/index', [
            'items' => $items,
            'categories' => $categories,
            'units' => $units,
            'taxes' => $taxes
        ]);
    }

    // Handle Item Creation
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
            $db = (new Model())->getDb();

            $itemType = $_POST['item_type'] ?? 'PRODUCT';
            $openingStock = ($itemType === 'PRODUCT') ? (float)($_POST['opening_stock'] ?? 0) : 0.00;
            $currentStock = $openingStock;

            $stmt = $db->prepare("INSERT INTO acc_items 
                (name, description, category_id, item_type, sku, hsn_sac, unit, price, tax_rate, opening_stock, current_stock, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['description'] ?? ''),
                !empty($_POST['category_id']) ? $_POST['category_id'] : null,
                $itemType,
                trim($_POST['sku'] ?? ''),
                trim($_POST['hsn_sac'] ?? ''),
                $_POST['unit'] ?? 'PCS',
                (float)($_POST['price'] ?? 0),
                (float)($_POST['tax_rate'] ?? 18.00),
                $openingStock,
                $currentStock
            ]);
        }
        header('Location: ' . APP_URL . '/items');
        exit;
    }

    // Handle Item Edit
    // Handle Item Edit (Including opening stock & recalculated stock)
    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
            $db = (new Model())->getDb();

            $itemType = $_POST['item_type'] ?? 'PRODUCT';
            $openingStock = ($itemType === 'PRODUCT') ? (float)($_POST['opening_stock'] ?? 0) : 0.00;

            $stmt = $db->prepare("UPDATE acc_items SET 
                name = ?, 
                description = ?, 
                category_id = ?, 
                item_type = ?, 
                sku = ?, 
                hsn_sac = ?, 
                unit = ?, 
                price = ?, 
                tax_rate = ?,
                opening_stock = ?
                WHERE item_id = ?");

            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['description'] ?? ''),
                !empty($_POST['category_id']) ? $_POST['category_id'] : null,
                $itemType,
                trim($_POST['sku'] ?? ''),
                trim($_POST['hsn_sac'] ?? ''),
                $_POST['unit'] ?? 'PCS',
                (float)$_POST['price'],
                (float)$_POST['tax_rate'],
                $openingStock,
                $_POST['item_id']
            ]);
        }
        header('Location: ' . APP_URL . '/items');
        exit;
    }

    // Dedicated Product Movement History Page
    public function history($itemId = null) {
        if (!$itemId) {
            header('Location: ' . APP_URL . '/items');
            exit;
        }

        $db = (new Model())->getDb();

        // 1. Fetch Item Master Record
        $stmtItem = $db->prepare("SELECT i.*, c.category_name 
                                  FROM acc_items i 
                                  LEFT JOIN acc_categories c ON i.category_id = c.category_id 
                                  WHERE i.item_id = ?");
        $stmtItem->execute([$itemId]);
        $item = $stmtItem->fetch();

        if (!$item) {
            header('Location: ' . APP_URL . '/items');
            exit;
        }

        // 2. Fetch Sales (Out Qty) and Credit Notes / Returns (In Qty) with Seller Name & Company Name
        $sql = "
            SELECT 
                'Sale' AS trans_type,
                inv.invoice_number AS doc_number,
                p.name AS party_name,
                p.business_name AS company_name,
                inv.invoice_date AS trans_date,
                0.00 AS in_qty,
                it.quantity AS out_qty,
                it.unit_price AS price,
                inv.status AS doc_status
            FROM acc_invoice_items it
            JOIN acc_invoices inv ON it.invoice_id = inv.invoice_id
            LEFT JOIN acc_parties p ON inv.party_id = p.party_id
            WHERE (it.item_name = ? OR it.item_name = ?)
              AND inv.status != 'CANCELLED'

            UNION ALL

            SELECT 
                'Cr. Note' AS trans_type,
                cn.credit_note_number AS doc_number,
                p.name AS party_name,
                p.business_name AS company_name,
                cn.credit_note_date AS trans_date,
                cni.quantity AS in_qty,
                0.00 AS out_qty,
                cni.unit_price AS price,
                cn.status AS doc_status
            FROM acc_credit_note_items cni
            JOIN acc_credit_notes cn ON cni.credit_note_id = cn.credit_note_id
            LEFT JOIN acc_parties p ON cn.party_id = p.party_id
            WHERE (cni.item_name = ? OR cni.item_name = ?)

            ORDER BY trans_date ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$item['name'], $item['sku'], $item['name'], $item['sku']]);
        $movements = $stmt->fetchAll();

        $this->view('items/history', [
            'item' => $item,
            'movements' => $movements
        ]);
    }

    // Handle Item Deletion — blocked if the item is referenced by any transaction
public function delete($id = null) {
    if (!$id) {
        header('Location: ' . APP_URL . '/items');
        exit;
    }

    $db = (new Model())->getDb();

    // 1. Fetch the item's name + sku (needed to match against invoice / credit-note lines)
    $stmt = $db->prepare("SELECT name, sku FROM acc_items WHERE item_id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
        header('Location: ' . APP_URL . '/items');
        exit;
    }

    $name = $item['name'];
    $sku  = $item['sku'];

    // 2. Count active sales (invoice line items)
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM acc_invoice_items it
        JOIN acc_invoices inv ON it.invoice_id = inv.invoice_id
        WHERE (it.item_name = ? OR it.item_name = ?)
          AND inv.status != 'CANCELLED'
    ");
    $stmt->execute([$name, $sku]);
    $invoiceRefs = (int)$stmt->fetchColumn();

    // 3. Count credit notes / returns
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM acc_credit_note_items cni
        JOIN acc_credit_notes cn ON cni.credit_note_id = cn.credit_note_id
        WHERE (cni.item_name = ? OR cni.item_name = ?)
          AND cn.status != 'CANCELLED'
    ");
    $stmt->execute([$name, $sku]);
    $creditRefs = (int)$stmt->fetchColumn();

    $totalRefs = $invoiceRefs + $creditRefs;

    // 4. Refuse if any references exist
    if ($totalRefs > 0) {
        $_SESSION['flash_error'] =
            "Cannot delete \"{$name}\" — it is referenced by {$invoiceRefs} invoice line(s) " .
            "and {$creditRefs} credit note line(s). " .
            "Cancel or remove those documents first.";
        header('Location: ' . APP_URL . '/items');
        exit;
    }

    // 5. Safe to soft-delete
    $stmt = $db->prepare("UPDATE acc_items SET status = 0 WHERE item_id = ?");
    $stmt->execute([$id]);

    $_SESSION['flash_success'] = "Item \"{$name}\" deleted successfully.";
    header('Location: ' . APP_URL . '/items');
    exit;
}

    // AJAX Endpoint: Fetch 8-column Transaction History for a specific product
    public function getItemTransactionsAjax($itemId = null) {
        if (!$itemId) {
            $this->json(['status' => false, 'transactions' => []]);
            return;
        }

        $db = (new Model())->getDb();

        // 1. Get Item details
        $stmtItem = $db->prepare("SELECT * FROM acc_items WHERE item_id = ?");
        $stmtItem->execute([$itemId]);
        $item = $stmtItem->fetch();

        if (!$item) {
            $this->json(['status' => false, 'transactions' => []]);
            return;
        }

        // 2. Fetch Sales from Invoices and Returns from Credit Notes
        $sql = "
            SELECT 
                'Sale' AS trans_type,
                inv.invoice_number AS doc_number,
                COALESCE(p.name, 'Walk-in Customer') AS party_name,
                inv.invoice_date AS trans_date,
                it.quantity AS qty,
                it.unit_price AS price,
                inv.status AS doc_status
            FROM acc_invoice_items it
            JOIN acc_invoices inv ON it.invoice_id = inv.invoice_id
            LEFT JOIN acc_parties p ON inv.party_id = p.party_id
            WHERE (it.item_name = ? OR it.item_name = ?)
              AND inv.status != 'CANCELLED'

            UNION ALL

            SELECT 
                'Cr. Note' AS trans_type,
                cn.credit_note_number AS doc_number,
                COALESCE(p.name, 'Customer') AS party_name,
                cn.credit_note_date AS trans_date,
                cni.quantity AS qty,
                cni.unit_price AS price,
                cn.status AS doc_status
            FROM acc_credit_note_items cni
            JOIN acc_credit_notes cn ON cni.credit_note_id = cn.credit_note_id
            LEFT JOIN acc_parties p ON cn.party_id = p.party_id
            WHERE (cni.item_name = ? OR cni.item_name = ?)

            ORDER BY trans_date DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$item['name'], $item['sku'], $item['name'], $item['sku']]);
        $transactions = $stmt->fetchAll();

        $this->json([
            'status' => true,
            'item' => $item,
            'transactions' => $transactions
        ]);
    }
}