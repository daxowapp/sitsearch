<?php

namespace SIT\Search\Actions;

class Dashboard {

    public function __construct() {
        // We will register the page via RegisterMenu class, 
        // but we need a method to render it.
    }

    public function render() {
        // Collect stats
        $uni_count = wp_count_posts('sit-university')->publish;
        $prog_count = wp_count_posts('sit-program')->publish;
        $featured_count = count((new \SIT\Search\Services\FeaturedUniversity())->getFeatured());
        
        // Supabase Status
        $supabase = new \SIT\Search\Services\Supabase();
        $supabase_status = $supabase->is_configured() ? 'Connected' : 'Not Configured';
        $supabase_class = $supabase->is_configured() ? 'sit-status-ok' : 'sit-status-error';

        ?>
        <div class="wrap sit-admin-wrap">
            <h1 class="wp-heading-inline">Study in Turkiye Dashboard</h1>
            
            <div class="sit-dashboard-grid">
                <!-- Welcome Card -->
                <div class="sit-card sit-welcome-card">
                    <h2>Welcome to SIT Search</h2>
                    <p>Manage your universities, programs, and sync settings from here.</p>
                </div>

                <!-- Stats Cards -->
                <div class="sit-stats-row">
                    <div class="sit-stat-card">
                        <div class="sit-stat-icon">🎓</div>
                        <div class="sit-stat-content">
                            <h3><?= $uni_count ?></h3>
                            <span>Universities</span>
                        </div>
                    </div>
                    
                    <div class="sit-stat-card">
                        <div class="sit-stat-icon">📚</div>
                        <div class="sit-stat-content">
                            <h3><?= $prog_count ?></h3>
                            <span>Programs</span>
                        </div>
                    </div>

                    <div class="sit-stat-card">
                        <div class="sit-stat-icon">⭐</div>
                        <div class="sit-stat-content">
                            <h3><?= $featured_count ?></h3>
                            <span>Featured</span>
                        </div>
                    </div>

                    <div class="sit-stat-card <?= $supabase_class ?>">
                        <div class="sit-stat-icon">🔄</div>
                        <div class="sit-stat-content">
                            <h3><?= $supabase_status ?></h3>
                            <span>Supabase Sync</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="sit-card sit-actions-card">
                    <h3>Quick Actions</h3>
                    <div class="sit-actions-grid">
                        <a href="<?= admin_url('admin.php?page=sit-supabase-sync') ?>" class="sit-action-btn">
                            <span class="dashicons dashicons-update"></span> Sync Data
                        </a>
                        <a href="<?= admin_url('admin.php?page=sit-featured-universities') ?>" class="sit-action-btn">
                            <span class="dashicons dashicons-star-filled"></span> Manage Featured
                        </a>
                        <a href="<?= admin_url('edit.php?post_type=sit-program') ?>" class="sit-action-btn">
                            <span class="dashicons dashicons-welcome-add-page"></span> View Programs
                        </a>
                        <a href="<?= admin_url('admin.php?page=sit-settings') ?>" class="sit-action-btn">
                            <span class="dashicons dashicons-admin-settings"></span> Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
