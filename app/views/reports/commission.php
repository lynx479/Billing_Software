<h4 class="fw-bold mb-3">Marketplace Commission Report</h4>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark"><tr><th>Reference</th><th>Seller</th><th>Gross Sales</th><th>Commission Earned</th><th>GST on Fee</th></tr></thead>
            <tbody>
                <?php foreach ($commissions as $c): ?>
                    <tr><td><?php echo $c['settlement_reference']; ?></td><td><?php echo $c['store_name']; ?></td><td><?php echo cur_symbol(); ?><?php echo number_format($c['gross_sales'],2); ?></td><td class="text-success fw-bold"><?php echo cur_symbol(); ?><?php echo number_format($c['commission_amount'],2); ?></td><td><?php echo cur_symbol(); ?><?php echo number_format($c['tax_on_commission'],2); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>