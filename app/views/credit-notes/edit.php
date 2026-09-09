<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?php echo APP_URL; ?>/creditNotes/preview/<?php echo $creditNote['credit_note_id']; ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Credit Note
    </a>
    <h3 class="fw-bold text-uppercase mb-0 text-danger">Edit Credit Note <?php echo htmlspecialchars($creditNote['credit_note_number']); ?></h3>
    <span></span>
</div>

<?php if (!empty($hasLinks)): ?>
    <div class="alert alert-info d-flex align-items-start gap-2 shadow-sm">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            This credit note already has a payout linked to it, so line items and amounts can't be changed here.
            You can still update the party, date, place of supply, reason and notes below.
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0" style="max-width: 720px;">
    <form method="POST" action="<?php echo APP_URL; ?>/creditNotes/edit/<?php echo $creditNote['credit_note_id']; ?>">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Party <span class="text-danger">*</span></label>
                    <select name="party_id" class="form-select" required>
                        <?php foreach (($parties ?? []) as $p): ?>
                            <option value="<?php echo $p['party_id']; ?>" <?php echo ($p['party_id'] == $creditNote['party_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?><?php echo !empty($p['business_name']) ? ' - ' . htmlspecialchars($p['business_name']) : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Place of Supply</label>
                    <input type="text" name="place_of_supply" class="form-control" value="<?php echo htmlspecialchars($creditNote['place_of_supply'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Credit Note Date <span class="text-danger">*</span></label>
                    <input type="date" name="credit_note_date" class="form-control" value="<?php echo htmlspecialchars($creditNote['credit_note_date']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Reason</label>
                    <input type="text" name="reason" class="form-control" value="<?php echo htmlspecialchars($creditNote['reason'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($creditNote['notes'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <div class="p-3 bg-light rounded border small text-muted">
                        <strong>Line items (<?php echo count($creditNote['items'] ?? []); ?>) and the total amount
                        <?php echo cur_symbol(); ?> <?php echo number_format($creditNote['total_amount'] ?? 0, 2); ?> are not editable here.</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="<?php echo APP_URL; ?>/creditNotes/preview/<?php echo $creditNote['credit_note_id']; ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Save Changes</button>
        </div>
    </form>
</div>
