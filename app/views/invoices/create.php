<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div><strong>Could not save invoice:</strong> <?php echo htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>
<form method="POST" action="<?php echo APP_URL; ?>/invoices/create" id="invoiceForm">
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?php echo APP_URL; ?>/invoices" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
        <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-primary">TAX INVOICE</h3>
        <button type="submit" class="btn btn-success btn-lg px-4 shadow-sm">
            <i class="bi bi-save me-1"></i> Save Tax Invoice
        </button>
    </div>

    <!-- Top Details Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left: Party Selection & Details -->
                <div class="col-md-5 border-end">
                    <label class="form-label fw-bold">Customer / Party <span class="text-danger">*</span></label>
                    <select name="party_id" id="partySelect" class="form-select" required>
                        <option value="">-- Search & Select Party --</option>
                        <?php foreach ($parties as $p): ?>
                            <option value="<?php echo $p['party_id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                    data-state="<?php echo htmlspecialchars($p['state']); ?>"
                                    data-gstin="<?php echo htmlspecialchars($p['gstin'] ?? ''); ?>"
                                    data-address="<?php echo htmlspecialchars($p['address'] ?? ''); ?>"
                                    data-shipping-address="<?php echo htmlspecialchars($p['shipping_address'] ?? $p['address'] ?? ''); ?>"
                                    data-phone="<?php echo htmlspecialchars($p['phone'] ?? ''); ?>"
                                    <?php echo (isset($duplicateData) && $duplicateData['party_id'] == $p['party_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?> <?php echo !empty($p['business_name']) ? ' (' . htmlspecialchars($p['business_name']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div id="partyInfoBox" class="mt-3 p-3 bg-light rounded border d-none">
                        <small class="d-block text-muted"><strong>Billing Address:</strong> <span id="partyAddressText">-</span></small>
                        <small class="d-block text-muted"><strong>Shipping Address:</strong> <span id="partyShippingAddressText">-</span></small>
                        <small class="d-block text-muted"><strong>GSTIN / PAN:</strong> <span id="partyGstinText">-</span></small>
                        <small class="d-block text-muted"><strong>State:</strong> <span id="partyStateText">-</span></small>
                    </div>
                </div>

                <!-- Right: Invoice Metadata -->
                <div class="col-md-7">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control fw-bold text-primary" value="<?php echo htmlspecialchars($invoice_number); ?>" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Invoice Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <?php echo sys_time_badge('System Date & Time'); ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>" required>
                        </div>
                            <div class="col-md-6">
                                    <label class="form-label fw-bold">Place of Supply (State) <span class="text-danger">*</span></label>
                                    <input type="text" name="place_of_supply" id="placeOfSupplyInput" class="form-control bg-light fw-bold" value="<?php echo htmlspecialchars($settings['company_state'] ?? 'Kerala'); ?>" required readonly tabindex="-1">
                            </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">GST Tax Type</label>
                            <div class="form-control bg-light text-primary fw-bold" id="gstTypeBadge">
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
            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-cart3 me-2"></i>Invoice Line Items</h6>
            <button type="button" class="btn btn-sm btn-primary" id="btnAddItemRow">
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
                            <th>Description</th>
                            <th width="90px">Qty</th>
                            <th width="110px">Units</th>
                            <th width="130px">Rate (<?php echo cur_symbol(); ?>)</th>
                            <th width="100px">Disc %</th>
                            <th width="110px">Disc (<?php echo cur_symbol(); ?>)</th>
                            <th width="90px" class="text-center">Tax %</th>
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
        <td></td>
        <td class="text-end text-primary" id="colTotalTax"><?php echo cur_symbol(); ?> 0.00</td>
        <td class="text-end text-success fs-6" id="colGrandTotal"><?php echo cur_symbol(); ?> 0.00</td>
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
        <!-- Left: Payment Options & Link Amount Table -->
        <div class="col-md-7">
            
            <!-- Link Payment Trigger Button -->
            <!-- Link Payment Trigger Banner (Hidden by default until customer is selected) -->
            <div class="p-3 bg-light rounded border mb-3 d-none justify-content-between align-items-center" id="linkBannerContainer">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-link-45deg fs-5 text-primary me-1"></i>Link Payment to Transaction</h6>
                    <small class="text-muted" id="linkBannerSubtext">Offset this invoice against unused Pay-In advance receipts from this customer.</small>
                </div>
                <button type="button" id="btnToggleLinkPayIn" class="btn btn-outline-primary fw-bold" disabled>
                    <i class="bi bi-link me-1"></i> Link Payment
                </button>
            </div>

            <!-- Link Payment Table Section (7 Columns) -->
            <div id="linkPayInSection" class="card border-primary mb-3 d-none">
                <div class="card-header bg-primary bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">
                        <i class="bi bi-list-check me-1"></i> Unused Pay-Ins for <span id="selectedPartyName" class="text-dark"></span>
                    </span>
                    <span class="badge bg-primary">Invoice Total: <?php echo cur_symbol(); ?><span id="displayInvoiceTotal">0.00</span></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover align-middle mb-0">
                            <thead class="table-light text-center small fw-bold">
                                <tr>
                                    <th width="50px">Select</th>
                                    <th width="110px">Date</th>
                                    <th width="90px">Type</th>
                                    <th>Pay In #</th>
                                    <th class="text-end" width="120px">Total (<?php echo cur_symbol(); ?>)</th>
                                    <th class="text-end" width="130px">Balance (<?php echo cur_symbol(); ?>)</th>
                                    <th class="text-end" width="150px">Linked Amount (<?php echo cur_symbol(); ?>)</th>
                                </tr>
                            </thead>
                            <tbody id="payInTableBody">
                                <tr><td colspan="7" class="text-center py-3 text-muted">Select a party to view available Pay-Ins.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted small">Total Linked: <?php echo cur_symbol(); ?><strong id="totalLinkedDisplay" class="text-success">0.00</strong></span>
                    <h6 class="mb-0 fw-bold">Unused / Remaining Amount: <?php echo cur_symbol(); ?><span id="unusedAmountDisplay" class="text-danger">0.00</span></h6>
                </div>
            </div>

            <!-- Multiple Payment Methods Allocation -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-credit-card me-1"></i> Direct Payment (Add Cash / Bank)</span>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnAddPaymentRow">
                        <i class="bi bi-plus-circle me-1"></i> Add Payment
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0" id="paymentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Account / Bank</th>
                                <th>Payment Method</th>
                                <th width="150px">Amount (<?php echo cur_symbol(); ?>)</th>
                                <th width="40px"></th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTableBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label fw-bold">Invoice Terms / Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Custom notes for this invoice..."><?php echo htmlspecialchars($settings['invoice_footer_notes'] ?? ''); ?></textarea>
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
                            <td class="fs-5 fw-bold text-dark">Total Amount:</td>
                            <td class="text-end fs-5 fw-bold text-success" id="dispFinalTotal"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Direct Received:</td>
                            <td class="text-end text-success fw-bold" id="dispReceived"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Linked Pay-In:</td>
                            <td class="text-end text-info fw-bold" id="dispAdvanceLinked"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fs-6 fw-bold text-danger">Balance Due:</td>
                            <td class="text-end fs-6 fw-bold text-danger" id="dispBalance"><?php echo cur_symbol(); ?> 0.00</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const ITEM_MASTER = <?php echo json_encode($items); ?>;
const UNIT_MASTER = <?php echo json_encode($units); ?>;
const TAX_MASTER = <?php echo json_encode($taxes); ?>;
const BANK_MASTER = <?php echo json_encode($banks); ?>;
const COMPANY_STATE = "<?php echo addslashes($settings['company_state'] ?? 'Kerala'); ?>";
let itemRowIndex = 0;
let paymentRowIndex = 0;

// -------------------------------------------------------------
// QUANTITY-BY-UNIT VALIDATION (mirrors app/core/Validation.php)
// Weight-type units (KG etc.) may be fractional; every other unit
// (pieces, numbers, boxes, service, time...) must be a whole number.
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
    addPaymentRow();

    const invoiceFormEl = document.getElementById('invoiceForm');
    if (invoiceFormEl) {
        invoiceFormEl.addEventListener('submit', function (e) {
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
    const btnToggleLinkPayIn = document.getElementById('btnToggleLinkPayIn');
    const linkBannerContainer = document.getElementById('linkBannerContainer');
    const linkBannerSubtext = document.getElementById('linkBannerSubtext');
    const linkSection = document.getElementById('linkPayInSection');
    const payInBody = document.getElementById('payInTableBody');
    const selectedPartyName = document.getElementById('selectedPartyName');

    // Party Selection Handler
    if (partySelect) {
        partySelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const partyId = this.value;

            // Hide link banner and link table immediately on every party change
            if (linkBannerContainer) {
                linkBannerContainer.classList.add('d-none');
                linkBannerContainer.classList.remove('d-flex');
            }
            if (linkSection) linkSection.classList.add('d-none');
            if (btnToggleLinkPayIn) btnToggleLinkPayIn.disabled = true;

            if (!partyId) {
                const infoBox = document.getElementById('partyInfoBox');
                if (infoBox) infoBox.classList.add('d-none');
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

            if (selectedPartyName) selectedPartyName.textContent = opt.getAttribute('data-name') || opt.text;
            loadPartyPayIns(partyId);
        });
    }

    if (btnToggleLinkPayIn && linkSection) {
        btnToggleLinkPayIn.addEventListener('click', function () {
            linkSection.classList.toggle('d-none');
            calculateAll();
        });
    }

    const posEl = document.getElementById('placeOfSupplyInput');
    if (posEl) posEl.addEventListener('input', updateGstType);

    const btnAddItm = document.getElementById('btnAddItemRow');
    if (btnAddItm) btnAddItm.addEventListener('click', addItemRow);

    const btnAddPay = document.getElementById('btnAddPaymentRow');
    if (btnAddPay) btnAddPay.addEventListener('click', addPaymentRow);

    const roundEl = document.getElementById('roundOffInput');
    if (roundEl) roundEl.addEventListener('input', calculateAll);

    // Auto Round-Off Checkbox Listener
    const autoRoundCheck = document.getElementById('autoRoundOffCheck');
    if (autoRoundCheck) {
        autoRoundCheck.addEventListener('change', function () {
            if (!this.checked && roundEl) {
                roundEl.value = '0.00';
            }
            calculateAll();
        });
    }

    // Fetch and populate unused Pay-Ins
    function loadPartyPayIns(partyId) {
        if (!payInBody) return;

        fetch('<?php echo APP_URL; ?>/invoices/getPartyUnusedPayInsAjax/' + partyId)
            .then(res => res.json())
            .then(payIns => {
                payInBody.innerHTML = '';

                if (!payIns || payIns.length === 0) {
                    if (linkBannerContainer) {
                        linkBannerContainer.classList.add('d-none');
                        linkBannerContainer.classList.remove('d-flex');
                    }
                    if (linkSection) linkSection.classList.add('d-none');
                    if (btnToggleLinkPayIn) btnToggleLinkPayIn.disabled = true;
                    calculateAll();
                    return;
                }

                if (linkBannerContainer) {
                    linkBannerContainer.classList.remove('d-none');
                    linkBannerContainer.classList.add('d-flex');
                }
                if (btnToggleLinkPayIn) btnToggleLinkPayIn.disabled = false;

                payIns.forEach(p => {
                    const row = document.createElement('tr');
                    const bal = parseFloat(p.balance_amount);
                    const tot = parseFloat(p.total_amount);

                    row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input payin-check" data-id="${p.payment_id}" data-orig-bal="${bal}">
                        </td>
                        <td class="text-center">${p.payment_date}</td>
                        <td class="text-center"><span class="badge bg-success">Pay In</span></td>
                        <td><strong>${p.pay_in_no}</strong></td>
                        <td class="text-end"><?php echo cur_symbol(); ?> ${tot.toFixed(2)}</td>
                        <td class="text-end text-danger fw-bold payin-bal-display"><?php echo cur_symbol(); ?> ${bal.toFixed(2)}</td>
                        <td>
                            <input type="number" step="0.01" name="linked_pay_in[${p.payment_id}]" 
                                   class="form-control form-control-sm text-end payin-alloc-input" 
                                   data-orig-bal="${bal}" value="0.00" disabled>
                        </td>
                    `;
                    payInBody.appendChild(row);
                });

                attachCheckboxListeners();
                calculateAll();
            });
    }

    function attachCheckboxListeners() {
        document.querySelectorAll('.payin-check').forEach(chk => {
            chk.addEventListener('change', function () {
                const row = this.closest('tr');
                const allocInput = row.querySelector('.payin-alloc-input');
                const origBal = parseFloat(this.getAttribute('data-orig-bal')) || 0;

                if (this.checked) {
                    allocInput.disabled = false;
                    const finalInvoiceTotal = parseFloat(document.getElementById('grandTotalInput').value) || 0;
                    const currentlyAllocatedOther = getCurrentlyAllocatedPayIns(allocInput);
                    const remainingInvoiceNeeded = Math.max(0, finalInvoiceTotal - currentlyAllocatedOther);

                    allocInput.value = Math.min(origBal, remainingInvoiceNeeded).toFixed(2);
                } else {
                    allocInput.disabled = true;
                    allocInput.value = '0.00';
                }

                updatePayInBalanceRow(row);
                calculateAll();
            });
        });

        document.querySelectorAll('.payin-alloc-input').forEach(inp => {
            inp.addEventListener('input', function () {
                const row = this.closest('tr');
                const origBal = parseFloat(this.getAttribute('data-orig-bal')) || 0;
                let val = parseFloat(this.value) || 0;

                if (val > origBal) {
                    this.value = origBal.toFixed(2);
                }
                updatePayInBalanceRow(row);
                calculateAll();
            });
        });
    }

    function updatePayInBalanceRow(row) {
        const allocInput = row.querySelector('.payin-alloc-input');
        const balDisp = row.querySelector('.payin-bal-display');
        const origBal = parseFloat(allocInput.getAttribute('data-orig-bal')) || 0;
        const linkedVal = allocInput.disabled ? 0 : (parseFloat(allocInput.value) || 0);
        const remBal = Math.max(0, origBal - linkedVal);

        balDisp.textContent = '<?php echo cur_symbol(); ?> ' + remBal.toFixed(2);
    }

    function getCurrentlyAllocatedPayIns(excludeInput = null) {
        let sum = 0;
        document.querySelectorAll('.payin-alloc-input').forEach(inp => {
            if (!inp.disabled && inp !== excludeInput) {
                sum += parseFloat(inp.value) || 0;
            }
        });
        return sum;
    }
});

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

function addItemRow() {
    itemRowIndex++;
    const tbody = document.getElementById('itemsTableBody');
    const tr = document.createElement('tr');
    tr.id = 'item_row_' + itemRowIndex;

    let itemOptions = '<option value="">-- Choose / Search Item --</option>';
    ITEM_MASTER.forEach(itm => {
        itemOptions += `<option value="${itm.name}" data-desc="${itm.description || ''}" data-price="${itm.price}" data-unit="${itm.unit || 'PCS'}" data-tax="${itm.tax_rate}">${itm.name}</option>`;
    });

    let unitOptions = '';
    UNIT_MASTER.forEach(u => {
        unitOptions += `<option value="${u.unit_symbol}">${u.unit_symbol} (${u.unit_name})</option>`;
    });

    tr.innerHTML = `
        <td class="text-center fw-bold row-idx">${itemRowIndex}</td>
        <td>
            <select name="items[${itemRowIndex}][item_name]" class="form-select form-select-sm item-select" required>
                ${itemOptions}
            </select>
        </td>
        <td><input type="text" name="items[${itemRowIndex}][description]" class="form-control form-control-sm row-desc" placeholder="Description"></td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][quantity]" class="form-control form-control-sm text-center row-qty" value="1.00" required></td>
        <td>
            <select name="items[${itemRowIndex}][unit]" class="form-select form-select-sm row-unit">
                ${unitOptions}
            </select>
        </td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][unit_price]" class="form-control form-control-sm text-end row-rate" value="0.00" required></td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][discount_percent]" class="form-control form-control-sm text-center row-disc-pct" value="0.00"></td>
        <td><input type="number" step="0.01" name="items[${itemRowIndex}][discount_amount]" class="form-control form-control-sm text-end row-disc-amt" value="0.00"></td>
        <td class="text-center">
            <span class="badge row-tax-badge badge-accent">18%</span>
        </td>
        <td class="text-end">
            <span class="row-tax-disp text-primary fw-bold"><?php echo cur_symbol(); ?> 0.00</span>
            <input type="hidden" name="items[${itemRowIndex}][tax_rate]" class="row-tax-rate" value="18">
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
            <input type="number" step="0.01" name="items[${itemRowIndex}][total_amount]" class="form-control form-control-sm text-end fw-bold text-success row-total-amt" value="0.00" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeItemRow(${itemRowIndex})"><i class="bi bi-trash"></i></button>
        </td>
    `;

    tbody.appendChild(tr);

    // Auto-fill details on Item select
    const sel = tr.querySelector('.item-select');
    sel.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            const tax = parseFloat(opt.getAttribute('data-tax') || 18);
            tr.querySelector('.row-desc').value = opt.getAttribute('data-desc') || '';
            tr.querySelector('.row-rate').value = parseFloat(opt.getAttribute('data-price') || 0).toFixed(2);
            tr.querySelector('.row-unit').value = opt.getAttribute('data-unit') || 'PCS';
            tr.querySelector('.row-tax-rate').value = tax;
            tr.querySelector('.row-tax-badge').textContent = tax + '%';
        }
        enforceQtyRule(tr);
        calculateForward(tr);
    });

    // Re-check whole-number/decimal rule whenever the unit changes
    tr.querySelector('.row-unit').addEventListener('change', function () {
        enforceQtyRule(tr);
        calculateForward(tr);
    });

    // Reverse calculation when Total Amount is edited
    tr.querySelector('.row-total-amt').addEventListener('input', function () {
        calculateReverse(tr);
    });

    // Flat Discount <?php echo cur_symbol(); ?> manual edit
    tr.querySelector('.row-disc-amt').addEventListener('input', function () {
        calculateReverseDiscount(tr);
    });

    // Forward calculation when Qty, Rate, or Disc % is edited
    tr.querySelectorAll('.row-qty, .row-rate, .row-disc-pct').forEach(inp => {
        inp.addEventListener('input', function () {
            if (inp.classList.contains('row-qty')) enforceQtyRule(tr);
            calculateForward(tr);
        });
    });

    enforceQtyRule(tr);
    calculateForward(tr);
}

// 1. Forward: Qty/Rate/Disc% -> Tax & Total
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

// 2. Reverse: Total -> Taxable, Tax Amount, Discount, and Rate
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

// 3. Flat Discount <?php echo cur_symbol(); ?> -> Disc %
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
        // Re-index displayed row numbers
        document.querySelectorAll('#itemsTableBody tr').forEach((r, i) => {
            r.querySelector('.row-idx').textContent = i + 1;
        });
        calculateAll();
    }
}

// -------------------------------------------------------------
// PAYMENT ROW CREATION & OVERALL GRAND TOTAL CALCULATION
// -------------------------------------------------------------

function addPaymentRow() {
    paymentRowIndex++;
    const tbody = document.getElementById('paymentsTableBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'payment_row_' + paymentRowIndex;

    let bankOpts = '';
    BANK_MASTER.forEach(b => {
        bankOpts += `<option value="${b.bank_id}">${b.bank_name} - ${b.account_number}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="payments[${paymentRowIndex}][bank_id]" class="form-select form-select-sm" required>
                ${bankOpts}
            </select>
        </td>
        <td>
            <select name="payments[${paymentRowIndex}][payment_method]" class="form-select form-select-sm">
                <option value="Bank Account">Bank Transfer / IMPS</option>
                <option value="UPI">UPI / QR Code</option>
                <option value="Credit Card">Credit / Debit Card</option>
                <option value="Cheque">Cheque</option>
                <option value="Cash">Cash</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="payments[${paymentRowIndex}][amount]" class="form-control form-control-sm text-end fw-bold pay-amt" value="0.00" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove(); calculateAll();"><i class="bi bi-x-circle"></i></button>
        </td>
    `;

    tbody.appendChild(tr);
    tr.querySelector('.pay-amt').addEventListener('input', calculateAll);
    calculateAll();
}

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
    const autoRoundOffCheck = document.getElementById('autoRoundOffCheck');

    let roundOff = 0;

    if (autoRoundOffCheck && autoRoundOffCheck.checked) {
        if (roundOffInput) roundOffInput.readOnly = true;

        // Extract fraction with two-decimal precision
        const decimalPart = +(sumGrand - Math.floor(sumGrand)).toFixed(2);

        // > 0.60 rounds up to next integer; <= 0.60 rounds down to same integer
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

    // Sum Linked Pay-Ins
    let totalLinkedPayIns = 0;
    document.querySelectorAll('.payin-alloc-input').forEach(inp => {
        if (!inp.disabled) {
            totalLinkedPayIns += parseFloat(inp.value) || 0;
        }
    });

    // Sum Direct Payments
    let totalPaid = 0;
    document.querySelectorAll('.pay-amt').forEach(p => {
        totalPaid += parseFloat(p.value) || 0;
    });

    const balanceDue = Math.max(0, finalTotal - (totalPaid + totalLinkedPayIns));
    const unusedRemaining = Math.max(0, finalTotal - totalLinkedPayIns);

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
    const dRecv = document.getElementById('dispReceived');
    const dAdv = document.getElementById('dispAdvanceLinked');
    const dBal = document.getElementById('dispBalance');

    if (dTaxable) dTaxable.textContent = '<?php echo cur_symbol(); ?> ' + sumTaxable.toFixed(2);
    if (dDiscount) dDiscount.textContent = '- <?php echo cur_symbol(); ?> ' + sumDiscount.toFixed(2);
    if (dCgst) dCgst.textContent = '<?php echo cur_symbol(); ?> ' + sumCgst.toFixed(2);
    if (dSgst) dSgst.textContent = '<?php echo cur_symbol(); ?> ' + sumSgst.toFixed(2);
    if (dIgst) dIgst.textContent = '<?php echo cur_symbol(); ?> ' + sumIgst.toFixed(2);
    if (dFinal) dFinal.textContent = '<?php echo cur_symbol(); ?> ' + finalTotal.toFixed(2);
    if (dRecv) dRecv.textContent = '<?php echo cur_symbol(); ?> ' + totalPaid.toFixed(2);
    if (dAdv) dAdv.textContent = '<?php echo cur_symbol(); ?> ' + totalLinkedPayIns.toFixed(2);
    if (dBal) dBal.textContent = '<?php echo cur_symbol(); ?> ' + balanceDue.toFixed(2);

    // Update Linked Pay-In Banner/Modal displays
    const dispInvEl = document.getElementById('displayInvoiceTotal');
    if (dispInvEl) dispInvEl.textContent = finalTotal.toFixed(2);
    const totLinkEl = document.getElementById('totalLinkedDisplay');
    if (totLinkEl) totLinkEl.textContent = totalLinkedPayIns.toFixed(2);
    const unuseEl = document.getElementById('unusedAmountDisplay');
    if (unuseEl) unuseEl.textContent = unusedRemaining.toFixed(2);

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