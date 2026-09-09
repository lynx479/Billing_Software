<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Seller Settlements</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateSettlementModal">
        <i class="bi bi-calculator"></i> Calculate Settlement
    </button>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Reference #</th>
                        <th>Seller</th>
                        <th>Period</th>
                        <th>Gross Sales</th>
                        <th>Commission + Tax</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($settlements)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No settlement records found. Click "Calculate Settlement" to run a payout period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($settlements as $st): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($st['settlement_reference']); ?></code></td>
                            <td>
                                <strong><?php echo htmlspecialchars($st['store_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($st['owner_name']); ?></small>
                            </td>
                            <td><?php echo $st['period_start'] . ' to ' . $st['period_end']; ?></td>
                            <td><?php echo cur_symbol(); ?> <?php echo number_format($st['gross_sales'], 2); ?></td>
                            <td class="text-danger">
                                - <?php echo cur_symbol(); ?> <?php echo number_format($st['commission_amount'] + $st['tax_on_commission'], 2); ?>
                            </td>
                            <td><strong class="text-success"><?php echo cur_symbol(); ?> <?php echo number_format($st['net_payable'], 2); ?></strong></td>
                            <td>
                                <span class="badge bg-<?php echo $st['status'] === 'PAID' ? 'success' : 'warning text-dark'; ?>">
                                    <?php echo htmlspecialchars($st['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal to run settlement calculations -->
<div class="modal fade" id="generateSettlementModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/settlements/generate">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Calculate Seller Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Seller ID</label>
                        <input type="number" name="seller_id" class="form-control" value="1" required>
                        <small class="text-muted">Use 1 or 2 to match mock sellers.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="2026-08-01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period End Date</label>
                        <input type="date" name="end_date" class="form-control" value="2026-08-31" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Run Calculation</button>
                </div>
            </div>
        </form>
    </div>
</div>