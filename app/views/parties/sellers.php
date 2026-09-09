<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Seller Parties (Marketplace)</h4>
</div>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body p-2">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="sellerSearchInput" class="form-control border-start-0" placeholder="Type store name, owner name, state, or GSTIN..." autocomplete="off">
        </div>
    </div>
</div>

<!-- Sellers List -->
<div class="card shadow-sm border-0">
    <div class="card-header list-head d-flex justify-content-between align-items-center">
        <span class="list-head-label">Store / Owner Details</span>
        <span class="list-head-label me-4">Total Amount</span>
    </div>
    <div class="list-group list-group-flush" id="sellersListGroup">
        <?php if (empty($sellers)): ?>
            <div class="p-4 text-center text-muted" id="noSellerMessage">No marketplace sellers found.</div>
        <?php else: ?>
            <?php foreach ($sellers as $s): ?>
                <a href="<?php echo APP_URL; ?>/parties/sellerView/<?php echo $s['seller_id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($s['store_name']); ?></h6>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($s['owner_name']); ?> (<?php echo htmlspecialchars($s['state']); ?><?php echo !empty($s['gstin']) ? ' | GSTIN: ' . htmlspecialchars($s['gstin']) : ''; ?>)
                        </small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold fs-6 text-dark me-3"><?php echo cur_symbol(); ?> <?php echo number_format($s['total_amount'], 2); ?></span>
                        <span class="btn btn-sm btn-outline-primary rounded-circle"><i class="bi bi-arrow-right"></i></span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('sellerSearchInput');
    const listGroup = document.getElementById('sellersListGroup');

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();

        fetch('<?php echo APP_URL; ?>/parties/searchSellersAjax?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                listGroup.innerHTML = '';
                if (!data || data.length === 0) {
                    listGroup.innerHTML = '<div class="p-4 text-center text-muted">No sellers matching "' + q + '".</div>';
                    return;
                }

                data.forEach(s => {
                    const row = document.createElement('a');
                    row.href = '<?php echo APP_URL; ?>/parties/sellerView/' + s.seller_id;
                    row.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3';
                    
                    const gstinLabel = s.gstin ? ' | GSTIN: ' + s.gstin : '';
                    const totalFormatted = new Intl.NumberFormat(undefined, { style: 'currency', currency: '<?php echo cur_code(); ?>' }).format(s.total_amount);

                    row.innerHTML = `
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">${s.store_name}</h6>
                            <small class="text-muted">${s.owner_name} (${s.state}${gstinLabel})</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold fs-6 text-dark me-3">${totalFormatted}</span>
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