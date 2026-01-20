<?php
/**
 * Build Script for SIT Program Recommender
 * 
 * Creates a distributable ZIP file of the plugin
 */

// Prevent direct access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

class SIT_Plugin_Builder {
    
    private $plugin_dir;
    private $build_dir;
    private $plugin_name = 'sit-program-recommender';
    private $version;
    
    public function __construct() {
        $this->plugin_dir = __DIR__;
        $this->build_dir = dirname($this->plugin_dir) . '/builds';
        $this->version = $this->get_plugin_version();
        
        echo "SIT Program Recommender Build Script\n";
        echo "=====================================\n\n";
    }
    
    /**
     * Main build process
     */
    public function build() {
        try {
            $this->create_build_directory();
            $this->copy_plugin_files();
            $this->create_zip_package();
            $this->cleanup();
            
            echo "✅ Build completed successfully!\n";
            echo "📦 Package created: {$this->build_dir}/{$this->plugin_name}-{$this->version}.zip\n\n";
            
        } catch (Exception $e) {
            echo "❌ Build failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    /**
     * Get plugin version from main file
     */
    private function get_plugin_version() {
        $main_file = $this->plugin_dir . '/' . $this->plugin_name . '.php';
        
        if (!file_exists($main_file)) {
            throw new Exception("Main plugin file not found: {$main_file}");
        }
        
        $content = file_get_contents($main_file);
        
        if (preg_match('/Version:\s*(.+)/', $content, $matches)) {
            return trim($matches[1]);
        }
        
        return '1.0.0';
    }
    
    /**
     * Create build directory
     */
    private function create_build_directory() {
        echo "📁 Creating build directory...\n";
        
        if (!is_dir($this->build_dir)) {
            if (!mkdir($this->build_dir, 0755, true)) {
                throw new Exception("Failed to create build directory: {$this->build_dir}");
            }
        }
        
        $temp_dir = $this->build_dir . '/temp';
        if (is_dir($temp_dir)) {
            $this->remove_directory($temp_dir);
        }
        
        if (!mkdir($temp_dir, 0755, true)) {
            throw new Exception("Failed to create temp directory: {$temp_dir}");
        }
    }
    
    /**
     * Copy plugin files to build directory
     */
    private function copy_plugin_files() {
        echo "📋 Copying plugin files...\n";
        
        $temp_plugin_dir = $this->build_dir . '/temp/' . $this->plugin_name;
        
        if (!mkdir($temp_plugin_dir, 0755, true)) {
            throw new Exception("Failed to create temp plugin directory: {$temp_plugin_dir}");
        }
        
        $exclude_patterns = [
            '.git',
            '.gitignore',
            '.DS_Store',
            'node_modules',
            'build.php',
            'builds',
            '*.log',
            '*.tmp',
            '.vscode',
            '.idea',
            'composer.lock',
            'package-lock.json'
        ];
        
        $this->copy_directory($this->plugin_dir, $temp_plugin_dir, $exclude_patterns);
        
        // Create optimized version info
        $this->create_build_info($temp_plugin_dir);
    }
    
    /**
     * Create ZIP package
     */
    private function create_zip_package() {
        echo "📦 Creating ZIP package...\n";
        
        $zip_file = $this->build_dir . '/' . $this->plugin_name . '-' . $this->version . '.zip';
        
        if (file_exists($zip_file)) {
            unlink($zip_file);
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Failed to create ZIP file: {$zip_file}");
        }
        
        $temp_dir = $this->build_dir . '/temp';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $file_path = $file->getRealPath();
            $relative_path = substr($file_path, strlen($temp_dir) + 1);
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relative_path);
            } else {
                $zip->addFile($file_path, $relative_path);
            }
        }
        
        $zip->close();
        
        echo "📊 Package size: " . $this->format_bytes(filesize($zip_file)) . "\n";
    }
    
    /**
     * Create build information file
     */
    private function create_build_info($plugin_dir) {
        $build_info = [
            'version' => $this->version,
            'build_date' => date('Y-m-d H:i:s'),
            'build_timestamp' => time(),
            'php_version' => PHP_VERSION,
            'files_count' => $this->count_files($plugin_dir),
            'total_size' => $this->get_directory_size($plugin_dir)
        ];
        
        file_put_contents(
            $plugin_dir . '/build-info.json',
            json_encode($build_info, JSON_PRETTY_PRINT)
        );
    }
    
    /**
     * Cleanup temporary files
     */
    private function cleanup() {
        echo "🧹 Cleaning up temporary files...\n";
        
        $temp_dir = $this->build_dir . '/temp';
        if (is_dir($temp_dir)) {
            $this->remove_directory($temp_dir);
        }
    }
    
    /**
     * Copy directory recursively with exclusions
     */
    private function copy_directory($source, $destination, $exclude_patterns = []) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relative_path = substr($file->getRealPath(), strlen($source) + 1);
            
            // Check if file should be excluded
            $should_exclude = false;
            foreach ($exclude_patterns as $pattern) {
                if (fnmatch($pattern, $relative_path) || fnmatch($pattern, basename($file))) {
                    $should_exclude = true;
                    break;
                }
            }
            
            if ($should_exclude) {
                continue;
            }
            
            $dest_path = $destination . '/' . $relative_path;
            
            if ($file->isDir()) {
                if (!is_dir($dest_path)) {
                    mkdir($dest_path, 0755, true);
                }
            } else {
                $dest_dir = dirname($dest_path);
                if (!is_dir($dest_dir)) {
                    mkdir($dest_dir, 0755, true);
                }
                copy($file->getRealPath(), $dest_path);
            }
        }
    }
    
    /**
     * Remove directory recursively
     */
    private function remove_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Count files in directory
     */
    private function count_files($dir) {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get directory size
     */
    private function get_directory_size($dir) {
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Format bytes to human readable
     */
    private function format_bytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Run the build process
$builder = new SIT_Plugin_Builder();
$builder->build();

echo "🎉 Build process completed!\n";
echo "\nTo install the plugin:\n";
echo "1. Upload the ZIP file to WordPress admin > Plugins > Add New > Upload\n";
echo "2. Or extract to wp-content/plugins/ directory\n";
echo "3. Activate the plugin\n";
echo "4. Configure settings in SIT Recommender menu\n\n";
