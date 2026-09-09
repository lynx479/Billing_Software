<?php
/**
 * Lightweight in-memory pagination helper.
 * Keeps all existing filters/search (GET params) intact by re-building the query string.
 */

if (!function_exists('lx_paginate')) {
    function lx_paginate($rows, $perPage = 20, $param = 'page') {
        $rows    = is_array($rows) ? $rows : [];
        $perPage = max(1, (int)$perPage);
        $total   = count($rows);
        $pages   = max(1, (int)ceil($total / $perPage));
        $page    = (int)($_GET[$param] ?? 1);
        if ($page < 1) { $page = 1; }
        if ($page > $pages) { $page = $pages; }
        $offset  = ($page - 1) * $perPage;

        $meta = [
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => $perPage,
            'total'   => $total,
            'offset'  => $offset,
            'param'   => $param,
            'from'    => $total ? $offset + 1 : 0,
            'to'      => min($offset + $perPage, $total),
        ];

        return [array_slice($rows, $offset, $perPage), $meta];
    }
}

if (!function_exists('lx_page_url')) {
    function lx_page_url($pageNo, $param = 'page') {
        $query = $_GET;
        unset($query['url']);
        $query[$param] = (int)$pageNo;
        $base = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
        $qs   = http_build_query($query);
        return htmlspecialchars($base . ($qs ? '?' . $qs : ''), ENT_QUOTES);
    }
}

if (!function_exists('lx_pagination_links')) {
    function lx_pagination_links($meta) {
        if (empty($meta) || $meta['total'] <= $meta['perPage']) {
            return;
        }
        $param = $meta['param'];
        $page  = $meta['page'];
        $pages = $meta['pages'];

        $start = max(1, $page - 2);
        $end   = min($pages, $page + 2);

        echo '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-top">';
        echo '<small class="text-muted">Showing ' . $meta['from'] . '&ndash;' . $meta['to'] . ' of ' . $meta['total'] . ' records</small>';
        echo '<nav><ul class="pagination pagination-sm mb-0">';

        echo '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . lx_page_url($page - 1, $param) . '">Previous</a></li>';

        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . lx_page_url(1, $param) . '">1</a></li>';
            if ($start > 2) { echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>'; }
        }

        for ($i = $start; $i <= $end; $i++) {
            echo '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="' . lx_page_url($i, $param) . '">' . $i . '</a></li>';
        }

        if ($end < $pages) {
            if ($end < $pages - 1) { echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>'; }
            echo '<li class="page-item"><a class="page-link" href="' . lx_page_url($pages, $param) . '">' . $pages . '</a></li>';
        }

        echo '<li class="page-item' . ($page >= $pages ? ' disabled' : '') . '"><a class="page-link" href="' . lx_page_url($page + 1, $param) . '">Next</a></li>';
        echo '</ul></nav></div>';
    }
}
