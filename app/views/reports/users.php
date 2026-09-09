<h4 class="fw-bold mb-3">User & Seller Report</h4>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark"><tr><th>Store</th><th>Owner</th><th>Email</th><th>Commission</th></tr></thead>
            <tbody>
                <?php foreach ($sellers as $s): ?>
                    <tr><td><?php echo $s['store_name']; ?></td><td><?php echo $s['owner_name']; ?></td><td><?php echo $s['email']; ?></td><td><?php echo $s['commission_rate']; ?>%</td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>