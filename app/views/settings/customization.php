<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Company &amp; Document Customization</h4>
        <p class="text-muted small mb-0">Configure your business profile, numbering series, FY period, printable templates, and digital authorization.</p>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> All customization settings updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo APP_URL; ?>/settings/customization" enctype="multipart/form-data">
    <!-- SECTION 1: Business Identity & Branding -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-building me-2"></i>1. Business Identity &amp; Financial Year</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Company / Marketplace Legal Name <span class="text-danger">*</span></label>
                    <input type="text" name="settings[company_name]" class="form-control" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'LX India Marketplace'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Active Financial Year <span class="text-danger">*</span></label>
                    <select name="settings[financial_year]" class="form-select" required>
                        <?php 
                        $currentYear = (int)date('Y');
                        $selectedFY = $settings['financial_year'] ?? ($currentYear . '-' . ($currentYear + 1));
                        for ($y = $currentYear - 2; $y <= $currentYear + 3; $y++): 
                            $fyOption = $y . '-' . ($y + 1);
                        ?>
                            <option value="<?php echo $fyOption; ?>" <?php echo ($selectedFY === $fyOption) ? 'selected' : ''; ?>>
                                FY <?php echo $fyOption; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Company GSTIN</label>
                    <input type="text" name="settings[company_gstin]" class="form-control text-uppercase" value="<?php echo htmlspecialchars($settings['company_gstin'] ?? ''); ?>" placeholder="32AAAAA0000A1Z5">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Billing Support Email</label>
                    <input type="email" name="settings[company_email]" class="form-control" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Support Phone / Mobile</label>
                    <input type="text" name="settings[company_phone]" class="form-control" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Place of Supply (State) <span class="text-danger">*</span></label>
                    <input type="text" name="settings[company_state]" class="form-control" value="<?php echo htmlspecialchars($settings['company_state'] ?? 'Kerala'); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Default Currency <span class="text-danger">*</span></label>
                    <?php $activeCurrency = $settings['default_currency'] ?? cur_code(); ?>
                    <select name="settings[default_currency]" class="form-select" required>
                        <?php foreach (($currencies ?? []) as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['currency_code']); ?>" <?php echo ($activeCurrency === $c['currency_code']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['currency_code'] . ' - ' . $c['currency_name'] . ' (' . $c['symbol'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Applied to Tax Invoice, Credit Note, Pay-In and Pay-Out (creation &amp; preview).</small>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Registered Office Address</label>
                    <textarea name="settings[company_address]" class="form-control" rows="2"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Document Prefixes & Stacking Numbers -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-hash me-2"></i>2. Document Prefixes &amp; Stacking Sequences</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small">Configure document identifiers and sequential starting numbers. Example: Prefix <code>INV-</code> with number <code>1001</code> generates <code>INV-1001</code>.</p>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Document Type</th>
                            <th width="35%">Prefix Code</th>
                            <th width="35%">Starting / Stacking Sequence #</th>
                            <th>Sample Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><i class="bi bi-receipt text-primary me-2"></i> Tax Invoice</strong></td>
                            <td>
                                <input type="text" name="settings[invoice_prefix]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['invoice_prefix'] ?? 'INV-'); ?>" required>
                            </td>
                            <td>
                                <input type="number" name="settings[invoice_next_number]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['invoice_next_number'] ?? '1001'); ?>" required>
                            </td>
                            <td><code class="fw-bold"><?php echo htmlspecialchars($settings['invoice_prefix'] ?? 'INV-') . htmlspecialchars($settings['invoice_next_number'] ?? '1001'); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong><i class="bi bi-file-earmark-minus text-warning me-2"></i> Credit Note</strong></td>
                            <td>
                                <input type="text" name="settings[credit_note_prefix]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['credit_note_prefix'] ?? 'CN-'); ?>" required>
                            </td>
                            <td>
                                <input type="number" name="settings[credit_note_next_number]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['credit_note_next_number'] ?? '101'); ?>" required>
                            </td>
                            <td><code class="fw-bold"><?php echo htmlspecialchars($settings['credit_note_prefix'] ?? 'CN-') . htmlspecialchars($settings['credit_note_next_number'] ?? '101'); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong><i class="bi bi-arrow-down-left-circle text-success me-2"></i> Pay-In (Receipt)</strong></td>
                            <td>
                                <input type="text" name="settings[payin_prefix]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['payin_prefix'] ?? 'REC-'); ?>" required>
                            </td>
                            <td>
                                <input type="number" name="settings[payin_next_number]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['payin_next_number'] ?? '1001'); ?>" required>
                            </td>
                            <td><code class="fw-bold"><?php echo htmlspecialchars($settings['payin_prefix'] ?? 'REC-') . htmlspecialchars($settings['payin_next_number'] ?? '1001'); ?></code></td>
                        </tr>
                        <tr>
                            <td><strong><i class="bi bi-arrow-up-right-circle text-danger me-2"></i> Pay-Out (Voucher)</strong></td>
                            <td>
                                <input type="text" name="settings[payout_prefix]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['payout_prefix'] ?? 'PAY-'); ?>" required>
                            </td>
                            <td>
                                <input type="number" name="settings[payout_next_number]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($settings['payout_next_number'] ?? '1001'); ?>" required>
                            </td>
                            <td><code class="fw-bold"><?php echo htmlspecialchars($settings['payout_prefix'] ?? 'PAY-') . htmlspecialchars($settings['payout_next_number'] ?? '1001'); ?></code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Invoice Template & Media Uploads -->
    <div class="row g-4 mb-4">
        <!-- Template Selection -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-palette me-2"></i>3. Printable Invoice Template</h6>
                </div>
                <div class="card-body">
                    <?php $currTpl = $settings['invoice_template'] ?? 'classic_gst'; ?>
                    <div class="form-check p-3 border rounded mb-3 <?php echo ($currTpl === 'classic_gst') ? 'border-primary bg-light' : ''; ?>">
                        <input class="form-check-input" type="radio" name="settings[invoice_template]" id="tplClassic" value="classic_gst" <?php echo ($currTpl === 'classic_gst') ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold d-block" for="tplClassic">
                            Standard GST Template (Recommended)
                        </label>
                        <small class="text-muted">Includes complete Tax Breakdowns (CGST, SGST, IGST), HSN codes, and Terms.</small>
                    </div>

                    <div class="form-check p-3 border rounded mb-3 <?php echo ($currTpl === 'modern_clean') ? 'border-primary bg-light' : ''; ?>">
                        <input class="form-check-input" type="radio" name="settings[invoice_template]" id="tplModern" value="modern_clean" <?php echo ($currTpl === 'modern_clean') ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold d-block" for="tplModern">
                            Modern Minimalist Template
                        </label>
                        <small class="text-muted">Clean headers with prominent brand logo and streamlined line items.</small>
                    </div>

                    <div class="form-check p-3 border rounded <?php echo ($currTpl === 'thermal_slip') ? 'border-primary bg-light' : ''; ?>">
                        <input class="form-check-input" type="radio" name="settings[invoice_template]" id="tplThermal" value="thermal_slip" <?php echo ($currTpl === 'thermal_slip') ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold d-block" for="tplThermal">
                            Compact POS / Thermal Format (80mm)
                        </label>
                        <small class="text-muted">Ideal for quick physical slips and counter receipts.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo & Digital Signature -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-image me-2"></i>4. Logo &amp; Digital Signature</h6>
                </div>
                <div class="card-body">
                    <!-- Company Logo -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block">Company / Brand Logo</label>
                        <?php if (!empty($settings['company_logo']) && file_exists('../public/uploads/' . $settings['company_logo'])): ?>
                            <div class="mb-2 p-2 bg-light border rounded d-inline-block">
                                <img src="<?php echo APP_URL; ?>/public/uploads/<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Logo" style="max-height: 60px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="company_logo" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted">Recommended: PNG / JPG, transparent background, max 2MB.</small>
                    </div>

                    <!-- Digital Signature -->
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Authorized Signatory / Digital Signature</label>
                        <?php if (!empty($settings['digital_signature']) && file_exists('../public/uploads/' . $settings['digital_signature'])): ?>
                            <div class="mb-2 p-2 bg-light border rounded d-inline-block">
                                <img src="<?php echo APP_URL; ?>/public/uploads/<?php echo htmlspecialchars($settings['digital_signature']); ?>" alt="Signature" style="max-height: 50px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="digital_signature" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted">Appears at bottom-right on finalized Tax Invoices.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Terms & Footer Notes -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-chat-left-text me-2"></i>5. Default Footer Notes &amp; Legal Terms</h6>
        </div>
        <div class="card-body">
            <textarea name="settings[invoice_footer_notes]" class="form-control" rows="2"><?php echo htmlspecialchars($settings['invoice_footer_notes'] ?? 'Thank you for your business! All disputes subject to local jurisdiction.'); ?></textarea>
        </div>
    </div>

    <!-- Submit Bar -->
    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
            <i class="bi bi-save me-1"></i> Save All Customizations
        </button>
    </div>
</form>