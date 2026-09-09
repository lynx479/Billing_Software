<!-- Header & Navigation -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?php echo APP_URL; ?>/parties/created" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Back to Created Parties
        </a>
        <div class="d-flex align-items-center mt-1">
            <h4 class="fw-bold mb-0 me-2"><?php echo htmlspecialchars($party['name']); ?></h4>
            <span class="badge bg-<?php echo $party['party_type'] === 'CUSTOMER' ? 'primary' : 'warning text-dark'; ?>">
                <?php echo $party['party_type']; ?>
            </span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo APP_URL; ?>/parties/edit/<?php echo $party['party_id']; ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Details
        </a>
        <a href="<?php echo APP_URL; ?>/invoices/create" class="btn btn-success">
            <i class="bi bi-receipt me-1"></i> Create Invoice
        </a>
        <a href="<?php echo APP_URL; ?>/parties/deletePartyEntirely/<?php echo $party['party_id']; ?>" 
           class="btn btn-outline-danger" 
           onclick="return confirm('Warning: This will permanently delete this party, all related invoices, payments, and statement history. Are you sure?');">
            <i class="bi bi-trash me-1"></i> Delete Party & History
        </a>
    </div>
</div>

<!-- Party Details Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <span class="text-muted small text-uppercase fw-bold d-block">Business / Company</span>
                <strong><?php echo htmlspecialchars($party['business_name'] ?? '-'); ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small text-uppercase fw-bold d-block">Contact</span>
                <span><?php echo htmlspecialchars($party['phone'] ?? '-'); ?></span><br>
                <small class="text-muted"><?php echo htmlspecialchars($party['email'] ?? '-'); ?></small>
            </div>
            <div class="col-md-3">
                <span class="text-muted small text-uppercase fw-bold d-block">Tax Identifiers</span>
                <small class="text-muted">GSTIN:</small> <code><?php echo htmlspecialchars($party['gstin'] ?? 'N/A'); ?></code><br>
                <small class="text-muted">PAN:</small> <code><?php echo htmlspecialchars($party['pan'] ?? 'N/A'); ?></code>
            </div>
            <div class="col-md-3">
                <span class="text-muted small text-uppercase fw-bold d-block">Location</span>
                <span><?php echo htmlspecialchars($party['state']); ?> (<?php echo htmlspecialchars($party['city'] ?? ''); ?> <?php echo htmlspecialchars($party['pincode'] ?? ''); ?>)</span>
            </div>
            <?php if (!empty($party['address'])): ?>
                <div class="col-12 border-top pt-2 mt-2">
                    <small class="text-muted fw-bold">Billing Address:</small>
                    <span class="small ms-1"><?php echo htmlspecialchars($party['address']); ?></span>
                    <?php if (!empty($party['shipping_address']) && $party['shipping_address'] !== $party['address']): ?>
                        <br><small class="text-muted fw-bold">Shipping Address:</small>
                        <span class="small ms-1"><?php echo htmlspecialchars($party['shipping_address']); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Summary strip -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100"><div class="card-body py-3">
            <span class="text-muted small text-uppercase fw-bold d-block">Total Invoiced</span>
            <h5 class="fw-bold mb-0"><?php echo cur_symbol(); ?> <?php echo number_format($totals['invoiced'], 2); ?></h5>
            <small class="text-danger">Due: <?php echo cur_symbol(); ?> <?php echo number_format($totals['invoice_due'], 2); ?></small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100"><div class="card-body py-3">
            <span class="text-muted small text-uppercase fw-bold d-block">Credit Notes</span>
            <h5 class="fw-bold mb-0"><?php echo cur_symbol(); ?> <?php echo number_format($totals['credit'], 2); ?></h5>
            <small class="text-danger">Open: <?php echo cur_symbol(); ?> <?php echo number_format($totals['credit_open'], 2); ?></small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100"><div class="card-body py-3">
            <span class="text-muted small text-uppercase fw-bold d-block">Total Pay In</span>
            <h5 class="fw-bold mb-0 text-success"><?php echo cur_symbol(); ?> <?php echo number_format($totals['paid_in'], 2); ?></h5>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100"><div class="card-body py-3">
            <span class="text-muted small text-uppercase fw-bold d-block">Total Pay Out</span>
            <h5 class="fw-bold mb-0 text-danger"><?php echo cur_symbol(); ?> <?php echo number_format($totals['paid_out'], 2); ?></h5>
        </div></div>
    </div>
</div>

<!-- Tax Invoices -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-1 text-primary"></i> Tax Invoices</h6>
        <span class="badge bg-primary"><?php echo count($invoices); ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50px">#</th>
                        <th width="120px">Date</th>
                        <th>Invoice No</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance Due</th>
                        <th class="text-center" width="140px">Status</th>
                        <th class="text-center" width="80px">View</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No tax invoices for this party.</td></tr>
                <?php else: ?>
                    <?php list($pagedInvoices, $invPg) = lx_paginate($invoices, 20, 'inv_page'); $n = $invPg['offset']; ?>
                    <?php foreach ($pagedInvoices as $t): $n++; ?>
                        <tr>
                            <td class="text-muted"><?php echo $n; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($t['tx_date'])); ?></td>
                            <td><strong class="text-primary"><?php echo htmlspecialchars($t['ref_no']); ?></strong></td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($t['total_amount'], 2); ?></td>
                            <td class="text-end text-success"><?php echo cur_symbol(); ?> <?php echo number_format($t['paid_amount'], 2); ?></td>
                            <td class="text-end fw-bold text-danger"><?php echo cur_symbol(); ?> <?php echo number_format($t['balance'], 2); ?></td>
                            <td class="text-center"><span class="badge bg-<?php echo $t['badge_class']; ?>"><?php echo $t['tx_status']; ?></span></td>
                            <td class="text-center"><a href="<?php echo $t['action_url']; ?>" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($invoices)) lx_pagination_links($invPg); ?>
    </div>
</div>

<!-- Credit Notes -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-minus me-1 text-warning"></i> Credit Notes <small class="text-muted fw-normal">(settled through Pay-Out only)</small></h6>
        <span class="badge bg-warning text-dark"><?php echo count($creditNotes); ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50px">#</th>
                        <th width="120px">Date</th>
                        <th>Credit Note No</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Settled</th>
                        <th class="text-end">Open Balance</th>
                        <th class="text-center" width="160px">Status</th>
                        <th class="text-center" width="80px">View</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($creditNotes)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No credit notes for this party.</td></tr>
                <?php else: ?>
                    <?php list($pagedCns, $cnPg) = lx_paginate($creditNotes, 20, 'cn_page'); $n = $cnPg['offset']; ?>
                    <?php foreach ($pagedCns as $t): $n++; ?>
                        <tr>
                            <td class="text-muted"><?php echo $n; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($t['tx_date'])); ?></td>
                            <td><strong class="text-warning-emphasis"><?php echo htmlspecialchars($t['ref_no']); ?></strong></td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($t['total_amount'], 2); ?></td>
                            <td class="text-end text-success"><?php echo cur_symbol(); ?> <?php echo number_format($t['adjusted'], 2); ?></td>
                            <td class="text-end fw-bold text-danger"><?php echo cur_symbol(); ?> <?php echo number_format($t['balance'], 2); ?></td>
                            <td class="text-center"><span class="badge bg-<?php echo $t['badge_class']; ?>"><?php echo $t['tx_status']; ?></span></td>
                            <td class="text-center"><a href="<?php echo $t['action_url']; ?>" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($creditNotes)) lx_pagination_links($cnPg); ?>
    </div>
</div>

<?php
$paymentSections = [
    ['title' => 'Pay In (Receipts)', 'icon' => 'bi-arrow-down-circle', 'colour' => 'success', 'rows' => $payIns, 'param' => 'pin_page'],
    ['title' => 'Pay Out (Disbursements)', 'icon' => 'bi-arrow-up-circle', 'colour' => 'danger', 'rows' => $payOuts, 'param' => 'pout_page'],
];
foreach ($paymentSections as $section):
?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi <?php echo $section['icon']; ?> me-1 text-<?php echo $section['colour']; ?>"></i> <?php echo $section['title']; ?></h6>
        <span class="badge bg-<?php echo $section['colour']; ?>"><?php echo count($section['rows']); ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50px">#</th>
                        <th width="120px">Date</th>
                        <th>Reference No</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Allocated</th>
                        <th class="text-end">Unused</th>
                        <th class="text-center" width="150px">Status</th>
                        <th class="text-center" width="80px">View</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($section['rows'])): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No <?php echo $section['title']; ?> records for this party.</td></tr>
                <?php else: ?>
                    <?php list($pagedPays, $payPg) = lx_paginate($section['rows'], 20, $section['param']); $n = $payPg['offset']; ?>
                    <?php foreach ($pagedPays as $t): $n++; ?>
                        <tr>
                            <td class="text-muted"><?php echo $n; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($t['tx_date'])); ?></td>
                            <td><strong class="text-<?php echo $section['colour']; ?>"><?php echo htmlspecialchars($t['ref_no']); ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($t['method']); ?></span></td>
                            <td class="text-end fw-bold text-<?php echo $section['colour']; ?>"><?php echo cur_symbol(); ?> <?php echo number_format($t['total_amount'], 2); ?></td>
                            <td class="text-end"><?php echo cur_symbol(); ?> <?php echo number_format($t['allocated'], 2); ?></td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($t['unused'], 2); ?></td>
                            <td class="text-center"><span class="badge bg-<?php echo $t['badge_class']; ?>"><?php echo $t['tx_status']; ?></span></td>
                            <td class="text-center"><a href="<?php echo $t['action_url']; ?>" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($section['rows'])) lx_pagination_links($payPg); ?>
    </div>
</div>
<?php endforeach; ?>
