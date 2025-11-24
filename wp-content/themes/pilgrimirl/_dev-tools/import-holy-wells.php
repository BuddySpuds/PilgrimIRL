<?php
/**
 * Import Holy Wells from JSON data
 * 
 * This script extracts Holy Wells mentioned in the monastic sites data
 * and creates them as separate Christian Site posts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function import_holy_wells_from_json() {
    $json_directory = ABSPATH . 'MonasticSites_JSON/';
    $imported_count = 0;
    $errors = array();
    $debug = array();
    
    if (!is_dir($json_directory)) {
        return array(
            'imported' => 0,
            'errors' => array('JSON directory not found: ' . $json_directory),
            'debug' => array()
        );
    }
    
    $json_files = glob($json_directory . '*.json');
    $debug[] = 'Found ' . count($json_files) . ' JSON files to process';
    
    foreach ($json_files as $file) {
        $county_name = basename($file, '.json');
        $county_name = str_replace('-enriched', '', $county_name);
        $county_name = ucfirst($county_name);
        
        $debug[] = 'Processing county: ' . $county_name;
        
        $json_content = file_get_contents($file);
        $data = json_decode($json_content, true);
        
        if (!$data || !is_array($data)) {
            $errors[] = 'Failed to parse JSON file or invalid format: ' . basename($file);
            continue;
        }
        
        $debug[] = 'Found ' . count($data) . ' sites in ' . $county_name;
        
        foreach ($data as $site) {
            // Look for Holy Wells in various fields
            $holy_wells = extract_holy_wells_from_site($site, $county_name);
            
            foreach ($holy_wells as $well) {
                $result = create_holy_well_post($well, $site);
                if ($result['success']) {
                    $imported_count++;
                    $debug[] = 'Imported Holy Well: ' . $well['name'] . ' (' . $county_name . ')';
                } else {
                    $errors[] = $result['error'];
                }
            }
        }
    }
    
    return array(
        'imported' => $imported_count,
        'errors' => $errors,
        'debug' => $debug
    );
}

function extract_holy_wells_from_site($site, $county_name) {
    $holy_wells = array();
    
    // Check metadata_tags for holy wells
    if (isset($site['metadata_tags']) && is_array($site['metadata_tags'])) {
        foreach ($site['metadata_tags'] as $tag) {
            if (stripos($tag, 'holy well') !== false || 
                stripos($tag, 'sacred well') !== false ||
                stripos($tag, 'blessed well') !== false ||
                (stripos($tag, 'well') !== false && stripos($tag, 'holy') !== false)) {
                
                // Extract well name from tag
                $well_name = trim($tag);
                if (strlen($well_name) > 4) {
                    $holy_wells[] = array(
                        'name' => $well_name,
                        'source_site' => $site['foundation_name'] ?? 'Unknown',
                        'county' => $county_name,
                        'latitude' => isset($site['coordinates']['latitude']) ? $site['coordinates']['latitude'] : null,
                        'longitude' => isset($site['coordinates']['longitude']) ? $site['coordinates']['longitude'] : null,
                        'description' => 'Holy well associated with ' . ($site['foundation_name'] ?? 'monastic site'),
                        'context' => 'Found in metadata tags: ' . $tag
                    );
                }
            }
        }
    }
    
    // Check communities_provenance for holy wells
    if (isset($site['communities_provenance'])) {
        $text = $site['communities_provenance'];
        
        // Look for patterns like "holy well", "Tubernacool holy well", etc.
        $well_patterns = array(
            '/([A-Za-z\s\']+holy well)/i',
            '/([A-Za-z\s\']+sacred well)/i',
            '/([A-Za-z\s\']+blessed well)/i',
            '/([A-Za-z\s\']+well)(?:\s+(?:of|at|near))/i',
            '/(?:well of|well at|well near)\s+([A-Za-z\s\']+)/i',
            '/St\s+([A-Za-z\s\']*well)/i'
        );
        
        foreach ($well_patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $match) {
                    $well_name = trim($match);
                    
                    // Validate well name
                    if (strlen($well_name) >= 5 && 
                        strlen($well_name) <= 60 && 
                        !preg_match('/\d{4}/', $well_name) && // Avoid years
                        !preg_match('/century|founded|built|burned|plundered/i', $well_name) && // Avoid historical terms
                        (stripos($well_name, 'holy well') !== false || 
                         stripos($well_name, 'sacred well') !== false ||
                         stripos($well_name, 'blessed well') !== false ||
                         stripos($well_name, 'well') !== false)) {
                        
                        $holy_wells[] = array(
                            'name' => $well_name,
                            'source_site' => $site['foundation_name'] ?? 'Unknown',
                            'county' => $county_name,
                            'latitude' => isset($site['coordinates']['latitude']) ? $site['coordinates']['latitude'] : null,
                            'longitude' => isset($site['coordinates']['longitude']) ? $site['coordinates']['longitude'] : null,
                            'description' => 'Holy well mentioned in historical records of ' . ($site['foundation_name'] ?? 'monastic site') . '. ' . substr($text, 0, 200) . '...',
                            'context' => 'Found in communities_provenance text'
                        );
                    }
                }
            }
        }
    }
    
    // Check foundation_name itself for holy well indicators
    if (isset($site['foundation_name']) && 
        (stripos($site['foundation_name'], 'well') !== false ||
         stripos($site['foundation_name'], 'holy') !== false)) {
        
        $name = $site['foundation_name'];
        if (stripos($name, 'well') !== false) {
            $holy_wells[] = array(
                'name' => $name,
                'source_site' => $name,
                'county' => $county_name,
                'latitude' => isset($site['coordinates']['latitude']) ? $site['coordinates']['latitude'] : null,
                'longitude' => isset($site['coordinates']['longitude']) ? $site['coordinates']['longitude'] : null,
                'description' => 'Holy well site in ' . $county_name . '. ' . ($site['communities_provenance'] ?? ''),
                'context' => 'Site name indicates Holy Well'
            );
        }
    }
    
    // Remove duplicates based on name
    $unique_wells = array();
    foreach ($holy_wells as $well) {
        $key = strtolower(trim($well['name']));
        if (!isset($unique_wells[$key])) {
            $unique_wells[$key] = $well;
        }
    }
    
    return array_values($unique_wells);
}

function create_holy_well_post($well_data, $source_site) {
    // Check if this holy well already exists
    $existing_posts = get_posts(array(
        'post_type' => 'christian_site',
        'title' => $well_data['name'],
        'post_status' => 'any',
        'numberposts' => 1
    ));
    
    if (!empty($existing_posts)) {
        return array('success' => false, 'error' => 'Holy well already exists: ' . $well_data['name']);
    }
    
    // Create the post
    $post_data = array(
        'post_title' => $well_data['name'],
        'post_content' => $well_data['description'],
        'post_status' => 'publish',
        'post_type' => 'christian_site',
        'post_author' => 1
    );
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return array('success' => false, 'error' => 'Failed to create post: ' . $post_id->get_error_message());
    }
    
    // Set county taxonomy
    if (!empty($well_data['county'])) {
        $county_term = get_term_by('name', $well_data['county'], 'county');
        if ($county_term) {
            wp_set_post_terms($post_id, array($county_term->term_id), 'county');
        }
    }
    
    // Set site type as Holy Well
    $site_type_term = get_term_by('name', 'Holy Well', 'site_type');
    if (!$site_type_term) {
        $site_type_term = wp_insert_term('Holy Well', 'site_type', array(
            'slug' => 'holy-well',
            'description' => 'Sacred wells and springs with religious significance'
        ));
        if (!is_wp_error($site_type_term)) {
            $site_type_term = get_term($site_type_term['term_id'], 'site_type');
        }
    }
    if (!is_wp_error($site_type_term) && $site_type_term) {
        wp_set_post_terms($post_id, array($site_type_term->term_id), 'site_type');
    }
    
    // Set historical period
    $period_term = get_term_by('name', 'Early Christian', 'historical_period');
    if (!$period_term) {
        $period_term = wp_insert_term('Early Christian', 'historical_period', array(
            'slug' => 'early-christian',
            'description' => 'Early Christian period in Ireland (5th-12th centuries)'
        ));
        if (!is_wp_error($period_term)) {
            $period_term = get_term($period_term['term_id'], 'historical_period');
        }
    }
    if (!is_wp_error($period_term) && $period_term) {
        wp_set_post_terms($post_id, array($period_term->term_id), 'historical_period');
    }
    
    // Add coordinates if available
    if (!empty($well_data['latitude'])) {
        update_post_meta($post_id, '_pilgrimirl_latitude', $well_data['latitude']);
    }
    if (!empty($well_data['longitude'])) {
        update_post_meta($post_id, '_pilgrimirl_longitude', $well_data['longitude']);
    }
    
    // Add source site reference
    update_post_meta($post_id, '_pilgrimirl_source_site', $well_data['source_site']);
    update_post_meta($post_id, '_pilgrimirl_extraction_context', $well_data['context']);
    
    return array('success' => true, 'post_id' => $post_id);
}

// Add admin page for importing holy wells
function add_holy_wells_import_page() {
    add_management_page(
        'Import Holy Wells',
        'Import Holy Wells',
        'manage_options',
        'import-holy-wells',
        'holy_wells_import_page'
    );
}
add_action('admin_menu', 'add_holy_wells_import_page');

function holy_wells_import_page() {
    ?>
    <div class="wrap">
        <h1>Import Holy Wells</h1>
        <p>This tool will extract Holy Wells from the monastic sites JSON data and create them as separate Christian Site posts.</p>
        
        <?php if (isset($_POST['import_holy_wells'])): ?>
            <div class="notice notice-info">
                <p>Running Holy Wells import...</p>
            </div>
            
            <?php
            $result = import_holy_wells_from_json();
            ?>
            
            <div class="notice notice-success">
                <p><strong>Holy Wells import completed!</strong></p>
                <ul>
                    <li>Imported: <?php echo $result['imported']; ?> Holy Wells</li>
                    <?php if (!empty($result['errors'])): ?>
                        <li>Errors: <?php echo count($result['errors']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php if (!empty($result['errors'])): ?>
                <div class="notice notice-error">
                    <p><strong>Errors encountered:</strong></p>
                    <ul>
                        <?php foreach ($result['errors'] as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($result['debug'])): ?>
                <div class="notice notice-info">
                    <p><strong>Debug Information:</strong></p>
                    <ul>
                        <?php foreach ($result['debug'] as $debug): ?>
                            <li><?php echo esc_html($debug); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <form method="post" action="">
            <p>
                <input type="submit" name="import_holy_wells" class="button button-primary" 
                       value="Import Holy Wells" 
                       onclick="return confirm('Are you sure you want to import Holy Wells? This will create new Christian Site posts.');">
            </p>
        </form>
        
        <h2>Current Holy Wells</h2>
        <?php
        $holy_wells = get_posts(array(
            'post_type' => 'christian_site',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'site_type',
                    'field' => 'name',
                    'terms' => 'Holy Well'
                )
            )
        ));
        
        if ($holy_wells) {
            echo '<p>Currently ' . count($holy_wells) . ' Holy Wells in database:</p>';
            echo '<ul>';
            foreach ($holy_wells as $well) {
                $counties = wp_get_post_terms($well->ID, 'county', array('fields' => 'names'));
                $county_str = !empty($counties) ? ' (' . implode(', ', $counties) . ')' : '';
                echo '<li>' . esc_html($well->post_title) . $county_str . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No Holy Wells found in database.</p>';
        }
        ?>
        
        <p><a href="<?php echo get_post_type_archive_link('christian_site'); ?>">View Christian Sites Archive</a></p>
    </div>
    <?php
}
?>
