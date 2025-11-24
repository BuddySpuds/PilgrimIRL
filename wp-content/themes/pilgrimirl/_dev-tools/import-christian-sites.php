<?php
/**
 * Christian Sites Data Import Script
 * 
 * Imports Holy Wells and High Crosses data into the new Christian Sites post type
 * Run this script once to migrate existing data to the new schema
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Check if user has admin privileges
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to run this script.');
}

// Set execution time limit
set_time_limit(300); // 5 minutes

echo "<h1>Christian Sites Data Import</h1>\n";
echo "<p>Starting import process...</p>\n";

// Flush output
if (ob_get_level()) {
    ob_end_flush();
}
flush();

/**
 * Import Holy Wells data
 */
function import_holy_wells() {
    echo "<h2>Importing Holy Wells...</h2>\n";
    
    $holy_wells_file = get_stylesheet_directory() . '/../../../holy_wells.json';
    
    if (!file_exists($holy_wells_file)) {
        echo "<p style='color: red;'>Holy Wells JSON file not found at: $holy_wells_file</p>\n";
        return;
    }
    
    $json_data = file_get_contents($holy_wells_file);
    $wells_data = json_decode($json_data, true);
    
    if (!$wells_data) {
        echo "<p style='color: red;'>Failed to parse Holy Wells JSON data</p>\n";
        return;
    }
    
    $imported_count = 0;
    $skipped_count = 0;
    
    foreach ($wells_data as $well) {
        // Check if post already exists
        $existing_post = get_page_by_title($well['name'], OBJECT, 'christian_site');
        if ($existing_post) {
            echo "<p>Skipping existing well: " . esc_html($well['name']) . "</p>\n";
            $skipped_count++;
            continue;
        }
        
        // Create post data
        $post_data = array(
            'post_title' => sanitize_text_field($well['name']),
            'post_content' => wp_kses_post($well['description'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'christian_site',
            'post_author' => 1
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            echo "<p style='color: red;'>Failed to create post for: " . esc_html($well['name']) . "</p>\n";
            continue;
        }
        
        // Add site type taxonomy
        wp_set_post_terms($post_id, array('Holy Well'), 'site_type');
        
        // Add county if available
        if (!empty($well['county'])) {
            $county_term = get_term_by('name', $well['county'], 'county');
            if (!$county_term) {
                // Create county if it doesn't exist
                $county_result = wp_insert_term($well['county'], 'county');
                if (!is_wp_error($county_result)) {
                    wp_set_post_terms($post_id, array($well['county']), 'county');
                }
            } else {
                wp_set_post_terms($post_id, array($well['county']), 'county');
            }
        }
        
        // Add location data
        if (!empty($well['latitude']) && !empty($well['longitude'])) {
            update_post_meta($post_id, '_pilgrimirl_latitude', sanitize_text_field($well['latitude']));
            update_post_meta($post_id, '_pilgrimirl_longitude', sanitize_text_field($well['longitude']));
        }
        
        // Add address if available
        if (!empty($well['location'])) {
            update_post_meta($post_id, '_pilgrimirl_address', sanitize_text_field($well['location']));
        }
        
        // Add alternative names if available
        if (!empty($well['alternative_names'])) {
            if (is_array($well['alternative_names'])) {
                $alt_names = implode("\n", $well['alternative_names']);
            } else {
                $alt_names = $well['alternative_names'];
            }
            update_post_meta($post_id, '_pilgrimirl_alternative_names', sanitize_textarea_field($alt_names));
        }
        
        // Add historical information
        if (!empty($well['historical_period'])) {
            wp_set_post_terms($post_id, array($well['historical_period']), 'historical_period');
        }
        
        if (!empty($well['associated_saints'])) {
            if (is_array($well['associated_saints'])) {
                wp_set_post_terms($post_id, $well['associated_saints'], 'associated_saints');
            } else {
                wp_set_post_terms($post_id, array($well['associated_saints']), 'associated_saints');
            }
        }
        
        // Add foundation date if available
        if (!empty($well['foundation_date'])) {
            update_post_meta($post_id, '_pilgrimirl_foundation_date', sanitize_text_field($well['foundation_date']));
        }
        
        // Add site status
        if (!empty($well['status'])) {
            wp_set_post_terms($post_id, array($well['status']), 'site_status');
        } else {
            wp_set_post_terms($post_id, array('Active'), 'site_status');
        }
        
        $imported_count++;
        echo "<p>✓ Imported Holy Well: " . esc_html($well['name']) . "</p>\n";
        
        // Flush output periodically
        if ($imported_count % 10 == 0) {
            flush();
        }
    }
    
    echo "<p><strong>Holy Wells Import Complete:</strong> $imported_count imported, $skipped_count skipped</p>\n";
}

/**
 * Import High Crosses data
 */
function import_high_crosses() {
    echo "<h2>Importing High Crosses...</h2>\n";
    
    $high_crosses_file = get_stylesheet_directory() . '/../../../high_crosses.json';
    
    if (!file_exists($high_crosses_file)) {
        echo "<p style='color: red;'>High Crosses JSON file not found at: $high_crosses_file</p>\n";
        return;
    }
    
    $json_data = file_get_contents($high_crosses_file);
    $crosses_data = json_decode($json_data, true);
    
    if (!$crosses_data) {
        echo "<p style='color: red;'>Failed to parse High Crosses JSON data</p>\n";
        return;
    }
    
    $imported_count = 0;
    $skipped_count = 0;
    
    foreach ($crosses_data as $cross) {
        // Check if post already exists
        $existing_post = get_page_by_title($cross['name'], OBJECT, 'christian_site');
        if ($existing_post) {
            echo "<p>Skipping existing cross: " . esc_html($cross['name']) . "</p>\n";
            $skipped_count++;
            continue;
        }
        
        // Create post data
        $post_data = array(
            'post_title' => sanitize_text_field($cross['name']),
            'post_content' => wp_kses_post($cross['description'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'christian_site',
            'post_author' => 1
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            echo "<p style='color: red;'>Failed to create post for: " . esc_html($cross['name']) . "</p>\n";
            continue;
        }
        
        // Add site type taxonomy
        wp_set_post_terms($post_id, array('High Cross'), 'site_type');
        
        // Add county if available
        if (!empty($cross['county'])) {
            $county_term = get_term_by('name', $cross['county'], 'county');
            if (!$county_term) {
                // Create county if it doesn't exist
                $county_result = wp_insert_term($cross['county'], 'county');
                if (!is_wp_error($county_result)) {
                    wp_set_post_terms($post_id, array($cross['county']), 'county');
                }
            } else {
                wp_set_post_terms($post_id, array($cross['county']), 'county');
            }
        }
        
        // Add location data
        if (!empty($cross['latitude']) && !empty($cross['longitude'])) {
            update_post_meta($post_id, '_pilgrimirl_latitude', sanitize_text_field($cross['latitude']));
            update_post_meta($post_id, '_pilgrimirl_longitude', sanitize_text_field($cross['longitude']));
        }
        
        // Add address if available
        if (!empty($cross['location'])) {
            update_post_meta($post_id, '_pilgrimirl_address', sanitize_text_field($cross['location']));
        }
        
        // Add alternative names if available
        if (!empty($cross['alternative_names'])) {
            if (is_array($cross['alternative_names'])) {
                $alt_names = implode("\n", $cross['alternative_names']);
            } else {
                $alt_names = $cross['alternative_names'];
            }
            update_post_meta($post_id, '_pilgrimirl_alternative_names', sanitize_textarea_field($alt_names));
        }
        
        // Add historical information
        if (!empty($cross['period'])) {
            wp_set_post_terms($post_id, array($cross['period']), 'historical_period');
        }
        
        if (!empty($cross['century'])) {
            wp_set_post_terms($post_id, array($cross['century']), 'century');
        }
        
        if (!empty($cross['associated_saints'])) {
            if (is_array($cross['associated_saints'])) {
                wp_set_post_terms($post_id, $cross['associated_saints'], 'associated_saints');
            } else {
                wp_set_post_terms($post_id, array($cross['associated_saints']), 'associated_saints');
            }
        }
        
        // Add foundation date if available
        if (!empty($cross['date_erected'])) {
            update_post_meta($post_id, '_pilgrimirl_foundation_date', sanitize_text_field($cross['date_erected']));
        }
        
        // Add site status
        if (!empty($cross['condition'])) {
            wp_set_post_terms($post_id, array($cross['condition']), 'site_status');
        } else {
            wp_set_post_terms($post_id, array('Standing'), 'site_status');
        }
        
        // Add height information if available
        if (!empty($cross['height'])) {
            update_post_meta($post_id, '_pilgrimirl_height', sanitize_text_field($cross['height']));
        }
        
        $imported_count++;
        echo "<p>✓ Imported High Cross: " . esc_html($cross['name']) . "</p>\n";
        
        // Flush output periodically
        if ($imported_count % 10 == 0) {
            flush();
        }
    }
    
    echo "<p><strong>High Crosses Import Complete:</strong> $imported_count imported, $skipped_count skipped</p>\n";
}

/**
 * Create default site type terms
 */
function create_default_site_types() {
    echo "<h2>Creating Default Site Types...</h2>\n";
    
    $site_types = array(
        'Holy Well' => 'Sacred wells associated with saints and healing',
        'High Cross' => 'Ornate stone crosses from the medieval period',
        'Mass Rock' => 'Outdoor altars used during penal times',
        'Christian Ruin' => 'Remains of ancient Christian structures',
        'Church Ruin' => 'Ruins of medieval and early Christian churches',
        'Abbey Ruin' => 'Remains of monastic abbey buildings',
        'Priory Ruin' => 'Ruins of religious priory buildings',
        'Cathedral Ruin' => 'Remains of cathedral structures',
        'Chapel Ruin' => 'Small church or chapel ruins',
        'Monastery Ruin' => 'Monastic building remains'
    );
    
    foreach ($site_types as $name => $description) {
        $existing_term = get_term_by('name', $name, 'site_type');
        if (!$existing_term) {
            $result = wp_insert_term($name, 'site_type', array(
                'description' => $description
            ));
            if (!is_wp_error($result)) {
                echo "<p>✓ Created site type: $name</p>\n";
            } else {
                echo "<p style='color: red;'>Failed to create site type: $name</p>\n";
            }
        } else {
            echo "<p>Site type already exists: $name</p>\n";
        }
    }
}

/**
 * Create default site status terms
 */
function create_default_site_statuses() {
    echo "<h2>Creating Default Site Statuses...</h2>\n";
    
    $statuses = array(
        'Active' => 'Site is active and accessible',
        'Standing' => 'Structure is standing and intact',
        'Ruined' => 'Structure is in ruins',
        'Partially Ruined' => 'Structure is partially damaged',
        'Restored' => 'Structure has been restored',
        'Archaeological' => 'Only archaeological remains',
        'Lost' => 'Site location is lost or destroyed',
        'Private' => 'Site is on private property',
        'Restricted Access' => 'Access to site is restricted'
    );
    
    foreach ($statuses as $name => $description) {
        $existing_term = get_term_by('name', $name, 'site_status');
        if (!$existing_term) {
            $result = wp_insert_term($name, 'site_status', array(
                'description' => $description
            ));
            if (!is_wp_error($result)) {
                echo "<p>✓ Created site status: $name</p>\n";
            } else {
                echo "<p style='color: red;'>Failed to create site status: $name</p>\n";
            }
        } else {
            echo "<p>Site status already exists: $name</p>\n";
        }
    }
}

// Main execution
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px; padding: 20px; background: #f9f9f9; border-radius: 8px;'>\n";

// Create default terms first
create_default_site_types();
create_default_site_statuses();

// Import data
import_holy_wells();
import_high_crosses();

echo "<h2 style='color: green;'>Import Process Complete!</h2>\n";
echo "<p>All Christian Sites data has been imported successfully.</p>\n";
echo "<p><a href='" . admin_url('edit.php?post_type=christian_site') . "'>View Christian Sites in Admin</a></p>\n";
echo "<p><a href='" . get_post_type_archive_link('christian_site') . "'>View Christian Sites Archive</a></p>\n";

echo "</div>\n";

// Flush rewrite rules to ensure new post type URLs work
flush_rewrite_rules();
?>
