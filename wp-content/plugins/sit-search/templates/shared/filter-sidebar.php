<?php
/**
 * Shared Filter Sidebar Template
 * Used by all result pages for consistent filtering experience
 * 
 * Expected variables:
 * - $filter_config: Array defining which filters to show
 * - $current_filters: Array of current filter values from $_GET
 * - $filter_data: Array containing filter options (degrees, universities, etc.)
 */

// Default filter configuration if not provided
if (!isset($filter_config)) {
    $filter_config = [
        'degree' => true,
        'duration' => true,
        'language' => true,
        'price' => true,
        'university' => true,
        'scholarship' => true
    ];
}

// Get current filter values
$current_filters = [
    'degree' => isset($_GET['level']) ? (is_array($_GET['level']) ? $_GET['level'] : [$_GET['level']]) : [],
    'duration' => isset($_GET['duration']) ? $_GET['duration'] : '',
    'language' => isset($_GET['language']) ? $_GET['language'] : '',
    'min_fee' => isset($_GET['min_fee']) ? $_GET['min_fee'] : '',
    'max_fee' => isset($_GET['max_fee']) ? $_GET['max_fee'] : '',
    'university' => isset($_GET['university']) ? (is_array($_GET['university']) ? $_GET['university'] : [$_GET['university']]) : [],
    'scholarship' => isset($_GET['isScholarShip']) ? $_GET['isScholarShip'] : ''
];
?>

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

        <?php if (isset($filter_config['degree']) && $filter_config['degree']): ?>
        <!-- Degree Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">🎓 Degree Level</h4>
            <div class="filter-options">
                <?php
                if (isset($filter_data['degrees']) && !empty($filter_data['degrees'])) {
                    foreach ($filter_data['degrees'] as $degree_term) {
                        $is_checked = in_array($degree_term->term_id, $current_filters['degree']) ? 'checked' : '';
                        $active_class = in_array($degree_term->term_id, $current_filters['degree']) ? 'active' : '';
                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                        echo '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                        echo '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                        echo '</label>';
                    }
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($filter_config['duration']) && $filter_config['duration']): ?>
        <!-- Duration Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">⏱️ Duration</h4>
            <div class="filter-button-group">
                <?php
                $durations = ['1 year', '2 years', '3 years', '4 years', '4+ years'];
                foreach ($durations as $duration) {
                    $active_class = ($current_filters['duration'] == $duration) ? 'active' : '';
                    echo '<button class="filter-button ' . $active_class . '" data-filter="duration" data-value="' . esc_attr($duration) . '">' . esc_html($duration) . '</button>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($filter_config['language']) && $filter_config['language']): ?>
        <!-- Language Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">🌐 Language</h4>
            <div class="filter-options">
                <?php
                $languages = get_terms(array(
                    'taxonomy' => 'sit-language',
                    'hide_empty' => true,
                ));
                $allowed_languages = ['Arabic', 'English', 'Turkish'];
                
                foreach ($languages as $language) {
                    $language_name = str_replace('%', ' ', $language->name);
                    if (in_array($language_name, $allowed_languages)) {
                        $is_checked = ($current_filters['language'] == $language->term_id) ? 'checked' : '';
                        $active_class = ($current_filters['language'] == $language->term_id) ? 'active' : '';
                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                        echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($language->term_id) . '" ' . $is_checked . '>';
                        echo '<span class="filter-checkbox-text">' . esc_html($language_name) . '</span>';
                        echo '</label>';
                    }
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($filter_config['price']) && $filter_config['price']): ?>
        <!-- Price Range Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">💰 Annual Fee (USD)</h4>
            <div class="price-range-inputs">
                <input type="number" class="price-input" placeholder="Min" id="minPrice" value="<?= esc_attr($current_filters['min_fee']) ?>">
                <span class="price-separator">-</span>
                <input type="number" class="price-input" placeholder="Max" id="maxPrice" value="<?= esc_attr($current_filters['max_fee']) ?>">
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($filter_config['university']) && $filter_config['university']): ?>
        <!-- University Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">🏫 University</h4>
            <div class="filter-options">
                <?php
                if (isset($filter_data['universities']) && !empty($filter_data['universities'])) {
                    foreach ($filter_data['universities'] as $uni_name) {
                        $is_checked = in_array($uni_name, $current_filters['university']) ? 'checked' : '';
                        $active_class = in_array($uni_name, $current_filters['university']) ? 'active' : '';
                        echo '<label class="filter-checkbox-label ' . $active_class . '">';
                        echo '<input type="checkbox" class="university-checkbox" value="' . esc_attr($uni_name) . '" ' . $is_checked . '>';
                        echo '<span class="filter-checkbox-text">' . esc_html($uni_name) . '</span>';
                        echo '</label>';
                    }
                }
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($filter_config['scholarship']) && $filter_config['scholarship']): ?>
        <!-- Scholarships Filter -->
        <div class="filter-section">
            <h4 class="filter-section-title">🎯 Scholarships</h4>
            <div class="filter-button-group">
                <?php
                $active_class = ($current_filters['scholarship'] == 'Yes') ? 'active' : '';
                ?>
                <button class="filter-button <?= $active_class ?>" data-filter="scholarship" data-value="Yes">Available</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
