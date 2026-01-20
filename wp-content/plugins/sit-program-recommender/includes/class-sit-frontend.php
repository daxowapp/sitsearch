<?php
/**
 * SIT Frontend
 * 
 * Handles the frontend shortcode, assets, and user interface.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SIT_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('sit_program_recommender', array($this, 'shortcode_callback'));
        add_action('wp_footer', array($this, 'add_inline_scripts'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        global $post;
        if (!$post || !has_shortcode($post->post_content, 'sit_program_recommender')) {
            return;
        }
        
        // Enqueue JavaScript (Clean version)
        wp_enqueue_script(
            'sit-recommender-frontend',
            SIT_RECOMMENDER_PLUGIN_URL . 'assets/js/frontend-chat-clean.js',
            array('jquery'),
            SIT_RECOMMENDER_VERSION,
            true
        );
        // Enqueue CSS
        wp_enqueue_style('sit-frontend-css', SIT_RECOMMENDER_PLUGIN_URL . 'assets/css/frontend.css', array(), SIT_RECOMMENDER_VERSION);
        
        // Localize script with settings and endpoints
        $display_settings = get_option('sit_recommender_display', array());
        $filter_settings = get_option('sit_recommender_filters', array());
        $general_settings = get_option('sit_recommender_general', array());
        $openai_settings = get_option('sit_recommender_openai', array());
        
        wp_localize_script('sit-recommender-frontend', 'sitRecommender', array(
            'apiUrl' => rest_url('sit-recommender/v1/'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'trackStats' => !empty($general_settings['track_statistics']),
            'openaiEnabled' => !empty($openai_settings['api_key']),
            'settings' => array(
                'resultsPerPage' => $display_settings['results_per_page'] ?? 10,
                'showProgressBar' => !empty($display_settings['show_progress_bar']),
                'showReasons' => !empty($display_settings['show_reasons']),
                'enableLiveFilters' => !empty($filter_settings['enable_live_filters']),
                'theme' => $display_settings['theme'] ?? 'default'
            ),
            'strings' => array(
                'loading' => __('Loading...', 'sit-program-recommender'),
                'error' => __('An error occurred. Please try again.', 'sit-program-recommender'),
                'noResults' => __('No programs found matching your criteria.', 'sit-program-recommender'),
                'startQuiz' => __('Start Quiz', 'sit-program-recommender'),
                'nextQuestion' => __('Next Question', 'sit-program-recommender'),
                'previousQuestion' => __('Previous Question', 'sit-program-recommender'),
                'getRecommendations' => __('Get My Recommendations', 'sit-program-recommender'),
                'retakeQuiz' => __('Retake Quiz', 'sit-program-recommender'),
                'viewProgram' => __('View Program Details', 'sit-program-recommender'),
                'matchStrength' => __('Match Strength:', 'sit-program-recommender'),
                'reasons' => __('Why this program:', 'sit-program-recommender'),
                'filterResults' => __('Filter Results', 'sit-program-recommender'),
                'clearFilters' => __('Clear All Filters', 'sit-program-recommender'),
                'showMore' => __('Show More Programs', 'sit-program-recommender'),
                'showLess' => __('Show Less', 'sit-program-recommender')
            )
        ));
    }
    
    /**
     * Shortcode callback
     */
    public function shortcode_callback($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'default',
            'show_filters' => 'true',
            'show_search' => 'true',
            'max_results' => '10'
        ), $atts, 'sit_program_recommender');
        
        // Check if plugin is enabled
        $general_settings = get_option('sit_recommender_general', array());
        if (empty($general_settings['enabled'])) {
            return '<div class="sit-recommender-disabled">' . __('Program Recommender is currently disabled.', 'sit-program-recommender') . '</div>';
        }
        
        ob_start();
        $this->render_recommender_interface($atts);
        return ob_get_clean();
    }
    
    /**
     * Render the main recommender interface
     */
    private function render_recommender_interface($atts) {
        $theme_class = 'sit-theme-' . sanitize_html_class($atts['theme']);
        ?>
        <div id="sit-recommender" class="sit-recommender-container <?php echo $theme_class; ?>" data-max-results="<?php echo esc_attr($atts['max_results']); ?>">
            
            <!-- Loading Overlay -->
            <div class="sit-loading-overlay" style="display: none;">
                <div class="sit-spinner"></div>
                <p class="sit-loading-text"><?php _e('Loading...', 'sit-program-recommender'); ?></p>
            </div>
            
            <!-- Welcome Screen -->
            <div class="sit-screen sit-welcome-screen">
                <div class="sit-welcome-content">
                    <h2><?php _e('Find Your Perfect Program', 'sit-program-recommender'); ?></h2>
                    <p><?php _e('Take our quick assessment to discover SIT programs that match your interests, skills, and career goals.', 'sit-program-recommender'); ?></p>
                    
                    <div class="sit-welcome-features">
                        <div class="sit-feature">
                            <span class="sit-feature-icon">🎯</span>
                            <h4><?php _e('Personalized Matching', 'sit-program-recommender'); ?></h4>
                            <p><?php _e('Get recommendations tailored to your unique profile', 'sit-program-recommender'); ?></p>
                        </div>
                        <div class="sit-feature">
                            <span class="sit-feature-icon">⚡</span>
                            <h4><?php _e('Quick Assessment', 'sit-program-recommender'); ?></h4>
                            <p><?php _e('Complete the quiz in just 5-10 minutes', 'sit-program-recommender'); ?></p>
                        </div>
                        <div class="sit-feature">
                            <span class="sit-feature-icon">🎓</span>
                            <h4><?php _e('Expert Insights', 'sit-program-recommender'); ?></h4>
                            <p><?php _e('Detailed explanations for each recommendation', 'sit-program-recommender'); ?></p>
                        </div>
                    </div>
                    
                    <button class="sit-btn sit-btn-primary sit-start-quiz" type="button">
                        <?php _e('Start Assessment', 'sit-program-recommender'); ?>
                    </button>
                    
                    <?php if ($atts['show_search'] === 'true'): ?>
                    <div class="sit-alternative-actions">
                        <p><?php _e('Or browse programs directly:', 'sit-program-recommender'); ?></p>
                        <button class="sit-btn sit-btn-secondary sit-browse-programs" type="button">
                            <?php _e('Browse All Programs', 'sit-program-recommender'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quiz Screen -->
            <div class="sit-screen sit-quiz-screen" style="display: none;">
                <div class="sit-quiz-header">
                    <div class="sit-progress-container">
                        <div class="sit-progress-bar">
                            <div class="sit-progress-fill" style="width: 0%;"></div>
                        </div>
                        <span class="sit-progress-text">0%</span>
                    </div>
                    <button class="sit-btn sit-btn-link sit-exit-quiz" type="button">
                        <?php _e('Exit Quiz', 'sit-program-recommender'); ?>
                    </button>
                </div>
                
                <div class="sit-quiz-content">
                    <div class="sit-question-container">
                        <!-- Questions will be loaded dynamically -->
                    </div>
                    
                    <div class="sit-quiz-navigation">
                        <button class="sit-btn sit-btn-secondary sit-prev-question" type="button" style="display: none;">
                            <?php _e('Previous', 'sit-program-recommender'); ?>
                        </button>
                        <button class="sit-btn sit-btn-primary sit-next-question" type="button" disabled>
                            <?php _e('Next', 'sit-program-recommender'); ?>
                        </button>
                        <button class="sit-btn sit-btn-primary sit-get-recommendations" type="button" style="display: none;">
                            <?php _e('Get My Recommendations', 'sit-program-recommender'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Results Screen -->
            <div class="sit-screen sit-results-screen" style="display: none;">
                <div class="sit-results-header">
                    <h2><?php _e('Your Program Recommendations', 'sit-program-recommender'); ?></h2>
                    <p class="sit-results-summary"></p>
                    
                    <div class="sit-results-actions">
                        <button class="sit-btn sit-btn-secondary sit-retake-quiz" type="button">
                            <?php _e('Retake Assessment', 'sit-program-recommender'); ?>
                        </button>
                        <?php if ($atts['show_filters'] === 'true'): ?>
                        <button class="sit-btn sit-btn-secondary sit-toggle-filters" type="button">
                            <?php _e('Filter Results', 'sit-program-recommender'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($atts['show_filters'] === 'true'): ?>
                <div class="sit-filters-panel" style="display: none;">
                    <div class="sit-filters-content">
                        <h4><?php _e('Filter Programs', 'sit-program-recommender'); ?></h4>
                        <div class="sit-filter-groups">
                            <div class="sit-filter-group">
                                <label><?php _e('School', 'sit-program-recommender'); ?></label>
                                <select class="sit-filter-select" data-filter="school">
                                    <option value=""><?php _e('All Schools', 'sit-program-recommender'); ?></option>
                                </select>
                            </div>
                            <div class="sit-filter-group">
                                <label><?php _e('Level', 'sit-program-recommender'); ?></label>
                                <select class="sit-filter-select" data-filter="level">
                                    <option value=""><?php _e('All Levels', 'sit-program-recommender'); ?></option>
                                </select>
                            </div>
                            <div class="sit-filter-group">
                                <label><?php _e('Study Mode', 'sit-program-recommender'); ?></label>
                                <select class="sit-filter-select" data-filter="mode">
                                    <option value=""><?php _e('All Modes', 'sit-program-recommender'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="sit-filter-actions">
                            <button class="sit-btn sit-btn-primary sit-apply-filters" type="button">
                                <?php _e('Apply Filters', 'sit-program-recommender'); ?>
                            </button>
                            <button class="sit-btn sit-btn-link sit-clear-filters" type="button">
                                <?php _e('Clear All', 'sit-program-recommender'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="sit-recommendations-container">
                    <!-- Recommendations will be loaded dynamically -->
                </div>
                
                <div class="sit-load-more-container" style="display: none;">
                    <button class="sit-btn sit-btn-secondary sit-load-more" type="button">
                        <?php _e('Load More Programs', 'sit-program-recommender'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Browse Screen -->
            <?php if ($atts['show_search'] === 'true'): ?>
            <div class="sit-screen sit-browse-screen" style="display: none;">
                <div class="sit-browse-header">
                    <h2><?php _e('Browse All Programs', 'sit-program-recommender'); ?></h2>
                    
                    <div class="sit-search-container">
                        <input type="text" class="sit-search-input" placeholder="<?php _e('Search programs...', 'sit-program-recommender'); ?>" />
                        <button class="sit-btn sit-btn-primary sit-search-btn" type="button">
                            <?php _e('Search', 'sit-program-recommender'); ?>
                        </button>
                    </div>
                    
                    <button class="sit-btn sit-btn-link sit-back-to-quiz" type="button">
                        <?php _e('← Back to Assessment', 'sit-program-recommender'); ?>
                    </button>
                </div>
                
                <div class="sit-browse-filters">
                    <!-- Filter options will be loaded dynamically -->
                </div>
                
                <div class="sit-browse-results">
                    <!-- Browse results will be loaded dynamically -->
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Error Screen -->
            <div class="sit-screen sit-error-screen" style="display: none;">
                <div class="sit-error-content">
                    <h3><?php _e('Oops! Something went wrong', 'sit-program-recommender'); ?></h3>
                    <p class="sit-error-message"></p>
                    <button class="sit-btn sit-btn-primary sit-retry-action" type="button">
                        <?php _e('Try Again', 'sit-program-recommender'); ?>
                    </button>
                    <button class="sit-btn sit-btn-link sit-back-to-start" type="button">
                        <?php _e('Start Over', 'sit-program-recommender'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Server-side fallback for no-JS users -->
        <noscript>
            <div class="sit-noscript-fallback">
                <h3><?php _e('JavaScript Required', 'sit-program-recommender'); ?></h3>
                <p><?php _e('This program recommender requires JavaScript to function properly. Please enable JavaScript in your browser or view our', 'sit-program-recommender'); ?> 
                   <a href="<?php echo esc_url(site_url('/programs')); ?>"><?php _e('complete program list', 'sit-program-recommender'); ?></a>.
                </p>
                <?php $this->render_fallback_program_list(); ?>
            </div>
        </noscript>
        <?php
    }
    
    /**
     * Render fallback program list for no-JS users
     */
    private function render_fallback_program_list() {
        $dal = new SIT_DAL();
        $programs = $dal->get_programs(array(), array('posts_per_page' => 20));
        
        if (empty($programs)) {
            return;
        }
        ?>
        <div class="sit-fallback-programs">
            <h4><?php _e('Featured Programs', 'sit-program-recommender'); ?></h4>
            <div class="sit-program-grid">
                <?php foreach ($programs as $program): ?>
                <div class="sit-program-card">
                    <?php if ($program->featured_image): ?>
                    <div class="sit-program-image">
                        <img src="<?php echo esc_url($program->featured_image); ?>" alt="<?php echo esc_attr($program->post_title); ?>" />
                    </div>
                    <?php endif; ?>
                    
                    <div class="sit-program-content">
                        <h5><a href="<?php echo esc_url($program->permalink); ?>"><?php echo esc_html($program->post_title); ?></a></h5>
                        <p><?php echo esc_html($program->post_excerpt); ?></p>
                        
                        <?php if (!empty($program->meta['school'])): ?>
                        <div class="sit-program-meta">
                            <span class="sit-meta-school"><?php echo esc_html($program->meta['school']); ?></span>
                            <?php if (!empty($program->meta['level'])): ?>
                            <span class="sit-meta-level"><?php echo esc_html($program->meta['level']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url($program->permalink); ?>" class="sit-btn sit-btn-primary sit-btn-small">
                            <?php _e('Learn More', 'sit-program-recommender'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add inline scripts to footer
     */
    public function add_inline_scripts() {
        global $post;
        if (!$post || !has_shortcode($post->post_content, 'sit_program_recommender')) {
            return;
        }
        
        // Add any additional inline scripts or styles needed
        ?>
        <script type="text/javascript">
        // Initialize the recommender when DOM is ready
        jQuery(document).ready(function($) {
            if (typeof SITRecommender !== 'undefined') {
                SITRecommender.init();
            }
        });
        </script>
        <?php
    }
}

// Include the results template for server-side rendering fallback
if (!function_exists('sit_render_program_card')) {
    /**
     * Render a program card
     */
    function sit_render_program_card($program, $recommendation_data = null) {
        $card_class = 'sit-program-card';
        if ($recommendation_data) {
            $card_class .= ' sit-recommendation-card';
        }
        ?>
        <div class="<?php echo $card_class; ?>" data-program-id="<?php echo esc_attr($program->ID); ?>">
            <?php if ($program->featured_image): ?>
            <div class="sit-program-image">
                <img src="<?php echo esc_url($program->featured_image); ?>" alt="<?php echo esc_attr($program->post_title); ?>" loading="lazy" />
                <?php if ($recommendation_data): ?>
                <div class="sit-match-badge sit-match-<?php echo esc_attr(strtolower(str_replace(' ', '-', $recommendation_data['match_strength']))); ?>">
                    <?php echo esc_html($recommendation_data['match_strength']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="sit-program-content">
                <h3 class="sit-program-title">
                    <a href="<?php echo esc_url($program->permalink); ?>"><?php echo esc_html($program->post_title); ?></a>
                </h3>
                
                <div class="sit-program-meta">
                    <?php if (!empty($program->meta['school'])): ?>
                    <span class="sit-meta-item sit-meta-school">
                        <i class="sit-icon-school"></i>
                        <?php echo esc_html($program->meta['school']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($program->meta['level'])): ?>
                    <span class="sit-meta-item sit-meta-level">
                        <i class="sit-icon-level"></i>
                        <?php echo esc_html($program->meta['level']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($program->meta['duration'])): ?>
                    <span class="sit-meta-item sit-meta-duration">
                        <i class="sit-icon-duration"></i>
                        <?php echo esc_html($program->meta['duration']); ?> <?php _e('years', 'sit-program-recommender'); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <p class="sit-program-excerpt"><?php echo esc_html($program->post_excerpt); ?></p>
                
                <?php if ($recommendation_data && !empty($recommendation_data['reasons'])): ?>
                <div class="sit-recommendation-reasons">
                    <h4><?php _e('Why this program matches you:', 'sit-program-recommender'); ?></h4>
                    <ul>
                        <?php foreach ($recommendation_data['reasons'] as $reason): ?>
                        <li><?php echo esc_html($reason); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($recommendation_data && !empty($recommendation_data['score'])): ?>
                <div class="sit-match-score">
                    <span class="sit-score-label"><?php _e('Match Score:', 'sit-program-recommender'); ?></span>
                    <div class="sit-score-bar">
                        <div class="sit-score-fill" style="width: <?php echo esc_attr($recommendation_data['score'] * 100); ?>%;"></div>
                    </div>
                    <span class="sit-score-value"><?php echo esc_html(round($recommendation_data['score'] * 100)); ?>%</span>
                </div>
                <?php endif; ?>
                
                <div class="sit-program-actions">
                    <a href="<?php echo esc_url($program->permalink); ?>" class="sit-btn sit-btn-primary">
                        <?php _e('View Details', 'sit-program-recommender'); ?>
                    </a>
                    
                    <?php if (!empty($program->meta['requirements'])): ?>
                    <button class="sit-btn sit-btn-secondary sit-show-requirements" type="button" data-program-id="<?php echo esc_attr($program->ID); ?>">
                        <?php _e('Requirements', 'sit-program-recommender'); ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
