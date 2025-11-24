<?php
/**
 * Register Custom Taxonomies
 *
 * @package PilgrimIRL
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all custom taxonomies
 */
function pilgrimirl_register_taxonomies() {

    // County Taxonomy
    register_taxonomy('county', array('monastic_site', 'pilgrimage_route', 'christian_site'), array(
        'labels' => array(
            'name' => 'Counties',
            'singular_name' => 'County',
            'search_items' => 'Search Counties',
            'all_items' => 'All Counties',
            'edit_item' => 'Edit County',
            'update_item' => 'Update County',
            'add_new_item' => 'Add New County',
            'new_item_name' => 'New County Name',
            'menu_name' => 'Counties',
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'county'),
        'show_in_rest' => true,
    ));

    // Religious Order Taxonomy
    register_taxonomy('religious_order', array('monastic_site', 'christian_site'), array(
        'labels' => array(
            'name' => 'Religious Orders',
            'singular_name' => 'Religious Order',
            'search_items' => 'Search Religious Orders',
            'all_items' => 'All Religious Orders',
            'edit_item' => 'Edit Religious Order',
            'update_item' => 'Update Religious Order',
            'add_new_item' => 'Add New Religious Order',
            'new_item_name' => 'New Religious Order Name',
            'menu_name' => 'Religious Orders',
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'religious-order'),
        'show_in_rest' => true,
    ));

    // Historical Period Taxonomy
    register_taxonomy('historical_period', array('monastic_site', 'christian_site'), array(
        'labels' => array(
            'name' => 'Historical Periods',
            'singular_name' => 'Historical Period',
            'search_items' => 'Search Historical Periods',
            'all_items' => 'All Historical Periods',
            'edit_item' => 'Edit Historical Period',
            'update_item' => 'Update Historical Period',
            'add_new_item' => 'Add New Historical Period',
            'new_item_name' => 'New Historical Period Name',
            'menu_name' => 'Historical Periods',
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'historical-period'),
        'show_in_rest' => true,
    ));

    // Site Status Taxonomy
    register_taxonomy('site_status', array('monastic_site', 'christian_site'), array(
        'labels' => array(
            'name' => 'Site Status',
            'singular_name' => 'Site Status',
            'search_items' => 'Search Site Status',
            'all_items' => 'All Site Status',
            'edit_item' => 'Edit Site Status',
            'update_item' => 'Update Site Status',
            'add_new_item' => 'Add New Site Status',
            'new_item_name' => 'New Site Status Name',
            'menu_name' => 'Site Status',
        ),
        'hierarchical' => false,
        'public' => true,
        'rewrite' => array('slug' => 'site-status'),
        'show_in_rest' => true,
    ));

    // Difficulty Level Taxonomy (for routes)
    register_taxonomy('difficulty_level', array('pilgrimage_route'), array(
        'labels' => array(
            'name' => 'Difficulty Levels',
            'singular_name' => 'Difficulty Level',
            'search_items' => 'Search Difficulty Levels',
            'all_items' => 'All Difficulty Levels',
            'edit_item' => 'Edit Difficulty Level',
            'update_item' => 'Update Difficulty Level',
            'add_new_item' => 'Add New Difficulty Level',
            'new_item_name' => 'New Difficulty Level Name',
            'menu_name' => 'Difficulty Levels',
        ),
        'hierarchical' => false,
        'public' => true,
        'rewrite' => array('slug' => 'difficulty'),
        'show_in_rest' => true,
    ));

    // Pilgrimage Features Taxonomy
    register_taxonomy('pilgrimage_features', array('pilgrimage_route'), array(
        'labels' => array(
            'name' => 'Pilgrimage Features',
            'singular_name' => 'Pilgrimage Feature',
            'search_items' => 'Search Pilgrimage Features',
            'all_items' => 'All Pilgrimage Features',
            'edit_item' => 'Edit Pilgrimage Feature',
            'update_item' => 'Update Pilgrimage Feature',
            'add_new_item' => 'Add New Pilgrimage Feature',
            'new_item_name' => 'New Pilgrimage Feature Name',
            'menu_name' => 'Pilgrimage Features',
        ),
        'hierarchical' => false,
        'public' => true,
        'rewrite' => array('slug' => 'features'),
        'show_in_rest' => true,
    ));

    // Site Type Taxonomy (for Christian Sites)
    register_taxonomy('site_type', array('christian_site'), array(
        'labels' => array(
            'name' => 'Site Types',
            'singular_name' => 'Site Type',
            'search_items' => 'Search Site Types',
            'all_items' => 'All Site Types',
            'edit_item' => 'Edit Site Type',
            'update_item' => 'Update Site Type',
            'add_new_item' => 'Add New Site Type',
            'new_item_name' => 'New Site Type Name',
            'menu_name' => 'Site Types',
        ),
        'hierarchical' => false,
        'public' => true,
        'rewrite' => array('slug' => 'site-type'),
        'show_in_rest' => true,
    ));

    // Saints Taxonomy
    register_taxonomy('associated_saints', array('monastic_site', 'christian_site', 'pilgrimage_route'), array(
        'labels' => array(
            'name' => 'Associated Saints',
            'singular_name' => 'Saint',
            'search_items' => 'Search Saints',
            'all_items' => 'All Saints',
            'edit_item' => 'Edit Saint',
            'update_item' => 'Update Saint',
            'add_new_item' => 'Add New Saint',
            'new_item_name' => 'New Saint Name',
            'menu_name' => 'Saints',
        ),
        'hierarchical' => false,
        'public' => true,
        'rewrite' => array('slug' => 'saint'),
        'show_in_rest' => true,
    ));

    // Century Taxonomy
    register_taxonomy('century', array('monastic_site', 'christian_site'), array(
        'labels' => array(
            'name' => 'Centuries',
            'singular_name' => 'Century',
            'search_items' => 'Search Centuries',
            'all_items' => 'All Centuries',
            'edit_item' => 'Edit Century',
            'update_item' => 'Update Century',
            'add_new_item' => 'Add New Century',
            'new_item_name' => 'New Century Name',
            'menu_name' => 'Centuries',
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'century'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'pilgrimirl_register_taxonomies');

/**
 * Create default Irish counties on theme activation
 */
function pilgrimirl_create_default_counties() {
    $counties = pilgrimirl_get_irish_counties();

    foreach ($counties as $slug => $name) {
        if (!term_exists($name, 'county')) {
            wp_insert_term($name, 'county', array('slug' => $slug));
        }
    }
}
add_action('after_switch_theme', 'pilgrimirl_create_default_counties');

/**
 * Get list of Irish counties
 *
 * @return array County slug => name pairs
 */
function pilgrimirl_get_irish_counties() {
    return array(
        'antrim' => 'Antrim',
        'armagh' => 'Armagh',
        'carlow' => 'Carlow',
        'cavan' => 'Cavan',
        'clare' => 'Clare',
        'cork' => 'Cork',
        'derry' => 'Derry',
        'donegal' => 'Donegal',
        'down' => 'Down',
        'dublin' => 'Dublin',
        'fermanagh' => 'Fermanagh',
        'galway' => 'Galway',
        'kerry' => 'Kerry',
        'kildare' => 'Kildare',
        'kilkenny' => 'Kilkenny',
        'laois' => 'Laois',
        'leitrim' => 'Leitrim',
        'limerick' => 'Limerick',
        'longford' => 'Longford',
        'louth' => 'Louth',
        'mayo' => 'Mayo',
        'meath' => 'Meath',
        'monaghan' => 'Monaghan',
        'offaly' => 'Offaly',
        'roscommon' => 'Roscommon',
        'sligo' => 'Sligo',
        'tipperary' => 'Tipperary',
        'tyrone' => 'Tyrone',
        'waterford' => 'Waterford',
        'westmeath' => 'Westmeath',
        'wexford' => 'Wexford',
        'wicklow' => 'Wicklow'
    );
}
