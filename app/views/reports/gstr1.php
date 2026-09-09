<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-uppercase mb-0">GSTR-1 Report</h3>
        <p class="text-muted small mb-0">Outward supplies filing data for <strong><?php echo htmlspecialchars($monthLabel); ?></strong></p>
    </div>
    <form method="GET" action="<?php echo APP_URL; ?>/reports/gstr1" class="d-flex gap-2 align-items-end">
        <div>
            <label class="form-label small fw-bold text-muted mb-1">Filing Period</label>
            <input type="month" name="month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($month); ?>" onchange="this.form.submit()">
        </div>
    </form>
</div>

<!-- GSTR-1 Summary Tiles -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">B2B Taxable Value</span>
            <h5 class="fw-bold text-primary mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['b2b']['taxable'], 2); ?></h5>
            <small class="text-muted"><?php echo $gstr1Summary['b2b']['count']; ?> rate lines</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-info border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">B2C Total Taxable</span>
            <h5 class="fw-bold text-info mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['b2c_l']['taxable'] + $gstr1Summary['b2c_s']['taxable'], 2); ?></h5>
            <small class="text-muted"><?php echo $gstr1Summary['b2c_l']['count'] + $gstr1Summary['b2c_s']['count']; ?> rate lines</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-danger border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Credit Notes Taxable</span>
            <h5 class="fw-bold text-danger mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['cdn_b2b']['taxable'] + $gstr1Summary['cdn_b2c']['taxable'], 2); ?></h5>
            <small class="text-muted"><?php echo $gstr1Summary['cdn_b2b']['count'] + $gstr1Summary['cdn_b2c']['count']; ?> rate lines</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Net Tax Liability</span>
            <h5 class="fw-bold text-success mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['net_tax'], 2); ?></h5>
            <small class="text-muted">Net Taxable <?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['net_taxable'], 2); ?></small>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-3" id="gstr1Tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-b2b" type="button">1. B2B Summary</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-b2cl" type="button">2. B2C (Large)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-b2cs" type="button">3. B2C (Small)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cdnb2b" type="button">4. Credit Note – B2B</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cdnb2c" type="button">5. Credit Note – B2C</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-hsn-b2c" type="button">6. HSN B2C</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-hsn-b2b" type="button">7. HSN B2B</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-item" type="button">8. Item Summary</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-doc" type="button">9. Documents</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-summary" type="button">10. GSTR-1 Summary</button></li>
</ul>

<div class="tab-content">

    <!-- 1. B2B SUMMARY TAB -->
    <div class="tab-pane fade show active" id="tab-b2b">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">1. B2B Summary (Split per GST Tax Rate Category)</span>
                <a href="<?php echo APP_URL; ?>/reports/gstr1Export?tab=b2b&month=<?php echo htmlspecialchars($month); ?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>GSTIN</th>
                                <th>Party Name</th>
                                <th>Invoice Number</th>
                                <th>Invoice Date</th>
                                <th class="text-end">Invoice Value</th>
                                <th>Place of Supply</th>
                                <th class="text-center">GST Rate</th>
                                <th class="text-end">Tax Value</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($b2b)): ?>
                                <tr><td colspan="12" class="text-center py-4 text-muted"><i class="bi bi-inbox me-1"></i>No B2B invoices found for this period.</td></tr>
                            <?php else: $sno = 1; foreach ($b2b as $r): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td><code><?php echo htmlspecialchars($r['gstin']); ?></code></td>
                                    <td><?php echo htmlspecialchars($r['party_name']); ?></td>
                                    <td><strong class="text-primary"><?php echo htmlspecialchars($r['invoice_number']); ?></strong></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['invoice_date'])); ?></td>
                                    <td class="text-end"><?php echo number_format($r['invoice_value'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply']); ?></td>
                                    <td class="text-center fw-bold"><span class="badge bg-secondary"><?php echo (float)$r['gst_rate']; ?>%</span></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['tax_value'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. B2C (LARGE) TAB -->
    <div class="tab-pane fade" id="tab-b2cl">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">2. B2C (Large) Invoices (Interstate &gt; ₹2,50,000 to Unregistered Parties)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>Invoice Number</th>
                                <th>Invoice Date</th>
                                <th class="text-end">Invoice Value</th>
                                <th>Place of Supply</th>
                                <th class="text-center">Tax Rate</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($b2cLarge)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted">No B2C Large invoices in this period.</td></tr>
                            <?php else: $sno = 1; foreach ($b2cLarge as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong class="text-info"><?php echo htmlspecialchars($r['invoice_number']); ?></strong>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['invoice_date'])); ?></td>
                                    <td class="text-end"><?php echo number_format($r['invoice_value'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply']); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['tax_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. B2C (SMALL) TAB -->
    <div class="tab-pane fade" id="tab-b2cs">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">3. B2C (Small) Supplies (Grouped by POS &amp; Tax Rate)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>Place of Supply</th>
                                <th class="text-center">Tax Rate</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($b2cSmall)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No B2C Small supplies in this period.</td></tr>
                            <?php else: $sno = 1; foreach ($b2cSmall as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($r['place_of_supply']); ?>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['tax_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. CREDIT NOTE – B2B TAB -->
    <div class="tab-pane fade" id="tab-cdnb2b">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">4. Credit Notes – B2B (Registered Parties, Split per Tax Rate)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>GSTIN</th>
                                <th>Party Name</th>
                                <th>Credit Note Number</th>
                                <th>Credit Note Date</th>
                                <th class="text-end">Credit Note Value</th>
                                <th>Place of Supply</th>
                                <th class="text-center">GST Rate</th>
                                <th class="text-end">Tax Value</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($cnB2b)): ?>
                                <tr><td colspan="12" class="text-center py-4 text-muted">No B2B credit notes in this period.</td></tr>
                            <?php else: $sno = 1; foreach ($cnB2b as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td><code><?php echo htmlspecialchars($r['gstin']); ?></code></td>
                                    <td>
                                        <?php echo htmlspecialchars($r['party_name']); ?>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td><strong class="text-danger"><?php echo htmlspecialchars($r['credit_note_number']); ?></strong></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['credit_note_date'])); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($r['credit_note_value'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply']); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['gst_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['tax_value'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. CREDIT NOTE – B2C TAB -->
    <div class="tab-pane fade" id="tab-cdnb2c">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">5. Credit Notes – B2C (Unregistered Parties, Split per Tax Rate)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>Credit Note Number</th>
                                <th>Date</th>
                                <th class="text-end">Value</th>
                                <th>Place of Supply</th>
                                <th class="text-center">GST Rate</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($cnB2c)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted">No B2C credit notes in this period.</td></tr>
                            <?php else: $sno = 1; foreach ($cnB2c as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong class="text-danger"><?php echo htmlspecialchars($r['credit_note_number']); ?></strong>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['credit_note_date'])); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($r['credit_note_value'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply']); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['gst_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. HSN B2C TAB -->
    <div class="tab-pane fade" id="tab-hsn-b2c">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">6. HSN B2C Summary (Unregistered Consumers)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>HSN/SAC</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Tax Rate</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">Tax Amount</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($hsnB2c)): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No HSN data for B2C supplies.</td></tr>
                            <?php else: $sno = 1; foreach ($hsnB2c as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td>
                                        <code><?php echo htmlspecialchars($r['hsn_sac']); ?></code>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($r['unit'] ?? 'PCS'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['tax_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['total_quantity'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['tax_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. HSN B2B TAB -->
    <div class="tab-pane fade" id="tab-hsn-b2b">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">7. HSN B2B Summary (Registered Businesses)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>HSN/SAC</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Tax Rate</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">Tax Amount</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($hsnB2b)): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No HSN data for B2B supplies.</td></tr>
                            <?php else: $sno = 1; foreach ($hsnB2b as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td>
                                        <code><?php echo htmlspecialchars($r['hsn_sac']); ?></code>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($r['unit'] ?? 'PCS'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['tax_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['total_quantity'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['tax_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. ITEM SUMMARY TAB -->
    <div class="tab-pane fade" id="tab-item">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">8. Item Summary</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Tax Rate</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">Tax Amount</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if (empty($itemSummary)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No items sold in this period.</td></tr>
                            <?php else: $sno = 1; foreach ($itemSummary as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($r['item_name']); ?></strong>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($r['description']); ?></small></td>
                                    <td class="text-center"><?php echo htmlspecialchars($r['unit'] ?? 'PCS'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)$r['tax_rate']; ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['total_quantity'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['tax_amount'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. DOCUMENTS TAB -->
    <div class="tab-pane fade" id="tab-doc">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><span class="fw-bold">9. Document Summary (Nature of Document)</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>Nature of Document</th>
                                <th class="text-end">Total Issued</th>
                                <th class="text-end">Cancelled</th>
                                <th class="text-center">From No</th>
                                <th class="text-center">To No</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php $sno = 1; foreach ($documents as $d): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($d['nature_of_doc']); ?></strong></td>
                                    <td class="text-end fw-bold"><?php echo (int)($d['total_issued'] ?? 0); ?></td>
                                    <td class="text-end text-danger"><?php echo (int)($d['cancelled_count'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($d['from_no'] ?? '-'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($d['to_no'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 10. GSTR-1 SUMMARY TAB -->
    <div class="tab-pane fade" id="tab-summary">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><span class="fw-bold">10. GSTR-1 Filing Summary - <?php echo htmlspecialchars($monthLabel); ?></span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-dark small text-center">
                            <tr>
                                <th width="50px">S.No.</th>
                                <th>GSTR-1 Category / Table</th>
                                <th class="text-end">Records Count</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">Tax Amount</th>
                                <th class="text-end">Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr>
                                <td class="text-center">1</td>
                                <td>4A/4B/4C - B2B Invoices (Registered)</td>
                                <td class="text-end"><?php echo $gstr1Summary['b2b']['count']; ?></td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['b2b']['taxable'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['b2b']['tax'], 2); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($gstr1Summary['b2b']['total'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                <td>5 - B2C (Large) Invoices</td>
                                <td class="text-end"><?php echo $gstr1Summary['b2c_l']['count']; ?></td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['b2c_l']['taxable'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['b2c_l']['tax'], 2); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($gstr1Summary['b2c_l']['total'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>7 - B2C (Small) Invoices</td>
                                <td class="text-end"><?php echo $gstr1Summary['b2c_s']['count']; ?></td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['b2c_s']['taxable'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['b2c_s']['tax'], 2); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($gstr1Summary['b2c_s']['total'], 2); ?></td>
                            </tr>
                            <tr class="text-danger">
                                <td class="text-center">4</td>
                                <td>9B - Credit Notes (B2B Registered)</td>
                                <td class="text-end"><?php echo $gstr1Summary['cdn_b2b']['count']; ?></td>
                                <td class="text-end">(<?php echo number_format($gstr1Summary['cdn_b2b']['taxable'], 2); ?>)</td>
                                <td class="text-end">(<?php echo number_format($gstr1Summary['cdn_b2b']['tax'], 2); ?>)</td>
                                <td class="text-end fw-bold">(<?php echo number_format($gstr1Summary['cdn_b2b']['total'], 2); ?>)</td>
                            </tr>
                            <tr class="text-danger">
                                <td class="text-center">5</td>
                                <td>9B - Credit Notes (B2C Unregistered)</td>
                                <td class="text-end"><?php echo $gstr1Summary['cdn_b2c']['count']; ?></td>
                                <td class="text-end">(<?php echo number_format($gstr1Summary['cdn_b2c']['taxable'], 2); ?>)</td>
                                <td class="text-end">(<?php echo number_format($gstr1Summary['cdn_b2c']['tax'], 2); ?>)</td>
                                <td class="text-end fw-bold">(<?php echo number_format($gstr1Summary['cdn_b2c']['total'], 2); ?>)</td>
                            </tr>
                            <tr class="border-top fs-6 fw-bold table-light">
                                <td class="text-center">-</td>
                                <td>Net Outward Tax Liability</td>
                                <td class="text-end">-</td>
                                <td class="text-end"><?php echo number_format($gstr1Summary['net_taxable'], 2); ?></td>
                                <td class="text-end text-success"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['net_tax'], 2); ?></td>
                                <td class="text-end"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['net_total'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>