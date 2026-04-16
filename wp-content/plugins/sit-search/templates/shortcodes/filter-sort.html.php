<?php
// Security Check
if (!defined('ABSPATH')) {
    exit;
}
// Session should be started in App.php init hook, not here

$degree_term = get_term($degreeid, 'sit-degree');
$country_term = get_term($countryid, 'sit-country');
$speciality_term = get_term($specialityid, 'sit-speciality');

// Check if terms are valid (not WP_Error and not empty)
$degree_valid = (!is_wp_error($degree_term) && $degree_term);
$country_valid = (!is_wp_error($country_term) && $country_term);
$speciality_valid = (!is_wp_error($speciality_term) && $speciality_term);

// Create SEO-friendly heading based on available filters
if ($speciality_valid && $degree_valid && $country_valid) {
    // All three filters
    $heading = $degree_term->name . ' ' . $speciality_term->name . ' Courses In ' . $country_term->name;
} elseif ($speciality_valid && $country_valid) {
    // Speciality and country
    $heading = $speciality_term->name . ' Programs in ' . $country_term->name;
} elseif ($speciality_valid && $degree_valid) {
    // Speciality and degree
    $heading = $degree_term->name . ' in ' . $speciality_term->name;
} elseif ($speciality_valid) {
    // Only speciality - SEO friendly
    $heading = 'Study ' . $speciality_term->name . ' in Turkey - Find the Best Programs';
} elseif ($degree_valid && $country_valid) {
    // Degree and country
    $heading = $degree_term->name . ' Programs in ' . $country_term->name;
} elseif ($degree_valid) {
    // Only degree
    $heading = $degree_term->name . ' Programs in Turkey';
} elseif ($country_valid) {
    // Only country
    $heading = 'University Programs in ' . $country_term->name;
} else {
    // No filters
    $heading = "Search For Course";
}

?>

<!-- Load shared filter layout styles -->
<link rel="stylesheet" href="<?= plugin_dir_url(__FILE__) ?>../shared/filter-styles.css">

<!-- Search Results Header v2 Styles -->
<style>
/* ═══════════════════════════════════════════
   SEARCH RESULTS PAGE — sr-* Design System
   Matches pp-*/up-* aesthetic
   ═══════════════════════════════════════════ */

/* --- Results Header Bar --- */
.results-header-v2 {
    background: #ffffff;
    border-bottom: 1px solid #e9ecef;
    padding: 16px 24px;
    margin-bottom: 0;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    margin: 0 auto 20px;
    max-width: 1400px;
}

.results-header-inner {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

/* Results Count Badge */
.results-count-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #fef2f2, #fff5f5);
    border: 1px solid rgba(226, 10, 23, 0.15);
    padding: 8px 16px;
    border-radius: 10px;
    white-space: nowrap;
}

.results-count-number {
    font-size: 22px;
    font-weight: 700;
    color: #E20A17;
    line-height: 1;
}

.results-count-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

/* Search Bar */
.results-search-container {
    flex: 1;
    min-width: 200px;
}

.results-search-box {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 4px 4px 4px 14px;
    transition: all 0.2s ease;
}

.results-search-box:focus-within {
    border-color: #E20A17;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(226, 10, 23, 0.08);
}

.results-search-icon {
    color: #9ca3af;
    flex-shrink: 0;
    margin-right: 8px;
}

.results-search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 14px;
    color: #1a1a2e;
    padding: 8px 4px;
    outline: none;
    font-family: inherit;
}

.results-search-input::placeholder {
    color: #9ca3af;
}

.results-search-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: #E20A17;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.results-search-btn:hover {
    background: #c00912;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(226, 10, 23, 0.25);
}

/* Right Side Actions */
.results-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.results-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.results-action-btn:hover {
    border-color: #E20A17;
    color: #E20A17;
    background: #fef7f7;
}

/* Sort Dropdown */
.results-sort-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.results-sort-select {
    appearance: none;
    -webkit-appearance: none;
    padding: 8px 32px 8px 12px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
}

.results-sort-select:hover,
.results-sort-select:focus {
    border-color: #E20A17;
}

.results-sort-chevron {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6b7280;
}

/* View Toggle */
.results-view-toggle {
    display: inline-flex;
    align-items: center;
    background: #f3f4f6;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.results-view-btn {
    padding: 7px 10px;
    border: none;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.results-view-btn.active {
    background: #E20A17;
    color: #fff;
}

.results-view-btn:hover:not(.active) {
    background: #e5e7eb;
    color: #374151;
}

/* Export Button */
.results-export-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.results-export-btn:hover {
    border-color: #E20A17;
    color: #E20A17;
    background: #fef7f7;
}

/* --- Pagination --- */
.filter-pagination {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

.filter-pagination .pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}

.filter-pagination .pagination .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #4b5563;
    background: #fff;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    transition: all 0.2s ease;
}

.filter-pagination .pagination .page-numbers:hover {
    border-color: #E20A17;
    color: #E20A17;
    background: #fef7f7;
}

.filter-pagination .pagination .page-numbers.current {
    background: #E20A17;
    color: #fff;
    border-color: #E20A17;
    font-weight: 600;
}

.filter-pagination .pagination .page-numbers img {
    width: 16px;
    height: 16px;
}

/* --- Related Searches --- */
.related-result {
    max-width: 1400px;
    margin: 30px auto;
    padding: 24px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.related-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 16px 0;
}

.related-row {
    margin-bottom: 20px;
}

.related-row h3 {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.related-row h3 img {
    width: 20px;
    height: 20px;
}

.related-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.related-list li {
    margin: 0;
}

.related-list li a {
    display: inline-block;
    padding: 6px 14px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    font-size: 13px;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.2s ease;
}

.related-list li a:hover {
    background: #fef7f7;
    border-color: #E20A17;
    color: #E20A17;
}

/* --- Export Modal --- */
.export-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.export-popup {
    background: #fff;
    border-radius: 16px;
    width: 95%;
    max-width: 900px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 28px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.export-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
}

.export-header h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.generated-date {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0 0;
}

.header-action {
    display: flex;
    align-items: center;
    gap: 12px;
}

.print-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #E20A17;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.print-btn:hover {
    background: #c00912;
}

.close-export {
    background: none;
    border: none;
    font-size: 28px;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px 8px;
    line-height: 1;
    transition: color 0.2s;
}

.close-export:hover {
    color: #E20A17;
}

.program-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.program-table thead th {
    background: #f8fafc;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.program-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #374151;
}

.program-table tbody tr:hover {
    background: #fef7f7;
}

.footer-popup {
    text-align: center;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid #f1f5f9;
    color: #9ca3af;
    font-size: 12px;
}

/* --- Mobile Responsive for Results Header --- */
@media (max-width: 768px) {
    .results-header-v2 {
        padding: 12px 16px;
        border-radius: 0;
        margin: 0 0 16px;
    }

    .results-header-inner {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .results-count-badge {
        justify-content: center;
    }

    .results-search-container {
        min-width: auto;
    }

    .results-actions {
        justify-content: center;
        flex-wrap: wrap;
    }

    .results-export-btn span,
    .results-filter-toggle span {
        display: none;
    }

    .filter-results {
        grid-template-columns: 1fr !important;
        gap: 16px;
    }

    .ProgramBoxUni-image-container {
        height: 160px;
    }

    .ProgramBoxUni-attributes {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .ProgramBoxUni-attributes {
        grid-template-columns: 1fr;
    }
}

/* Filter sidebar mobile toggle improvements */
@media (max-width: 768px) {
    .filter-sidebar {
        display: none;
    }
    
    .filter-sidebar.show-mobile {
        display: flex;
    }
    
    .results-filter-toggle {
        display: inline-flex !important;
    }
}

@media (min-width: 769px) {
    .results-filter-toggle {
        display: none !important;
    }
}
</style>

<?php
// Prepare data for shared templates
$results_count = isset($found_posts) ? $found_posts : (isset($query) ? $query->found_posts : 0);
$search_value = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Configure which filters to show for this page
$filter_config = [
    'degree' => true,
    'duration' => true,
    'language' => true,
    'price' => true,
    'university' => true,
    'scholarship' => true
];

// Prepare filter data
$filter_data = [
    'degrees' => isset($all_degrees) ? $all_degrees : [],
    'universities' => isset($all_universities_for_filter) ? $all_universities_for_filter : []
];
?>

<!-- Results Header - Modern Redesign -->
<div class="results-header-v2">
    <div class="results-header-inner">
        <!-- Left: Results Count Badge -->
        <div class="results-count-badge">
            <span class="results-count-number"><?= isset($found_posts) ? $found_posts : (isset($query) ? $query->found_posts : 0) ?></span>
            <span class="results-count-label">Programs Found</span>
        </div>

        <!-- Center: Search Bar -->
        <div class="results-search-container">
            <div class="results-search-box">
                <svg class="results-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="search-university" class="results-search-input" value="<?= esc_attr($search_value) ?>" placeholder="Search programs, universities..." />
                <button class="results-search-btn">
                    <span class="btn-text">Search</span>
                    <div class="sit-btn-dots">
                        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="results-actions">
            <!-- Mobile Filter Toggle -->
            <button class="results-action-btn results-filter-toggle" onclick="toggleMobileFilters()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <span>Filters</span>
            </button>

            <!-- Sort Dropdown -->
            <div class="results-sort-wrapper">
                <select class="results-sort-select" id="sort-dropdown">
                    <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "newest") echo "selected"; ?> value="newest">Newest First</option>
                    <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "fee_low") echo "selected"; ?> value="fee_low">Price: Low to High</option>
                    <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "fee_high") echo "selected"; ?> value="fee_high">Price: High to Low</option>
                    <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "popular") echo "selected"; ?> value="popular">Most Popular</option>
                </select>
                <svg class="results-sort-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </div>

            <!-- View Toggle -->
            <div class="results-view-toggle" role="group" aria-label="View toggle">
                <button class="results-view-btn active" data-view="grid" aria-label="Grid view" aria-pressed="true" title="Grid View">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                </button>
                <button class="results-view-btn" data-view="list" aria-label="List view" aria-pressed="false" title="List View">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Export Button -->
            <button onclick="openExportPopup()" id="openExportPopup" class="results-export-btn" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Export</span>
            </button>
        </div>
    </div>
</div>

<!-- Main Container with Sidebar Layout -->
<div class="filter-results-container">
    <!-- Filter Sidebar -->
    <div class="filter-sidebar" id="filterSidebar">
        <div class="filter-sidebar-content">
            <!-- Sidebar Header -->
            <div class="filter-sidebar-header">
                <h3 class="filter-sidebar-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="21" x2="4" y2="14"></line>
                        <line x1="4" y1="10" x2="4" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12" y2="3"></line>
                        <line x1="20" y1="21" x2="20" y2="16"></line>
                        <line x1="20" y1="12" x2="20" y2="3"></line>
                        <line x1="1" y1="14" x2="7" y2="14"></line>
                        <line x1="9" y1="8" x2="15" y2="8"></line>
                        <line x1="17" y1="16" x2="23" y2="16"></line>
                    </svg>
                    Filters
                </h3>
                <button class="clear-all-filters">Clear All</button>
            </div>

            <!-- Applied Filters -->
            <div class="applied-filters-sidebar" id="appliedFiltersSidebar" style="display: none;">
                <h4 class="applied-filters-title">Applied Filters</h4>
                <div class="applied-filters-list filtersApplied">
                    <!-- Applied filters will be populated here by JavaScript -->
                </div>
            </div>

            <!-- Degree Filter (Dynamic - only shows degrees from current results) -->
            <div class="filter-section">
                <h4 class="filter-section-title">🎓 Degree Level</h4>
                <div class="filter-options">
                    <?php
                    $selected_degrees = isset($_GET['level']) ? (is_array($_GET['level']) ? $_GET['level'] : [$_GET['level']]) : [];
                    // Use available_degrees (from results) if provided, otherwise fall back to all_degrees
                    $degrees_to_show = isset($available_degrees) && !empty($available_degrees) ? $available_degrees : (isset($all_degrees) ? $all_degrees : []);
                    
                    if (!empty($degrees_to_show)) {
                        $primary_degrees = ['associate', 'bachelor', 'master', 'phd'];
                        $visible_html = '';
                        $hidden_html = '';
                        $has_hidden = false;

                        foreach ($degrees_to_show as $degree_term) {
                            $is_checked = in_array($degree_term->term_id, $selected_degrees) ? 'checked' : '';
                            $active_class = in_array($degree_term->term_id, $selected_degrees) ? 'active' : '';
                            
                            $html = '<label class="filter-checkbox-label ' . $active_class . '">';
                            $html .= '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                            $html .= '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                            $html .= '</label>';
                            
                            $term_name_lower = strtolower($degree_term->name);
                            $is_primary = false;
                            foreach ($primary_degrees as $pd) {
                                if (strpos($term_name_lower, $pd) !== false) {
                                    $is_primary = true;
                                    break;
                                }
                            }
                            
                            if ($is_primary || !empty($is_checked)) {
                                $visible_html .= $html;
                            } else {
                                $has_hidden = true;
                                $hidden_html .= $html;
                            }
                        }
                        
                        echo $visible_html;
                        if ($has_hidden) {
                            $hidden_html .= '<button type="button" class="show-less-btn" onclick="this.parentElement.style.display=\'none\'; this.parentElement.nextElementSibling.style.display=\'block\'; return false;" style="font-size:13px; color:var(--sit-primary); font-weight:600; padding:8px 0 0 0; margin-top:4px; background:none; border:none; cursor:pointer; width:100%; text-align:left;">- Show less</button>';
                            echo '<div class="hidden-options hidden-degree-options" style="display:none; margin-top:8px;">' . $hidden_html . '</div>';
                            echo '<button type="button" class="show-more-btn" onclick="this.previousElementSibling.style.display=\'block\'; this.style.display=\'none\'; return false;" style="font-size:13px; color:var(--sit-primary); font-weight:600; padding:8px 0 0 0; background:none; border:none; cursor:pointer; width:100%; text-align:left;">+ Show more</button>';
                        }
                    } else {
                        echo '<p class="filter-empty-message">No degree options for current results</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Duration Filter (Dynamic - only shows durations from current results) -->
            <div class="filter-section">
                <h4 class="filter-section-title">⏱️ Duration</h4>
                <div class="filter-button-group">
                    <?php
                    $current_duration = isset($_GET['duration']) ? $_GET['duration'] : '';
                    // Use available_durations from results if provided, otherwise show all
                    $durations = isset($available_durations) && !empty($available_durations) ? $available_durations : ['1 year', '2 years', '3 years', '4 years', '4+ years'];
                    
                    if (!empty($durations)) {
                        foreach ($durations as $duration) {
                            $active_class = ($current_duration == $duration) ? 'active' : '';
                            echo '<button type="button" class="filter-button ' . $active_class . '" data-filter="duration" data-value="' . esc_attr($duration) . '">' . esc_html($duration) . '</button>';
                        }
                    } else {
                        echo '<p class="filter-empty-message">No duration options</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Language Filter (Dynamic - only shows languages from current results) -->
            <div class="filter-section">
                <h4 class="filter-section-title">🌐 Language</h4>
                <div class="filter-options">
                    <?php
                    // Use available_languages from results if provided
                    $languages_to_show = isset($available_languages) && !empty($available_languages) ? $available_languages : [];
                    
                    // Fallback: if no available_languages, use the old method
                    if (empty($languages_to_show)) {
                        $languages_to_show = get_terms(array(
                            'taxonomy' => 'sit-language',
                            'hide_empty' => true,
                        ));
                    }
                    
                    $primary_languages = ['english', 'turkish'];
                    $current_language = isset($_GET['language']) ? $_GET['language'] : '';
                    
                    $has_languages = false;
                    if (!empty($languages_to_show) && !is_wp_error($languages_to_show)) {
                        $visible_html = '';
                        $hidden_html = '';
                        $has_hidden = false;

                        foreach ($languages_to_show as $language) {
                            $language_name = str_replace('%', ' ', $language->name);
                            $has_languages = true;
                            
                            $is_checked = ($current_language == $language->term_id) ? 'checked' : '';
                            $active_class = ($current_language == $language->term_id) ? 'active' : '';
                            
                            $html = '<label class="filter-checkbox-label ' . $active_class . '">';
                            $html .= '<input type="checkbox" class="language-checkbox" value="' . esc_attr($language->term_id) . '" ' . $is_checked . '>';
                            $html .= '<span class="filter-checkbox-text">' . esc_html($language_name) . '</span>';
                            $html .= '</label>';

                            $is_primary = in_array(strtolower($language_name), $primary_languages);
                            
                            // If it's a primary language, or it's currently selected, show it immediately.
                            if ($is_primary || !empty($is_checked)) {
                                $visible_html .= $html;
                            } else {
                                $has_hidden = true;
                                $hidden_html .= $html;
                            }
                        }
                        
                        echo $visible_html;
                        if ($has_hidden) {
                            $hidden_html .= '<button type="button" class="show-less-btn" onclick="this.parentElement.style.display=\'none\'; this.parentElement.nextElementSibling.style.display=\'block\'; return false;" style="font-size:13px; color:var(--sit-primary); font-weight:600; padding:8px 0 0 0; margin-top:4px; background:none; border:none; cursor:pointer; width:100%; text-align:left;">- Show less</button>';
                            echo '<div class="hidden-options hidden-language-options" style="display:none; margin-top:8px;">' . $hidden_html . '</div>';
                            echo '<button type="button" class="show-more-btn" onclick="this.previousElementSibling.style.display=\'block\'; this.style.display=\'none\'; return false;" style="font-size:13px; color:var(--sit-primary); font-weight:600; padding:8px 0 0 0; background:none; border:none; cursor:pointer; width:100%; text-align:left;">+ Show more</button>';
                        }
                    }
                    
                    if (!$has_languages) {
                        echo '<p class="filter-empty-message">No language options for current results</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">💰 Annual Fee (USD)</h4>
                <div class="price-range-inputs">
                    <input type="number" class="price-input" placeholder="Min" id="minPrice" value="<?= isset($_GET['min_fee']) ? esc_attr($_GET['min_fee']) : '' ?>">
                    <span class="price-separator">-</span>
                    <input type="number" class="price-input" placeholder="Max" id="maxPrice" value="<?= isset($_GET['max_fee']) ? esc_attr($_GET['max_fee']) : '' ?>">
                </div>
            </div>

            <!-- University Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">🏫 University</h4>
                <div class="filter-options">
                    <?php
                    $current_universities = isset($all_universities_for_filter) ? $all_universities_for_filter : [];
                    $selected_universities = isset($_GET['university']) ? (is_array($_GET['university']) ? $_GET['university'] : [$_GET['university']]) : [];
                    
                    foreach ($current_universities as $uni_name) {
                        $is_checked = in_array($uni_name, $selected_universities) ? 'checked' : '';
                        $active_class = in_array($uni_name, $selected_universities) ? 'active' : '';
                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                        echo '<input type="checkbox" class="university-checkbox" value="' . esc_attr($uni_name) . '" ' . $is_checked . '>';
                        echo '<span class="filter-checkbox-text">' . esc_html($uni_name) . '</span>';
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>

            <!-- City Filter (Dynamic - only shows cities from current results) -->
            <div class="filter-section">
                <h4 class="filter-section-title">📍 City</h4>
                <div class="filter-options">
                    <?php
                    // Use available_cities from results if provided
                    $cities_to_show = isset($available_cities) && !empty($available_cities) ? $available_cities : [];
                    $selected_cities = isset($_GET['city']) ? (is_array($_GET['city']) ? $_GET['city'] : [$_GET['city']]) : [];
                    
                    $has_cities = false;
                    if (!empty($cities_to_show) && !is_wp_error($cities_to_show)) {
                        foreach ($cities_to_show as $city_term) {
                            $has_cities = true;
                            $is_checked = in_array($city_term->term_id, $selected_cities) ? 'checked' : '';
                            $active_class = in_array($city_term->term_id, $selected_cities) ? 'active' : '';
                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                            echo '<input type="checkbox" class="city-checkbox" value="' . esc_attr($city_term->term_id) . '" ' . $is_checked . '>';
                            echo '<span class="filter-checkbox-text">' . esc_html($city_term->name) . '</span>';
                            echo '</label>';
                        }
                    }
                    
                    if (!$has_cities) {
                        echo '<p class="filter-empty-message">No city options for current results</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Scholarships Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">🎯 Scholarships</h4>
                <div class="filter-button-group">
                    <?php
                    $current_scholarship = isset($_GET['isScholarShip']) ? $_GET['isScholarShip'] : '';
                    $active_class = ($current_scholarship == 'Yes') ? 'active' : '';
                    ?>
                    <button class="filter-button <?= $active_class ?>" data-filter="scholarship" data-value="Yes">Available</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Main Content -->
    <div class="results-main-content">
        


        <!-- GRID VIEW: Default view -->
        <div class="filter-results" id="programsGridContainer">
            <?php
            if (isset($programs) && !empty($programs)) {
                echo '<!-- Programs found: ' . count($programs) . ' -->';
                foreach ($programs as $program) {
                    \SIT\Search\Services\Template::render('shortcodes/program-box', ['program' => $program]);
                }
            } else {
                echo '<!-- No programs variable found or empty -->';
                echo '<div style="padding: 20px; text-align: center; color: #666;">No programs found or programs variable not set.</div>';
            }
            ?>
        </div>

<!-- LIST VIEW: Compact mobile-optimized view -->
<div class="all-faculties-program-list" id="programsListContainer" style="display: none;">
    <?php
    foreach ($programs as $program) {
        ?>
        <div class="program-list-item <?php echo isset($program['featured_class']) ? $program['featured_class'] : ''; ?><?php echo !empty($program['is_featured']) ? ' is-recommended' : ''; ?>">
            <?php if (!empty($program['is_featured'])): ?>
                <div class="program-list-badge-recommended">⭐ Recommended</div>
            <?php endif; ?>
            <div class="program-list-image">
                <?php if (!empty($program['logo_url'])): ?>
                    <img src="<?php echo $program['logo_url']; ?>" alt="<?php echo $program['title']; ?>">
                <?php elseif (!empty($program['image_url'])): ?>
                    <img src="<?php echo $program['image_url']; ?>" alt="<?php echo $program['title']; ?>">
                <?php else: ?>
                    <div class="program-list-placeholder">🏫</div>
                <?php endif; ?>
            </div>
            
            <div class="program-list-content">
                <div class="program-list-info">
                    <h3 class="program-list-title"><?php echo $program['title']; ?></h3>
                    <p class="program-list-university"><?php echo $program['uni_title']; ?></p>
                    
                    <div class="program-list-details">
                        <span class="program-list-detail">
                            🕒 <?php echo $program['duration']; ?>
                        </span>
                        <span class="program-list-detail">
                            🌐 <?php 
                            // Extract language from title if it's in parentheses at the end
                            if (preg_match('/\(([^)]+)\)$/', $program['title'], $matches)) {
                                echo $matches[1];
                            } else {
                                // Fallback to a default or extract from other fields
                                echo 'English'; // or extract from other program data
                            }
                            ?>
                        </span>
                        <span class="program-list-detail">
                            📍 <?php echo $program['country']; ?>
                        </span>
                        <?php if (!empty($program['ranking'])): ?>
                        <span class="program-list-detail">
                            ⭐ Ranking: <?php echo $program['ranking']; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="program-list-right">
                    <div class="program-list-fee">
                        <?php 
                        $currency = !empty($program['Tuition_Currency']) ? $program['Tuition_Currency'] : 'USD';
                        ?>
                        <?php if (!empty($program['discounted_fee'])): ?>
                            <span class="program-list-original-fee"><?php echo $program['fee']; ?> <?php echo $currency; ?></span>
                            <span class="program-list-discounted-fee"><?php echo $program['discounted_fee']; ?> <?php echo $currency; ?></span>
                        <?php elseif (!empty($program['Advanced_Discount'])): ?>
                            <span class="program-list-original-fee"><?php echo $program['fee']; ?> <?php echo $currency; ?></span>
                            <span class="program-list-discounted-fee"><?php echo $program['Advanced_Discount']; ?> <?php echo $currency; ?></span>
                        <?php else: ?>
                            <span class="program-list-current-fee"><?php echo $program['fee']; ?> <?php echo $currency; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="program-list-actions">
                        <?php 
                        // Simple and safe approach
                        $program_link = isset($program['link']) ? $program['link'] : '#';
                        ?>
                        <a href="<?php echo esc_url($program_link); ?>" class="program-list-btn program-list-btn-primary">View Details</a>
                        <?php // Apply button temporarily removed - apply page needs fixing ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
    </div>
</div>

<div class="filter-pagination">
    <?php
    global $wp;
    $big = 999999999;

    // Preserve existing query parameters
    $base_url = esc_url(add_query_arg($_GET, get_pagenum_link($big)));
    $clean_url = strstr($base_url, '#', true) ?: $base_url;
    $paginate_links = paginate_links(array(
        'base' => str_replace($big, '%#%', $clean_url),
        'format' => '&paged=%#%', // Use & instead of ? since params already exist
        'current' => max(1, get_query_var('paged')),
        'total' => isset($max_num_pages) ? $max_num_pages : $query->max_num_pages,
        'prev_text' => '<img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-left-975UQXVKZF.svg" alt="Previous">',
        'next_text' => '<img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-right-WDGHUKQ634.svg" alt="Next">',
    ));

    if ($paginate_links) {
        echo '<div class="pagination">' . $paginate_links . '</div>';
    }
    ?>
</div>

<div class="related-result" style="" bis_skin_checked="1">
    <h4 class="related-title">Related searches</h4>
    <?php
    if (!empty($degreeid) && $degreeid != '0' && !empty($countryid) && $countryid != '0') {
        $tax_query_rec = array('relation' => 'AND');
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-degree',
            'field' => 'term_id',
            'terms' => $degreeid,
        );
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-country',
            'field' => 'term_id',
            'terms' => $countryid,
        );
        $args_recom = array(
            'post_type' => 'sit-program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        $args_recom['tax_query'] = $tax_query_rec;
        $recommended = new \WP_Query($args_recom);
        $rem_programs = $recommended->get_posts();
        if ($recommended->found_posts > 0) {
            ?>
            <div class="related-row" bis_skin_checked="1">
                <h3 class="">
                    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/open-book-1.png"
                         alt="open-book open-book">
                    Recommended study areas</h3>
                <ul class="related-list studyarea">
                    <?php
                    foreach ($rem_programs as $program) {
                        ?>
                        <li><a href="<?= $program->guid ?>"><?= $program->post_title ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <?php
    }
    ?>
    <?php
    if (!empty($degreeid) && $degreeid != '0' && !empty($specialityid) && $specialityid != '0') {
        $tax_query_rec = array('relation' => 'AND');
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-degree',
            'field' => 'term_id',
            'terms' => $degreeid,
        );
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-speciality',
            'field' => 'term_id',
            'terms' => $specialityid,
        );
        $args_recom = array(
            'post_type' => 'sit-program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        $args_recom['tax_query'] = $tax_query_rec;
        $recommended = new \WP_Query($args_recom);
        $rem_programs = $recommended->get_posts();
        if ($recommended->found_posts > 0) {
            ?>
            <div class="related-row" bis_skin_checked="1">
                <h3 class="">
                    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/open-book-1.png"
                         alt="open-book open-book">
                    Recommended destinations</h3>
                <ul class="related-list studydestination">
                    <?php
                    foreach ($rem_programs as $program) {
                        ?>
                        <li><a href="<?= $program->guid ?>"><?= $program->post_title ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <?php
    }
    ?>
    <?php
    if (!empty($specialityid) && $specialityid != '0' && !empty($countryid) && $countryid != '0') {
        $tax_query_rec = array('relation' => 'AND');
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-speciality',
            'field' => 'term_id',
            'terms' => $specialityid,
        );
        $tax_query_rec[] = array(
            'taxonomy' => 'sit-country',
            'field' => 'term_id',
            'terms' => $countryid,
        );
        $args_recom = array(
            'post_type' => 'sit-program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
        $args_recom['tax_query'] = $tax_query_rec;
        $recommended = new \WP_Query($args_recom);
        $rem_programs = $recommended->get_posts();
        if ($recommended->found_posts > 0) {
            ?>
            <div class="related-row" bis_skin_checked="1">
                <h3 class="">
                    <img src="https://search.studyinturkiye.com/wp-content/uploads/2025/02/open-book-1.png"
                         alt="open-book open-book">
                    Recommended study levels</h3>
                <ul class="related-list studylevel">
                    <?php
                    foreach ($rem_programs as $program) {
                        ?>
                        <li><a href="<?= $program->guid ?>"><?= $program->post_title ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>
        <?php
    }
    ?>
</div>
<!-- Load jsPDF + autoTable (text-based PDF, no html2canvas) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>



<script>
    function downloadPDF() {
        if (typeof jspdf === 'undefined') {
            alert('PDF library is loading, please try again in a moment.');
            return;
        }

        var previewTable = document.querySelector('#exportModal .program-table');
        if (!previewTable) {
            alert('No program data found!');
            return;
        }
        var rows = Array.from(previewTable.querySelectorAll('tbody tr'));
        var totalPrograms = rows.length;
        var today = new Date().toISOString().split('T')[0];

        // Extract table data + track discount info separately
        var tableData = [];
        var discountMap = {};

        rows.forEach(function(row, rowIdx) {
            var cells = row.querySelectorAll('td');
            var rowData = [];
            for (var i = 0; i < cells.length; i++) {
                if (i === cells.length - 1) {
                    var crossed = cells[i].querySelector('s, del, strike, span[style*="line-through"]');
                    var bold = cells[i].querySelector('b, strong');
                    if (crossed && bold) {
                        var oldP = crossed.textContent.trim();
                        var newP = bold.textContent.trim();
                        rowData.push(newP);
                        discountMap[rowIdx] = { oldPrice: oldP, newPrice: newP };
                    } else {
                        rowData.push(cells[i].textContent.trim());
                    }
                } else {
                    rowData.push(cells[i].textContent.trim());
                }
            }
            tableData.push(rowData);
        });

        var btn = document.querySelector('#exportModal .print-btn');
        var originalText = btn.innerHTML;
        btn.innerHTML = '<svg style="animation:spin 1s linear infinite" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Generating PDF...';
        btn.disabled = true;

        var logoUrl = 'https://search.studyinturkiye.com/wp-content/uploads/2025/02/image-1-1-e1738931290741.png';
        var fontRegUrl = 'https://cdn.jsdelivr.net/gh/googlefonts/roboto/src/hinted/Roboto-Regular.ttf';
        var fontBoldUrl = 'https://cdn.jsdelivr.net/gh/googlefonts/roboto/src/hinted/Roboto-Bold.ttf';

        Promise.all([
            pdfLoadImage(logoUrl),
            pdfLoadFont(fontRegUrl),
            pdfLoadFont(fontBoldUrl)
        ]).then(function(res) {
            pdfBuild(res[0], res[1], res[2]);
        }).catch(function(err) {
            console.warn('PDF resource load error:', err);
            pdfBuild(null, null, null);
        });

        function pdfLoadImage(url) {
            return new Promise(function(resolve) {
                var img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function() {
                    try {
                        var c = document.createElement('canvas');
                        c.width = img.naturalWidth;
                        c.height = img.naturalHeight;
                        c.getContext('2d').drawImage(img, 0, 0);
                        resolve({ data: c.toDataURL('image/png'), w: img.naturalWidth, h: img.naturalHeight });
                    } catch(e) { resolve(null); }
                };
                img.onerror = function() { resolve(null); };
                img.src = url;
            });
        }

        function pdfLoadFont(url) {
            return fetch(url).then(function(r) {
                if (!r.ok) throw new Error('Font fetch ' + r.status);
                return r.arrayBuffer();
            }).then(function(buf) {
                var bin = '';
                var u8 = new Uint8Array(buf);
                for (var i = 0; i < u8.length; i++) bin += String.fromCharCode(u8[i]);
                return btoa(bin);
            }).catch(function() { return null; });
        }

        function pdfBuild(logo, fontReg, fontBold) {
            var doc = new jspdf.jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
            var pw = doc.internal.pageSize.getWidth();
            var ph = doc.internal.pageSize.getHeight();
            var mx = 14;
            var fn = 'helvetica';

            if (fontReg) {
                doc.addFileToVFS('Roboto-Regular.ttf', fontReg);
                doc.addFont('Roboto-Regular.ttf', 'Roboto', 'normal');
                fn = 'Roboto';
            }
            if (fontBold) {
                doc.addFileToVFS('Roboto-Bold.ttf', fontBold);
                doc.addFont('Roboto-Bold.ttf', 'Roboto', 'bold');
            }

            // ===== PAGE 1 HEADER =====
            var logoW = 45;
            var logoH = 18;
            if (logo) {
                logoH = logoW * (logo.h / logo.w);
                doc.addImage(logo.data, 'PNG', pw - mx - logoW, 8, logoW, logoH);
            }

            doc.setFont(fn, 'bold');
            doc.setFontSize(18);
            doc.setTextColor(26, 26, 46);
            doc.text('Academic Programs in Turkey', mx, 20);

            doc.setFont(fn, 'normal');
            doc.setFontSize(9);
            doc.setTextColor(107, 114, 128);
            doc.text('Generated on: ' + today + '  |  Total Programs: ' + totalPrograms, mx, 26);

            doc.setDrawColor(226, 10, 23);
            doc.setLineWidth(0.8);
            doc.line(mx, 30, pw - mx, 30);

            // ===== PROGRAM TABLE =====
            doc.autoTable({
                startY: 34,
                head: [['Program', 'University', 'Duration', 'Language', 'Tuition']],
                body: tableData,
                theme: 'grid',
                headStyles: {
                    fillColor: [226, 10, 23],
                    textColor: 255,
                    fontStyle: 'bold',
                    fontSize: 8,
                    font: fn,
                    cellPadding: 3,
                    halign: 'left'
                },
                bodyStyles: {
                    fontSize: 7.5,
                    cellPadding: 2.5,
                    textColor: [55, 65, 81],
                    font: fn,
                    lineColor: [229, 231, 235],
                    lineWidth: 0.2
                },
                alternateRowStyles: {
                    fillColor: [248, 249, 250]
                },
                columnStyles: {
                    0: { cellWidth: 52, fontStyle: 'bold', textColor: [26, 26, 46] },
                    1: { cellWidth: 44 },
                    2: { cellWidth: 18, halign: 'center' },
                    3: { cellWidth: 20, halign: 'center' },
                    4: { cellWidth: 'auto' }
                },
                styles: {
                    overflow: 'linebreak',
                    font: fn
                },
                showHead: 'everyPage',
                margin: { top: 22, right: mx, bottom: 18, left: mx },

                didParseCell: function(data) {
                    if (data.section === 'body' && data.column.index === 4 && discountMap[data.row.index]) {
                        data.cell.styles.cellPadding = { top: 5, bottom: 4, left: 2.5, right: 2.5 };
                    }
                },

                didDrawCell: function(data) {
                    if (data.section === 'body' && data.column.index === 4 && discountMap[data.row.index]) {
                        var info = discountMap[data.row.index];
                        var cell = data.cell;
                        var x = cell.x + 2.5;
                        var bg = data.row.index % 2 === 0 ? [255, 255, 255] : [248, 249, 250];
                        doc.setFillColor(bg[0], bg[1], bg[2]);
                        doc.rect(cell.x + 0.3, cell.y + 0.3, cell.width - 0.6, cell.height - 0.6, 'F');

                        doc.setFont(fn, 'normal');
                        doc.setFontSize(7);
                        doc.setTextColor(170, 170, 170);
                        var oldY = cell.y + 5;
                        doc.text(info.oldPrice, x, oldY);
                        var oldW = doc.getTextWidth(info.oldPrice);
                        doc.setDrawColor(170, 170, 170);
                        doc.setLineWidth(0.3);
                        doc.line(x, oldY - 0.8, x + oldW, oldY - 0.8);

                        doc.setFont(fn, 'bold');
                        doc.setFontSize(8);
                        doc.setTextColor(26, 26, 46);
                        doc.text(info.newPrice, x, oldY + 5);
                    }
                },

                didDrawPage: function(data) {
                    var pn = doc.internal.getCurrentPageInfo().pageNumber;

                    if (pn > 1) {
                        if (logo) {
                            var lw2 = 32;
                            var lh2 = lw2 * (logo.h / logo.w);
                            doc.addImage(logo.data, 'PNG', pw - mx - lw2, 4, lw2, lh2);
                        }
                        doc.setFont(fn, 'bold');
                        doc.setFontSize(11);
                        doc.setTextColor(26, 26, 46);
                        doc.text('Academic Programs in Turkey', mx, 12);
                        doc.setFont(fn, 'normal');
                        doc.setFontSize(8);
                        doc.setTextColor(156, 163, 175);
                        doc.text('Page ' + pn, mx, 17);
                        doc.setDrawColor(226, 10, 23);
                        doc.setLineWidth(0.4);
                        doc.line(mx, 19, pw - mx, 19);
                    }

                    doc.setDrawColor(229, 231, 235);
                    doc.setLineWidth(0.3);
                    doc.line(mx, ph - 12, pw - mx, ph - 12);
                    doc.setFont(fn, 'normal');
                    doc.setFontSize(7);
                    doc.setTextColor(156, 163, 175);
                    doc.text('StudyinTurkiye.com \u2014 Powered by Sitconnect', mx, ph - 8);
                    doc.text('Page ' + pn, pw - mx, ph - 8, { align: 'right' });
                }
            });

            // ===== CONTACT INFO =====
            var finalY = doc.lastAutoTable.finalY + 12;
            if (finalY > ph - 55) {
                doc.addPage();
                finalY = 22;
            }

            doc.setDrawColor(226, 10, 23);
            doc.setLineWidth(0.5);
            doc.line(mx, finalY, pw - mx, finalY);

            doc.setFont(fn, 'bold');
            doc.setFontSize(12);
            doc.setTextColor(26, 26, 46);
            doc.text('Contact Information', mx, finalY + 8);

            doc.setFillColor(248, 249, 250);
            doc.roundedRect(mx, finalY + 12, pw - 2 * mx, 22, 2, 2, 'F');

            doc.setFont(fn, 'normal');
            doc.setFontSize(9);
            doc.setTextColor(55, 65, 81);
            doc.text('For more information about these programs, please contact our support team.', mx + 5, finalY + 20);

            doc.setFont(fn, 'bold');
            doc.setTextColor(226, 10, 23);
            doc.text('info@studyinturkiye.com  |  www.studyinturkiye.com', mx + 5, finalY + 28);

            var bY = finalY + 42;
            doc.setDrawColor(229, 231, 235);
            doc.setLineWidth(0.3);
            doc.line(mx, bY, pw - mx, bY);

            doc.setFont(fn, 'normal');
            doc.setFontSize(8);
            doc.setTextColor(100, 100, 100);
            doc.text('StudyinTurkiye.com \u2014 Powered by Sitconnect', pw / 2, bY + 6, { align: 'center' });

            doc.setFontSize(7);
            doc.setTextColor(156, 163, 175);
            doc.text('This document was generated for informational purposes only.', pw / 2, bY + 11, { align: 'center' });

            doc.save('Academic_Programs_Turkey_' + today + '.pdf');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
<!-- Export Popup (Preview Modal) -->
<div class="export-overlay" id="exportModal">
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <button onclick="downloadPDF()" class="print-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
                <p>Total Programs: <?= is_array($pdf_program) ? count($pdf_program) : 0; ?></p>
            </div>
            <button class="close-export" onclick="closeExportPopup()">×</button>
        </div>

        <?php if (!empty($disstr)): ?>
        <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;"><?= $disstr ?></p>
        <?php endif; ?>

        <h3 style="margin-bottom: 15px;">Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array($pdf_program)) {
            foreach ($pdf_program as $program) {
                $currency = !empty($program['Tuition_Currency']) ? $program['Tuition_Currency'] : 'USD';
                if(!empty($program['discounted_fee'])){
                    $fee='<span style="text-decoration:line-through;color:#999;">'.$program['fee'].' '.$currency.'</span> <strong>'.$program['discounted_fee'].' '.$currency.'</strong>';
                } else {
                    $fee=$program['fee'].' '.$currency;
                }
                $lang = !empty($program['language']) ? $program['language'] : 'English';
                $dur = !empty($program['duration']) ? $program['duration'] . ' Year' . ($program['duration'] > 1 ? 's' : '') : '-';
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $dur ?></td>
                    <td><?= $lang ?></td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            }
            ?>
            </tbody>
        </table>

        <div class="footer-popup">
            <p>StudyinTurkiye.com — Powered by <strong>Sitconnect</strong></p>
            <p style="font-size: 11px; color: #9ca3af; margin-top: 4px;">This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>

<!-- Hidden PDF Source (used by downloadPDF) -->
<div id="expoting-download" style="display:none;"></div>

<script>
    function closeExportPopup() {
        document.getElementById("exportModal").style.display = "none";
    }

    function openExportPopup() {
        document.getElementById("exportModal").style.display = "flex";
    }
</script>


<style>
/* View Toggle Styles */
.view-toggle {
    display: flex;
    
    background: #f8f9fa;
    overflow: hidden;
    margin-right: 12px;
}

.view-toggle button {
    padding: 10px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
    color: #6c757d;
}

.view-toggle button.active {
    background: #E20A17;
    color: white;
}

.view-toggle button:hover:not(.active) {
    background: #e9ecef;
    color: #495057;
}

/* Debug: Ensure List button is visible - VERY AGGRESSIVE */
.view-toggle button[data-view="list"] {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    min-width: 80px !important;
    height: 40px !important;
    color: #000 !important;
    font-weight: bold !important;
    z-index: 9999 !important;
    position: relative !important;
}

/* Also make the container more visible */
.view-toggle {

    min-width: 150px !important;
}

/* List View Styles - Same as program-archive.html.php */
.all-faculties-program-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
}

.program-list-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.program-list-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #E20A17;
}

.program-list-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.program-list-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.program-list-placeholder {
    font-size: 24px;
    color: #6c757d;
}

.program-list-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.program-list-info {
    flex: 1;
}

.program-list-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.program-list-university {
    color: #6c757d;
    font-size: 14px;
    margin: 0 0 12px 0;
}

.program-list-details {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.program-list-detail {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #6c757d;
}

.program-list-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 16px;
    text-align: right;
}

.program-list-fee {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.program-list-original-fee {
    font-size: 14px;
    color: #6c757d;
    text-decoration: line-through;
    margin-bottom: 2px;
}

.program-list-discounted-fee,
.program-list-current-fee {
    font-size: 18px;
    font-weight: 600;
    color: #E20A17;
}

.program-list-actions {
    display: flex;
    gap: 8px;
}

.program-list-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
}

.program-list-btn-primary {
    background: #E20A17;
    color: white;
    border: 1px solid #E20A17;
}

.program-list-btn-primary:hover {
    background: #B8080F;
    border-color: #B8080F;
    transform: translateY(-1px);
}

.program-list-btn-outline {
    background: transparent;
    color: #E20A17;
    border: 1px solid #E20A17;
}

.program-list-btn-outline:hover {
    background: #E20A17;
    color: white;
    transform: translateY(-1px);
}

/* Mobile Responsive - Same compact design */
@media (max-width: 768px) {
    .view-toggle {
        order: -1;
        width: 100%;
        justify-content: center;
        margin-right: 0;
        margin-bottom: 12px;
    }
    
    .all-faculties-program-list {
        gap: 12px;
        padding: 0 4px;
    }
    
    .program-list-item {
        flex-direction: row;
        align-items: center;
        text-align: left;
        gap: 0;
        padding: 12px 16px;
        min-height: auto;
    }
    
    .program-list-image {
        display: none;
    }
    
    .program-list-content {
        flex-direction: row;
        gap: 12px;
        width: 100%;
        justify-content: space-between;
        align-items: center;
    }
    
    .program-list-info {
        text-align: left;
        flex: 1;
        min-width: 0;
    }
    
    .program-list-title {
        font-size: 15px;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    
    .program-list-university {
        font-size: 12px;
        margin-bottom: 6px;
        color: #666;
    }
    
    .program-list-right {
        align-items: flex-end;
        text-align: right;
        gap: 8px;
        min-width: 120px;
        flex-shrink: 0;
    }
    
    .program-list-details {
        justify-content: flex-start;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 2px;
    }
    
    .program-list-detail {
        font-size: 11px;
        padding: 2px 6px;
        background: #f0f0f0;
        border-radius: 3px;
        white-space: nowrap;
    }
    
    .program-list-fee {
        margin-bottom: 8px;
    }
    
    .program-list-original-fee {
        font-size: 11px;
    }
    
    .program-list-discounted-fee,
    .program-list-current-fee {
        font-size: 14px;
        font-weight: 600;
    }
    
    .program-list-actions {
        width: 100%;
        flex-direction: column;
        gap: 4px;
    }
    
    .program-list-btn {
        width: 100%;
        padding: 6px 10px;
        font-size: 11px;
        min-width: 0;
        white-space: nowrap;
    }
}

@media (max-width: 480px) {
    .all-faculties-program-list {
        gap: 8px;
        padding: 0 2px;
    }
    
    .program-list-item {
        padding: 10px 12px;
        gap: 0;
    }
    
    .program-list-btn {
        padding: 5px 8px;
        font-size: 10px;
    }
}
</style>

<script>
// View toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== VIEW TOGGLE INIT ===');
    
    const gridContainer = document.getElementById('programsGridContainer');
    const listContainer = document.getElementById('programsListContainer');
    
    console.log('Grid container:', gridContainer);
    console.log('List container:', listContainer);
    
    if (!gridContainer || !listContainer) {
        console.log('Missing containers, aborting view toggle init');
        return;
    }
    
    // Get view buttons using multiple selectors for compatibility
    const viewButtons = document.querySelectorAll('.view-btn, .results-view-btn');
    console.log('View buttons found:', viewButtons.length);
    
    function switchView(view) {
        console.log('Switching to view:', view);
        
        // Update button states
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', 'false');
        });
        
        const activeBtn = document.querySelector(`[data-view="${view}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.setAttribute('aria-pressed', 'true');
        }
        
        // Toggle container visibility
        if (view === 'grid') {
            gridContainer.style.display = '';
            listContainer.style.display = 'none';
        } else if (view === 'list') {
            gridContainer.style.display = 'none';
            listContainer.style.display = 'flex';
        }
        
        localStorage.setItem('programView', view);
    }
    
    // Attach click handlers
    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Button clicked:', this.dataset.view);
            switchView(this.dataset.view);
        });
    });
    
    // Restore saved view preference
    const savedView = localStorage.getItem('programView');
    if (savedView && savedView === 'list') {
        switchView('list');
    }
    
    console.log('View toggle initialized successfully');
});

// Mobile filter toggle functionality
function toggleMobileFilters() {
    const sidebar = document.getElementById('filterSidebar');
    sidebar.classList.toggle('mobile-hidden');
}
</script>
