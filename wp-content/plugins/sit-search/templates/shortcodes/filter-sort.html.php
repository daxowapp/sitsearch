<?php
// Session should be started in App.php init hook, not here
// Removed session_start() to prevent "headers already sent" warning

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

<!-- Filter styles are embedded in this file for now -->

<?php
// Prepare data for shared templates
$results_count = isset($query) ? $query->found_posts : 0;
$search_value = isset($_GET['search']) ? $_GET['search'] : '';

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
            <span class="results-count-number"><?= $query->found_posts ?></span>
            <span class="results-count-label">Programs Found</span>
        </div>

        <!-- Center: Search Bar -->
        <div class="results-search-container">
            <div class="results-search-box">
                <svg class="results-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="search-university" class="results-search-input" value="<?= isset($_GET['search']) ? esc_attr($_GET['search']) : '' ?>" placeholder="Search programs, universities..." />
                <button class="results-search-btn">
                    <span>Search</span>
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
                        foreach ($degrees_to_show as $degree_term) {
                            $is_checked = in_array($degree_term->term_id, $selected_degrees) ? 'checked' : '';
                            $active_class = in_array($degree_term->term_id, $selected_degrees) ? 'active' : '';
                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                            echo '<input type="checkbox" class="degree-checkbox" value="' . esc_attr($degree_term->term_id) . '" ' . $is_checked . '>';
                            echo '<span class="filter-checkbox-text">' . esc_html($degree_term->name) . '</span>';
                            echo '</label>';
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
                    
                    $allowed_languages = ['Arabic', 'English', 'Turkish'];
                    $current_language = isset($_GET['language']) ? $_GET['language'] : '';
                    
                    $has_languages = false;
                    if (!empty($languages_to_show) && !is_wp_error($languages_to_show)) {
                        foreach ($languages_to_show as $language) {
                            $language_name = str_replace('%', ' ', $language->name);
                            if (in_array($language_name, $allowed_languages)) {
                                $has_languages = true;
                                $is_checked = ($current_language == $language->term_id) ? 'checked' : '';
                                $active_class = ($current_language == $language->term_id) ? 'active' : '';
                                echo '<label class="filter-checkbox-label ' . $active_class . '">';
                                echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($language->term_id) . '" ' . $is_checked . '>';
                                echo '<span class="filter-checkbox-text">' . esc_html($language_name) . '</span>';
                                echo '</label>';
                            }
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
                <?php if (!empty($program['image_url'])): ?>
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
<!-- Export Popup -->
<div class="export-overlay" id="exportModal">
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <button onclick="downloadPDF()" class="print-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer h-4 w-4"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path><rect x="6" y="14" width="12" height="8" rx="1"></rect></svg> Print/Save PDF</button>
                <p>Total Programs:<?= is_array($pdf_program) ? count($pdf_program) : 0; ?></p>
            </div>
            <button class="export-btn" onclick="exportToPDF()">Export PDF</button>
            <button class="close-export" onclick="closeExportPopup()">×</button>
        </div>

        <p><?= $disstr ?></p>

        <h3>Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Deadline</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array($pdf_program)) {
            foreach ($pdf_program as $program) {
                if(!empty($program['discounted_fee'])){
                    $fee='<span>'.$program['fee'].' USD</span>'.$program['discounted_fee'].' USD';
                }
                else{
                    $fee=$program['fee'].' USD';
                }
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $program['duration'] ?></td>
                    <td>English</td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            }
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <h3>Program Details</h3>
        <?php
        if (is_array($pdf_program)) {
        foreach ($pdf_program as $program) {
        ?>
            <div class="program-card">
                <div class="university-image">
                    <img src="<?= $program['image_url'] ?>" alt="">
                </div>
                <div class="program-detail">
                    <div class="program-title"><?= $program['title'] ?></div>
                    <div class="uni-name"><?= $program['uni_title'] ?></div>
                    <div class="program-info-grid">
                        <div class="info-item">
                            <span class="icon">🕒</span>
                            <span><?= $program['duration'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">🌐</span>
                            <span>English</span>
                        </div>
                        <div class="info-item">
                            <span class="icon">📍</span>
                            <span><?= $program['country'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">💲</span>
                            <span><?= $program['fee'] ?> USD</span>
                        </div>
                        <div class="info-item">
                            <span class="icon">💳</span>
                            <span>Rankings: <?= $program['ranking'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="icon">📅</span>
                            <span>Students: <?= $program['students'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        }
        ?>
        <!-- Repeat .program-card blocks for other programs -->

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p>This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>
<div id="expoting-download" >
    <div class="export-popup">
        <div class="export-header">
            <div class="headers-info">
                <h2>Academic Programs in Turkey</h2>
                <p class="generated-date">Generated on: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="header-action">
                <p>Total Programs:<?= is_array($pdf_program) ? count($pdf_program) : 0; ?></p>
            </div>
        </div>

        <p><?= $disstr ?></p>

        <h3>Program Listing</h3>
        <table class="program-table" id="table-program">
            <thead>
            <tr>
                <th>Program</th>
                <th>University</th>
                <th>Duration</th>
                <th>Language</th>
                <th>Deadline</th>
                <th>Tuition</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array($pdf_program)) {
            foreach ($pdf_program as $program) {
                if(!empty($program['discounted_fee'])){
                    $fee='<span style=" text-decoration: line-through;display: block;">'.$program['fee'].' USD</span>'.$program['discounted_fee'].' USD';
                }
                else{
                    $fee=$program['fee'].' USD';
                }
                ?>
                <tr>
                    <td><?= $program['title'] ?></td>
                    <td><?= $program['uni_title'] ?></td>
                    <td><?= $program['duration'] ?></td>
                    <td>English</td>
                    <td>May 15, 2024</td>
                    <td><?= $fee ?></td>
                </tr>
                <?php
            }
            }
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <div class="contact-info">
            <h4>Contact Information</h4>
            <p>
                For more information about these programs or assistance with your application, please contact our support team.<br>
                Email: support@studyinturkey.com<br>
                Website: www.studyinturkey.com
            </p>
        </div>
        <div class="footer-popup">
            <p>© 2025 Study in Turkey. All rights reserved.</p>
            <p>This document was generated for informational purposes only.</p>
        </div>
    </div>
</div>
<script>
    function closeExportPopup() {
        document.getElementById("exportModal").style.display = "none";
    }

    function openExportPopup() {
        document.getElementById("exportModal").style.display = "flex";
    }
</script>
<script>
    function downloadPDF() {
        const originalElement = document.getElementById("expoting-download");

        // Clone the original element
        const clone = originalElement.cloneNode(true);

        // Remove contact info and footer if present in the clone
        const contactInfo = clone.querySelector('.contact-info');
        const footer = clone.querySelector('.footer-popup');
        const table = clone.querySelector("table");

        if (contactInfo) contactInfo.remove();
        if (footer) footer.remove();

        if (!table) {
            alert("No table found!");
            return;
        }

        // Extract the table's thead and tbody rows
        const rows = Array.from(table.querySelectorAll("tbody tr"));
        const thead = table.querySelector("thead")?.cloneNode(true);

        // Remove the original table from the cloned content
        table.remove();

        // --- Get header content (before the table) ---
        const headerContent = document.createElement("div");
        let reachedTable = false;
        Array.from(clone.childNodes).forEach(node => {
            if (node.nodeType === 1 && node.tagName === "TABLE") {
                reachedTable = true;
            }
            if (!reachedTable) {
                headerContent.appendChild(node.cloneNode(true));
            }
        });

        // --- Add logo to top-right of headerContent ---
        const logoWrapper = document.createElement("div");
        logoWrapper.style.display = "flex";
        logoWrapper.style.justifyContent = "space-between";
        logoWrapper.style.alignItems = "center";

        const spacer = document.createElement("div");
        const logo = document.createElement("img");
        logo.src = "https://search.studyinturkiye.com/wp-content/uploads/2025/02/image-1-1-e1738931290741.png";
        logo.style.maxWidth = "120px";
        logo.style.marginBottom = "20px";

        logoWrapper.appendChild(spacer);
        logoWrapper.appendChild(logo);
        headerContent.insertBefore(logoWrapper, headerContent.firstChild);

        // Create wrapper for all pages
        const wrapper = document.createElement("div");

        let i = 0;
        let pageIndex = 0;

        while (i < rows.length) {
            const newTable = document.createElement("table");
            newTable.style.width = "100%";
            newTable.style.borderCollapse = "collapse";
            if (thead) newTable.appendChild(thead.cloneNode(true));

            const tbody = document.createElement("tbody");

            // Determine how many rows this page should have
            const rowsPerPage = pageIndex === 0 ? 4 : 8;

            for (let j = i; j < i + rowsPerPage && j < rows.length; j++) {
                tbody.appendChild(rows[j].cloneNode(true));
            }

            newTable.appendChild(tbody);

            const pageDiv = document.createElement("div");
            pageDiv.style.pageBreakAfter = "always";

            if (pageIndex === 0) {
                pageDiv.appendChild(headerContent.cloneNode(true));
            }

            pageDiv.appendChild(newTable);
            wrapper.appendChild(pageDiv);

            i += rowsPerPage;
            pageIndex++;
        }

        // Final page: contact info + footer
        const finalPage = document.createElement("div");
        finalPage.style.padding = "20px";
        finalPage.innerHTML = `
            <div class="contact-info">
                <h4>Contact Information</h4>
                <p>
                    For more information about these programs or assistance with your application, please contact our support team.<br>
                    Email: support@studyinturkey.com<br>
                    Website: www.studyinturkey.com
                </p>
            </div>
            <div class="footer-popup" style="margin-top: 40px; text-align: center;">
                <p>© 2025 Study in Turkey. All rights reserved.</p>
                <p style="font-style: italic; font-size: 12px;">This document was generated for informational purposes only.</p>
            </div>
        `;
        wrapper.appendChild(finalPage);

        // Generate the PDF
        const options = {
            margin: 10,
            filename: 'Academic_Programs_Turkey.pdf',
            image: { type: 'png', quality: 1 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                letterRendering: true
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().from(wrapper).set(options).save().catch(function (error) {
            console.error("Error generating PDF:", error);
        });
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
