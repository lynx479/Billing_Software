<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Item Categories</h4>
        <p class="text-muted small mb-0">Organize goods and services into categories for reporting and tax profiling.</p>
    </div>
</div>

<div class="row">
    <!-- Left Column: Categories Table -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2">
                <span class="text-muted small fw-bold text-uppercase">Configured Categories</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th class="text-center" width="80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    No categories configured yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['category_name']); ?></strong></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($c['description'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <a class="dropdown-item btn-edit-cat" href="javascript:void(0)"
                                                   data-id="<?php echo $c['category_id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($c['category_name']); ?>"
                                                   data-desc="<?php echo htmlspecialchars($c['description'] ?? ''); ?>">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/settings/categoryDelete/<?php echo $c['category_id']; ?>" onclick="return confirm('Are you sure you want to delete this category?');">
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

    <!-- Right Column: Add Category Form -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1 text-primary"></i> Add Category</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo APP_URL; ?>/settings/categoryCreate">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required placeholder="e.g. Electronics, Apparel, Grocery">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief category description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/settings/categoryEdit">
            <input type="hidden" name="category_id" id="edit_cat_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" name="category_name" id="edit_cat_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_cat_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    document.querySelectorAll('.btn-edit-cat').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_cat_id').value = this.getAttribute('data-id');
            document.getElementById('edit_cat_name').value = this.getAttribute('data-name');
            document.getElementById('edit_cat_desc').value = this.getAttribute('data-desc');
            editModal.show();
        });
    });
});
</script>