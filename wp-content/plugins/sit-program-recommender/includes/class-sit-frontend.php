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
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'sit-recommender-frontend',
            SIT_RECOMMENDER_PLUGIN_URL . 'assets/js/frontend.js',
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
        $openrouter_settings = get_option('sit_recommender_openrouter', array());
        
        wp_localize_script('sit-recommender-frontend', 'sitRecommender', array(
            'apiUrl' => rest_url('sit-recommender/v1/'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'trackStats' => !empty($general_settings['track_statistics']),
            'openrouterEnabled' => !empty($openrouter_settings['api_key']),
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
            
            <div class="sit-bot-initialize">
                <div class="sit-bot-icon-pulse">🤖</div>
                <div class="sit-pulse-ring"></div>
                <p><?php _e('Initializing AI Study Advisor...', 'sit-program-recommender'); ?></p>
            </div>
            
        </div>
        
        <!-- Server-side fallback for no-JS users -->
        <noscript>
            <div class="sit-noscript-fallback">
                <h3><?php _e('JavaScript Required', 'sit-program-recommender'); ?></h3>
                <p><?php _e('Our AI Study Advisor requires JavaScript to function properly. Please enable JavaScript in your browser or view our', 'sit-program-recommender'); ?> 
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
        $programs = get_posts(array(
            'post_type' => 'sit_program',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'no_found_rows' => true,
        ));
        
        if (empty($programs)) {
            return;
        }
        ?>
        <div class="sit-fallback-programs">
            <h4><?php _e('Featured Programs', 'sit-program-recommender'); ?></h4>
            <div class="sit-program-grid">
                <?php foreach ($programs as $program): 
                    $featured_image = get_the_post_thumbnail_url($program->ID, 'medium');
                    $permalink = get_permalink($program->ID);
                    $school = get_post_meta($program->ID, 'sit_program_school', true);
                ?>
                <div class="sit-program-card">
                    <?php if ($featured_image): ?>
                    <div class="sit-program-image">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($program->post_title); ?>" />
                    </div>
                    <?php endif; ?>
                    
                    <div class="sit-program-content">
                        <h5><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($program->post_title); ?></a></h5>
                        <p><?php echo esc_html(wp_trim_words($program->post_content, 30)); ?></p>
                        
                        <?php if (!empty($school)): ?>
                        <div class="sit-program-meta">
                            <span class="sit-meta-item sit-school">
                                <i class="fas fa-university"></i> <?php echo esc_html($school); ?>
                            </span>
                        </div>
                        <?php endif; ?>
    <?php
                        $level = get_post_meta($program->ID, 'sit_program_level', true);
                        $duration = get_post_meta($program->ID, 'sit_program_duration', true);
                        ?>
                        <?php if (!empty($level) || !empty($duration)): ?>
                        <div class="sit-program-meta-details">
                            <?php if (!empty($level)): ?>
                            <span class="sit-meta-level"><?php echo esc_html($level); ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($duration)): ?>
                            <span class="sit-meta-duration"><i class="far fa-clock"></i> <?php echo esc_html($duration); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="sit-program-actions">
                            <a href="<?php echo esc_url($permalink); ?>" class="sit-btn sit-btn-outline"><?php _e('View Details', 'sit-program-recommender'); ?></a>
                        </div>
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
