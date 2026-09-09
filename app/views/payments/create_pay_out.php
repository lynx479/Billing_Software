<form method="POST" action="<?php echo APP_URL; ?>/payments/createPayOut" id="payOutForm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?php echo APP_URL; ?>/payments/payOut" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Pay Out List
        </a>
        <h3 class="fw-bold text-uppercase mb-0 text-danger">RECORD PAY OUT</h3>
        <button type="submit" class="btn btn-danger btn-lg px-4 shadow-sm fw-bold">
            <i class="bi bi-save me-1"></i> Save Pay Out
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6 border-end">
                    <label class="form-label fw-bold">Party / Vendor / Customer <span class="text-danger">*</span></label>
                    <select name="party_id" id="partySelect" class="form-select" required>
                        <option value="">-- Choose Party --</option>
                        <?php foreach ($parties as $p): ?>
                            <option value="<?php echo $p['party_id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['state']); ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <div class="mt-3">
                        <label class="form-label fw-bold">Payment Remarks</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Sales return refund, Vendor payout..."></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Voucher / Reference #</label>
                            <input type="text" name="reference_number" class="form-control fw-bold text-danger" value="<?php echo htmlspecialchars($reference_number); ?>" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <?php echo sys_time_badge('System Date & Time'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multiple Payment Allocations Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 me-2"></i>Payment Paid Out Breakdown</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btnAddPaymentRow">
                <i class="bi bi-plus-circle me-1"></i> Add Payment Mode
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered align-middle mb-0" id="paymentsTable">
                <thead class="table-light">
                    <tr>
                        <th>Paid From Account / Bank</th>
                        <th>Payment Mode</th>
                        <th width="200px">Amount (<?php echo cur_symbol(); ?>)</th>
                        <th width="40px"></th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <!-- Injected by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Linking: Open Credit Notes & Unused Pay Ins -->
    <div class="card shadow-sm border-0 mb-4" id="linkingCard" style="display:none;">
        <div class="card-header bg-light py-2">
            <span class="fw-bold text-dark"><i class="bi bi-link-45deg me-1"></i> Link Pay Out to Open Credit Notes &amp; Unused Pay Ins</span>
        </div>
        <div class="card-body p-3">
            <h6 class="fw-bold text-danger mb-2">1. Open Credit Notes (Refunds Due)</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Select</th><th>Credit Note #</th><th>Date</th><th class="text-end">Credit Amount</th><th class="text-end">Open Balance</th><th class="text-end" width="180px">Allocated Amount (<?php echo cur_symbol(); ?>)</th></tr>
                    </thead>
                    <tbody id="cnLinkingBody">
                        <tr><td colspan="6" class="text-center py-2 text-muted">No open credit notes for this party.</td></tr>
                    </tbody>
                </table>
            </div>

            <h6 class="fw-bold text-danger mb-2">2. Unused Pay Ins (Advance Return)</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Select</th><th>Pay In #</th><th>Date</th><th class="text-end">Total Received</th><th class="text-end">Unused Balance</th><th class="text-end" width="180px">Linked Amount (<?php echo cur_symbol(); ?>)</th></tr>
                    </thead>
                    <tbody id="payinLinkingBody">
                        <tr><td colspan="6" class="text-center py-2 text-muted">No unused pay ins available.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live Total Bar -->
    <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
    <div class="card shadow-sm border-0 bg-light p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fs-5 fw-bold text-dark">Total Pay Out Amount Paid:</span>
            <h3 class="fw-bold text-danger mb-0"><?php echo cur_symbol(); ?> <span id="dispTotalPaid">0.00</span></h3>
        </div>
    </div>
</form>

<script>
const BANKS = <?php echo json_encode($banks); ?>;
let payIndex = 0;

document.addEventListener('DOMContentLoaded', function () {
    addPaymentRow();
    document.getElementById('btnAddPaymentRow').addEventListener('click', addPaymentRow);

    const partySelect = document.getElementById('partySelect');
    partySelect.addEventListener('change', function () {
        if (!this.value) {
            document.getElementById('linkingCard').style.display = 'none';
            return;
        }
        loadPayOutLinks(this.value);
    });
});

function addPaymentRow() {
    payIndex++;
    const tbody = document.getElementById('paymentsTableBody');
    const tr = document.createElement('tr');
    tr.id = 'pay_row_' + payIndex;

    let bOpts = '';
    BANKS.forEach(b => { bOpts += `<option value="${b.bank_id}">${b.bank_name} - ${b.account_number}</option>`; });

    tr.innerHTML = `
        <td><select name="payments[${payIndex}][bank_id]" class="form-select form-select-sm" required>${bOpts}</select></td>
        <td>
            <select name="payments[${payIndex}][payment_method]" class="form-select form-select-sm">
                <option value="Bank Transfer">Bank Transfer / NEFT / IMPS</option>
                <option value="UPI">UPI / QR</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
            </select>
        </td>
        <td><input type="number" step="0.01" name="payments[${payIndex}][amount]" class="form-control form-control-sm text-end fw-bold pay-input" value="0.00" required></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove(); calculatePayOutTotal();"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);

    tr.querySelector('.pay-input').addEventListener('input', calculatePayOutTotal);
    calculatePayOutTotal();
}

function calculatePayOutTotal() {
    let directPaid = 0;
    document.querySelectorAll('.pay-input').forEach(inp => {
        directPaid += parseFloat(inp.value) || 0;
    });

    // Sum selected unused pay ins
    let payinLinked = 0;
    document.querySelectorAll('.pi-chk:checked').forEach(chk => {
        const row = chk.closest('tr');
        const inp = row.querySelector('.pi-alloc');
        payinLinked += parseFloat(inp.value) || 0;
    });

    // Total Paid Out = Direct entered + Unused Pay In linked
    const totalPaid = directPaid + payinLinked;

    document.getElementById('dispTotalPaid').textContent = totalPaid.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalAmountInput').value = totalPaid.toFixed(2);

    // Auto-allocate to ticked open credit notes
    recalculateCreditNoteAllocations(totalPaid);
}

function recalculateCreditNoteAllocations(availableFunds) {
    let remaining = availableFunds;
    document.querySelectorAll('.cn-chk').forEach(chk => {
        const row = chk.closest('tr');
        const inp = row.querySelector('.cn-alloc');
        const bal = parseFloat(chk.getAttribute('data-bal')) || 0;

        if (chk.checked) {
            const alloc = Math.min(bal, remaining);
            inp.value = alloc.toFixed(2);
            remaining = Math.max(0, remaining - alloc);
        }
    });
}

function loadPayOutLinks(partyId) {
    fetch('<?php echo APP_URL; ?>/payments/getPayOutLinkingDataAjax/' + partyId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('linkingCard').style.display = 'block';

            // Credit Notes
            const cnBody = document.getElementById('cnLinkingBody');
            cnBody.innerHTML = '';
            if (!data.credit_notes || data.credit_notes.length === 0) {
                cnBody.innerHTML = '<tr><td colspan="6" class="text-center py-2 text-muted">No open credit notes for this party.</td></tr>';
            } else {
                data.credit_notes.forEach(cn => {
                    const bal = parseFloat(cn.balance_due);
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input cn-chk" data-bal="${bal}"></td>
                        <td><strong>${cn.credit_note_number}</strong></td>
                        <td>${cn.credit_note_date}</td>
                        <td class="text-end"><?php echo cur_symbol(); ?> ${parseFloat(cn.total_amount).toFixed(2)}</td>
                        <td class="text-end text-danger fw-bold"><?php echo cur_symbol(); ?> ${bal.toFixed(2)}</td>
                        <td><input type="number" step="0.01" name="allocated_credit_notes[${cn.credit_note_id}]" class="form-control form-control-sm text-end cn-alloc" value="0.00" disabled></td>
                    `;
                    cnBody.appendChild(tr);
                });
            }

            // Pay Ins
            const inBody = document.getElementById('payinLinkingBody');
            inBody.innerHTML = '';
            if (!data.unused_payins || data.unused_payins.length === 0) {
                inBody.innerHTML = '<tr><td colspan="6" class="text-center py-2 text-muted">No unused pay ins available.</td></tr>';
            } else {
                data.unused_payins.forEach(pi => {
                    const bal = parseFloat(pi.balance_amount);
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input pi-chk" data-bal="${bal}"></td>
                        <td><strong>${pi.reference_number}</strong></td>
                        <td>${pi.payment_date}</td>
                        <td class="text-end"><?php echo cur_symbol(); ?> ${parseFloat(pi.total_amount).toFixed(2)}</td>
                        <td class="text-end text-success fw-bold"><?php echo cur_symbol(); ?> ${bal.toFixed(2)}</td>
                        <td><input type="number" step="0.01" name="linked_payment_alloc[${pi.payment_id}]" class="form-control form-control-sm text-end pi-alloc" value="0.00" disabled></td>
                    `;
                    inBody.appendChild(tr);
                });
            }

            attachLinkCheckboxListeners();
        });
}

function attachLinkCheckboxListeners() {
    // Pay Ins Checkbox
    document.querySelectorAll('.pi-chk').forEach(chk => {
        chk.addEventListener('change', function () {
            const row = this.closest('tr');
            const inp = row.querySelector('.pi-alloc');
            const bal = parseFloat(this.getAttribute('data-bal'));
            if (this.checked) {
                inp.disabled = false;
                inp.value = bal.toFixed(2);
            } else {
                inp.disabled = true;
                inp.value = '0.00';
            }
            calculatePayOutTotal();
        });
    });

    // Credit Notes Checkbox
    document.querySelectorAll('.cn-chk').forEach(chk => {
        chk.addEventListener('change', function () {
            const row = this.closest('tr');
            const inp = row.querySelector('.cn-alloc');
            if (this.checked) {
                inp.disabled = false;
            } else {
                inp.disabled = true;
                inp.value = '0.00';
            }
            calculatePayOutTotal();
        });
    });

    document.querySelectorAll('.cn-alloc, .pi-alloc').forEach(inp => {
        inp.addEventListener('input', calculatePayOutTotal);
    });
}
</script>