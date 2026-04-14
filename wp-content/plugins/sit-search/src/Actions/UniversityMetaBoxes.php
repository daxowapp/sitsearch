<?php

namespace SIT\Search\Actions;

class UniversityMetaBoxes
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_sit-university', [$this, 'save_meta_boxes']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'sit_university_media',
            __('University Media (Logo & Banner)', 'sit-search'),
            [$this, 'render_media_meta_box'],
            'sit-university',
            'side',
            'high'
        );
    }

    public function render_media_meta_box($post)
    {
        wp_nonce_field('sit_university_media_save', 'sit_university_media_nonce');

        $uni_logo = get_post_meta($post->ID, 'uni_logo', true);
        $uni_image = get_post_meta($post->ID, 'uni_image', true);

        // Logo field.
        echo '<div style="margin-bottom: 20px;">';
        echo '<label style="display:block; margin-bottom: 5px; font-weight: bold;">' . esc_html__('University Logo URL', 'sit-search') . '</label>';
        echo '<div class="sit-image-uploader" style="display:flex; flex-direction:column; gap:8px;">';
        if ($uni_logo && $uni_logo !== 'null') {
            echo '<img src="' . esc_url($uni_logo) . '" style="max-width: 100px; height: auto; border-radius: 8px; border: 1px solid #ddd; padding: 4px; background: #fff;" class="sit-image-preview" />';
        } else {
            echo '<img src="" style="max-width: 100px; height: auto; border-radius: 8px; border: 1px solid #ddd; padding: 4px; background: #fff; display:none;" class="sit-image-preview" />';
        }
        echo '<input type="text" name="uni_logo" value="' . esc_attr($uni_logo === 'null' ? '' : $uni_logo) . '" class="sit-image-url" style="width: 100%;" placeholder="https://..." />';
        echo '<button type="button" class="button sit-upload-image-btn">' . esc_html__('Select/Upload Logo', 'sit-search') . '</button>';
        echo '</div>';
        echo '</div>';
        
        // Uni Image field (Banner).
        echo '<div style="margin-bottom: 20px;">';
        echo '<label style="display:block; margin-bottom: 5px; font-weight: bold;">' . esc_html__('University Hero Banner URL', 'sit-search') . '</label>';
        echo '<div class="sit-image-uploader" style="display:flex; flex-direction:column; gap:8px;">';
        if ($uni_image && $uni_image !== 'null') {
            echo '<img src="' . esc_url($uni_image) . '" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ddd; padding: 4px; background: #fff;" class="sit-image-preview" />';
        } else {
            echo '<img src="" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #ddd; padding: 4px; background: #fff; display:none;" class="sit-image-preview" />';
        }
        echo '<input type="text" name="uni_image" value="' . esc_attr($uni_image === 'null' ? '' : $uni_image) . '" class="sit-image-url" style="width: 100%;" placeholder="https://..." />';
        echo '<button type="button" class="button sit-upload-image-btn">' . esc_html__('Select/Upload Banner', 'sit-search') . '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function save_meta_boxes($post_id)
    {
        if (!isset($_POST['sit_university_media_nonce']) || !wp_verify_nonce($_POST['sit_university_media_nonce'], 'sit_university_media_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['uni_logo'])) {
            update_post_meta($post_id, 'uni_logo', esc_url_raw($_POST['uni_logo']));
        }
        
        if (isset($_POST['uni_image'])) {
            update_post_meta($post_id, 'uni_image', esc_url_raw($_POST['uni_image']));
        }
    }

    public function admin_scripts($hook)
    {
        global $typenow, $post_type;
        $current_type = $typenow ?: $post_type;
        
        if ($current_type === 'sit-university') {
            wp_enqueue_media();
            
            wp_add_inline_script('jquery', "
                jQuery(document).ready(function($){
                    $('.sit-upload-image-btn').on('click', function(e){
                        e.preventDefault();
                        var button = $(this);
                        var container = button.closest('.sit-image-uploader');
                        var custom_uploader = wp.media({
                            title: 'Select Image',
                            button: { text: 'Use this image' },
                            multiple: false
                        }).on('select', function() {
                            var attachment = custom_uploader.state().get('selection').first().toJSON();
                            container.find('.sit-image-url').val(attachment.url);
                            container.find('.sit-image-preview').attr('src', attachment.url).show();
                        }).open();
                    });
                });
            ");
        }
    }
}
