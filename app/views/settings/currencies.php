<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Currencies &amp; Multipliers</h4>
        <p class="text-muted small mb-0">Manage billing currencies and their exchange multiplier relative to Base (INR ₹ = 1.0000).</p>
    </div>
</div>

<div class="row">
    <!-- Left Column: Currencies Table -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2">
                <span class="text-muted small fw-bold text-uppercase">Configured Currencies</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Currency Name</th>
                            <th>Symbol</th>
                            <th>Multiplier (vs INR)</th>
                            <th class="text-center" width="80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($currencies)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No currencies configured yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($currencies as $c): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($c['currency_code']); ?></strong>
                                    <?php if (!empty($c['is_default'])): ?>
                                        <span class="badge bg-success ms-1">Base</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($c['currency_name']); ?></td>
                                <td><span class="fs-6 fw-bold"><?php echo htmlspecialchars($c['symbol']); ?></span></td>
                                <td><code><?php echo number_format($c['exchange_rate'], 4); ?></code></td>
                                <td class="text-center">
                                    <?php if (empty($c['is_default'])): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a class="dropdown-item btn-edit-cur" href="javascript:void(0)"
                                                       data-id="<?php echo $c['currency_id']; ?>"
                                                       data-code="<?php echo htmlspecialchars($c['currency_code']); ?>"
                                                       data-name="<?php echo htmlspecialchars($c['currency_name']); ?>"
                                                       data-symbol="<?php echo htmlspecialchars($c['symbol']); ?>"
                                                       data-rate="<?php echo $c['exchange_rate']; ?>">
                                                        <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/settings/currencyDelete/<?php echo $c['currency_id']; ?>" onclick="return confirm('Are you sure you want to delete this currency?');">
                                                        <i class="bi bi-trash me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Default</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Add Currency Form -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-1 text-primary"></i> Add New Currency</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo APP_URL; ?>/settings/currencyCreate">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="currency_code" class="form-control text-uppercase" required placeholder="USD, EUR, AED">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="symbol" class="form-control" required placeholder="$, €, د.إ">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Currency Name <span class="text-danger">*</span></label>
                        <input type="text" name="currency_name" class="form-control" required placeholder="e.g. US Dollar, Euro">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Exchange Multiplier (vs 1 INR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" name="exchange_rate" class="form-control" required value="1.0000" placeholder="e.g. 0.0120">
                        <small class="text-muted">Example: If 1 USD = 83 INR, multiplier is <code>1 / 83 = 0.0120</code></small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Save Currency
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Currency -->
<div class="modal fade" id="editCurrencyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/settings/currencyEdit">
            <input type="hidden" name="currency_id" id="edit_cur_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Currency</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Code</label>
                            <input type="text" name="currency_code" id="edit_cur_code" class="form-control text-uppercase" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Symbol</label>
                            <input type="text" name="symbol" id="edit_cur_symbol" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Currency Name</label>
                        <input type="text" name="currency_name" id="edit_cur_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Exchange Multiplier</label>
                        <input type="number" step="0.0001" name="exchange_rate" id="edit_cur_rate" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Currency</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = new bootstrap.Modal(document.getElementById('editCurrencyModal'));
    document.querySelectorAll('.btn-edit-cur').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_cur_id').value = this.getAttribute('data-id');
            document.getElementById('edit_cur_code').value = this.getAttribute('data-code');
            document.getElementById('edit_cur_name').value = this.getAttribute('data-name');
            document.getElementById('edit_cur_symbol').value = this.getAttribute('data-symbol');
            document.getElementById('edit_cur_rate').value = this.getAttribute('data-rate');
            editModal.show();
        });
    });
});
</script>