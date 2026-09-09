<!-- Top Section: Party Details (Read-Only) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?php echo APP_URL; ?>/parties/sellers" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Back to Seller Search
        </a>
        <h4 class="fw-bold mb-0 mt-1"><?php echo htmlspecialchars($seller['store_name']); ?></h4>
    </div>
    <span class="badge bg-secondary p-2"><i class="bi bi-lock-fill me-1"></i> Core Marketplace Seller (Read-Only)</span>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <span class="text-muted small text-uppercase fw-bold d-block">Owner Name</span>
                <strong><?php echo htmlspecialchars($seller['owner_name']); ?></strong>
            </div>
            <div class="col-md-4">
                <span class="text-muted small text-uppercase fw-bold d-block">GSTIN</span>
                <code><?php echo htmlspecialchars($seller['gstin'] ?? 'Not Available'); ?></code>
            </div>
            <div class="col-md-4">
                <span class="text-muted small text-uppercase fw-bold d-block">Address / State</span>
                <span><?php echo htmlspecialchars($seller['state'] . ', India'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: 7-Column Transactions Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">Statement & Transactions</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Type</th>
                        <th>Invoice / Reference No</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Balance / Unused</th>
                        <th>Status</th>
                        <th class="text-center" width="80px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No financial transactions recorded for this seller.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?php echo $t['tx_type'] === 'Order Sale' ? 'info text-dark' : 'success'; ?>">
                                    <?php echo $t['tx_type']; ?>
                                </span>
                            </td>
                            <td><code><?php echo htmlspecialchars($t['ref_no']); ?></code></td>
                            <td><?php echo date('d M Y', strtotime($t['tx_date'])); ?></td>
                            <td><strong><?php echo cur_symbol(); ?> <?php echo number_format($t['total_amount'], 2); ?></strong></td>
                            <td class="text-muted"><?php echo cur_symbol(); ?> <?php echo number_format($t['balance_unused'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($t['tx_status'] === 'Completed' || $t['tx_status'] === 'PAID') ? 'success' : 'warning text-dark'; ?>">
                                    <?php echo htmlspecialchars($t['tx_status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="alert('Viewing reference: <?php echo $t['ref_no']; ?>')"><i class="bi bi-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item text-muted disabled" href="javascript:void(0)"><i class="bi bi-pencil me-2"></i> Edit (Marketplace Locked)</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>