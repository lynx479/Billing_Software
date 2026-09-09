<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">GST Tax Rates</h4>
        <p class="text-muted small mb-0">Configure standard Indian GST slabs with automatic CGST/SGST splitting.</p>
    </div>
</div>

<div class="row">
    <!-- Left Column: Tax Rates Table -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2">
                <span class="text-muted small fw-bold text-uppercase">Configured Tax Slabs</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Tax Name</th>
                            <th>Total GST (%)</th>
                            <th>CGST + SGST (%)</th>
                            <th class="text-center" width="80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($taxes)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No tax slabs configured yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($taxes as $t): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($t['tax_name']); ?></strong></td>
                                <td><span class="badge badge-dark fs-6"><?php echo $t['tax_rate']; ?>%</span></td>
                                <td>
                                    <small class="text-muted">
                                        CGST: <strong><?php echo $t['cgst_rate']; ?>%</strong> | SGST: <strong><?php echo $t['sgst_rate']; ?>%</strong>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <a class="dropdown-item btn-edit-tax" href="javascript:void(0)"
                                                   data-id="<?php echo $t['tax_id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($t['tax_name']); ?>"
                                                   data-rate="<?php echo $t['tax_rate']; ?>">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/settings/taxDelete/<?php echo $t['tax_id']; ?>" onclick="return confirm('Are you sure you want to delete this tax slab?');">
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

    <!-- Right Column: Add Tax Slab Form -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1 text-primary"></i> Add Tax Slab</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo APP_URL; ?>/settings/taxCreate">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tax Slab Name <span class="text-danger">*</span></label>
                        <input type="text" name="tax_name" class="form-control" required placeholder="e.g. GST 18%, GST 5%">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Total GST Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="tax_rate" class="form-control" required placeholder="18.00">
                        <small class="text-muted">CGST and SGST will automatically be set to half of this percentage.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Save Tax Slab
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Tax Slab -->
<div class="modal fade" id="editTaxModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/settings/taxEdit">
            <input type="hidden" name="tax_id" id="edit_tax_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Tax Slab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tax Slab Name</label>
                        <input type="text" name="tax_name" id="edit_tax_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Total GST Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" id="edit_tax_rate" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Tax Slab</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = new bootstrap.Modal(document.getElementById('editTaxModal'));
    document.querySelectorAll('.btn-edit-tax').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_tax_id').value = this.getAttribute('data-id');
            document.getElementById('edit_tax_name').value = this.getAttribute('data-name');
            document.getElementById('edit_tax_rate').value = this.getAttribute('data-rate');
            editModal.show();
        });
    });
});
</script>