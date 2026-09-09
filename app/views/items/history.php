<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo APP_URL; ?>/items" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Items List
        </a>
        <h3 class="fw-bold mb-0 text-primary">
            <i class="bi bi-clock-history me-2"></i>Product Movement History: <?php echo htmlspecialchars($item['name']); ?>
        </h3>
        <small class="text-muted">
            <strong>Category:</strong> <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?> | 
            <strong>Unit:</strong> <?php echo htmlspecialchars($item['unit']); ?> | 
            <strong>SKU:</strong> <?php echo htmlspecialchars($item['sku'] ?: '-'); ?> | 
            <strong>Type:</strong> <?php echo htmlspecialchars($item['item_type']); ?>
        </small>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i> Print History
        </button>
    </div>
</div>

<?php 
$isService = ($item['item_type'] === 'SERVICE');
$openingStock = (float)($item['opening_stock'] ?? 0.00);
$totalInQty = $openingStock;
$totalOutQty = 0;
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="120px">Type</th>
                        <th width="150px">Inv / Ref #</th>
                        <th>Party &amp; Company Details</th>
                        <th width="120px" class="text-center">Date</th>
                        <th width="110px" class="text-end">In Qty</th>
                        <th width="110px" class="text-end">Out Qty</th>
                        <th width="130px" class="text-end">Price (<?php echo cur_symbol(); ?>)</th>
                        <th width="120px" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- First Row: Opening Balance -->
                    <?php if (!$isService): ?>
                    <tr class="table-light fw-bold">
                        <td><span class="badge bg-secondary">Opening</span></td>
                        <td><code>-</code></td>
                        <td>
                            <strong class="text-dark">Opening Stock Inventory</strong>
                            <small class="d-block text-muted">Initial balance recorded in Item Master</small>
                        </td>
                        <td class="text-center">-</td>
                        <td class="text-end text-success fs-6"><?php echo number_format($openingStock, 2); ?></td>
                        <td class="text-end text-muted">0.00</td>
                        <td class="text-end"><?php echo cur_symbol(); ?> <?php echo number_format($item['price'], 2); ?></td>
                        <td class="text-center"><span class="badge bg-success">On Hand</span></td>
                    </tr>
                    <?php endif; ?>

                    <!-- Transaction Movement Rows -->
                    <?php if (empty($movements)): ?>
                        <?php if ($isService): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No sales or transactions recorded yet for this service.
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php foreach ($movements as $m): 
                            $inQty = (float)$m['in_qty'];
                            $outQty = (float)$m['out_qty'];
                            $totalInQty += $inQty;
                            $totalOutQty += $outQty;

                            $isSale = ($m['trans_type'] === 'Sale');
                            $badgeClass = $isSale ? 'bg-primary' : 'bg-warning text-dark';
                            $statusClass = in_array(strtoupper($m['doc_status']), ['PAID', 'ADJUSTED']) ? 'success' : 'danger';
                        ?>
                        <tr>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($m['trans_type']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($m['doc_number']); ?></strong></td>
                            <td>
                                <strong class="text-dark d-block"><?php echo htmlspecialchars($m['party_name'] ?: 'Customer'); ?></strong>
                                <?php if (!empty($m['company_name'])): ?>
                                    <small class="text-muted"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($m['company_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo date('d/m/Y', strtotime($m['trans_date'])); ?></td>
                            <td class="text-end text-success fw-bold">
                                <?php echo ($inQty > 0) ? number_format($inQty, 2) : '-'; ?>
                            </td>
                            <td class="text-end text-danger fw-bold">
                                <?php echo ($outQty > 0) ? number_format($outQty, 2) : '-'; ?>
                            </td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($m['price'], 2); ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $statusClass; ?> px-2 py-1"><?php echo htmlspecialchars($m['doc_status']); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

                <!-- Separate Summary Footer: Balance Quantity -->
                <?php if (!$isService): 
                    $netBalance = $totalInQty - $totalOutQty;
                ?>
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td colspan="4" class="text-end text-uppercase fs-6">Total In &amp; Out Summaries:</td>
                        <td class="text-end text-success fs-6"><?php echo number_format($totalInQty, 2); ?></td>
                        <td class="text-end text-danger fs-6"><?php echo number_format($totalOutQty, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="table-warning text-dark fs-5">
                        <td colspan="4" class="text-end fw-bold text-uppercase">Total Balance Quantity on Hand:</td>
                        <td colspan="2" class="text-center fw-bold text-dark fs-4">
                            <?php echo number_format($netBalance, 2); ?> <small class="fs-6"><?php echo htmlspecialchars($item['unit']); ?></small>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                <?php else: ?>
                <!-- Service Summary (No Physical Stock Balance) -->
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td colspan="4" class="text-end text-uppercase fs-6">Total Service Units Billed:</td>
                        <td colspan="2" class="text-center text-warning fs-5">
                            <?php echo number_format($totalOutQty, 2); ?> <small class="fs-6"><?php echo htmlspecialchars($item['unit']); ?></small>
                        </td>
                        <td colspan="2" class="text-center text-muted small">Service (Non-Inventory)</td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>