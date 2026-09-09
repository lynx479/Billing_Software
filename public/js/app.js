/*
  Global application behaviour: sidebar toggling, active-link resolution,
  collapse memory and global search filtering of visible tables.
  Shared by every page — do not duplicate this logic in views.
*/
(function () {
    'use strict';

    var body = document.body;
    var sidebar = document.getElementById('appSidebar');
    var overlay = document.getElementById('appOverlay');
    var toggle = document.getElementById('sidebarToggle');
    var mini = document.getElementById('sidebarMini');
    var STORAGE_KEY = 'ui.sidebar.mini';

    function setOpen(open) {
        body.classList.toggle('sidebar-open', open);
        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        }
    }

    function setMini(on) {
        body.classList.toggle('sidebar-mini', on);
        try { localStorage.setItem(STORAGE_KEY, on ? '1' : '0'); } catch (e) {}
        if (mini) {
            mini.innerHTML = '<i class="bi bi-chevron-bar-' + (on ? 'right' : 'left') + '"></i>';
        }
    }

    try { if (localStorage.getItem(STORAGE_KEY) === '1') setMini(true); } catch (e) {}

    if (toggle) toggle.addEventListener('click', function () { setOpen(!body.classList.contains('sidebar-open')); });
    if (mini) mini.addEventListener('click', function () { setMini(!body.classList.contains('sidebar-mini')); });
    if (overlay) overlay.addEventListener('click', function () { setOpen(false); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setOpen(false);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) setOpen(false);
    });

    if (sidebar) {
        // Close the drawer after navigating on small screens.
        sidebar.addEventListener('click', function (e) {
            var link = e.target.closest('a.sidebar-link');
            if (link && !link.hasAttribute('data-bs-toggle') && window.innerWidth < 992) setOpen(false);
        });

        // Resolve the deepest matching link as active and open its parents.
        var current = window.location.pathname.replace(/\/$/, '') || '/';
        var best = null;
        var bestLen = -1;
        sidebar.querySelectorAll('a.sidebar-link[href]').forEach(function (link) {
            if (link.hasAttribute('data-bs-toggle')) return;
            var url;
            try { url = new URL(link.href, window.location.origin); } catch (err) { return; }
            var path = url.pathname.replace(/\/$/, '') || '/';
            var match = current === path || (path !== '/' && current.indexOf(path + '/') === 0);
            if (match && path.length > bestLen) { best = link; bestLen = path.length; }
        });

        if (best) {
            sidebar.querySelectorAll('a.sidebar-link.active').forEach(function (el) { el.classList.remove('active'); });
            best.classList.add('active');
            best.setAttribute('aria-current', 'page');
            var group = best.closest('.collapse');
            while (group) {
                group.classList.add('show');
                var trigger = sidebar.querySelector('[href="#' + group.id + '"]');
                if (trigger) trigger.setAttribute('aria-expanded', 'true');
                group = group.parentElement ? group.parentElement.closest('.collapse') : null;
            }
        }
    }

    // Global search: filters rows of every table on the current page.
    var search = document.getElementById('globalSearch');
    if (search) {
        search.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            document.querySelectorAll('#mainContent table tbody').forEach(function (tbody) {
                tbody.querySelectorAll('tr').forEach(function (row) {
                    if (!q) { row.style.removeProperty('display'); return; }
                    row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
                });
            });
        });
    }

    // Auto-theme date inputs with flatpickr where available.
    if (window.flatpickr) {
        document.querySelectorAll('input[data-flatpickr]').forEach(function (el) {
            window.flatpickr(el, { dateFormat: 'Y-m-d', allowInput: true });
        });
    }
})();
