<!-- Header Area -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?php echo APP_URL; ?>/invoices/exportCsv?<?php echo http_build_query($filters); ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export as CSV
        </a>
    </div>
    <div>
        <h2 class="fw-bold text-uppercase mb-0 tracking-wide">TAX INVOICE</h2>
    </div>
    <div>
        <a href="<?php echo APP_URL; ?>/invoices/create" class="btn btn-primary btn-lg shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Tax Invoice
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo APP_URL; ?>/invoices" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['from_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['to_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Invoice Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Invoice #, Party, Phone..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Tax Amount</label>
                <input type="number" step="0.01" name="tax_amount" class="form-control form-control-sm" placeholder="e.g. 180.00" value="<?php echo htmlspecialchars($filters['tax_amount'] ?? ''); ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="<?php echo APP_URL; ?>/invoices" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Invoiced</span>
            <h4 class="fw-bold text-primary mb-0 mt-1">
                <?php echo cur_symbol(); ?> <?php echo number_format($summary['totalSales'] ?? $summary['totalAmount'] ?? 0, 2); ?>
            </h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Received</span>
            <h4 class="fw-bold text-success mb-0 mt-1">
                <?php echo cur_symbol(); ?> <?php echo number_format($summary['totalReceived'] ?? 0, 2); ?>
            </h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-danger border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Balance Due</span>
            <h4 class="fw-bold text-danger mb-0 mt-1">
                <?php echo cur_symbol(); ?> <?php echo number_format($summary['totalBalance'] ?? $summary['totalBalanceDue'] ?? 0, 2); ?>
            </h4>
        </div>
    </div>
</div>

<!-- Invoices 8-Column Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50px">#</th>
                        <th width="90px">Date</th>
                        <th width="70px">Time</th>
                        <th width="140px">Invoice No</th>
                        <th>Party Name</th>
                        <th>Payment Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center" width="120px">Status</th>
                        <th class="text-center" width="70px">Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-2 d-block text-muted mb-2"></i>
                                No tax invoices found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php list($pagedInvoices, $invPg) = lx_paginate($invoices, 20); $rowNo = $invPg['offset']; ?>
                        <?php foreach ($pagedInvoices as $inv): $rowNo++;
                            $totAmt = (float)($inv['total_amount'] ?? 0);
                            $directPaid = (float)($inv['paid_amount'] ?? 0);
                            $advLinked = (float)($inv['advance_linked_amount'] ?? 0);
                            $totPaid = $directPaid + $advLinked;
                            $balDue = max(0, $totAmt - $totPaid);

                            if ($balDue <= 0.005 && $totAmt > 0) {
                                $statusClass = 'success';
                                $statusLabel = 'PAID';
                            } elseif ($totPaid > 0.005) {
                                $statusClass = 'warning text-dark';
                                $statusLabel = 'PARTIALLY PAID';
                            } else {
                                $statusClass = 'danger';
                                $statusLabel = 'DUE';
                            }
                        ?>
                        <tr style="cursor: pointer;" onclick="if (!event.target.closest('.dropdown')) window.location='<?php echo APP_URL; ?>/invoices/preview/<?php echo $inv['invoice_id']; ?>'">
                            <td class="text-muted"><?php echo $rowNo; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?></td>
                            <td class="text-muted small"><?php echo !empty($inv['created_at']) ? date('H:i', strtotime($inv['created_at'])) : '-'; ?></td>
                            <td><strong class="text-primary"><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                            <td>
                                <strong class="text-dark"><?php echo htmlspecialchars($inv['party_name']); ?></strong>
                                <?php if (!empty($inv['business_name'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($inv['business_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($inv['payment_methods'] ?? 'Credit / Open'); ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($totAmt, 2); ?></td>
                            <td class="text-end fw-bold text-danger">
                                <?php echo cur_symbol(); ?> <?php echo number_format($balDue, 2); ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $statusClass; ?> px-2 py-1">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/invoices/preview/<?php echo $inv['invoice_id']; ?>"><i class="bi bi-eye me-2 text-primary"></i> Preview</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/invoices/preview/<?php echo $inv['invoice_id']; ?>?print=1" target="_blank"><i class="bi bi-printer me-2 text-secondary"></i> Download / Print PDF</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/invoices/duplicate/<?php echo $inv['invoice_id']; ?>"><i class="bi bi-copy me-2 text-info"></i> Duplicate</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/invoices/edit/<?php echo $inv['invoice_id']; ?>"><i class="bi bi-pencil me-2 text-warning"></i> Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/invoices/delete/<?php echo $inv['invoice_id']; ?>" onclick="return confirm('This cancels the invoice. It is blocked if any payment is linked to it. Continue?');"><i class="bi bi-trash me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($invoices)) lx_pagination_links($invPg); ?>
    </div>
</div>