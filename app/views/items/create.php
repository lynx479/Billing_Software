<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Add Item / Service</h5>
        <a href="<?php echo APP_URL; ?>/items" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Items
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo APP_URL; ?>/items/create">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Item / Service Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Platform Commission Fee, Web Services">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SKU / Code</label>
                    <input type="text" name="sku" class="form-control" placeholder="SKU-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label">HSN / SAC Code</label>
                    <input type="text" name="hsn_sac" class="form-control" placeholder="998311">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Unit <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="unit" class="form-select" required>
                            <option value="">-- Select Unit --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?php echo htmlspecialchars($u['unit_symbol']); ?>">
                                    <?php echo htmlspecialchars($u['unit_name']); ?> (<?php echo htmlspecialchars($u['unit_symbol']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="<?php echo APP_URL; ?>/settings/units" class="btn btn-outline-secondary" title="Manage Units" target="_blank">
                            <i class="bi bi-gear"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Unit Base Price (<?php echo cur_symbol(); ?>) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" required value="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Applicable Tax Rate (%) <span class="text-danger">*</span></label>
                    <select name="tax_rate" class="form-select" required>
                        <?php if (empty($taxes)): ?>
                            <option value="18">18%</option>
                            <option value="12">12%</option>
                            <option value="5">5%</option>
                            <option value="0">0%</option>
                        <?php else: ?>
                            <?php foreach ($taxes as $t): ?>
                                <option value="<?php echo $t['tax_rate']; ?>" <?php echo ((float)$t['tax_rate'] === 18.0) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['tax_name']); ?> (<?php echo $t['tax_rate']; ?>%)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4 text-end">
                <a href="<?php echo APP_URL; ?>/items" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Save Item</button>
            </div>
        </form>
    </div>
</div>