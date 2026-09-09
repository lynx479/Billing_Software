<?php
// gstr1.php - Complete fixed version with all issues addressed

// Active tab detection
$activeTab = $_GET['tab'] ?? 'b2b';

// Get date range filters
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-t');

// Helper function for currency symbol
if (!function_exists('cur_symbol')) {
    function cur_symbol() {
        return '₹';
    }
}

// Helper function for pagination
if (!function_exists('lx_paginate')) {
    function lx_paginate($data, $perPage = 10, $pageParam = 'page') {
        $currentPage = isset($_GET[$pageParam]) ? (int)$_GET[$pageParam] : 1;
        if ($currentPage < 1) $currentPage = 1;
        
        $total = count($data);
        $totalPages = ceil($total / $perPage);
        if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;
        
        $offset = ($currentPage - 1) * $perPage;
        $pagedData = array_slice($data, $offset, $perPage);
        
        $meta = [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total' => $total,
            'offset' => $offset,
            'per_page' => $perPage
        ];
        
        return [$pagedData, $meta];
    }
}

// Helper function for pagination links
if (!function_exists('lx_pagination_links')) {
    function lx_pagination_links($meta, $tabName = '') {
        if ($meta['total_pages'] <= 1) return;
        
        // Build URL with current parameters
        $params = $_GET;
        $pageParams = ['page_b2b', 'page_b2cl', 'page_b2cs', 'page_cdnb2b', 'page_cdnb2c', 'page_hsnb2c', 'page_hsnb2b', 'page_item', 'page_doc', 'page_summary'];
        foreach ($pageParams as $p) {
            unset($params[$p]);
        }
        $queryString = http_build_query($params);
        
        echo '<nav aria-label="Page navigation" class="mt-2">';
        echo '<ul class="pagination pagination-sm justify-content-center mb-0">';
        
        // Previous button
        $prevPage = $meta['current_page'] - 1;
        if ($prevPage < 1) $prevPage = 1;
        echo '<li class="page-item ' . ($meta['current_page'] == 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?' . $queryString . '&' . $meta['page_param'] . '=' . $prevPage . '">&laquo;</a>';
        echo '</li>';
        
        for ($i = 1; $i <= $meta['total_pages']; $i++) {
            $active = $i == $meta['current_page'] ? 'active' : '';
            echo '<li class="page-item ' . $active . '">';
            echo '<a class="page-link" href="?' . $queryString . '&' . $meta['page_param'] . '=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        
        // Next button
        $nextPage = $meta['current_page'] + 1;
        if ($nextPage > $meta['total_pages']) $nextPage = $meta['total_pages'];
        echo '<li class="page-item ' . ($meta['current_page'] == $meta['total_pages'] ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?' . $queryString . '&' . $meta['page_param'] . '=' . $nextPage . '">&raquo;</a>';
        echo '</li>';
        
        echo '</ul>';
        echo '</nav>';
    }
}

// Summary table rows
$summaryRows = [
    ['sno' => 1, 'category' => '4A/4B/4C - B2B Invoices (Registered)', 'count' => $gstr1Summary['b2b']['count'] ?? 0, 'taxable' => $gstr1Summary['b2b']['taxable'] ?? 0, 'tax' => $gstr1Summary['b2b']['tax'] ?? 0, 'total' => $gstr1Summary['b2b']['total'] ?? 0, 'is_negative' => false],
    ['sno' => 2, 'category' => '5 - B2C (Large) Invoices', 'count' => $gstr1Summary['b2c_l']['count'] ?? 0, 'taxable' => $gstr1Summary['b2c_l']['taxable'] ?? 0, 'tax' => $gstr1Summary['b2c_l']['tax'] ?? 0, 'total' => $gstr1Summary['b2c_l']['total'] ?? 0, 'is_negative' => false],
    ['sno' => 3, 'category' => '7 - B2C (Small) Invoices', 'count' => $gstr1Summary['b2c_s']['count'] ?? 0, 'taxable' => $gstr1Summary['b2c_s']['taxable'] ?? 0, 'tax' => $gstr1Summary['b2c_s']['tax'] ?? 0, 'total' => $gstr1Summary['b2c_s']['total'] ?? 0, 'is_negative' => false],
    ['sno' => 4, 'category' => '9B - Credit Notes (B2B Registered)', 'count' => $gstr1Summary['cdn_b2b']['count'] ?? 0, 'taxable' => $gstr1Summary['cdn_b2b']['taxable'] ?? 0, 'tax' => $gstr1Summary['cdn_b2b']['tax'] ?? 0, 'total' => $gstr1Summary['cdn_b2b']['total'] ?? 0, 'is_negative' => true],
    ['sno' => 5, 'category' => '9B - Credit Notes (B2C Unregistered)', 'count' => $gstr1Summary['cdn_b2c']['count'] ?? 0, 'taxable' => $gstr1Summary['cdn_b2c']['taxable'] ?? 0, 'tax' => $gstr1Summary['cdn_b2c']['tax'] ?? 0, 'total' => $gstr1Summary['cdn_b2c']['total'] ?? 0, 'is_negative' => true],
];

// Define table data mapping for dynamic exports and search
$tableDataMap = [
    'b2b' => ['data' => $b2b ?? [], 'headers' => ['S.No.', 'GSTIN', 'Party Name', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'GST Rate', 'Tax Value', 'IGST', 'CGST', 'SGST']],
    'b2cl' => ['data' => $b2cLarge ?? [], 'headers' => ['S.No.', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Tax Rate', 'Taxable Value', 'IGST', 'CGST', 'SGST']],
    'b2cs' => ['data' => $b2cSmall ?? [], 'headers' => ['S.No.', 'Place of Supply', 'Tax Rate', 'Taxable Value', 'IGST', 'CGST', 'SGST']],
    'cdnb2b' => ['data' => $cnB2b ?? [], 'headers' => ['S.No.', 'GSTIN', 'Party Name', 'Original Invoice Number', 'Credit Note Number', 'Credit Note Date', 'Credit Note Value', 'Place of Supply', 'GST Rate', 'Tax Value', 'IGST', 'CGST', 'SGST']],
    'cdnb2c' => ['data' => $cnB2c ?? [], 'headers' => ['S.No.', 'Credit Note Number', 'Date', 'Value', 'Place of Supply', 'GST Rate', 'Taxable Value', 'IGST', 'CGST', 'SGST']],
    'hsnb2c' => ['data' => $hsnB2c ?? [], 'headers' => ['S.No.', 'HSN/SAC', 'Unit', 'Tax Rate', 'Total Qty', 'Taxable Value', 'Tax Amount', 'IGST', 'CGST', 'SGST', 'Total']],
    'hsnb2b' => ['data' => $hsnB2b ?? [], 'headers' => ['S.No.', 'HSN/SAC', 'Unit', 'Tax Rate', 'Total Qty', 'Taxable Value', 'Tax Amount', 'IGST', 'CGST', 'SGST', 'Total']],
    'item' => ['data' => $itemSummary ?? [], 'headers' => ['S.No.', 'Item Name', 'Description', 'Unit', 'Tax Rate', 'Total Qty', 'Taxable Value', 'Tax Amount', 'Total Amount']],
    'doc' => ['data' => $documents ?? [], 'headers' => ['S.No.', 'Nature of Document', 'Total Issued', 'Cancelled', 'From No', 'To No']],
    'summary' => ['data' => $summaryRows, 'headers' => ['S.No.', 'GSTR-1 Category / Table', 'Records Count', 'Taxable Value', 'Tax Amount', 'Total Value']],
];

// Apply pagination with page parameters
list($pB2b, $metaB2b) = lx_paginate($b2b ?? [], 10, 'page_b2b');
$metaB2b['page_param'] = 'page_b2b';

list($pB2cLarge, $metaB2cl) = lx_paginate($b2cLarge ?? [], 10, 'page_b2cl');
$metaB2cl['page_param'] = 'page_b2cl';

list($pB2cSmall, $metaB2cs) = lx_paginate($b2cSmall ?? [], 10, 'page_b2cs');
$metaB2cs['page_param'] = 'page_b2cs';

list($pCnB2b, $metaCnB2b) = lx_paginate($cnB2b ?? [], 10, 'page_cdnb2b');
$metaCnB2b['page_param'] = 'page_cdnb2b';

list($pCnB2c, $metaCnB2c) = lx_paginate($cnB2c ?? [], 10, 'page_cdnb2c');
$metaCnB2c['page_param'] = 'page_cdnb2c';

list($pHsnB2c, $metaHsnB2c) = lx_paginate($hsnB2c ?? [], 10, 'page_hsnb2c');
$metaHsnB2c['page_param'] = 'page_hsnb2c';

list($pHsnB2b, $metaHsnB2b) = lx_paginate($hsnB2b ?? [], 10, 'page_hsnb2b');
$metaHsnB2b['page_param'] = 'page_hsnb2b';

list($pItem, $metaItem) = lx_paginate($itemSummary ?? [], 10, 'page_item');
$metaItem['page_param'] = 'page_item';

list($pDocs, $metaDocs) = lx_paginate($documents ?? [], 10, 'page_doc');
$metaDocs['page_param'] = 'page_doc';

list($pSummary, $metaSummary) = lx_paginate($summaryRows, 10, 'page_summary');
$metaSummary['page_param'] = 'page_summary';

// Search functionality - Fixed to display results in table format
$searchTable = $_GET['search_table'] ?? '';
$searchQuery = $_GET['search_query'] ?? '';
$searchResults = [];
$searchMeta = null;

if ($searchTable && $searchQuery && isset($$searchTable)) {
    $dataToSearch = $$searchTable;
    $searchQuery = strtolower(trim($searchQuery));
    $filteredResults = array_filter($dataToSearch, function($row) use ($searchQuery) {
        foreach ($row as $value) {
            if ($value !== null && $value !== '' && stripos((string)$value, $searchQuery) !== false) {
                return true;
            }
        }
        return false;
    });
    $searchResults = array_values($filteredResults);
    // Paginate search results
    list($searchResults, $searchMeta) = lx_paginate($searchResults, 10, 'page_search');
    $searchMeta['page_param'] = 'page_search';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold text-uppercase mb-0">GSTR-1 Report</h3>
        <p class="text-muted small mb-0">Outward supplies filing data for <strong><?php echo htmlspecialchars($monthLabel ?? ''); ?></strong></p>
    </div>
    
    <!-- Date Range Filters -->
    <form method="GET" action="<?php echo APP_URL ?? ''; ?>/reports/gstr1" class="d-flex gap-2 align-items-end flex-wrap">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab); ?>">
        <div>
            <label class="form-label small fw-bold text-muted mb-1">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($fromDate); ?>" onchange="this.form.submit()">
        </div>
        <div>
            <label class="form-label small fw-bold text-muted mb-1">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($toDate); ?>" onchange="this.form.submit()">
        </div>
        <div>
            <label class="form-label small fw-bold text-muted mb-1">Filing Period</label>
            <input type="month" name="month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($month ?? ''); ?>" onchange="this.form.submit()">
        </div>
    </form>
</div>

<!-- GSTR-1 Summary Tiles -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">B2B Taxable Value</span>
            <h5 class="fw-bold text-primary mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['b2b']['taxable'] ?? 0, 2); ?></h5>
            <small class="text-muted"><?php echo $gstr1Summary['b2b']['count'] ?? 0; ?> rate lines</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-info border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">B2C Total Taxable</span>
            <h5 class="fw-bold text-info mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format(($gstr1Summary['b2c_l']['taxable'] ?? 0) + ($gstr1Summary['b2c_s']['taxable'] ?? 0), 2); ?></h5>
            <small class="text-muted"><?php echo ($gstr1Summary['b2c_l']['count'] ?? 0) + ($gstr1Summary['b2c_s']['count'] ?? 0); ?> rate lines</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-danger border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Credit Notes Taxable</span>
            <h5 class="fw-bold text-danger mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format(($gstr1Summary['cdn_b2b']['taxable'] ?? 0) + ($gstr1Summary['cdn_b2c']['taxable'] ?? 0), 2); ?></h5>
            <small class="text-muted"><?php echo ($gstr1Summary['cdn_b2b']['count'] ?? 0) + ($gstr1Summary['cdn_b2c']['count'] ?? 0); ?> rate lines</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
            <span class="text-muted small fw-bold text-uppercase">Net Tax Liability</span>
            <h5 class="fw-bold text-success mb-0 mt-1"><?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['net_tax'] ?? 0, 2); ?></h5>
            <small class="text-muted">Net Taxable <?php echo cur_symbol(); ?> <?php echo number_format($gstr1Summary['net_taxable'] ?? 0, 2); ?></small>
        </div>
    </div>
</div>

<!-- GST Portal Links & Export Options - Dynamic based on active tab -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2">
        <a href="https://www.gst.gov.in/" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i> GST Portal
        </a>
        <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success" id="csvExportBtn">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <a href="javascript:void(0)" onclick="exportJSON()" class="btn btn-sm btn-outline-info" id="jsonExportBtn">
            <i class="bi bi-file-earmark-code me-1"></i> Export JSON
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?php echo APP_URL ?? ''; ?>/reports/gstr1" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab); ?>">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($month ?? ''); ?>">
            <input type="hidden" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>">
            <input type="hidden" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>">
            
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-0">Search Table</label>
                <select name="search_table" class="form-select form-select-sm">
                    <option value="">Select Table</option>
                    <option value="pB2b" <?php echo $searchTable == 'pB2b' ? 'selected' : ''; ?>>B2B Summary</option>
                    <option value="pB2cLarge" <?php echo $searchTable == 'pB2cLarge' ? 'selected' : ''; ?>>B2C Large</option>
                    <option value="pB2cSmall" <?php echo $searchTable == 'pB2cSmall' ? 'selected' : ''; ?>>B2C Small</option>
                    <option value="pCnB2b" <?php echo $searchTable == 'pCnB2b' ? 'selected' : ''; ?>>Credit Note B2B</option>
                    <option value="pCnB2c" <?php echo $searchTable == 'pCnB2c' ? 'selected' : ''; ?>>Credit Note B2C</option>
                    <option value="pHsnB2c" <?php echo $searchTable == 'pHsnB2c' ? 'selected' : ''; ?>>HSN B2C</option>
                    <option value="pHsnB2b" <?php echo $searchTable == 'pHsnB2b' ? 'selected' : ''; ?>>HSN B2B</option>
                    <option value="pItem" <?php echo $searchTable == 'pItem' ? 'selected' : ''; ?>>Item Summary</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-0">Search Query</label>
                <input type="text" name="search_query" class="form-control form-control-sm" placeholder="Enter search term..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?php echo APP_URL ?? ''; ?>/reports/gstr1?tab=<?php echo $activeTab; ?>&month=<?php echo htmlspecialchars($month ?? ''); ?>&from_date=<?php echo htmlspecialchars($fromDate); ?>&to_date=<?php echo htmlspecialchars($toDate); ?>" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i> Clear
                </a>
            </div>
        </form>
        
        <!-- Search Results Display - Fixed to show table format -->
        <?php if (!empty($searchResults) && $searchTable): ?>
            <div class="mt-3">
                <div class="alert alert-info mb-2">
                    <i class="bi bi-search me-1"></i> Found <strong><?php echo count($searchResults); ?></strong> result(s) in <strong><?php echo str_replace('p', '', $searchTable); ?></strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <?php
                                // Get headers based on the searched table
                                $headerMap = [
                                    'pB2b' => ['S.No.', 'GSTIN', 'Party Name', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'GST Rate', 'Tax Value', 'IGST', 'CGST', 'SGST'],
                                    'pB2cLarge' => ['S.No.', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Tax Rate', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
                                    'pB2cSmall' => ['S.No.', 'Place of Supply', 'Tax Rate', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
                                    'pCnB2b' => ['S.No.', 'GSTIN', 'Party Name', 'Original Invoice Number', 'Credit Note Number', 'Credit Note Date', 'Credit Note Value', 'Place of Supply', 'GST Rate', 'Tax Value', 'IGST', 'CGST', 'SGST'],
                                    'pCnB2c' => ['S.No.', 'Credit Note Number', 'Date', 'Value', 'Place of Supply', 'GST Rate', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
                                    'pHsnB2c' => ['S.No.', 'HSN/SAC', 'Unit', 'Tax Rate', 'Total Qty', 'Taxable Value', 'Tax Amount', 'IGST', 'CGST', 'SGST', 'Total'],
                                    'pHsnB2b' => ['S.No.', 'HSN/SAC', 'Unit', 'Tax Rate', 'Total Qty', 'Taxable Value', 'Tax Amount', 'IGST', 'CGST', 'SGST', 'Total'],
                                    'pItem' => ['S.No.', 'Item Name', 'Description', 'Unit', 'Tax Rate', 'Total Qty', 'Taxable Value', 'Tax Amount', 'Total Amount'],
                                ];
                                $headers = $headerMap[$searchTable] ?? ['S.No.', 'Data'];
                                foreach ($headers as $header): ?>
                                    <th><?php echo $header; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sno = ($searchMeta['offset'] ?? 0) + 1;
                            foreach ($searchResults as $row): 
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <?php 
                                    // Render based on table type
                                    if ($searchTable === 'pB2b'): ?>
                                        <td><code><?php echo htmlspecialchars($row['gstin'] ?? ''); ?></code></td>
                                        <td><?php echo htmlspecialchars($row['party_name'] ?? ''); ?></td>
                                        <td><strong class="text-primary"><?php echo htmlspecialchars($row['invoice_number'] ?? ''); ?></strong></td>
                                        <td class="text-center"><?php echo date('d/m/Y', strtotime($row['invoice_date'] ?? '')); ?></td>
                                        <td class="text-end"><?php echo number_format($row['invoice_value'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['place_of_supply'] ?? ''); ?></td>
                                        <td class="text-center fw-bold"><span class="badge bg-secondary"><?php echo (float)($row['gst_rate'] ?? 0); ?>%</span></td>
                                        <td class="text-end fw-bold"><?php echo number_format($row['tax_value'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['igst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['cgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['sgst_amount'] ?? 0, 2); ?></td>
                                    <?php elseif ($searchTable === 'pB2cLarge'): ?>
                                        <td><strong class="text-info"><?php echo htmlspecialchars($row['invoice_number'] ?? ''); ?></strong></td>
                                        <td class="text-center"><?php echo date('d/m/Y', strtotime($row['invoice_date'] ?? '')); ?></td>
                                        <td class="text-end"><?php echo number_format($row['invoice_value'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['place_of_supply'] ?? ''); ?></td>
                                        <td class="text-center fw-bold"><?php echo (float)($row['tax_rate'] ?? 0); ?>%</td>
                                        <td class="text-end"><?php echo number_format($row['taxable_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['igst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['cgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['sgst_amount'] ?? 0, 2); ?></td>
                                    <?php elseif ($searchTable === 'pB2cSmall'): ?>
                                        <td><?php echo htmlspecialchars($row['place_of_supply'] ?? ''); ?></td>
                                        <td class="text-center fw-bold"><?php echo (float)($row['tax_rate'] ?? 0); ?>%</td>
                                        <td class="text-end"><?php echo number_format($row['taxable_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['igst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['cgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['sgst_amount'] ?? 0, 2); ?></td>
                                    <?php elseif ($searchTable === 'pCnB2b'): ?>
                                        <td><code><?php echo htmlspecialchars($row['gstin'] ?? ''); ?></code></td>
                                        <td><?php echo htmlspecialchars($row['party_name'] ?? ''); ?></td>
                                        <td><span class="text-primary"><?php echo htmlspecialchars($row['original_invoice_number'] ?? '-'); ?></span></td>
                                        <td><strong class="text-danger"><?php echo htmlspecialchars($row['credit_note_number'] ?? ''); ?></strong></td>
                                        <td class="text-center"><?php echo date('d/m/Y', strtotime($row['credit_note_date'] ?? '')); ?></td>
                                        <td class="text-end fw-bold text-danger"><?php echo number_format($row['credit_note_value'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['place_of_supply'] ?? ''); ?></td>
                                        <td class="text-center fw-bold"><?php echo (float)($row['gst_rate'] ?? 0); ?>%</td>
                                        <td class="text-end"><?php echo number_format($row['tax_value'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['igst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['cgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['sgst_amount'] ?? 0, 2); ?></td>
                                    <?php elseif ($searchTable === 'pCnB2c'): ?>
                                        <td><strong class="text-danger"><?php echo htmlspecialchars($row['credit_note_number'] ?? ''); ?></strong></td>
                                        <td class="text-center"><?php echo date('d/m/Y', strtotime($row['credit_note_date'] ?? '')); ?></td>
                                        <td class="text-end fw-bold text-danger"><?php echo number_format($row['credit_note_value'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['place_of_supply'] ?? ''); ?></td>
                                        <td class="text-center fw-bold"><?php echo (float)($row['gst_rate'] ?? 0); ?>%</td>
                                        <td class="text-end"><?php echo number_format($row['taxable_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['igst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['cgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['sgst_amount'] ?? 0, 2); ?></td>
                                    <?php elseif ($searchTable === 'pHsnB2c' || $searchTable === 'pHsnB2b'): ?>
                                        <td><code><?php echo htmlspecialchars($row['hsn_sac'] ?? ''); ?></code></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['unit'] ?? 'PCS'); ?></td>
                                        <td class="text-center fw-bold"><?php echo (float)($row['tax_rate'] ?? 0); ?>%</td>
                                        <td class="text-end"><?php echo number_format($row['total_quantity'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['taxable_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['tax_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['igst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['cgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['sgst_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end fw-bold"><?php echo number_format($row['total_amount'] ?? 0, 2); ?></td>
                                    <?php elseif ($searchTable === 'pItem'): ?>
                                        <td><strong><?php echo htmlspecialchars($row['item_name'] ?? ''); ?></strong></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($row['description'] ?? ''); ?></small></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['unit'] ?? 'PCS'); ?></td>
                                        <td class="text-center fw-bold"><?php echo (float)($row['tax_rate'] ?? 0); ?>%</td>
                                        <td class="text-end"><?php echo number_format($row['total_quantity'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['taxable_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end"><?php echo number_format($row['tax_amount'] ?? 0, 2); ?></td>
                                        <td class="text-end fw-bold"><?php echo number_format($row['total_amount'] ?? 0, 2); ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($searchMeta && $searchMeta['total_pages'] > 1): ?>
                    <?php lx_pagination_links($searchMeta, 'search'); ?>
                <?php endif; ?>
            </div>
        <?php elseif ($searchTable && $searchQuery && empty($searchResults)): ?>
            <div class="mt-3 alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i> No results found for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>" in <strong><?php echo str_replace('p', '', $searchTable); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-3" id="gstr1Tabs" role="tablist">
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'b2b' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-b2b" type="button" onclick="setTab('b2b')">1. B2B Summary</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'b2cl' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-b2cl" type="button" onclick="setTab('b2cl')">2. B2C (Large)</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'b2cs' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-b2cs" type="button" onclick="setTab('b2cs')">3. B2C (Small)</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'cdnb2b' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-cdnb2b" type="button" onclick="setTab('cdnb2b')">4. Credit Note – B2B</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'cdnb2c' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-cdnb2c" type="button" onclick="setTab('cdnb2c')">5. Credit Note – B2C</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'hsnb2c' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-hsn-b2c" type="button" onclick="setTab('hsnb2c')">6. HSN B2C</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'hsnb2b' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-hsn-b2b" type="button" onclick="setTab('hsnb2b')">7. HSN B2B</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'item' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-item" type="button" onclick="setTab('item')">8. Item Summary</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'doc' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-doc" type="button" onclick="setTab('doc')">9. Documents</button></li>
    <li class="nav-item"><button class="nav-link <?php echo $activeTab === 'summary' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-summary" type="button" onclick="setTab('summary')">10. GSTR-1 Summary</button></li>
</ul>

<div class="tab-content">

    <!-- 1. B2B SUMMARY TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'b2b' ? 'show active' : ''; ?>" id="tab-b2b">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">1. B2B Summary (Split per GST Tax Rate Category)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
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
                            <?php if (empty($pB2b)): ?>
                                <tr><td colspan="12" class="text-center py-4 text-muted"><i class="bi bi-inbox me-1"></i>No B2B invoices found for this period.</td></tr>
                            <?php else: $sno = $metaB2b['total'] - $metaB2b['offset']; foreach ($pB2b as $r): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td><code><?php echo htmlspecialchars($r['gstin'] ?? ''); ?></code></td>
                                    <td><?php echo htmlspecialchars($r['party_name'] ?? ''); ?></td>
                                    <td><strong class="text-primary"><?php echo htmlspecialchars($r['invoice_number'] ?? ''); ?></strong></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['invoice_date'] ?? '')); ?></td>
                                    <td class="text-end"><?php echo number_format($r['invoice_value'] ?? 0, 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply'] ?? ''); ?></td>
                                    <td class="text-center fw-bold"><span class="badge bg-secondary"><?php echo (float)($r['gst_rate'] ?? 0); ?>%</span></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['tax_value'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaB2b); ?>
            </div>
        </div>
    </div>

    <!-- 2. B2C (LARGE) TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'b2cl' ? 'show active' : ''; ?>" id="tab-b2cl">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">2. B2C (Large) Invoices (Interstate &gt; ₹2,50,000)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
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
                            <?php if (empty($pB2cLarge)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted">No B2C Large invoices in this period.</td></tr>
                            <?php else: $sno = $metaB2cl['total'] - $metaB2cl['offset']; foreach ($pB2cLarge as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td>
                                        <strong class="text-info"><?php echo htmlspecialchars($r['invoice_number'] ?? ''); ?></strong>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['invoice_date'] ?? '')); ?></td>
                                    <td class="text-end"><?php echo number_format($r['invoice_value'] ?? 0, 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply'] ?? ''); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['tax_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaB2cl); ?>
            </div>
        </div>
    </div>

    <!-- 3. B2C (SMALL) TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'b2cs' ? 'show active' : ''; ?>" id="tab-b2cs">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">3. B2C (Small) Supplies (Grouped by POS &amp; Tax Rate)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
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
                            <?php if (empty($pB2cSmall)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No B2C Small supplies in this period.</td></tr>
                            <?php else: $sno = $metaB2cs['total'] - $metaB2cs['offset']; foreach ($pB2cSmall as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($r['place_of_supply'] ?? ''); ?>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['tax_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaB2cs); ?>
            </div>
        </div>
    </div>

    <!-- 4. CREDIT NOTE – B2B TAB - Added Original Invoice Number column -->
    <div class="tab-pane fade <?php echo $activeTab === 'cdnb2b' ? 'show active' : ''; ?>" id="tab-cdnb2b">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">4. Credit Notes – B2B (Registered Parties)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
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
                                <th>Original Invoice Number</th>
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
                            <?php if (empty($pCnB2b)): ?>
                                <tr><td colspan="13" class="text-center py-4 text-muted">No B2B credit notes in this period.</td></tr>
                            <?php else: $sno = $metaCnB2b['total'] - $metaCnB2b['offset']; foreach ($pCnB2b as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td><code><?php echo htmlspecialchars($r['gstin'] ?? ''); ?></code></td>
                                    <td>
                                        <?php echo htmlspecialchars($r['party_name'] ?? ''); ?>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td><span class="text-primary"><?php echo htmlspecialchars($r['original_invoice_number'] ?? '-'); ?></span></td>
                                    <td><strong class="text-danger"><?php echo htmlspecialchars($r['credit_note_number'] ?? ''); ?></strong></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['credit_note_date'] ?? '')); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($r['credit_note_value'] ?? 0, 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply'] ?? ''); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['gst_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['tax_value'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaCnB2b); ?>
            </div>
        </div>
    </div>

    <!-- 5. CREDIT NOTE – B2C TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'cdnb2c' ? 'show active' : ''; ?>" id="tab-cdnb2c">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">5. Credit Notes – B2C (Unregistered Parties)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
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
                            <?php if (empty($pCnB2c)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted">No B2C credit notes in this period.</td></tr>
                            <?php else: $sno = $metaCnB2c['total'] - $metaCnB2c['offset']; foreach ($pCnB2c as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td>
                                        <strong class="text-danger"><?php echo htmlspecialchars($r['credit_note_number'] ?? ''); ?></strong>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($r['credit_note_date'] ?? '')); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($r['credit_note_value'] ?? 0, 2); ?></td>
                                    <td><?php echo htmlspecialchars($r['place_of_supply'] ?? ''); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['gst_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaCnB2c); ?>
            </div>
        </div>
    </div>

    <!-- 6. HSN B2C TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'hsnb2c' ? 'show active' : ''; ?>" id="tab-hsn-b2c">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">6. HSN B2C Summary (Unregistered Consumers)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
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
                            <?php if (empty($pHsnB2c)): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No HSN data for B2C supplies.</td></tr>
                            <?php else: $sno = $metaHsnB2c['total'] - $metaHsnB2c['offset']; foreach ($pHsnB2c as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td>
                                        <code><?php echo htmlspecialchars($r['hsn_sac'] ?? ''); ?></code>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($r['unit'] ?? 'PCS'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['tax_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['total_quantity'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['tax_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['total_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaHsnB2c); ?>
            </div>
        </div>
    </div>

    <!-- 7. HSN B2B TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'hsnb2b' ? 'show active' : ''; ?>" id="tab-hsn-b2b">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">7. HSN B2B Summary (Registered Businesses)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
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
                            <?php if (empty($pHsnB2b)): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No HSN data for B2B supplies.</td></tr>
                            <?php else: $sno = $metaHsnB2b['total'] - $metaHsnB2b['offset']; foreach ($pHsnB2b as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td>
                                        <code><?php echo htmlspecialchars($r['hsn_sac'] ?? ''); ?></code>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($r['unit'] ?? 'PCS'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['tax_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['total_quantity'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['tax_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['igst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['cgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['sgst_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['total_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaHsnB2b); ?>
            </div>
        </div>
    </div>

    <!-- 8. ITEM SUMMARY TAB -->
    <div class="tab-pane fade <?php echo $activeTab === 'item' ? 'show active' : ''; ?>" id="tab-item">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">8. Item Summary</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
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
                            <?php if (empty($pItem)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No items sold in this period.</td></tr>
                            <?php else: $sno = $metaItem['total'] - $metaItem['offset']; foreach ($pItem as $r): ?>
                                <tr <?php echo !empty($r['is_dummy']) ? 'class="table-warning"' : ''; ?>>
                                    <td class="text-center"><?php echo $sno--; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($r['item_name'] ?? ''); ?></strong>
                                        <?php if (!empty($r['is_dummy'])): ?><span class="badge bg-warning text-dark ms-1">SAMPLE</span><?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($r['description'] ?? ''); ?></small></td>
                                    <td class="text-center"><?php echo htmlspecialchars($r['unit'] ?? 'PCS'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (float)($r['tax_rate'] ?? 0); ?>%</td>
                                    <td class="text-end"><?php echo number_format($r['total_quantity'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['taxable_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($r['tax_amount'] ?? 0, 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($r['total_amount'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaItem); ?>
            </div>
        </div>
    </div>

    <!-- 9. DOCUMENTS TAB - Keep serial numbering as is (ascending) -->
    <div class="tab-pane fade <?php echo $activeTab === 'doc' ? 'show active' : ''; ?>" id="tab-doc">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">9. Document Summary (Nature of Document)</span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
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
                            <?php $sno = 1; foreach ($pDocs as $d): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($d['nature_of_doc'] ?? ''); ?></strong></td>
                                    <td class="text-end fw-bold"><?php echo (int)($d['total_issued'] ?? 0); ?></td>
                                    <td class="text-end text-danger"><?php echo (int)($d['cancelled_count'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($d['from_no'] ?? '-'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($d['to_no'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaDocs); ?>
            </div>
        </div>
    </div>

    <!-- 10. GSTR-1 SUMMARY TAB - Keep serial numbering as is (ascending) -->
    <div class="tab-pane fade <?php echo $activeTab === 'summary' ? 'show active' : ''; ?>" id="tab-summary">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">10. GSTR-1 Filing Summary - <?php echo htmlspecialchars($monthLabel ?? ''); ?></span>
                <a href="javascript:void(0)" onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
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
                            <?php $sno = 1; foreach ($pSummary as $row): 
                                $prefix = $row['is_negative'] ? '- ' : '';
                                $class  = $row['is_negative'] ? 'text-danger' : '';
                            ?>
                                <tr class="<?php echo $class; ?>">
                                    <td class="text-center"><?php echo $sno++; ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td class="text-end"><?php echo $row['count']; ?></td>
                                    <td class="text-end"><?php echo $prefix . cur_symbol() . ' ' . number_format($row['taxable'], 2); ?></td>
                                    <td class="text-end"><?php echo $prefix . cur_symbol() . ' ' . number_format($row['tax'], 2); ?></td>
                                    <td class="text-end fw-bold"><?php echo $prefix . cur_symbol() . ' ' . number_format($row['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <tr class="border-top fs-6 fw-bold table-light">
                                <td class="text-center">-</td>
                                <td>Net Outward Tax Liability</td>
                                <td class="text-end">-</td>
                                <td class="text-end"><?php echo (($gstr1Summary['net_taxable'] ?? 0) < 0 ? '- ' : '') . cur_symbol() . ' ' . number_format(abs($gstr1Summary['net_taxable'] ?? 0), 2); ?></td>
                                <td class="text-end text-success"><?php echo (($gstr1Summary['net_tax'] ?? 0) < 0 ? '- ' : '') . cur_symbol() . ' ' . number_format(abs($gstr1Summary['net_tax'] ?? 0), 2); ?></td>
                                <td class="text-end"><?php echo (($gstr1Summary['net_total'] ?? 0) < 0 ? '- ' : '') . cur_symbol() . ' ' . number_format(abs($gstr1Summary['net_total'] ?? 0), 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php lx_pagination_links($metaSummary); ?>
            </div>
        </div>
    </div>
</div>

<script>
// Keeps active tab parameter persistent in URL across page reloads
function setTab(tabName) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.replaceState(null, '', url);
}

// Dynamic JSON Export - exports data from currently active tab only
function exportJSON() {
    // Get the current active tab from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'b2b';
    
    let data = [];
    let headers = [];
    
    // Get the active tab content
    const tabContent = document.getElementById('tab-' + activeTab);
    if (tabContent) {
        const table = tabContent.querySelector('table');
        if (table) {
            // Get headers
            const headerCells = table.querySelectorAll('thead th');
            headers = Array.from(headerCells).map(th => th.textContent.trim());
            
            // Get data rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                // Skip "No data" rows
                if (row.querySelector('td[colspan]')) return;
                
                const rowData = {};
                const cells = row.querySelectorAll('td');
                cells.forEach((td, index) => {
                    if (index < headers.length) {
                        rowData[headers[index]] = td.textContent.trim();
                    }
                });
                if (Object.keys(rowData).length > 0) {
                    data.push(rowData);
                }
            });
        }
    }
    
    const month = '<?php echo htmlspecialchars($month ?? ''); ?>';
    const fromDate = '<?php echo htmlspecialchars($fromDate); ?>';
    const toDate = '<?php echo htmlspecialchars($toDate); ?>';
    
    const jsonData = {
        report: 'GSTR-1',
        tab: activeTab,
        period: month,
        from_date: fromDate,
        to_date: toDate,
        exported_at: new Date().toISOString(),
        data: data
    };
    
    const blob = new Blob([JSON.stringify(jsonData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'gstr1_' + activeTab + '_' + month + '.json';
    a.click();
    URL.revokeObjectURL(url);
}

// Dynamic CSV Export - uses current active tab
function exportCSV() {
    // Get the current active tab from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'b2b';
    const month = '<?php echo htmlspecialchars($month ?? ''); ?>';
    const fromDate = '<?php echo htmlspecialchars($fromDate); ?>';
    const toDate = '<?php echo htmlspecialchars($toDate); ?>';
    
    window.location.href = '<?php echo APP_URL ?? ''; ?>/reports/gstr1Export?tab=' + activeTab + '&month=' + month + '&from_date=' + fromDate + '&to_date=' + toDate;
}

// Update setTab function to also handle export
function setTab(tabName) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.replaceState(null, '', url);
}
</script>