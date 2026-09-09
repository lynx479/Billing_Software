<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Created Parties (Customers &amp; Vendors)</h4>
        <p class="text-muted small mb-0">Browse or filter customers and vendors created in the system.</p>
    </div>
    <a href="<?php echo APP_URL; ?>/parties/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create New Party
    </a>
</div>

<!-- Full Width Search Bar -->
<div class="card shadow-sm border-0 mb-3 w-100">
    <div class="card-body p-2">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="partySearchInput" class="form-control border-start-0" placeholder="Type party name, business, phone, or GSTIN/PAN..." autocomplete="off">
        </div>
    </div>
</div>

<!-- Full Width Parties List -->
<div class="card shadow-sm border-0 w-100">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <span class="text-muted small fw-bold text-uppercase">Party / Contact Details</span>
        <span class="text-muted small fw-bold text-uppercase me-4">Balance Due</span>
    </div>
    
    <div class="list-group list-group-flush" id="partiesListGroup">
        <?php if (empty($parties)): ?>
            <div class="p-4 text-center text-muted" id="noPartyMessage">
                No custom parties found. Click "Create New Party" to add one.
            </div>
        <?php else: ?>
            <?php foreach ($parties as $p): 
                $bal = (float)($p['net_balance'] ?? $p['total_amount'] ?? 0);
                $isSettled = (abs($bal) <= 0.005);
            ?>
                <a href="<?php echo APP_URL; ?>/parties/partyView/<?php echo $p['party_id']; ?>" 
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-dark me-2"><?php echo htmlspecialchars($p['name']); ?></h6>
                            <span class="badge bg-<?php echo $p['party_type'] === 'CUSTOMER' ? 'primary' : 'warning text-dark'; ?>" style="font-size: 0.7rem;">
                                <?php echo $p['party_type']; ?>
                            </span>
                        </div>
                        <small class="text-muted">
                            <?php if (!empty($p['business_name'])): ?>
                                <span class="text-dark fw-semibold"><?php echo htmlspecialchars($p['business_name']); ?></span> • 
                            <?php endif; ?>
                            <?php echo htmlspecialchars($p['phone'] ?? ''); ?> • <?php echo htmlspecialchars($p['state']); ?>
                            <?php echo !empty($p['gstin']) ? ' • GST: ' . htmlspecialchars($p['gstin']) : ''; ?>
                            <?php echo !empty($p['pan']) ? ' • PAN: ' . htmlspecialchars($p['pan']) : ''; ?>
                        </small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold fs-6 me-3 <?php echo $isSettled ? 'text-muted' : 'text-danger'; ?>">
                            <?php echo cur_symbol(); ?> <?php echo number_format($bal, 2); ?>
                        </span>
                        <span class="btn btn-sm btn-outline-primary rounded-circle">
                            <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('partySearchInput');
    const listGroup = document.getElementById('partiesListGroup');

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();

        fetch('<?php echo APP_URL; ?>/parties/searchCreatedAjax?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                listGroup.innerHTML = '';
                if (!data || data.length === 0) {
                    listGroup.innerHTML = '<div class="p-4 text-center text-muted">No parties matching "' + q + '".</div>';
                    return;
                }

                data.forEach(p => {
                    const row = document.createElement('a');
                    row.href = '<?php echo APP_URL; ?>/parties/partyView/' + p.party_id;
                    row.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3';

                    const badgeClass = p.party_type === 'CUSTOMER' ? 'primary' : 'warning text-dark';
                    const business = p.business_name ? `<span class="text-dark fw-semibold">${p.business_name}</span> • ` : '';
                    const phone = p.phone ? `${p.phone} • ` : '';
                    const gst = p.gstin ? ` • GST: ${p.gstin}` : '';
                    const pan = p.pan ? ` • PAN: ${p.pan}` : '';

                    const bal = parseFloat(p.net_balance !== undefined ? p.net_balance : (p.total_amount || 0));
                    const isSettled = Math.abs(bal) <= 0.005;
                    const colorClass = isSettled ? 'text-muted' : 'text-danger';
                    const formattedBal = '<?php echo cur_symbol(); ?> ' + bal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                    row.innerHTML = `
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-dark me-2">${p.name}</h6>
                                <span class="badge bg-${badgeClass}" style="font-size: 0.7rem;">${p.party_type}</span>
                            </div>
                            <small class="text-muted">
                                ${business}${phone}${p.state}${gst}${pan}
                            </small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold fs-6 me-3 ${colorClass}">${formattedBal}</span>
                            <span class="btn btn-sm btn-outline-primary rounded-circle"><i class="bi bi-arrow-right"></i></span>
                        </div>
                    `;
                    listGroup.appendChild(row);
                });
            })
            .catch(err => console.error(err));
    });
});
</script>