<h4 class="fw-bold mb-3">Seller Settlement Report</h4>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark"><tr><th>Ref</th><th>Seller</th><th>Period</th><th>Net Paid</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($settlements as $s): ?>
                    <tr><td><?php echo $s['settlement_reference']; ?></td><td><?php echo $s['store_name']; ?></td><td><?php echo $s['period_start'].' to '.$s['period_end']; ?></td><td><?php echo cur_symbol(); ?><?php echo number_format($s['net_payable'],2); ?></td><td><span class="badge bg-info"><?php echo $s['status']; ?></span></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>