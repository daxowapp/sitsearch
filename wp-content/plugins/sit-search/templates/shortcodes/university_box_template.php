<?php
/**
 * University Box Template
 * Create this file at: templates/shortcodes/university-box.html.php
 * 
 * @var WP_Post $university The university post object
 */

$university_id = $university->ID;
$country = get_post_meta($university_id, 'Country', true);
$city = get_post_meta($university_id, 'City', true);
$type = get_post_meta($university_id, 'University_Type', true);
$website = get_post_meta($university_id, 'Website', true);
$logo = get_post_meta($university_id, 'Logo_URL', true);
$phone = get_post_meta($university_id, 'Phone', true);
$email = get_post_meta($university_id, 'Email', true);
$establishment_year = get_post_meta($university_id, 'Establishment_Year', true);

// Get program count for this university
$program_count = wp_count_posts('sit-program');
$program_args = [
    'post_type' => 'sit-program',
    'post_status' => 'publish',
    'meta_query' => [
        [
            'key' => 'zh_university',
            'value' => $university_id,
            'compare' => '='
        ]
    ]
];
$programs_query = new WP_Query($program_args);
$program_count = $programs_query->found_posts;
wp_reset_postdata();
?>

<div class="university-box" data-university-id="<?php echo esc_attr($university_id); ?>">
    <div class="university-box-inner">
        
        <?php if ($logo): ?>
        <div class="university-logo">
            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($university->post_title); ?>" loading="lazy">
        </div>
        <?php else: ?>
        <div class="university-logo university-logo-placeholder">
            <div class="logo-placeholder">
                <?php echo esc_html(substr($university->post_title, 0, 1)); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="university-content">
            <h3 class="university-title">
                <a href="<?php echo get_permalink($university_id); ?>" title="<?php echo esc_attr($university->post_title); ?>">
                    <?php echo esc_html($university->post_title); ?>
                </a>
            </h3>

            <div class="university-meta">
                <?php if ($country || $city): ?>
                <div class="university-location">
                    <span class="location-icon">📍</span>
                    <?php 
                    $location_parts = array_filter([$city, $country]);
                    echo esc_html(implode(', ', $location_parts)); 
                    ?>
                </div>
                <?php endif; ?>

                <?php if ($type): ?>
                <div class="university-type">
                    <span class="type-icon">🏛️</span>
                    <span class="meta-value"><?php echo esc_html($type); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($establishment_year): ?>
                <div class="university-year">
                    <span class="year-icon">📅</span>
                    <span class="meta-value"><?php printf(__('Est. %s', SIT_SEARCH_TEXT_DOMAIN), esc_html($establishment_year)); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($program_count > 0): ?>
                <div class="university-programs">
                    <span class="programs-icon">📚</span>
                    <span class="meta-value">
                        <?php printf(_n('%d Program', '%d Programs', $program_count, SIT_SEARCH_TEXT_DOMAIN), $program_count); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($university->post_excerpt): ?>
            <div class="university-excerpt">
                <?php echo wp_trim_words($university->post_excerpt, 25, '...'); ?>
            </div>
            <?php endif; ?>

            <div class="university-contact">
                <?php if ($website): ?>
                <a href="<?php echo esc_url($website); ?>" class="contact-link website-link" target="_blank" rel="noopener" title="<?php _e('Visit Website', SIT_SEARCH_TEXT_DOMAIN); ?>">
                    🌐
                </a>
                <?php endif; ?>

                <?php if ($email): ?>
                <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-link email-link" title="<?php _e('Send Email', SIT_SEARCH_TEXT_DOMAIN); ?>">
                    ✉️
                </a>
                <?php endif; ?>

                <?php if ($phone): ?>
                <a href="tel:<?php echo esc_attr($phone); ?>" class="contact-link phone-link" title="<?php _e('Call', SIT_SEARCH_TEXT_DOMAIN); ?>">
                    📞
                </a>
                <?php endif; ?>
            </div>

            <div class="university-actions">
                <a href="<?php echo get_permalink($university_id); ?>" class="btn btn-primary">
                    <?php _e('View Details', SIT_SEARCH_TEXT_DOMAIN); ?>
                </a>
                
                <?php if ($program_count > 0): ?>
                <a href="<?php echo get_permalink($university_id); ?>#programs" class="btn btn-secondary">
                    <?php _e('View Programs', SIT_SEARCH_TEXT_DOMAIN); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>