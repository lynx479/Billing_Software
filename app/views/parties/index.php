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

<!-- Full Width Parties Table -->
<div class="card shadow-sm border-0 w-100">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="partiesTable">
                <thead class="table-dark">
                    <tr>
                        <th>Party Name</th>
                        <th width="110px">Type</th>
                        <th>Business</th>
                        <th width="140px">Phone</th>
                        <th width="140px">State</th>
                        <th width="150px">GSTIN</th>
                        <th width="130px">PAN</th>
                        <th class="text-end" width="150px">Balance Due</th>
                        <th class="text-center" width="60px"></th>
                    </tr>
                </thead>
                <tbody id="partiesTableBody">
                    <?php if (empty($parties)): ?>
                        <tr id="noPartyRow">
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block text-muted mb-2"></i>
                                No parties found. Click "Create New Party" to add one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($parties as $p):
                            $bal = (float)($p['net_balance'] ?? $p['total_amount'] ?? 0);
                            $isSettled = (abs($bal) <= 0.005);
                        ?>
                        <tr class="party-row" style="cursor: pointer;"
                            onclick="window.location='<?php echo APP_URL; ?>/parties/partyView/<?php echo $p['party_id']; ?>'">
                            <td>
                                <strong class="text-dark"><?php echo htmlspecialchars($p['name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $p['party_type'] === 'CUSTOMER' ? 'primary' : 'warning text-dark'; ?>">
                                    <?php echo htmlspecialchars($p['party_type']); ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo htmlspecialchars($p['business_name'] ?? '-'); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($p['phone'] ?? '-'); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($p['state'] ?? '-'); ?></td>
                            <td><code class="small"><?php echo htmlspecialchars($p['gstin'] ?? '-'); ?></code></td>
                            <td><code class="small"><?php echo htmlspecialchars($p['pan'] ?? '-'); ?></code></td>
                            <td class="text-end fw-bold <?php echo $isSettled ? 'text-muted' : 'text-danger'; ?>">
                                <?php echo cur_symbol(); ?> <?php echo number_format($bal, 2); ?>
                            </td>
                            <td class="text-center">
                                <span class="btn btn-sm btn-outline-primary rounded-circle">
                                    <i class="bi bi-arrow-right"></i>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .party-row { position: relative; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('partySearchInput');
    const tbody = document.getElementById('partiesTableBody');

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();

        fetch('<?php echo APP_URL; ?>/parties/searchCreatedAjax?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';

                if (!data || data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No parties matching "${q}".
                            </td>
                        </tr>`;
                    return;
                }

                data.forEach(p => {
                    const bal = parseFloat(p.net_balance !== undefined ? p.net_balance : (p.total_amount || 0));
                    const isSettled = Math.abs(bal) <= 0.005;
                    const colorClass = isSettled ? 'text-muted' : 'text-danger';
                    const formattedBal = '<?php echo cur_symbol(); ?> ' +
                        bal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                    const badgeClass = p.party_type === 'CUSTOMER' ? 'primary' : 'warning text-dark';
                    const business = p.business_name || '-';
                    const phone    = p.phone || '-';
                    const state    = p.state || '-';
                    const gstin    = p.gstin || '-';
                    const pan      = p.pan || '-';

                    const tr = document.createElement('tr');
                    tr.className = 'party-row';
                    tr.innerHTML = `
                        <td>
                            <a href="<?php echo APP_URL; ?>/parties/partyView/${p.party_id}"
                               class="fw-bold text-dark text-decoration-none stretched-link">
                                ${p.name ?? ''}
                            </a>
                        </td>
                        <td><span class="badge bg-${badgeClass}">${p.party_type ?? ''}</span></td>
                        <td class="text-muted small text-truncate" style="max-width: 220px;"
                            title="${business}">${business}</td>
                        <td class="text-muted small">${phone}</td>
                        <td class="text-muted small">${state}</td>
                        <td class="d-none d-md-table-cell"><code class="small">${gstin}</code></td>
                        <td class="d-none d-md-table-cell"><code class="small">${pan}</code></td>
                        <td class="text-end fw-bold ${colorClass}"
                            style="font-variant-numeric: tabular-nums;">
                            ${formattedBal}
                        </td>
                        <td class="text-center">
                            <i class="bi bi-arrow-right text-primary"></i>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => console.error(err));
    });
});
</script>