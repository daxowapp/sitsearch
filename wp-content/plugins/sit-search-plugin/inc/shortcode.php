<?php
class UPD_Shortcode {
    public function __construct() {
        add_shortcode('university_programs', [$this, 'render_programs']);
        add_shortcode('program_search', [$this, 'program_search']);
        add_shortcode('university_search', [$this, 'university_search']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('save_post', [$this, 'fetch_and_insert_programs_to_custom_table'], 10, 3);
        add_action('wp_ajax_load_universities', [$this, 'ajax_load_universities']);
        add_action('wp_ajax_nopriv_load_universities', [$this, 'ajax_load_universities']);
        add_action('wp_ajax_get_filters', [$this, 'ajax_get_filters']);
        add_action('wp_ajax_nopriv_get_filters', [$this, 'ajax_get_filters']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);

        $css_path = plugin_dir_path(__FILE__) . '../assets/style.css';
        $js_path  = plugin_dir_path(__FILE__) . '../assets/script.js';
        wp_enqueue_style('upd-styles', plugin_dir_url(__FILE__) . '../assets/style.css', [], file_exists($css_path) ? filemtime($css_path) : '1.0');
        wp_enqueue_script('upd-scripts', plugin_dir_url(__FILE__) . '../assets/script.js', ['jquery'], file_exists($js_path) ? filemtime($js_path) : '1.0', true);
        wp_localize_script('upd-scripts', 'upd_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('program_search_nonce')
        ]);
    }

    public function fetch_and_insert_programs_to_custom_table($post_id, $post, $update) {
        global $wpdb;
       
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type !== 'universities') return;

        $zoho_id = get_field('university_zoho_id', $post_id);
        if (!$zoho_id) return;
        
        $api_url = 'https://search.studyinturkiye.com/wp-json/sit-search/v1/programs?zoho_university_id=' . $zoho_id;
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) return;

        $body = wp_remote_retrieve_body($response);
        $programs = json_decode($body, true);

        if (empty($programs) || !is_array($programs)) return;

        $table_name = 'universities_programs';

        $wpdb->delete($table_name, ['zoho_university_id' => $zoho_id]);

        foreach ($programs['Programs'] as $program) {
            $wpdb->insert($table_name, [
                'id'                 => $program['id'],
                'url'                => $program['url'],
                'uni_title'         => $program['university_title'],
                'zoho_university_id'=> $program['zoho_university_id'],
                'program_title'     => $program['title'],
                'content'           => $program['content'],
                'duration'          => $program['duration'],
                'fee'               => $program['fee'],
                'discounted_fee'    => $program['discounted_fee'],
                'Advanced_Discount' => $program['Advanced_Discount'],
                'level'             => $program['level'],
                'language'          => $program['language'],
                'speciality'        => $program['speciality'],
            ]);
        }
        $unitable_name = 'universities_zoho';

        $wpdb->delete($unitable_name, ['zoho_university_id' => $zoho_id]);

        $wpdb->insert($unitable_name, [
            'uni_url'                => $programs['University']['url'],
            'university_title'         => $programs['University']['title'],
            'zoho_university_id'=> $programs['University']['zoho_university_id'],
            'country'     => $programs['University']['country'],
            'city'           => $programs['University']['city'],
            'description'          => $programs['University']['uni_description'],
            'uni_image'               => $programs['University']['image_url'],
            'uni_logo'    => $programs['University']['uni_logo'],
            'type'     => $programs['University']['type'],
        ]);
    }

    public function ajax_get_filters() {
        global $wpdb;
        $table = 'universities_zoho';

        // Use transient cache to avoid 3 DISTINCT queries per page load
        $cache_key = 'upd_filter_options';
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            wp_send_json($cached);
        }

        $countries = $wpdb->get_col("SELECT DISTINCT country FROM $table ORDER BY country");
        $cities = $wpdb->get_col("SELECT DISTINCT city FROM $table ORDER BY city");
        $type = $wpdb->get_col("SELECT DISTINCT type FROM $table ORDER BY type");

        $country_opts = '';
        foreach ($countries as $c) {
            $country_opts .= "<option value='" . esc_attr($c) . "'>" . esc_html($c) . "</option>";
        }

        $city_opts = '';
        $cities = array_filter($cities);
        foreach ($cities as $c) {
            $city_opts .= "<option value='" . esc_attr($c) . "'>" . esc_html($c) . "</option>";
        }

        $type_opts = '';
        $type = array_filter($type);
        foreach ($type as $c) {
            $type_opts .= "<option value='" . esc_attr($c) . "'>" . esc_html($c) . "</option>";
        }

        $result = ['countries' => $country_opts, 'cities' => $city_opts, 'type' => $type_opts];
        set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
        wp_send_json($result);
    }

    public function ajax_load_universities() {
        global $wpdb;
        $table = 'universities_zoho';

        $keyword = sanitize_text_field($_POST['keyword'] ?? '');
        $country = sanitize_text_field($_POST['country'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = 6;
        $offset = ($page - 1) * $per_page;

        $where = "WHERE 1=1";
        if ($keyword) $where .= $wpdb->prepare(" AND university_title LIKE %s", '%' . $wpdb->esc_like($keyword) . '%');
        if ($country) $where .= $wpdb->prepare(" AND country = %s", $country);
        if ($city) $where .= $wpdb->prepare(" AND city = %s", $city);
        if ($type) $where .= $wpdb->prepare(" AND type = %s", $type);

        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
        $universities = $wpdb->get_results("SELECT * FROM $table $where ORDER BY university_title ASC LIMIT $offset, $per_page");

        // Batch-fetch WordPress post IDs for all universities to eliminate N+1 queries
        $zoho_ids = wp_list_pluck($universities, 'zoho_university_id');
        $zoho_to_post = [];
        if (!empty($zoho_ids)) {
            $batch_posts = get_posts([
                'post_type'   => 'universities',
                'meta_key'    => 'university_zoho_id',
                'meta_value'  => $zoho_ids,
                'meta_compare'=> 'IN',
                'numberposts' => count($zoho_ids),
                'fields'      => 'ids',
            ]);
            foreach ($batch_posts as $pid) {
                $zid = get_post_meta($pid, 'university_zoho_id', true);
                if ($zid) $zoho_to_post[$zid] = $pid;
            }
        }

        ob_start();
        foreach ($universities as $u):
            $post_id = $zoho_to_post[$u->zoho_university_id] ?? 0;
            ?>
            <div class="university-card" data-aos="fade-up">
                <div class="university-card-header">
                    <div class="status-indicator">✓ Verified</div>
                    <img src="https://search.studyinturkiye.com<?= esc_url($u->uni_image) ?>" 
                         alt="<?= esc_attr($u->university_title) ?>" 
                         class="university-image"
                         loading="lazy" />
 
                </div>
                
                <div class="university-card-body">
                    <div class="university-header-info">
                        <h3 class="university-title"><?= esc_html($u->university_title) ?></h3>
                    </div>
                    </div>
                    
                    <p class="university-location">
                        <svg class="location-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?= esc_html($u->city) ?>, <?= esc_html($u->country) ?>
                    </p>
                    
                    <p class="university-description">
                        <?= esc_html(wp_trim_words($u->description ?: 'A leading institution dedicated to excellence in education, research, and innovation, preparing students for successful careers in their chosen fields.', 20)) ?>
                    </p>

                    <div class="university-badges">
                        <?php if ($u->type): ?>
                        <span class="type-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <?= esc_html($u->type) ?>
                        </span>
                        <?php endif; ?>
                        <span class="accreditation-badge">YÖK Accredited</span>
                    </div>
                </div>
                
                <div class="university-card-footer">
                    <a href="<?= esc_url($u->uni_url) ?>" target="_blank" class="btn btn-primary">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22,4 12,14.01 9,11.01"></polyline>
                        </svg>
                        Apply Now
                    </a>
                    <a href="<?= $post_id ? get_permalink($post_id) : '#' ?>" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Learn More
                    </a>
                </div>
            </div>
        <?php endforeach;

        $html = ob_get_clean();

        // Calculate pagination info
        $pages = ceil($total / $per_page);
        $pagination = '';

        if ($pages > 1) {
            $range = 1;
            $show_dots = false;

            for ($i = 1; $i <= $pages; $i++) {
                if (
                    $i == 1 || $i == $pages ||
                    ($i >= $page - $range && $i <= $page + $range)
                ) {
                    $active = ($i == $page) ? " class='active'" : '';
                    $pagination .= "<span data-page='$i'$active>$i</span>";
                    $show_dots = true;
                } elseif ($show_dots) {
                    $pagination .= "<span class='dots'>...</span>";
                    $show_dots = false;
                }
            }
        }

        // Send response with total count for proper results display
        wp_send_json([
            'html' => $html, 
            'pagination' => $pagination,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $per_page
        ]);
    }

    public function university_search(){
        ob_start(); ?>
        <div class="modern-university-search">
            <!-- Mobile Filter Toggle -->
            <button class="mobile-filter-toggle" id="mobile-filter-toggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            </button>

            <div class="search-container">
                <!-- Filters Sidebar -->
                <aside class="filters-sidebar" id="filters-sidebar">
                    <div class="filters-header">
                        <h3>Filter Universities</h3>
                        <button class="close-filters" id="close-filters">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-country" class="filter-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="16.24,7.76 14.12,14.12 7.76,16.24 9.88,9.88 16.24,7.76"></polygon>
                            </svg>
                            Country
                        </label>
                        <select id="filter-country" class="modern-select">
                            <option value="">All Countries</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-city" class="filter-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                            </svg>
                            City
                        </label>
                        <select id="filter-city" class="modern-select">
                            <option value="">All Cities</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-type" class="filter-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            Type
                        </label>
                        <select id="filter-type" class="modern-select">
                            <option value="">All Types</option>
                        </select>
                    </div>

                    <button class="reset-btn" id="reset-filters">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="1,4 1,10 7,10"></polyline>
                            <path d="M3.51,15a9,9,0,0,0,2.13,3.09,9,9,0,0,0,13.72,0A9,9,0,0,0,21.49,15"></path>
                            <polyline points="23,20 23,14 17,14"></polyline>
                            <path d="M20.49,9A9,9,0,0,0,18.36,5.91,9,9,0,0,0,4.64,5.91,9,9,0,0,0,2.51,9"></path>
                        </svg>
                        Reset Filters
                    </button>
                </aside>

                <!-- Main Content -->
                <main class="search-content">
                    <!-- Search Header -->
                    <div class="search-header">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <input type="text" id="search-keyword" placeholder="Search universities..." />
                            <button class="clear-search" id="clear-search" style="display: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Results Info -->
                    <div class="results-info">
                        <div class="results-count" id="results-count">Loading universities...</div>
                        <div class="view-toggle">
                            <button class="view-btn active" data-view="grid" title="Grid View">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                            </button>
                            <button class="view-btn" data-view="list" title="List View">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div class="loading-state" id="loading-state" style="display: none;">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                        </div>
                        <p>Finding universities...</p>
                        
                        <!-- Skeleton Cards -->
                        <div class="skeleton-grid">
                            <?php for($i = 0; $i < 6; $i++): ?>
                            <div class="skeleton-card">
                                <div class="skeleton-image"></div>
                                <div class="skeleton-content">
                                    <div class="skeleton-title"></div>
                                    <div class="skeleton-subtitle"></div>
                                    <div class="skeleton-text"></div>
                                    <div class="skeleton-buttons">
                                        <div class="skeleton-button"></div>
                                        <div class="skeleton-button"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Universities Grid -->
                    <div class="universities-grid" id="university-cards" style="display: grid !important; opacity: 1 !important;"></div>

                    <!-- Pagination -->
                    <div class="pagination-wrapper">
                        <div class="pagination" id="pagination"></div>
                    </div>
                </main>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('University search script loaded');
            console.log('upd_ajax object:', typeof upd_ajax !== 'undefined' ? upd_ajax : 'NOT FOUND');

            const mobileFilterToggle = document.getElementById('mobile-filter-toggle');
            const filtersSidebar = document.getElementById('filters-sidebar');
            const closeFilters = document.getElementById('close-filters');
            const searchInput = document.getElementById('search-keyword');
            const clearSearch = document.getElementById('clear-search');
            const filterCountry = document.getElementById('filter-country');
            const filterCity = document.getElementById('filter-city');
            const filterType = document.getElementById('filter-type');
            const resetFilters = document.getElementById('reset-filters');
            const universitiesGrid = document.getElementById('university-cards');
            const loadingState = document.getElementById('loading-state');
            const resultsCount = document.getElementById('results-count');
            const pagination = document.getElementById('pagination');

            let currentPage = 1;
            let isLoading = false;

            // Mobile filter toggle
            if (mobileFilterToggle) {
                mobileFilterToggle.addEventListener('click', function() {
                    filtersSidebar.classList.add('open');
                    document.body.style.overflow = 'hidden';
                });
            }

            if (closeFilters) {
                closeFilters.addEventListener('click', function() {
                    filtersSidebar.classList.remove('open');
                    document.body.style.overflow = '';
                });
            }

            if (filtersSidebar) {
                filtersSidebar.addEventListener('click', function(e) {
                    if (e.target === filtersSidebar) {
                        filtersSidebar.classList.remove('open');
                        document.body.style.overflow = '';
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    if (this.value.length > 0) {
                        clearSearch.style.display = 'block';
                    } else {
                        clearSearch.style.display = 'none';
                    }
                    
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        currentPage = 1;
                        loadUniversities();
                    }, 500);
                });
            }

            if (clearSearch) {
                clearSearch.addEventListener('click', function() {
                    searchInput.value = '';
                    clearSearch.style.display = 'none';
                    searchInput.focus();
                    currentPage = 1;
                    loadUniversities();
                });
            }

            [filterCountry, filterCity, filterType].forEach(filter => {
                if (filter) {
                    filter.addEventListener('change', function() {
                        currentPage = 1;
                        loadUniversities();
                    });
                }
            });

            if (resetFilters) {
                resetFilters.addEventListener('click', function() {
                    searchInput.value = '';
                    clearSearch.style.display = 'none';
                    filterCountry.value = '';
                    filterCity.value = '';
                    filterType.value = '';
                    currentPage = 1;
                    loadUniversities();
                });
            }

            function showPageTransition() {
                const overlay = document.createElement('div');
                overlay.className = 'page-transition-overlay';
                overlay.innerHTML = `
                    <div class="transition-content">
                        <div class="transition-spinner"></div>
                        <p>Loading universities...</p>
                    </div>
                `;
                document.body.appendChild(overlay);

                const cards = universitiesGrid.querySelectorAll('.university-card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.transform = 'translateY(-20px)';
                        card.style.opacity = '0';
                    }, index * 50);
                });

                return overlay;
            }

            function hidePageTransition(overlay) {
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => {
                        if (overlay.parentNode) {
                            overlay.parentNode.removeChild(overlay);
                        }
                    }, 300);
                }
            }

            function loadUniversities() {
                if (isLoading) return;
                
                isLoading = true;
                const overlay = showPageTransition();
                
                if (resultsCount) {
                    resultsCount.textContent = 'Loading...';
                }

                const requestData = {
                    action: 'load_universities',
                    keyword: searchInput?.value || '',
                    country: filterCountry?.value || '',
                    city: filterCity?.value || '',
                    type: filterType?.value || '',
                    page: currentPage
                };

                if (typeof jQuery === 'undefined') {
                    console.error('jQuery is not loaded!');
                    hidePageTransition(overlay);
                    if (resultsCount) {
                        resultsCount.textContent = 'Error: jQuery not loaded';
                    }
                    isLoading = false;
                    return;
                }

                if (typeof upd_ajax === 'undefined' || !upd_ajax.ajax_url) {
                    console.error('upd_ajax object not found!');
                    hidePageTransition(overlay);
                    if (resultsCount) {
                        resultsCount.textContent = 'Error: AJAX configuration missing';
                    }
                    isLoading = false;
                    return;
                }

                jQuery.ajax({
                    url: upd_ajax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: requestData,
                    success: function(data) {
                        console.log('AJAX success:', data);
                        
                        hidePageTransition(overlay);
                        
                        if (universitiesGrid) {
                            universitiesGrid.innerHTML = data.html || '';
                        }
                        if (pagination) {
                            pagination.innerHTML = data.pagination || '';
                        }
                        
                        const total = data.total || 0;
                        const currentResults = universitiesGrid ? universitiesGrid.querySelectorAll('.university-card').length : 0;
                        
                        let resultsText = '';
                        if (total === 0) {
                            resultsText = 'No universities found';
                        } else if (total === 1) {
                            resultsText = '1 university found';
                        } else {
                            resultsText = `${total} universities found`;
                            if (currentResults < total) {
                                resultsText += ` (showing ${currentResults})`;
                            }
                        }
                        
                        if (resultsCount) {
                            resultsCount.textContent = resultsText;
                        }

                        if (universitiesGrid) {
                            const newCards = universitiesGrid.querySelectorAll('.university-card');
                            newCards.forEach((card, index) => {
                                card.style.opacity = '0';
                                card.style.transform = 'translateY(30px)';
                                setTimeout(() => {
                                    card.style.transition = 'all 0.4s ease';
                                    card.style.opacity = '1';
                                    card.style.transform = 'translateY(0)';
                                }, index * 100);
                            });
                        }

                        setupPaginationListeners();
                        isLoading = false;
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', {xhr, status, error});
                        console.error('Response text:', xhr.responseText);
                        
                        hidePageTransition(overlay);
                        
                        if (resultsCount) {
                            resultsCount.textContent = 'Error loading universities';
                        }
                        if (universitiesGrid) {
                            universitiesGrid.innerHTML = '<p style="text-align: center; padding: 2rem; color: #ef4444;">Error loading universities. Please try again.</p>';
                        }
                        isLoading = false;
                    }
                });
            }

            function setupPaginationListeners() {
                if (!pagination) return;
                
                const paginationBtns = pagination.querySelectorAll('span[data-page]');
                paginationBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const page = parseInt(this.dataset.page);
                        if (page && page !== currentPage) {
                            currentPage = page;
                            loadUniversities();
                            
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            }

            function loadFilters() {
                if (typeof upd_ajax === 'undefined' || !upd_ajax.ajax_url) {
                    console.error('Cannot load filters: upd_ajax not available');
                    return;
                }

                jQuery.ajax({
                    url: upd_ajax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'get_filters'
                    },
                    success: function(data) {
                        if (filterCountry && data.countries) {
                            filterCountry.innerHTML = '<option value="">All Countries</option>' + data.countries;
                        }
                        if (filterCity && data.cities) {
                            filterCity.innerHTML = '<option value="">All Cities</option>' + data.cities;
                        }
                        if (filterType && data.type) {
                            filterType.innerHTML = '<option value="">All Types</option>' + data.type;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading filters:', {xhr, status, error});
                    }
                });
            }

            const viewBtns = document.querySelectorAll('.view-btn');
            viewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    viewBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const view = this.dataset.view;
                    if (view === 'list') {
                        universitiesGrid.classList.add('list-view');
                    } else {
                        universitiesGrid.classList.remove('list-view');
                    }
                });
            });

            setTimeout(() => {
                loadFilters();
                loadUniversities();

                if (loadingState) {
                    loadingState.style.display = 'none';
                }
            }, 500);
        });
        </script>

        <style>
        /* Ensure university cards remain visible when AJAX loads content */
        .universities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }


        /* Page Transition Animation Styles */
        .page-transition-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            animation: fadeInOverlay 0.3s ease forwards;
        }

        .transition-content {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            min-width: 200px;
        }

        .transition-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f8fafc;
            border-top: 4px solid #e30a17;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        .transition-content p {
            margin: 0;
            color: #64748b;
            font-weight: 500;
            font-size: 1rem;
        }

        @keyframes fadeInOverlay {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .universities-grid {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }
            
            .transition-content {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .transition-spinner {
                width: 40px;
                height: 40px;
                border-width: 3px;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }

    public function program_search(){
        ob_start(); ?>
        <div class="modern-program-search">
                            <div class="search-header">
                    <h2 class="search-title">Find Your Perfect Program</h2>
                    <p class="search-subtitle">Search through thousands of academic programs</p>
                </div>

            <div class="search-container">
                
                <div class="search-input-section">
                    <div class="search-input-wrapper">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="program-search-input" placeholder="Search by program name, fee, level, or language..." />
                        <button id="program-search-btn" class="search-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="search-suggestions">
                        <span class="suggestion-label">Try:</span>
                        <button class="suggestion-tag" data-search="Master Computer">Master Computer</button>
                        <button class="suggestion-tag" data-search="Software Engineering">Software Engineering</button>
                        <button class="suggestion-tag" data-search="Bachelor of Business ">Bachelor of Business </button>
                        <button class="suggestion-tag" data-search="Medicine English">Medicine English</button>
                    </div>
                </div>
            </div>

            <div class="results-section" id="results-section">
                <div class="loading-container" id="loading-container">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                    <p class="loading-text">Searching programs...</p>
                </div>

                <div class="results-header" id="results-header" style="display: none;">
                    <div class="results-info">
                        <h3 class="results-count" id="results-count">0 Programs Found    </h3>
                        <p class="results-subtitle">Showing the best matches for your search</p>
                    </div>
                    <div class="results-controls">
                        <div class="swiper-navigation">
                            <button class="swiper-button-prev">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="15,18 9,12 15,6"></polyline>
                                </svg>
                            </button>
                            <div class="swiper-pagination"></div>
                            <button class="swiper-button-next">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9,6 15,12 9,18"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="programs-slider-container" style="display: none;" id="programs-slider-container">
                    <div class="swiper program-slider">
                        <div class="swiper-wrapper" id="program-results">
                            <!-- AJAX content here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('program-search-input');
            const searchBtn = document.getElementById('program-search-btn');
            const suggestionTags = document.querySelectorAll('.suggestion-tag');
            const loadingContainer = document.getElementById('loading-container');
            const resultsHeader = document.getElementById('results-header');
            const sliderContainer = document.getElementById('programs-slider-container');
            const resultsSection = document.getElementById('results-section');
            const resultsCount = document.getElementById('results-count');
            const programResults = document.getElementById('program-results');

            suggestionTags.forEach(tag => {
                tag.addEventListener('click', function() {
                    const searchText = this.dataset.search;
                    searchInput.value = searchText;
                    performSearch();
                });
            });

            searchBtn.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            function createCard(p) {
                return `
                  <div class="program-card">
                    <h4><a href="${p.url}">${p.title}</a></h4>
                    <div class="program-info-grid">
                        <div class="program-info-item">
                            <span class="label">University:</span>
                            <span class="value">${p.university}</span>
                        </div>
                        <div class="program-info-item">
                            <span class="label">Language:</span>
                            <span class="value">${p.language}</span>
                        </div>
                        <div class="program-info-item">
                            <span class="label">Period:</span>
                            <span class="value">${p.period}</span>
                        </div>
                    </div>
                    <div class="program-fees-row">
                        <div class="fee-item">
                            <span class="fee-label">Tuition:</span>
                            <span class="fee-amount">${p.tuition}</span>
                        </div>
                        ${p.discounted ? `<div class="fee-item">
                            <span class="fee-label">Discounted:</span>
                            <span class="fee-amount discounted">${p.discounted}</span>
                        </div>` : ''}
                    </div>
                    <span class="program-level-badge">${p.level}</span>
                  </div>`;
              }

            function performSearch() {
                const query = searchInput.value.trim();
                if (query) {
                    showLoading();
                    
                    jQuery.ajax({
                        url: upd_ajax.ajax_url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'program_search_ajax',
                            nonce: upd_ajax.nonce,
                            keyword: query
                        },
                        success: function(response) {
                            hideLoading();
                            
                            if (response && response.length > 0) {
                                displayResults(response);
                            } else {
                                showNoResults();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', error);
                            hideLoading();
                            showNoResults();
                        }
                    });
                } else {
                    hideAll();
                }
            }

            function displayResults(programs) {
                resultsSection.style.display = 'block';
                resultsHeader.style.display = 'flex';
                sliderContainer.style.display = 'block';
                
                resultsCount.textContent = `${programs.length} Programs Found`;
                
                let slides = '';
                for (let i = 0; i < programs.length; i += 4) {
                    let group = programs.slice(i, i + 4).map(createCard).join('');
                    slides += `<div class="swiper-slide"><div class="program-grid">${group}</div></div>`;
                }
                
                programResults.innerHTML = slides;
                
                if (window.programSwiper) {
                    window.programSwiper.destroy(true, true);
                }
                
                window.programSwiper = new Swiper(".program-slider", {
                    slidesPerView: 1,
                    spaceBetween: 30,
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev"
                    }
                });
            }

            function showNoResults() {
                resultsSection.style.display = 'block';
                resultsHeader.style.display = 'flex';
                sliderContainer.style.display = 'none';
                resultsCount.textContent = '0 Programs Found';
                programResults.innerHTML = '';
            }

            function showLoading() {
                resultsSection.style.display = 'block';
                loadingContainer.style.display = 'block';
                resultsHeader.style.display = 'none';
                sliderContainer.style.display = 'none';
            }

            function hideLoading() {
                loadingContainer.style.display = 'none';
            }

            function hideAll() {
                resultsSection.style.display = 'none';
                loadingContainer.style.display = 'none';
                resultsHeader.style.display = 'none';
                sliderContainer.style.display = 'none';
            }

            hideAll();
        });
        </script>
        <?php
        return ob_get_clean();
    }

   
public function render_programs($atts) {
    ob_start();
    $post_id = get_the_ID();
    
    $zoho_id = get_post_meta($post_id, 'university_zoho_id', true);
    
    global $wpdb;
    $programs = [];
    
    if ($zoho_id) {
        $programs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM universities_programs WHERE zoho_university_id = %s ORDER BY level, program_title",
            $zoho_id
        ));
    }
    
    // Group programs by level
    $programs_by_level = [];
    foreach ($programs as $program) {
        $level = $program->level ?: 'Other';
        $programs_by_level[$level][] = $program;
    }
    
    $uid = 'pl_' . uniqid();
    ?>
    <style>
    .prog-list-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem 1rem;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .prog-list-wrap * { box-sizing: border-box; }

    /* Header */
    .prog-list-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .prog-list-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.25rem;
    }
    .prog-list-header h2 span { color: #e30a17; }
    .prog-list-header p {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }

    /* Search + Tabs bar */
    .prog-list-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1.25rem;
    }
    .prog-search-box {
        flex: 1;
        min-width: 220px;
        position: relative;
    }
    .prog-search-box svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .prog-search-box input {
        width: 100%;
        padding: 0.7rem 0.75rem 0.7rem 2.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        background: #fff;
        color: #1f2937;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .prog-search-box input:focus {
        border-color: #e30a17;
        box-shadow: 0 0 0 3px rgba(227,10,23,0.08);
    }
    .prog-search-box input::placeholder { color: #94a3b8; }

    .prog-tabs {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 0.3rem;
    }
    .prog-tab {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.82rem;
        color: #64748b;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
    }
    .prog-tab:hover { color: #e30a17; background: rgba(227,10,23,0.06); }
    .prog-tab.active {
        background: #e30a17;
        color: #fff;
        box-shadow: 0 2px 8px rgba(227,10,23,0.25);
    }

    .prog-results-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding: 0 0.25rem;
    }
    .prog-results-count {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }
    .prog-results-count strong { color: #1f2937; }

    /* List table */
    .prog-list-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .prog-list-table thead th {
        padding: 0.6rem 1rem;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #f1f5f9;
    }
    .prog-list-table thead th:last-child { text-align: right; }

    .prog-list-row {
        background: #fff;
        transition: all 0.25s ease;
    }
    .prog-list-row:hover {
        background: #fef2f2;
        transform: translateX(4px);
    }
    .prog-list-row td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #374151;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
    }
    .prog-list-row td:first-child {
        border-left: 1px solid #f1f5f9;
        border-radius: 10px 0 0 10px;
    }
    .prog-list-row td:last-child {
        border-right: 1px solid #f1f5f9;
        border-radius: 0 10px 10px 0;
        text-align: right;
    }
    .prog-list-row:hover td {
        border-color: rgba(227,10,23,0.15);
    }
    .prog-list-row:hover td:first-child {
        box-shadow: inset 4px 0 0 #e30a17;
    }

    .prog-name-cell a {
        color: #1f2937;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }
    .prog-name-cell a:hover { color: #e30a17; }

    .prog-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: rgba(227,10,23,0.1);
        color: #e30a17;
    }

    .prog-fee-cell {
        font-weight: 600;
        color: #059669;
    }
    .prog-fee-cell .fee-old {
        text-decoration: line-through;
        color: #94a3b8;
        font-weight: 400;
        font-size: 0.8rem;
        margin-right: 0.35rem;
    }
    .prog-fee-cell .fee-disc {
        color: #e30a17;
        font-weight: 700;
    }

    .prog-apply-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 1rem;
        background: #e30a17;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .prog-apply-link:hover {
        background: #b8000f;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(227,10,23,0.3);
        color: #fff;
        text-decoration: none;
    }

    .prog-empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }
    .prog-empty-state svg { margin-bottom: 0.75rem; opacity: 0.5; }
    .prog-empty-state p { margin: 0; font-size: 0.95rem; }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .prog-list-toolbar { flex-direction: column; }
        .prog-tabs { width: 100%; overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; }
        
        .prog-list-table, .prog-list-table thead, .prog-list-table tbody, .prog-list-table th, .prog-list-table td, .prog-list-table tr {
            display: block;
        }
        .prog-list-table thead { display: none; }
        .prog-list-row {
            margin-bottom: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.5rem;
        }
        .prog-list-row:hover { transform: none; }
        .prog-list-row td {
            padding: 0.4rem 0.75rem;
            border: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }
        .prog-list-row td:first-child {
            border-radius: 0;
            border-left: none;
            font-size: 0.95rem;
        }
        .prog-list-row td:last-child {
            border-radius: 0;
            border-right: none;
            text-align: left;
            justify-content: flex-start;
            padding-top: 0.5rem;
        }
        .prog-list-row:hover td:first-child { box-shadow: none; }
        .prog-list-row td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #94a3b8;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            min-width: 80px;
        }
        .prog-list-row td:first-child::before,
        .prog-list-row td:last-child::before { content: ''; min-width: 0; }
        .prog-apply-link { width: 100%; justify-content: center; }
    }
    </style>

    <div class="prog-list-wrap" id="<?= $uid ?>">
        <div class="prog-list-header">
            <h2>Academic <span>Programs</span></h2>
            <p>Explore our comprehensive range of academic offerings</p>
        </div>

        <?php if (!$zoho_id || empty($programs)): ?>
            <div class="prog-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <p><?php echo !$zoho_id ? 'No Zoho University ID found.' : 'Programs are currently being updated.'; ?></p>
            </div>
        <?php else: ?>
            <div class="prog-list-toolbar">
                <div class="prog-search-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="prog-search-<?= $uid ?>" placeholder="Search programs by name, language, speciality..." />
                </div>
                <div class="prog-tabs" id="prog-tabs-<?= $uid ?>">
                    <button class="prog-tab active" data-tab="all">All (<?= count($programs) ?>)</button>
                    <?php foreach ($programs_by_level as $level => $level_programs): ?>
                    <button class="prog-tab" data-tab="<?= esc_attr(strtolower($level)) ?>"><?= esc_html($level) ?> (<?= count($level_programs) ?>)</button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="prog-results-bar">
                <span class="prog-results-count" id="prog-count-<?= $uid ?>">Showing <strong><?= count($programs) ?></strong> programs</span>
            </div>

            <table class="prog-list-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Level</th>
                        <th>Duration</th>
                        <th>Language</th>
                        <th>Fee</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="prog-body-<?= $uid ?>">
                    <?php foreach ($programs as $program): ?>
                    <tr class="prog-list-row"
                        data-level="<?= esc_attr(strtolower($program->level ?: 'other')) ?>"
                        data-search="<?= esc_attr(strtolower($program->program_title . ' ' . $program->language . ' ' . $program->speciality . ' ' . $program->level)) ?>">
                        <td class="prog-name-cell" data-label="">
                            <a href="<?= esc_url($program->url) ?>" target="_blank"><?= esc_html($program->program_title) ?></a>
                        </td>
                        <td data-label="Level">
                            <span class="prog-badge"><?= esc_html($program->level ?: 'N/A') ?></span>
                        </td>
                        <td data-label="Duration"><?= esc_html($program->duration ? $program->duration . ' Years' : '—') ?></td>
                        <td data-label="Language"><?= esc_html($program->language ?: '—') ?></td>
                        <td class="prog-fee-cell" data-label="Fee">
                            <?php if ($program->discounted_fee && $program->discounted_fee != $program->fee): ?>
                                <span class="fee-old">$<?= esc_html($program->fee) ?></span>
                                <span class="fee-disc">$<?= esc_html($program->discounted_fee) ?></span>
                            <?php elseif ($program->fee): ?>
                                $<?= esc_html($program->fee) ?>
                            <?php else: ?>
                                <span style="color:#94a3b8">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="">
                            <a href="<?= esc_url($program->url) ?>" target="_blank" class="prog-apply-link">
                                Apply
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var uid = '<?= $uid ?>';
        var searchInput = document.getElementById('prog-search-' + uid);
        var tabsContainer = document.getElementById('prog-tabs-' + uid);
        var tbody = document.getElementById('prog-body-' + uid);
        var countEl = document.getElementById('prog-count-' + uid);
        if (!tbody) return;

        var allRows = Array.from(tbody.querySelectorAll('.prog-list-row'));
        var activeTab = 'all';

        function filterRows() {
            var query = (searchInput ? searchInput.value.toLowerCase().trim() : '');
            var visible = 0;
            allRows.forEach(function(row) {
                var matchesTab = (activeTab === 'all' || row.dataset.level === activeTab);
                var matchesSearch = (!query || row.dataset.search.indexOf(query) !== -1);
                if (matchesTab && matchesSearch) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (countEl) {
                countEl.innerHTML = 'Showing <strong>' + visible + '</strong> of ' + allRows.length + ' programs';
            }
        }

        // Tabs
        if (tabsContainer) {
            tabsContainer.addEventListener('click', function(e) {
                var btn = e.target.closest('.prog-tab');
                if (!btn) return;
                tabsContainer.querySelectorAll('.prog-tab').forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
                activeTab = btn.dataset.tab;
                filterRows();
            });
        }

        // Search with debounce
        if (searchInput) {
            var timer;
            searchInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(filterRows, 200);
            });
        }
    });
    </script>

    <?php
    return ob_get_clean();
}

}