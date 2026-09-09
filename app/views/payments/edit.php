<div class="card shadow-sm border-0 max-w-lg mx-auto" style="max-width: 640px;">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit <?php echo ($type === 'PAY_IN' ? 'Pay In Receipt' : 'Pay Out Voucher'); ?>: <?php echo htmlspecialchars($payment['reference_number']); ?></h5>
    </div>

    <?php if (!empty($blockReason)): ?>
        <div class="alert alert-warning m-3 mb-0 d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Amount &amp; party are locked.</strong><br>
                <?php echo htmlspecialchars($blockReason); ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>/payments/<?php echo ($type === 'PAY_IN' ? 'editPayIn' : 'editPayOut'); ?>">
        <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Party</label>
                    <?php if (empty($blockReason)): ?>
                        <select name="party_id" class="form-select">
                            <?php foreach (($parties ?? []) as $p): ?>
                                <option value="<?php echo $p['party_id']; ?>" <?php echo ($p['party_id'] == $payment['party_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?><?php echo !empty($p['business_name']) ? ' - ' . htmlspecialchars($p['business_name']) : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payment['party_name']); ?>" disabled>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Amount (<?php echo cur_symbol(); ?>)</label>
                    <?php if (empty($blockReason)): ?>
                        <input type="number" step="0.01" name="amount" class="form-control fw-bold" value="<?php echo htmlspecialchars($payment['amount']); ?>" required>
                    <?php else: ?>
                        <input type="text" class="form-control fw-bold" value="<?php echo cur_symbol(); ?> <?php echo number_format($payment['amount'], 2); ?>" disabled>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Bank / Cash Account</label>
                    <select name="bank_id" class="form-select">
                        <?php foreach (($banks ?? []) as $b): ?>
                            <option value="<?php echo $b['bank_id']; ?>" <?php echo ($b['bank_id'] == $payment['bank_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($b['bank_name']); ?> - <?php echo htmlspecialchars($b['account_number']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <?php foreach (['Bank Account' => 'Bank Transfer / IMPS', 'UPI' => 'UPI / QR Code', 'Cheque' => 'Cheque', 'Cash' => 'Cash', 'Credit Card' => 'Credit / Debit Card'] as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo ($payment['payment_method'] === $val) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Reference Number</label>
                    <input type="text" name="reference_number" class="form-control" value="<?php echo htmlspecialchars($payment['reference_number']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="<?php echo htmlspecialchars($payment['payment_date']); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($payment['notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="<?php echo APP_URL; ?>/payments/<?php echo ($type === 'PAY_IN' ? 'payIn' : 'payOut'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Update</button>
        </div>
    </form>
</div>
