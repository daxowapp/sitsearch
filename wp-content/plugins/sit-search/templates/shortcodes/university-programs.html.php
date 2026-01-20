<?php
// Session should be started in App.php init hook, not here
// Removed session_start() to prevent "headers already sent" warning

$degree_term = get_term($degreeid, 'sit-degree');
$country_term = get_term($countryid, 'sit-country');
$speciality_term = get_term($specialityid, 'sit-speciality');

// Get language filter information
$language_filter = '';
if (!empty($_GET['language'])) {
    $language_term = get_term($_GET['language'], 'sit-language');
    if ($language_term && !is_wp_error($language_term)) {
        $language_filter = str_replace('%', ' ', $language_term->name) . ' ';
    }
}

// Get duration filter information
$duration_filter = '';
if (!empty($_GET['duration'])) {
    $duration_filter = $_GET['duration'] . ' ';
}

// Get scholarship filter information
$scholarship_filter = '';
if (!empty($_GET['isScholarShip']) && $_GET['isScholarShip'] == 'Yes') {
    $scholarship_filter = 'Scholarship ';
}

if (!empty($speciality_term) && !empty($degree_term) && !empty($country_term)) {
    $heading = $degree_term->name . ' ' . $speciality_term->name . ' Courses In ' . $country_term->name;
} else {
    $heading = "Search For Course";
}

// Configure which filters to show for this page (University-specific results)
$filter_config = [
    'degree' => false,     // Hide degree filter
    'duration' => true,    // Show duration filter
    'language' => true,    // Show language filter
    'price' => false,      // Hide price filter
    'university' => false, // Hide university filter (already filtered by university)
    'scholarship' => true  // Show scholarship filter
];

// Prepare filter data
$filter_data = [
    'degrees' => [],
    'universities' => []
];

// Set page-specific variables
$results_count = isset($query) ? $query->found_posts : 0;
$search_value = isset($_GET['search']) ? $_GET['search'] : '';
?>

<style>
/* Main Layout Styles */
.filter-results-container {
    display: flex;
    gap: 24px;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.filter-sidebar {
    width: 320px;
    min-width: 320px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 0;
    height: calc(100vh - 40px);
    position: sticky;
    top: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.filter-sidebar-content {
    padding: 24px;
    flex: 1;
    overflow-y: auto;
}

.results-main-content {
    flex: 1;
    min-width: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .filter-results-container {
        flex-direction: column;
        padding: 16px;
    }
    
    .filter-sidebar {
        width: 100%;
        min-width: auto;
        position: static;
        height: auto;
        max-height: 400px;
        margin-bottom: 20px;
    }
    
    .filter-sidebar.mobile-hidden {
        display: none;
    }
    
    .mobile-filter-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: #E20A17;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }
    
    .mobile-filter-toggle:hover {
        background: #B8080F;
    }
}

@media (min-width: 769px) {
    .mobile-filter-toggle {
        display: none;
    }
}

/* Sidebar Header */
.filter-sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e9ecef;
}

.filter-sidebar-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.clear-all-filters {
    background: none;
    border: none;
    color: #E20A17;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background 0.2s;
}

.clear-all-filters:hover {
    background: #f8f9fa;
}

/* Filter Sections */
.filter-section {
    margin-bottom: 32px;
}

.filter-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.filter-checkbox-label:hover {
    border-color: #E20A17;
    background: #fef7f7;
}

.filter-checkbox-label.active {
    border-color: #E20A17;
    background: linear-gradient(135deg, #fef7f7 0%, #fdf2f2 100%);
    box-shadow: 0 2px 4px rgba(226, 10, 23, 0.1);
}

.filter-checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #E20A17;
    cursor: pointer;
}

.filter-checkbox-text {
    font-size: 14px;
    color: #495057;
    font-weight: 500;
    flex: 1;
}

.filter-checkbox-label.active .filter-checkbox-text {
    color: #E20A17;
    font-weight: 600;
}

/* Filter Button Groups */
.filter-button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.filter-button {
    padding: 8px 16px;
    border: 1px solid #e9ecef;
    border-radius: 20px;
    background: white;
    color: #495057;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-button:hover {
    border-color: #E20A17;
    background: #fef7f7;
}

.filter-button.active {
    background: #E20A17;
    color: white;
    border-color: #E20A17;
}

/* View Toggle Styles */
.view-toggle {
    display: flex;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    margin-right: 12px;
}

.view-toggle button {
    padding: 10px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
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

.view-toggle svg {
    flex-shrink: 0;
}
</style>

<div class="header-container">
    <div class="header-title">
        <div class="filter-header">
            <h4><?= $language_filter . $duration_filter . $scholarship_filter ?>Programs in <?= $uni_title ?></h4>
        </div>
    </div>
    <div class="header-info">
        <span class="courses-found"><?= $query->found_posts ?> courses found</span>
        <div class="search-by-name-campus">
            <input type="text" id="search-university" value="<?= $_GET['search'] ?>" placeholder="Search by name..." />
            <button>Go</button>
        </div>
        <div class="header-actions">
            <!-- Mobile Filter Toggle -->
            <button class="mobile-filter-toggle" onclick="toggleMobileFilters()">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.7077 9.00023H6.41185M2.77768 9.00023H1.29102M2.77768 9.00023C2.77768 8.51842 2.96908 8.05635 3.30977 7.71566C3.65046 7.37497 4.11254 7.18357 4.59435 7.18357C5.07616 7.18357 5.53824 7.37497 5.87893 7.71566C6.21962 8.05635 6.41102 8.51842 6.41102 9.00023C6.41102 9.48204 6.21962 9.94412 5.87893 10.2848C5.53824 10.6255 5.07616 10.8169 4.59435 10.8169C4.11254 10.8169 3.65046 10.6255 3.30977 10.2848C2.96908 9.94412 2.77768 9.48204 2.77768 9.00023ZM16.7077 14.5061H11.9177M11.9177 14.5061C11.9177 14.988 11.7258 15.4506 11.3851 15.7914C11.0443 16.1321 10.5821 16.3236 10.1002 16.3236C9.61837 16.3236 9.1563 16.1313 8.81561 15.7906C8.47491 15.45 8.28352 14.9879 8.28352 14.5061M11.9177 14.5061C11.9177 14.0241 11.7258 13.5624 11.3851 13.2216C11.0443 12.8808 10.5821 12.6894 10.1002 12.6894C9.61837 12.6894 9.1563 12.8808 8.81561 13.2215C8.47491 13.5622 8.28352 14.0243 8.28352 14.5061M8.28352 14.5061H1.29102M16.7077 3.4944H14.1202M10.486 3.4944H1.29102M10.486 3.4944C10.486 3.01259 10.6774 2.55051 11.0181 2.20982C11.3588 1.86913 11.8209 1.67773 12.3027 1.67773C12.5413 1.67773 12.7775 1.72472 12.9979 1.81602C13.2183 1.90732 13.4186 2.04113 13.5873 2.20982C13.756 2.37852 13.8898 2.57878 13.9811 2.79919C14.0724 3.0196 14.1193 3.25583 14.1193 3.4944C14.1193 3.73297 14.0724 3.9692 13.9811 4.18961C13.8898 4.41002 13.756 4.61028 13.5873 4.77898C13.4186 4.94767 13.2183 5.08149 12.9979 5.17278C12.7775 5.26408 12.5413 5.31107 12.3027 5.31107C11.8209 5.31107 11.3588 5.11967 11.0181 4.77898C10.6774 4.43829 10.486 3.97621 10.486 3.4944Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
                </svg>
                Filters
            </button>
            
            <!-- View Toggle -->
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Grid
                </button>
                <button class="view-btn" data-view="list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    List
                </button>
            </div>

            <select class="sort-dropdown">
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "newest") {
                        echo "selected";
                    }
                } ?> value="newest">Sort by Newest
                </option>
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "fee_low") {
                        echo "selected";
                    }
                } ?> value="fee_low">Sort by Tuition Fee Low
                </option>
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "fee_high") {
                        echo "selected";
                    }
                } ?> value="fee_high">Sort by Tuition Fee High
                </option>
                <option <?php if (isset($_GET['sort'])) {
                    if ($_GET['sort'] == "popular") {
                        echo "selected";
                    }
                } ?> value="popular">Sort by Popular
                </option>
            </select>

            <button onclick="openExportPopup()" id="openExportPopup" class="export-btn" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                    <path d="M10 9H8"></path>
                    <path d="M16 13H8"></path>
                    <path d="M16 17H8"></path>
                </svg> 
                Export PDF
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

            <!-- Duration Filter -->
            <div class="filter-section">
                <h4 class="filter-section-title">⏱️ Duration</h4>
                <div class="filter-button-group">
                    <?php
                    $current_duration = isset($_GET['duration']) ? $_GET['duration'] : '';
                    $durations = ['1 year', '2 years', '3 years', '4 years', '4+ years'];
                    
                    foreach ($durations as $duration) {
                        $active_class = ($current_duration == $duration) ? 'active' : '';
                        echo '<button class="filter-button ' . $active_class . '" data-filter="duration" data-value="' . esc_attr($duration) . '">' . esc_html($duration) . '</button>';
                    }
                    ?>
                </div>
            </div>

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
                    $current_language = isset($_GET['language']) ? $_GET['language'] : '';
                    
                    foreach ($languages as $language) {
                        $language_name = str_replace('%', ' ', $language->name);
                        if (in_array($language_name, $allowed_languages)) {
                            $is_checked = ($current_language == $language->term_id) ? 'checked' : '';
                            $active_class = ($current_language == $language->term_id) ? 'active' : '';
                            echo '<label class="filter-checkbox-label ' . $active_class . '">';
                            echo '<input type="checkbox" class="language-checkbox" value="' . esc_attr($language->term_id) . '" ' . $is_checked . '>';
                            echo '<span class="filter-checkbox-text">' . esc_html($language_name) . '</span>';
                            echo '</label>';
                        }
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
        <div class="single-campus-content">
    <!-- GRID VIEW: Default view -->
    <div class="all-faculties-program university-programs" id="programsGridContainer">
        <?php
        foreach ($programs as $university) {
            \SIT\Search\Services\Template::render('shortcodes/program-box-uni', ['program' => $university]);
        }
        ?>
    </div>
    
    <!-- LIST VIEW: Compact mobile-optimized view -->
    <div class="all-faculties-program-list" id="programsListContainer" style="display: none;">
        <?php
        foreach ($programs as $university) {
            // Use the same program data structure as your grid view
            $program = $university;
            ?>
            <div class="program-list-item">
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
                            <?php if (!empty($program['discounted_fee'])): ?>
                                <span class="program-list-original-fee"><?php echo $program['fee']; ?> USD</span>
                                <span class="program-list-discounted-fee"><?php echo $program['discounted_fee']; ?> USD</span>
                            <?php else: ?>
                                <span class="program-list-current-fee"><?php echo $program['fee']; ?> USD</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="program-list-actions">
                            <?php 
                            // Simple and safe approach
                            $program_link = isset($program['link']) ? $program['link'] : '#';
                            
                            // Apply URL logic
                            $uni_id = $program['uni_id'] ?? '';
                            if ($uni_id) {
                                $apply_url = \SIT\Search\Services\URLHelper::apply($uni_id);
                            }
                            ?>
                            <a href="<?php echo esc_url($program_link); ?>" class="program-list-btn program-list-btn-primary">View Details</a>
                            <a href="<?php echo esc_url($apply_url); ?>" class="program-list-btn program-list-btn-outline" target="_blank">Apply</a>
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
        'total' => $query->max_num_pages,
        'prev_text' => '<img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-left-975UQXVKZF.svg" alt="Previous">',
        'next_text' => '<img src="https://search.studyinturkiye.com/wp-content/uploads/2025/03/reshot-icon-arrow-chevron-right-WDGHUKQ634.svg" alt="Next">',
    ));

    if ($paginate_links) {
        echo '<div class="pagination">' . $paginate_links . '</div>';
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
                <p>Total Programs:<?= count($pdf_program); ?></p>
            </div>
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
            ?>
            <!-- Add more rows as needed -->
            </tbody>
        </table>

        <h3>Program Details</h3>
        <?php
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
                <p>Total Programs:<?= count($pdf_program); ?></p>
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

        // Mobile filter toggle functionality
        function toggleMobileFilters() {
            const sidebar = document.getElementById('filterSidebar');
            sidebar.classList.toggle('mobile-hidden');
        }
    </script>
<style>
/* View Toggle Styles - Same as other pages */
.view-toggle {
    display: flex;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
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
// View toggle functionality for university-programs page
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const gridContainer = document.getElementById('programsGridContainer');
    const listContainer = document.getElementById('programsListContainer');
    
    if (!gridContainer || !listContainer) {
        return;
    }
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const currentView = this.dataset.view;
            
            if (currentView === 'grid') {
                gridContainer.style.display = '';
                listContainer.style.display = 'none';
            } else if (currentView === 'list') {
                gridContainer.style.display = 'none';
                listContainer.style.display = 'flex';
            }
            
            localStorage.setItem('programView', currentView);
        });
    });
    
    // Restore saved view preference
    const savedView = localStorage.getItem('programView');
    if (savedView && savedView === 'list') {
        const listButton = document.querySelector('[data-view="list"]');
        if (listButton) {
            listButton.click();
        }
    }
});

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    // View Toggle Functionality
    const viewButtons = document.querySelectorAll('.view-btn');
    const gridContainer = document.getElementById('programsGridContainer');
    const listContainer = document.getElementById('programsListContainer');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update button states
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Toggle containers
            if (view === 'grid') {
                if (gridContainer) gridContainer.style.display = 'block';
                if (listContainer) listContainer.style.display = 'none';
            } else if (view === 'list') {
                if (gridContainer) gridContainer.style.display = 'none';
                if (listContainer) listContainer.style.display = 'block';
            }
            
            // Save preference
            localStorage.setItem('programView', view);
        });
    });
    
    // Restore saved view preference
    const savedView = localStorage.getItem('programView');
    if (savedView && savedView === 'list') {
        const listButton = document.querySelector('[data-view="list"]');
        if (listButton) {
            listButton.click();
        }
    }
    
    // Filter Button Interactions
    const filterButtons = document.querySelectorAll('.filter-button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            const filterValue = this.getAttribute('data-value');
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Update URL parameters
            const url = new URL(window.location);
            if (this.classList.contains('active')) {
                url.searchParams.set(filterType === 'scholarship' ? 'isScholarShip' : filterType, filterValue);
            } else {
                url.searchParams.delete(filterType === 'scholarship' ? 'isScholarShip' : filterType);
            }
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    
    // Checkbox Filter Interactions
    const checkboxes = document.querySelectorAll('.filter-checkbox-label input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('.filter-checkbox-label');
            const filterType = this.className.replace('-checkbox', '');
            const filterValue = this.value;
            
            // Update label state
            if (this.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
            
            // Update URL parameters
            const url = new URL(window.location);
            const paramName = filterType === 'language' ? 'language' : filterType;
            
            if (this.checked) {
                url.searchParams.set(paramName, filterValue);
            } else {
                url.searchParams.delete(paramName);
            }
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
    
    // Clear All Filters
    const clearAllButton = document.querySelector('.clear-all-filters');
    if (clearAllButton) {
        clearAllButton.addEventListener('click', function() {
            const url = new URL(window.location);
            
            // Remove all filter parameters
            const filterParams = ['duration', 'language', 'isScholarShip'];
            filterParams.forEach(param => {
                url.searchParams.delete(param);
            });
            
            window.location.href = url.toString();
        });
    }
});

// Mobile filter toggle functionality
function toggleMobileFilters() {
    const sidebar = document.getElementById('filterSidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-hidden');
    }
}
</script>