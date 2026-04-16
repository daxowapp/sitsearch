<?php
/**
 * Admin template — Search Queries
 * Shows what people are searching for in the AI search.
 */

use SIT\Search\Services\SearchQueryLogger;

// Ensure table exists
SearchQueryLogger::create_table();

// Get initial stats
$stats = SearchQueryLogger::get_stats();
?>

<div class="wrap">
    <h1>🔍 Search Queries</h1>
    <p class="description">See what people are searching for in the AI search.</p>

    <!-- Stats Cards -->
    <div class="sq-stats-grid" id="sq-stats">
        <div class="sq-stat-card">
            <div class="sq-stat-number" id="stat-total"><?php echo number_format($stats['total']); ?></div>
            <div class="sq-stat-label">Total Searches</div>
        </div>
        <div class="sq-stat-card">
            <div class="sq-stat-number" id="stat-today"><?php echo number_format($stats['today']); ?></div>
            <div class="sq-stat-label">Today</div>
        </div>
        <div class="sq-stat-card">
            <div class="sq-stat-number" id="stat-unique"><?php echo number_format($stats['unique_queries']); ?></div>
            <div class="sq-stat-label">Unique Queries</div>
        </div>
        <div class="sq-stat-card">
            <div class="sq-stat-number" id="stat-week"><?php echo number_format($stats['this_week']); ?></div>
            <div class="sq-stat-label">This Week</div>
        </div>
    </div>

    <!-- Top Queries -->
    <?php if (!empty($stats['top_queries'])): ?>
    <div class="sq-card">
        <h2>🔥 Top Searches</h2>
        <div class="sq-top-queries">
            <?php foreach ($stats['top_queries'] as $tq): ?>
                <span class="sq-tag">
                    <?php echo esc_html($tq->query); ?>
                    <strong>(<?php echo (int) $tq->search_count; ?>)</strong>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="sq-card">
        <h2>Search Log</h2>
        <div class="sq-filters">
            <input type="text" id="sq-search" placeholder="Search queries..." class="regular-text">
            <input type="date" id="sq-date-from" title="From date">
            <input type="date" id="sq-date-to" title="To date">
            <button id="sq-filter-btn" class="button button-primary">Filter</button>
            <button id="sq-reset-btn" class="button">Reset</button>
            <span style="flex:1"></span>
            <button id="sq-export-btn" class="button button-secondary">📥 Export CSV</button>
            <button id="sq-clear-btn" class="button" style="color:#d63638;">🗑 Clear Old (30d+)</button>
        </div>

        <!-- Table -->
        <table class="wp-list-table widefat fixed striped" id="sq-table">
            <thead>
                <tr>
                    <th style="width:160px" class="sq-sortable" data-sort="created_at">Date ↕</th>
                    <th class="sq-sortable" data-sort="query">Query ↕</th>
                    <th>Expanded Terms</th>
                    <th>Filters Detected</th>
                    <th style="width:80px" class="sq-sortable" data-sort="results_count">Results ↕</th>
                    <th style="width:80px">Source</th>
                </tr>
            </thead>
            <tbody id="sq-tbody">
                <tr><td colspan="6" style="text-align:center;padding:30px;">Loading...</td></tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="sq-pagination" id="sq-pagination"></div>
    </div>
</div>

<style>
.sq-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin: 20px 0;
}
.sq-stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.sq-stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #2271b1;
    line-height: 1.2;
}
.sq-stat-label {
    font-size: 13px;
    color: #646970;
    margin-top: 4px;
}
.sq-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.sq-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f1;
}
.sq-top-queries {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
.sq-tag {
    background: #f0f6fc;
    border: 1px solid #c3ddf6;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 13px;
    color: #1d2327;
}
.sq-tag strong {
    color: #2271b1;
    margin-left: 4px;
}
.sq-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 15px;
}
.sq-filters input[type="text"] {
    min-width: 200px;
}
.sq-filters input[type="date"] {
    width: 150px;
}
#sq-table {
    margin-top: 0;
}
#sq-table th.sq-sortable {
    cursor: pointer;
    user-select: none;
}
#sq-table th.sq-sortable:hover {
    background: #f0f6fc;
}
.sq-terms {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.sq-term {
    background: #e7f5e7;
    border-radius: 3px;
    padding: 2px 6px;
    font-size: 12px;
    color: #1e4620;
}
.sq-filter-badge {
    background: #fcf0e3;
    border-radius: 3px;
    padding: 2px 6px;
    font-size: 12px;
    color: #6e4200;
}
.sq-source {
    background: #f0f0f1;
    border-radius: 3px;
    padding: 2px 8px;
    font-size: 11px;
    color: #50575e;
    text-transform: uppercase;
}
.sq-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    gap: 10px;
}
.sq-pagination .sq-page-info {
    color: #646970;
    font-size: 13px;
}
.sq-pagination .sq-page-buttons {
    display: flex;
    gap: 4px;
}
.sq-pagination .sq-page-buttons button {
    min-width: 36px;
}
.sq-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}
.sq-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #c3c4c7;
    margin-bottom: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var nonce   = '<?php echo wp_create_nonce('sit_search_queries_nonce'); ?>';

    var currentPage = 1;
    var currentSort = 'created_at';
    var currentOrder = 'DESC';

    // Load initial data
    loadQueries();

    // Filter button
    $('#sq-filter-btn').on('click', function() {
        currentPage = 1;
        loadQueries();
    });

    // Reset button
    $('#sq-reset-btn').on('click', function() {
        $('#sq-search').val('');
        $('#sq-date-from').val('');
        $('#sq-date-to').val('');
        currentPage = 1;
        currentSort = 'created_at';
        currentOrder = 'DESC';
        loadQueries();
    });

    // Enter key in search
    $('#sq-search').on('keypress', function(e) {
        if (e.which === 13) {
            currentPage = 1;
            loadQueries();
        }
    });

    // Sortable columns
    $(document).on('click', '.sq-sortable', function() {
        var sort = $(this).data('sort');
        if (currentSort === sort) {
            currentOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
        } else {
            currentSort = sort;
            currentOrder = 'DESC';
        }
        loadQueries();
    });

    // Clear old queries
    $('#sq-clear-btn').on('click', function() {
        if (!confirm('Delete all search queries older than 30 days?')) return;
        var btn = $(this);
        btn.prop('disabled', true);
        $.post(ajaxurl, {
            action: 'sit_clear_old_queries',
            nonce: nonce,
            days: 30
        }, function(resp) {
            btn.prop('disabled', false);
            if (resp.success) {
                alert(resp.data.message);
                loadQueries();
                loadStats();
            }
        });
    });

    // Export CSV
    $('#sq-export-btn').on('click', function() {
        var params = new URLSearchParams({
            action: 'sit_export_queries_csv',
            nonce: nonce,
            date_from: $('#sq-date-from').val(),
            date_to: $('#sq-date-to').val()
        });
        window.location.href = ajaxurl + '?' + params.toString();
    });

    function loadQueries() {
        $('#sq-tbody').html('<tr><td colspan="6" style="text-align:center;padding:30px;">Loading...</td></tr>');

        $.post(ajaxurl, {
            action: 'sit_get_search_queries',
            nonce: nonce,
            page: currentPage,
            per_page: 50,
            search: $('#sq-search').val(),
            date_from: $('#sq-date-from').val(),
            date_to: $('#sq-date-to').val(),
            orderby: currentSort,
            order: currentOrder
        }, function(resp) {
            if (!resp.success) {
                $('#sq-tbody').html('<tr><td colspan="6">Error loading data</td></tr>');
                return;
            }

            var data = resp.data;
            renderTable(data.items);
            renderPagination(data.total, data.pages);
        });
    }

    function loadStats() {
        $.post(ajaxurl, {
            action: 'sit_get_search_stats',
            nonce: nonce
        }, function(resp) {
            if (resp.success) {
                var s = resp.data;
                $('#stat-total').text(numberFormat(s.total));
                $('#stat-today').text(numberFormat(s.today));
                $('#stat-unique').text(numberFormat(s.unique_queries));
                $('#stat-week').text(numberFormat(s.this_week));
            }
        });
    }

    function renderTable(items) {
        if (!items || items.length === 0) {
            $('#sq-tbody').html(
                '<tr><td colspan="6">' +
                '<div class="sq-empty-state">' +
                '<span class="dashicons dashicons-search"></span>' +
                '<p>No search queries found.</p>' +
                '<p>Queries will appear here once people start searching.</p>' +
                '</div></td></tr>'
            );
            return;
        }

        var html = '';
        items.forEach(function(item) {
            var terms = '';
            if (item.expanded_terms) {
                try {
                    var t = JSON.parse(item.expanded_terms);
                    if (Array.isArray(t)) {
                        terms = t.map(function(term) {
                            return '<span class="sq-term">' + escapeHtml(term) + '</span>';
                        }).join('');
                    }
                } catch(e) { terms = escapeHtml(item.expanded_terms); }
            }

            var filters = '';
            if (item.filters_extracted) {
                try {
                    var f = JSON.parse(item.filters_extracted);
                    if (typeof f === 'object' && f !== null) {
                        Object.keys(f).forEach(function(key) {
                            var val = Array.isArray(f[key]) ? f[key].join(', ') : f[key];
                            filters += '<span class="sq-filter-badge">' + escapeHtml(key) + ': ' + escapeHtml(val) + '</span> ';
                        });
                    }
                } catch(e) { filters = escapeHtml(item.filters_extracted); }
            }

            html += '<tr>' +
                '<td>' + escapeHtml(item.created_at) + '</td>' +
                '<td><strong>' + escapeHtml(item.query) + '</strong></td>' +
                '<td><div class="sq-terms">' + (terms || '—') + '</div></td>' +
                '<td>' + (filters || '—') + '</td>' +
                '<td>' + (item.results_count || '0') + '</td>' +
                '<td><span class="sq-source">' + escapeHtml(item.source || 'server') + '</span></td>' +
                '</tr>';
        });

        $('#sq-tbody').html(html);
    }

    function renderPagination(total, pages) {
        if (pages <= 1) {
            $('#sq-pagination').html('<span class="sq-page-info">Showing ' + total + ' queries</span>');
            return;
        }

        var info = '<span class="sq-page-info">Page ' + currentPage + ' of ' + pages + ' (' + total + ' total)</span>';
        var buttons = '<div class="sq-page-buttons">';

        if (currentPage > 1) {
            buttons += '<button class="button sq-page-btn" data-page="1">«</button>';
            buttons += '<button class="button sq-page-btn" data-page="' + (currentPage - 1) + '">‹</button>';
        }

        // Show nearby pages
        var start = Math.max(1, currentPage - 2);
        var end = Math.min(pages, currentPage + 2);

        for (var i = start; i <= end; i++) {
            var cls = i === currentPage ? 'button button-primary' : 'button';
            buttons += '<button class="' + cls + ' sq-page-btn" data-page="' + i + '">' + i + '</button>';
        }

        if (currentPage < pages) {
            buttons += '<button class="button sq-page-btn" data-page="' + (currentPage + 1) + '">›</button>';
            buttons += '<button class="button sq-page-btn" data-page="' + pages + '">»</button>';
        }

        buttons += '</div>';
        $('#sq-pagination').html(info + buttons);
    }

    // Pagination click
    $(document).on('click', '.sq-page-btn', function() {
        currentPage = parseInt($(this).data('page'));
        loadQueries();
        // Scroll to table
        $('html, body').animate({ scrollTop: $('#sq-table').offset().top - 50 }, 200);
    });

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function numberFormat(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});
</script>
