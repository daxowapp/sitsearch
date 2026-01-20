<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\FeaturedUniversity;

class FeaturedUniversityAdmin {

    private $service;

    public function __construct() {
        $this->service = new FeaturedUniversity();
        add_action('admin_menu', [$this, 'add_menu_page'], 20); // Priority 20 to sit after main items
        add_action('wp_ajax_sit_save_featured', [$this, 'handle_save']);
        add_action('wp_ajax_sit_remove_featured', [$this, 'handle_remove']);
        add_action('wp_ajax_sit_search_universities', [$this, 'handle_search']);
    }

    public function add_menu_page() {
        add_submenu_page(
            'sit-search',
            'Featured Universities',
            '⭐ Featured Unis',
            'manage_options',
            'sit-featured-universities',
            [$this, 'render_page']
        );
    }

    public function render_page() {
        $featured = $this->service->getFeatured();
        ?>
        <div class="wrap sit-admin-wrap">
            <h1 class="wp-heading-inline">⭐ Featured Universities</h1>
            <p class="description">Manage universities that appear at the top of search results.</p>
            <hr class="wp-header-end">

            <div class="sit-featured-container">
                
                <!-- Add New Section -->
                <div class="card sit-add-new-card">
                    <h2>Add Featured University</h2>
                    <div class="sit-form-row">
                        <select id="sit-uni-search" style="width: 300px;" placeholder="Search university..."></select>
                        <input type="number" id="sit-new-priority" placeholder="Priority (1-10)" min="1" max="10" value="5" class="small-text">
                        <input type="date" id="sit-new-expiry" placeholder="Expiry Date">
                        <button class="button button-primary" id="sit-add-btn">Add to Featured</button>
                    </div>
                </div>

                <!-- List Section -->
                <div class="card sit-list-card" style="margin-top: 20px;">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>University</th>
                                <th width="100">Status</th>
                                <th width="100">Priority</th>
                                <th width="150">Expires</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sit-featured-list">
                            <?php if (empty($featured)): ?>
                                <tr><td colspan="5" style="text-align:center;">No featured universities yet.</td></tr>
                            <?php else: 
                                foreach ($featured as $uni): 
                                    $details = $this->service->getDetails($uni->ID);
                                    $is_active = get_post_meta($uni->ID, 'Active_in_Search', true);
                                    $active_display = ($is_active == '1' || $is_active === 'true') 
                                        ? '<span style="color:green;font-weight:bold;">Active</span>' 
                                        : '<span style="color:red;font-weight:bold;">⚠ Inactive</span>';
                            ?>
                                <tr>
                                    <td><?= $uni->ID ?></td>
                                    <td>
                                        <strong><?= esc_html($uni->post_title) ?></strong> 
                                        <a href="<?= get_edit_post_link($uni->ID) ?>" target="_blank">Edit</a>
                                        <?php if($active_display == '<span style="color:red;font-weight:bold;">⚠ Inactive</span>'): ?>
                                            <br><small style="color:red;">(Won't appear in search results!)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $active_display ?></td>
                                    <td><?= $details['priority'] ?></td>
                                    <td><?= $details['expiry'] ? date('M j, Y', strtotime($details['expiry'])) : '<span style="color:#aaa">Never</span>' ?></td>
                                    <td>
                                        <button class="button button-small button-link-delete sit-remove-btn" data-id="<?= $uni->ID ?>">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <style>
            .sit-admin-wrap .card { padding: 15px; max-width: 100%; box-sizing: border-box; }
            .sit-form-row { display: flex; gap: 10px; align-items: center; }
            /* Select2 adjustments if needed */
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Initialize Select2 for searching posts
            $('#sit-uni-search').select2({
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            action: 'sit_search_universities',
                            nonce: '<?= wp_create_nonce("sit_featured_nonce") ?>'
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.data
                        };
                    },
                    cache: true
                },
                placeholder: 'Search for a university',
                minimumInputLength: 2
            });

            // Add new featured
            $('#sit-add-btn').on('click', function() {
                var uni_id = $('#sit-uni-search').val();
                var priority = $('#sit-new-priority').val();
                var expiry = $('#sit-new-expiry').val();

                if(!uni_id) { alert('Please select a university'); return; }

                $(this).prop('disabled', true).text('Adding...');

                $.post(ajaxurl, {
                    action: 'sit_save_featured',
                    id: uni_id,
                    priority: priority,
                    expiry: expiry,
                    nonce: '<?= wp_create_nonce("sit_featured_nonce") ?>'
                }, function(res) {
                    if(res.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + res.data);
                        $('#sit-add-btn').prop('disabled', false).text('Add to Featured');
                    }
                });
            });

            // Remove
            $('.sit-remove-btn').on('click', function() {
                if(!confirm('Are you sure?')) return;
                var btn = $(this);
                var id = btn.data('id');

                $.post(ajaxurl, {
                    action: 'sit_remove_featured',
                    id: id,
                    nonce: '<?= wp_create_nonce("sit_featured_nonce") ?>'
                }, function(res) {
                    if(res.success) {
                        btn.closest('tr').fadeOut();
                    } else {
                        alert('Error');
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function handle_search() {
        check_ajax_referer('sit_featured_nonce', 'nonce');
        $q = sanitize_text_field($_REQUEST['q']);
        $posts = get_posts([
            'post_type' => 'sit-university',
            's' => $q,
            'posts_per_page' => 10
        ]);

        $results = [];
        foreach($posts as $p) {
            $results[] = ['id' => $p->ID, 'text' => $p->post_title . ' (ID: ' . $p->ID . ')'];
        }
        wp_send_json_success($results);
    }

    public function handle_save() {
        check_ajax_referer('sit_featured_nonce', 'nonce');
        if(!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $id = intval($_POST['id']);
        $priority = intval($_POST['priority']);
        $expiry = sanitize_text_field($_POST['expiry']);

        $this->service->setFeatured($id, $priority, $expiry);
        wp_send_json_success();
    }

    public function handle_remove() {
        check_ajax_referer('sit_featured_nonce', 'nonce');
        if(!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
        
        $id = intval($_POST['id']);
        $this->service->removeFeatured($id);
        wp_send_json_success();
    }
}
