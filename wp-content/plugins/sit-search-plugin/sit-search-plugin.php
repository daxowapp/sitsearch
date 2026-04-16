<?php
/*
Plugin Name: University Programs Display
Description: Display university programs with tabs, search, and pagination.
Version: 1.0
Author: SIT Search
*/

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'UPD_';
    $base_dir = plugin_dir_path(__FILE__) . 'inc/';

    if (strpos($class, $prefix) === 0) {
        $class_file = strtolower(str_replace('_', '-', str_replace($prefix, '', $class))) . '.php';
        $file = $base_dir . $class_file;
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Initialize Plugin
function upd_init_plugin() {
    new UPD_Shortcode();
    new UPD_AjaxHandler();
}
add_action('plugins_loaded', 'upd_init_plugin');

register_deactivation_hook(__FILE__, 'clear_university_zoho_cron');
function clear_university_zoho_cron() {
    $timestamp = wp_next_scheduled('run_university_zoho_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'run_university_zoho_cron');
    }
}

