<?php

namespace SIT\Search\Actions;

class UniversityMediaAdmin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page'], 35);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_sit_save_uni_media', [$this, 'handle_save_media']);
    }

    public function add_menu_page() {
        add_submenu_page(
            'sit-search',
            'University Media',
            'University Media',
            'manage_options',
            'sit-university-media',
            [$this, 'render_page']
        );
    }

    public function enqueue_scripts($hook) {
        if (isset($_GET['page']) && $_GET['page'] === 'sit-university-media') {
            wp_enqueue_media();
        }
    }

    public function render_page() {
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
            'lang' => '', // Get all languages
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
        <div class="wrap sit-media-admin-wrap">
            <h1 class="wp-heading-inline">University Media Management</h1>
            <p class="description">Upload and manage University Logos and Cover Banners. All changes apply across all translations simultaneously.</p>
            <hr class="wp-header-end">

            <div class="card sit-list-card" style="margin-top: 20px; padding: 0; max-width: 100%;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="300">University Name</th>
                            <th width="150" style="text-align: center;">Languages</th>
                            <th width="150">Logo Status</th>
                            <th width="150">Banner Status</th>
                            <th>Actions (Logo & Banner)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($universities)): ?>
                            <tr><td colspan="5" style="text-align:center;">No universities found.</td></tr>
                        <?php else: 
                            foreach ($universities as $uni): 
                                if (in_array($uni->ID, $processed_ids)) continue;

                                $group_ids = [$uni->ID];
                                
                                // Group by Polylang
                                if (function_exists('pll_get_post_translations')) {
                                    $trans = pll_get_post_translations($uni->ID);
                                    if (!empty($trans)) {
                                        $group_ids = array_values($trans);
                                    }
                                }

                                // Fallback: Group by Zoho Account ID
                                if (count($group_ids) <= 1) {
                                    $z_id = get_post_meta($uni->ID, 'zoho_account_id', true);
                                    if ($z_id) {
                                        $same_zoho_posts = get_posts([
                                            'post_type' => 'sit-university',
                                            'meta_key' => 'zoho_account_id',
                                            'meta_value' => $z_id,
                                            'posts_per_page' => -1,
                                            'fields' => 'ids',
                                            'post_status' => 'any',
                                            'lang' => ''
                                        ]);
                                        if (!empty($same_zoho_posts)) {
                                            $group_ids = array_unique(array_merge($group_ids, $same_zoho_posts));
                                        }
                                    }
                                }

                                foreach($group_ids as $gid) $processed_ids[] = (int)$gid;

                                // Fetch existing images (using the first ID's data as representative)
                                $uni_logo = get_post_meta($group_ids[0], 'uni_logo', true);
                                $uni_image = get_post_meta($group_ids[0], 'uni_image', true);
                                
                                $has_logo = (!empty($uni_logo) && $uni_logo !== 'null');
                                $has_image = (!empty($uni_image) && $uni_image !== 'null');
                        ?>
                            <tr data-ids="<?= esc_attr(implode(',', $group_ids)) ?>">
                                <td>
                                    <strong><a href="<?= get_edit_post_link($group_ids[0]) ?>" target="_blank"><?= esc_html($uni->post_title) ?></a></strong>
                                </td>
                                <td style="text-align:center;">
                                    <?php foreach($group_ids as $gid): 
                                        $lang = function_exists('pll_get_post_language') ? pll_get_post_language($gid) : '-';
                                    ?>
                                        <span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:11px; margin:0 2px;">
                                            <?= strtoupper($lang) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if ($has_logo): ?>
                                        <img src="<?= esc_url($uni_logo) ?>" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; background: #fff;" />
                                    <?php else: ?>
                                        <span style="color: #d32f2f; font-weight: bold;"><span class="dashicons dashicons-warning" style="vertical-align: middle;"></span> Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($has_image): ?>
                                        <img src="<?= esc_url($uni_image) ?>" style="width: 80px; height: 40px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;" />
                                    <?php else: ?>
                                        <span style="color: #d32f2f; font-weight: bold;"><span class="dashicons dashicons-warning" style="vertical-align: middle;"></span> Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex; gap: 10px; align-items:center;">
                                        <button class="button sit-upload-btn" data-type="uni_logo" data-val="<?= esc_attr($uni_logo === 'null' ? '' : $uni_logo) ?>">
                                            <?= $has_logo ? 'Change Logo' : 'Upload Logo' ?>
                                        </button>
                                        <button class="button sit-upload-btn" data-type="uni_image" data-val="<?= esc_attr($uni_image === 'null' ? '' : $uni_image) ?>">
                                            <?= $has_image ? 'Change Banner' : 'Upload Banner' ?>
                                        </button>
                                        <span class="sit-save-indicator dashicons dashicons-saved" style="color: green; display: none;"></span>
                                        <span class="sit-spinner spinner" style="float: none; margin: 0;"></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.sit-upload-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var row = btn.closest('tr');
                var ids = row.data('ids');
                var type = btn.data('type'); // 'uni_logo' or 'uni_image'
                var titleText = type === 'uni_logo' ? 'Select Logo' : 'Select Banner';
                
                var custom_uploader = wp.media({
                    title: titleText,
                    button: { text: 'Use this image' },
                    multiple: false
                }).on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    var url = attachment.url;
                    
                    // Show spinner
                    row.find('.sit-spinner').addClass('is-active');
                    row.find('.sit-save-indicator').hide();
                    
                    $.post(ajaxurl, {
                        action: 'sit_save_uni_media',
                        nonce: '<?= wp_create_nonce("sit_media_nonce") ?>',
                        ids: ids,
                        type: type,
                        url: url
                    }, function(res) {
                        row.find('.sit-spinner').removeClass('is-active');
                        if (res.success) {
                            row.find('.sit-save-indicator').fadeIn().delay(2000).fadeOut();
                            btn.data('val', url);
                            // Visual update requires a page reload for the easiest robust DOM change, 
                            // or we can just reload dynamically. Reload is safer to ensure state sync.
                            setTimeout(function() { location.reload(); }, 500);
                        } else {
                            alert('Error: ' + res.data);
                        }
                    }).fail(function() {
                        row.find('.sit-spinner').removeClass('is-active');
                        alert('Server error saving media.');
                    });
                }).open();
            });
        });
        </script>
        <?php
    }

    public function handle_save_media() {
        check_ajax_referer('sit_media_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $ids_raw = $_POST['ids'] ?? '';
        $type = $_POST['type'] ?? '';
        $url = $_POST['url'] ?? '';

        if (empty($ids_raw) || empty($type) || !in_array($type, ['uni_logo', 'uni_image'])) {
            wp_send_json_error('Invalid parameters');
        }

        $ids = explode(',', $ids_raw);
        $updated = 0;

        foreach ($ids as $id) {
            $id = (int)trim($id);
            if ($id > 0) {
                // Remove trailing ?v= cachebusters if any
                $url = strtok($url, '?');
                update_post_meta($id, $type, esc_url_raw($url));
                $updated++;
            }
        }

        // Clear public interface caches so changes instantly appear in Search / Pages
        \SIT\Search\Services\CachedData::clear_university_cache();

        wp_send_json_success("Updated $updated translations successfully.");
    }
}
