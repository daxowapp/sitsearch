<?php
/**
 * Shared Results Header Template
 * Used by all result pages for consistent header experience
 * 
 * Expected variables:
 * - $heading: Page title/heading
 * - $results_count: Number of results found
 * - $search_value: Current search value
 * - $show_export: Whether to show export button (default: true)
 */

// Default values
$heading = isset($heading) ? $heading : 'Search Results';
$results_count = isset($results_count) ? $results_count : 0;
$search_value = isset($search_value) ? $search_value : (isset($_GET['search']) ? $_GET['search'] : '');
$show_export = isset($show_export) ? $show_export : true;
?>

<!-- Header with Search and Actions -->
<div class="header-container">
    <div class="header-title">
        <h1><?= esc_html($heading) ?></h1>
    </div>
    <div class="header-info">
        <span class="courses-found"><?= number_format($results_count) ?> <?= $results_count == 1 ? 'course' : 'courses' ?> found</span>
        <div class="search-by-name">
            <input type="text" id="search-university" value="<?= esc_attr($search_value) ?>" placeholder="Search by name..." />
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
                <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "newest") echo "selected"; ?> value="newest">Sort by Newest</option>
                <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "fee_low") echo "selected"; ?> value="fee_low">Sort by Tuition Fee Low</option>
                <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "fee_high") echo "selected"; ?> value="fee_high">Sort by Tuition Fee High</option>
                <option <?php if (isset($_GET['sort']) && $_GET['sort'] == "popular") echo "selected"; ?> value="popular">Sort by Popular</option>
            </select>

            <?php if ($show_export): ?>
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
            <?php endif; ?>
        </div>
    </div>
</div>
