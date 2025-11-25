<?php
/**
 * Reusable Site Card Component
 *
 * Usage: get_template_part('includes/components/card-site', null, $args);
 *
 * Arguments:
 * - excerpt_length: Number of words for excerpt (default: 20)
 * - show_meta: Show post type and county badges (default: true)
 * - show_map_btn: Show "View on Map" button (default: true)
 * - image_size: Thumbnail size to use (default: 'medium')
 * - card_class: Additional CSS classes for card (default: '')
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get arguments with defaults
$excerpt_length = isset($args['excerpt_length']) ? $args['excerpt_length'] : 20;
$show_meta = isset($args['show_meta']) ? $args['show_meta'] : true;
$show_map_btn = isset($args['show_map_btn']) ? $args['show_map_btn'] : true;
$image_size = isset($args['image_size']) ? $args['image_size'] : 'medium';
$card_class = isset($args['card_class']) ? $args['card_class'] : '';

// Get post data
$post_id = get_the_ID();
$post_type_obj = get_post_type_object(get_post_type());
$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : '';

// Get taxonomies
$counties = wp_get_post_terms($post_id, 'county', array('fields' => 'names'));
$county_name = !empty($counties) && !is_wp_error($counties) ? $counties[0] : '';

// Get coordinates for map button
$lat = get_post_meta($post_id, '_pilgrimirl_latitude', true);
$lng = get_post_meta($post_id, '_pilgrimirl_longitude', true);
?>

<div class="card card--site <?php echo esc_attr($card_class); ?>">
    <?php if (has_post_thumbnail()) : ?>
        <div class="card__image">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail($image_size); ?>
            </a>
            <?php if ($show_meta && $post_type_label) : ?>
                <span class="card__type-badge"><?php echo esc_html($post_type_label); ?></span>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <div class="card__image card__image--placeholder">
            <a href="<?php the_permalink(); ?>">
                <div class="card__placeholder-icon">
                    <?php
                    // Show appropriate icon based on post type
                    $icon = '🏛️';
                    if (get_post_type() === 'pilgrimage_route') {
                        $icon = '🚶';
                    } elseif (get_post_type() === 'christian_site') {
                        $icon = '⛪';
                    }
                    echo $icon;
                    ?>
                </div>
            </a>
            <?php if ($show_meta && $post_type_label) : ?>
                <span class="card__type-badge"><?php echo esc_html($post_type_label); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card__content">
        <?php if ($show_meta && $county_name) : ?>
            <div class="card__meta">
                <span class="card__county"><?php echo esc_html($county_name); ?></span>
            </div>
        <?php endif; ?>

        <h3 class="card__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="card__excerpt">
            <?php echo wp_trim_words(get_the_excerpt(), $excerpt_length, '...'); ?>
        </div>

        <div class="card__actions">
            <a href="<?php the_permalink(); ?>" class="card__btn card__btn--primary">Learn More</a>
            <?php if ($show_map_btn && $lat && $lng) : ?>
                <button class="card__btn card__btn--secondary show-on-map-btn"
                        data-lat="<?php echo esc_attr($lat); ?>"
                        data-lng="<?php echo esc_attr($lng); ?>">
                    View on Map
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
