<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div><strong>Could not save credit note:</strong> <?php echo htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>
<form method="POST" action="<?php echo APP_URL; ?>/creditNotes/create" id="creditNoteForm">
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?php echo APP_URL; ?>/credit_notes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Credit Notes
        </a>
        <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-danger">CREDIT NOTE</h3>
        <button type="submit" class="btn btn-danger btn-lg px-4 shadow-sm">
            <i class="bi bi-save me-1"></i> Save Credit Note
        </button>
    </div>

    <!-- Top Details Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left: Party Selection, Original Invoice Selector & Details -->
                <div class="col-md-5 border-end">
                    <label class="form-label fw-bold">Customer / Party <span class="text-danger">*</span></label>
                    <select name="party_id" id="partySelect" class="form-select mb-3" required>
                        <option value="">-- Search & Select Party --</option>
                        <?php foreach ($parties as $p): ?>
                            <option value="<?php echo $p['party_id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                    data-state="<?php echo htmlspecialchars($p['state']); ?>"
                                    data-gstin="<?php echo htmlspecialchars($p['gstin'] ?? ''); ?>"
                                    data-address="<?php echo htmlspecialchars($p['address'] ?? ''); ?>"
                                    data-shipping-address="<?php echo htmlspecialchars($p['shipping_address'] ?? $p['address'] ?? ''); ?>"
                                    data-phone="<?php echo htmlspecialchars($p['phone'] ?? ''); ?>">
                                <?php echo htmlspecialchars($p['name']); ?> <?php echo !empty($p['business_name']) ? ' (' . htmlspecialchars($p['business_name']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Original Invoice Selection Dropdown (Placed below customer) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Original Invoice # (Optional - Auto Fill Items)</label>
                        <select name="original_invoice_id" id="invoiceSelect" class="form-select form-select-sm" disabled style="max-height: 200px; overflow-y: auto;">
                            <option value="">-- Select Party First to Load Invoices --</option>
                        </select>
                        <input type="hidden" name="original_invoice_number" id="originalInvoiceNumber">
                    </div>

                    <div id="partyInfoBox" class="p-3 bg-light rounded border d-none">
                        <small class="d-block text-muted"><strong>Billing Address:</strong> <span id="partyAddressText">-</span></small>
                        <small class="d-block text-muted"><strong>Shipping Address:</strong> <span id="partyShippingAddressText">-</span></small>
                        <small class="d-block text-muted"><strong>GSTIN / PAN:</strong> <span id="partyGstinText">-</span></small>
                        <small class="d-block text-muted"><strong>State:</strong> <span id="partyStateText">-</span></small>
                    </div>
                </div>

                <!-- Right: Credit Note Metadata -->
                <div class="col-md-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Credit Note Number</label>
                            <input type="text" name="credit_note_number" class="form-control fw-bold text-danger" value="<?php echo htmlspecialchars($credit_note_number ?? 'CN-1001'); ?>" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="credit_note_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <?php echo sys_time_badge('System Date & Time'); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Place of Supply (State) <span class="text-danger">*</span></label>
                            <input type="text" name="place_of_supply" id="placeOfSupplyInput" class="form-control bg-light fw-bold" value="<?php echo htmlspecialchars($settings['company_state'] ?? 'Kerala'); ?>" required readonly tabindex="-1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">GST Tax Type</label>
                            <div class="form-control bg-light text-danger fw-bold" id="gstTypeBadge">
                                Intrastate (CGST + SGST)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 10-Column Items Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-file-earmark-minus me-2"></i>Returned / Credited Line Items</h6>
            <button type="button" class="btn btn-sm btn-danger" id="btnAddItemRow">
                <i class="bi bi-plus-circle me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr class="text-center small fw-bold">
                            <th width="40px">#</th>
                            <th width="20%">Items</th>
                            <th>Description / Reason</th>
                            <th width="90px">Qty</th>
                            <th width="110px">Units</th>
                            <th width="130px">Rate (<?php echo cur_symbol(); ?>)</th>
                            <th width="100px">Disc %</th>
                            <th width="110px">Disc (<?php echo cur_symbol(); ?>)</th>
                            <th width="120px">Tax Amount</th>
                            <th width="140px">Total (<?php echo cur_symbol(); ?>)</th>
                            <th width="40px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <!-- Dynamic Rows Injected by JS -->
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Column Totals:</td>
                            <td class="text-end" id="colTotalTaxable"><?php echo cur_symbol(); ?> 0.00</td>
                            <td></td>
                            <td class="text-end text-danger" id="colTotalDiscount"><?php echo cur_symbol(); ?> 0.00</td>
                            <td class="text-end text-primary" id="colTotalTax"><?php echo cur_symbol(); ?> 0.00</td>
                            <td class="text-end text-danger fs-6" id="colGrandTotal"><?php echo cur_symbol(); ?> 0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Hidden Fields for Backend Submission -->
    <input type="hidden" name="taxable_amount" id="subtotalInput" value="0.00">
    <input type="hidden" name="discount_amount" id="discountInput" value="0.00">
    <input type="hidden" name="tax_amount" id="taxInput" value="0.00">
    <input type="hidden" name="cgst_amount" id="cgstInput" value="0.00">
    <input type="hidden" name="sgst_amount" id="sgstInput" value="0.00">
    <input type="hidden" name="igst_amount" id="igstInput" value="0.00">
    <input type="hidden" name="total_amount" id="grandTotalInput" value="0.00">

    <!-- Bottom Section: Payments, Allocations & Totals -->
    <div class="row g-4 mb-4">
        <!-- Left: Pay-Out Linking & Direct Refund Options -->
        <div class="col-md-7">
            
            <!-- Credit notes are created OPEN unless settled by linking an existing Pay-Out. -->
            <div class="alert alert-warning border d-flex align-items-start gap-2 mb-3" role="alert">
                <i class="bi bi-info-circle fs-5"></i>
                <div>
                    <strong>This credit note will be saved as OPEN.</strong>
                    <div class="small">
                        Credit notes cannot be refunded directly from this screen. They are settled only through a
                        <a href="<?php echo APP_URL; ?>/payments/createPayOut" class="fw-bold">Pay-Out</a> &mdash;
                        created later, or by linking an existing unallocated Pay-Out below.
                    </div>
                </div>
            </div>

            <!-- Selected invoice payment position (drives the auto-filled credit amount) -->
            <div id="invoicePaidInfo" class="alert alert-info border d-none py-2 px-3 mb-3 small" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-receipt me-1"></i> <span id="invPaidNumber"></span></div>
                <div class="d-flex flex-wrap gap-3">
                    <span>Invoice Total: <strong id="invPaidTotal"><?php echo cur_symbol(); ?> 0.00</strong></span>
                    <span>Already Paid: <strong class="text-success" id="invPaidPaid"><?php echo cur_symbol(); ?> 0.00</strong></span>
                    <span>Outstanding: <strong class="text-danger" id="invPaidBalance"><?php echo cur_symbol(); ?> 0.00</strong></span>
                </div>
                <div class="mt-1" id="invPaidNote"></div>
            </div>

            <!-- Direct Payout (Add Cash / Bank) - mirrors Invoice's Direct Payment -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-cash-coin me-1"></i> Direct Payout (Refund Cash / Bank Now)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnAddPayoutRow">
                        <i class="bi bi-plus-circle me-1"></i> Add Payout
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0" id="payoutsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Account / Bank</th>
                                <th>Payment Method</th>
                                <th width="150px">Amount (<?php echo cur_symbol(); ?>)</th>
                                <th width="40px"></th>
                            </tr>
                        </thead>
                        <tbody id="payoutsTableBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Link Pay-Out Trigger Banner -->
            <div class="p-3 bg-light rounded border mb-3 d-none justify-content-between align-items-center" id="linkBannerContainer">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-link-45deg fs-5 text-danger me-1"></i>Link Pay-Out Disbursement</h6>
                    <small class="text-muted" id="linkBannerSubtext">Settle this credit note against unallocated Pay-Out disbursements already issued to this party.</small>
                </div>
                <button type="button" id="btnToggleLinkPayOut" class="btn btn-outline-danger fw-bold" disabled>
                    <i class="bi bi-link me-1"></i> Link Pay-Out
                </button>
            </div>

            <!-- Link Pay-Out Table Section -->
            <div id="linkPayOutSection" class="card border-danger mb-3 d-none">
                <div class="card-header bg-danger bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-danger">
                        <i class="bi bi-list-check me-1"></i> Unallocated Pay-Outs for <span id="selectedPartyName" class="text-dark"></span>
                    </span>
                    <span class="badge bg-danger">Credit Note Total: <?php echo cur_symbol(); ?> <span id="displayCreditNoteTotal">0.00</span></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover align-middle mb-0">
                            <thead class="table-light text-center small fw-bold">
                                <tr>
                                    <th width="50px">Select</th>
                                    <th width="110px">Date</th>
                                    <th width="90px">Type</th>
                                    <th>Pay Out #</th>
                                    <th class="text-end" width="120px">Total (<?php echo cur_symbol(); ?>)</th>
                                    <th class="text-end" width="130px">Balance (<?php echo cur_symbol(); ?>)</th>
                                    <th class="text-end" width="150px">Linked Amount (<?php echo cur_symbol(); ?>)</th>
                                </tr>
                            </thead>
                            <tbody id="payOutTableBody">
                                <tr><td colspan="7" class="text-center py-3 text-muted">Select a party to view available Pay-Outs.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted small">Total Linked: <?php echo cur_symbol(); ?> <strong id="totalLinkedDisplay" class="text-danger">0.00</strong></span>
                    <h6 class="mb-0 fw-bold">Unused / Remaining Credit: <?php echo cur_symbol(); ?> <span id="unusedAmountDisplay" class="text-primary">0.00</span></h6>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label fw-bold">Reason for Credit Note / Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="State reason for credit note (e.g. Sales return, rate difference, damaged goods)..."><?php echo htmlspecialchars($settings['credit_note_footer_notes'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Right: Grand Totals, Tax Breakdown & Round-Off -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Taxable Value:</td>
                            <td class="text-end fw-bold" id="dispTaxable"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Discount:</td>
                            <td class="text-end text-danger fw-bold" id="dispDiscount">- <?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr id="rowCgst">
                            <td class="text-muted">CGST Total:</td>
                            <td class="text-end text-primary" id="dispCgst"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr id="rowSgst">
                            <td class="text-muted">SGST Total:</td>
                            <td class="text-end text-primary" id="dispSgst"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr id="rowIgst" style="display:none;">
                            <td class="text-muted">IGST Total:</td>
                            <td class="text-end text-primary" id="dispIgst"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                            <tr>
                                <td class="text-muted align-middle">Round Off:</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <div class="form-check m-0 d-flex align-items-center gap-1">
                                            <input class="form-check-input mt-0" type="checkbox" id="autoRoundOffCheck" title="Auto round off (> 0.60 rounds up)">
                                            <label class="form-check-label small text-muted user-select-none" for="autoRoundOffCheck" style="font-size: 11px;">Auto</label>
                                        </div>
                                        <input type="number" step="0.01" name="round_off" id="roundOffInput" class="form-control form-control-sm text-end" style="width: 90px;" value="0.00">
                                    </div>
                                </td>
                            </tr>
                        <tr class="border-top">
                            <td class="fs-5 fw-bold text-dark">Credit Note Total:</td>
                            <td class="text-end fs-5 fw-bold text-danger" id="dispFinalTotal"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fs-6 fw-bold text-primary">Open Balance (settle via Pay-Out):</td>
                            <td class="text-end fs-6 fw-bold text-primary" id="dispBalance"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const ITEM_MASTER = <?php echo json_encode($items ?? []); ?>;
const UNIT_MASTER = <?php echo json_encode($units ?? []); ?>;
const TAX_MASTER = <?php echo json_encode($taxes ?? []); ?>;
const BANK_MASTER = <?php echo json_encode($banks ?? []); ?>;
const COMPANY_STATE = "<?php echo addslashes($settings['company_state'] ?? 'Kerala'); ?>";
let itemRowIndex = 0;
let payoutRowIndex = 0;

function addPayoutRow() {
    payoutRowIndex++;
    const tbody = document.getElementById('payoutsTableBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'payout_row_' + payoutRowIndex;

    let bankOpts = '';
    BANK_MASTER.forEach(b => {
        bankOpts += `<option value="${b.bank_id}">${b.bank_name} - ${b.account_number}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="payouts[${payoutRowIndex}][bank_id]" class="form-select form-select-sm" required>
                ${bankOpts}
            </select>
        </td>
        <td>
            <select name="payouts[${payoutRowIndex}][payment_method]" class="form-select form-select-sm">
                <option value="Bank Account">Bank Transfer / IMPS</option>
                <option value="UPI">UPI / QR Code</option>
                <option value="Cheque">Cheque</option>
                <option value="Cash">Cash</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="payouts[${payoutRowIndex}][amount]" class="form-control form-control-sm text-end fw-bold payout-amt" value="0.00" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove(); calculateAll();"><i class="bi bi-x-circle"></i></button>
        </td>
    `;

    tbody.appendChild(tr);
    tr.querySelector('.payout-amt').addEventListener('input', calculateAll);
    calculateAll();
}

// -------------------------------------------------------------
// QUANTITY-BY-UNIT VALIDATION (mirrors app/core/Validation.php)
// -------------------------------------------------------------
function unitAllowsDecimal(symbol) {
    const u = UNIT_MASTER.find(u => (u.unit_symbol || '').toUpperCase() === (symbol || '').toUpperCase());
    return !!u && (u.unit_type === 'Weight' || u.unit_type === 'Volume');
}

function enforceQtyRule(tr) {
    const unitSel = tr.querySelector('.row-unit');
    const qtyInput = tr.querySelector('.row-qty');
    if (!unitSel || !qtyInput) return true;
    const allowsDecimal = unitAllowsDecimal(unitSel.value);
    qtyInput.step = allowsDecimal ? '0.01' : '1';
    const qtyVal = parseFloat(qtyInput.value) || 0;
    const isWhole = Math.abs(qtyVal - Math.round(qtyVal)) < 0.0001;
    if (!allowsDecimal && !isWhole) {
        qtyInput.classList.add('is-invalid');
        qtyInput.title = 'Quantity for "' + unitSel.value + '" must be a whole number. Only weight-based units (e.g. KG) accept decimals.';
        return false;
    }
    qtyInput.classList.remove('is-invalid');
    qtyInput.title = '';
    return true;
}

function validateAllItemRows() {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    let firstBadRow = null;
    rows.forEach(tr => { if (!enforceQtyRule(tr) && !firstBadRow) firstBadRow = tr; });
    return firstBadRow;
}

document.addEventListener('DOMContentLoaded', function () {
    addItemRow();

    const btnAddPayoutRow = document.getElementById('btnAddPayoutRow');
    if (btnAddPayoutRow) btnAddPayoutRow.addEventListener('click', addPayoutRow);

    const creditNoteFormEl = document.getElementById('creditNoteForm');
    if (creditNoteFormEl) {
        creditNoteFormEl.addEventListener('submit', function (e) {
            const badRow = validateAllItemRows();
            if (badRow) {
                e.preventDefault();
                const badQty = badRow.querySelector('.row-qty');
                alert(badQty.title || 'One of the line items has an invalid quantity for its unit.');
                badQty.focus();
            }
        });
    }

    const partySelect = document.getElementById('partySelect');
    const invoiceSelect = document.getElementById('invoiceSelect');
    const originalInvoiceNumber = document.getElementById('originalInvoiceNumber');

    // Party Selection Handler
    if (partySelect) {
        partySelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const partyId = this.value;

            // Reset and load customer's invoices
            invoiceSelect.innerHTML = '<option value="">-- Loading Invoices... --</option>';
            invoiceSelect.disabled = true;
            originalInvoiceNumber.value = '';


            if (!partyId) {
                const infoBox = document.getElementById('partyInfoBox');
                if (infoBox) infoBox.classList.add('d-none');
                invoiceSelect.innerHTML = '<option value="">-- Select Party First --</option>';
                return;
            }

            const state = opt.getAttribute('data-state') || COMPANY_STATE;
            const partyAddr = document.getElementById('partyAddressText');
            const partyShip = document.getElementById('partyShippingAddressText');
            const partyGst = document.getElementById('partyGstinText');
            const partySt = document.getElementById('partyStateText');
            const posInput = document.getElementById('placeOfSupplyInput');
            const infoBox = document.getElementById('partyInfoBox');

            if (partyAddr) partyAddr.textContent = opt.getAttribute('data-address') || '-';
            if (partyShip) partyShip.textContent = opt.getAttribute('data-shipping-address') || '-';
            if (partyGst) partyGst.textContent = opt.getAttribute('data-gstin') || '-';
            if (partySt) partySt.textContent = state;
            if (posInput) posInput.value = state;
            if (infoBox) infoBox.classList.remove('d-none');

            updateGstType();

            
            const nameEl = document.getElementById('selectedPartyName');
            if (nameEl) nameEl.textContent = opt.textContent.trim();

            // Load Invoices for this party
            loadPartyInvoices(partyId);

            // Load unallocated Pay-Outs for this party
            loadPartyPayOuts(partyId);
        });
    }

    // Load Invoices Dropdown for the Selected Party
    function loadPartyInvoices(partyId) {
        fetch('<?php echo APP_URL; ?>/credit_notes/getPartyInvoicesAjax/' + partyId)
            .then(res => res.json())
            .then(invoices => {
                invoiceSelect.innerHTML = '<option value="">-- Choose Original Invoice (Optional) --</option>';
                if (invoices && invoices.length > 0) {
                    invoices.forEach(inv => {
                        const invTotal = parseFloat(inv.total_amount) || 0;
                        const invPaid = parseFloat(inv.paid_amount) || 0;
                        const rawBal = parseFloat(inv.balance_amount);
                        const balance = isNaN(rawBal) ? (invTotal - invPaid) : rawBal;
                        const payState = invPaid <= 0.005 ? 'Unpaid' : (balance > 0.005 ? 'Partially Paid' : 'Fully Paid');

                        invoiceSelect.innerHTML += `
                            <option value="${inv.invoice_id}" data-inv-no="${inv.invoice_number}"
                                    data-total="${invTotal.toFixed(2)}" data-paid="${invPaid.toFixed(2)}"
                                    data-balance="${balance.toFixed(2)}" data-pay-state="${payState}">
                                ${inv.invoice_number} (${inv.invoice_date}) - <?php echo cur_symbol(); ?> ${invTotal.toFixed(2)} | Paid: <?php echo cur_symbol(); ?> ${invPaid.toFixed(2)} | Due: <?php echo cur_symbol(); ?> ${balance.toFixed(2)} [${payState}]
                            </option>
                        `;
                    });
                    invoiceSelect.disabled = false;
                } else {
                    invoiceSelect.innerHTML = '<option value="">-- No Invoices Found for This Party --</option>';
                }
            })
            .catch(() => {
                invoiceSelect.innerHTML = '<option value="">-- Error Loading Invoices --</option>';
            });
    }

    // When an Original Invoice is chosen, fetch and auto-populate line items
    if (invoiceSelect) {
        invoiceSelect.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            const invoiceId = this.value;

            const paidBox = document.getElementById('invoicePaidInfo');

            if (!invoiceId) {
                originalInvoiceNumber.value = '';
                if (paidBox) paidBox.classList.add('d-none');
                return;
            }

            originalInvoiceNumber.value = selectedOpt.getAttribute('data-inv-no') || '';

            // Real payment position of the invoice (from Pay-In / allocation data)
            const invTotal = parseFloat(selectedOpt.getAttribute('data-total')) || 0;
            const invPaid = parseFloat(selectedOpt.getAttribute('data-paid')) || 0;
            const invBalance = parseFloat(selectedOpt.getAttribute('data-balance')) || 0;
            const payState = selectedOpt.getAttribute('data-pay-state') || '';

            // Business rule: only the amount actually received can be credited back.
            // Nothing received yet -> the credit note simply reverses the full invoice value.
            const creditable = invPaid > 0.005 ? invPaid : invTotal;
            const ratio = invTotal > 0 ? (creditable / invTotal) : 1;

            if (paidBox) {
                paidBox.classList.remove('d-none');
                document.getElementById('invPaidNumber').textContent =
                    (selectedOpt.getAttribute('data-inv-no') || '') + ' \u2014 ' + payState;
                document.getElementById('invPaidTotal').textContent = '<?php echo cur_symbol(); ?> ' + invTotal.toFixed(2);
                document.getElementById('invPaidPaid').textContent = '<?php echo cur_symbol(); ?> ' + invPaid.toFixed(2);
                document.getElementById('invPaidBalance').textContent = '<?php echo cur_symbol(); ?> ' + invBalance.toFixed(2);
                document.getElementById('invPaidNote').textContent = invPaid > 0.005
                    ? 'Credit amount auto-filled from the amount actually received (<?php echo cur_symbol(); ?> ' + creditable.toFixed(2) + '). Every line remains editable.'
                    : 'No payment received against this invoice \u2014 the full invoice value has been auto-filled. Every line remains editable.';
            }

            // Fetch Items of this invoice
            fetch('<?php echo APP_URL; ?>/credit_notes/getInvoiceItemsAjax/' + invoiceId)
                .then(res => res.json())
                .then(items => {
                    if (items && items.length > 0) {
                        const tbody = document.getElementById('itemsTableBody');
                        tbody.innerHTML = ''; // Clear default/empty rows
                        itemRowIndex = 0;

                        items.forEach(it => {
                            addItemRow({
                                name: it.item_name,
                                description: it.description || '',
                                quantity: it.quantity || 1,
                                unit: it.unit || 'PCS',
                                price: it.unit_price || 0,
                                discount_percent: it.discount_percent || 0,
                                discount_amount: it.discount_amount || 0,
                                tax_rate: it.tax_rate || 18,
                                total_amount: (parseFloat(it.total_amount) || 0) * ratio
                            });
                        });

                        // Reverse-calculate every populated row from its credited total
                        document.querySelectorAll('#itemsTableBody tr').forEach(tr => calculateReverse(tr));
                        calculateAll();
                    }
                });
        });
    }

    const posEl = document.getElementById('placeOfSupplyInput');
    if (posEl) posEl.addEventListener('input', updateGstType);

    const btnAddItm = document.getElementById('btnAddItemRow');
    if (btnAddItm) btnAddItm.addEventListener('click', () => addItemRow());

    const roundEl = document.getElementById('roundOffInput');
    if (roundEl) roundEl.addEventListener('input', calculateAll);

    const autoRoundCheck = document.getElementById('autoRoundOffCheck');
    if (autoRoundCheck) {
        autoRoundCheck.addEventListener('change', function () {
            if (!this.checked && roundEl) {
                roundEl.value = '0.00';
            }
            calculateAll();
        });
    }

    const btnToggleLinkPayOut = document.getElementById('btnToggleLinkPayOut');
    const linkSection = document.getElementById('linkPayOutSection');
    if (btnToggleLinkPayOut && linkSection) {
        btnToggleLinkPayOut.addEventListener('click', function () {
            linkSection.classList.toggle('d-none');
            calculateAll();
        });
    }

    // Fetch and populate unallocated Pay-Outs for the selected party
    function loadPartyPayOuts(partyId) {
        const payOutBody = document.getElementById('payOutTableBody');
        const linkBannerContainer = document.getElementById('linkBannerContainer');
        if (!payOutBody) return;

        fetch('<?php echo APP_URL; ?>/credit_notes/getPartyUnusedPayOutsAjax/' + partyId)
            .then(res => res.json())
            .then(payOuts => {
                payOutBody.innerHTML = '';

                if (!payOuts || payOuts.length === 0) {
                    if (linkBannerContainer) {
                        linkBannerContainer.classList.add('d-none');
                        linkBannerContainer.classList.remove('d-flex');
                    }
                    if (linkSection) linkSection.classList.add('d-none');
                    if (btnToggleLinkPayOut) btnToggleLinkPayOut.disabled = true;
                    payOutBody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">No unallocated Pay-Outs available for this party.</td></tr>';
                    calculateAll();
                    return;
                }

                if (linkBannerContainer) {
                    linkBannerContainer.classList.remove('d-none');
                    linkBannerContainer.classList.add('d-flex');
                }
                if (btnToggleLinkPayOut) btnToggleLinkPayOut.disabled = false;

                payOuts.forEach(p => {
                    const row = document.createElement('tr');
                    const bal = parseFloat(p.balance_amount) || 0;
                    const tot = parseFloat(p.total_amount) || 0;

                    row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input payout-check" data-id="${p.payment_id}" data-orig-bal="${bal}">
                        </td>
                        <td class="text-center">${p.payment_date}</td>
                        <td class="text-center"><span class="badge bg-danger">Pay Out</span></td>
                        <td><strong>${p.pay_out_no || ('PAY-' + p.payment_id)}</strong></td>
                        <td class="text-end"><?php echo cur_symbol(); ?> ${tot.toFixed(2)}</td>
                        <td class="text-end text-danger fw-bold payout-bal-display"><?php echo cur_symbol(); ?> ${bal.toFixed(2)}</td>
                        <td>
                            <input type="number" step="0.01" min="0" name="linked_pay_out[${p.payment_id}]"
                                   class="form-control form-control-sm text-end payout-alloc-input"
                                   data-orig-bal="${bal}" value="0.00" disabled>
                        </td>
                    `;
                    payOutBody.appendChild(row);
                });

                attachPayOutListeners();
                calculateAll();
            })
            .catch(() => {
                payOutBody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-danger">Error loading Pay-Outs.</td></tr>';
            });
    }

    function attachPayOutListeners() {
        document.querySelectorAll('.payout-check').forEach(chk => {
            chk.addEventListener('change', function () {
                const row = this.closest('tr');
                const allocInput = row.querySelector('.payout-alloc-input');
                const origBal = parseFloat(this.getAttribute('data-orig-bal')) || 0;

                if (this.checked) {
                    allocInput.disabled = false;
                    const cnTotal = parseFloat(document.getElementById('grandTotalInput').value) || 0;
                    const otherAllocated = getCurrentlyAllocatedPayOuts(allocInput);
                    const stillNeeded = Math.max(0, cnTotal - otherAllocated);
                    allocInput.value = Math.min(origBal, stillNeeded).toFixed(2);
                } else {
                    allocInput.disabled = true;
                    allocInput.value = '0.00';
                }

                updatePayOutBalanceRow(row);
                calculateAll();
            });
        });

        document.querySelectorAll('.payout-alloc-input').forEach(inp => {
            inp.addEventListener('input', function () {
                const row = this.closest('tr');
                const origBal = parseFloat(this.getAttribute('data-orig-bal')) || 0;
                if ((parseFloat(this.value) || 0) > origBal) this.value = origBal.toFixed(2);
                updatePayOutBalanceRow(row);
                calculateAll();
            });
        });
    }

    function updatePayOutBalanceRow(row) {
        const allocInput = row.querySelector('.payout-alloc-input');
        const balDisp = row.querySelector('.payout-bal-display');
        const origBal = parseFloat(allocInput.getAttribute('data-orig-bal')) || 0;
        const linkedVal = allocInput.disabled ? 0 : (parseFloat(allocInput.value) || 0);
        balDisp.textContent = '<?php echo cur_symbol(); ?> ' + Math.max(0, origBal - linkedVal).toFixed(2);
    }
});

function getCurrentlyAllocatedPayOuts(excludeInput = null) {
    let sum = 0;
    document.querySelectorAll('.payout-alloc-input').forEach(inp => {
        if (!inp.disabled && inp !== excludeInput) sum += parseFloat(inp.value) || 0;
    });
    return sum;
}



function updateGstType() {
    const pos = (document.getElementById('placeOfSupplyInput')?.value || '').trim();
    const isInter = (pos.toLowerCase() !== COMPANY_STATE.toLowerCase());
    const badge = document.getElementById('gstTypeBadge');
    
    if (badge) {
        badge.textContent = isInter ? 'Interstate (IGST)' : 'Intrastate (CGST + SGST)';
    }

    const rowCgst = document.getElementById('rowCgst');
    const rowSgst = document.getElementById('rowSgst');
    const rowIgst = document.getElementById('rowIgst');

    if (rowCgst) rowCgst.style.display = isInter ? 'none' : '';
    if (rowSgst) rowSgst.style.display = isInter ? 'none' : '';
    if (rowIgst) rowIgst.style.display = isInter ? '' : 'none';

    calculateAll();
}

// -------------------------------------------------------------
// ITEM ROW CREATION & BIDIRECTIONAL REVERSE/FORWARD ENGINE
// -------------------------------------------------------------

function addItemRow(prefill = null) {
    itemRowIndex++;
    const tbody = document.getElementById('itemsTableBody');
    const tr = document.createElement('tr');
    tr.id = 'item_row_' + itemRowIndex;

    let itemOptions = '<option value="">-- Choose / Search Item --</option>';
    ITEM_MASTER.forEach(itm => {
        const sel = (prefill && prefill.name === itm.name) ? 'selected' : '';
        itemOptions += `<option value="${itm.name}" data-desc="${itm.description || ''}" data-price="${itm.price || itm.selling_price || 0}" data-unit="${itm.unit || 'PCS'}" data-tax="${itm.tax_rate || 18}" ${sel}>${itm.name}</option>`;
    });

    // If prefilled item name is custom (not in catalog)
    if (prefill && prefill.name && !ITEM_MASTER.some(i => i.name === prefill.name)) {
        itemOptions += `<option value="${prefill.name}" selected>${prefill.name}</option>`;
    }

    let unitOptions = '';
    UNIT_MASTER.forEach(u => {
        const uSel = (prefill && prefill.unit === u.unit_symbol) ? 'selected' : '';
        unitOptions += `<option value="${u.unit_symbol}" ${uSel}>${u.unit_symbol} (${u.unit_name})</option>`;
    });

    const initQty = prefill ? prefill.quantity : '1.00';
    const initDesc = prefill ? prefill.description : '';
    const initRate = prefill ? parseFloat(prefill.price).toFixed(2) : '0.00';
    const initDiscPct = prefill ? parseFloat(prefill.discount_percent).toFixed(2) : '0.00';
    const initDiscAmt = prefill ? parseFloat(prefill.discount_amount).toFixed(2) : '0.00';
    const initTaxRate = prefill ? parseFloat(prefill.tax_rate) : 18;
    const initTotal = prefill ? parseFloat(prefill.total_amount).toFixed(2) : '0.00';

    tr.innerHTML = `
        <td class="text-center fw-bold row-idx">${itemRowIndex}</td>
        <td>
            <select name="items[${itemRowIndex}][item_name]" class="form-select form-select-sm item-select" required>
                ${itemOptions}
            </select>
        </td>
        <td><input type="text" name="items[${itemRowIndex}][description]" class="form-control form-control-sm row-desc" placeholder="Reason / Details" value="${initDesc}"></td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][quantity]" class="form-control form-control-sm text-center row-qty" value="${initQty}" required></td>
        <td>
            <select name="items[${itemRowIndex}][unit]" class="form-select form-select-sm row-unit">
                ${unitOptions}
            </select>
        </td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][unit_price]" class="form-control form-control-sm text-end row-rate" value="${initRate}" required></td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][discount_percent]" class="form-control form-control-sm text-center row-disc-pct" value="${initDiscPct}"></td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][discount_amount]" class="form-control form-control-sm text-end row-disc-amt" value="${initDiscAmt}"></td>
        <td class="text-end">
            <span class="row-tax-disp text-primary fw-bold"><?php echo cur_symbol(); ?> 0.00</span>
            <input type="hidden" name="items[${itemRowIndex}][tax_rate]" class="row-tax-rate" value="${initTaxRate}">
            <input type="hidden" name="items[${itemRowIndex}][tax_amount]" class="row-tax-amt" value="0.00">
            <input type="hidden" name="items[${itemRowIndex}][cgst_rate]" class="row-cgst-rate" value="9">
            <input type="hidden" name="items[${itemRowIndex}][cgst_amount]" class="row-cgst-amt" value="0.00">
            <input type="hidden" name="items[${itemRowIndex}][sgst_rate]" class="row-sgst-rate" value="9">
            <input type="hidden" name="items[${itemRowIndex}][sgst_amount]" class="row-sgst-amt" value="0.00">
            <input type="hidden" name="items[${itemRowIndex}][igst_rate]" class="row-igst-rate" value="18">
            <input type="hidden" name="items[${itemRowIndex}][igst_amount]" class="row-igst-amt" value="0.00">
            <input type="hidden" name="items[${itemRowIndex}][taxable_amount]" class="row-taxable-amt" value="0.00">
        </td>
        <td class="text-end fw-bold">
            <input type="number" step="0.01" name="items[${itemRowIndex}][total_amount]" class="form-control form-control-sm text-end fw-bold text-danger row-total-amt" value="${initTotal}" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeItemRow(${itemRowIndex})"><i class="bi bi-trash"></i></button>
        </td>
    `;

    tbody.appendChild(tr);

    const sel = tr.querySelector('.item-select');
    sel.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            tr.querySelector('.row-desc').value = opt.getAttribute('data-desc') || '';
            tr.querySelector('.row-rate').value = parseFloat(opt.getAttribute('data-price') || 0).toFixed(2);
            tr.querySelector('.row-unit').value = opt.getAttribute('data-unit') || 'PCS';
            tr.querySelector('.row-tax-rate').value = parseFloat(opt.getAttribute('data-tax') || 18);
        }
        enforceQtyRule(tr);
        calculateForward(tr);
    });

    tr.querySelector('.row-unit').addEventListener('change', function () {
        enforceQtyRule(tr);
        calculateForward(tr);
    });

    tr.querySelector('.row-total-amt').addEventListener('input', function () {
        calculateReverse(tr);
    });

    tr.querySelector('.row-disc-amt').addEventListener('input', function () {
        calculateReverseDiscount(tr);
    });

    tr.querySelectorAll('.row-qty, .row-rate, .row-disc-pct').forEach(inp => {
        inp.addEventListener('input', function () {
            if (inp.classList.contains('row-qty')) enforceQtyRule(tr);
            calculateForward(tr);
        });
    });

    enforceQtyRule(tr);
    if (prefill && parseFloat(prefill.total_amount) > 0) {
        calculateReverse(tr);
    } else {
        calculateForward(tr);
    }
}

function calculateForward(tr) {
    const qty = parseFloat(tr.querySelector('.row-qty').value) || 0;
    const rate = parseFloat(tr.querySelector('.row-rate').value) || 0;
    const discPct = parseFloat(tr.querySelector('.row-disc-pct').value) || 0;
    const taxRate = parseFloat(tr.querySelector('.row-tax-rate').value) || 0;

    const baseAmount = qty * rate;
    const discAmt = baseAmount * (discPct / 100);
    const taxable = Math.max(0, baseAmount - discAmt);
    const taxAmt = taxable * (taxRate / 100);
    const rowTotal = taxable + taxAmt;

    tr.querySelector('.row-disc-amt').value = discAmt.toFixed(2);
    tr.querySelector('.row-taxable-amt').value = taxable.toFixed(2);
    tr.querySelector('.row-tax-disp').textContent = '<?php echo cur_symbol(); ?> ' + taxAmt.toFixed(2);
    tr.querySelector('.row-tax-amt').value = taxAmt.toFixed(2);
    tr.querySelector('.row-total-amt').value = rowTotal.toFixed(2);

    calculateAll();
}

function calculateReverse(tr) {
    const total = parseFloat(tr.querySelector('.row-total-amt').value) || 0;
    const qty = parseFloat(tr.querySelector('.row-qty').value) || 1;
    const discPct = parseFloat(tr.querySelector('.row-disc-pct').value) || 0;
    const taxRate = parseFloat(tr.querySelector('.row-tax-rate').value) || 0;

    if (total <= 0 || qty <= 0) {
        calculateAll();
        return;
    }

    const taxable = total / (1 + (taxRate / 100));
    const taxAmt = total - taxable;

    let baseAmount = taxable;
    let discAmt = 0;
    if (discPct > 0 && discPct < 100) {
        baseAmount = taxable / (1 - (discPct / 100));
        discAmt = baseAmount - taxable;
    }

    const unitRate = baseAmount / qty;

    tr.querySelector('.row-rate').value = unitRate.toFixed(2);
    tr.querySelector('.row-disc-amt').value = discAmt.toFixed(2);
    tr.querySelector('.row-taxable-amt').value = taxable.toFixed(2);
    tr.querySelector('.row-tax-disp').textContent = '<?php echo cur_symbol(); ?> ' + taxAmt.toFixed(2);
    tr.querySelector('.row-tax-amt').value = taxAmt.toFixed(2);

    calculateAll();
}

function calculateReverseDiscount(tr) {
    const qty = parseFloat(tr.querySelector('.row-qty').value) || 0;
    const rate = parseFloat(tr.querySelector('.row-rate').value) || 0;
    const discAmt = parseFloat(tr.querySelector('.row-disc-amt').value) || 0;
    const taxRate = parseFloat(tr.querySelector('.row-tax-rate').value) || 0;

    const baseAmount = qty * rate;
    const discPct = baseAmount > 0 ? (discAmt / baseAmount) * 100 : 0;

    tr.querySelector('.row-disc-pct').value = discPct.toFixed(2);

    const taxable = Math.max(0, baseAmount - discAmt);
    const taxAmt = taxable * (taxRate / 100);
    const rowTotal = taxable + taxAmt;

    tr.querySelector('.row-taxable-amt').value = taxable.toFixed(2);
    tr.querySelector('.row-tax-disp').textContent = '<?php echo cur_symbol(); ?> ' + taxAmt.toFixed(2);
    tr.querySelector('.row-tax-amt').value = taxAmt.toFixed(2);
    tr.querySelector('.row-total-amt').value = rowTotal.toFixed(2);

    calculateAll();
}

function removeItemRow(idx) {
    const row = document.getElementById('item_row_' + idx);
    if (row && document.querySelectorAll('#itemsTableBody tr').length > 1) {
        row.remove();
        document.querySelectorAll('#itemsTableBody tr').forEach((r, i) => {
            r.querySelector('.row-idx').textContent = i + 1;
        });
        calculateAll();
    }
}

// -------------------------------------------------------------
// REFUND PAYMENT ROW & ACCOUNT / BANK POPULATION
// -------------------------------------------------------------

function calculateAll() {
    const posVal = (document.getElementById('placeOfSupplyInput')?.value || '').trim();
    const isInter = (posVal.toLowerCase() !== COMPANY_STATE.toLowerCase());

    let sumTaxable = 0;
    let sumDiscount = 0;
    let sumTax = 0;
    let sumCgst = 0;
    let sumSgst = 0;
    let sumIgst = 0;
    let sumGrand = 0;

    document.querySelectorAll('#itemsTableBody tr').forEach(tr => {
        const taxable = parseFloat(tr.querySelector('.row-taxable-amt')?.value) || 0;
        const discAmt = parseFloat(tr.querySelector('.row-disc-amt')?.value) || 0;
        const taxRate = parseFloat(tr.querySelector('.row-tax-rate')?.value) || 0;
        const taxAmt = parseFloat(tr.querySelector('.row-tax-amt')?.value) || 0;
        const rowTotal = parseFloat(tr.querySelector('.row-total-amt')?.value) || 0;

        if (isInter) {
            tr.querySelector('.row-igst-rate').value = taxRate;
            tr.querySelector('.row-igst-amt').value = taxAmt.toFixed(2);
            tr.querySelector('.row-cgst-amt').value = '0.00';
            tr.querySelector('.row-sgst-amt').value = '0.00';
            sumIgst += taxAmt;
        } else {
            const halfRate = taxRate / 2;
            const halfAmt = taxAmt / 2;
            tr.querySelector('.row-cgst-rate').value = halfRate;
            tr.querySelector('.row-cgst-amt').value = halfAmt.toFixed(2);
            tr.querySelector('.row-sgst-rate').value = halfRate;
            tr.querySelector('.row-sgst-amt').value = halfAmt.toFixed(2);
            tr.querySelector('.row-igst-amt').value = '0.00';
            sumCgst += halfAmt;
            sumSgst += halfAmt;
        }

        sumTaxable += taxable;
        sumDiscount += discAmt;
        sumTax += taxAmt;
        sumGrand += rowTotal;
    });

    const roundOffInput = document.getElementById('roundOffInput');
    const autoRoundCheck = document.getElementById('autoRoundOffCheck');

    let roundOff = 0;

    if (autoRoundCheck && autoRoundCheck.checked) {
        if (roundOffInput) roundOffInput.readOnly = true;

        // Decimal remainder with 2-digit precision
        const decimalPart = +(sumGrand - Math.floor(sumGrand)).toFixed(2);

        // If decimal > 0.60 round up; otherwise round down to the same whole number
        const targetTotal = decimalPart > 0.60 ? Math.ceil(sumGrand) : Math.floor(sumGrand);

        roundOff = +(targetTotal - sumGrand).toFixed(2);

        if (roundOffInput) {
            roundOffInput.value = roundOff.toFixed(2);
        }
    } else {
        if (roundOffInput) roundOffInput.readOnly = false;
        roundOff = parseFloat(roundOffInput?.value) || 0;
    }

    const finalTotal = +(sumGrand + roundOff).toFixed(2);

    // Credit notes stay OPEN unless settled by linking existing Pay-Out disbursements
    // or paying out cash/bank directly right now.
    const totalLinkedPayOuts = getCurrentlyAllocatedPayOuts();
    const totalDirectPayouts = Array.from(document.querySelectorAll('#payoutsTableBody .payout-amt'))
        .reduce((sum, inp) => sum + (parseFloat(inp.value) || 0), 0);
    const unadjustedBalance = Math.max(0, finalTotal - totalLinkedPayOuts - totalDirectPayouts);

    const totLinkEl = document.getElementById('totalLinkedDisplay');
    const unusedEl = document.getElementById('unusedAmountDisplay');
    const cnTotalEl = document.getElementById('displayCreditNoteTotal');
    if (totLinkEl) totLinkEl.textContent = totalLinkedPayOuts.toFixed(2);
    if (unusedEl) unusedEl.textContent = unadjustedBalance.toFixed(2);
    if (cnTotalEl) cnTotalEl.textContent = finalTotal.toFixed(2);

    // Update Table Footer Totals
    const elColTaxable = document.getElementById('colTotalTaxable');
    const elColDiscount = document.getElementById('colTotalDiscount');
    const elColTax = document.getElementById('colTotalTax');
    const elColTotal = document.getElementById('colGrandTotal');

    if (elColTaxable) elColTaxable.textContent = '<?php echo cur_symbol(); ?> ' + sumTaxable.toFixed(2);
    if (elColDiscount) elColDiscount.textContent = '<?php echo cur_symbol(); ?> ' + sumDiscount.toFixed(2);
    if (elColTax) elColTax.textContent = '<?php echo cur_symbol(); ?> ' + sumTax.toFixed(2);
    if (elColTotal) elColTotal.textContent = '<?php echo cur_symbol(); ?> ' + sumGrand.toFixed(2);

    // Update Summary Card Values
    const dTaxable = document.getElementById('dispTaxable');
    const dDiscount = document.getElementById('dispDiscount');
    const dCgst = document.getElementById('dispCgst');
    const dSgst = document.getElementById('dispSgst');
    const dIgst = document.getElementById('dispIgst');
    const dFinal = document.getElementById('dispFinalTotal');
    const dBal = document.getElementById('dispBalance');

    if (dTaxable) dTaxable.textContent = '<?php echo cur_symbol(); ?> ' + sumTaxable.toFixed(2);
    if (dDiscount) dDiscount.textContent = '- <?php echo cur_symbol(); ?> ' + sumDiscount.toFixed(2);
    if (dCgst) dCgst.textContent = '<?php echo cur_symbol(); ?> ' + sumCgst.toFixed(2);
    if (dSgst) dSgst.textContent = '<?php echo cur_symbol(); ?> ' + sumSgst.toFixed(2);
    if (dIgst) dIgst.textContent = '<?php echo cur_symbol(); ?> ' + sumIgst.toFixed(2);
    if (dFinal) dFinal.textContent = '<?php echo cur_symbol(); ?> ' + finalTotal.toFixed(2);
    if (dBal) dBal.textContent = '<?php echo cur_symbol(); ?> ' + unadjustedBalance.toFixed(2);

    // Update Hidden Form Inputs
    const subInp = document.getElementById('subtotalInput');
    const discInp = document.getElementById('discountInput');
    const taxInp = document.getElementById('taxInput');
    const cgstInp = document.getElementById('cgstInput');
    const sgstInp = document.getElementById('sgstInput');
    const igstInp = document.getElementById('igstInput');
    const grandInp = document.getElementById('grandTotalInput');

    if (subInp) subInp.value = sumTaxable.toFixed(2);
    if (discInp) discInp.value = sumDiscount.toFixed(2);
    if (taxInp) taxInp.value = sumTax.toFixed(2);
    if (cgstInp) cgstInp.value = sumCgst.toFixed(2);
    if (sgstInp) sgstInp.value = sumSgst.toFixed(2);
    if (igstInp) igstInp.value = sumIgst.toFixed(2);
    if (grandInp) grandInp.value = finalTotal.toFixed(2);
}
</script>
