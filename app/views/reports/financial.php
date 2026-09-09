<h4 class="fw-bold mb-3">Financial Performance Summary</h4>
<div class="card shadow-sm border-0 p-4">
    <p><strong>Total Revenue Invoiced:</strong> <?php echo cur_symbol(); ?> <?php echo number_format($metrics['total_sales'], 2); ?></p>
    <p><strong>Total GST Collected:</strong> <?php echo cur_symbol(); ?> <?php echo number_format($metrics['total_tax'], 2); ?></p>
    <p><strong>Marketplace Commission Earned:</strong> <?php echo cur_symbol(); ?> <?php echo number_format($metrics['total_commission'], 2); ?></p>
</div>