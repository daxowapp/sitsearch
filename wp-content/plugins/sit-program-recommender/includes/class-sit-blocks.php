<?php
/**
 * SIT Gutenberg Blocks
 * 
 * Handles Gutenberg block registration for the program recommender.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SIT_Blocks {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }
    
    /**
     * Register blocks
     */
    public function register_blocks() {
        // Register the program recommender block
        register_block_type('sit/program-recommender', array(
            'attributes' => array(
                'theme' => array(
                    'type' => 'string',
                    'default' => 'default'
                ),
                'showFilters' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'showSearch' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'maxResults' => array(
                    'type' => 'number',
                    'default' => 10
                )
            ),
            'render_callback' => array($this, 'render_program_recommender_block'),
            'editor_script' => 'sit-block-editor',
            'editor_style' => 'sit-block-editor-style'
        ));
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'sit-block-editor',
            SIT_RECOMMENDER_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            SIT_RECOMMENDER_VERSION,
            true
        );
        
        wp_enqueue_style(
            'sit-block-editor-style',
            SIT_RECOMMENDER_PLUGIN_URL . 'assets/css/blocks.css',
            array('wp-edit-blocks'),
            SIT_RECOMMENDER_VERSION
        );
        
        wp_localize_script('sit-block-editor', 'sitBlocks', array(
            'pluginUrl' => SIT_RECOMMENDER_PLUGIN_URL,
            'strings' => array(
                'title' => __('SIT Program Recommender', 'sit-program-recommender'),
                'description' => __('Interactive program recommendation quiz for SIT students', 'sit-program-recommender'),
                'theme' => __('Theme', 'sit-program-recommender'),
                'showFilters' => __('Show Filters', 'sit-program-recommender'),
                'showSearch' => __('Show Search', 'sit-program-recommender'),
                'maxResults' => __('Maximum Results', 'sit-program-recommender')
            )
        ));
    }
    
    /**
     * Render program recommender block
     */
    public function render_program_recommender_block($attributes) {
        $atts = array(
            'theme' => $attributes['theme'] ?? 'default',
            'show_filters' => $attributes['showFilters'] ? 'true' : 'false',
            'show_search' => $attributes['showSearch'] ? 'true' : 'false',
            'max_results' => $attributes['maxResults'] ?? 10
        );
        
        // Use the shortcode callback from frontend class
        $frontend = new SIT_Frontend();
        return $frontend->shortcode_callback($atts);
    }
}
