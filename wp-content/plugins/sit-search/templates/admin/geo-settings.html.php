<?php
/**
 * GEO Settings Admin Page Template
 *
 * @since 1.3.0
 */

if (!defined('ABSPATH')) exit;

$status = \SIT\Search\Actions\GeoSettingsAdmin::getStatus();
$api_key = get_option('sit_openrouter_api_key', '');
$model = get_option('sit_geo_faq_model', 'google/gemini-2.0-flash');
$has_key = !empty($api_key);
$table_exists = !empty($status['table_exists']);
$completion_pct = $status['total_programs'] > 0
    ? round(($status['programs_with_faqs'] / $status['total_programs']) * 100, 1)
    : 0;
?>
<div class="wrap sit-geo-settings">
    <h1>
        <span class="dashicons dashicons-chart-area" style="font-size: 28px; margin-right: 8px; color: #9C27B0;"></span>
        GEO — Generative Engine Optimization
    </h1>
    <p class="description" style="font-size: 14px; margin-bottom: 24px;">
        Generate AI-powered FAQs for every program to maximize visibility in ChatGPT, Perplexity, Claude, and Gemini search results.
    </p>

    <?php if (!$table_exists): ?>
    <!-- Database Table Missing Warning -->
    <div class="sit-geo-table-warning" style="background: #fff3cd; border: 1px solid #ffc107; border-left: 4px solid #ff9800; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <h3 style="margin: 0 0 6px; color: #856404; font-size: 16px;">
                <span class="dashicons dashicons-warning" style="color: #ff9800; margin-right: 4px;"></span>
                Database Table Not Found
            </h3>
            <p style="margin: 0; color: #856404; font-size: 13px;">
                The <code>program_faqs</code> table does not exist in your database. This table is required to store AI-generated FAQs.
                Click the button to create it now.
            </p>
        </div>
        <button type="button" id="geo-create-table-btn" class="button button-primary" style="background: #ff9800; border-color: #e68a00; font-size: 14px; padding: 8px 24px; height: auto; white-space: nowrap;">
            <span class="dashicons dashicons-database-add" style="margin-right: 4px; line-height: 1.4;"></span>
            Create Table Now
        </button>
    </div>
    <script>
    jQuery(function($) {
        $('#geo-create-table-btn').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).text('Creating...');
            $.post(ajaxurl, {
                action: 'sit_geo_create_table',
                nonce: '<?= wp_create_nonce('sit_geo_nonce') ?>'
            }, function(resp) {
                if (resp.success) {
                    btn.css({background: '#4caf50', borderColor: '#388e3c'}).text('✓ Table Created!');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    btn.prop('disabled', false).text('Retry').css({background: '#f44336', borderColor: '#d32f2f'});
                    alert('Error: ' + (resp.data || 'Unknown error'));
                }
            }).fail(function() {
                btn.prop('disabled', false).text('Retry');
                alert('Network error. Please try again.');
            });
        });
    });
    </script>
    <?php endif; ?>

    <?php if ($table_exists): ?>

    <!-- Status Cards -->
    <div class="sit-geo-status-grid">
        <div class="sit-geo-status-card">
            <div class="sit-geo-status-number"><?= number_format($status['total_programs']) ?></div>
            <div class="sit-geo-status-label">Total Programs</div>
        </div>
        <div class="sit-geo-status-card sit-geo-status-success">
            <div class="sit-geo-status-number" id="geo-programs-done"><?= number_format($status['programs_with_faqs']) ?></div>
            <div class="sit-geo-status-label">With FAQs</div>
        </div>
        <div class="sit-geo-status-card sit-geo-status-warning">
            <div class="sit-geo-status-number" id="geo-programs-pending"><?= number_format($status['programs_pending']) ?></div>
            <div class="sit-geo-status-label">Pending</div>
        </div>
        <div class="sit-geo-status-card">
            <div class="sit-geo-status-number" id="geo-total-faqs"><?= number_format($status['total_faqs']) ?></div>
            <div class="sit-geo-status-label">Total FAQs</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="sit-geo-progress-wrapper">
        <div class="sit-geo-progress-bar">
            <div class="sit-geo-progress-fill" style="width: <?= $completion_pct ?>%"></div>
        </div>
        <span class="sit-geo-progress-text"><?= $completion_pct ?>% complete</span>
    </div>

    <!-- Settings Form -->
    <div class="sit-geo-settings-grid">
        <div class="sit-geo-settings-section">
            <h2>API Configuration</h2>
            <form method="post" action="options.php">
                <?php settings_fields('sit_geo_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="sit_openrouter_api_key">OpenRouter API Key</label></th>
                        <td>
                            <input type="password" name="sit_openrouter_api_key" id="sit_openrouter_api_key"
                                   value="<?= esc_attr($api_key) ?>"
                                   class="regular-text" placeholder="sk-or-v1-..." />
                            <p class="description">Get your API key from <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a></p>
                            <?php if ($has_key): ?>
                                <span class="sit-geo-key-status sit-geo-key-ok">✓ Key configured</span>
                            <?php else: ?>
                                <span class="sit-geo-key-status sit-geo-key-missing">✗ Key not set</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sit_geo_faq_model">LLM Model</label></th>
                        <td>
                            <select name="sit_geo_faq_model" id="sit_geo_faq_model">
                                <option value="google/gemini-2.0-flash" <?php selected($model, 'google/gemini-2.0-flash'); ?>>Google Gemini 2.0 Flash (Recommended)</option>
                                <option value="google/gemini-2.5-flash-preview" <?php selected($model, 'google/gemini-2.5-flash-preview'); ?>>Google Gemini 2.5 Flash Preview</option>
                                <option value="meta-llama/llama-4-maverick" <?php selected($model, 'meta-llama/llama-4-maverick'); ?>>Meta Llama 4 Maverick</option>
                                <option value="anthropic/claude-3.5-sonnet" <?php selected($model, 'anthropic/claude-3.5-sonnet'); ?>>Anthropic Claude 3.5 Sonnet</option>
                                <option value="openai/gpt-4o-mini" <?php selected($model, 'openai/gpt-4o-mini'); ?>>OpenAI GPT-4o Mini</option>
                            </select>
                            <p class="description">Model used for FAQ generation. Gemini 2.0 Flash offers the best cost/quality ratio.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>

        <div class="sit-geo-settings-section">
            <h2>Generate FAQs</h2>
            <p class="description">Generate 100 AI-powered FAQs for a specific program or test the pipeline.</p>

            <div class="sit-geo-generate-form">
                <label for="geo-program-id"><strong>Program Post ID:</strong></label>
                <input type="number" id="geo-program-id" placeholder="e.g. 12345" class="regular-text" />
                <br style="margin-bottom: 12px;" />
                <button type="button" class="button button-primary" id="geo-generate-btn" <?= $has_key ? '' : 'disabled' ?>>
                    <span class="dashicons dashicons-admin-generic" style="margin-top: 3px;"></span>
                    Generate FAQs
                </button>
                <button type="button" class="button" id="geo-regenerate-btn" <?= $has_key ? '' : 'disabled' ?>>
                    <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                    Regenerate FAQs
                </button>
            </div>
            <div id="geo-generate-status" class="sit-geo-generate-status" style="display:none;"></div>
        </div>
    </div>

    <!-- Batch Generation -->
    <div class="sit-geo-settings-section" style="margin-top: 24px;">
        <h2>
            <span class="dashicons dashicons-superhero" style="color: #9C27B0;"></span>
            Batch Generate All FAQs
        </h2>
        <p class="description">Generate FAQs for ALL pending programs automatically. Processes 1 program at a time. You can stop and resume anytime — already generated programs will be skipped.</p>

        <div style="margin-top: 16px; display: flex; gap: 10px; align-items: center;">
            <button type="button" class="button button-primary button-hero" id="geo-batch-start" <?= $has_key ? '' : 'disabled' ?> style="background: #9C27B0; border-color: #7B1FA2;">
                🚀 Generate All FAQs (<?= number_format($status['programs_pending']) ?> pending)
            </button>
            <button type="button" class="button button-hero" id="geo-batch-stop" style="display:none; background: #dc3232; color: white; border-color: #c62828;">
                ⏹ Stop Generation
            </button>
        </div>

        <!-- Batch Progress -->
        <div id="geo-batch-progress" style="display:none; margin-top: 16px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span id="geo-batch-counter" style="font-weight: 600;">Processing...</span>
                <span id="geo-batch-eta" style="color: #666;"></span>
            </div>
            <div class="sit-geo-progress-bar">
                <div class="sit-geo-progress-fill" id="geo-batch-fill" style="width: 0%; background: linear-gradient(90deg, #9C27B0, #E040FB);"></div>
            </div>
        </div>

        <!-- Batch Log -->
        <div id="geo-batch-log" style="display:none; margin-top: 16px; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; max-height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6;">
            <div style="color: #6a9955;">// Ready to generate FAQs...</div>
        </div>
    </div>

    <!-- GEO Checklist -->
    <div class="sit-geo-settings-section" style="margin-top: 24px;">
        <h2>GEO Optimization Checklist</h2>
        <div class="sit-geo-checklist">
            <div class="sit-geo-check-item sit-geo-check-done">
                <span class="dashicons dashicons-yes-alt"></span>
                <span>FAQPage JSON-LD Schema on every program page</span>
            </div>
            <div class="sit-geo-check-item sit-geo-check-done">
                <span class="dashicons dashicons-yes-alt"></span>
                <span>Course schema with provider, duration, tuition</span>
            </div>
            <div class="sit-geo-check-item sit-geo-check-done">
                <span class="dashicons dashicons-yes-alt"></span>
                <span>AI crawlers allowed in robots.txt (GPTBot, PerplexityBot, Claude-Web, etc.)</span>
            </div>
            <div class="sit-geo-check-item sit-geo-check-done">
                <span class="dashicons dashicons-yes-alt"></span>
                <span>llms.txt endpoint for machine-readable site summary</span>
            </div>
            <div class="sit-geo-check-item sit-geo-check-done">
                <span class="dashicons dashicons-yes-alt"></span>
                <span>Entity-rich meta descriptions with timestamps</span>
            </div>
            <div class="sit-geo-check-item sit-geo-check-done">
                <span class="dashicons dashicons-yes-alt"></span>
                <span>BreadcrumbList schema on all pages</span>
            </div>
            <div class="sit-geo-check-item <?= $status['programs_with_faqs'] > 0 ? 'sit-geo-check-done' : 'sit-geo-check-pending' ?>">
                <span class="dashicons <?= $status['programs_with_faqs'] > 0 ? 'dashicons-yes-alt' : 'dashicons-clock' ?>"></span>
                <span>AI-generated FAQs (100 per program)</span>
            </div>
        </div>
    </div>
</div>

<style>
.sit-geo-settings { max-width: 1200px; }

/* Hide WP admin footer on this page — it overlaps our buttons */
.sit-geo-settings ~ #wpfooter,
#wpfooter {
    display: none !important;
}
#wpcontent { padding-bottom: 40px; }

.sit-geo-status-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.sit-geo-status-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.sit-geo-status-card.sit-geo-status-success { border-left: 4px solid #4CAF50; }
.sit-geo-status-card.sit-geo-status-warning { border-left: 4px solid #FF9800; }

.sit-geo-status-number {
    font-size: 32px;
    font-weight: 700;
    color: #1d2327;
    line-height: 1.2;
}
.sit-geo-status-label {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

.sit-geo-progress-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}
.sit-geo-progress-bar {
    flex: 1;
    height: 12px;
    background: #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
}
.sit-geo-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #8BC34A);
    border-radius: 6px;
    transition: width 0.5s ease;
}
.sit-geo-progress-text {
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
    white-space: nowrap;
}

.sit-geo-settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.sit-geo-settings-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
}
.sit-geo-settings-section h2 {
    margin-top: 0;
    font-size: 18px;
    border-bottom: 1px solid #eee;
    padding-bottom: 12px;
}

.sit-geo-key-status {
    display: inline-block;
    margin-top: 6px;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}
.sit-geo-key-ok { background: #e8f5e9; color: #2e7d32; }
.sit-geo-key-missing { background: #fbe9e7; color: #c62828; }

.sit-geo-generate-form { margin-top: 16px; }
.sit-geo-generate-form label { display: block; margin-bottom: 6px; }
.sit-geo-generate-form input { margin-bottom: 12px; }
.sit-geo-generate-form .button { margin-right: 8px; }

.sit-geo-generate-status {
    margin-top: 16px;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 13px;
}
.sit-geo-generate-status.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.sit-geo-generate-status.error { background: #fbe9e7; color: #c62828; border: 1px solid #ffcdd2; }
.sit-geo-generate-status.loading { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

.sit-geo-checklist { display: flex; flex-direction: column; gap: 8px; }
.sit-geo-check-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
}
.sit-geo-check-done { background: #e8f5e9; }
.sit-geo-check-done .dashicons { color: #4CAF50; }
.sit-geo-check-pending { background: #fff3e0; }
.sit-geo-check-pending .dashicons { color: #FF9800; }

@media (max-width: 960px) {
    .sit-geo-status-grid { grid-template-columns: repeat(2, 1fr); }
    .sit-geo-settings-grid { grid-template-columns: 1fr; }
}
</style>

<script>
jQuery(document).ready(function($) {
    var nonce = '<?= wp_create_nonce('sit_geo_nonce') ?>';
    var batchRunning = false;
    var batchStartTime = null;
    var batchProcessed = 0;
    var batchFailed = 0;
    var batchTotalFaqs = 0;
    var PARALLEL_WORKERS = 3;
    var activeWorkers = 0;
    var doneSignalSent = false;

    function showStatus(msg, type) {
        var $el = $('#geo-generate-status');
        $el.removeClass('success error loading').addClass(type).text(msg).show();
    }

    function batchLog(msg, type) {
        type = type || 'info';
        var colors = { info: '#569cd6', success: '#4ec9b0', error: '#f14c4c', warn: '#dcdcaa' };
        var time = new Date().toLocaleTimeString();
        var $log = $('#geo-batch-log');
        $log.append('<div><span style="color:#808080">[' + time + ']</span> <span style="color:' + (colors[type] || '#d4d4d4') + '">' + msg + '</span></div>');
        $log.scrollTop($log[0].scrollHeight);
    }

    function updateBatchUI(stats) {
        if (!stats) return;
        $('#geo-programs-done').text(stats.programs_with_faqs.toLocaleString());
        $('#geo-programs-pending').text(stats.programs_pending.toLocaleString());
        $('#geo-total-faqs').text(stats.total_faqs.toLocaleString());
        var pct = stats.total_programs > 0
            ? ((stats.programs_with_faqs / stats.total_programs) * 100).toFixed(1) : 0;
        $('.sit-geo-progress-fill').not('#geo-batch-fill').css('width', pct + '%');
        $('.sit-geo-progress-text').text(pct + '% complete');

        var batchPct = stats.total_programs > 0
            ? ((stats.programs_with_faqs / stats.total_programs) * 100).toFixed(1) : 0;
        $('#geo-batch-fill').css('width', batchPct + '%');
        $('#geo-batch-counter').text(
            stats.programs_with_faqs.toLocaleString() + ' / ' + stats.total_programs.toLocaleString() + ' programs done'
        );

        if (batchStartTime && batchProcessed > 0) {
            var elapsed = (Date.now() - batchStartTime) / 1000;
            var throughput = batchProcessed / elapsed; // programs per second
            var remaining = stats.programs_pending;
            var etaSec = Math.round(remaining / Math.max(throughput, 0.001));
            var etaStr = '';
            if (etaSec > 3600) {
                etaStr = Math.floor(etaSec / 3600) + 'h ' + Math.floor((etaSec % 3600) / 60) + 'm';
            } else if (etaSec > 60) {
                etaStr = Math.floor(etaSec / 60) + 'm ' + (etaSec % 60) + 's';
            } else {
                etaStr = etaSec + 's';
            }
            var perMin = (throughput * 60).toFixed(1);
            $('#geo-batch-eta').text('ETA: ~' + etaStr + ' | ' + perMin + ' prog/min | ' + activeWorkers + ' workers');
        }
    }

    var batchProgramsCompleted = 0;

    function workerLoop(wid) {
        if (!batchRunning) { activeWorkers--; checkAllDone(); return; }

        $.post(ajaxurl, {
            action: 'sit_geo_batch_generate',
            nonce: nonce
        }, function(resp) {
            if (!batchRunning) { activeWorkers--; checkAllDone(); return; }

            if (!resp.success) {
                batchLog('[W' + wid + '] ✗ ' + (typeof resp.data === 'string' ? resp.data : JSON.stringify(resp.data)), 'error');
                batchFailed++;
                if (batchRunning && batchFailed < 15) {
                    setTimeout(function() { workerLoop(wid); }, 3000);
                } else {
                    activeWorkers--;
                    if (batchFailed >= 15) stopBatch('Too many errors. Stopped.');
                    checkAllDone();
                }
                return;
            }

            var d = resp.data;

            if (d.done) {
                activeWorkers--;
                if (!doneSignalSent) {
                    doneSignalSent = true;
                    var msg = d.stopped ? '⏹ Stopped by user.' : '🎉 All programs now have FAQs!';
                    batchLog(msg, 'success');
                    batchLog('📊 Session: ' + batchProgramsCompleted + ' programs, ' + batchTotalFaqs + ' FAQs', 'info');
                    if (d.stats) updateBatchUI(d.stats);
                }
                checkAllDone();
                return;
            }

            batchProcessed++;
            batchFailed = 0;

            if (d.success) {
                batchTotalFaqs += (d.faqs_generated || 0);
                var nameLink = d.program_url
                    ? '<a href="' + d.program_url + '" target="_blank" style="color:inherit;text-decoration:underline;text-underline-offset:2px">' + d.program_name + '</a>'
                    : d.program_name;
                if (d.program_complete) {
                    batchProgramsCompleted++;
                    batchLog('[W' + wid + '] ✓ [' + batchProgramsCompleted + '] #' + d.post_id + ' — ' + nameLink + ' ✅ (' + d.total_faqs_for_program + ' FAQs)', 'success');
                } else {
                    batchLog('[W' + wid + '] ↳ #' + d.post_id + ' R' + d.round + ' +' + d.faqs_generated + ' FAQs (' + d.total_faqs_for_program + ' total)', 'info');
                }
            } else {
                batchLog('[W' + wid + '] ⚠ #' + d.post_id + ' — ' + (d.message || 'Failed'), 'warn');
            }

            if (d.stats) updateBatchUI(d.stats);
            workerLoop(wid);
        }).fail(function(xhr) {
            batchFailed++;
            batchLog('[W' + wid + '] ✗ HTTP ' + xhr.status + ' — retrying 5s...', 'error');
            if (batchRunning && batchFailed < 15) {
                setTimeout(function() { workerLoop(wid); }, 5000);
            } else {
                activeWorkers--;
                checkAllDone();
            }
        });
    }

    function checkAllDone() {
        if (activeWorkers <= 0 && batchRunning) stopBatch(null, true);
    }

    function stopBatch(errorMsg, silent) {
        batchRunning = false;
        $('#geo-batch-start').show().prop('disabled', false);
        $('#geo-batch-stop').hide();
        if (errorMsg) batchLog(errorMsg, 'error');
    }

    // Batch Start — launches parallel workers
    $('#geo-batch-start').on('click', function() {
        if (!confirm('Start generating FAQs for ~' + <?= $status['programs_pending'] ?> + ' programs with ' + PARALLEL_WORKERS + ' parallel workers?')) return;

        batchRunning = true;
        batchStartTime = Date.now();
        batchProcessed = 0;
        batchFailed = 0;
        batchTotalFaqs = 0;
        batchProgramsCompleted = 0;
        doneSignalSent = false;
        activeWorkers = PARALLEL_WORKERS;

        $(this).hide();
        $('#geo-batch-stop').show();
        $('#geo-batch-progress').show();
        $('#geo-batch-log').show().html('<div style="color:#6a9955">// Starting batch FAQ generation...</div>');

        batchLog('🚀 Launching ' + PARALLEL_WORKERS + ' parallel workers for ~' + <?= $status['programs_pending'] ?> + ' programs', 'info');
        batchLog('Model: <?= esc_js($model) ?> | Key: ****' + '<?= esc_js(substr($api_key, -4)) ?>', 'info');

        for (var i = 1; i <= PARALLEL_WORKERS; i++) {
            workerLoop(i);
        }
    });

    // Batch Stop
    $('#geo-batch-stop').on('click', function() {
        if (!confirm('Stop FAQ generation? Progress is saved — you can resume anytime.')) return;
        $(this).prop('disabled', true).text('⏳ Stopping...');
        batchRunning = false;

        $.post(ajaxurl, { action: 'sit_geo_stop_batch', nonce: nonce }, function() {
            batchLog('⏹ Stop requested. Finishing current program...', 'warn');
            $('#geo-batch-stop').hide();
            $('#geo-batch-start').show().prop('disabled', false);
        });
    });

    // Single Generate
    $('#geo-generate-btn').on('click', function() {
        var postId = $('#geo-program-id').val();
        if (!postId) { alert('Enter a program post ID'); return; }

        showStatus('Generating 100 FAQs... This may take 30-60 seconds.', 'loading');
        $(this).prop('disabled', true);

        $.post(ajaxurl, {
            action: 'sit_geo_generate_faqs',
            nonce: nonce,
            post_id: postId
        }, function(resp) {
            $('#geo-generate-btn').prop('disabled', false);
            if (resp.success) {
                showStatus('✓ ' + resp.data.message, 'success');
                refreshStatus();
            } else {
                showStatus('✗ Error: ' + resp.data, 'error');
            }
        }).fail(function() {
            $('#geo-generate-btn').prop('disabled', false);
            showStatus('✗ Request failed', 'error');
        });
    });

    // Single Regenerate
    $('#geo-regenerate-btn').on('click', function() {
        var postId = $('#geo-program-id').val();
        if (!postId) { alert('Enter a program post ID'); return; }
        if (!confirm('This will DELETE existing FAQs for this program and regenerate. Continue?')) return;

        showStatus('Regenerating FAQs...', 'loading');
        $(this).prop('disabled', true);

        $.post(ajaxurl, {
            action: 'sit_geo_regenerate_program',
            nonce: nonce,
            post_id: postId
        }, function(resp) {
            $('#geo-regenerate-btn').prop('disabled', false);
            if (resp.success) {
                showStatus('✓ ' + resp.data.message, 'success');
                refreshStatus();
            } else {
                showStatus('✗ Error: ' + resp.data, 'error');
            }
        }).fail(function() {
            $('#geo-regenerate-btn').prop('disabled', false);
            showStatus('✗ Request failed', 'error');
        });
    });

    function refreshStatus() {
        $.post(ajaxurl, { action: 'sit_geo_get_status', nonce: nonce }, function(resp) {
            if (resp.success) updateBatchUI(resp.data);
        });
    }
});
</script>

<?php endif; // $table_exists ?>
</div><!-- /.wrap -->
