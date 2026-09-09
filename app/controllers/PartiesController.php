<?php
class PartiesController extends Controller {
    public function index() {
        $this->created();
    }

    public function sellers() {
        $partyModel = $this->model('Party');
        // Fetch all platform sellers so the page is populated on initial load
        $sellers = $partyModel->getAllMarketplaceSellers();
        $this->view('parties/sellers', ['sellers' => $sellers]);
    }

    public function searchSellersAjax() {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        $partyModel = $this->model('Party');

        if ($query === '') {
            $results = $partyModel->getAllMarketplaceSellers();
        } else {
            $results = $partyModel->searchMarketplaceSellers($query);
        }

        $this->json($results);
    }

    public function sellerView($sellerId = null) {
        if (!$sellerId) {
            header('Location: ' . APP_URL . '/parties/sellers');
            exit;
        }
        $partyModel = $this->model('Party');
        $seller = $partyModel->getSellerById($sellerId);
        $transactions = $partyModel->getSellerTransactions($sellerId);

        $this->view('parties/seller-statement', [
            'seller' => $seller,
            'transactions' => $transactions
        ]);
    }

    public function created() {
        $db = (new Model())->getDb();

        // Added party_type filter to exclude SELLER parties
        $sql = "
            SELECT 
                p.*,
                (
                    COALESCE((SELECT SUM(total_amount) FROM acc_invoices WHERE party_id = p.party_id AND status != 'CANCELLED'), 0)
                    -
                    COALESCE((SELECT SUM(amount) FROM acc_payments WHERE party_id = p.party_id AND payment_type = 'PAY_IN' AND (status != 'CANCELLED' OR status IS NULL)), 0)
                    -
                    COALESCE((SELECT SUM(total_amount) FROM acc_credit_notes WHERE party_id = p.party_id AND status != 'CANCELLED'), 0)
                    +
                    COALESCE((SELECT SUM(amount) FROM acc_payments WHERE party_id = p.party_id AND payment_type = 'PAY_OUT' AND (status != 'CANCELLED' OR status IS NULL)), 0)
                ) AS net_balance
            FROM acc_parties p
            WHERE p.status = 1 
              AND p.party_type != 'SELLER'
            ORDER BY p.name ASC
        ";

        $parties = $db->query($sql)->fetchAll();
        $this->view('parties/created', ['parties' => $parties]);
    }

    public function searchCreatedAjax() {
        $q = trim($_GET['q'] ?? '');
        $db = (new Model())->getDb();

        $sql = "
            SELECT 
                p.*,
                (
                    COALESCE((SELECT SUM(total_amount) FROM acc_invoices WHERE party_id = p.party_id AND status != 'CANCELLED'), 0)
                    -
                    COALESCE((SELECT SUM(amount) FROM acc_payments WHERE party_id = p.party_id AND payment_type = 'PAY_IN' AND (status != 'CANCELLED' OR status IS NULL)), 0)
                    -
                    COALESCE((SELECT SUM(total_amount) FROM acc_credit_notes WHERE party_id = p.party_id AND status != 'CANCELLED'), 0)
                    +
                    COALESCE((SELECT SUM(amount) FROM acc_payments WHERE party_id = p.party_id AND payment_type = 'PAY_OUT' AND (status != 'CANCELLED' OR status IS NULL)), 0)
                ) AS net_balance
            FROM acc_parties p
            WHERE p.status = 1 
              AND p.party_type != 'SELLER'
        ";

        $params = [];
        if (!empty($q)) {
            $sql .= " AND (p.name LIKE ? OR p.business_name LIKE ? OR p.phone LIKE ? OR p.gstin LIKE ? OR p.pan LIKE ?)";
            $term = '%' . $q . '%';
            $params = [$term, $term, $term, $term, $term];
        }

        $sql .= " ORDER BY p.name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $parties = $stmt->fetchAll();

        $this->json($parties);
    }

   public function partyView($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/parties/created');
            exit;
        }

        $db = (new Model())->getDb();
        $stmt = $db->prepare("SELECT * FROM acc_parties WHERE party_id = ?");
        $stmt->execute([$id]);
        $party = $stmt->fetch();

        if (! $party) {
            die("Party not found.");
        }

        // Separate datasets so each document type gets its own clearly readable table
        $invoices    = [];
        $creditNotes = [];
        $payIns      = [];
        $payOuts     = [];

        $totals = [
            'invoiced'     => 0.0,
            'invoice_due'  => 0.0,
            'credit'       => 0.0,
            'credit_open'  => 0.0,
            'paid_in'      => 0.0,
            'paid_out'     => 0.0,
        ];

        // 1. Tax Invoices
        $invStmt = $db->prepare("SELECT * FROM acc_invoices WHERE party_id = ? AND status != 'CANCELLED' ORDER BY invoice_date DESC, invoice_id DESC");
        $invStmt->execute([$id]);
        foreach ($invStmt->fetchAll() as $inv) {
            $tot  = (float)($inv['total_amount'] ?? 0);
            $paid = (float)($inv['paid_amount'] ?? 0) + (float)($inv['advance_linked_amount'] ?? 0);
            $bal  = max(0, $tot - $paid);

            $totals['invoiced']    += $tot;
            $totals['invoice_due'] += $bal;

            $invoices[] = [
                'ref_no'       => $inv['invoice_number'],
                'tx_date'      => $inv['invoice_date'],
                'total_amount' => $tot,
                'paid_amount'  => $paid,
                'balance'      => $bal,
                'tx_status'    => ($bal <= 0.005) ? 'PAID' : ($paid > 0.005 ? 'PARTIALLY PAID' : 'DUE'),
                'badge_class'  => ($bal <= 0.005) ? 'success' : ($paid > 0.005 ? 'warning text-dark' : 'danger'),
                'action_url'   => APP_URL . '/invoices/preview/' . $inv['invoice_id'],
            ];
        }

        // 2. Credit Notes (settled only through Pay-Out)
        $cnStmt = $db->prepare("SELECT * FROM acc_credit_notes WHERE party_id = ? AND status != 'CANCELLED' ORDER BY credit_note_date DESC, credit_note_id DESC");
        $cnStmt->execute([$id]);
        foreach ($cnStmt->fetchAll() as $cn) {
            $tot      = (float)($cn['total_amount'] ?? 0);
            $adjusted = (float)($cn['adjusted_amount'] ?? 0);
            $bal      = max(0, $tot - $adjusted);

            $totals['credit']      += $tot;
            $totals['credit_open'] += $bal;

            $creditNotes[] = [
                'ref_no'        => $cn['credit_note_number'],
                'tx_date'       => $cn['credit_note_date'],
                'total_amount'  => $tot,
                'adjusted'      => $adjusted,
                'balance'       => $bal,
                'tx_status'     => ($bal <= 0.005) ? 'SETTLED' : ($adjusted > 0.005 ? 'PARTIALLY SETTLED' : 'OPEN'),
                'badge_class'   => ($bal <= 0.005) ? 'success' : ($adjusted > 0.005 ? 'warning text-dark' : 'danger'),
                'action_url'    => APP_URL . '/creditNotes/preview/' . $cn['credit_note_id'],
            ];
        }

        // 3. Payments (split into Pay In / Pay Out) with allocated amounts
        $payStmt = $db->prepare("
            SELECT p.*,
                   COALESCE((SELECT SUM(pa.allocated_amount) FROM acc_payment_allocations pa WHERE pa.payment_id = p.payment_id), 0)
                 + COALESCE((SELECT SUM(pa2.allocated_amount) FROM acc_payment_allocations pa2 WHERE pa2.linked_payment_id = p.payment_id), 0) AS total_allocated
            FROM acc_payments p
            WHERE p.party_id = ? AND (p.status != 'CANCELLED' OR p.status IS NULL)
            ORDER BY p.payment_date DESC, p.payment_id DESC
        ");
        $payStmt->execute([$id]);
        foreach ($payStmt->fetchAll() as $pay) {
            $amt       = (float)($pay['amount'] ?? 0);
            $allocated = (float)($pay['total_allocated'] ?? 0);
            $unused    = max(0, $amt - $allocated);

            $row = [
                'ref_no'       => $pay['reference_number'] ?? ('PAY-' . $pay['payment_id']),
                'tx_date'      => $pay['payment_date'],
                'total_amount' => $amt,
                'allocated'    => $allocated,
                'unused'       => $unused,
                'method'       => $pay['payment_method'] ?? '-',
                'tx_status'    => ($unused <= 0.005) ? 'FULLY ALLOCATED' : 'UNUSED BALANCE',
                'badge_class'  => ($unused <= 0.005) ? 'success' : 'warning text-dark',
                'action_url'   => APP_URL . '/payments/preview/' . $pay['payment_id'],
            ];

            if (($pay['payment_type'] ?? '') === 'PAY_IN') {
                $totals['paid_in'] += $amt;
                $payIns[] = $row;
            } else {
                $totals['paid_out'] += $amt;
                $payOuts[] = $row;
            }
        }

        $this->view('parties/party-statement', [
            'party'        => $party,
            'invoices'     => $invoices,
            'creditNotes'  => $creditNotes,
            'payIns'       => $payIns,
            'payOuts'      => $payOuts,
            'totals'       => $totals
        ]);
    }

    public function create() {
        $partyModel = $this->model('Party');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $partyModel->createParty($_POST);
            header('Location: ' . APP_URL . '/parties/created');
            exit;
        }
        $states = $partyModel->getStates();
        $this->view('parties/create', ['states' => $states]);
    }

    public function edit($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/parties/created');
            exit;
        }
        $partyModel = $this->model('Party');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $partyModel->updateParty($id, $_POST);
            header('Location: ' . APP_URL . '/parties/created');
            exit;
        }
        $party = $partyModel->getPartyById($id);
        $states = $partyModel->getStates();
        $this->view('parties/edit', ['party' => $party, 'states' => $states]);
    }

    public function deletePartyEntirely($partyId = null) {
        if ($partyId) {
            $this->model('Party')->deletePartyWithTransactions($partyId);
        }
        header('Location: ' . APP_URL . '/parties/created');
        exit;
    }
}