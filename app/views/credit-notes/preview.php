<div class="d-print-none mb-3 d-flex justify-content-between align-items-center">
    <a href="<?php echo APP_URL; ?>/creditNotes" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Credit Notes
    </a>
    <div class="d-flex gap-2">
        <a href="<?php echo APP_URL; ?>/creditNotes/create" class="btn btn-outline-primary">
            <i class="bi bi-plus-lg me-1"></i> Create New Credit Note
        </a>
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i> Print / Download PDF
        </button>
    </div>
</div>

<?php
// Safe item fallback
$itemList = !empty($creditNoteItems) ? $creditNoteItems : (!empty($items) ? $items : []);

$companyState = $settings['company_state'] ?? 'Kerala';
$pos = $creditNote['place_of_supply'] ?? $companyState;
$isInterstate = (bool)($creditNote['is_interstate'] ?? (strcasecmp(trim($companyState), trim($pos)) !== 0));

$cnTotal = (float)($creditNote['total_amount'] ?? 0);
$adjustedAmt = (float)($creditNote['adjusted_amount'] ?? 0);
$openCreditBalance = max(0, $cnTotal - $adjustedAmt);

// Status Badge Logic
$status = strtoupper($creditNote['status'] ?? 'OPEN');
if ($status === 'ADJUSTED') {
    $statusBadge = 'bg-success';
} elseif ($status === 'PARTIALLY_ADJUSTED') {
    $statusBadge = 'bg-warning text-dark';
} else {
    $statusBadge = 'bg-danger';
}
?>

<!-- ============================================================ -->
<!-- 1. ON-SCREEN INTERACTIVE VIEW (Hidden completely on print)   -->
<!-- ============================================================ -->
<div class="card shadow-sm border-0 p-4 p-md-5 bg-white d-print-none">
    <!-- Header: Company & Credit Note Metadata -->
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
            <h2 class="fw-bold text-danger tracking-wide mb-1">CREDIT NOTE</h2>
            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($creditNote['credit_note_number']); ?></h5>
            <small class="text-muted d-block"><strong>Credit Note Date:</strong> <?php echo date('d/m/Y', strtotime($creditNote['credit_note_date'])); ?></small>
            <?php if (function_exists('sys_time_badge')): ?>
                <?php echo sys_time_badge('Recorded On', $creditNote['created_at'] ?? null); ?>
            <?php endif; ?>
            <?php if (!empty($creditNote['original_invoice_no'])): ?>
                <small class="text-muted d-block"><strong>Against Invoice:</strong> <?php echo htmlspecialchars($creditNote['original_invoice_no']); ?></small>
            <?php endif; ?>
            <div class="mt-2">
                <span class="badge <?php echo $statusBadge; ?> px-3 py-1 fs-6">
                    <?php echo htmlspecialchars($status); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Customer Information: Issued To & Supply Details -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="p-3 bg-light rounded border h-100">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1"><i class="bi bi-person me-1"></i>Issued To</span>
                <strong class="text-dark d-block fs-6"><?php echo htmlspecialchars($creditNote['party_name'] ?? 'Walk-in Customer'); ?></strong>
                <?php if (!empty($creditNote['business_name'])): ?>
                    <small class="d-block text-muted fw-bold"><?php echo htmlspecialchars($creditNote['business_name']); ?></small>
                <?php endif; ?>
                <small class="d-block text-muted mt-1"><?php echo nl2br(htmlspecialchars($creditNote['address'] ?? 'N/A')); ?></small>
                <small class="d-block text-muted mt-1"><strong>GSTIN / PAN:</strong> <?php echo htmlspecialchars($creditNote['gstin'] ?? 'Unregistered'); ?></small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-3 bg-light rounded border h-100">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1"><i class="bi bi-info-circle me-1"></i>Supply Details</span>
                <small class="d-block text-muted"><strong>Place of Supply:</strong> <?php echo htmlspecialchars($pos); ?></small>
                <small class="d-block text-muted"><strong>GST Type:</strong> 
                    <span class="badge <?php echo $isInterstate ? 'bg-warning text-dark' : 'bg-primary'; ?>">
                        <?php echo $isInterstate ? 'Interstate (IGST)' : 'Intrastate (CGST + SGST)'; ?>
                    </span>
                </small>
                <?php if (!empty($creditNote['phone'])): ?>
                    <small class="d-block text-muted mt-1"><strong>Phone:</strong> <?php echo htmlspecialchars($creditNote['phone']); ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Screen Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark small text-center">
                <tr>
                    <th width="40px">#</th>
                    <th>Item &amp; Description</th>
                    <th width="90px">Qty</th>
                    <th width="80px">Unit</th>
                    <th width="110px" class="text-end">Rate (<?php echo cur_symbol(); ?>)</th>
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
                        <td colspan="10" class="text-center py-4 text-muted">No line items attached to this credit note.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    foreach ($itemList as $item): 
                        $qty = (float)($item['quantity'] ?? 1);
                        $rate = (float)($item['unit_price'] ?? 0);
                        $discAmt = (float)($item['discount_amount'] ?? 0);
                        $taxable = (float)($item['taxable_amount'] ?? (($qty * $rate) - $discAmt));
                        $taxRate = (float)($item['tax_rate'] ?? 18);
                        $taxAmt = (float)($item['tax_amount'] ?? ($taxable * ($taxRate / 100)));
                        $itemTotal = (float)($item['total_amount'] ?? ($taxable + $taxAmt));
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
                        <td class="text-end text-danger"><?php echo $discAmt > 0 ? cur_symbol() . ' ' . number_format($discAmt, 2) : '-'; ?></td>
                        <td class="text-end"><?php echo cur_symbol(); ?> <?php echo number_format($taxable, 2); ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?php echo $taxRate; ?>%</span></td>
                        <td class="text-end text-primary"><?php echo cur_symbol(); ?> <?php echo number_format($taxAmt, 2); ?></td>
                        <td class="text-end fw-bold text-dark"><?php echo cur_symbol(); ?> <?php echo number_format($itemTotal, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Settlement Details, Notes & Totals -->
    <div class="row g-4">
        <div class="col-md-6">
            <?php if (!empty($allocations)): ?>
            <div class="card bg-light border-0 p-3 mb-3">
                <span class="fw-bold small text-uppercase text-dark mb-2 d-block"><i class="bi bi-wallet2 me-1"></i>Settlement / Allocation Details:</span>
                <ul class="list-unstyled mb-0 small">
                    <?php foreach ($allocations as $al): 
                        $refNo = trim($al['reference_number'] ?? $al['ref_no'] ?? '');
                        $method = htmlspecialchars($al['payment_method'] ?? 'Cash/Bank');
                    ?>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>
                                <strong class="text-dark"><?php echo htmlspecialchars($al['payment_type'] ?? 'Pay-Out'); ?></strong>
                                <span class="text-muted">(<?php echo $method; ?>)</span>
                                <?php if (!empty($refNo)): ?>
                                    <span class="badge ms-1 bg-success">Ref: <?php echo htmlspecialchars($refNo); ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="fw-bold text-success"><?php echo cur_symbol(); ?> <?php echo number_format((float)($al['allocated_amount'] ?? 0), 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="p-3 bg-light rounded border">
                <span class="fw-bold small text-uppercase text-dark mb-1 d-block">Reason / Notes:</span>
                <p class="text-muted small mb-0"><?php echo nl2br(htmlspecialchars($creditNote['notes'] ?? ($creditNote['reason'] ?? 'Goods returned / Price adjustment.'))); ?></p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-light border-0 p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Taxable Value:</td>
                        <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)$creditNote['taxable_amount'], 2); ?></td>
                    </tr>
                    <?php if ($isInterstate): ?>
                    <tr>
                        <td class="text-muted">Integrated GST (IGST):</td>
                        <td class="text-end text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($creditNote['igst_amount'] ?? $creditNote['tax_amount']), 2); ?></td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td class="text-muted">Central GST (CGST):</td>
                        <td class="text-end text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($creditNote['cgst_amount'] ?? ($creditNote['tax_amount'] / 2)), 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">State GST (SGST):</td>
                        <td class="text-end text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)($creditNote['sgst_amount'] ?? ($creditNote['tax_amount'] / 2)), 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if ((float)($creditNote['round_off'] ?? 0) != 0): ?>
                    <tr>
                        <td class="text-muted">Round Off:</td>
                        <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format((float)$creditNote['round_off'], 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <tr class="border-top">
                        <td class="fs-5 fw-bold text-dark">Credit Note Total:</td>
                        <td class="text-end fs-5 fw-bold text-danger"><?php echo cur_symbol(); ?> <?php echo number_format($cnTotal, 2); ?></td>
                    </tr>

                    <?php if (!empty($creditNote['invoice_total_amount'])): ?>
                    <tr>
                        <td class="text-muted">Original Invoice Total:</td>
                        <td class="text-end fw-bold text-secondary"><?php echo cur_symbol(); ?> <?php echo number_format((float)$creditNote['invoice_total_amount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>

                    <tr>
                        <td class="text-muted">Credit Applied / Adjusted:</td>
                        <td class="text-end text-success fw-bold">- <?php echo cur_symbol(); ?> <?php echo number_format($adjustedAmt, 2); ?></td>
                    </tr>

                    <tr class="border-top">
                        <td class="fs-6 fw-bold text-primary">Open Credit Balance:</td>
                        <td class="text-end fs-6 fw-bold text-primary">
                            <?php echo cur_symbol(); ?> <?php echo number_format($openCreditBalance, 2); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Authorized Signatory -->
    <div class="row pt-5 mt-4 border-top align-items-end">
        <div class="col-8">
            <small class="text-muted d-block">This is a computer-generated credit note and requires no physical signature.</small>
        </div>
        <div class="col-4 text-center">
            <div style="height: 50px;"></div>
            <span class="d-block border-top pt-1 small fw-bold text-dark">Authorized Signatory</span>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- 2. EXACT PRINT-ONLY TEMPLATE (100% matched to Tax Invoice Print CSS)  -->
<!-- ===================================================================== -->
<div id="printInvoiceArea">
    <!-- TOP / HEADER SECTION -->
    <div>
        <!-- Company & Document Meta Box with Rounded Border -->
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
                        <div style="font-size: 18px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 4px; color: var(--ui-print-ink);">CREDIT NOTE</div>
                        <div style="font-size: 11px; line-height: 1.5;">
                            <div><strong>Credit Note No:</strong> <?php echo htmlspecialchars($creditNote['credit_note_number']); ?></div>
                            <div><strong>Credit Note Date:</strong> <?php echo date('d/m/Y', strtotime($creditNote['credit_note_date'])); ?></div>
                            <?php if (!empty($creditNote['original_invoice_no'])): ?>
                                <div><strong>Against Invoice:</strong> <?php echo htmlspecialchars($creditNote['original_invoice_no']); ?></div>
                            <?php endif; ?>
                            <div><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Issued To & Supply Details Box -->
        <div style="border: 1px solid var(--ui-print-line); border-radius: 6px; padding: 10px 12px; margin-bottom: 14px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 50%; padding-right: 15px;">
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: var(--ui-print-muted); margin-bottom: 2px;">Issued To:</div>
                        <div style="font-size: 12px; font-weight: 700;"><?php echo htmlspecialchars($creditNote['party_name'] ?? 'Walk-in Customer'); ?></div>
                        <?php if (!empty($creditNote['business_name'])): ?>
                            <div style="font-size: 10.5px;"><?php echo htmlspecialchars($creditNote['business_name']); ?></div>
                        <?php endif; ?>
                        <div style="font-size: 10.5px;"><?php echo nl2br(htmlspecialchars($creditNote['address'] ?? '')); ?></div>
                        <div style="font-size: 10.5px; margin-top: 2px;"><strong>GSTIN / PAN:</strong> <?php echo htmlspecialchars($creditNote['gstin'] ?? 'Unregistered'); ?></div>
                    </td>
                    <td style="vertical-align: top; width: 50%; padding-left: 15px; border-left: 1px solid var(--ui-print-line);">
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: var(--ui-print-muted); margin-bottom: 2px;">Supply Details:</div>
                        <div style="font-size: 10.5px;"><strong>Place of Supply:</strong> <?php echo htmlspecialchars($pos); ?></div>
                        <div style="font-size: 10.5px;"><strong>GST Supply Type:</strong> <?php echo $isInterstate ? 'Interstate (IGST)' : 'Intrastate (CGST + SGST)'; ?></div>
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
        <!-- Totals, Notes, and Balance Box -->
        <div style="border: 1px solid var(--ui-print-ink); border-radius: 6px; padding: 10px 12px; margin-bottom: 16px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <!-- Left: Settlement & Notes -->
                    <td style="vertical-align: top; width: 55%; padding-right: 20px;">
                        <?php if (!empty($allocations)): ?>
                            <div style="margin-bottom: 8px;">
                                <div style="font-weight: 700; text-transform: uppercase; font-size: 9.5px; margin-bottom: 3px;">Settlement Details:</div>
                                <table class="no-border" style="width: 100%; font-size: 10px;">
                                    <?php foreach ($allocations as $al): 
                                        $refNo = trim($al['reference_number'] ?? $al['ref_no'] ?? '');
                                        $method = htmlspecialchars($al['payment_method'] ?? 'Cash/Bank');
                                    ?>
                                        <tr>
                                            <td style="padding: 1px 0; color: var(--ui-print-muted);">
                                                Pay-Out (<?php echo $method; ?>)<?php echo !empty($refNo) ? ' Ref: ' . htmlspecialchars($refNo) : ''; ?>
                                            </td>
                                            <td style="padding: 1px 0; text-align: right; font-weight: 700;">
                                                <?php echo cur_symbol(); ?> <?php echo number_format((float)($al['allocated_amount'] ?? 0), 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>

                        <div>
                            <div style="font-weight: 700; text-transform: uppercase; font-size: 9.5px; margin-bottom: 2px;">Reason / Notes:</div>
                            <div style="font-size: 9.5px; color: var(--ui-print-muted);">
                                <?php echo nl2br(htmlspecialchars($creditNote['notes'] ?? ($creditNote['reason'] ?? 'Goods returned or adjusted against billing.'))); ?>
                            </div>
                        </div>
                    </td>

                    <!-- Right: Exact Financial Summary -->
                    <td style="vertical-align: top; width: 45%; border-left: 1px solid var(--ui-print-line); padding-left: 14px;">
                        <table class="no-border" style="width: 100%; font-size: 11px;">
                            <tr>
                                <td>Taxable Subtotal:</td>
                                <td style="text-align: right; font-weight: 700;"><?php echo cur_symbol(); ?> <?php echo number_format((float)$creditNote['taxable_amount'], 2); ?></td>
                            </tr>
                            <?php if ($isInterstate): ?>
                            <tr>
                                <td>IGST Total:</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format((float)($creditNote['igst_amount'] ?? $creditNote['tax_amount']), 2); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td>CGST (Central):</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format((float)($creditNote['cgst_amount'] ?? ($creditNote['tax_amount'] / 2)), 2); ?></td>
                            </tr>
                            <tr>
                                <td>SGST (State):</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format((float)($creditNote['sgst_amount'] ?? ($creditNote['tax_amount'] / 2)), 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php if ((float)($creditNote['round_off'] ?? 0) != 0): ?>
                            <tr>
                                <td>Round Off:</td>
                                <td style="text-align: right;"><?php echo cur_symbol(); ?> <?php echo number_format((float)$creditNote['round_off'], 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <tr style="border-top: 1.5px solid var(--ui-print-ink) !important; border-bottom: 1.5px solid var(--ui-print-ink) !important;">
                                <td style="font-weight: 800; font-size: 13px; padding: 5px 0;">Credit Note Total:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 13px; padding: 5px 0;">
                                    <?php echo cur_symbol(); ?> <?php echo number_format($cnTotal, 2); ?>
                                </td>
                            </tr>

                            <?php if (!empty($creditNote['invoice_total_amount'])): ?>
                            <tr>
                                <td style="color: var(--ui-print-muted);">Original Invoice Total:</td>
                                <td style="text-align: right; font-weight: 700;"><?php echo cur_symbol(); ?> <?php echo number_format((float)$creditNote['invoice_total_amount'], 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <tr>
                                <td style="color: var(--ui-print-muted);">Credit Applied:</td>
                                <td style="text-align: right; font-weight: 700;">- <?php echo cur_symbol(); ?> <?php echo number_format($adjustedAmt, 2); ?></td>
                            </tr>

                            <tr style="border-top: 1px solid var(--ui-print-line);">
                                <td style="font-weight: 800;">Open Credit Balance:</td>
                                <td style="text-align: right; font-weight: 800;"><?php echo cur_symbol(); ?> <?php echo number_format($openCreditBalance, 2); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Signature Block -->
        <table class="no-border" style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="font-size: 9.5px; color: var(--ui-print-muted); vertical-align: bottom;">
                    Declaration: We declare that this credit note shows the actual adjustment and that all particulars are true and correct.
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