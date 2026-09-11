<!-- Header Area -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?php echo APP_URL; ?>/creditNotes/exportCsv?<?php echo http_build_query($filters); ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export as CSV
        </a>
    </div>
    <div>
        <h2 class="fw-bold text-uppercase mb-0 tracking-wide">CREDIT NOTE</h2>
    </div>
    <div>
        <a href="<?php echo APP_URL; ?>/creditNotes/create" class="btn btn-primary btn-lg shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Credit Note
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo APP_URL; ?>/creditNotes" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['from_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['to_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Credit Note Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by CN #, Party, Invoice #..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
            </div>
           
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="<?php echo APP_URL; ?>/creditNotes" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Credit Note Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Credit Amount</span>
            <h4 class="fw-bold text-primary mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalCredit'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Adjusted / Settled</span>
            <h4 class="fw-bold text-success mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalAdjusted'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-danger border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Open Balance</span>
            <h4 class="fw-bold text-danger mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($summary['totalBalance'], 2); ?></h4>
        </div>
    </div>
</div>

<!-- Credit Notes 8-Column Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50px">#</th>
                        <th width="90px">Date</th>
                        <th width="70px">Time</th>
                        <th width="150px">Credit Note No</th>
                        <th>Party Name</th>
                        <th>Against Invoice</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center" width="120px">Status</th>
                        <th class="text-center" width="70px">Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($creditNotes)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-minus fs-2 d-block text-muted mb-2"></i>
                                No credit notes found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php list($pagedCreditNotes, $cnPg) = lx_paginate($creditNotes, 20); $rowNo = $cnPg['offset']; ?>
                        <?php foreach ($pagedCreditNotes as $cn): $rowNo++;
                            $statusClass = 'danger';
                            if ($cn['status'] === 'ADJUSTED') $statusClass = 'success';
                            elseif ($cn['status'] === 'PARTIALLY_ADJUSTED') $statusClass = 'warning text-dark';
                        ?>
                        <tr style="cursor: pointer;" onclick="if (!event.target.closest('.dropdown')) window.location='<?php echo APP_URL; ?>/creditNotes/preview/<?php echo $cn['credit_note_id']; ?>'">
                            <td class="text-muted"><?php echo $rowNo; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($cn['credit_note_date'])); ?></td>
                            <td class="text-muted small"><?php echo !empty($cn['created_at']) ? date('H:i', strtotime($cn['created_at'])) : '-'; ?></td>
                            <td><strong class="text-primary"><?php echo htmlspecialchars($cn['credit_note_number']); ?></strong></td>
                            <td>
                                <strong class="text-dark"><?php echo htmlspecialchars($cn['party_name']); ?></strong>
                                <?php if (!empty($cn['business_name'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($cn['business_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($cn['original_invoice_no'])): ?>
                                    <span class="badge bg-light text-primary border">
                                        <?php echo htmlspecialchars($cn['original_invoice_no']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Direct / None</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($cn['total_amount'], 2); ?></td>
                            <td class="text-end <?php echo $cn['balance_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                <?php echo cur_symbol(); ?> <?php echo number_format($cn['balance_amount'], 2); ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $statusClass; ?> px-2 py-1">
                                    <?php echo ($cn['status'] === 'OPEN') ? 'Open' : (($cn['status'] === 'ADJUSTED') ? 'Adjusted' : 'Partially Adjusted'); ?>
                                </span>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/creditNotes/preview/<?php echo $cn['credit_note_id']; ?>"><i class="bi bi-eye me-2 text-primary"></i> Preview</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/creditNotes/preview/<?php echo $cn['credit_note_id']; ?>?print=1" target="_blank"><i class="bi bi-printer me-2 text-secondary"></i> Download / Print PDF</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/creditNotes/duplicate/<?php echo $cn['credit_note_id']; ?>"><i class="bi bi-copy me-2 text-info"></i> Duplicate</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/creditNotes/edit/<?php echo $cn['credit_note_id']; ?>"><i class="bi bi-pencil me-2 text-warning"></i> Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/creditNotes/delete/<?php echo $cn['credit_note_id']; ?>" onclick="return confirm('This permanently deletes the credit note. It is blocked if any payout is linked to it. Continue?');"><i class="bi bi-trash me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($creditNotes)) lx_pagination_links($cnPg); ?>
    </div>
</div>