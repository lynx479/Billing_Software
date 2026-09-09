<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-danger text-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-up-right-circle me-2"></i>Record Pay Out (Money Paid)</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo APP_URL; ?>/payments/payOut" id="paymentForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Paid To (Vendor / Seller) <span class="text-danger">*</span></label>
                    <select name="party_id" id="partySelect" class="form-select" required>
                        <option value="">-- Choose Party --</option>
                        <?php foreach ($parties as $p): ?>
                            <option value="<?php echo $p['party_id']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?> 
                                <?php echo !empty($p['business_name']) ? ' (' . htmlspecialchars($p['business_name']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Paid From Bank Account <span class="text-danger">*</span></label>
                    <select name="bank_id" class="form-select" required>
                        <option value="">-- Choose Bank Account --</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?php echo $b['bank_id']; ?>"><?php echo htmlspecialchars($b['bank_name'] . ' - ' . $b['account_number']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Amount Paid (<?php echo cur_symbol(); ?>) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount" id="totalAmountInput" class="form-control fw-bold" required placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_method" class="form-select">
                        <option value="Bank Transfer">Bank Transfer / IMPS</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Reference / UTR #</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="UTR Number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Purpose / Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Vendor bill settlement">
                </div>
            </div>

            <!-- Link Payment Trigger Button -->
            <div class="mt-4 p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-link-45deg fs-5 text-danger me-1"></i>Link Payment to Transactions</h6>
                    <small class="text-muted">Allocate this payment directly to settle vendor bills / invoices.</small>
                </div>
                <button type="button" id="btnToggleLink" class="btn btn-outline-danger" disabled>
                    <i class="bi bi-receipt me-1"></i> Link Invoices
                </button>
            </div>

            <!-- Link Payment Table Section -->
            <div id="linkPaymentSection" class="mt-3 card border-danger d-none">
                <div class="card-header bg-danger bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-danger"><i class="bi bi-list-check me-1"></i> Open Tax Invoices for <span id="selectedPartyName" class="text-dark"></span></span>
                    <span class="badge bg-danger">Paid Amount: <?php echo cur_symbol(); ?><span id="displayRecAmount">0.00</span></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="40px" class="text-center">Select</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Invoice No</th>
                                    <th>Total (<?php echo cur_symbol(); ?>)</th>
                                    <th>Balance Due (<?php echo cur_symbol(); ?>)</th>
                                    <th width="180px">Linked Amount (<?php echo cur_symbol(); ?>)</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTableBody">
                                <tr><td colspan="7" class="text-center py-3 text-muted">Select a party to view unpaid invoices.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted small">Total Allocated: <?php echo cur_symbol(); ?><strong id="totalAllocatedDisplay" class="text-success">0.00</strong></span>
                    <h6 class="mb-0 fw-bold">Unused / Unallocated Amount: <?php echo cur_symbol(); ?><span id="unusedAmountDisplay" class="text-danger">0.00</span></h6>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="<?php echo APP_URL; ?>/parties/created" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-danger px-4"><i class="bi bi-check-circle"></i> Save Pay-Out</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const partySelect = document.getElementById('partySelect');
    const totalAmountInput = document.getElementById('totalAmountInput');
    const btnToggleLink = document.getElementById('btnToggleLink');
    const linkSection = document.getElementById('linkPaymentSection');
    const invoicesBody = document.getElementById('invoicesTableBody');
    const selectedPartyName = document.getElementById('selectedPartyName');
    const displayRecAmount = document.getElementById('displayRecAmount');
    const totalAllocatedDisplay = document.getElementById('totalAllocatedDisplay');
    const unusedAmountDisplay = document.getElementById('unusedAmountDisplay');

    partySelect.addEventListener('change', function () {
        const partyId = this.value;
        if (!partyId) {
            btnToggleLink.disabled = true;
            linkSection.classList.add('d-none');
            return;
        }

        btnToggleLink.disabled = false;
        selectedPartyName.textContent = partySelect.options[partySelect.selectedIndex].text;
        loadInvoices(partyId);
    });

    btnToggleLink.addEventListener('click', function () {
        linkSection.classList.toggle('d-none');
        calculateAllocations();
    });

    totalAmountInput.addEventListener('input', calculateAllocations);

    function loadInvoices(partyId) {
        fetch('<?php echo APP_URL; ?>/payments/getPartyInvoicesAjax/' + partyId)
            .then(res => res.json())
            .then(invoices => {
                invoicesBody.innerHTML = '';
                if (!invoices || invoices.length === 0) {
                    invoicesBody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">No unpaid invoices found for this party.</td></tr>';
                    return;
                }

                invoices.forEach(inv => {
                    const row = document.createElement('tr');
                    const bal = parseFloat(inv.balance_due);
                    const tot = parseFloat(inv.total_amount);

                    row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input inv-check" data-id="${inv.invoice_id}" data-bal="${bal}">
                        </td>
                        <td>${inv.invoice_date}</td>
                        <td><span class="badge bg-primary">Tax Invoice</span></td>
                        <td><strong>${inv.invoice_number}</strong></td>
                        <td><?php echo cur_symbol(); ?> ${tot.toFixed(2)}</td>
                        <td class="text-danger fw-bold"><?php echo cur_symbol(); ?> ${bal.toFixed(2)}</td>
                        <td>
                            <input type="number" step="0.01" name="linked_amount[${inv.invoice_id}]" 
                                   class="form-control form-control-sm alloc-input" 
                                   data-max="${bal}" value="0.00" disabled>
                        </td>
                    `;
                    invoicesBody.appendChild(row);
                });

                attachCheckboxListeners();
                calculateAllocations();
            });
    }

    function attachCheckboxListeners() {
        document.querySelectorAll('.inv-check').forEach(chk => {
            chk.addEventListener('change', function () {
                const row = this.closest('tr');
                const allocInput = row.querySelector('.alloc-input');
                const bal = parseFloat(this.getAttribute('data-bal'));

                if (this.checked) {
                    allocInput.disabled = false;
                    const totalRec = parseFloat(totalAmountInput.value) || 0;
                    const currentAllocated = getCurrentlyAllocatedTotal(allocInput);
                    const remainingToAllocate = Math.max(0, totalRec - currentAllocated);
                    allocInput.value = Math.min(bal, remainingToAllocate).toFixed(2);
                } else {
                    allocInput.disabled = true;
                    allocInput.value = '0.00';
                }
                calculateAllocations();
            });
        });

        document.querySelectorAll('.alloc-input').forEach(inp => {
            inp.addEventListener('input', calculateAllocations);
        });
    }

    function getCurrentlyAllocatedTotal(excludeInput = null) {
        let sum = 0;
        document.querySelectorAll('.alloc-input').forEach(inp => {
            if (!inp.disabled && inp !== excludeInput) {
                sum += parseFloat(inp.value) || 0;
            }
        });
        return sum;
    }

    function calculateAllocations() {
        const totalReceived = parseFloat(totalAmountInput.value) || 0;
        displayRecAmount.textContent = totalReceived.toFixed(2);

        let totalAllocated = 0;
        document.querySelectorAll('.alloc-input').forEach(inp => {
            if (!inp.disabled) {
                totalAllocated += parseFloat(inp.value) || 0;
            }
        });

        totalAllocatedDisplay.textContent = totalAllocated.toFixed(2);
        const unused = Math.max(0, totalReceived - totalAllocated);
        unusedAmountDisplay.textContent = unused.toFixed(2);
    }
});
</script>