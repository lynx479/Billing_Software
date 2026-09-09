<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold text-uppercase mb-0 tracking-wide" >ITEM &amp; PRODUCT MASTER</h3>
        <p class="text-muted small mb-0">Manage goods, stock balances, service catalogs, tax rates, and click rows to view dedicated movement history.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Item
        </button>
    </div>
</div>

<!-- Live Search Filter Bar (Active on >= 3 chars) -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="itemSearchInput" class="form-control" placeholder="Search by Item Name, SKU, HSN, or Description (Type 3+ letters)...">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted small">Showing <strong id="itemCountDisplay"><?php echo count($items); ?></strong> item(s)</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Items Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="itemsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>SKU / HSN</th>
                        <th>Unit</th>
                        <th class="text-end">Price (<?php echo cur_symbol(); ?>)</th>
                        <th class="text-center">Tax Rate</th>
                        <th class="text-end">Balance Qty</th>
                        <th class="text-center" width="70px">Options</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-2 d-block text-muted mb-2"></i>
                                No items found in master catalog.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $db = (new Model())->getDb();
                        foreach ($items as $it): 
                            $isService = ($it['item_type'] === 'SERVICE');
                            $searchString = strtolower($it['name'] . ' ' . ($it['description'] ?? '') . ' ' . ($it['sku'] ?? '') . ' ' . ($it['hsn_sac'] ?? '') . ' ' . ($it['category_name'] ?? ''));

                            // Compute Real-time Balance Quantity = Opening Stock - Sold Out Qty + Credited In Qty
                            $balanceQty = 0;
                            if (!$isService) {
                                $stmtSold = $db->prepare("SELECT COALESCE(SUM(quantity), 0) AS total_sold 
                                                          FROM acc_invoice_items it 
                                                          JOIN acc_invoices inv ON it.invoice_id = inv.invoice_id 
                                                          WHERE (it.item_name = ? OR it.item_name = ?) AND inv.status != 'CANCELLED'");
                                $stmtSold->execute([$it['name'], $it['sku']]);
                                $totalSold = (float)$stmtSold->fetch()['total_sold'];

                                $stmtReturned = $db->prepare("SELECT COALESCE(SUM(quantity), 0) AS total_returned 
                                                              FROM acc_credit_note_items cni 
                                                              JOIN acc_credit_notes cn ON cni.credit_note_id = cn.credit_note_id 
                                                              WHERE (cni.item_name = ? OR cni.item_name = ?)");
                                $stmtReturned->execute([$it['name'], $it['sku']]);
                                $totalReturned = (float)$stmtReturned->fetch()['total_returned'];

                                $balanceQty = ((float)$it['opening_stock'] + $totalReturned) - $totalSold;
                            }
                        ?>
                        <tr class="item-data-row" 
                            style="cursor: pointer;" 
                            data-search="<?php echo htmlspecialchars($searchString); ?>"
                            onclick="if (!event.target.closest('.dropdown')) window.location='<?php echo APP_URL; ?>/items/history/<?php echo $it['item_id']; ?>'">
                            
                            <td>
                                <strong><?php echo htmlspecialchars($it['name']); ?></strong>
                                <?php if ($isService): ?>
    <span class="badge ms-1 chip-accent">Service</span>
<?php endif; ?>
                            </td>
                            <td class="text-muted small" style="max-width: 220px;">
                                <?php echo !empty($it['description']) ? htmlspecialchars($it['description']) : '-'; ?>
                            </td>
                            <td>
                                <span class="badge chip-accent">
    <?php echo htmlspecialchars($it['category_name'] ?? 'General'); ?>
</span>
                            </td>
                            <td>
                                <small class="d-block"><strong>SKU:</strong> <?php echo htmlspecialchars($it['sku'] ?: '-'); ?></small>
                                <small class="d-block text-muted">
                                    <strong><?php echo ($isService ? 'SAC:' : 'HSN:'); ?></strong> 
                                    <?php echo htmlspecialchars($it['hsn_sac'] ?: '-'); ?>
                                </small>
                            </td>
                            <td><code class="chip-accent"><?php echo htmlspecialchars($it['unit'] ?: 'PCS'); ?></code></td>
                            <td class="text-end fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($it['price'], 2); ?></td>
                            <td class="text-center">
    <span class="badge badge-dark">
        <?php echo $it['tax_rate']; ?>%
    </span>
</td>
                            <td class="text-end">
                                <?php if ($isService): ?>
    <span class="badge chip-accent">N/A</span>
<?php else: ?>
                                    <strong class="<?php echo ($balanceQty <= 5) ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($balanceQty, 2); ?>
                                    </strong>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <a class="dropdown-item" href="<?php echo APP_URL; ?>/items/history/<?php echo $it['item_id']; ?>">
                                                <i class="bi bi-clock-history me-2 text-info"></i> Movement History
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item btn-edit-item" href="javascript:void(0)"
                                               data-id="<?php echo $it['item_id']; ?>"
                                               data-name="<?php echo htmlspecialchars($it['name']); ?>"
                                               data-desc="<?php echo htmlspecialchars($it['description'] ?? ''); ?>"
                                               data-category="<?php echo $it['category_id'] ?? ''; ?>"
                                               data-type="<?php echo $it['item_type'] ?? 'PRODUCT'; ?>"
                                               data-sku="<?php echo htmlspecialchars($it['sku'] ?? ''); ?>"
                                               data-hsn="<?php echo htmlspecialchars($it['hsn_sac'] ?? ''); ?>"
                                               data-unit="<?php echo htmlspecialchars($it['unit'] ?? 'PCS'); ?>"
                                               data-price="<?php echo $it['price']; ?>"
                                               data-tax="<?php echo $it['tax_rate']; ?>"
                                               data-opening="<?php echo $it['opening_stock']; ?>">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/items/delete/<?php echo $it['item_id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </a>
                                        </li>
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

<!-- Modal: Add Item -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?php echo APP_URL; ?>/items/create">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add New Item / Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Wireless Keyboard, Annual Maintenance">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Item Type <span class="text-danger">*</span></label>
                            <select name="item_type" id="addItemTypeSelect" class="form-select" required>
                                <option value="PRODUCT">Product (Goods - Tracks Stock)</option>
                                <option value="SERVICE">Service (No Stock Tracking)</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Item Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Enter specifications or item description..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">SKU Code</label>
                            <input type="text" name="sku" class="form-control" placeholder="e.g. SKU-1002">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac" class="form-control" placeholder="e.g. 8471">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Measurement Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?php echo $u['unit_symbol']; ?>"><?php echo htmlspecialchars($u['unit_name']); ?> (<?php echo $u['unit_symbol']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Selling Price (<?php echo cur_symbol(); ?>) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tax Rate (%) <span class="text-danger">*</span></label>
                            <select name="tax_rate" class="form-select" required>
                                <?php foreach ($taxes as $tx): ?>
                                    <option value="<?php echo $tx['tax_rate']; ?>"><?php echo htmlspecialchars($tx['tax_name']); ?> (<?php echo $tx['tax_rate']; ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6" id="addOpeningStockDiv">
                            <label class="form-label fw-bold">Opening Stock Quantity</label>
                            <input type="number" step="0.01" name="opening_stock" class="form-control" value="0.00">
                            <small class="text-muted">Initial inventory balance on hand.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Save Item</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Item (Exact match with Add Item) -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?php echo APP_URL; ?>/items/edit">
            <input type="hidden" name="item_id" id="edit_item_id">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Item Type <span class="text-danger">*</span></label>
                            <select name="item_type" id="edit_item_type" class="form-select" required>
                                <option value="PRODUCT">Product (Goods - Tracks Stock)</option>
                                <option value="SERVICE">Service (No Stock Tracking)</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Item Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" id="edit_category_id" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">SKU Code</label>
                            <input type="text" name="sku" id="edit_sku" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac" id="edit_hsn" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Measurement Unit <span class="text-danger">*</span></label>
                            <select name="unit" id="edit_unit" class="form-select" required>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?php echo $u['unit_symbol']; ?>"><?php echo htmlspecialchars($u['unit_name']); ?> (<?php echo $u['unit_symbol']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Selling Price (<?php echo cur_symbol(); ?>) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tax Rate (%) <span class="text-danger">*</span></label>
                            <select name="tax_rate" id="edit_tax_rate" class="form-select" required>
                                <?php foreach ($taxes as $tx): ?>
                                    <option value="<?php echo $tx['tax_rate']; ?>"><?php echo htmlspecialchars($tx['tax_name']); ?> (<?php echo $tx['tax_rate']; ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6" id="editOpeningStockDiv">
                            <label class="form-label fw-bold">Opening Stock Quantity</label>
                            <input type="number" step="0.01" name="opening_stock" id="edit_opening_stock" class="form-control" value="0.00">
                            <small class="text-muted">Initial inventory balance on hand.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Update Item</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Filter on >= 3 characters
    const searchInput = document.getElementById('itemSearchInput');
    const rows = document.querySelectorAll('.item-data-row');
    const countDisplay = document.getElementById('itemCountDisplay');

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();

        if (query.length === 0) {
            rows.forEach(r => r.style.display = '');
            countDisplay.textContent = rows.length;
            return;
        }

        if (query.length >= 3) {
            let matches = 0;
            rows.forEach(r => {
                const text = r.getAttribute('data-search');
                if (text && text.includes(query)) {
                    r.style.display = '';
                    matches++;
                } else {
                    r.style.display = 'none';
                }
            });
            countDisplay.textContent = matches;
        }
    });

    // 2. Hide Opening Stock for Services in Add Modal
    const addTypeSelect = document.getElementById('addItemTypeSelect');
    const addStockDiv = document.getElementById('addOpeningStockDiv');
    addTypeSelect.addEventListener('change', function () {
        addStockDiv.style.display = (this.value === 'SERVICE') ? 'none' : 'block';
    });

    // 3. Hide Opening Stock for Services in Edit Modal
    const editTypeSelect = document.getElementById('edit_item_type');
    const editStockDiv = document.getElementById('editOpeningStockDiv');
    editTypeSelect.addEventListener('change', function () {
        editStockDiv.style.display = (this.value === 'SERVICE') ? 'none' : 'block';
    });

    // 4. Edit Item Modal Population
    const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    document.querySelectorAll('.btn-edit-item').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_item_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_description').value = this.getAttribute('data-desc');
            document.getElementById('edit_category_id').value = this.getAttribute('data-category');
            document.getElementById('edit_item_type').value = this.getAttribute('data-type');
            document.getElementById('edit_sku').value = this.getAttribute('data-sku');
            document.getElementById('edit_hsn').value = this.getAttribute('data-hsn');
            document.getElementById('edit_unit').value = this.getAttribute('data-unit');
            document.getElementById('edit_price').value = this.getAttribute('data-price');
            document.getElementById('edit_tax_rate').value = this.getAttribute('data-tax');
            document.getElementById('edit_opening_stock').value = this.getAttribute('data-opening');

            editStockDiv.style.display = (this.getAttribute('data-type') === 'SERVICE') ? 'none' : 'block';
            editModal.show();
        });
    });
});
</script>