<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Measurement Units</h4>
        <p class="text-muted small mb-0">Manage units with standardized measurement types (e.g., Quantity, Weight, Length).</p>
    </div>
</div>

<div class="row">
    <!-- Left Column: Units Table -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2">
                <span class="text-muted small fw-bold text-uppercase">Configured Units</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Unit Name</th>
                            <th>Symbol</th>
                            <th>Unit Type</th>
                            <th class="text-center" width="80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($units)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No measurement units configured.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($units as $u): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($u['unit_name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($u['unit_symbol']); ?></code></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($u['unit_type'] ?? 'Quantity'); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <a class="dropdown-item btn-edit-unit" href="javascript:void(0)"
                                                   data-id="<?php echo $u['unit_id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($u['unit_name']); ?>"
                                                   data-symbol="<?php echo htmlspecialchars($u['unit_symbol']); ?>"
                                                   data-type="<?php echo htmlspecialchars($u['unit_type'] ?? 'Quantity'); ?>">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/settings/unitDelete/<?php echo $u['unit_id']; ?>" onclick="return confirm('Are you sure you want to delete this unit?');">
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

    <!-- Right Column: Add Unit Form -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1 text-primary"></i> Add Measurement Unit</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo APP_URL; ?>/settings/unitCreate">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" name="unit_name" class="form-control" required placeholder="e.g. Kilogram, Pieces">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Symbol / Short Code <span class="text-danger">*</span></label>
                        <input type="text" name="unit_symbol" class="form-control" required placeholder="e.g. KG, PCS">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Measurement Type <span class="text-danger">*</span></label>
                        <select name="unit_type" class="form-select" required>
                            <option value="Quantity">Quantity (e.g. Pieces, Boxes, Nos)</option>
                            <option value="Weight">Weight (e.g. Kilogram, Gram, Ton)</option>
                            <option value="Volume">Volume (e.g. Litre, Millilitre)</option>
                            <option value="Length">Length / Area (e.g. Meter, Sq. Ft)</option>
                            <option value="Service">Service (e.g. Service Unit, Visit)</option>
                            <option value="Time">Time (e.g. Hours, Days, Months)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Save Unit
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Unit -->
<div class="modal fade" id="editUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/settings/unitEdit">
            <input type="hidden" name="unit_id" id="edit_unit_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Measurement Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unit Name</label>
                        <input type="text" name="unit_name" id="edit_unit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Symbol / Short Code</label>
                        <input type="text" name="unit_symbol" id="edit_unit_symbol" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Measurement Type</label>
                        <select name="unit_type" id="edit_unit_type" class="form-select" required>
                            <option value="Quantity">Quantity</option>
                            <option value="Weight">Weight</option>
                            <option value="Volume">Volume</option>
                            <option value="Length">Length / Area</option>
                            <option value="Service">Service</option>
                            <option value="Time">Time</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Unit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = new bootstrap.Modal(document.getElementById('editUnitModal'));
    document.querySelectorAll('.btn-edit-unit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_unit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_unit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_unit_symbol').value = this.getAttribute('data-symbol');
            document.getElementById('edit_unit_type').value = this.getAttribute('data-type');
            editModal.show();
        });
    });
});
</script>