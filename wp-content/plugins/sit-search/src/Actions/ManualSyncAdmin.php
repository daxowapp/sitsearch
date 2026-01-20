<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Supabase;
use SIT\Search\Services\CachedData;

/**
 * Manual Sync Admin Page
 */
class ManualSyncAdmin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page'], 20);
        add_action('wp_ajax_sit_manual_sync', [$this, 'handle_sync_ajax']);
        add_action('wp_ajax_sit_sync_table', [$this, 'handle_table_sync']);
    }
    
    public function add_menu_page(): void
    {
        add_submenu_page(
            'sit-search',
            'Supabase Sync',
            '🔄 Supabase Sync',
            'manage_options',
            'sit-supabase-sync',
            [$this, 'render_page']
        );
    }
    
    public function render_page(): void
    {
        $supabase = new Supabase();
        $is_configured = $supabase->is_configured();
        ?>
        <div class="wrap">
            <h1>🔄 Supabase Sync</h1>
            
            <?php if (!$is_configured): ?>
                <div class="notice notice-error">
                    <p><strong>Supabase not configured!</strong></p>
                </div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>Sync data from Supabase to WordPress. Only new/updated records are fetched (incremental sync).</p>
                </div>
                
                <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>WordPress Entity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sync-tables">
                        <?php
                        $tables = [
                            ['zoho_degrees', 'sit-degree', 'Taxonomy'],
                            ['zoho_countries', 'sit-country', 'Taxonomy'],
                            ['zoho_cities', 'sit-city', 'Taxonomy'],
                            ['zoho_faculty', 'sit-faculty', 'Taxonomy'],
                            ['zoho_speciality', 'sit-speciality', 'Taxonomy'],
                            ['zoho_languages', 'sit-language', 'Taxonomy'],
                            ['zoho_universities', 'sit-university', 'Post Type'],
                            ['zoho_programs', 'sit-program', 'Post Type'],
                            ['zoho_campus', 'sit-campus', 'Post Type'],
                        ];
                        
                        foreach ($tables as $table):
                        ?>
                        <tr data-table="<?= esc_attr($table[0]) ?>">
                            <td><code><?= esc_html($table[0]) ?></code></td>
                            <td><?= esc_html($table[1]) ?> (<?= esc_html($table[2]) ?>)</td>
                            <td class="sync-status">Ready</td>
                            <td><button class="button sync-table-btn" data-table="<?= esc_attr($table[0]) ?>">Sync</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p style="margin-top: 20px;">
                    <button id="sync-all-btn" class="button button-primary button-hero">🔄 Sync All</button>
                    <button id="clear-cache-btn" class="button">🗑️ Clear Cache</button>
                </p>
                
                <h3>Sync Log</h3>
                <div id="sync-log" style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:5px;max-height:300px;overflow-y:auto;font-family:monospace;font-size:12px;">
                    <p style="color:#6a9955;">// Ready to sync</p>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
            .sync-status.syncing{color:#0073aa}.sync-status.success{color:#46b450}.sync-status.error{color:#dc3232}
            #sync-log .log-info{color:#569cd6}#sync-log .log-success{color:#4ec9b0}#sync-log .log-error{color:#f14c4c}
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            const nonce = '<?= wp_create_nonce('sit_sync_nonce') ?>';
            
            function log(msg, type) {
                const time = new Date().toLocaleTimeString();
                $('#sync-log').append('<div><span style="color:#808080">['+time+']</span> <span class="log-'+type+'">'+msg+'</span></div>');
                $('#sync-log').scrollTop($('#sync-log')[0].scrollHeight);
            }
            
            function syncTable(table) {
                return new Promise((resolve,reject) => {
                    const $row = $('tr[data-table="'+table+'"]');
                    $row.find('.sync-status').text('Syncing...').addClass('syncing');
                    $row.find('button').prop('disabled',true);
                    log('Syncing '+table+'...','info');
                    
                    $.post(ajaxurl, {action:'sit_sync_table',table:table,nonce:nonce}, function(r) {
                        $row.find('button').prop('disabled',false);
                        if(r.success) {
                            $row.find('.sync-status').text('✓ '+r.data.created+' new, '+r.data.updated+' updated').removeClass('syncing').addClass('success');
                            log('✓ '+table+': '+r.data.created+' created, '+r.data.updated+' updated','success');
                            resolve(r.data);
                        } else {
                            $row.find('.sync-status').text('✗ Error').removeClass('syncing').addClass('error');
                            log('✗ '+table+': '+r.data,'error');
                            reject(r.data);
                        }
                    });
                });
            }
            
            $('.sync-table-btn').on('click', function() { syncTable($(this).data('table')); });
            
            $('#sync-all-btn').on('click', async function() {
                const tables = ['zoho_degrees','zoho_countries','zoho_cities','zoho_faculty','zoho_speciality','zoho_languages','zoho_universities','zoho_programs','zoho_campus'];
                $(this).prop('disabled',true).text('⏳ Syncing...');
                $('#sync-log').html('<p style="color:#6a9955;">// Starting full sync...</p>');
                for(const t of tables) { try { await syncTable(t); } catch(e){} }
                log('🎉 Sync complete!','success');
                $(this).prop('disabled',false).text('🔄 Sync All');
            });
            
            $('#clear-cache-btn').on('click', function() {
                $.post(ajaxurl, {action:'sit_manual_sync',task:'clear_cache',nonce:nonce}, function() {
                    log('🗑️ Cache cleared','success');
                });
            });
        });
        </script>
        <?php
    }
    
    public function handle_table_sync(): void
    {
        check_ajax_referer('sit_sync_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error('Unauthorized'); return; }
        
        $table = sanitize_text_field($_POST['table'] ?? '');
        if (empty($table)) { wp_send_json_error('No table'); return; }
        
        try {
            $result = $this->sync_table($table);
            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    private function sync_table(string $table): array
    {
        $supabase = new Supabase();
        $created = $updated = $skipped = 0;
        
        // Incremental sync - get last sync time
        $last_sync_key = 'sit_last_sync_' . $table;
        $last_sync = get_option($last_sync_key, null);
        
        $query = ['limit' => 500, 'order' => 'id.asc'];
        if ($last_sync) {
            $query['or'] = '(updated_at.gt.' . $last_sync . ',update_at.gt.' . $last_sync . ')';
        }
        
        $records = $supabase->get($table, $query, false);
        
        if (empty($records)) {
            update_option($last_sync_key, current_time('c'));
            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'message' => 'No new updates'];
        }
        
        $sync_endpoint = new \SIT\Search\Endpoints\SupabaseSyncEndpoint();
        
        foreach ($records as $record) {
            $request = new \WP_REST_Request('POST');
            $request->set_body(json_encode(['type' => 'UPDATE', 'table' => $table, 'record' => $record]));
            $request->set_header('Content-Type', 'application/json');
            
            $response = $sync_endpoint->handle_webhook($request);
            $data = $response->get_data();
            
            if (!empty($data['success']) && isset($data['result']['action'])) {
                if ($data['result']['action'] === 'created') $created++;
                else $updated++;
            } else {
                $skipped++;
            }
        }
        
        update_option($last_sync_key, current_time('c'));
        CachedData::clear_all();
        
        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped, 'total' => count($records)];
    }
    
    public function handle_sync_ajax(): void
    {
        check_ajax_referer('sit_sync_nonce', 'nonce');
        $task = $_POST['task'] ?? '';
        if ($task === 'clear_cache') {
            CachedData::clear_all();
            (new Supabase())->clear_cache();
            wp_send_json_success('Cleared');
        }
        wp_send_json_error('Unknown');
    }
}
