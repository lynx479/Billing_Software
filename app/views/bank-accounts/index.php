<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Bank & Cash Accounts</h4>
        <p class="text-muted small mb-0">Manage bank accounts, current balances, and account types.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
        <i class="bi bi-plus-lg me-1"></i> Add Bank Account
    </button>
</div>

<!-- Search Bar (Full Width, Matching Created Parties) -->
<div class="card shadow-sm border-0 mb-3 w-100">
    <div class="card-body p-2">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="bankSearchInput" class="form-control border-start-0" placeholder="Type account name, bank name, account number, or IFSC..." autocomplete="off">
        </div>
    </div>
</div>

<!-- Bank Accounts Table Card -->
<div class="card shadow-sm border-0 w-100">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Account Name</th>
                        <th>Bank Name</th>
                        <th>Account Number</th>
                        <th>IFSC Code</th>
                        <th>Account Type</th>
                        <th>Current Balance</th>
                        <th class="text-center" width="70px">Action</th>
                    </tr>
                </thead>
                <tbody id="bankTableBody">
                    <?php if (empty($accounts)): ?>
                        <tr id="noBankRow">
                            <td colspan="7" class="text-center py-4 text-muted">
                                No bank accounts configured. Click "Add Bank Account" to add one.
                            </td>
                        </tr>
                    <?php else: ?>
                         <?php foreach ($accounts as $a): ?>
                        <tr class="bank-row" style="cursor: pointer;" onclick="if (!event.target.closest('.dropdown')) window.location='<?php echo APP_URL; ?>/bankAccounts/show/<?php echo $a['bank_id']; ?>'">
                            <td><strong><?php echo htmlspecialchars($a['account_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($a['bank_name']); ?></td>
                            <td><code><?php echo htmlspecialchars($a['account_number']); ?></code></td>
                            <td><?php echo htmlspecialchars($a['ifsc_code']); ?></td>
                            <td>
                                 <?php $isCurrent = (($a['account_type'] ?? 'CURRENT') === 'CURRENT'); ?>
                                <span class="badge <?php echo $isCurrent ? 'badge-accent' : 'badge-neutral'; ?>">
                                    <?php echo htmlspecialchars($a['account_type'] ?? 'CURRENT'); ?>
                                 </span>
                            </td>
                            <td><strong class="text-success"><?php echo cur_symbol(); ?> <?php echo number_format($a['current_balance'], 2); ?></strong></td>
                                <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <a class="dropdown-item" href="<?php echo APP_URL; ?>/bankAccounts/show/<?php echo $a['bank_id']; ?>">
                                                <i class="bi bi-eye me-2 text-primary"></i> View Transactions
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item btn-edit-bank" href="javascript:void(0)" 
                                               data-id="<?php echo $a['bank_id']; ?>"
                                               data-name="<?php echo htmlspecialchars($a['account_name']); ?>"
                                               data-bank="<?php echo htmlspecialchars($a['bank_name']); ?>"
                                               data-number="<?php echo htmlspecialchars($a['account_number']); ?>"
                                               data-ifsc="<?php echo htmlspecialchars($a['ifsc_code']); ?>"
                                               data-type="<?php echo htmlspecialchars($a['account_type'] ?? 'CURRENT'); ?>"
                                               data-balance="<?php echo $a['current_balance']; ?>">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/bankAccounts/delete/<?php echo $a['bank_id']; ?>" onclick="return confirm('Are you sure you want to deactivate this bank account?');">
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

<!-- Modal 1: Add Bank Account -->
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/bankAccounts/create">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">New Bank Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" required placeholder="e.g. Primary Operations Account" maxlength="40">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" required placeholder="e.g. HDFC Bank, ICICI Bank" maxlength="40">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="account_number" class="form-control" required placeholder="50200012345678" maxlength="18">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">IFSC Code <span class="text-danger">*</span></label>
                            <input type="text" name="ifsc_code" class="form-control" required placeholder="HDFC0001234" maxlength="11">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <option value="CURRENT">Current Account</option>
                                <option value="SAVINGS">Savings Account</option>
                                <option value="OVERDRAFT">Overdraft (OD) Account</option>
                                <option value="CASH">Cash in Hand</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Opening Balance (<?php echo cur_symbol(); ?>)</label>
                            <input type="number" step="0.01" name="current_balance" class="form-control" value="0.00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Bank Account</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Bank Account -->
<div class="modal fade" id="editBankModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo APP_URL; ?>/bankAccounts/edit">
            <input type="hidden" name="bank_id" id="edit_bank_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Bank Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Account Name</label>
                        <input type="text" name="account_name" id="edit_account_name" class="form-control" required maxlength="40">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bank Name</label>
                        <input type="text" name="bank_name" id="edit_bank_name" class="form-control" required maxlength="40">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Account Number</label>
                            <input type="text" name="account_number" id="edit_account_number" class="form-control" required maxlength="18">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">IFSC Code</label>
                            <input type="text" name="ifsc_code" id="edit_ifsc_code" class="form-control" required maxlength="11">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Account Type</label>
                            <select name="account_type" id="edit_account_type" class="form-select" required>
                                <option value="CURRENT">Current Account</option>
                                <option value="SAVINGS">Savings Account</option>
                                <option value="OVERDRAFT">Overdraft (OD) Account</option>
                                <option value="CASH">Cash in Hand</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Current Balance (<?php echo cur_symbol(); ?>)</label>
                            <input type="number" step="0.01" name="current_balance" id="edit_current_balance" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Bank Account</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Instant Client-Side Prefix Filter
    const searchInput = document.getElementById('bankSearchInput');
    const rows = document.querySelectorAll('.bank-row');

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        rows.forEach(function (row) {
            const text = (row.textContent || row.innerText || '').toLowerCase();
            if (query === '' || text.indexOf(query) !== -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // 2. Edit Modal Population
    const editModal = new bootstrap.Modal(document.getElementById('editBankModal'));
    document.querySelectorAll('.btn-edit-bank').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_bank_id').value = this.getAttribute('data-id');
            document.getElementById('edit_account_name').value = this.getAttribute('data-name');
            document.getElementById('edit_bank_name').value = this.getAttribute('data-bank');
            document.getElementById('edit_account_number').value = this.getAttribute('data-number');
            document.getElementById('edit_ifsc_code').value = this.getAttribute('data-ifsc');
            document.getElementById('edit_account_type').value = this.getAttribute('data-type');
            document.getElementById('edit_current_balance').value = this.getAttribute('data-balance');
            editModal.show();
        });
    });
});
</script>