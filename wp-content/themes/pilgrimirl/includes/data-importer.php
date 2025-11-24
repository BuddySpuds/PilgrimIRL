<?php
/**
 * PilgrimIRL Data Importer
 * 
 * Utility to import JSON data into WordPress custom post types
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Robust JSON file parser with error handling and memory management
 */
function pilgrimirl_parse_json_file($file_path) {
    if (!file_exists($file_path)) {
        error_log("JSON file not found: $file_path");
        return false;
    }

    // Increase memory limit for large files
    $original_memory_limit = ini_get('memory_limit');
    ini_set('memory_limit', '512M');
    
    // Increase execution time
    $original_time_limit = ini_get('max_execution_time');
    set_time_limit(300); // 5 minutes

    try {
        $content = file_get_contents($file_path);
        if ($content === false) {
            error_log("Could not read JSON file: $file_path");
            return false;
        }

        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        // Try to decode JSON
        $data = json_decode($content, true);
        $json_error = json_last_error();
        
        if ($json_error !== JSON_ERROR_NONE) {
            error_log("JSON decode error in $file_path: " . json_last_error_msg() . " (Code: $json_error)");
            
            // Try to fix common JSON issues
            $fixed_content = pilgrimirl_fix_json_content($content);
            if ($fixed_content !== $content) {
                $data = json_decode($fixed_content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    error_log("Successfully fixed JSON issues in $file_path");
                } else {
                    error_log("Could not fix JSON issues in $file_path");
                    return false;
                }
            } else {
                return false;
            }
        }

        return $data;
        
    } catch (Exception $e) {
        error_log("Exception parsing JSON file $file_path: " . $e->getMessage());
        return false;
    } finally {
        // Restore original limits
        ini_set('memory_limit', $original_memory_limit);
        set_time_limit($original_time_limit);
    }
}

/**
 * Attempt to fix common JSON formatting issues
 */
function pilgrimirl_fix_json_content($content) {
    // Remove trailing commas before closing brackets/braces
    $content = preg_replace('/,(\s*[}\]])/', '$1', $content);
    
    // Fix unescaped quotes in strings (basic attempt)
    $content = preg_replace('/(?<!\\\\)"(?=.*":)/', '\\"', $content);
    
    // Remove any non-printable characters except newlines and tabs
    $content = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', $content);
    
    return $content;
}

/**
 * Import JSON data for monastic sites and pilgrimage routes
 */
function pilgrimirl_import_json_data() {
    $json_dir = ABSPATH . 'MonasticSites_JSON/';
    $routes_dir = ABSPATH . 'PilgrimageRoutes_JSON/';
    
    // Check if directories exist in the expected location, if not try alternative paths
    if (!is_dir($json_dir)) {
        // Try the public directory path
        $json_dir = get_home_path() . 'MonasticSites_JSON/';
    }
    
    if (!is_dir($routes_dir)) {
        // Try the public directory path
        $routes_dir = get_home_path() . 'PilgrimageRoutes_JSON/';
    }
    
    $import_log = array();
    $total_imported = 0;
    $failed_files = array();
    
    // Import Monastic Sites
    if (is_dir($json_dir)) {
        $files = glob($json_dir . '*.json');
        
        foreach ($files as $file) {
            $county_name = basename($file, '-enriched.json');
            $county_name = ucfirst(strtolower($county_name));
            
            $data = pilgrimirl_parse_json_file($file);
            
            if ($data && is_array($data)) {
                $imported_count = 0;
                $sites_data = array();
                
                // Handle different JSON structures
                if (isset($data['monasticHouses'])) {
                    // New format (like Limerick)
                    $sites_data = $data['monasticHouses'];
                } elseif (isset($data[0]) && is_array($data[0])) {
                    // Old format (like Dublin) - array of sites
                    $sites_data = $data;
                }
                
                foreach ($sites_data as $site) {
                    $post_id = pilgrimirl_create_monastic_site($site, $county_name);
                    if ($post_id) {
                        $imported_count++;
                        $total_imported++;
                    }
                }
                
                $import_log[] = "✅ Imported {$imported_count} sites from {$county_name}";
            } else {
                $failed_files[] = $county_name;
                $import_log[] = "❌ Failed to parse JSON for {$county_name}";
            }
        }
    }
    
    // Import Pilgrimage Routes
    if (is_dir($routes_dir)) {
        $route_file = $routes_dir . 'pilgrim_data_new_sites.json';
        
        if (file_exists($route_file)) {
            $data = pilgrimirl_parse_json_file($route_file);
            
            if ($data && isset($data['pilgrimages'])) {
                $imported_count = 0;
                
                foreach ($data['pilgrimages'] as $route) {
                    $post_id = pilgrimirl_create_pilgrimage_route($route);
                    if ($post_id) {
                        $imported_count++;
                        $total_imported++;
                    }
                }
                
                $import_log[] = "✅ Imported {$imported_count} pilgrimage routes";
            } else {
                $import_log[] = "❌ Failed to parse pilgrimage routes JSON";
            }
        }
    }
    
    $import_log[] = "📊 Total imported: {$total_imported} items";
    
    if (!empty($failed_files)) {
        $import_log[] = "⚠️  Failed files: " . implode(', ', $failed_files);
    }
    
    return $import_log;
}

/**
 * Create a monastic site post from JSON data
 */
function pilgrimirl_create_monastic_site($site_data, $county_name) {
    // Determine site name based on data format
    $site_name = '';
    if (isset($site_data['foundation_name'])) {
        // Old format (Dublin)
        $site_name = $site_data['foundation_name'];
    } elseif (isset($site_data['name'])) {
        // New format (Limerick)
        $site_name = $site_data['name'];
    } else {
        return false; // No valid name found
    }
    
    // Check if post already exists
    $existing_post = get_page_by_title($site_name, OBJECT, 'monastic_site');
    if ($existing_post) {
        return false; // Skip if already exists
    }
    
    // Determine content based on data format
    $content = '';
    if (isset($site_data['communities_provenance'])) {
        // Old format
        $content = $site_data['communities_provenance'];
    } elseif (isset($site_data['history']['notes'])) {
        // New format
        $content = $site_data['history']['notes'];
    }
    
    // Create the post
    $post_data = array(
        'post_title' => sanitize_text_field($site_name),
        'post_content' => wp_kses_post($content),
        'post_status' => 'publish',
        'post_type' => 'monastic_site',
        'post_excerpt' => wp_trim_words($content, 30),
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Add county taxonomy
        $county_term = get_term_by('name', $county_name, 'county');
        if ($county_term) {
            wp_set_post_terms($post_id, array($county_term->term_id), 'county');
        }
        
        // Add coordinates - handle both formats
        $coordinates = null;
        if (isset($site_data['coordinates'])) {
            // Old format
            $coordinates = $site_data['coordinates'];
        } elseif (isset($site_data['location']['coordinates'])) {
            // New format
            $coordinates = $site_data['location']['coordinates'];
        }
        
        if ($coordinates) {
            $lat = isset($coordinates['latitude']) ? $coordinates['latitude'] : $coordinates['lat'];
            $lng = isset($coordinates['longitude']) ? $coordinates['longitude'] : $coordinates['lng'];
            
            if ($lat && $lng) {
                update_post_meta($post_id, '_pilgrimirl_latitude', sanitize_text_field($lat));
                update_post_meta($post_id, '_pilgrimirl_longitude', sanitize_text_field($lng));
            }
        }
        
        // Add alternative names - handle both formats
        $alt_names = array();
        if (isset($site_data['alternative_names']) && is_array($site_data['alternative_names'])) {
            $alt_names = $site_data['alternative_names'];
        } elseif (isset($site_data['alternativeNames']) && is_array($site_data['alternativeNames'])) {
            $alt_names = $site_data['alternativeNames'];
        }
        
        if (!empty($alt_names)) {
            $alt_names_str = implode("\n", array_map('sanitize_text_field', $alt_names));
            update_post_meta($post_id, '_pilgrimirl_alternative_names', $alt_names_str);
        }
        
        // Add communities and provenance
        if (isset($site_data['communities_provenance'])) {
            update_post_meta($post_id, '_pilgrimirl_communities_provenance', sanitize_textarea_field($site_data['communities_provenance']));
        }
        
        // Add historical information from new format
        if (isset($site_data['history'])) {
            $history = $site_data['history'];
            
            if (isset($history['foundingDate'])) {
                update_post_meta($post_id, '_pilgrimirl_foundation_date', sanitize_text_field($history['foundingDate']));
            }
            
            if (isset($history['founder'])) {
                update_post_meta($post_id, '_pilgrimirl_associated_saints', sanitize_text_field($history['founder']));
            }
            
            if (isset($history['dissolutionDate'])) {
                update_post_meta($post_id, '_pilgrimirl_dissolution_date', sanitize_text_field($history['dissolutionDate']));
            }
        }
        
        // Process metadata tags for taxonomies - handle both formats
        $metadata_tags = array();
        if (isset($site_data['metadata_tags']) && is_array($site_data['metadata_tags'])) {
            // Old format
            $metadata_tags = $site_data['metadata_tags'];
        } elseif (isset($site_data['metadata']['tags']) && is_array($site_data['metadata']['tags'])) {
            // New format
            $metadata_tags = $site_data['metadata']['tags'];
        }
        
        if (!empty($metadata_tags)) {
            pilgrimirl_process_metadata_tags($post_id, $metadata_tags);
        }
        
        // Process new format metadata
        if (isset($site_data['metadata'])) {
            $metadata = $site_data['metadata'];
            
            // Religious orders
            if (isset($metadata['religiousOrder']) && is_array($metadata['religiousOrder'])) {
                foreach ($metadata['religiousOrder'] as $order) {
                    $term = pilgrimirl_get_or_create_term($order, 'religious_order');
                    if ($term) {
                        wp_set_post_terms($post_id, array($term->term_id), 'religious_order', true);
                    }
                }
            }
            
            // Time periods
            if (isset($metadata['timePeriod']) && is_array($metadata['timePeriod'])) {
                foreach ($metadata['timePeriod'] as $period) {
                    $term = pilgrimirl_get_or_create_term($period, 'historical_period');
                    if ($term) {
                        wp_set_post_terms($post_id, array($term->term_id), 'historical_period', true);
                    }
                }
            }
            
            // Site status
            if (isset($metadata['status']) && is_array($metadata['status'])) {
                foreach ($metadata['status'] as $status) {
                    $term = pilgrimirl_get_or_create_term($status, 'site_status');
                    if ($term) {
                        wp_set_post_terms($post_id, array($term->term_id), 'site_status', true);
                    }
                }
            }
        }
        
        return $post_id;
    }
    
    return false;
}

/**
 * Create a pilgrimage route post from JSON data
 */
function pilgrimirl_create_pilgrimage_route($route_data) {
    // Check if post already exists
    $existing_post = get_page_by_title($route_data['name'], OBJECT, 'pilgrimage_route');
    if ($existing_post) {
        return false; // Skip if already exists
    }
    
    // Create the post
    $post_data = array(
        'post_title' => sanitize_text_field($route_data['name']),
        'post_content' => isset($route_data['description']) ? wp_kses_post($route_data['description']) : '',
        'post_status' => 'publish',
        'post_type' => 'pilgrimage_route',
        'post_excerpt' => isset($route_data['excerpt']) ? wp_kses_post($route_data['excerpt']) : '',
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Add county taxonomy (handle multiple counties)
        if (isset($route_data['county'])) {
            $counties = explode(',', $route_data['county']);
            $county_ids = array();
            
            foreach ($counties as $county_name) {
                $county_name = trim($county_name);
                $county_term = get_term_by('name', $county_name, 'county');
                if ($county_term) {
                    $county_ids[] = $county_term->term_id;
                }
            }
            
            if (!empty($county_ids)) {
                wp_set_post_terms($post_id, $county_ids, 'county');
            }
        }
        
        // Add coordinates
        if (isset($route_data['location_lat']) && isset($route_data['location_lng'])) {
            update_post_meta($post_id, '_pilgrimirl_latitude', sanitize_text_field($route_data['location_lat']));
            update_post_meta($post_id, '_pilgrimirl_longitude', sanitize_text_field($route_data['location_lng']));
        }
        
        // Add address
        if (isset($route_data['location_address'])) {
            update_post_meta($post_id, '_pilgrimirl_address', sanitize_text_field($route_data['location_address']));
        }
        
        // Add distance
        if (isset($route_data['distance']) && $route_data['distance'] !== 'unknown') {
            update_post_meta($post_id, '_pilgrimirl_distance', sanitize_text_field($route_data['distance']));
        }
        
        // Add pets allowed
        if (isset($route_data['pets_allowed'])) {
            update_post_meta($post_id, '_pilgrimirl_pets_allowed', sanitize_text_field($route_data['pets_allowed']));
        }
        
        // Add associated saints
        if (isset($route_data['associated_saints'])) {
            update_post_meta($post_id, '_pilgrimirl_associated_saints', sanitize_text_field($route_data['associated_saints']));
        }
        
        // Add route excerpt
        if (isset($route_data['excerpt'])) {
            update_post_meta($post_id, '_pilgrimirl_route_excerpt', sanitize_textarea_field($route_data['excerpt']));
        }
        
        // Add difficulty level taxonomy
        if (isset($route_data['difficulty'])) {
            $difficulty_term = get_term_by('name', $route_data['difficulty'], 'difficulty_level');
            if (!$difficulty_term) {
                $difficulty_term = wp_insert_term($route_data['difficulty'], 'difficulty_level');
                if (!is_wp_error($difficulty_term)) {
                    $difficulty_term = get_term($difficulty_term['term_id'], 'difficulty_level');
                }
            }
            if ($difficulty_term && !is_wp_error($difficulty_term)) {
                wp_set_post_terms($post_id, array($difficulty_term->term_id), 'difficulty_level');
            }
        }
        
        // Add features taxonomy
        if (isset($route_data['features']) && is_array($route_data['features'])) {
            $feature_ids = array();
            
            foreach ($route_data['features'] as $feature) {
                $feature_term = get_term_by('name', $feature, 'pilgrimage_features');
                if (!$feature_term) {
                    $feature_term = wp_insert_term($feature, 'pilgrimage_features');
                    if (!is_wp_error($feature_term)) {
                        $feature_term = get_term($feature_term['term_id'], 'pilgrimage_features');
                    }
                }
                if ($feature_term && !is_wp_error($feature_term)) {
                    $feature_ids[] = $feature_term->term_id;
                }
            }
            
            if (!empty($feature_ids)) {
                wp_set_post_terms($post_id, $feature_ids, 'pilgrimage_features');
            }
        }
        
        return $post_id;
    }
    
    return false;
}

/**
 * Process metadata tags and assign to appropriate taxonomies
 */
function pilgrimirl_process_metadata_tags($post_id, $metadata_tags) {
    foreach ($metadata_tags as $tag) {
        $tag = trim($tag);
        
        // Determine taxonomy based on tag content
        if (pilgrimirl_is_religious_order($tag)) {
            $term = pilgrimirl_get_or_create_term($tag, 'religious_order');
            if ($term) {
                wp_set_post_terms($post_id, array($term->term_id), 'religious_order', true);
            }
        } elseif (pilgrimirl_is_historical_period($tag)) {
            $term = pilgrimirl_get_or_create_term($tag, 'historical_period');
            if ($term) {
                wp_set_post_terms($post_id, array($term->term_id), 'historical_period', true);
            }
        } elseif (pilgrimirl_is_site_status($tag)) {
            $term = pilgrimirl_get_or_create_term($tag, 'site_status');
            if ($term) {
                wp_set_post_terms($post_id, array($term->term_id), 'site_status', true);
            }
        }
    }
}

/**
 * Check if tag represents a religious order
 */
function pilgrimirl_is_religious_order($tag) {
    $religious_orders = array(
        'franciscan', 'cistercian', 'augustinian', 'dominican', 'benedictine',
        'carmelite', 'jesuit', 'premonstratensian', 'canons regular', 'knights templar',
        'knights hospitaller', 'culdees', 'savignac', 'victorine', 'arroasian'
    );
    
    foreach ($religious_orders as $order) {
        if (stripos($tag, $order) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if tag represents a historical period
 */
function pilgrimirl_is_historical_period($tag) {
    $periods = array(
        'early christian', 'medieval', 'norman', '12th century', '13th century',
        '14th century', '15th century', '16th century', '5th century', '6th century',
        '7th century', '8th century', '9th century', '10th century', '11th century'
    );
    
    foreach ($periods as $period) {
        if (stripos($tag, $period) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if tag represents site status
 */
function pilgrimirl_is_site_status($tag) {
    $statuses = array(
        'extant', 'ruined', 'dissolved', 'suppressed', 'demolished', 'burned',
        'plundered', 'destroyed', 'rebuilt', 'restored', 'continuing'
    );
    
    foreach ($statuses as $status) {
        if (stripos($tag, $status) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get or create a taxonomy term
 */
function pilgrimirl_get_or_create_term($term_name, $taxonomy) {
    $term = get_term_by('name', $term_name, $taxonomy);
    
    if (!$term) {
        $result = wp_insert_term($term_name, $taxonomy);
        if (!is_wp_error($result)) {
            $term = get_term($result['term_id'], $taxonomy);
        }
    }
    
    return $term;
}

/**
 * Admin page for data import
 */
function pilgrimirl_add_import_admin_page() {
    add_management_page(
        'PilgrimIRL Data Import',
        'PilgrimIRL Import',
        'manage_options',
        'pilgrimirl-import',
        'pilgrimirl_import_admin_page'
    );
}
add_action('admin_menu', 'pilgrimirl_add_import_admin_page');

/**
 * Admin page callback
 */
function pilgrimirl_import_admin_page() {
    if (isset($_POST['import_data']) && wp_verify_nonce($_POST['pilgrimirl_import_nonce'], 'pilgrimirl_import')) {
        $log = pilgrimirl_import_json_data();
        
        echo '<div class="notice notice-success"><p>Import completed! Check the log below for details.</p></div>';
        echo '<div class="import-log"><h3>Import Log:</h3>';
        echo '<pre>' . implode("\n", $log) . '</pre></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>PilgrimIRL Data Import</h1>
        <p>Import monastic sites and pilgrimage routes from JSON files.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('pilgrimirl_import', 'pilgrimirl_import_nonce'); ?>
            <p>
                <input type="submit" name="import_data" class="button button-primary" value="Import Data" />
            </p>
        </form>
        
        <div class="import-info">
            <h3>Import Information:</h3>
            <ul>
                <li>Monastic Sites: <?php echo count(glob(ABSPATH . 'MonasticSites_JSON/*.json')); ?> JSON files found</li>
                <li>Pilgrimage Routes: <?php echo file_exists(ABSPATH . 'PilgrimageRoutes_JSON/pilgrim_data_new_sites.json') ? '1 file found' : 'File not found'; ?></li>
            </ul>
        </div>
    </div>
    
    <style>
    .import-log {
        background: #f1f1f1;
        padding: 15px;
        margin-top: 20px;
        border-radius: 5px;
    }
    .import-log pre {
        white-space: pre-wrap;
        font-size: 12px;
        max-height: 400px;
        overflow-y: auto;
    }
    .import-info {
        background: #fff;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 20px;
    }
    </style>
    <?php
}
