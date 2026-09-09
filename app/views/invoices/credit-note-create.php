<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">Issue Credit Note</h5></div>
    <div class="card-body">
        <form method="POST" action="<?php echo APP_URL; ?>/invoices/creditNoteCreate">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Select Original Invoice</label>
                    <select name="invoice_id" class="form-select" required>
                        <option value="">-- Choose Invoice --</option>
                        <?php foreach ($invoices as $inv): ?>
                            <option value="<?php echo $inv['invoice_id']; ?>" data-party="<?php echo $inv['party_id']; ?>">
                                <?php echo htmlspecialchars($inv['invoice_number'] . ' - ' . $inv['party_name'] . ' (<?php echo cur_symbol(); ?>' . $inv['total_amount'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="party_id" value="1">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Credit Note Date</label>
                    <input type="date" name="credit_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtotal Refund Amount (<?php echo cur_symbol(); ?>)</label>
                    <input type="number" name="subtotal" step="0.01" class="form-control" required value="0.00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tax Adjustment (<?php echo cur_symbol(); ?>)</label>
                    <input type="number" name="tax_amount" step="0.01" class="form-control" required value="0.00">
                </div>
                <div class="col-12">
                    <label class="form-label">Reason for Credit Note</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Goods returned / Overbilled adjustment"></textarea>
                </div>
            </div>
            <div class="mt-4 text-end">
                <a href="<?php echo APP_URL; ?>/invoices" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-danger px-4"><i class="bi bi-file-earmark-diff"></i> Issue Credit Note</button>
            </div>
        </form>
    </div>
</div>