<?php
/**
 * High Crosses Import Script
 * 
 * This script extracts High Cross data from the monastic sites JSON files
 * and creates Christian Site posts specifically for High Crosses
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Import High Crosses from JSON data
 */
function import_high_crosses_from_json() {
    $results = array(
        'imported' => 0,
        'skipped' => 0,
        'errors' => array(),
        'debug' => array()
    );
    
    // Path to the MonasticSites_JSON directory
    $json_dir = ABSPATH . 'MonasticSites_JSON/';
    
    if (!is_dir($json_dir)) {
        $results['errors'][] = "JSON directory not found: " . $json_dir;
        return $results;
    }
    
    // Get all JSON files
    $json_files = glob($json_dir . '*.json');
    $results['debug'][] = "Found " . count($json_files) . " JSON files to process";
    
    foreach ($json_files as $file) {
        $county_name = basename($file, '.json');
        $county_name = str_replace('-enriched', '', $county_name);
        $county_name = ucfirst($county_name);
        
        $results['debug'][] = "Processing county: " . $county_name;
        
        $json_content = file_get_contents($file);
        $data = json_decode($json_content, true);
        
        if (!$data || !isset($data['sites'])) {
            $results['errors'][] = "Invalid JSON structure in file: " . basename($file);
            continue;
        }
        
        foreach ($data['sites'] as $site) {
            $high_crosses = extract_high_crosses_from_site($site, $county_name);
            
            foreach ($high_crosses as $cross_data) {
                $existing_cross = get_posts(array(
                    'post_type' => 'christian_site',
                    'title' => $cross_data['name'],
                    'post_status' => 'publish',
                    'numberposts' => 1
                ));
                
                if (empty($existing_cross)) {
                    $cross_id = create_high_cross_post($cross_data, $county_name);
                    
                    if ($cross_id) {
                        $results['imported']++;
                        $results['debug'][] = "Imported High Cross: " . $cross_data['name'];
                    } else {
                        $results['errors'][] = "Failed to create High Cross: " . $cross_data['name'];
                    }
                } else {
                    $results['skipped']++;
                    $results['debug'][] = "Skipped existing High Cross: " . $cross_data['name'];
                }
            }
        }
    }
    
    return $results;
}

/**
 * Extract High Cross data from a monastic site
 */
function extract_high_crosses_from_site($site, $county_name) {
    $high_crosses = array();
    
    // Check metadata_tags for high cross references
    if (isset($site['metadata_tags']) && is_array($site['metadata_tags'])) {
        foreach ($site['metadata_tags'] as $tag) {
            if (stripos($tag, 'high cross') !== false || stripos($tag, 'cross') !== false) {
                // Extract cross name from tag
                if (preg_match('/([A-Za-z\s]+(?:high cross|cross))/i', $tag, $matches)) {
                    $cross_name = trim($matches[1]);
                    if (strlen($cross_name) > 5) { // Avoid very short matches
                        $high_crosses[] = array(
                            'name' => $cross_name,
                            'description' => "High Cross associated with " . $site['name'],
                            'source_site' => $site['name'],
                            'latitude' => $site['latitude'] ?? null,
                            'longitude' => $site['longitude'] ?? null,
                            'context' => 'Found in metadata tags: ' . $tag
                        );
                    }
                }
            }
        }
    }
    
    // Check communities_provenance for high cross mentions
    if (isset($site['communities_provenance'])) {
        $provenance = $site['communities_provenance'];
        
        // Look for high cross patterns in the text
        $cross_patterns = array(
            '/([A-Za-z\s]+high cross)/i',
            '/([A-Za-z\s]+cross)(?:\s+(?:stands|located|found|erected))/i',
            '/(?:cross of|cross at)\s+([A-Za-z\s]+)/i'
        );
        
        foreach ($cross_patterns as $pattern) {
            if (preg_match_all($pattern, $provenance, $matches)) {
                foreach ($matches[1] as $cross_name) {
                    $cross_name = trim($cross_name);
                    
                    // Validate cross name
                    if (strlen($cross_name) >= 5 && 
                        strlen($cross_name) <= 50 && 
                        !preg_match('/\d{4}/', $cross_name) && // Avoid years
                        stripos($cross_name, 'high cross') !== false || stripos($cross_name, 'cross') !== false) {
                        
                        $high_crosses[] = array(
                            'name' => $cross_name,
                            'description' => "High Cross mentioned in historical records of " . $site['name'] . ". " . substr($provenance, 0, 200) . "...",
                            'source_site' => $site['name'],
                            'latitude' => $site['latitude'] ?? null,
                            'longitude' => $site['longitude'] ?? null,
                            'context' => 'Found in communities_provenance text'
                        );
                    }
                }
            }
        }
    }
    
    // Check site name itself for high cross indicators
    if (stripos($site['name'], 'cross') !== false) {
        $high_crosses[] = array(
            'name' => $site['name'],
            'description' => "High Cross site in " . $county_name . ". " . ($site['communities_provenance'] ?? ''),
            'source_site' => $site['name'],
            'latitude' => $site['latitude'] ?? null,
            'longitude' => $site['longitude'] ?? null,
            'context' => 'Site name indicates High Cross'
        );
    }
    
    // Remove duplicates based on name
    $unique_crosses = array();
    foreach ($high_crosses as $cross) {
        $key = strtolower(trim($cross['name']));
        if (!isset($unique_crosses[$key])) {
            $unique_crosses[$key] = $cross;
        }
    }
    
    return array_values($unique_crosses);
}

/**
 * Create a WordPress post for a High Cross
 */
function create_high_cross_post($cross_data, $county_name) {
    $post_data = array(
        'post_type' => 'christian_site',
        'post_title' => $cross_data['name'],
        'post_content' => $cross_data['description'],
        'post_status' => 'publish',
        'post_author' => 1
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Set county taxonomy
        $county_term = get_term_by('name', $county_name, 'county');
        if ($county_term) {
            wp_set_post_terms($post_id, array($county_term->term_id), 'county');
        }
        
        // Set site type to "High Cross"
        $site_type_term = get_term_by('name', 'High Cross', 'site_type');
        if (!$site_type_term) {
            $site_type_term = wp_insert_term('High Cross', 'site_type', array(
                'description' => 'Celtic High Crosses and stone crosses'
            ));
            if (!is_wp_error($site_type_term)) {
                $site_type_term = get_term($site_type_term['term_id'], 'site_type');
            }
        }
        if ($site_type_term && !is_wp_error($site_type_term)) {
            wp_set_post_terms($post_id, array($site_type_term->term_id), 'site_type');
        }
        
        // Set historical period to "Early Christian"
        $period_term = get_term_by('name', 'Early Christian', 'historical_period');
        if (!$period_term) {
            $period_term = wp_insert_term('Early Christian', 'historical_period', array(
                'description' => 'Early Christian period in Ireland (5th-12th centuries)'
            ));
            if (!is_wp_error($period_term)) {
                $period_term = get_term($period_term['term_id'], 'historical_period');
            }
        }
        if ($period_term && !is_wp_error($period_term)) {
            wp_set_post_terms($post_id, array($period_term->term_id), 'historical_period');
        }
        
        // Add location metadata
        if ($cross_data['latitude']) {
            update_post_meta($post_id, '_pilgrimirl_latitude', $cross_data['latitude']);
        }
        if ($cross_data['longitude']) {
            update_post_meta($post_id, '_pilgrimirl_longitude', $cross_data['longitude']);
        }
        
        // Add source site reference
        update_post_meta($post_id, '_pilgrimirl_source_site', $cross_data['source_site']);
        update_post_meta($post_id, '_pilgrimirl_extraction_context', $cross_data['context']);
        
        return $post_id;
    }
    
    return false;
}

/**
 * Admin page for High Crosses import
 */
function add_high_crosses_import_admin_page() {
    add_management_page(
        'Import High Crosses',
        'Import High Crosses',
        'manage_options',
        'import-high-crosses',
        'high_crosses_import_admin_page'
    );
}
add_action('admin_menu', 'add_high_crosses_import_admin_page');

function high_crosses_import_admin_page() {
    ?>
    <div class="wrap">
        <h1>Import High Crosses</h1>
        <p>This tool extracts High Cross data from the monastic sites JSON files and creates Christian Site posts.</p>
        
        <?php if (isset($_POST['import_high_crosses'])): ?>
            <div class="notice notice-info">
                <p>Running High Crosses import...</p>
            </div>
            
            <?php
            $results = import_high_crosses_from_json();
            ?>
            
            <div class="notice notice-success">
                <p><strong>High Crosses import completed!</strong></p>
                <ul>
                    <li>Imported: <?php echo $results['imported']; ?> High Crosses</li>
                    <li>Skipped (already exist): <?php echo $results['skipped']; ?></li>
                    <?php if (!empty($results['errors'])): ?>
                        <li>Errors: <?php echo count($results['errors']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php if (!empty($results['errors'])): ?>
                <div class="notice notice-error">
                    <p><strong>Errors encountered:</strong></p>
                    <ul>
                        <?php foreach ($results['errors'] as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($results['debug'])): ?>
                <div class="notice notice-info">
                    <p><strong>Debug Information:</strong></p>
                    <ul>
                        <?php foreach ($results['debug'] as $debug): ?>
                            <li><?php echo esc_html($debug); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <form method="post" action="">
            <p>
                <input type="submit" name="import_high_crosses" class="button button-primary" 
                       value="Import High Crosses" 
                       onclick="return confirm('Are you sure you want to import High Crosses? This will create new Christian Site posts.');">
            </p>
        </form>
        
        <h2>Current High Crosses</h2>
        <?php
        $high_crosses = get_posts(array(
            'post_type' => 'christian_site',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'site_type',
                    'field' => 'name',
                    'terms' => 'High Cross'
                )
            )
        ));
        
        if ($high_crosses) {
            echo "<p>Currently " . count($high_crosses) . " High Crosses in database:</p>";
            echo "<ul>";
            foreach ($high_crosses as $cross) {
                $counties = wp_get_post_terms($cross->ID, 'county', array('fields' => 'names'));
                $county_str = !empty($counties) ? ' (' . implode(', ', $counties) . ')' : '';
                echo "<li>" . esc_html($cross->post_title) . $county_str . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No High Crosses found in database.</p>";
        }
        ?>
        
        <p><a href="<?php echo get_post_type_archive_link('christian_site'); ?>">View Christian Sites Archive</a></p>
    </div>
    <?php
}
?>
