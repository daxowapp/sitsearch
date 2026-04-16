<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\CachedData;

class UniversityStatusAdmin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page'], 30); // Priority 30 to sit after Featured Unis
        // Removed toggle handler (read only now)
        add_action('wp_ajax_sit_fix_duplicates', [$this, 'handle_fix_duplicates']);
    }

    public function add_menu_page() {
        add_submenu_page(
            'sit-search',
            'University Status',
            'University Status',
            'manage_options',
            'sit-university-status',
            [$this, 'render_page']
        );
    }

    public function render_page() {
        // Fetch all universities (publish status)
        $allowed_countries = [];
        $turkey_term = get_term_by('name', 'Turkey', 'sit-country');
        if ($turkey_term) $allowed_countries[] = $turkey_term->term_id;
        
        $nc_term = get_term_by('name', 'Northern Cyprus', 'sit-country');
        if ($nc_term) $allowed_countries[] = $nc_term->term_id;

        $args = [
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        if (!empty($allowed_countries)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                ]
            ];
        }

        $universities = get_posts($args);

        $processed_ids = [];
        ?>
        <div class="wrap sit-admin-wrap">
            <h1 class="wp-heading-inline">University Status Monitor</h1>
            <p class="description">Overview of university visibility and data status. (Synced from Supabase)</p>
            <hr class="wp-header-end">

            <div class="card sit-list-card" style="margin-top: 20px; padding: 0;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="50">ID(s)</th>
                            <th width="100">Zoho ID</th>
                            <th>University Name</th>
                            <th width="150" style="text-align: center;">Languages</th>
                            <th width="120" style="text-align: center;">Total Programs</th>
                            <th width="150" style="text-align: center;">Active in Search</th>
                            <th width="150" style="text-align: center;">Active in New Apps</th>
                            <th width="100" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($universities)): ?>
                            <tr><td colspan="7" style="text-align:center;">No universities found.</td></tr>
                        <?php else: 
                            foreach ($universities as $uni): 
                                if (in_array($uni->ID, $processed_ids)) continue;

                                $group_ids = [$uni->ID];
                                
                                // 1. Try Polylang
                                if (function_exists('pll_get_post_translations')) {
                                    $trans = pll_get_post_translations($uni->ID);
                                    if (!empty($trans)) {
                                        $group_ids = array_values($trans);
                                    }
                                }

                                // 2. Fallback: Group by Zoho Account ID (stronger link)
                                // If Polylang returned 1 (just self) or empty, check meta
                                if (count($group_ids) <= 1) {
                                    $z_id = get_post_meta($uni->ID, 'zoho_account_id', true);
                                    if ($z_id) {
                                        $same_zoho_posts = get_posts([
                                            'post_type' => 'sit-university',
                                            'meta_key' => 'zoho_account_id',
                                            'meta_value' => $z_id,
                                            'posts_per_page' => -1,
                                            'fields' => 'ids',
                                            'post_status' => 'any'
                                        ]);
                                        if (!empty($same_zoho_posts)) {
                                            $group_ids = array_unique(array_merge($group_ids, $same_zoho_posts));
                                        }
                                    }
                                }

                                // Mark all as processed immediately
                                foreach($group_ids as $gid) $processed_ids[] = (int)$gid;

                                // Check Status (check all, if any is active, consider active)
                                $is_search_active = false;
                                $is_apps_active = false;
                                
                                foreach($group_ids as $gid) {
                                    $s_active = get_post_meta($gid, 'Active_in_Search', true);
                                    // Check both meta keys for compatibility (sync writes Active_in_Apps)
                                    $a_active = get_post_meta($gid, 'Active_in_Apps', true);
                                    if (empty($a_active)) {
                                        $a_active = get_post_meta($gid, 'Active_in_New_Apps', true);
                                    }
                                    if ($s_active == '1' || $s_active === 'true' || $s_active === true) $is_search_active = true;
                                    if ($a_active == '1' || $a_active === 'true' || $a_active === true) $is_apps_active = true;
                                }

                                // Calculate Total Programs across ALL linked IDs
                                $total_prog_count = 0;
                                foreach($group_ids as $gid) {
                                    $total_prog_count += count(get_posts([
                                        'post_type' => 'sit-program', 
                                        'meta_key' => 'zh_university', 
                                        'meta_value' => $gid,
                                        'posts_per_page' => -1,
                                        'fields' => 'ids'
                                    ]));
                                }
                                
                                $warn_class = ($total_prog_count == 0 && $is_search_active) ? 'style="color:red; font-weight:bold;"' : '';
                                $zoho_id = get_post_meta($uni->ID, 'zoho_account_id', true);
                        ?>
                            <tr>
                                <td><?= implode(', ', $group_ids) ?></td>
                                <td><code><?= $zoho_id ?: 'NULL' ?></code></td>
                                <td>
                                    <strong><a href="<?= get_edit_post_link($uni->ID) ?>" target="_blank"><?= esc_html($uni->post_title) ?></a></strong>
                                </td>
                                <td style="text-align:center;">
                                    <?php foreach($group_ids as $gid): 
                                        $lang = function_exists('pll_get_post_language') ? pll_get_post_language($gid) : '-';
                                    ?>
                                        <span class="sit-lang-badge" style="background:#eee; padding:2px 6px; border-radius:4px; font-size:11px; margin:0 2px;">
                                            <?= strtoupper($lang) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td style="text-align:center;" <?= $warn_class ?>>
                                    <?= $total_prog_count ?>
                                    <?php if($total_prog_count == 0 && $is_search_active): ?>
                                        <div class="dashicons dashicons-warning" title="Active but 0 programs found across all languages!"></div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($is_search_active): ?>
                                        <span class="sit-status-badge active">
                                            <span class="dashicons dashicons-yes"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="sit-status-badge inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($is_apps_active): ?>
                                        <span class="sit-status-badge active">
                                            <span class="dashicons dashicons-yes"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="sit-status-badge inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= get_edit_post_link($uni->ID) ?>" class="button button-small" target="_blank">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

            <div style="margin-top: 20px;">
                <button id="sit-fix-duplicates-btn" class="button button-secondary">🛠 Fix Duplicates & Migrate IDs</button>
                <span id="sit-fix-msg" style="margin-left:10px; font-weight:bold;"></span>
            </div>
        </div>

        <style>
            .sit-admin-wrap .card { max-width: 100%; box-sizing: border-box; }
            .sit-status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 12px; }
            .sit-status-badge.active { background-color: #e7f4e8; color: #2e7d32; }
            .sit-status-badge.inactive { background-color: #f5f5f5; color: #757575; }
            .sit-status-badge .dashicons { font-size: 16px; line-height: 16px; width: 16px; height: 16px; margin-right: 4px; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#sit-fix-duplicates-btn').on('click', function() {
                if(!confirm('This will merge duplicates based on Zoho ID and backfill supabase_id meta keys. Continue?')) return;
                
                var btn = $(this);
                btn.prop('disabled', true).text('Fixing...');
                $('#sit-fix-msg').text('');
                
                $.post(ajaxurl, {
                    action: 'sit_fix_duplicates',
                    nonce: '<?= wp_create_nonce("sit_status_nonce") ?>'
                }, function(res) {
                    btn.prop('disabled', false).text('🛠 Fix Duplicates & Migrate IDs');
                    if (res.success) {
                        $('#sit-fix-msg').css('color', 'green').text(res.data);
                        setTimeout(function(){ location.reload(); }, 2000);
                    } else {
                        $('#sit-fix-msg').css('color', 'red').text('Error: ' + res.data);
                    }
                }).fail(function() {
                    btn.prop('disabled', false);
                    $('#sit-fix-msg').css('color', 'red').text('Server Error');
                });
            });
        });
        </script>
        <?php
    }
    
    // Handler for duplicate fixing
    public function handle_fix_duplicates() {
        check_ajax_referer('sit_status_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
        
        // 1. Fetch all universities raw
        $unis = get_posts([
            'post_type' => 'sit-university',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'lang' => '' // Polylang bypass
        ]);
        
        $groups = [];
        $migrated_count = 0;
        $deleted_count = 0;
        
        foreach ($unis as $u) {
            $z_id = get_post_meta($u->ID, 'zoho_account_id', true);
            $s_id = get_post_meta($u->ID, 'supabase_id', true);
            
            // Migration: If has ZohoID but no SupabaseID, copy it.
            if ($z_id && empty($s_id)) {
                update_post_meta($u->ID, 'supabase_id', $z_id);
                $migrated_count++;
            }
            
            // Grouping for duplicate check
            $key = $z_id ?: 'unknown_'.$u->ID;
            if ($z_id) {
                if (!isset($groups[$key])) $groups[$key] = [];
                $groups[$key][] = $u;
            }
        }
        
        // 2. Process Duplicates
        foreach ($groups as $zid => $posts) {
            if (count($posts) > 1) {
                // Sort by ID ASC (Oldest first)
                usort($posts, function($a, $b) { return $a->ID - $b->ID; });
                
                // Keep the first one (Original), delete the rest
                $original = array_shift($posts);
                
                // Ensure original has the meta
                update_post_meta($original->ID, 'supabase_id', $zid);
                
                // Delete duplicates
                foreach ($posts as $dup) {
                    wp_delete_post($dup->ID, true);
                    $deleted_count++;
                }
            }
        }
        
        wp_send_json_success("Fixed! Migrated $migrated_count IDs, Deleted $deleted_count duplicate posts.");
    }
}
