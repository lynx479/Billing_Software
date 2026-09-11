<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <a href="<?php echo APP_URL; ?>/bankAccounts" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Accounts
    </a>
    <h3 class="fw-bold mb-0 text-uppercase"><?php echo htmlspecialchars($account['account_name']); ?></h3>
    <a href="<?php echo APP_URL; ?>/bankAccounts/exportTransactions/<?php echo $account['bank_id']; ?>?<?php echo http_build_query($filters); ?>" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3">
            <span class="text-muted small fw-bold text-uppercase">Bank / Account No</span>
            <h6 class="fw-bold mb-0 mt-1"><?php echo htmlspecialchars($account['bank_name']); ?></h6>
            <code class="small"><?php echo htmlspecialchars($account['account_number']); ?></code>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Received</span>
            <h5 class="fw-bold text-success mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalIn'], 2); ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-danger border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Paid Out</span>
            <h5 class="fw-bold text-danger mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalOut'], 2); ?></h5>
        </div>
    </div>
    <div class="col-md-3">
    <div class="card shadow-sm border-0 border-start border-primary border-4 p-3">
        <span class="text-muted small fw-bold text-uppercase">Current Balance</span>
        <h5 class="fw-bold mb-0 mt-1 <?php echo balance_color_class($summary['closing']); ?>">
            <?php echo cur_symbol(); ?> <?php echo number_format(abs($summary['closing']), 2); ?>
        </h5>
        <small class="text-muted">
            Opening: <?php echo cur_symbol(); ?> <?php echo number_format($account['current_balance'], 2); ?>
        </small>
    </div>
</div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo APP_URL; ?>/bankAccounts/show/<?php echo $account['bank_id']; ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['from_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['to_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="<?php echo APP_URL; ?>/bankAccounts/show/<?php echo $account['bank_id']; ?>" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><span class="fw-bold">Transaction History</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="90px">Date</th>
                        <th width="70px">Time</th>
                        <th>Reference</th>
                        <th>Party</th>
                        <th>Method</th>
                        <th class="text-center" width="100px">Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php list($pagedTx, $txPg) = lx_paginate($transactions, 25); ?>

<?php if (empty($transactions)): ?>
    <tr class="table-light">
        <td colspan="7" class="text-end fw-bold">Opening Balance</td>
        <td class="text-end fw-bold <?php echo balance_color_class($account['current_balance']); ?>">
            <?php echo cur_symbol(); ?> <?php echo number_format(abs($account['current_balance']), 2); ?>
        </td>
    </tr>
    <tr>
        <td colspan="8" class="table-empty-state">
            <i class="bi bi-inbox"></i>
            No transactions recorded against this account yet.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($pagedTx as $t): $isIn = ($t['payment_type'] === 'PAY_IN'); ?>
        <tr style="cursor: pointer;" onclick="window.location='<?php echo APP_URL; ?>/payments/preview/<?php echo $t['payment_id']; ?>'">
            <td><?php echo date('d/m/Y', strtotime($t['payment_date'])); ?></td>
            <td class="text-muted small"><?php echo !empty($t['created_at']) ? date('H:i', strtotime($t['created_at'])) : '-'; ?></td>
            <td><strong class="<?php echo $isIn ? 'text-success' : 'text-danger'; ?>"><?php echo htmlspecialchars($t['reference_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($t['party_name'] ?? '-'); ?></td>
            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($t['payment_method']); ?></span></td>
            <td class="text-center">
                <span class="badge bg-<?php echo $isIn ? 'success' : 'danger'; ?>"><?php echo $isIn ? 'Received' : 'Paid'; ?></span>
            </td>
            <td class="text-end fw-bold <?php echo $isIn ? 'text-success' : 'text-danger'; ?>">
                <?php echo $isIn ? '+' : '-'; ?><?php echo cur_symbol(); ?> <?php echo number_format($t['amount'], 2); ?>
            </td>
            <td class="text-end fw-bold <?php echo balance_color_class($t['running_balance']); ?>">
                <?php echo cur_symbol(); ?> <?php echo number_format(abs($t['running_balance']), 2); ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <!-- Opening Balance = the very first event, shown at the bottom -->
    <tr class="table-light">
        <td colspan="7" class="text-end fw-bold text-uppercase small">Opening Balance</td>
        <td class="text-end fw-bold <?php echo balance_color_class($account['current_balance']); ?>">
            <?php echo cur_symbol(); ?> <?php echo number_format(abs($account['current_balance']), 2); ?>
        </td>
    </tr>
<?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($transactions)) lx_pagination_links($txPg); ?>
    </div>
</div>