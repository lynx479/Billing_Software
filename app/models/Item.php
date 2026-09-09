<?php
class Item extends Model {
    public function getAll() {
        return $this->getDb()->query("SELECT * FROM acc_items ORDER BY item_id DESC")->fetchAll();
    }

    public function create($data) {
        $stmt = $this->getDb()->prepare("INSERT INTO acc_items (name, sku, hsn_sac, unit, price, tax_rate) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['sku'], $data['hsn_sac'], $data['unit'], $data['price'], $data['tax_rate']]);
    }
}