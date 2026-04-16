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
        add_action('wp_ajax_sit_reset_sync', [$this, 'handle_reset_sync']);
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
                            <th>Last Synced</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sync-tables">
                        <?php foreach ($tables as $table): 
                            $last_sync = get_option('sit_last_sync_' . $table[0], null);
                        ?>
                        <tr data-table="<?= esc_attr($table[0]) ?>">
                            <td><code><?= esc_html($table[0]) ?></code></td>
                            <td><?= esc_html($table[1]) ?> (<?= esc_html($table[2]) ?>)</td>
                            <td class="last-sync-col" style="font-size:11px;color:#666;">
                                <?= $last_sync ? esc_html($last_sync) : '<em>Never</em>' ?>
                            </td>
                            <td class="sync-status">Ready</td>
                            <td><button class="button sync-table-btn" data-table="<?= esc_attr($table[0]) ?>">Sync</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p style="margin-top: 20px;">
                    <button id="sync-programs-btn" class="button button-primary button-hero" style="background:#2271b1;margin-right:10px;">📚 Sync Changed Programs</button>
                    <button id="sync-all-btn" class="button button-hero" style="margin-right:10px;">🔄 Sync All Tables</button>
                    <button id="clear-cache-btn" class="button button-hero" style="margin-right:10px;">🗑️ Clear Cache</button>
                    <button id="reset-sync-btn" class="button button-hero" style="background:#dc3232;color:#fff;">⚠️ Reset Sync (Full Re-sync)</button>
                </p>
                
                <h3>Sync Log</h3>
                <div id="sync-log" style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:5px;max-height:400px;overflow-y:auto;font-family:monospace;font-size:12px;">
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
            
            function syncTable(table, currentOffset, aggregatedStats) {
                currentOffset = currentOffset || 0;
                aggregatedStats = aggregatedStats || {created:0, updated:0, skipped:0, retries:0, processed:0};
                return new Promise(function(resolve, reject) {
                    var $row = $('tr[data-table="'+table+'"]');
                    if (currentOffset === 0 && (!aggregatedStats.retries || aggregatedStats.retries === 0)) {
                        $row.find('.sync-status').text('Starting...').addClass('syncing');
                        $row.find('button').prop('disabled',true);
                        log('Syncing '+table+'...','info');
                    }
                    $row.find('.sync-status').text('Syncing... ('+aggregatedStats.processed+' done)');
                    
                    $.ajax({
                        url: ajaxurl, type: 'POST',
                        data: {action:'sit_sync_table', table:table, offset:currentOffset, nonce:nonce},
                        timeout: 60000,
                        success: function(r) {
                            aggregatedStats.retries = 0;
                            if(r.success) {
                                var d = r.data;
                                aggregatedStats.created += d.created;
                                aggregatedStats.updated += d.updated;
                                aggregatedStats.skipped += (d.skipped || 0);
                                aggregatedStats.processed += d.total;
                                
                                // Show debug info if available
                                if (d.debug) {
                                    log('  ↳ Supabase returned '+d.debug.fetched+' records (filter: '+(d.debug.filter || 'none')+')','info');
                                }
                                
                                if (d.done) {
                                    $row.find('button').prop('disabled',false);
                                    var t = aggregatedStats;
                                    var doneText = 'Done: '+t.created+' new, '+t.updated+' updated';
                                    if (t.skipped > 0) doneText += ', '+t.skipped+' skipped';
                                    doneText += ' ('+t.processed+' total)';
                                    $row.find('.sync-status').text(doneText).removeClass('syncing').addClass('success');
                                    log('✓ '+table+': '+t.created+' created, '+t.updated+' updated, '+t.processed+' total','success');
                                    resolve(r.data);
                                } else {
                                    log(table+': batch done — '+aggregatedStats.processed+' total so far ('+aggregatedStats.created+' new, '+aggregatedStats.updated+' upd)','info');
                                    syncTable(table, d.next_offset, aggregatedStats).then(resolve).catch(reject);
                                }
                            } else {
                                $row.find('button').prop('disabled',false);
                                var errMsg = typeof r.data === 'string' ? r.data : JSON.stringify(r.data);
                                $row.find('.sync-status').text('Error').removeClass('syncing').addClass('error');
                                log('✗ '+table+': '+errMsg,'error');
                                reject(r.data);
                            }
                        },
                        error: function(xhr) {
                            var errDetail = 'HTTP '+xhr.status+' — '+(xhr.responseText || '').substring(0, 200);
                            if (!aggregatedStats.retries) aggregatedStats.retries = 0;
                            if (aggregatedStats.retries < 3) {
                                aggregatedStats.retries++;
                                log('⚠ Retry '+aggregatedStats.retries+'/3 at record '+currentOffset+' ('+errDetail+')','error');
                                $row.find('.sync-status').text('Retrying ('+aggregatedStats.retries+'/3)...');
                                setTimeout(function() {
                                    syncTable(table, currentOffset, aggregatedStats).then(resolve).catch(reject);
                                }, 3000);
                            } else {
                                $row.find('button').prop('disabled',false);
                                $row.find('.sync-status').text('Failed at '+aggregatedStats.processed+' records').removeClass('syncing').addClass('error');
                                log('✗ '+table+' FAILED at record '+currentOffset+': '+errDetail,'error');
                                reject(errDetail);
                            }
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
                log('🎉 Full Sync complete!','success');
                $(this).prop('disabled',false).text('🔄 Sync All Tables');
            });
            
            $('#sync-programs-btn').on('click', async function() {
                $(this).prop('disabled',true).text('⏳ Syncing...');
                $('#sync-log').html('<p style="color:#6a9955;">// Checking for changed programs...</p>');
                try { await syncTable('zoho_programs'); } catch(e){}
                log('🎉 Program Sync complete!','success');
                $(this).prop('disabled',false).text('📚 Sync Changed Programs');
            });
            
            $('#clear-cache-btn').on('click', function() {
                $.post(ajaxurl, {action:'sit_manual_sync',task:'clear_cache',nonce:nonce}, function() {
                    log('🗑️ Cache cleared','success');
                });
            });
            
            $('#reset-sync-btn').on('click', function() {
                if (!confirm('This will reset ALL sync timestamps. Next sync will re-process ALL records from Supabase. Continue?')) return;
                $(this).prop('disabled',true);
                $.post(ajaxurl, {action:'sit_reset_sync',nonce:nonce}, function(r) {
                    if (r.success) {
                        log('⚠️ All sync timestamps reset. Click Sync to do a full re-sync.','success');
                        $('.sync-status').text('Ready (reset)');
                        $('.last-sync-col').html('<em>Never</em>');
                    } else {
                        log('Reset failed: '+JSON.stringify(r.data),'error');
                    }
                    $('#reset-sync-btn').prop('disabled',false);
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
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        
        if (empty($table)) { wp_send_json_error('No table specified'); return; }
        
        try {
            $result = $this->sync_table($table, $offset);
            wp_send_json_success($result);
        } catch (\Throwable $e) {
            wp_send_json_error('PHP Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
    }
    
    private function sync_table(string $table, int $offset = 0): array
    {
        @set_time_limit(300);
        
        $supabase = new Supabase();
        $created = $updated = $skipped = 0;
        
        // Incremental sync - get last sync time
        $last_sync_key = 'sit_last_sync_' . $table;
        $last_sync = get_option($last_sync_key, null);
        
        $sync_endpoint = new \SIT\Search\Endpoints\SupabaseSyncEndpoint();
        $page_size = 3; // Small batch to fit within 30s server limit
        
        // Tables with 'update_at' (no 'd'): universities, degrees, faculty, speciality
        // Tables with 'updated_at': programs, campus, countries, cities, languages
        $update_at_tables = ['zoho_universities', 'zoho_degrees', 'zoho_faculty', 'zoho_speciality'];
        $ts_col = in_array($table, $update_at_tables) ? 'update_at' : 'updated_at';
        
        // Build filter for changed records only
        $filter_query = [];
        $filter_desc = 'none (full sync)';
        if ($last_sync) {
            $filter_query['or'] = '(' . $ts_col . '.gt.' . $last_sync . ',created_at.gt.' . $last_sync . ')';
            $filter_desc = 'since ' . $last_sync . ' (col: ' . $ts_col . ')';
        }
        
        // Fetch this batch from Supabase
        $query = array_merge($filter_query, [
            'limit' => $page_size,
            'offset' => $offset,
            'order' => 'id.asc'
        ]);
        
        $records = $supabase->get($table, $query, false);
        
        // Handle Supabase connection errors
        if ($records === null) {
            return [
                'created' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0,
                'next_offset' => $offset, 'done' => true,
                'debug' => ['fetched' => 'ERROR (null response)', 'filter' => $filter_desc],
                'error' => 'Supabase returned null — check connection/API key',
            ];
        }
        
        $max_timestamp = null;
        $total_fetched = is_array($records) ? count($records) : 0;
        
        if (!empty($records) && is_array($records)) {
            foreach ($records as $record) {
                try {
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
                } catch (\Throwable $e) {
                    $skipped++;
                }
                
                // Track latest timestamp for incremental save
                $record_ts = $record[$ts_col] ?? $record['updated_at'] ?? $record['update_at'] ?? $record['created_at'] ?? null;
                if ($record_ts && (!$max_timestamp || $record_ts > $max_timestamp)) {
                    $max_timestamp = $record_ts;
                }
            }
        }
        
        $done = empty($records) || !is_array($records) || count($records) < $page_size;
        
        // Save progress after EVERY batch so resume works
        if ($max_timestamp) {
            update_option($last_sync_key, $max_timestamp);
        }
        
        if ($done) {
            // Use UTC to match Supabase timestamps
            update_option($last_sync_key, gmdate('c'));
            CachedData::clear_all();
        }
        
        return [
            'created' => $created, 
            'updated' => $updated, 
            'skipped' => $skipped, 
            'total' => $total_fetched,
            'next_offset' => $offset + $page_size,
            'done' => $done,
            'debug' => [
                'fetched' => $total_fetched,
                'filter' => $filter_desc,
            ],
        ];
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
    
    public function handle_reset_sync(): void
    {
        check_ajax_referer('sit_sync_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error('Unauthorized'); return; }
        
        $tables = ['zoho_degrees','zoho_countries','zoho_cities','zoho_faculty','zoho_speciality','zoho_languages','zoho_universities','zoho_programs','zoho_campus'];
        foreach ($tables as $table) {
            delete_option('sit_last_sync_' . $table);
        }
        CachedData::clear_all();
        (new Supabase())->clear_cache();
        wp_send_json_success('All sync timestamps reset');
    }
}
