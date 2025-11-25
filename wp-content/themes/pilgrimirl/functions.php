<?php
/**
 * PilgrimIRL Theme Functions
 * 
 * Custom functionality for Irish Pilgrimage and Monastic Sites
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fix calendar page year parameter conflict with WordPress date archives
 * WordPress treats 'year' as a date archive query, causing 404 on calendar page
 */
function pilgrimirl_fix_calendar_year_query($query) {
    // Only on frontend, main query
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Check if we're trying to access calendar page with year parameter
    // WordPress sees ?year=2026 and thinks it's a date archive
    if (isset($_GET['year'])) {
        $request_uri = $_SERVER['REQUEST_URI'];

        // If the URL contains /calendar/ with a year parameter
        if (strpos($request_uri, '/calendar/') !== false || strpos($request_uri, 'page_id=') !== false) {
            // Get the calendar page
            $calendar_page = get_page_by_path('calendar');

            if ($calendar_page) {
                // Override the query to load the calendar page
                $query->set('page_id', $calendar_page->ID);
                $query->set('post_type', 'page');
                $query->is_date = false;
                $query->is_year = false;
                $query->is_archive = false;
                $query->is_page = true;
                $query->is_singular = true;
                $query->is_404 = false;
            }
        }
    }
}
add_action('pre_get_posts', 'pilgrimirl_fix_calendar_year_query', 1);

/**
 * Handle 404 override for calendar year pages
 */
function pilgrimirl_calendar_handle_404() {
    global $wp_query;

    if (is_404() && isset($_GET['year'])) {
        $request_uri = $_SERVER['REQUEST_URI'];

        if (strpos($request_uri, '/calendar/') !== false) {
            $calendar_page = get_page_by_path('calendar');

            if ($calendar_page) {
                // Reset 404 status
                status_header(200);
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
                $wp_query->is_singular = true;

                // Load the calendar page
                $wp_query->queried_object = $calendar_page;
                $wp_query->queried_object_id = $calendar_page->ID;
                $wp_query->post = $calendar_page;
                $wp_query->posts = array($calendar_page);
                $wp_query->found_posts = 1;
                $wp_query->post_count = 1;

                // Load the template
                include(get_page_template_slug($calendar_page->ID) ? locate_template(get_page_template_slug($calendar_page->ID)) : get_stylesheet_directory() . '/page-calendar.php');
                exit;
            }
        }
    }
}
add_action('template_redirect', 'pilgrimirl_calendar_handle_404', 1);

/**
 * Helper function to get asset URL with automatic minification in production
 *
 * @param string $path Relative path to asset (e.g., 'css/footer.css')
 * @return string Full URL to asset (.min version in production)
 */
function pilgrimirl_asset($path) {
    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    // Use minified version in production (when WP_DEBUG is false)
    if (!defined('WP_DEBUG') || WP_DEBUG === false) {
        $min_path = preg_replace('/\.(css|js)$/', '.min.$1', $path);
        $min_file = $theme_dir . '/' . $min_path;

        // Check if minified file exists
        if (file_exists($min_file)) {
            return $theme_uri . '/' . $min_path;
        }
    }

    // Return original file
    return $theme_uri . '/' . $path;
}

/**
 * Get current theme version for cache busting
 *
 * @return string Theme version
 */
function pilgrimirl_version() {
    $theme = wp_get_theme();
    return $theme->get('Version');
}

/**
 * Enqueue parent and child theme styles
 */
function pilgrimirl_enqueue_styles() {
    $version = pilgrimirl_version();

    // Enqueue parent theme style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', array(), $version);

    // Enqueue child theme style (main stylesheet)
    wp_enqueue_style('pilgrimirl-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'), $version);

    // Enqueue component CSS (automatically uses .min.css in production)
    wp_enqueue_style('pilgrimirl-county-pages', pilgrimirl_asset('css/county-pages.css'), array('pilgrimirl-style'), $version);
    wp_enqueue_style('pilgrimirl-footer', pilgrimirl_asset('css/footer.css'), array('pilgrimirl-style'), $version);
    wp_enqueue_style('pilgrimirl-homepage-filters', pilgrimirl_asset('css/homepage-filters.css'), array('pilgrimirl-style'), $version);

    // Archive pages CSS
    if (is_post_type_archive(array('monastic_site', 'pilgrimage_route', 'christian_site')) || is_tax('county')) {
        wp_enqueue_style('pilgrimirl-archive-pages', pilgrimirl_asset('css/archive-pages.css'), array('pilgrimirl-style'), $version);
    }

    // Saints page CSS
    if (is_page_template('page-saints.php') || is_page('saints')) {
        wp_enqueue_style('pilgrimirl-saints-page', pilgrimirl_asset('css/saints-page.css'), array('pilgrimirl-style'), $version);
    }

    // Calendar page CSS
    if (is_page_template('page-calendar.php') || is_page('calendar')) {
        wp_enqueue_style('pilgrimirl-calendar', pilgrimirl_asset('css/calendar.css'), array('pilgrimirl-style'), $version);
    }

    // Enqueue Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap', array(), null);

    // Enqueue JavaScript (automatically uses .min.js in production)
    wp_enqueue_script('pilgrimirl-debug', pilgrimirl_asset('js/debug-utils.js'), array(), $version, true);
    wp_enqueue_script('pilgrimirl-scripts', pilgrimirl_asset('js/pilgrimirl.js'), array('jquery', 'pilgrimirl-debug'), $version, true);
    wp_enqueue_script('pilgrimirl-maps', pilgrimirl_asset('js/maps.js'), array('jquery', 'pilgrimirl-debug'), $version, true);
    wp_enqueue_script('pilgrimirl-homepage-filters', pilgrimirl_asset('js/homepage-filters.js'), array('jquery', 'pilgrimirl-debug'), $version, true);

    // Saints page JavaScript
    if (is_page_template('page-saints.php') || is_page('saints')) {
        wp_enqueue_script('pilgrimirl-saints-page', pilgrimirl_asset('js/saints-page-filters.js'), array('jquery', 'pilgrimirl-maps'), $version, true);
    }
    
    // Localize script for AJAX
    wp_localize_script('pilgrimirl-scripts', 'pilgrimirl_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pilgrimirl_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'pilgrimirl_enqueue_styles');

/**
 * Enqueue Google Maps API
 */
function pilgrimirl_enqueue_google_maps() {
    // Get API key from WordPress settings
    $api_key = get_option('pilgrimirl_google_maps_api_key', '');
    
    // Only load maps on relevant pages
    if (is_singular(array('monastic_site', 'pilgrimage_route', 'christian_site')) ||
        is_post_type_archive(array('monastic_site', 'pilgrimage_route', 'christian_site')) ||
        is_front_page() || is_page('counties') || is_page('saints') || is_page_template('page-saints.php') || is_tax('county')) {
        
        // Only enqueue if we have a valid API key
        if (!empty($api_key)) {
            wp_enqueue_script(
                'google-maps-api',
                'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=initPilgrimMaps',
                array(),
                null,
                true
            );
        } else {
            // Add admin notice if no API key
            if (current_user_can('manage_options')) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-warning"><p>Google Maps API key is missing. Please add it in <a href="' . admin_url('options-general.php') . '">Settings → General</a>.</p></div>';
                });
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'pilgrimirl_enqueue_google_maps');

// Add Google Maps API key to WordPress admin
function pilgrimirl_add_maps_settings() {
    add_settings_section(
        'pilgrimirl_maps_settings',
        'Google Maps Settings',
        null,
        'general'
    );
    
    add_settings_field(
        'pilgrimirl_google_maps_api_key',
        'Google Maps API Key',
        'pilgrimirl_maps_api_key_callback',
        'general',
        'pilgrimirl_maps_settings'
    );
    
    register_setting('general', 'pilgrimirl_google_maps_api_key');
}
add_action('admin_init', 'pilgrimirl_add_maps_settings');

function pilgrimirl_maps_api_key_callback() {
    $api_key = get_option('pilgrimirl_google_maps_api_key', '');
    echo '<input type="text" id="pilgrimirl_google_maps_api_key" name="pilgrimirl_google_maps_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
    echo '<p class="description">Enter your Google Maps API key here. <a href="https://console.cloud.google.com/" target="_blank">Get API Key</a></p>';
}

/**
 * Theme Setup
 */
function pilgrimirl_theme_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('title-tag');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'pilgrimirl'),
        'county-menu' => __('County Menu', 'pilgrimirl'),
        'footer-menu' => __('Footer Menu', 'pilgrimirl'),
    ));
    
    // Add image sizes
    add_image_size('site-card-thumb', 300, 200, true);
    add_image_size('county-hero', 1200, 400, true);
    add_image_size('site-gallery', 800, 600, true);
}
add_action('after_setup_theme', 'pilgrimirl_theme_setup');

/**
 * Register Custom Post Types
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

    // Liturgical Calendar Events Post Type
    register_post_type('calendar_event', array(
        'labels' => array(
            'name' => 'Calendar Events',
            'singular_name' => 'Calendar Event',
            'add_new' => 'Add New Event',
            'add_new_item' => 'Add New Calendar Event',
            'edit_item' => 'Edit Calendar Event',
            'new_item' => 'New Calendar Event',
            'view_item' => 'View Calendar Event',
            'search_items' => 'Search Calendar Events',
            'not_found' => 'No calendar events found',
            'not_found_in_trash' => 'No calendar events found in trash',
            'menu_name' => 'Liturgical Calendar'
        ),
        'public' => true,
        'has_archive' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'calendar-event'),
        'capability_type' => 'post',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_icon' => 'dashicons-calendar-alt',
        'show_in_rest' => true,
        'menu_position' => 25,
    ));
}
add_action('init', 'pilgrimirl_register_post_types');

/**
 * Register Custom Taxonomies
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

    // Liturgical Rank taxonomy (for Calendar Events)
    register_taxonomy('liturgical_rank', 'calendar_event', array(
        'labels' => array(
            'name' => 'Liturgical Ranks',
            'singular_name' => 'Liturgical Rank',
            'search_items' => 'Search Ranks',
            'all_items' => 'All Ranks',
            'edit_item' => 'Edit Rank',
            'update_item' => 'Update Rank',
            'add_new_item' => 'Add New Rank',
            'new_item_name' => 'New Rank Name',
            'menu_name' => 'Ranks'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'liturgical-rank'),
        'show_in_rest' => true,
    ));

    // Liturgical Color taxonomy (for Calendar Events)
    register_taxonomy('liturgical_color', 'calendar_event', array(
        'labels' => array(
            'name' => 'Liturgical Colors',
            'singular_name' => 'Liturgical Color',
            'search_items' => 'Search Colors',
            'all_items' => 'All Colors',
            'edit_item' => 'Edit Color',
            'update_item' => 'Update Color',
            'add_new_item' => 'Add New Color',
            'new_item_name' => 'New Color Name',
            'menu_name' => 'Colors'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'liturgical-color'),
        'show_in_rest' => true,
    ));

    // Liturgical Season taxonomy (for Calendar Events)
    register_taxonomy('liturgical_season', 'calendar_event', array(
        'labels' => array(
            'name' => 'Liturgical Seasons',
            'singular_name' => 'Liturgical Season',
            'search_items' => 'Search Seasons',
            'all_items' => 'All Seasons',
            'edit_item' => 'Edit Season',
            'update_item' => 'Update Season',
            'add_new_item' => 'Add New Season',
            'new_item_name' => 'New Season Name',
            'menu_name' => 'Seasons'
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'liturgical-season'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'pilgrimirl_register_taxonomies');

/**
 * Add Custom Meta Boxes
 */
function pilgrimirl_add_meta_boxes() {
    // Location Meta Box
    add_meta_box(
        'pilgrimirl_location',
        'Location Details',
        'pilgrimirl_location_meta_box_callback',
        array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'normal',
        'high'
    );

    // Historical Details Meta Box
    add_meta_box(
        'pilgrimirl_historical',
        'Historical Information',
        'pilgrimirl_historical_meta_box_callback',
        array('monastic_site', 'christian_site'),
        'normal',
        'high'
    );
    
    // Route Details Meta Box
    add_meta_box(
        'pilgrimirl_route',
        'Route Information',
        'pilgrimirl_route_meta_box_callback',
        'pilgrimage_route',
        'normal',
        'high'
    );

    // Calendar Event Details Meta Box
    add_meta_box(
        'calendar_event_details',
        'Event Details',
        'pilgrimirl_calendar_event_meta_box_callback',
        'calendar_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'pilgrimirl_add_meta_boxes');

/**
 * Location Meta Box Callback
 */
function pilgrimirl_location_meta_box_callback($post) {
    wp_nonce_field('pilgrimirl_save_meta_box_data', 'pilgrimirl_meta_box_nonce');
    
    $latitude = get_post_meta($post->ID, '_pilgrimirl_latitude', true);
    $longitude = get_post_meta($post->ID, '_pilgrimirl_longitude', true);
    $address = get_post_meta($post->ID, '_pilgrimirl_address', true);
    $alternative_names = get_post_meta($post->ID, '_pilgrimirl_alternative_names', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="pilgrimirl_latitude">Latitude:</label></th>';
    echo '<td><input type="text" id="pilgrimirl_latitude" name="pilgrimirl_latitude" value="' . esc_attr($latitude) . '" size="25" /></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_longitude">Longitude:</label></th>';
    echo '<td><input type="text" id="pilgrimirl_longitude" name="pilgrimirl_longitude" value="' . esc_attr($longitude) . '" size="25" /></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_address">Address:</label></th>';
    echo '<td><input type="text" id="pilgrimirl_address" name="pilgrimirl_address" value="' . esc_attr($address) . '" size="50" /></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_alternative_names">Alternative Names:</label></th>';
    echo '<td><textarea id="pilgrimirl_alternative_names" name="pilgrimirl_alternative_names" rows="3" cols="50">' . esc_textarea($alternative_names) . '</textarea>';
    echo '<p class="description">Enter alternative names, one per line</p></td></tr>';
    echo '</table>';
}

/**
 * Historical Meta Box Callback
 */
function pilgrimirl_historical_meta_box_callback($post) {
    $foundation_date = get_post_meta($post->ID, '_pilgrimirl_foundation_date', true);
    $dissolution_date = get_post_meta($post->ID, '_pilgrimirl_dissolution_date', true);
    $communities_provenance = get_post_meta($post->ID, '_pilgrimirl_communities_provenance', true);
    $associated_saints = get_post_meta($post->ID, '_pilgrimirl_associated_saints', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="pilgrimirl_foundation_date">Foundation Date:</label></th>';
    echo '<td><input type="text" id="pilgrimirl_foundation_date" name="pilgrimirl_foundation_date" value="' . esc_attr($foundation_date) . '" size="25" /></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_dissolution_date">Dissolution Date:</label></th>';
    echo '<td><input type="text" id="pilgrimirl_dissolution_date" name="pilgrimirl_dissolution_date" value="' . esc_attr($dissolution_date) . '" size="25" /></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_communities_provenance">Communities & Provenance:</label></th>';
    echo '<td><textarea id="pilgrimirl_communities_provenance" name="pilgrimirl_communities_provenance" rows="4" cols="50">' . esc_textarea($communities_provenance) . '</textarea></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_associated_saints">Associated Saints:</label></th>';
    echo '<td><input type="text" id="pilgrimirl_associated_saints" name="pilgrimirl_associated_saints" value="' . esc_attr($associated_saints) . '" size="50" /></td></tr>';
    echo '</table>';
}

/**
 * Route Meta Box Callback
 */
function pilgrimirl_route_meta_box_callback($post) {
    $distance = get_post_meta($post->ID, '_pilgrimirl_distance', true);
    $pets_allowed = get_post_meta($post->ID, '_pilgrimirl_pets_allowed', true);
    $route_excerpt = get_post_meta($post->ID, '_pilgrimirl_route_excerpt', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="pilgrimirl_distance">Distance (km):</label></th>';
    echo '<td><input type="text" id="pilgrimirl_distance" name="pilgrimirl_distance" value="' . esc_attr($distance) . '" size="10" /></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_pets_allowed">Pets Allowed:</label></th>';
    echo '<td><select id="pilgrimirl_pets_allowed" name="pilgrimirl_pets_allowed">';
    echo '<option value="yes"' . selected($pets_allowed, 'yes', false) . '>Yes</option>';
    echo '<option value="no"' . selected($pets_allowed, 'no', false) . '>No</option>';
    echo '<option value="unknown"' . selected($pets_allowed, 'unknown', false) . '>Unknown</option>';
    echo '</select></td></tr>';
    
    echo '<tr><th><label for="pilgrimirl_route_excerpt">Route Excerpt:</label></th>';
    echo '<td><textarea id="pilgrimirl_route_excerpt" name="pilgrimirl_route_excerpt" rows="3" cols="50">' . esc_textarea($route_excerpt) . '</textarea></td></tr>';
    echo '</table>';
}

/**
 * Calendar Event Meta Box Callback
 */
function pilgrimirl_calendar_event_meta_box_callback($post) {
    wp_nonce_field('pilgrimirl_calendar_event_nonce', 'pilgrimirl_calendar_event_nonce_field');

    $event_month = get_post_meta($post->ID, '_calendar_event_month', true);
    $event_day = get_post_meta($post->ID, '_calendar_event_day', true);
    $event_year = get_post_meta($post->ID, '_calendar_event_year', true);
    $is_irish_saint = get_post_meta($post->ID, '_calendar_event_irish', true);
    $is_moveable = get_post_meta($post->ID, '_calendar_event_moveable', true);
    $related_sites = get_post_meta($post->ID, '_calendar_event_related_sites', true);
    $traditions = get_post_meta($post->ID, '_calendar_event_traditions', true);
    $significance = get_post_meta($post->ID, '_calendar_event_significance', true);

    echo '<table class="form-table">';

    // Date fields
    echo '<tr><th><label>Event Date:</label></th><td>';
    echo '<select name="calendar_event_month" id="calendar_event_month">';
    echo '<option value="">Select Month</option>';
    $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    foreach ($months as $i => $month) {
        $selected = ($event_month == ($i + 1)) ? ' selected' : '';
        echo '<option value="' . ($i + 1) . '"' . $selected . '>' . esc_html($month) . '</option>';
    }
    echo '</select> ';

    echo '<select name="calendar_event_day" id="calendar_event_day">';
    echo '<option value="">Day</option>';
    for ($d = 1; $d <= 31; $d++) {
        $selected = ($event_day == $d) ? ' selected' : '';
        echo '<option value="' . $d . '"' . $selected . '>' . $d . '</option>';
    }
    echo '</select> ';

    echo '<select name="calendar_event_year" id="calendar_event_year">';
    echo '<option value="">Year (optional)</option>';
    $current_year = date('Y');
    for ($y = $current_year; $y <= $current_year + 5; $y++) {
        $selected = ($event_year == $y) ? ' selected' : '';
        echo '<option value="' . $y . '"' . $selected . '>' . $y . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Leave year empty for recurring annual events</p>';
    echo '</td></tr>';

    // Irish Saint checkbox
    echo '<tr><th><label for="calendar_event_irish">Irish Saint/Feast:</label></th>';
    echo '<td><input type="checkbox" id="calendar_event_irish" name="calendar_event_irish" value="1"' . checked($is_irish_saint, '1', false) . ' />';
    echo '<label for="calendar_event_irish"> This is an Irish saint or feast day</label></td></tr>';

    // Moveable feast checkbox
    echo '<tr><th><label for="calendar_event_moveable">Moveable Feast:</label></th>';
    echo '<td><input type="checkbox" id="calendar_event_moveable" name="calendar_event_moveable" value="1"' . checked($is_moveable, '1', false) . ' />';
    echo '<label for="calendar_event_moveable"> Date varies each year (e.g., Easter)</label></td></tr>';

    // Related Sites
    echo '<tr><th><label for="calendar_event_related_sites">Related Sacred Sites:</label></th>';
    echo '<td><textarea id="calendar_event_related_sites" name="calendar_event_related_sites" rows="3" cols="50">' . esc_textarea($related_sites) . '</textarea>';
    echo '<p class="description">List related monastic sites, holy wells, etc.</p></td></tr>';

    // Traditions
    echo '<tr><th><label for="calendar_event_traditions">Traditions & Customs:</label></th>';
    echo '<td><textarea id="calendar_event_traditions" name="calendar_event_traditions" rows="4" cols="50">' . esc_textarea($traditions) . '</textarea>';
    echo '<p class="description">Traditional observances, pilgrimages, patterns, etc.</p></td></tr>';

    // Significance
    echo '<tr><th><label for="calendar_event_significance">Significance:</label></th>';
    echo '<td><textarea id="calendar_event_significance" name="calendar_event_significance" rows="3" cols="50">' . esc_textarea($significance) . '</textarea>';
    echo '<p class="description">Historical and spiritual significance</p></td></tr>';

    echo '</table>';
}

/**
 * Save Calendar Event Meta Box Data
 */
function pilgrimirl_save_calendar_event_meta($post_id) {
    if (!isset($_POST['pilgrimirl_calendar_event_nonce_field'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['pilgrimirl_calendar_event_nonce_field'], 'pilgrimirl_calendar_event_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) !== 'calendar_event') {
        return;
    }

    // Save date fields
    if (isset($_POST['calendar_event_month'])) {
        update_post_meta($post_id, '_calendar_event_month', intval($_POST['calendar_event_month']));
    }
    if (isset($_POST['calendar_event_day'])) {
        update_post_meta($post_id, '_calendar_event_day', intval($_POST['calendar_event_day']));
    }
    if (isset($_POST['calendar_event_year'])) {
        update_post_meta($post_id, '_calendar_event_year', intval($_POST['calendar_event_year']));
    }

    // Save checkboxes
    update_post_meta($post_id, '_calendar_event_irish', isset($_POST['calendar_event_irish']) ? '1' : '0');
    update_post_meta($post_id, '_calendar_event_moveable', isset($_POST['calendar_event_moveable']) ? '1' : '0');

    // Save text fields
    if (isset($_POST['calendar_event_related_sites'])) {
        update_post_meta($post_id, '_calendar_event_related_sites', sanitize_textarea_field($_POST['calendar_event_related_sites']));
    }
    if (isset($_POST['calendar_event_traditions'])) {
        update_post_meta($post_id, '_calendar_event_traditions', sanitize_textarea_field($_POST['calendar_event_traditions']));
    }
    if (isset($_POST['calendar_event_significance'])) {
        update_post_meta($post_id, '_calendar_event_significance', sanitize_textarea_field($_POST['calendar_event_significance']));
    }
}
add_action('save_post', 'pilgrimirl_save_calendar_event_meta');

/**
 * Save Meta Box Data
 */
function pilgrimirl_save_meta_box_data($post_id) {
    if (!isset($_POST['pilgrimirl_meta_box_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['pilgrimirl_meta_box_nonce'], 'pilgrimirl_save_meta_box_data')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save location data
    $fields = array(
        'pilgrimirl_latitude',
        'pilgrimirl_longitude',
        'pilgrimirl_address',
        'pilgrimirl_alternative_names',
        'pilgrimirl_foundation_date',
        'pilgrimirl_dissolution_date',
        'pilgrimirl_communities_provenance',
        'pilgrimirl_associated_saints',
        'pilgrimirl_distance',
        'pilgrimirl_pets_allowed',
        'pilgrimirl_route_excerpt'
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'pilgrimirl_save_meta_box_data');

/**
 * AJAX Search Handler
 */
function pilgrimirl_ajax_search() {
    check_ajax_referer('pilgrimirl_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search_term']);
    $county = sanitize_text_field($_POST['county']);
    $post_type = sanitize_text_field($_POST['post_type']);
    
    $args = array(
        'post_type' => $post_type ? $post_type : array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => 20,
        's' => $search_term,
        'post_status' => 'publish'
    );
    
    if ($county) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'county',
                'field' => 'slug',
                'terms' => $county
            )
        );
    }
    
    $query = new WP_Query($args);
    $results = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'permalink' => get_permalink(),
                'latitude' => get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true),
                'longitude' => get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true),
                'county' => wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'names')),
                'post_type' => get_post_type()
            );
        }
    }
    
    wp_reset_postdata();
    wp_send_json_success($results);
}
add_action('wp_ajax_pilgrimirl_search', 'pilgrimirl_ajax_search');
add_action('wp_ajax_nopriv_pilgrimirl_search', 'pilgrimirl_ajax_search');

/**
 * AJAX handler to get county sites for maps
 */
function pilgrimirl_get_county_sites() {
    check_ajax_referer('pilgrimirl_nonce', 'nonce');
    
    $county_slug = sanitize_text_field($_POST['county']);
    
    // Log the request for debugging
    error_log("PilgrimIRL: Getting sites for county: " . $county_slug);
    
    $args = array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_pilgrimirl_latitude',
                'value' => '',
                'compare' => '!='
            ),
            array(
                'key' => '_pilgrimirl_longitude',
                'value' => '',
                'compare' => '!='
            )
        ),
        'tax_query' => array(
            array(
                'taxonomy' => 'county',
                'field' => 'slug',
                'terms' => $county_slug
            )
        )
    );
    
    $query = new WP_Query($args);
    $sites = array();
    $processed_count = 0;
    $skipped_count = 0;
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $processed_count++;
            
            $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
            $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
            
            if ($latitude && $longitude && is_numeric($latitude) && is_numeric($longitude)) {
                $county_terms = wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'names'));
                $excerpt = get_the_excerpt();
                if (empty($excerpt)) {
                    $excerpt = wp_trim_words(get_the_content(), 20);
                }
                
                $sites[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => $excerpt,
                    'permalink' => get_permalink(),
                    'latitude' => floatval($latitude),
                    'longitude' => floatval($longitude),
                    'county' => $county_terms,
                    'post_type' => get_post_type()
                );
            } else {
                $skipped_count++;
                error_log("PilgrimIRL: Skipped site " . get_the_title() . " - missing/invalid coordinates");
            }
        }
    }
    
    wp_reset_postdata();
    
    // Log results for debugging
    error_log("PilgrimIRL: County {$county_slug} - Found {$query->found_posts} posts, processed {$processed_count}, returned " . count($sites) . " sites, skipped {$skipped_count}");
    
    wp_send_json_success(array(
        'sites' => $sites,
        'debug' => array(
            'county' => $county_slug,
            'found_posts' => $query->found_posts,
            'processed_count' => $processed_count,
            'returned_count' => count($sites),
            'skipped_count' => $skipped_count,
            'memory_usage' => size_format(memory_get_usage()),
            'peak_memory' => size_format(memory_get_peak_usage())
        )
    ));
}
add_action('wp_ajax_get_county_sites', 'pilgrimirl_get_county_sites');
add_action('wp_ajax_nopriv_get_county_sites', 'pilgrimirl_get_county_sites');

/**
 * AJAX handler to get all sites for main map
 */
function pilgrimirl_get_all_sites() {
    check_ajax_referer('pilgrimirl_nonce', 'nonce');
    
    $args = array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_pilgrimirl_latitude',
                'value' => '',
                'compare' => '!='
            ),
            array(
                'key' => '_pilgrimirl_longitude',
                'value' => '',
                'compare' => '!='
            )
        )
    );
    
    $query = new WP_Query($args);
    $sites = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
            $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
            
            if ($latitude && $longitude) {
                $sites[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'latitude' => floatval($latitude),
                    'longitude' => floatval($longitude),
                    'county' => wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'names')),
                    'post_type' => get_post_type()
                );
            }
        }
    }
    
    wp_reset_postdata();
    wp_send_json_success($sites);
}
add_action('wp_ajax_get_all_sites', 'pilgrimirl_get_all_sites');
add_action('wp_ajax_nopriv_get_all_sites', 'pilgrimirl_get_all_sites');

/**
 * AJAX handler for filtered site search
 */
function pilgrimirl_get_filtered_sites() {
    check_ajax_referer('pilgrimirl_nonce', 'nonce');
    
    $post_type = sanitize_text_field($_POST['post_type'] ?? '');
    $county = sanitize_text_field($_POST['county'] ?? '');
    $saint = sanitize_text_field($_POST['saint'] ?? '');
    $century = sanitize_text_field($_POST['century'] ?? '');
    
    error_log("PilgrimIRL Filter Debug - Received filters: post_type={$post_type}, county={$county}, saint={$saint}, century={$century}");
    
    $args = array(
        'post_type' => $post_type ? $post_type : array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    );
    
    // Build tax query for taxonomy-based filters
    $tax_query = array('relation' => 'AND');
    
    if ($county) {
        $tax_query[] = array(
            'taxonomy' => 'county',
            'field' => 'slug',
            'terms' => $county
        );
    }
    
    // Only add century to tax_query if it exists as a taxonomy term
    if ($century) {
        $century_term = get_term_by('slug', $century, 'century');
        if ($century_term) {
            $tax_query[] = array(
                'taxonomy' => 'century',
                'field' => 'slug',
                'terms' => $century
            );
        }
    }
    
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }
    
    // Get all posts first
    $query = new WP_Query($args);
    $sites = array();
    $filtered_count = 0;
    $total_processed = 0;
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $total_processed++;
            
            // Apply content-based filters
            $include_post = true;
            
            // If saint filter is applied, check content for saint mentions
            if ($saint) {
                $include_post = pilgrimirl_post_contains_saint(get_the_ID(), $saint);
            }
            
            // If century filter is applied, check content for century mentions
            if ($century && $include_post) {
                $include_post = pilgrimirl_post_contains_century(get_the_ID(), $century);
            }
            
            if ($include_post) {
                $filtered_count++;
                $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                $county_terms = wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'names'));
                $saint_terms = wp_get_post_terms(get_the_ID(), 'associated_saints', array('fields' => 'names'));
                $century_terms = wp_get_post_terms(get_the_ID(), 'century', array('fields' => 'names'));
                
                // Extract saints from content for display
                $content_saints = pilgrimirl_extract_saints_from_post_content(get_the_ID());
                
                $sites[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 20),
                    'permalink' => get_permalink(),
                    'latitude' => $latitude ? floatval($latitude) : null,
                    'longitude' => $longitude ? floatval($longitude) : null,
                    'county' => $county_terms,
                    'saints' => array_merge($saint_terms, $content_saints), // Combine taxonomy and content saints
                    'century' => $century_terms,
                    'post_type' => get_post_type()
                );
            }
        }
    }
    
    wp_reset_postdata();
    
    error_log("PilgrimIRL Filter Debug - Total processed: {$total_processed}, Filtered count: {$filtered_count}, Final sites: " . count($sites));
    
    wp_send_json_success($sites);
}
add_action('wp_ajax_get_filtered_sites', 'pilgrimirl_get_filtered_sites');
add_action('wp_ajax_nopriv_get_filtered_sites', 'pilgrimirl_get_filtered_sites');

/**
 * AJAX handler to get filter options
 */
function pilgrimirl_get_filter_options() {
    check_ajax_referer('pilgrimirl_nonce', 'nonce');
    
    $filter_type = sanitize_text_field($_POST['filter_type']);
    $options = array();
    
    switch ($filter_type) {
        case 'saints':
            $options = pilgrimirl_extract_saints_from_content();
            break;
            
        case 'centuries':
            $options = pilgrimirl_extract_centuries_from_content();
            break;
            
        case 'counties':
            $terms = get_terms(array(
                'taxonomy' => 'county',
                'hide_empty' => true,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            foreach ($terms as $term) {
                $options[] = array(
                    'slug' => $term->slug,
                    'name' => $term->name,
                    'count' => $term->count
                );
            }
            break;
    }
    
    wp_send_json_success($options);
}
add_action('wp_ajax_get_filter_options', 'pilgrimirl_get_filter_options');
add_action('wp_ajax_nopriv_get_filter_options', 'pilgrimirl_get_filter_options');

/**
 * Extract saints from post content and metadata
 */
function pilgrimirl_extract_saints_from_content() {
    $saints = array();
    $saint_counts = array();
    
    // Get all posts
    $posts = get_posts(array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
    
    // Count saints by actually checking each post with the same logic as filtering
    foreach ($posts as $post) {
        $content = $post->post_content;
        $title = $post->post_title;
        $communities_provenance = get_post_meta($post->ID, '_pilgrimirl_communities_provenance', true);
        $all_text = $content . ' ' . $title . ' ' . $communities_provenance;
        
        // Extract saint names using the same patterns as filtering
        $saint_patterns = array(
            '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
            '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
            '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
        );
        
        $post_saints = array();
        foreach ($saint_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches)) {
                foreach ($matches[1] as $saint_name) {
                    $saint_name = trim($saint_name);
                    $saint_name_lower = strtolower($saint_name);
                    
                    // Use same validation as the filtering function
                    $false_positives = array(
                        'times', 'until', 'after', 'before', 'during', 'within', 'about',
                        'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
                        'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
                        'century', 'period', 'time', 'year', 'date', 'early', 'late'
                    );
                    
                    if (strlen($saint_name) >= 3 && 
                        strlen($saint_name) <= 20 && 
                        !in_array($saint_name_lower, $false_positives) &&
                        !preg_match('/\d/', $saint_name) && 
                        !preg_match('/[^a-zA-Z\s]/', $saint_name)) {
                        
                        $saint_key = sanitize_title($saint_name);
                        $post_saints[$saint_key] = $saint_name;
                    }
                }
            }
        }
        
        // Count each saint only once per post
        foreach ($post_saints as $saint_key => $saint_name) {
            if (!isset($saint_counts[$saint_key])) {
                $saint_counts[$saint_key] = array(
                    'name' => $saint_name,
                    'count' => 0
                );
            }
            $saint_counts[$saint_key]['count']++;
        }
    }
    
    // Convert to format expected by frontend
    foreach ($saint_counts as $slug => $data) {
        if ($data['count'] >= 2) { // Only include saints with at least 2 occurrences
            $saints[] = array(
                'slug' => $slug,
                'name' => 'St. ' . $data['name'],
                'count' => $data['count']
            );
        }
    }
    
    // Sort by count (descending) then by name
    usort($saints, function($a, $b) {
        if ($a['count'] == $b['count']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['count'] - $a['count'];
    });
    
    return $saints;
}

/**
 * Extract centuries/historical periods from post content
 */
function pilgrimirl_extract_centuries_from_content() {
    $periods = array();
    $period_counts = array();
    
    // Get all posts
    $posts = get_posts(array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
    
    foreach ($posts as $post) {
        $content = $post->post_content;
        $title = $post->post_title;
        
        // Get communities_provenance and foundation_date meta fields
        $communities_provenance = get_post_meta($post->ID, '_pilgrimirl_communities_provenance', true);
        $foundation_date = get_post_meta($post->ID, '_pilgrimirl_foundation_date', true);
        $all_text = $content . ' ' . $title . ' ' . $communities_provenance . ' ' . $foundation_date;
        
        // Century patterns
        $century_patterns = array(
            '/(\d{1,2})(?:st|nd|rd|th)\s+century/i',
            '/(\d{3,4})\s*(?:AD|CE)?/i', // Years
            '/founded\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/built\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/established\s+(?:c\.?\s*)?(\d{3,4})/i'
        );
        
        foreach ($century_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches)) {
                foreach ($matches[1] as $match) {
                    $year_or_century = intval($match);
                    
                    if ($year_or_century > 20) { // It's a year
                        if ($year_or_century >= 400 && $year_or_century <= 2000) {
                            $century = ceil($year_or_century / 100);
                            $century_name = $century . pilgrimirl_get_ordinal_suffix($century) . ' Century';
                            $century_key = $century . 'th-century';
                        } else {
                            continue;
                        }
                    } else { // It's already a century number
                        if ($year_or_century >= 4 && $year_or_century <= 20) {
                            $century_name = $year_or_century . pilgrimirl_get_ordinal_suffix($year_or_century) . ' Century';
                            $century_key = $year_or_century . 'th-century';
                        } else {
                            continue;
                        }
                    }
                    
                    if (!isset($period_counts[$century_key])) {
                        $period_counts[$century_key] = array(
                            'name' => $century_name,
                            'count' => 0
                        );
                    }
                    $period_counts[$century_key]['count']++;
                }
            }
        }
        
        // Also look for specific historical periods
        $historical_periods = array(
            'Early Christian' => 'early-christian',
            'Medieval' => 'medieval',
            'Norman' => 'norman',
            'Anglo-Norman' => 'anglo-norman',
            'Gaelic' => 'gaelic',
            'Viking' => 'viking',
            'Reformation' => 'reformation',
            'Dissolution' => 'dissolution'
        );
        
        foreach ($historical_periods as $period_name => $period_slug) {
            if (stripos($all_text, $period_name) !== false) {
                if (!isset($period_counts[$period_slug])) {
                    $period_counts[$period_slug] = array(
                        'name' => $period_name,
                        'count' => 0
                    );
                }
                $period_counts[$period_slug]['count']++;
            }
        }
    }
    
    // Convert to format expected by frontend
    foreach ($period_counts as $slug => $data) {
        if ($data['count'] > 0) {
            $periods[] = array(
                'slug' => $slug,
                'name' => $data['name'],
                'count' => $data['count']
            );
        }
    }
    
    // Sort by name
    usort($periods, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    return $periods;
}

/**
 * Get ordinal suffix for numbers
 */
function pilgrimirl_get_ordinal_suffix($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
        return 'th';
    } else {
        return $ends[$number % 10];
    }
}

/**
 * Check if a post contains mentions of a specific saint
 */
function pilgrimirl_post_contains_saint($post_id, $saint_slug) {
    $post = get_post($post_id);
    if (!$post) return false;
    
    $content = $post->post_content;
    $title = $post->post_title;
    $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
    $all_text = $content . ' ' . $title . ' ' . $communities_provenance;
    
    // Convert saint slug back to name for searching
    // e.g., "enda" becomes "Enda"
    $saint_name = ucfirst(str_replace('-', ' ', $saint_slug));
    
    // Use the same patterns and validation as the extraction function
    $saint_patterns = array(
        '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
    );
    
    foreach ($saint_patterns as $pattern) {
        if (preg_match_all($pattern, $all_text, $matches)) {
            foreach ($matches[1] as $found_saint) {
                $found_saint = trim($found_saint);
                $found_saint_lower = strtolower($found_saint);
                $saint_name_lower = strtolower($saint_name);
                
                // Check if this matches our target saint
                if ($found_saint_lower === $saint_name_lower) {
                    // Apply same validation as extraction function
                    $false_positives = array(
                        'times', 'until', 'after', 'before', 'during', 'within', 'about',
                        'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
                        'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
                        'century', 'period', 'time', 'year', 'date', 'early', 'late'
                    );
                    
                    if (strlen($found_saint) >= 3 && 
                        strlen($found_saint) <= 20 && 
                        !in_array($found_saint_lower, $false_positives) &&
                        !preg_match('/\d/', $found_saint) && 
                        !preg_match('/[^a-zA-Z\s]/', $found_saint)) {
                        return true;
                    }
                }
            }
        }
    }
    
    return false;
}

/**
 * Check if a post contains mentions of a specific century/historical period
 */
function pilgrimirl_post_contains_century($post_id, $century_slug) {
    $post = get_post($post_id);
    if (!$post) return false;
    
    $content = $post->post_content;
    $title = $post->post_title;
    $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
    $foundation_date = get_post_meta($post_id, '_pilgrimirl_foundation_date', true);
    $all_text = $content . ' ' . $title . ' ' . $communities_provenance . ' ' . $foundation_date;
    
    error_log("PilgrimIRL Century Debug - Post ID: {$post_id}, Title: {$title}, Century Slug: {$century_slug}");
    
    // Handle different century slug formats
    if (preg_match('/(\d+)th-century/', $century_slug, $matches)) {
        $target_century = intval($matches[1]);
        $target_century_name = $target_century . pilgrimirl_get_ordinal_suffix($target_century) . ' Century';
        
        // Check for century mentions
        $century_patterns = array(
            '/(\d{1,2})(?:st|nd|rd|th)\s+century/i',
            '/(\d{3,4})\s*(?:AD|CE)?/i', // Years
            '/founded\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/built\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/established\s+(?:c\.?\s*)?(\d{3,4})/i'
        );
        
        foreach ($century_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches_inner)) {
                foreach ($matches_inner[1] as $match) {
                    $year_or_century = intval($match);
                    
                    if ($year_or_century > 20) { // It's a year
                        if ($year_or_century >= 400 && $year_or_century <= 2000) {
                            $century = ceil($year_or_century / 100);
                            if ($century == $target_century) {
                                return true;
                            }
                        }
                    } else { // It's already a century number
                        if ($year_or_century >= 4 && $year_or_century <= 20) {
                            if ($year_or_century == $target_century) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
    } else {
        // Handle historical period names
        $historical_periods = array(
            'early-christian' => 'Early Christian',
            'medieval' => 'Medieval',
            'norman' => 'Norman',
            'anglo-norman' => 'Anglo-Norman',
            'gaelic' => 'Gaelic',
            'viking' => 'Viking',
            'reformation' => 'Reformation',
            'dissolution' => 'Dissolution'
        );
        
        if (isset($historical_periods[$century_slug])) {
            $period_name = $historical_periods[$century_slug];
            if (stripos($all_text, $period_name) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get Irish Counties List
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

/**
 * Create default counties on theme activation
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
 * Flush rewrite rules on theme activation
 */
function pilgrimirl_flush_rewrite_rules() {
    pilgrimirl_register_post_types();
    pilgrimirl_register_taxonomies();
    flush_rewrite_rules();
    
    // Set homepage to show front page
    update_option('show_on_front', 'page');
    
    // Create a homepage if it doesn't exist
    $homepage = get_page_by_title('Home');
    if (!$homepage) {
        $homepage_id = wp_insert_post(array(
            'post_title' => 'Home',
            'post_content' => 'Welcome to PilgrimIRL - Discover Ireland\'s Sacred Heritage',
            'post_status' => 'publish',
            'post_type' => 'page'
        ));
        update_option('page_on_front', $homepage_id);
    } else {
        update_option('page_on_front', $homepage->ID);
    }
}
add_action('after_switch_theme', 'pilgrimirl_flush_rewrite_rules');

/**
 * ==========================================================================
 * Saints Page Functions
 * ==========================================================================
 */

/**
 * Get saints with metadata for the saints page
 * Returns array of saints with site counts and associated counties
 */
function pilgrimirl_get_saints_with_metadata() {
    // Check for cached data first
    $cached = get_transient('pilgrimirl_saints_metadata');
    if ($cached !== false) {
        return $cached;
    }

    $saints_data = array();
    $saint_details = array();

    // Get all posts
    $posts = get_posts(array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    foreach ($posts as $post) {
        $content = $post->post_content;
        $title = $post->post_title;
        $communities_provenance = get_post_meta($post->ID, '_pilgrimirl_communities_provenance', true);
        $all_text = $content . ' ' . $title . ' ' . $communities_provenance;

        // Get post counties
        $post_counties = wp_get_post_terms($post->ID, 'county', array('fields' => 'names'));
        if (is_wp_error($post_counties)) {
            $post_counties = array();
        }

        // Extract saint names
        $saint_patterns = array(
            '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
            '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
            '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
        );

        $false_positives = array(
            'times', 'until', 'after', 'before', 'during', 'within', 'about',
            'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
            'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
            'century', 'period', 'time', 'year', 'date', 'early', 'late',
            'well', 'cross', 'site', 'stone', 'holy', 'sacred'
        );

        $post_saints = array();
        foreach ($saint_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches)) {
                foreach ($matches[1] as $saint_name) {
                    $saint_name = trim($saint_name);
                    $saint_name_lower = strtolower($saint_name);

                    if (strlen($saint_name) >= 3 &&
                        strlen($saint_name) <= 20 &&
                        !in_array($saint_name_lower, $false_positives) &&
                        !preg_match('/\d/', $saint_name) &&
                        !preg_match('/[^a-zA-Z\s]/', $saint_name)) {

                        $saint_key = sanitize_title($saint_name);
                        $post_saints[$saint_key] = $saint_name;
                    }
                }
            }
        }

        // Aggregate saint data
        foreach ($post_saints as $saint_key => $saint_name) {
            if (!isset($saint_details[$saint_key])) {
                $saint_details[$saint_key] = array(
                    'name' => $saint_name,
                    'site_count' => 0,
                    'counties' => array(),
                    'site_types' => array()
                );
            }
            $saint_details[$saint_key]['site_count']++;

            // Add counties
            foreach ($post_counties as $county) {
                if (!in_array($county, $saint_details[$saint_key]['counties'])) {
                    $saint_details[$saint_key]['counties'][] = $county;
                }
            }

            // Add site type
            if (!in_array($post->post_type, $saint_details[$saint_key]['site_types'])) {
                $saint_details[$saint_key]['site_types'][] = $post->post_type;
            }
        }
    }

    // Convert to array format and filter
    foreach ($saint_details as $slug => $data) {
        if ($data['site_count'] >= 2) {
            $saints_data[] = array(
                'slug' => $slug,
                'name' => 'St. ' . $data['name'],
                'site_count' => $data['site_count'],
                'counties' => $data['counties'],
                'site_types' => $data['site_types']
            );
        }
    }

    // Sort by count descending, then by name
    usort($saints_data, function($a, $b) {
        if ($a['site_count'] == $b['site_count']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['site_count'] - $a['site_count'];
    });

    // Cache for 1 hour
    set_transient('pilgrimirl_saints_metadata', $saints_data, HOUR_IN_SECONDS);

    return $saints_data;
}

/**
 * AJAX handler for getting all sites for the saints page
 */
function pilgrimirl_get_saints_page_sites() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pilgrimirl_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $sites = array();

    $args = array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Get coordinates
            $latitude = get_post_meta($post_id, '_pilgrimirl_latitude', true);
            $longitude = get_post_meta($post_id, '_pilgrimirl_longitude', true);

            // Get counties
            $counties = wp_get_post_terms($post_id, 'county', array('fields' => 'names'));
            if (is_wp_error($counties)) {
                $counties = array();
            }

            // Extract saints from content
            $saints = pilgrimirl_extract_saints_from_post_content($post_id);

            // Get communities provenance for additional saint matching
            $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);

            // Get thumbnail
            $thumbnail = '';
            if (has_post_thumbnail($post_id)) {
                $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
            }

            $sites[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'excerpt' => wp_trim_words(get_the_excerpt(), 25, '...'),
                'permalink' => get_permalink(),
                'post_type' => get_post_type(),
                'latitude' => $latitude ? floatval($latitude) : null,
                'longitude' => $longitude ? floatval($longitude) : null,
                'county' => $counties,
                'saints' => $saints,
                'communities_provenance' => $communities_provenance,
                'thumbnail' => $thumbnail
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($sites);
}
add_action('wp_ajax_get_saints_page_sites', 'pilgrimirl_get_saints_page_sites');
add_action('wp_ajax_nopriv_get_saints_page_sites', 'pilgrimirl_get_saints_page_sites');

/**
 * Extract saints from a specific post's content
 */
function pilgrimirl_extract_saints_from_post_content($post_id) {
    $post = get_post($post_id);
    if (!$post) return array();

    $content = $post->post_content;
    $title = $post->post_title;
    $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
    $all_text = $content . ' ' . $title . ' ' . $communities_provenance;

    $saints = array();

    $saint_patterns = array(
        '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
    );

    $false_positives = array(
        'times', 'until', 'after', 'before', 'during', 'within', 'about',
        'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
        'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
        'century', 'period', 'time', 'year', 'date', 'early', 'late',
        'well', 'cross', 'site', 'stone', 'holy', 'sacred'
    );

    foreach ($saint_patterns as $pattern) {
        if (preg_match_all($pattern, $all_text, $matches)) {
            foreach ($matches[1] as $saint_name) {
                $saint_name = trim($saint_name);
                $saint_name_lower = strtolower($saint_name);

                if (strlen($saint_name) >= 3 &&
                    strlen($saint_name) <= 20 &&
                    !in_array($saint_name_lower, $false_positives) &&
                    !preg_match('/\d/', $saint_name) &&
                    !preg_match('/[^a-zA-Z\s]/', $saint_name)) {

                    $formatted_name = 'St. ' . $saint_name;
                    if (!in_array($formatted_name, $saints)) {
                        $saints[] = $formatted_name;
                    }
                }
            }
        }
    }

    return $saints;
}

/**
 * Clear saints cache when posts are updated
 */
function pilgrimirl_clear_saints_cache($post_id) {
    $post_type = get_post_type($post_id);
    if (in_array($post_type, array('monastic_site', 'pilgrimage_route', 'christian_site'))) {
        delete_transient('pilgrimirl_saints_metadata');
    }
}
add_action('save_post', 'pilgrimirl_clear_saints_cache');
add_action('delete_post', 'pilgrimirl_clear_saints_cache');

/**
 * Include data importer and utilities
 */
require_once get_stylesheet_directory() . '/includes/data-importer.php';

// Development tools - only load for administrators in local/development environments
if (is_admin() && current_user_can('manage_options') && WP_ENVIRONMENT_TYPE === 'local') {
    // Import utilities moved to _dev-tools folder
    // Uncomment as needed for data imports:
    // require_once get_stylesheet_directory() . '/_dev-tools/import-holy-wells.php';
    // require_once get_stylesheet_directory() . '/_dev-tools/import-high-crosses.php';
    // require_once get_stylesheet_directory() . '/_dev-tools/cleanup-christian-sites.php';
}

/**
 * Handle iCal Export for Liturgical Calendar (Admin only)
 */
function pilgrimirl_handle_ical_export() {
    if (!isset($_GET['ical']) || $_GET['ical'] !== 'export') {
        return;
    }

    // Only on calendar page
    if (!is_page('calendar') && !is_page_template('page-calendar.php')) {
        return;
    }

    // Restrict to logged-in admins only
    if (!current_user_can('manage_options')) {
        wp_die('Access denied. iCal export is restricted to administrators.', 'Access Denied', array('response' => 403));
    }

    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    // Only allow valid years
    if (!in_array($year, array(2025, 2026))) {
        $year = 2025;
    }

    // Load calendar data
    $calendar_file = get_stylesheet_directory() . '/includes/calendar-data-' . $year . '.php';
    if (!file_exists($calendar_file)) {
        return;
    }

    $calendar_data = include($calendar_file);
    if (!$calendar_data) {
        return;
    }

    // Generate iCal content
    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//PilgrimIRL//Irish Catholic Liturgical Calendar//EN\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";
    $ical .= "METHOD:PUBLISH\r\n";
    $ical .= "X-WR-CALNAME:Irish Catholic Liturgical Calendar " . $year . "\r\n";
    $ical .= "X-WR-TIMEZONE:Europe/Dublin\r\n";

    foreach ($calendar_data['months'] as $month_num => $month_data) {
        if (empty($month_data['days'])) {
            continue;
        }

        foreach ($month_data['days'] as $day_num => $day_data) {
            $date = sprintf('%04d%02d%02d', $year, $month_num, $day_num);
            $uid = md5($date . $day_data['name']) . '@pilgrimirl.com';

            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "DTSTART;VALUE=DATE:" . $date . "\r\n";
            $ical .= "DTEND;VALUE=DATE:" . $date . "\r\n";
            $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $ical .= "UID:" . $uid . "\r\n";
            $ical .= "SUMMARY:" . pilgrimirl_ical_escape($day_data['name']) . "\r\n";

            $description = 'Rank: ' . ucfirst($day_data['rank']) . ' | Color: ' . ucfirst($day_data['color']);
            if (isset($day_data['irish']) && $day_data['irish']) {
                $description .= ' | Irish Saint';
            }
            $ical .= "DESCRIPTION:" . pilgrimirl_ical_escape($description) . "\r\n";

            $categories = array('Liturgical');
            if (isset($day_data['irish']) && $day_data['irish']) {
                $categories[] = 'Irish Saint';
            }
            $categories[] = ucfirst($day_data['rank']);
            $ical .= "CATEGORIES:" . implode(',', $categories) . "\r\n";

            $ical .= "END:VEVENT\r\n";
        }
    }

    $ical .= "END:VCALENDAR\r\n";

    // Send headers
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="irish-liturgical-calendar-' . $year . '.ics"');
    header('Content-Length: ' . strlen($ical));

    echo $ical;
    exit;
}
add_action('template_redirect', 'pilgrimirl_handle_ical_export');

/**
 * Escape text for iCal format
 */
function pilgrimirl_ical_escape($text) {
    $text = str_replace(array("\r\n", "\n", "\r"), "\\n", $text);
    $text = str_replace(array(",", ";", "\\"), array("\\,", "\\;", "\\\\"), $text);
    return $text;
}

/**
 * REST API Endpoint for Calendar Events (for Telegram integration)
 */
function pilgrimirl_register_calendar_api() {
    register_rest_route('pilgrimirl/v1', '/calendar', array(
        'methods' => 'GET',
        'callback' => 'pilgrimirl_get_calendar_events',
        'permission_callback' => '__return_true',
        'args' => array(
            'year' => array(
                'default' => date('Y'),
                'validate_callback' => function($param) {
                    return is_numeric($param) && in_array(intval($param), array(2025, 2026));
                }
            ),
            'month' => array(
                'default' => null,
                'validate_callback' => function($param) {
                    return is_null($param) || (is_numeric($param) && $param >= 1 && $param <= 12);
                }
            ),
            'irish_only' => array(
                'default' => false,
                'validate_callback' => function($param) {
                    return is_bool($param) || $param === 'true' || $param === 'false';
                }
            ),
        ),
    ));

    register_rest_route('pilgrimirl/v1', '/calendar/today', array(
        'methods' => 'GET',
        'callback' => 'pilgrimirl_get_today_feast',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('pilgrimirl/v1', '/calendar/upcoming', array(
        'methods' => 'GET',
        'callback' => 'pilgrimirl_get_upcoming_feasts',
        'permission_callback' => '__return_true',
        'args' => array(
            'days' => array(
                'default' => 7,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param >= 1 && $param <= 30;
                }
            ),
        ),
    ));
}
add_action('rest_api_init', 'pilgrimirl_register_calendar_api');

/**
 * Get calendar events via REST API
 */
function pilgrimirl_get_calendar_events($request) {
    $year = intval($request->get_param('year'));
    $month = $request->get_param('month');
    $irish_only = $request->get_param('irish_only');

    if ($irish_only === 'true') {
        $irish_only = true;
    }

    $calendar_file = get_stylesheet_directory() . '/includes/calendar-data-' . $year . '.php';
    if (!file_exists($calendar_file)) {
        return new WP_Error('no_calendar', 'Calendar data not found', array('status' => 404));
    }

    $calendar_data = include($calendar_file);
    $events = array();

    foreach ($calendar_data['months'] as $month_num => $month_data) {
        if ($month && intval($month) !== $month_num) {
            continue;
        }

        if (empty($month_data['days'])) {
            continue;
        }

        foreach ($month_data['days'] as $day_num => $day_data) {
            if ($irish_only && !isset($day_data['irish'])) {
                continue;
            }

            $events[] = array(
                'date' => sprintf('%04d-%02d-%02d', $year, $month_num, $day_num),
                'name' => $day_data['name'],
                'rank' => $day_data['rank'],
                'color' => $day_data['color'],
                'irish' => isset($day_data['irish']) ? true : false,
                'season' => isset($day_data['season']) ? $day_data['season'] : null,
            );
        }
    }

    return rest_ensure_response(array(
        'year' => $year,
        'count' => count($events),
        'events' => $events,
    ));
}

/**
 * Get today's feast via REST API
 */
function pilgrimirl_get_today_feast($request) {
    $year = intval(date('Y'));
    $month = intval(date('n'));
    $day = intval(date('j'));

    $calendar_file = get_stylesheet_directory() . '/includes/calendar-data-' . $year . '.php';
    if (!file_exists($calendar_file)) {
        return rest_ensure_response(array('feast' => null, 'message' => 'No calendar data for this year'));
    }

    $calendar_data = include($calendar_file);

    if (isset($calendar_data['months'][$month]['days'][$day])) {
        $feast = $calendar_data['months'][$month]['days'][$day];
        return rest_ensure_response(array(
            'date' => date('Y-m-d'),
            'feast' => array(
                'name' => $feast['name'],
                'rank' => $feast['rank'],
                'color' => $feast['color'],
                'irish' => isset($feast['irish']) ? true : false,
            ),
        ));
    }

    return rest_ensure_response(array(
        'date' => date('Y-m-d'),
        'feast' => null,
        'message' => 'No special feast day today',
    ));
}

/**
 * Get upcoming feasts via REST API
 */
function pilgrimirl_get_upcoming_feasts($request) {
    $days = intval($request->get_param('days'));
    $year = intval(date('Y'));

    $calendar_file = get_stylesheet_directory() . '/includes/calendar-data-' . $year . '.php';
    if (!file_exists($calendar_file)) {
        return new WP_Error('no_calendar', 'Calendar data not found', array('status' => 404));
    }

    $calendar_data = include($calendar_file);
    $upcoming = array();
    $today = new DateTime();

    for ($i = 0; $i <= $days; $i++) {
        $check_date = clone $today;
        $check_date->add(new DateInterval('P' . $i . 'D'));

        $check_year = intval($check_date->format('Y'));
        $check_month = intval($check_date->format('n'));
        $check_day = intval($check_date->format('j'));

        // Handle year boundary
        if ($check_year !== $year) {
            continue;
        }

        if (isset($calendar_data['months'][$check_month]['days'][$check_day])) {
            $feast = $calendar_data['months'][$check_month]['days'][$check_day];
            $upcoming[] = array(
                'date' => $check_date->format('Y-m-d'),
                'days_until' => $i,
                'name' => $feast['name'],
                'rank' => $feast['rank'],
                'color' => $feast['color'],
                'irish' => isset($feast['irish']) ? true : false,
            );
        }
    }

    return rest_ensure_response(array(
        'from' => date('Y-m-d'),
        'to' => $today->add(new DateInterval('P' . $days . 'D'))->format('Y-m-d'),
        'count' => count($upcoming),
        'feasts' => $upcoming,
    ));
}
