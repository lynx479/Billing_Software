<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold text-uppercase mb-0 tracking-wide text-success">PAY IN (RECEIPTS)</h2>
    </div>
    <div>
        <a href="<?php echo APP_URL; ?>/payments/createPayIn" class="btn btn-success btn-lg shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Pay In
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo APP_URL; ?>/payments/payIn" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['from_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['to_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Search Pay Ins</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Ref #, Party, Phone..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
            </div>
           
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="<?php echo APP_URL; ?>/payments/payIn" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Received</span>
            <h4 class="fw-bold text-success mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalAmount'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Allocated to Invoices</span>
            <h4 class="fw-bold text-primary mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalAllocated'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-warning border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Unused / Advance Balance</span>
            <h4 class="fw-bold text-warning text-dark mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalUnused'], 2); ?></h4>
        </div>
    </div>
</div>

<!-- Pay In Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50px">#</th>
                        <th width="100px">Date</th>
                        <th width="80px">Time</th>
                        <th width="150px">Reference #</th>
                        <th>Party Name</th>
                        <th>Account / Bank</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Amount Received</th>
                        <th class="text-center" width="70px">Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                     <tr><td colspan="9" class="table-empty-state"><i class="bi bi-arrow-down-circle"></i>No Pay In transactions found.</td></tr>
                    <?php else: ?>
                        <?php list($pagedPayments, $payPg) = lx_paginate($payments, 20); $rowNo = $payPg['offset']; ?>
                        <?php foreach ($pagedPayments as $p): $rowNo++; ?>
                        <tr style="cursor: pointer;" onclick="if (!event.target.closest('.dropdown')) window.location='<?php echo APP_URL; ?>/payments/preview/<?php echo $p['payment_id']; ?>'">
                            <td class="text-muted"><?php echo $rowNo; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($p['payment_date'])); ?></td>
                            <td class="text-muted small"><?php echo !empty($p['created_at']) ? date('H:i', strtotime($p['created_at'])) : '-'; ?></td>
                            <td><strong class="text-success"><?php echo htmlspecialchars($p['reference_number']); ?></strong><?php if (!empty($p['has_links'])): ?> <i class="bi bi-link-45deg text-muted" title="Linked to another transaction"></i><?php endif; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['party_name']); ?></strong>
                                <?php if (!empty($p['business_name'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($p['business_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($p['bank_name'] ?? 'Cash/Bank'); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['payment_method']); ?></span></td>
                            <td class="text-end fw-bold text-success"><?php echo cur_symbol(); ?> <?php echo number_format($p['amount'], 2); ?></td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/payments/preview/<?php echo $p['payment_id']; ?>"><i class="bi bi-eye me-2 text-primary"></i> Preview Receipt</a></li>
                                        <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/payments/delete/<?php echo $p['payment_id']; ?>" onclick="return confirm('Cancel this payment?');"><i class="bi bi-trash me-2"></i> Cancel / Delete</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/payments/editPayIn/<?php echo $p['payment_id']; ?>"><i class="bi bi-pencil me-2 text-warning"></i> Edit</a></li>

                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($payments)) lx_pagination_links($payPg); ?>
    </div>
</div>