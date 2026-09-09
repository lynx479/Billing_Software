<div class="d-print-none mb-3 d-flex justify-content-between align-items-center">
    <a href="<?php echo APP_URL; ?>/payments/<?php echo ($payment['payment_type'] === 'PAY_IN' ? 'payIn' : 'payOut'); ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <div>
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i> Print / Download Receipt
        </button>
    </div>
</div>

<?php
$isPayIn = ($payment['payment_type'] === 'PAY_IN');
$title = $isPayIn ? 'PAYMENT RECEIPT' : 'PAYMENT VOUCHER';
$companyState = $settings['company_state'] ?? 'Kerala';
?>

<!-- ============================================================ -->
<!-- 1. ON-SCREEN INTERACTIVE VIEW (Hidden completely on print)   -->
<!-- ============================================================ -->
<div class="card shadow-sm border-0 p-4 p-md-5 bg-white d-print-none">
    <div class="row align-items-center pb-4 border-bottom mb-4">
        <div class="col-6">
            <h4 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($settings['company_name'] ?? 'LX Accounting'); ?></h4>
            <small class="text-muted d-block"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></small>
            <small class="text-muted d-block"><strong>GSTIN:</strong> <?php echo htmlspecialchars($settings['company_gstin'] ?? 'N/A'); ?></small>
        </div>
        <div class="col-6 text-end">
            <h2 class="fw-bold text-uppercase <?php echo $isPayIn ? 'text-success' : 'text-danger'; ?> mb-1">
                <?php echo $title; ?>
            </h2>
            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($payment['reference_number']); ?></h5>
            <small class="text-muted d-block"><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></small>
            <?php if (function_exists('sys_time_badge')): ?>
                <?php echo sys_time_badge('Recorded On', $payment['created_at'] ?? null); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <span class="text-muted small fw-bold text-uppercase d-block"><?php echo $isPayIn ? 'Received From:' : 'Paid To:'; ?></span>
            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($payment['party_name']); ?></h5>
            <?php if (!empty($payment['business_name'])): ?>
                <strong class="d-block text-muted"><?php echo htmlspecialchars($payment['business_name']); ?></strong>
            <?php endif; ?>
            <span class="text-muted d-block"><?php echo htmlspecialchars($payment['address'] ?? '-'); ?></span>
        </div>
        <div class="col-6 text-end">
            <span class="text-muted small fw-bold text-uppercase d-block">Total Amount</span>
            <h2 class="fw-bold <?php echo $isPayIn ? 'text-success' : 'text-danger'; ?> mb-0">
                <?php echo cur_symbol(); ?> <?php echo number_format($payment['amount'], 2); ?>
            </h2>
            <small class="text-muted">Payment Mode: <strong><?php echo htmlspecialchars($payment['payment_method']); ?></strong> (<?php echo htmlspecialchars($payment['bank_name'] ?? 'Bank/Cash'); ?>)</small>
        </div>
    </div>

    <!-- Allocation History -->
    <?php if (!empty($payment['allocations'])): ?>
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-2">Adjustments &amp; Allocations Breakdown:</h6>
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light small">
                <tr><th>Document / Transaction</th><th>Type</th><th class="text-end">Adjusted Amount</th></tr>
            </thead>
            <tbody>
                <?php foreach ($payment['allocations'] as $al): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($al['invoice_number'] ?? $al['credit_note_number'] ?? $al['linked_ref_no'] ?? '-'); ?></strong></td>
                    <td>
                        <?php if (!empty($al['invoice_number'])) echo 'Tax Invoice';
                              elseif (!empty($al['credit_note_number'])) echo 'Credit Note';
                              else echo 'Payment Link'; ?>
                    </td>
                    <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($al['allocated_amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="row pt-5 border-top mt-auto align-items-end">
        <div class="col-8">
            <small class="text-muted fw-bold d-block">Notes:</small>
            <small class="text-muted"><?php echo nl2br(htmlspecialchars($payment['notes'] ?? '')); ?></small>
        </div>
        <div class="col-4 text-center">
            <span class="d-block small text-muted border-top pt-1 fw-bold">Authorized Signature</span>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- 2. EXACT PRINT-ONLY TEMPLATE (100% matched to Invoice Print-Only CSS) -->
<!-- ===================================================================== -->
<div id="printInvoiceArea">
    <!-- TOP / HEADER SECTION -->
    <div>
        <!-- Company & Receipt Meta Box with Rounded Border -->
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
                        <div style="font-size: 18px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 4px; color: var(--ui-print-ink);">
                            <?php echo $title; ?>
                        </div>
                        <div style="font-size: 11px; line-height: 1.5;">
                            <div><strong>Reference No:</strong> #<?php echo htmlspecialchars($payment['reference_number']); ?></div>
                            <div><strong>Payment Date:</strong> <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></div>
                            <div><strong>Payment Mode:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Party Information Box -->
        <div style="border: 1px solid var(--ui-print-line); border-radius: 6px; padding: 10px 12px; margin-bottom: 14px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 55%; padding-right: 15px;">
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: var(--ui-print-muted); margin-bottom: 2px;">
                            <?php echo $isPayIn ? 'Received From:' : 'Paid To:'; ?>
                        </div>
                        <div style="font-size: 12px; font-weight: 700;"><?php echo htmlspecialchars($payment['party_name']); ?></div>
                        <?php if (!empty($payment['business_name'])): ?>
                            <div style="font-size: 10.5px;"><?php echo htmlspecialchars($payment['business_name']); ?></div>
                        <?php endif; ?>
                        <div style="font-size: 10.5px;"><?php echo nl2br(htmlspecialchars($payment['address'] ?? '')); ?></div>
                    </td>
                    <td style="vertical-align: top; width: 45%; padding-left: 15px; border-left: 1px solid var(--ui-print-line);">
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: var(--ui-print-muted); margin-bottom: 2px;">Account Details:</div>
                        <div style="font-size: 10.5px;"><strong>Bank / Account:</strong> <?php echo htmlspecialchars($payment['bank_name'] ?? 'Bank/Cash'); ?></div>
                        <div style="font-size: 10.5px;"><strong>Status:</strong> Completed / Cleared</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Allocations / Breakdowns Table -->
        <table class="print-table" style="margin-bottom: 12px;">
            <thead>
                <tr style="text-align: center;">
                    <th style="width: 40px;">#</th>
                    <th style="text-align: left;">Allocated Document / Reference</th>
                    <th style="width: 150px; text-align: left;">Transaction Type</th>
                    <th style="width: 140px; text-align: right;">Allocated Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payment['allocations'])): ?>
                    <tr>
                        <td style="text-align: center; color: var(--ui-print-muted);">1</td>
                        <td>
                            <strong style="font-size: 11.5px;">On Account / Direct Payment</strong>
                            <div style="font-size: 9.5px; color: var(--ui-print-muted);">No invoice or credit note was directly linked.</div>
                        </td>
                        <td>Advance / Unadjusted</td>
                        <td style="text-align: right; font-weight: 700;"><?php echo cur_symbol(); ?> <?php echo number_format($payment['amount'], 2); ?></td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $alIdx = 1;
                    foreach ($payment['allocations'] as $al): 
                    ?>
                    <tr>
                        <td style="text-align: center; color: var(--ui-print-muted);"><?php echo $alIdx++; ?></td>
                        <td>
                            <strong style="font-size: 11.5px;"><?php echo htmlspecialchars($al['invoice_number'] ?? $al['credit_note_number'] ?? $al['linked_ref_no'] ?? 'Direct Entry'); ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($al['invoice_number'])) echo 'Tax Invoice';
                                  elseif (!empty($al['credit_note_number'])) echo 'Credit Note';
                                  else echo 'Settlement Allocation'; ?>
                        </td>
                        <td style="text-align: right; font-weight: 700;"><?php echo cur_symbol(); ?> <?php echo number_format($al['allocated_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ANCHORED FOOTER SECTION (Fills Bottom & Matches Invoice Style) -->
    <div class="print-avoid-break">
        <div style="border: 1px solid var(--ui-print-ink); border-radius: 6px; padding: 10px 12px; margin-bottom: 16px;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; width: 55%; padding-right: 20px;">
                        <div style="font-weight: 700; text-transform: uppercase; font-size: 9.5px; margin-bottom: 2px;">Notes / Remarks:</div>
                        <div style="font-size: 9.5px; color: var(--ui-print-muted);">
                            <?php echo nl2br(htmlspecialchars(!empty($payment['notes']) ? $payment['notes'] : 'Payment received and acknowledged successfully.')); ?>
                        </div>
                    </td>
                    <td style="vertical-align: top; width: 45%; border-left: 1px solid var(--ui-print-line); padding-left: 14px;">
                        <table class="no-border" style="width: 100%; font-size: 11px;">
                            <tr style="border-top: 1.5px solid var(--ui-print-ink) !important; border-bottom: 1.5px solid var(--ui-print-ink) !important;">
                                <td style="font-weight: 800; font-size: 13px; padding: 5px 0;">Total Amount Paid:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 13px; padding: 5px 0;">
                                    <?php echo cur_symbol(); ?> <?php echo number_format($payment['amount'], 2); ?>
                                </td>
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
                    Declaration: This receipt acknowledges the transaction details stated above.
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