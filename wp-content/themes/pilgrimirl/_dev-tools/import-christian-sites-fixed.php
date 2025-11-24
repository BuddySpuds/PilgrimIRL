<?php
/**
 * Christian Sites Data Import Script - Fixed Version
 * 
 * Imports Monastic Sites and High Crosses data into the new Christian Sites post type
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

echo "<h1>Christian Sites Data Import - Fixed Version</h1>\n";
echo "<p>Starting import process...</p>\n";

// Flush output
if (ob_get_level()) {
    ob_end_flush();
}
flush();

/**
 * Import Monastic Sites data from county JSON files
 */
function import_monastic_sites() {
    echo "<h2>Importing Monastic Sites...</h2>\n";
    
    $monastic_sites_dir = get_stylesheet_directory() . '/../../../MonasticSites_JSON/';
    
    if (!is_dir($monastic_sites_dir)) {
        echo "<p style='color: red;'>MonasticSites_JSON directory not found at: $monastic_sites_dir</p>\n";
        return;
    }
    
    $imported_count = 0;
    $skipped_count = 0;
    $files = glob($monastic_sites_dir . '*-enriched.json');
    
    foreach ($files as $file) {
        echo "<h3>Processing: " . basename($file) . "</h3>\n";
        
        $json_data = file_get_contents($file);
        $sites_data = json_decode($json_data, true);
        
        if (!$sites_data) {
            echo "<p style='color: red;'>Failed to parse JSON data from: " . basename($file) . "</p>\n";
            continue;
        }
        
        foreach ($sites_data as $site) {
            if (empty($site['foundation_name'])) {
                continue;
            }
            
            // Clean up the foundation name
            $foundation_name = trim(str_replace(['~', 'ø', '#', '^', '*', '≈', '=', '+'], '', $site['foundation_name']));
            
            // Check if post already exists
            $existing_post = get_page_by_title($foundation_name, OBJECT, 'christian_site');
            if ($existing_post) {
                echo "<p>Skipping existing site: " . esc_html($foundation_name) . "</p>\n";
                $skipped_count++;
                continue;
            }
            
            // Create post data
            $post_data = array(
                'post_title' => sanitize_text_field($foundation_name),
                'post_content' => wp_kses_post($site['communities_provenance'] ?? ''),
                'post_status' => 'publish',
                'post_type' => 'christian_site',
                'post_author' => 1
            );
            
            // Insert the post
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                echo "<p style='color: red;'>Failed to create post for: " . esc_html($foundation_name) . "</p>\n";
                continue;
            }
            
            // Determine site type from the foundation name and description
            $site_type = determine_site_type($foundation_name, $site['communities_provenance'] ?? '');
            wp_set_post_terms($post_id, array($site_type), 'site_type');
            
            // Add county
            if (!empty($site['county'])) {
                $county_term = get_term_by('name', $site['county'], 'county');
                if (!$county_term) {
                    $county_result = wp_insert_term($site['county'], 'county');
                    if (!is_wp_error($county_result)) {
                        wp_set_post_terms($post_id, array($site['county']), 'county');
                    }
                } else {
                    wp_set_post_terms($post_id, array($site['county']), 'county');
                }
            }
            
            // Add location data
            if (!empty($site['coordinates']['latitude']) && !empty($site['coordinates']['longitude'])) {
                update_post_meta($post_id, '_pilgrimirl_latitude', sanitize_text_field($site['coordinates']['latitude']));
                update_post_meta($post_id, '_pilgrimirl_longitude', sanitize_text_field($site['coordinates']['longitude']));
            }
            
            // Add alternative names
            if (!empty($site['alternative_names']) && is_array($site['alternative_names'])) {
                $alt_names = implode("\n", $site['alternative_names']);
                update_post_meta($post_id, '_pilgrimirl_alternative_names', sanitize_textarea_field($alt_names));
            }
            
            // Add communities provenance as description
            if (!empty($site['communities_provenance'])) {
                update_post_meta($post_id, '_pilgrimirl_communities_provenance', sanitize_textarea_field($site['communities_provenance']));
            }
            
            // Extract and add historical information from metadata tags
            if (!empty($site['metadata_tags']) && is_array($site['metadata_tags'])) {
                extract_historical_info($post_id, $site['metadata_tags']);
            }
            
            // Set default site status
            wp_set_post_terms($post_id, array('Ruined'), 'site_status');
            
            $imported_count++;
            echo "<p>✓ Imported Monastic Site: " . esc_html($foundation_name) . " (" . $site_type . ")</p>\n";
            
            // Flush output periodically
            if ($imported_count % 10 == 0) {
                flush();
            }
        }
    }
    
    echo "<p><strong>Monastic Sites Import Complete:</strong> $imported_count imported, $skipped_count skipped</p>\n";
}

/**
 * Determine site type from foundation name and description
 */
function determine_site_type($name, $description) {
    $name_lower = strtolower($name);
    $desc_lower = strtolower($description);
    
    if (strpos($name_lower, 'abbey') !== false || strpos($desc_lower, 'abbey') !== false) {
        return 'Abbey Ruin';
    }
    if (strpos($name_lower, 'priory') !== false || strpos($desc_lower, 'priory') !== false) {
        return 'Priory Ruin';
    }
    if (strpos($name_lower, 'cathedral') !== false || strpos($desc_lower, 'cathedral') !== false) {
        return 'Cathedral Ruin';
    }
    if (strpos($name_lower, 'friary') !== false || strpos($desc_lower, 'friary') !== false || 
        strpos($desc_lower, 'friars') !== false) {
        return 'Christian Ruin';
    }
    if (strpos($name_lower, 'monastery') !== false || strpos($desc_lower, 'monastery') !== false || 
        strpos($desc_lower, 'monastic') !== false) {
        return 'Monastery Ruin';
    }
    if (strpos($name_lower, 'chapel') !== false || strpos($desc_lower, 'chapel') !== false) {
        return 'Chapel Ruin';
    }
    if (strpos($name_lower, 'church') !== false || strpos($desc_lower, 'church') !== false) {
        return 'Church Ruin';
    }
    
    return 'Christian Ruin';
}

/**
 * Extract historical information from metadata tags
 */
function extract_historical_info($post_id, $metadata_tags) {
    foreach ($metadata_tags as $tag) {
        $tag_lower = strtolower($tag);
        
        // Extract foundation dates
        if (preg_match('/founded\s+(\d{3,4}|c\.\d{3,4}|before\s+\d{3,4}|after\s+\d{3,4})/i', $tag, $matches)) {
            update_post_meta($post_id, '_pilgrimirl_foundation_date', sanitize_text_field($matches[1]));
        }
        
        // Extract dissolution dates
        if (preg_match('/dissolved\s+(\d{3,4}|c\.\d{3,4}|before\s+\d{3,4}|after\s+\d{3,4})/i', $tag, $matches)) {
            update_post_meta($post_id, '_pilgrimirl_dissolution_date', sanitize_text_field($matches[1]));
        }
        
        // Extract religious orders
        if (strpos($tag_lower, 'augustinian') !== false) {
            wp_set_post_terms($post_id, array('Augustinian'), 'religious_order');
        } elseif (strpos($tag_lower, 'benedictine') !== false) {
            wp_set_post_terms($post_id, array('Benedictine'), 'religious_order');
        } elseif (strpos($tag_lower, 'cistercian') !== false) {
            wp_set_post_terms($post_id, array('Cistercian'), 'religious_order');
        } elseif (strpos($tag_lower, 'dominican') !== false) {
            wp_set_post_terms($post_id, array('Dominican'), 'religious_order');
        } elseif (strpos($tag_lower, 'franciscan') !== false) {
            wp_set_post_terms($post_id, array('Franciscan'), 'religious_order');
        } elseif (strpos($tag_lower, 'carmelite') !== false) {
            wp_set_post_terms($post_id, array('Carmelite'), 'religious_order');
        }
        
        // Extract centuries
        if (preg_match('/(\d{1,2})(st|nd|rd|th)\s+century/i', $tag, $matches)) {
            wp_set_post_terms($post_id, array($matches[1] . $matches[2] . ' Century'), 'century');
        }
        
        // Extract associated saints
        if (preg_match('/st\s+([a-z\s]+)/i', $tag, $matches)) {
            $saint_name = trim($matches[1]);
            if (strlen($saint_name) > 2 && strlen($saint_name) < 30) {
                wp_set_post_terms($post_id, array('St ' . $saint_name), 'associated_saints');
            }
        }
    }
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
    
    if (!$crosses_data || !isset($crosses_data['high_crosses'])) {
        echo "<p style='color: red;'>Failed to parse High Crosses JSON data or no high_crosses array found</p>\n";
        return;
    }
    
    $imported_count = 0;
    $skipped_count = 0;
    
    foreach ($crosses_data['high_crosses'] as $cross) {
        if (empty($cross['name'])) {
            continue;
        }
        
        $cross_title = $cross['name'] . ' High Cross';
        
        // Check if post already exists
        $existing_post = get_page_by_title($cross_title, OBJECT, 'christian_site');
        if ($existing_post) {
            echo "<p>Skipping existing cross: " . esc_html($cross['name']) . "</p>\n";
            $skipped_count++;
            continue;
        }
        
        // Create post data
        $post_data = array(
            'post_title' => sanitize_text_field($cross_title),
            'post_content' => 'High Cross located in ' . ($cross['county'] ?? 'Ireland') . '.',
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
        if (!empty($cross['coordinates']['lat']) && !empty($cross['coordinates']['lng'])) {
            update_post_meta($post_id, '_pilgrimirl_latitude', sanitize_text_field($cross['coordinates']['lat']));
            update_post_meta($post_id, '_pilgrimirl_longitude', sanitize_text_field($cross['coordinates']['lng']));
        }
        
        // Set default historical period for High Crosses
        wp_set_post_terms($post_id, array('Medieval'), 'historical_period');
        
        // Set default site status
        wp_set_post_terms($post_id, array('Standing'), 'site_status');
        
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
import_monastic_sites();
import_high_crosses();

echo "<h2 style='color: green;'>Import Process Complete!</h2>\n";
echo "<p>All Christian Sites data has been imported successfully.</p>\n";
echo "<p><a href='" . admin_url('edit.php?post_type=christian_site') . "'>View Christian Sites in Admin</a></p>\n";
echo "<p><a href='" . get_post_type_archive_link('christian_site') . "'>View Christian Sites Archive</a></p>\n";

echo "</div>\n";

// Flush rewrite rules to ensure new post type URLs work
flush_rewrite_rules();
?>
