<h4 class="fw-bold mb-3">Sales Report</h4>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark"><tr><th>Invoice #</th><th>Party</th><th>Date</th><th>Tax</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($invoices as $i): ?>
                    <tr><td><?php echo $i['invoice_number']; ?></td><td><?php echo $i['party_name']; ?></td><td><?php echo $i['invoice_date']; ?></td><td><?php echo cur_symbol(); ?><?php echo number_format($i['tax_amount'],2); ?></td><td><?php echo cur_symbol(); ?><?php echo number_format($i['total_amount'],2); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>