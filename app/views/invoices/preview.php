<div class="d-print-none mb-3 d-flex justify-content-between align-items-center">
    <a href="<?php echo APP_URL; ?>/invoices" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Invoices
    </a>
    <div class="d-flex gap-2">
        <a href="<?php echo APP_URL; ?>/invoices/create" class="btn btn-outline-primary">
            <i class="bi bi-plus-lg me-1"></i> Create New Invoice
        </a>
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i> Print / Download PDF
        </button>
    </div>
</div>

<?php
// Determine items array safely
$itemList = !empty($invoiceItems) ? $invoiceItems : (!empty($items) ? $items : []);

$companyState = $settings['company_state'] ?? 'Kerala';
$pos = $invoice['place_of_supply'] ?? $companyState;
$isInterstate = (bool)($invoice['is_interstate'] ?? (strcasecmp(trim($companyState), trim($pos)) !== 0));

// 1. Calculate Advance Linked & Direct Paid
$directPaid = (float)($invoice['paid_amount'] ?? 0);
$advanceLinked = (float)($invoice['advance_linked_amount'] ?? 0);

// Only compute from $allocations if BOTH master values are empty/zero
if ($directPaid == 0 && $advanceLinked == 0 && !empty($allocations)) {
    foreach ($allocations as $al) {
        $amt = (float)($al['allocated_amount'] ?? 0);
        $type = strtoupper($al['allocation_type'] ?? $al['payment_type'] ?? '');
        
        // Check explicitly for PAY_IN / ADVANCE allocations
        if ($type === 'PAY_IN' || $type === 'ADVANCE' || !empty($al['pay_in_id'])) {
            $advanceLinked += $amt;
        } else {
            $directPaid += $amt;
        }
    }
}
$totalSettled = $directPaid + $advanceLinked;
$totalAmount = (float)($invoice['total_amount'] ?? 0);

if ($totalAmount > 0 && ($totalAmount - $totalSettled) <= 0.005) {
    $status = 'PAID';
    $statusBadge = 'bg-success';
} elseif ($totalSettled > 0.005) {
    $status = 'PARTIALLY_PAID';
    $statusBadge = 'bg-warning text-dark';
} else {
    $status = 'DUE';
    $statusBadge = 'bg-danger';
}

$netBalanceDue = max(0, $totalAmount - $totalSettled);
?>

<div class="card shadow-sm border-0 p-4 p-md-5 bg-white">
    <!-- Header: Company & Invoice Metadata -->
    <div class="row pb-4 border-bottom mb-4 align-items-center">
        <div class="col-sm-7">
            <h3 class="fw-bold text-dark text-uppercase mb-1"><?php echo htmlspecialchars($settings['company_name'] ?? 'LX Accounting'); ?></h3>
            <p class="text-muted small mb-1"><?php echo nl2br(htmlspecialchars($settings['company_address'] ?? '')); ?></p>
            <p class="text-muted small mb-0">
                <strong>GSTIN:</strong> <?php echo htmlspecialchars($settings['company_gstin'] ?? 'N/A'); ?> | 
                <strong>State:</strong> <?php echo htmlspecialchars($companyState); ?>
            </p>
        </div>
        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
            <h2 class="fw-bold text-primary tracking-wide mb-1">TAX INVOICE</h2>
            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
            <small class="text-muted d-block"><strong>Invoice Date:</strong> <?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></small>
            <?php echo sys_time_badge('Recorded On', $invoice['created_at'] ?? null); ?>
            <small class="text-muted d-block"><strong>Due Date:</strong> <?php echo date('d/m/Y', strtotime($invoice['due_date'] ?? $invoice['invoice_date'])); ?></small>
            <div class="mt-2">
                <span class="badge <?php echo $statusBadge; ?> px-3 py-1 fs-6">
                    <?php echo ($status === 'DUE') ? 'PAYMENT DUE' : (($status === 'PAID') ? 'PAID' : 'PARTIALLY PAID'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Customer Information: Billing & Shipping Address -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded border h-100">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1"><i class="bi bi-geo-alt me-1"></i>Billed To</span>
                <strong class="text-dark d-block fs-6"><?php echo htmlspecialchars($invoice['party_name'] ?? 'Walk-in Customer'); ?></strong>
                <?php if (!empty($invoice['business_name'])): ?>
                    <small class="d-block text-muted fw-bold"><?php echo htmlspecialchars($invoice['business_name']); ?></small>
                <?php endif; ?>
                <small class="d-block text-muted mt-1"><?php echo nl2br(htmlspecialchars($invoice['address'] ?? 'N/A')); ?></small>
                <small class="d-block text-muted mt-1"><strong>GSTIN / PAN:</strong> <?php echo htmlspecialchars($invoice['gstin'] ?? 'Unregistered'); ?></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-3 bg-light rounded border h-100">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1"><i class="bi bi-truck me-1"></i>Shipped To</span>
                <strong class="text-dark d-block fs-6"><?php echo htmlspecialchars($invoice['party_name'] ?? 'Walk-in Customer'); ?></strong>
                <small class="d-block text-muted mt-1">
                    <?php echo nl2br(htmlspecialchars(!empty($invoice['shipping_address']) ? $invoice['shipping_address'] : ($invoice['address'] ?? 'Same as billing address'))); ?>
                </small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-3 bg-light rounded border h-100">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1"><i class="bi bi-info-circle me-1"></i>Supply Details</span>
                <small class="d-block text-muted"><strong>Place of Supply:</strong> <?php echo htmlspecialchars($pos); ?></small>
                <small class="d-block text-muted"><strong>GST Type:</strong> 
                    <span class="badge <?php echo $isInterstate ? 'bg-warning text-dark' : 'bg-primary'; ?>">
                        <?php echo $isInterstate ? 'Interstate (IGST)' : 'Intrastate (CGST + SGST)'; ?>
                    </span>
                </small>
                <?php if (!empty($invoice['phone'])): ?>
                    <small class="d-block text-muted mt-1"><strong>Phone:</strong> <?php echo htmlspecialchars($invoice['phone']); ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 11-Column Line Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark small text-center">
                <tr>
                    <th width="40px">#</th>
                    <th>Item &amp; Description</th>
                    <th width="90px">Qty</th>
                    <th width="80px">Unit</th>
                    <th width="110px" class="text-end">Rate (<?php echo cur_symbol(); ?>)</th>
                    <th width="80px" class="text-center">Disc %</th>
                    <th width="100px" class="text-end">Disc (<?php echo cur_symbol(); ?>)</th>
                    <th width="110px" class="text-end">Taxable (<?php echo cur_symbol(); ?>)</th>
                    <th width="80px" class="text-center">Tax %</th>
                    <th width="110px" class="text-end">Tax Amt (<?php echo cur_symbol(); ?>)</th>
                    <th width="130px" class="text-end">Total (<?php echo cur_symbol(); ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($itemList)): ?>
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">No line items attached to this invoice.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    $calcTaxableSum = 0;
                    $calcDiscountSum = 0;
                    $calcTaxSum = 0;
                    $calcTotalSum = 0;

                    foreach ($itemList as $item): 
                        $qty = (float)($item['quantity'] ?? 1);
                        $rate = (float)($item['unit_price'] ?? 0);
                        $discPct = (float)($item['discount_percent'] ?? 0);
                        $taxRate = (float)($item['tax_rate'] ?? 18);
                        
                        // Calculated fallback values
                        $baseVal = $qty * $rate;
                        $discAmt = (float)($item['discount_amount'] ?? ($baseVal * ($discPct / 100)));
                        $taxable = (float)($item['taxable_amount'] ?? ($baseVal - $discAmt));
                        $taxAmt = (float)($item['tax_amount'] ?? ($taxable * ($taxRate / 100)));
                        $itemTotal = (float)($item['total_amount'] ?? ($taxable + $taxAmt));

                        $calcTaxableSum += $taxable;
                        $calcDiscountSum += $discAmt;
                        $calcTaxSum += $taxAmt;
                        $calcTotalSum += $itemTotal;
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?php echo $idx++; ?></td>
                        <td>
                            <strong class="text-dark d-block"><?php echo htmlspecialchars($item['item_name']); ?></strong>
                            <?php if (!empty($item['description'])): ?>
                                <small class="text-muted"><?php echo nl2br(htmlspecialchars($item['description'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo number_format($qty, 2); ?></td>
                        <td class="text-center"><code><?php echo htmlspecialchars($item['unit'] ?? 'PCS'); ?></code></td>
                        <td class="text-end"><?php echo cur_symbol(); ?> <?php echo number_format($rate, 2); ?></td>
                        <td class="text-center"><?php echo $discPct > 0 ? $discPct . '%' : '-'; ?></td>
                        <td class="text-end text-danger"><?php echo $discAmt > 0 ? cur_symbol() . ' ' . number_format($discAmt, 2) : '-'; ?></td>
                        <td class="text-end"><?php echo cur_symbol(); ?> <?php echo number_format($taxable, 2); ?></td>
                        <td class="text-center">
                            <span class="badge badge-accent">
                                <?php echo $taxRate; ?>%
                            </span>
                        </td>
                        <td class="text-end text-primary"><?php echo cur_symbol(); ?> <?php echo number_format($taxAmt, 2); ?></td>
                        <td class="text-end fw-bold text-dark"><?php echo cur_symbol(); ?> <?php echo number_format($itemTotal, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Calculations, Settlement Breakdown & Notes -->
    <div class="row g-4">
        <!-- Left: Allocations & Notes -->
        <div class="col-md-6">
            <?php if (!empty($allocations)): ?>
            <div class="card bg-light border-0 p-3 mb-3">
                <span class="fw-bold small text-uppercase text-dark mb-2 d-block"><i class="bi bi-wallet2 me-1"></i>Settlement / Allocation Details:</span>
                <ul class="list-unstyled mb-0 small">
                   <?php foreach ($allocations as $al): 
    $refNo = trim($al['reference_number'] ?? $al['ref_no'] ?? '');
    // A "direct payment" was created together with the invoice itself
    // (reference always starts with DIRECT-PAY-<invoice_number>). Anything
    // else is an advance Pay-In that existed beforehand and was linked in.
    $isDirect = (stripos($refNo, 'DIRECT-PAY-') === 0);
    $payInNo = trim($al['pay_in_no'] ?? $al['reference_number'] ?? 'Pay-In');
    $method = htmlspecialchars($al['payment_method'] ?? 'Cash/Bank');
?>
    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <span>
            <?php if (!$isDirect): ?>
                <strong class="text-primary"><?php echo htmlspecialchars($payInNo); ?></strong> 
                <span class="text-muted">(Advance Linked)</span>
                <?php if (!empty($refNo) && $refNo !== $payInNo): ?>
                    <span class="badge ms-1 bg-success">
                        Ref: <?php echo htmlspecialchars($refNo); ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <strong class="text-dark">Direct Payment</strong> <span class="text-muted">(<?php echo $method; ?>)</span>
                <?php if (!empty($refNo)): ?>
                    <span class="badge ms-1 bg-success">
                        Ref: <?php echo htmlspecialchars($refNo); ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </span>
        <span class="fw-bold text-success"><?php echo cur_symbol(); ?> <?php echo number_format((float)($al['allocated_amount'] ?? 0), 2); ?></span>
    </li>
<?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="p-3 bg-light rounded border">
                <span class="fw-bold small text-uppercase text-dark mb-1 d-block">Terms &amp; Conditions / Notes:</span>
                <p class="text-muted small mb-0"><?php echo nl2br(htmlspecialchars($invoice['notes'] ?? ($settings['invoice_footer_notes'] ?? 'Thank you for your business!'))); ?></p>
            </div>
        </div>

        <!-- Right: Exact Financial Summary -->
        <div class="col-md-6">
            <div class="card bg-light border-0 p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Taxable Value:</td>
                        <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($invoice['taxable_amount'] ?? $calcTaxableSum), 2); ?></td>
                    </tr>
                    <?php 
                    $discVal = (float)($invoice['discount_amount'] ?? $calcDiscountSum);
                    if ($discVal > 0): ?>
                    <tr>
                        <td class="text-muted">Total Discount:</td>
                        <td class="text-end text-danger fw-bold">- <?php echo cur_symbol(); ?> <?php echo number_format($discVal, 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php 
                    $taxVal = (float)($invoice['tax_amount'] ?? $calcTaxSum);
                    if ($isInterstate): ?>
                    <tr>
                        <td class="text-muted">Integrated GST (IGST):</td>
                        <td class="text-end text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($invoice['igst_amount'] ?? $taxVal), 2); ?></td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td class="text-muted">Central GST (CGST):</td>
                        <td class="text-end text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($invoice['cgst_amount'] ?? ($taxVal / 2)), 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">State GST (SGST):</td>
                        <td class="text-end text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($invoice['sgst_amount'] ?? ($taxVal / 2)), 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ((float)($invoice['round_off'] ?? 0) != 0): ?>
                    <tr>
                        <td class="text-muted">Round Off:</td>
                        <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)$invoice['round_off'], 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <tr class="border-top">
                        <td class="fs-5 fw-bold text-dark">Invoice Grand Total:</td>
                        <td class="text-end fs-5 fw-bold text-primary"><?php echo cur_symbol(); ?> <?php echo number_format($totalAmount > 0 ? $totalAmount : $calcTotalSum, 2); ?></td>
                    </tr>

                    <!-- Direct Received & Linked Advance Payments -->
                    <?php if ($directPaid > 0): ?>
                    <tr>
                        <td class="text-muted">Direct Received (Cash/Bank):</td>
                        <td class="text-end text-success fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($directPaid, 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ($advanceLinked > 0): ?>
                    <tr>
                        <td class="text-muted">Pay In Advance (Linked):</td>
                        <td class="text-end text-info fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($advanceLinked, 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <!-- Net Balance Due -->
                    <tr class="border-top">
                        <td class="fs-6 fw-bold text-danger">Balance Due:</td>
                        <td class="text-end fs-6 fw-bold text-danger">
                            <?php echo cur_symbol(); ?> <?php echo number_format($netBalanceDue, 2); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Authorized Signature -->
    <div class="row pt-5 mt-4 border-top align-items-end">
        <div class="col-8">
            <small class="text-muted d-block">This is a computer-generated tax invoice and requires no physical signature.</small>
        </div>
        <div class="col-4 text-center">
            <div style="height: 50px;"></div>
            <span class="d-block border-top pt-1 small fw-bold text-dark">Authorized Signatory</span>
        </div>
    </div>
</div>

<!-- PRINT-ONLY TEMPLATE (Zero ink-heavy backgrounds) -->
<div id="printInvoiceArea">
    <!-- TOP / HEADER SECTION -->
    <div>
        <!-- Company & Invoice Meta Box with Rounded Border -->
        <div style="border: 1.5px solid var(--ui-print-ink); border-radius: 8px; padding: 12px 14px; margin-bottom: 12px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 62%;">
                        <div style="font-size: 16px; font-weight: 800; text-transform: uppercase; margin-bottom: 3px;">
                            <?php echo htmlspecialchars($settings['company_name'] ?? 'LX Accounting'); ?>
                        </div>
                        <div style="font-size: 10.5px; color: var(--ui-print-muted); line-height: 1.4;">
                            <?php echo nl2br(htmlspecialchars($settings['company_address'] ?? '')); ?>
                        </div>
                        <div style="font-size: 10.5px; margin-top: 4px;">
                            <strong>GSTIN:</strong> <?php echo htmlspecialchars($settings['company_gstin'] ?? 'N/A'); ?> &nbsp;|&nbsp; 
                            <strong>State:</strong> <?php echo htmlspecialchars($companyState); ?>
                        </div>
                    </td>
                    <td style="vertical-align: top; text-align: right; width: 38%;">
                        <div style="font-size: 18px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 4px;">TAX INVOICE</div>
                        <div style="font-size: 11px; line-height: 1.5;">
                            <div><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></div>
                            <div><strong>Invoice Date:</strong> <?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></div>
                            <div><strong>Due Date:</strong> <?php echo date('d/m/Y', strtotime($invoice['due_date'] ?? $invoice['invoice_date'])); ?></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Billing, Shipping & Supply Box -->
        <div style="border: 1px solid var(--ui-print-line); border-radius: 6px; padding: 10px 12px; margin-bottom: 14px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 50%; padding-right: 15px;">
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: var(--ui-print-muted); margin-bottom: 2px;">Billed To:</div>
                        <div style="font-size: 12px; font-weight: 700;"><?php echo htmlspecialchars($invoice['party_name'] ?? 'Walk-in Customer'); ?></div>
                        <?php if (!empty($invoice['business_name'])): ?>
                            <div style="font-size: 10.5px;"><?php echo htmlspecialchars($invoice['business_name']); ?></div>
                        <?php endif; ?>
                        <div style="font-size: 10.5px;"><?php echo nl2br(htmlspecialchars($invoice['address'] ?? '')); ?></div>
                        <div style="font-size: 10.5px; margin-top: 2px;"><strong>GSTIN / PAN:</strong> <?php echo htmlspecialchars($invoice['gstin'] ?? 'Unregistered'); ?></div>
                    </td>
                    <td style="vertical-align: top; width: 50%; padding-left: 15px; border-left: 1px solid var(--ui-print-line);">
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: var(--ui-print-muted); margin-bottom: 2px;">Supply / Shipping:</div>
                        <div style="font-size: 10.5px;"><strong>Place of Supply:</strong> <?php echo htmlspecialchars($pos); ?></div>
                        <div style="font-size: 10.5px;"><strong>GST Supply Type:</strong> <?php echo $isInterstate ? 'Interstate (IGST)' : 'Intrastate (CGST + SGST)'; ?></div>
                        <?php if (!empty($invoice['shipping_address']) && $invoice['shipping_address'] !== ($invoice['address'] ?? '')): ?>
                            <div style="font-size: 10.5px; margin-top: 3px;"><strong>Ship To:</strong> <?php echo nl2br(htmlspecialchars($invoice['shipping_address'])); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Line Items Table with Rounded Corners and Solid Black Header -->
        <table class="print-table" style="margin-bottom: 12px;">
            <thead>
                <tr style="text-align: center;">
                    <th style="width: 32px;">#</th>
                    <th style="text-align: left;">Item Description</th>
                    <th style="width: 48px;">Qty</th>
                    <th style="width: 52px;">Unit</th>
                    <th style="width: 80px; text-align: right;">Rate</th>
                    <th style="width: 60px;">Tax %</th>
                    <th style="width: 80px; text-align: right;">Tax Amt</th>
                    <th style="width: 95px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $pTaxableSum = 0; $pTaxSum = 0; $pTotalSum = 0;
                foreach ($itemList as $it): 
                    $qty = (int)($it['quantity'] ?? 1);
                    $rate = (float)($it['unit_price'] ?? 0);
                    $discAmt = (float)($it['discount_amount'] ?? 0);
                    $taxable = (float)($it['taxable_amount'] ?? (($qty * $rate) - $discAmt));
                    $taxRate = (float)($it['tax_rate'] ?? 18);
                    $taxAmt = (float)($it['tax_amount'] ?? ($taxable * ($taxRate / 100)));
                    $rowTot = (float)($it['total_amount'] ?? ($taxable + $taxAmt));

                    $pTaxableSum += $taxable;
                    $pTaxSum += $taxAmt;
                    $pTotalSum += $rowTot;
                ?>
                <tr>
                    <td style="text-align: center; color: var(--ui-print-muted);"><?php echo $i++; ?></td>
                    <td>
                        <strong style="font-size: 11.5px;"><?php echo htmlspecialchars($it['item_name']); ?></strong>
                        <?php if (!empty($it['description'])): ?>
                            <div style="font-size: 9.5px; color: var(--ui-print-muted);"><?php echo htmlspecialchars($it['description']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;"><?php echo $qty; ?></td>
                    <td style="text-align: center; color: var(--ui-print-muted);"><?php echo htmlspecialchars($it['unit'] ?? 'PCS'); ?></td>
                    <td style="text-align: right;"><?php echo number_format($rate, 2); ?></td>
                    <td style="text-align: center;"><?php echo $taxRate; ?>%</td>
                    <td style="text-align: right;"><?php echo number_format($taxAmt, 2); ?></td>
                    <td style="text-align: right; font-weight: 700;"><?php echo number_format($rowTot, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ANCHORED FOOTER SECTION (Fills Bottom & Eliminates Blank Space) -->
    <div class="print-avoid-break">
        <!-- Totals, Bank, and Declarations Box -->
        <div style="border: 1px solid var(--ui-print-ink); border-radius: 6px; padding: 10px 12px; margin-bottom: 16px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                   <!-- Bank Details & Terms -->
                    <td style="vertical-align: top; width: 55%; padding-right: 20px;">

                        <!-- ADDED SETTLEMENT DETAILS -->
                        <?php if (!empty($allocations)): ?>
                            <div style="margin-bottom: 8px;">
                                <div style="font-weight: 700; text-transform: uppercase; font-size: 9.5px; margin-bottom: 3px;">Settlement Details:</div>
                                <table class="no-border" style="width: 100%; font-size: 10px;">
                                    <?php foreach ($allocations as $al): 
                                        $refNo = trim($al['reference_number'] ?? $al['ref_no'] ?? '');
                                        $isDirect = (stripos($refNo, 'DIRECT-PAY-') === 0);
                                        $payInNo = trim($al['pay_in_no'] ?? $al['reference_number'] ?? 'Pay-In');
                                        $method = htmlspecialchars($al['payment_method'] ?? 'Cash/Bank');
                                    ?>
                                        <tr>
                                            <td style="padding: 1px 0; color: var(--ui-print-muted);">
                                                <?php if (!$isDirect): ?>
                                                    <strong><?php echo htmlspecialchars($payInNo); ?></strong> (Advance Linked)
                                                <?php else: ?>
                                                    Direct (<?php echo $method; ?>)<?php echo !empty($refNo) ? ' Ref: ' . htmlspecialchars($refNo) : ''; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 1px 0; text-align: right; font-weight: 700;">
                                                <?php echo cur_symbol(); ?> <?php echo number_format((float)($al['allocated_amount'] ?? 0), 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($settings['bank_name']) || !empty($settings['account_number'])): ?>
                            <div style="margin-bottom: 8px;">
                                <div style="font-weight: 700; text-transform: uppercase; font-size: 9.5px; margin-bottom: 2px;">Bank Details:</div>
                                <div><strong>Bank:</strong> <?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?></div>
                                <div><strong>A/c No:</strong> <?php echo htmlspecialchars($settings['account_number'] ?? ''); ?></div>
                                <div><strong>IFSC:</strong> <?php echo htmlspecialchars($settings['bank_ifsc'] ?? ''); ?></div>
                            </div>
                        <?php endif; ?>

                        <div>
                            <div style="font-weight: 700; text-transform: uppercase; font-size: 9.5px; margin-bottom: 2px;">Terms &amp; Conditions:</div>
                            <div style="font-size: 9.5px; color: var(--ui-print-muted);">
                                <?php echo nl2br(htmlspecialchars($invoice['notes'] ?? '1. Goods once sold will not be taken back.\n2. Payment due within specified due date.')); ?>
                            </div>
                        </div>
                    </td>

                    <!-- Accurate Summary Numbers -->
                    <td style="vertical-align: top; width: 45%; border-left: 1px solid var(--ui-print-line); padding-left: 14px;">
                        <table class="no-border" style="width: 100%; font-size: 11px;">
                            <tr>
                                <td>Taxable Subtotal:</td>
                                <td style="text-align: right; font-weight: 700;"><?php echo cur_symbol(); ?> <?php echo number_format($pTaxableSum, 2); ?></td>
                            </tr>
                            <?php if ($isInterstate): ?>
                            <tr>
                                <td>IGST Total:</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format($pTaxSum, 2); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td>CGST (Central):</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format($pTaxSum / 2, 2); ?></td>
                            </tr>
                            <tr>
                                <td>SGST (State):</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format($pTaxSum / 2, 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php if ((float)($invoice['round_off'] ?? 0) != 0): ?>
                            <tr>
                                <td>Round Off:</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format((float)$invoice['round_off'], 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <tr style="border-top: 1.5px solid var(--ui-print-ink) !important; border-bottom: 1.5px solid var(--ui-print-ink) !important;">
                                <td style="font-weight: 800; font-size: 13px; padding: 5px 0;">Grand Total:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 13px; padding: 5px 0;">
                                    <?php echo cur_symbol(); ?> <?php echo number_format($totalAmount > 0 ? $totalAmount : $pTotalSum, 2); ?>
                                </td>
                            </tr>
                            <?php if ($totalSettled > 0): ?>
                            <tr>
                                <td>Total Settled / Paid:</td>
                                <td style="text-align: right;">- <?php echo cur_symbol(); ?> <?php echo number_format($totalSettled, 2); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 800;">Balance Due:</td>
                                <td style="text-align: right; font-weight: 800;"><?php echo cur_symbol(); ?> <?php echo number_format($netBalanceDue, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Signature Block -->
        <table class="no-border" style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="font-size: 9.5px; color: var(--ui-print-muted); vertical-align: bottom;">
                    Declaration: We declare that this invoice shows the actual price of the goods/services described and that all particulars are true and correct.
                </td>
                <td style="text-align: center; width: 170px; vertical-align: bottom;">
                    <div style="font-size: 10px; margin-bottom: 35px;">For <?php echo htmlspecialchars($settings['company_name'] ?? 'LX Accounting'); ?></div>
                    <div style="border-top: 1px solid var(--ui-print-ink); padding-top: 3px; font-weight: 700; font-size: 10.5px;">
                        Authorized Signatory
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>