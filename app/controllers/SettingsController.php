<?php
class SettingsController extends Controller {

    public function index() {
        header('Location: ' . APP_URL . '/settings/taxes');
        exit;
    }

    // ==========================================
    // 1. TAXES (Add, Edit, Delete)
    // ==========================================
    public function taxes() {
        $db = (new Model())->getDb();
        $taxes = $db->query("SELECT * FROM acc_taxes WHERE status = 1 ORDER BY tax_rate ASC")->fetchAll();
        $this->view('settings/taxes', ['taxes' => $taxes]);
    }

    public function taxCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tax_rate'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("INSERT INTO acc_taxes (tax_name, tax_rate, igst_rate, cgst_rate, sgst_rate, status) VALUES (?, ?, ?, ?, ?, 1)");
            $rate = (float)$_POST['tax_rate'];
            $halfRate = $rate / 2;
            $stmt->execute([
                trim($_POST['tax_name']),
                $rate,
                $rate,
                $halfRate,
                $halfRate
            ]);
        }
        header('Location: ' . APP_URL . '/settings/taxes');
        exit;
    }

    public function taxEdit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tax_id'])) {
            $db = (new Model())->getDb();
            $rate = (float)$_POST['tax_rate'];
            $halfRate = $rate / 2;
            $stmt = $db->prepare("UPDATE acc_taxes SET tax_name = ?, tax_rate = ?, igst_rate = ?, cgst_rate = ?, sgst_rate = ? WHERE tax_id = ?");
            $stmt->execute([
                trim($_POST['tax_name']),
                $rate,
                $rate,
                $halfRate,
                $halfRate,
                $_POST['tax_id']
            ]);
        }
        header('Location: ' . APP_URL . '/settings/taxes');
        exit;
    }

    public function taxDelete($id = null) {
        if ($id) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_taxes SET status = 0 WHERE tax_id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . APP_URL . '/settings/taxes');
        exit;
    }

    // ==========================================
    // 2. UNITS (With Unit Type - Add, Edit, Delete)
    // ==========================================
    public function units() {
        $db = (new Model())->getDb();
        $units = $db->query("SELECT * FROM acc_units WHERE status = 1 ORDER BY unit_id DESC")->fetchAll();
        $this->view('settings/units', ['units' => $units]);
    }

    public function unitCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['unit_name'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("INSERT INTO acc_units (unit_name, unit_symbol, unit_type, status) VALUES (?, ?, ?, 1)");
            $stmt->execute([
                trim($_POST['unit_name']),
                trim($_POST['unit_symbol'] ?? ''),
                $_POST['unit_type'] ?? 'Quantity'
            ]);
        }
        header('Location: ' . APP_URL . '/settings/units');
        exit;
    }

    public function unitEdit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['unit_id'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_units SET unit_name = ?, unit_symbol = ?, unit_type = ? WHERE unit_id = ?");
            $stmt->execute([
                trim($_POST['unit_name']),
                trim($_POST['unit_symbol']),
                $_POST['unit_type'],
                $_POST['unit_id']
            ]);
        }
        header('Location: ' . APP_URL . '/settings/units');
        exit;
    }

    public function unitDelete($id = null) {
        if ($id) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_units SET status = 0 WHERE unit_id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . APP_URL . '/settings/units');
        exit;
    }

    // ==========================================
    // 3. CATEGORIES (Add, Edit, Delete)
    // ==========================================
    public function categories() {
        $db = (new Model())->getDb();
        $categories = $db->query("SELECT * FROM acc_categories WHERE status = 1 ORDER BY category_id DESC")->fetchAll();
        $this->view('settings/categories', ['categories' => $categories]);
    }

    public function categoryCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['category_name'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("INSERT INTO acc_categories (category_name, description, status) VALUES (?, ?, 1)");
            $stmt->execute([
                trim($_POST['category_name']),
                trim($_POST['description'] ?? '')
            ]);
        }
        header('Location: ' . APP_URL . '/settings/categories');
        exit;
    }

    public function categoryEdit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['category_id'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_categories SET category_name = ?, description = ? WHERE category_id = ?");
            $stmt->execute([
                trim($_POST['category_name']),
                trim($_POST['description'] ?? ''),
                $_POST['category_id']
            ]);
        }
        header('Location: ' . APP_URL . '/settings/categories');
        exit;
    }

    public function categoryDelete($id = null) {
        if ($id) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_categories SET status = 0 WHERE category_id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . APP_URL . '/settings/categories');
        exit;
    }

    // ==========================================
    // 4. CURRENCIES (With Multipliers - Add, Edit, Delete)
    // ==========================================
    public function currencies() {
        $db = (new Model())->getDb();
        $currencies = $db->query("SELECT * FROM acc_currencies WHERE status = 1 ORDER BY is_default DESC, currency_code ASC")->fetchAll();
        $this->view('settings/currencies', ['currencies' => $currencies]);
    }

    public function currencyCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['currency_code'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("INSERT INTO acc_currencies (currency_code, currency_name, symbol, exchange_rate, is_default, status) VALUES (?, ?, ?, ?, 0, 1)");
            $stmt->execute([
                strtoupper(trim($_POST['currency_code'])),
                trim($_POST['currency_name']),
                trim($_POST['symbol']),
                (float)($_POST['exchange_rate'] ?? 1.0000)
            ]);
        }
        header('Location: ' . APP_URL . '/settings/currencies');
        exit;
    }

    public function currencyEdit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['currency_id'])) {
            $db = (new Model())->getDb();
            $stmt = $db->prepare("UPDATE acc_currencies SET currency_code = ?, currency_name = ?, symbol = ?, exchange_rate = ? WHERE currency_id = ?");
            $stmt->execute([
                strtoupper(trim($_POST['currency_code'])),
                trim($_POST['currency_name']),
                trim($_POST['symbol']),
                (float)$_POST['exchange_rate'],
                $_POST['currency_id']
            ]);
        }
        header('Location: ' . APP_URL . '/settings/currencies');
        exit;
    }

    public function currencyDelete($id = null) {
        if ($id) {
            $db = (new Model())->getDb();
            // Prevent deleting the default base currency
            $stmt = $db->prepare("UPDATE acc_currencies SET status = 0 WHERE currency_id = ? AND is_default != 1");
            $stmt->execute([$id]);
        }
        header('Location: ' . APP_URL . '/settings/currencies');
        exit;
    }
    // ==========================================
    // 5. CUSTOMIZATION & GENERAL SETTINGS
    // ==========================================
    public function customization() {
        $db = (new Model())->getDb();

        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $_POST['settings'] ?? [];

            // 1. Process Logo Upload
            if (!empty($_FILES['company_logo']['name'])) {
                $uploadDir = '../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                    $logoName = 'company_logo_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadDir . $logoName)) {
                        $settings['company_logo'] = $logoName;
                    }
                }
            }

            // 2. Process Digital Signature Upload
            if (!empty($_FILES['digital_signature']['name'])) {
                $uploadDir = '../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['digital_signature']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $sigName = 'signature_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['digital_signature']['tmp_name'], $uploadDir . $sigName)) {
                        $settings['digital_signature'] = $sigName;
                    }
                }
            }

            // 3. Save all key-value settings to database
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO acc_settings (setting_key, setting_value) 
                                      VALUES (?, ?) 
                                      ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }

            // Keep acc_currencies in sync with the selected default currency
            if (!empty($settings['default_currency'])) {
                $db->prepare("UPDATE acc_currencies SET is_default = 0")->execute();
                $upd = $db->prepare("UPDATE acc_currencies SET is_default = 1 WHERE currency_code = ?");
                $upd->execute([$settings['default_currency']]);
            }

            header('Location: ' . APP_URL . '/settings/customization?saved=1');
            exit;
        }

        // Fetch settings from DB
        $rows = $db->query("SELECT setting_key, setting_value FROM acc_settings")->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        $currencies = $db->query("SELECT * FROM acc_currencies WHERE status = 1 ORDER BY is_default DESC, currency_code ASC")->fetchAll();

        $this->view('settings/customization', ['settings' => $settings, 'currencies' => $currencies]);
    }
}