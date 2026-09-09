<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Accounting Dashboard</h3>
    <span class="badge bg-success p-2">System Status: Standalone Operational</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 border-start border-primary border-4">
            <span class="text-muted small text-uppercase fw-bold">Total Invoiced Sales</span>
            <h4 class="mt-2 text-primary fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($metrics['total_sales'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 border-start border-warning border-4">
            <span class="text-muted small text-uppercase fw-bold">Pending Outstanding</span>
            <h4 class="mt-2 text-warning fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($metrics['outstanding'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 border-start border-danger border-4">
            <span class="text-muted small text-uppercase fw-bold">Seller Payables Due</span>
            <h4 class="mt-2 text-danger fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($metrics['pending_settlements'], 2); ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-white p-3 border-start border-success border-4">
            <span class="text-muted small text-uppercase fw-bold">Platform Commission</span>
            <h4 class="mt-2 text-success fw-bold"><?php echo cur_symbol(); ?> <?php echo number_format($metrics['total_commission'], 2); ?></h4>
        </div>
    </div>
</div>

<!-- Analytics Charts Section: Row 1 (The 2 Working Charts) -->
<div class="row g-4 mb-4">
    <!-- Invoices & Tax Trend Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; background: #ffffff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Invoicing &amp; GSTR-1 Tax Trend</h5>
                    <small class="text-muted">Last 6 months billed totals and tax collected</small>
                </div>
                <span class="badge" style="background: rgba(255,193,7,0.2); color: #856404;">6-Month View</span>
            </div>
            <div id="chartInvoiceTrend" style="min-height: 310px;"></div>
        </div>
    </div>

    <!-- Cashflow: Pay-In vs Pay-Out Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; background: #ffffff;">
            <div class="mb-3">
                <h5 class="fw-bold mb-0 text-dark">Cashflow Trend</h5>
                <small class="text-muted">Pay-In inflows vs Pay-Out outflows</small>
            </div>
            <div id="chartCashflowTrend" style="min-height: 310px;"></div>
        </div>
    </div>
</div>

<!-- Analytics Charts Section: Row 2 (Consolidated Overview Graph) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; background: #ffffff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Transaction &amp; Settlement Overview</h5>
                    <small class="text-muted">Monthly comparison of total invoices issued, pending dues, pay-ins, pay-outs, and unsettled credit notes</small>
                </div>
                <span class="badge bg-light text-dark border">Consolidated Cashflow</span>
            </div>
            <div id="chartTransactionOverview" style="min-height: 350px;"></div>
        </div>
    </div>
</div>

<!-- ApexCharts Script -->
<script src="<?php echo APP_URL; ?>/assets/libs/apexcharts/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = <?php echo json_encode($charts ?? []); ?>;

    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts library failed to load.');
        return;
    }

    // 1. Invoices & Tax Trend (Bar + Line)
    const elInvoice = document.querySelector("#chartInvoiceTrend");
    if (elInvoice && chartData.labels) {
        new ApexCharts(elInvoice, {
            series: [{
                name: 'Total Invoiced',
                type: 'column',
                data: chartData.invoice_totals || []
            }, {
                name: 'Tax Collected',
                type: 'line',
                data: chartData.tax_collected || []
            }],
            chart: {
                height: 310,
                type: 'line',
                toolbar: { show: false }
            },
            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },
            colors: ['#283237', '#ffc107'],
            labels: chartData.labels,
            yaxis: [{
                title: { text: 'Invoiced Amount (₹)' },
                labels: {
                    formatter: function (val) { return '₹ ' + Number(val).toLocaleString(); }
                }
            }, {
                opposite: true,
                title: { text: 'Tax (₹)' },
                labels: {
                    formatter: function (val) { return '₹ ' + Number(val).toLocaleString(); }
                }
            }],
            tooltip: {
                y: {
                    formatter: function (val) { return '₹ ' + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2}); }
                }
            }
        }).render();
    }

    // 2. Pay-In vs Pay-Out Comparison (Grouped Bar)
    const elCashflow = document.querySelector("#chartCashflowTrend");
    if (elCashflow && chartData.labels) {
        new ApexCharts(elCashflow, {
            series: [{
                name: 'Pay-In (Received)',
                data: chartData.pay_ins || []
            }, {
                name: 'Pay-Out (Paid)',
                data: chartData.pay_outs || []
            }],
            chart: {
                type: 'bar',
                height: 310,
                toolbar: { show: false }
            },
            colors: ['#12805c', '#c0394b'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 4
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: chartData.labels
            },
            yaxis: {
                labels: {
                    formatter: function (val) { return '₹ ' + Number(val).toLocaleString(); }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) { return '₹ ' + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2}); }
                }
            }
        }).render();
    }

    // 3. Consolidated Transaction Overview
    const elOverview = document.querySelector("#chartTransactionOverview");
    if (elOverview && chartData.labels) {
        new ApexCharts(elOverview, {
            series: [
                {
                    name: 'Total Invoices Issued',
                    type: 'column',
                    data: chartData.invoice_totals || []
                },
                {
                    name: 'Pay-In (Received)',
                    type: 'column',
                    data: chartData.pay_ins || []
                },
                {
                    name: 'Pay-Out (Paid)',
                    type: 'column',
                    data: chartData.pay_outs || []
                },
                {
                    name: 'Pending Invoices (Due)',
                    type: 'line',
                    data: chartData.pending_invoices || []
                },
                {
                    name: 'Unsettled Credit Notes',
                    type: 'line',
                    data: chartData.credit_unsettled || []
                }
            ],
            chart: {
                height: 350,
                type: 'line',
                toolbar: { show: false }
            },
            stroke: {
                width: [0, 0, 0, 3, 3],
                curve: 'smooth',
                dashArray: [0, 0, 0, 4, 2]
            },
            plotOptions: {
                bar: {
                    columnWidth: '55%',
                    borderRadius: 3
                }
            },
            colors: [
                '#283237', // Total Invoices Issued (Bar - Dark)
                '#12805c', // Pay-In (Bar - Green)
                '#c0394b', // Pay-Out (Bar - Red)
                '#f59e0b', // Pending Invoices Due (Line - Amber dashed)
                '#8b5cf6'  // Unsettled Credit Notes (Line - Purple dotted)
            ],
            labels: chartData.labels,
            xaxis: {
                categories: chartData.labels
            },
            yaxis: {
                title: { text: 'Amount (₹)' },
                labels: {
                    formatter: function (val) { return '₹ ' + Number(val).toLocaleString(); }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (val) {
                        return '₹ ' + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2});
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        }).render();
    }
});
</script>