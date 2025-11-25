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
 * Register Calendar Event Taxonomies
 */
function pilgrimirl_register_calendar_taxonomies() {
    // Liturgical Rank taxonomy
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

    // Liturgical Color taxonomy
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

    // Liturgical Season taxonomy
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
add_action('init', 'pilgrimirl_register_calendar_taxonomies');

/**
 * Add meta boxes for Calendar Events
 */
function pilgrimirl_add_calendar_meta_boxes() {
    add_meta_box(
        'calendar_event_details',
        'Event Details',
        'pilgrimirl_calendar_event_meta_box',
        'calendar_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'pilgrimirl_add_calendar_meta_boxes');

/**
 * Render Calendar Event meta box
 */
function pilgrimirl_calendar_event_meta_box($post) {
    wp_nonce_field('pilgrimirl_calendar_event_nonce', 'calendar_event_nonce');

    $event_date = get_post_meta($post->ID, '_calendar_event_date', true);
    $event_month = get_post_meta($post->ID, '_calendar_event_month', true);
    $event_day = get_post_meta($post->ID, '_calendar_event_day', true);
    $event_year = get_post_meta($post->ID, '_calendar_event_year', true);
    $is_irish_saint = get_post_meta($post->ID, '_calendar_is_irish_saint', true);
    $is_moveable = get_post_meta($post->ID, '_calendar_is_moveable', true);
    $related_sites = get_post_meta($post->ID, '_calendar_related_sites', true);
    $traditions = get_post_meta($post->ID, '_calendar_traditions', true);
    $significance = get_post_meta($post->ID, '_calendar_significance', true);
    ?>
    <style>
        .calendar-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .calendar-meta-field { margin-bottom: 15px; }
        .calendar-meta-field label { display: block; font-weight: 600; margin-bottom: 5px; color: #1e4620; }
        .calendar-meta-field input[type="text"],
        .calendar-meta-field input[type="number"],
        .calendar-meta-field select,
        .calendar-meta-field textarea { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        .calendar-meta-field textarea { min-height: 100px; }
        .calendar-meta-field input[type="checkbox"] { margin-right: 8px; }
        .calendar-meta-section { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .calendar-meta-section h4 { margin: 0 0 15px; color: #1e4620; border-bottom: 2px solid #2d5016; padding-bottom: 8px; }
    </style>

    <div class="calendar-meta-section">
        <h4>Date Information</h4>
        <div class="calendar-meta-grid">
            <div class="calendar-meta-field">
                <label for="calendar_event_month">Month</label>
                <select name="calendar_event_month" id="calendar_event_month">
                    <?php
                    $months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
                    foreach ($months as $num => $name) {
                        $selected = ($event_month == $num) ? 'selected' : '';
                        echo "<option value=\"$num\" $selected>$name</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="calendar-meta-field">
                <label for="calendar_event_day">Day of Month</label>
                <input type="number" name="calendar_event_day" id="calendar_event_day" value="<?php echo esc_attr($event_day); ?>" min="1" max="31">
            </div>
            <div class="calendar-meta-field">
                <label for="calendar_event_year">Year (leave blank for recurring)</label>
                <input type="number" name="calendar_event_year" id="calendar_event_year" value="<?php echo esc_attr($event_year); ?>" min="2024" max="2100" placeholder="All years">
            </div>
        </div>
        <div class="calendar-meta-field">
            <label>
                <input type="checkbox" name="calendar_is_moveable" value="1" <?php checked($is_moveable, '1'); ?>>
                This is a moveable feast (date varies by year, e.g., Easter)
            </label>
        </div>
    </div>

    <div class="calendar-meta-section">
        <h4>Irish Connection</h4>
        <div class="calendar-meta-field">
            <label>
                <input type="checkbox" name="calendar_is_irish_saint" value="1" <?php checked($is_irish_saint, '1'); ?>>
                Irish Saint / Special Irish Celebration
            </label>
        </div>
        <div class="calendar-meta-field">
            <label for="calendar_related_sites">Related Sacred Sites</label>
            <textarea name="calendar_related_sites" id="calendar_related_sites" placeholder="e.g., Croagh Patrick, Glendalough, Knock Shrine"><?php echo esc_textarea($related_sites); ?></textarea>
        </div>
    </div>

    <div class="calendar-meta-section">
        <h4>Additional Information</h4>
        <div class="calendar-meta-field">
            <label for="calendar_significance">Significance</label>
            <textarea name="calendar_significance" id="calendar_significance" placeholder="Explain the spiritual and historical significance of this feast day"><?php echo esc_textarea($significance); ?></textarea>
        </div>
        <div class="calendar-meta-field">
            <label for="calendar_traditions">Traditions & Customs</label>
            <textarea name="calendar_traditions" id="calendar_traditions" placeholder="Describe any Irish traditions, customs, or practices associated with this feast"><?php echo esc_textarea($traditions); ?></textarea>
        </div>
    </div>
    <?php
}

/**
 * Save Calendar Event meta
 */
function pilgrimirl_save_calendar_event_meta($post_id) {
    if (!isset($_POST['calendar_event_nonce']) || !wp_verify_nonce($_POST['calendar_event_nonce'], 'pilgrimirl_calendar_event_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'calendar_event_month' => '_calendar_event_month',
        'calendar_event_day' => '_calendar_event_day',
        'calendar_event_year' => '_calendar_event_year',
        'calendar_is_irish_saint' => '_calendar_is_irish_saint',
        'calendar_is_moveable' => '_calendar_is_moveable',
        'calendar_related_sites' => '_calendar_related_sites',
        'calendar_traditions' => '_calendar_traditions',
        'calendar_significance' => '_calendar_significance',
    );

    foreach ($fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }

    // Generate composite date for sorting/filtering
    $month = isset($_POST['calendar_event_month']) ? intval($_POST['calendar_event_month']) : 1;
    $day = isset($_POST['calendar_event_day']) ? intval($_POST['calendar_event_day']) : 1;
    $composite_date = sprintf('%02d-%02d', $month, $day);
    update_post_meta($post_id, '_calendar_event_date', $composite_date);
}
add_action('save_post_calendar_event', 'pilgrimirl_save_calendar_event_meta');

/**
 * Add custom columns to Calendar Events list
 */
function pilgrimirl_calendar_event_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['event_date'] = 'Date';
            $new_columns['irish_saint'] = 'Irish';
        }
    }
    return $new_columns;
}
add_filter('manage_calendar_event_posts_columns', 'pilgrimirl_calendar_event_columns');

/**
 * Populate custom columns
 */
function pilgrimirl_calendar_event_column_content($column, $post_id) {
    switch ($column) {
        case 'event_date':
            $month = get_post_meta($post_id, '_calendar_event_month', true);
            $day = get_post_meta($post_id, '_calendar_event_day', true);
            $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
            if ($month && $day) {
                echo esc_html($months[$month] . ' ' . $day);
            }
            break;
        case 'irish_saint':
            $is_irish = get_post_meta($post_id, '_calendar_is_irish_saint', true);
            echo $is_irish ? '<span style="color: #2d5016; font-size: 1.2em;">★</span>' : '—';
            break;
    }
}
add_action('manage_calendar_event_posts_custom_column', 'pilgrimirl_calendar_event_column_content', 10, 2);

/**
 * Make columns sortable
 */
function pilgrimirl_calendar_event_sortable_columns($columns) {
    $columns['event_date'] = 'event_date';
    return $columns;
}
add_filter('manage_edit-calendar_event_sortable_columns', 'pilgrimirl_calendar_event_sortable_columns');

/**
 * Handle sorting by event date
 */
function pilgrimirl_calendar_event_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') === 'calendar_event') {
        if ($query->get('orderby') === 'event_date' || $query->get('orderby') === '') {
            $query->set('meta_key', '_calendar_event_date');
            $query->set('orderby', 'meta_value');
        }
    }
}
add_action('pre_get_posts', 'pilgrimirl_calendar_event_orderby');

/**
 * Insert default liturgical ranks
 */
function pilgrimirl_insert_default_calendar_terms() {
    // Check if already inserted
    if (get_option('pilgrimirl_calendar_terms_inserted')) {
        return;
    }

    // Liturgical Ranks
    $ranks = array(
        'solemnity' => 'Solemnity - Highest rank celebrations',
        'feast' => 'Feast - Important celebrations',
        'memorial' => 'Memorial - Remembrance of saints',
        'optional-memorial' => 'Optional Memorial - Optional remembrance',
        'commemoration' => 'Commemoration - Simple remembrance',
    );

    foreach ($ranks as $slug => $description) {
        if (!term_exists($slug, 'liturgical_rank')) {
            wp_insert_term(ucfirst(str_replace('-', ' ', $slug)), 'liturgical_rank', array(
                'slug' => $slug,
                'description' => $description
            ));
        }
    }

    // Liturgical Colors
    $colors = array(
        'white' => 'Joy, purity, glory - Used for feasts of the Lord, Mary, saints who were not martyrs',
        'red' => 'Passion, fire of the Holy Spirit, martyrdom - Used for Palm Sunday, Good Friday, Pentecost, martyrs',
        'green' => 'Ordinary Time, hope, growth - Used during Ordinary Time',
        'violet' => 'Penance, preparation - Used during Advent and Lent',
        'rose' => 'Joy in the midst of penance - Used on Gaudete Sunday (Advent) and Laetare Sunday (Lent)',
    );

    foreach ($colors as $slug => $description) {
        if (!term_exists($slug, 'liturgical_color')) {
            wp_insert_term(ucfirst($slug), 'liturgical_color', array(
                'slug' => $slug,
                'description' => $description
            ));
        }
    }

    // Liturgical Seasons
    $seasons = array(
        'advent' => 'Preparation for Christmas',
        'christmas' => 'Christmas Time - Birth of Christ',
        'ordinary-time' => 'Ordinary Time - Growth in faith',
        'lent' => 'Preparation for Easter',
        'easter' => 'Easter Time - Resurrection of Christ',
    );

    foreach ($seasons as $slug => $description) {
        if (!term_exists($slug, 'liturgical_season')) {
            wp_insert_term(ucfirst(str_replace('-', ' ', $slug)), 'liturgical_season', array(
                'slug' => $slug,
                'description' => $description
            ));
        }
    }

    update_option('pilgrimirl_calendar_terms_inserted', true);
}
add_action('init', 'pilgrimirl_insert_default_calendar_terms', 20);
