<?php
/**
 * Register Custom Post Types
 *
 * @package PilgrimIRL
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all custom post types
 */
function pilgrimirl_register_post_types() {

    // Monastic Sites Post Type
    register_post_type('monastic_site', array(
        'labels' => array(
            'name' => 'Monastic Sites',
            'singular_name' => 'Monastic Site',
            'add_new' => 'Add New Site',
            'add_new_item' => 'Add New Monastic Site',
            'edit_item' => 'Edit Monastic Site',
            'new_item' => 'New Monastic Site',
            'view_item' => 'View Monastic Site',
            'search_items' => 'Search Monastic Sites',
            'not_found' => 'No monastic sites found',
            'not_found_in_trash' => 'No monastic sites found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'monastic-sites'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_icon' => 'dashicons-building',
        'show_in_rest' => true,
    ));

    // Pilgrimage Routes Post Type
    register_post_type('pilgrimage_route', array(
        'labels' => array(
            'name' => 'Pilgrimage Routes',
            'singular_name' => 'Pilgrimage Route',
            'add_new' => 'Add New Route',
            'add_new_item' => 'Add New Pilgrimage Route',
            'edit_item' => 'Edit Pilgrimage Route',
            'new_item' => 'New Pilgrimage Route',
            'view_item' => 'View Pilgrimage Route',
            'search_items' => 'Search Pilgrimage Routes',
            'not_found' => 'No pilgrimage routes found',
            'not_found_in_trash' => 'No pilgrimage routes found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'pilgrimage-routes'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_icon' => 'dashicons-location',
        'show_in_rest' => true,
    ));

    // Christian Sites Post Type (includes Holy Wells, High Crosses, Mass Rocks, and Ruins)
    register_post_type('christian_site', array(
        'labels' => array(
            'name' => 'Christian Sites',
            'singular_name' => 'Christian Site',
            'add_new' => 'Add New Site',
            'add_new_item' => 'Add New Christian Site',
            'edit_item' => 'Edit Christian Site',
            'new_item' => 'New Christian Site',
            'view_item' => 'View Christian Site',
            'search_items' => 'Search Christian Sites',
            'not_found' => 'No christian sites found',
            'not_found_in_trash' => 'No christian sites found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'christian-sites'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_icon' => 'dashicons-location-alt',
        'show_in_rest' => true,
    ));
}
add_action('init', 'pilgrimirl_register_post_types');
