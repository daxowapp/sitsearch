<?php
/**
 * Debug Check for SIT Program Recommender
 * 
 * This file helps identify what's causing the critical error.
 * Access it directly via: yoursite.com/wp-content/plugins/sit-program-recommender/debug-check.php
 */

// Basic PHP check
echo "<h1>SIT Plugin Debug Check</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check if WordPress is loaded
if (!defined('ABSPATH')) {
    echo "<p style='color: red;'><strong>WordPress not loaded.</strong> Trying to load...</p>";
    
    // Try to load WordPress
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../../../wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            echo "<p style='color: green;'><strong>WordPress loaded successfully!</strong></p>";
            break;
        }
    }
    
    if (!$wp_loaded) {
        echo "<p style='color: red;'><strong>Could not load WordPress.</strong></p>";
        exit;
    }
} else {
    echo "<p style='color: green;'><strong>WordPress is loaded.</strong></p>";
}

// Check if plugin files exist
$plugin_dir = __DIR__;
$required_files = [
    'sit-program-recommender.php',
    'includes/class-sit-admin.php',
    'includes/class-sit-frontend.php',
    'includes/class-sit-rest-api.php',
    'includes/class-sit-dal.php',
    'includes/class-sit-engine.php',
    'includes/class-sit-blocks.php'
];

echo "<h2>File Check</h2>";
foreach ($required_files as $file) {
    $file_path = $plugin_dir . '/' . $file;
    if (file_exists($file_path)) {
        echo "<p style='color: green;'>✓ {$file} exists</p>";
        
        // Check for syntax errors
        $output = shell_exec("php -l " . escapeshellarg($file_path) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color: green; margin-left: 20px;'>✓ No syntax errors</p>";
        } else {
            echo "<p style='color: red; margin-left: 20px;'>✗ Syntax error: {$output}</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ {$file} missing</p>";
    }
}

// Check if plugin is active
if (function_exists('is_plugin_active')) {
    $plugin_file = 'sit-program-recommender/sit-program-recommender.php';
    if (is_plugin_active($plugin_file)) {
        echo "<p style='color: green;'><strong>Plugin is active.</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>Plugin is NOT active.</strong></p>";
    }
} else {
    echo "<p style='color: orange;'><strong>Cannot check plugin status (WordPress admin functions not available).</strong></p>";
}

// Check constants
echo "<h2>Constants Check</h2>";
$constants = [
    'SIT_RECOMMENDER_VERSION',
    'SIT_RECOMMENDER_PLUGIN_FILE',
    'SIT_RECOMMENDER_PLUGIN_DIR',
    'SIT_RECOMMENDER_PLUGIN_URL',
    'SIT_RECOMMENDER_PLUGIN_BASENAME'
];

foreach ($constants as $constant) {
    if (defined($constant)) {
        echo "<p style='color: green;'>✓ {$constant} = " . constant($constant) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ {$constant} not defined</p>";
    }
}

// Try to instantiate the admin class
echo "<h2>Class Loading Test</h2>";
try {
    if (file_exists($plugin_dir . '/includes/class-sit-admin.php')) {
        require_once $plugin_dir . '/includes/class-sit-admin.php';
        echo "<p style='color: green;'>✓ Admin class file loaded</p>";
        
        if (class_exists('SIT_Admin')) {
            echo "<p style='color: green;'>✓ SIT_Admin class exists</p>";
            
            // Try to create instance (only if we have WordPress loaded)
            if (function_exists('current_user_can')) {
                $admin = new SIT_Admin();
                echo "<p style='color: green;'>✓ SIT_Admin instance created successfully</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ SIT_Admin class does not exist</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error loading admin class: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ Fatal error loading admin class: " . $e->getMessage() . "</p>";
}

echo "<h2>Database Check</h2>";
if (isset($wpdb)) {
    echo "<p style='color: green;'>✓ Database connection available</p>";
    
    // Test a simple query
    try {
        $result = $wpdb->get_var("SELECT 1");
        if ($result == 1) {
            echo "<p style='color: green;'>✓ Database query successful</p>";
        } else {
            echo "<p style='color: red;'>✗ Database query failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ No database connection</p>";
}

echo "<p><strong>Debug check completed.</strong></p>";
?>
