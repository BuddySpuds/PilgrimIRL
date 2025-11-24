<?php
/**
 * Enhanced Holy Wells Import Script
 * 
 * This script imports Holy Wells from two sources:
 * 1. Extracts Holy Wells mentioned in the monastic sites JSON data
 * 2. Imports Holy Wells from the government CSV data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function import_holy_wells_enhanced() {
    $results = array(
        'json_imported' => 0,
        'csv_imported' => 0,
        'total_imported' => 0,
        'errors' => array(),
        'debug' => array()
    );
    
    // Import from JSON data (existing functionality)
    $json_results = import_holy_wells_from_json();
    $results['json_imported'] = $json_results['imported'];
    $results['errors'] = array_merge($results['errors'], $json_results['errors']);
    $results['debug'] = array_merge($results['debug'], $json_results['debug']);
    
    // Import from CSV data (new functionality)
    $csv_results = import_holy_wells_from_csv();
    $results['csv_imported'] = $csv_results['imported'];
    $results['errors'] = array_merge($results['errors'], $csv_results['errors']);
    $results['debug'] = array_merge($results['debug'], $csv_results['debug']);
    
    $results['total_imported'] = $results['json_imported'] + $results['csv_imported'];
    
    return $results;
}

function import_holy_wells_from_csv() {
    $csv_file = ABSPATH . 'holy_wells_gov_data.csv';
    $imported_count = 0;
    $errors = array();
    $debug = array();
    
    if (!file_exists($csv_file)) {
        return array(
            'imported' => 0,
            'errors' => array('CSV file not found: ' . $csv_file),
            'debug' => array()
        );
    }
    
    $debug[] = 'Processing government CSV data: ' . $csv_file;
    
    // Read CSV file
    $handle = fopen($csv_file, 'r');
    if (!$handle) {
        return array(
            'imported' => 0,
            'errors' => array('Could not open CSV file'),
            'debug' => array()
        );
    }
    
    // Read header row
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return array(
            'imported' => 0,
            'errors' => array('Could not read CSV headers'),
            'debug' => array()
        );
    }
    
    $debug[] = 'CSV headers: ' . implode(', ', $headers);
    
    // Create header mapping
    $header_map = array_flip($headers);
    
    $row_count = 0;
    while (($row = fgetcsv($handle)) !== FALSE) {
        $row_count++;
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Extract data from row
        $well_data = array(
            'county' => isset($header_map['COUNTY']) ? trim($row[$header_map['COUNTY']]) : '',
            'townland' => isset($header_map['TOWNLAND']) ? trim($row[$header_map['TOWNLAND']]) : '',
            'latitude' => isset($header_map['LATITUDE']) ? trim($row[$header_map['LATITUDE']]) : '',
            'longitude' => isset($header_map['LONGITUDE']) ? trim($row[$header_map['LONGITUDE']]) : '',
            'monument_type' => isset($header_map['MONUMENT_T']) ? trim($row[$header_map['MONUMENT_T']]) : '',
            'first_edit' => isset($header_map['FIRST_EDIT']) ? trim($row[$header_map['FIRST_EDIT']]) : '',
            'web_notes' => isset($header_map['WEB_NOTES']) ? trim($row[$header_map['WEB_NOTES']]) : '',
            'smrs' => isset($header_map['SMRS']) ? trim($row[$header_map['SMRS']]) : '',
            'website_link' => isset($header_map['WEBSITE_LI']) ? trim($row[$header_map['WEBSITE_LI']]) : ''
        );
        
        // Skip if not a holy well
        if (stripos($well_data['monument_type'], 'holy well') === false) {
            continue;
        }
        
        // Determine well name
        $well_name = determine_well_name($well_data);
        if (empty($well_name)) {
            $errors[] = "Could not determine name for well in row $row_count";
            continue;
        }
        
        // Create the well post
        $result = create_csv_holy_well_post($well_data, $well_name);
        if ($result['success']) {
            $imported_count++;
            $debug[] = "Imported CSV Holy Well: $well_name ({$well_data['county']})";
        } else {
            $errors[] = $result['error'];
        }
    }
    
    fclose($handle);
    $debug[] = "Processed $row_count rows from CSV";
    
    return array(
        'imported' => $imported_count,
        'errors' => $errors,
        'debug' => $debug
    );
}

function determine_well_name($well_data) {
    // Priority order for determining name:
    // 1. FIRST_EDIT if it looks like a proper name
    // 2. Extract from WEB_NOTES
    // 3. Use townland + "Holy Well"
    
    $name = '';
    
    // Try FIRST_EDIT first
    if (!empty($well_data['first_edit']) && $well_data['first_edit'] !== 'Not indicated') {
        $name = $well_data['first_edit'];
        // Clean up the name
        $name = str_replace(array("'", '"'), '', $name);
        $name = trim($name);
        
        // If it's a reasonable length and contains well-related terms
        if (strlen($name) >= 3 && strlen($name) <= 60) {
            return $name;
        }
    }
    
    // Try to extract from web notes
    if (!empty($well_data['web_notes'])) {
        $notes = $well_data['web_notes'];
        
        // Look for patterns like "named 'X'" or "called 'X'"
        $patterns = array(
            "/named\s+['\"]([^'\"]+)['\"](?:\s+on|\s+in|\s+as)/i",
            "/called\s+['\"]([^'\"]+)['\"]/i",
            "/labelled\s+['\"]([^'\"]+)['\"]/i",
            "/indicated\s+and\s+named\s+['\"]([^'\"]+)['\"]/i",
            "/recorded\s+as\s+['\"]([^'\"]+)['\"]/i"
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $notes, $matches)) {
                $extracted_name = trim($matches[1]);
                if (strlen($extracted_name) >= 3 && strlen($extracted_name) <= 60) {
                    return $extracted_name;
                }
            }
        }
        
        // Look for standalone quoted names at the beginning
        if (preg_match("/^['\"]([^'\"]+)['\"]/", $notes, $matches)) {
            $extracted_name = trim($matches[1]);
            if (strlen($extracted_name) >= 3 && strlen($extracted_name) <= 60) {
                return $extracted_name;
            }
        }
    }
    
    // Fallback: use townland + "Holy Well"
    if (!empty($well_data['townland'])) {
        return $well_data['townland'] . ' Holy Well';
    }
    
    // Last resort: use county + "Holy Well" + row identifier
    return $well_data['county'] . ' Holy Well (' . $well_data['smrs'] . ')';
}

function create_csv_holy_well_post($well_data, $well_name) {
    // Check if this holy well already exists (by name or SMRS code)
    $existing_posts = get_posts(array(
        'post_type' => 'christian_site',
        'title' => $well_name,
        'post_status' => 'any',
        'numberposts' => 1
    ));
    
    if (!empty($existing_posts)) {
        return array('success' => false, 'error' => 'Holy well already exists: ' . $well_name);
    }
    
    // Check by SMRS code
    if (!empty($well_data['smrs'])) {
        $existing_by_smrs = get_posts(array(
            'post_type' => 'christian_site',
            'meta_query' => array(
                array(
                    'key' => '_pilgrimirl_smrs_code',
                    'value' => $well_data['smrs'],
                    'compare' => '='
                )
            ),
            'numberposts' => 1
        ));
        
        if (!empty($existing_by_smrs)) {
            return array('success' => false, 'error' => 'Holy well with SMRS code already exists: ' . $well_data['smrs']);
        }
    }
    
    // Create description from available data
    $description = create_well_description($well_data);
    
    // Create the post
    $post_data = array(
        'post_title' => $well_name,
        'post_content' => $description,
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
        $county_name = ucfirst(strtolower($well_data['county']));
        $county_term = get_term_by('name', $county_name, 'county');
        if (!$county_term) {
            // Try to create the county term
            $county_result = wp_insert_term($county_name, 'county', array(
                'slug' => sanitize_title($county_name)
            ));
            if (!is_wp_error($county_result)) {
                $county_term = get_term($county_result['term_id'], 'county');
            }
        }
        if ($county_term && !is_wp_error($county_term)) {
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
    if (!empty($well_data['latitude']) && is_numeric($well_data['latitude'])) {
        update_post_meta($post_id, '_pilgrimirl_latitude', floatval($well_data['latitude']));
    }
    if (!empty($well_data['longitude']) && is_numeric($well_data['longitude'])) {
        update_post_meta($post_id, '_pilgrimirl_longitude', floatval($well_data['longitude']));
    }
    
    // Add additional metadata
    if (!empty($well_data['townland'])) {
        update_post_meta($post_id, '_pilgrimirl_townland', $well_data['townland']);
    }
    if (!empty($well_data['smrs'])) {
        update_post_meta($post_id, '_pilgrimirl_smrs_code', $well_data['smrs']);
    }
    if (!empty($well_data['website_link'])) {
        update_post_meta($post_id, '_pilgrimirl_archaeology_link', $well_data['website_link']);
    }
    
    update_post_meta($post_id, '_pilgrimirl_data_source', 'Government CSV');
    update_post_meta($post_id, '_pilgrimirl_monument_type', $well_data['monument_type']);
    
    return array('success' => true, 'post_id' => $post_id);
}

function create_well_description($well_data) {
    $description_parts = array();
    
    // Add location information
    if (!empty($well_data['townland']) && !empty($well_data['county'])) {
        $description_parts[] = "Located in {$well_data['townland']}, County {$well_data['county']}.";
    } elseif (!empty($well_data['county'])) {
        $description_parts[] = "Located in County {$well_data['county']}.";
    }
    
    // Add web notes if available
    if (!empty($well_data['web_notes'])) {
        $notes = $well_data['web_notes'];
        // Clean up the notes
        $notes = str_replace(array('"', "'"), '', $notes);
        $notes = trim($notes);
        
        // Limit length and add ellipsis if needed
        if (strlen($notes) > 500) {
            $notes = substr($notes, 0, 497) . '...';
        }
        
        $description_parts[] = $notes;
    }
    
    // Add SMRS reference
    if (!empty($well_data['smrs'])) {
        $description_parts[] = "Archaeological Survey Reference: {$well_data['smrs']}";
    }
    
    // Add source attribution
    $description_parts[] = "Data sourced from the Archaeological Survey of Ireland.";
    
    return implode("\n\n", $description_parts);
}

// Include the original JSON import functions
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
    update_post_meta($post_id, '_pilgrimirl_data_source', 'JSON Extraction');
    
    return array('success' => true, 'post_id' => $post_id);
}

// Add admin page for importing holy wells
function add_enhanced_holy_wells_import_page() {
    add_management_page(
        'Import Holy Wells Enhanced',
        'Import Holy Wells Enhanced',
        'manage_options',
        'import-holy-wells-enhanced',
        'enhanced_holy_wells_import_page'
    );
}
add_action('admin_menu', 'add_enhanced_holy_wells_import_page');

function enhanced_holy_wells_import_page() {
    ?>
    <div class="wrap">
        <h1>Enhanced Holy Wells Import</h1>
        <p>This tool imports Holy Wells from two sources:</p>
        <ul>
            <li><strong>JSON Data:</strong> Extracts Holy Wells mentioned in the monastic sites JSON data</li>
            <li><strong>CSV Data:</strong> Imports Holy Wells from the government archaeological survey CSV data</li>
        </ul>
        
        <?php if (isset($_POST['import_holy_wells_enhanced'])): ?>
            <div class="notice notice-info">
                <p>Running Enhanced Holy Wells import...</p>
            </div>
            
            <?php
            $result = import_holy_wells_enhanced();
            ?>
            
            <div class="notice notice-success">
                <p><strong>Enhanced Holy Wells import completed!</strong></p>
                <ul>
                    <li>JSON Sources: <?php echo $result['json_imported']; ?> Holy Wells</li>
                    <li>CSV Sources: <?php echo $result['csv_imported']; ?> Holy Wells</li>
                    <li><strong>Total Imported: <?php echo $result['total_imported']; ?> Holy Wells</strong></li>
                    <?php if (!empty($result['errors'])): ?>
                        <li>Errors: <?php echo count($result['errors']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php if (!empty($result['errors'])): ?>
                <div class="notice notice-error">
                    <p><strong>Errors encountered:</strong></p>
                    <ul>
                        <?php foreach (array_slice($result['errors'], 0, 20) as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($result['errors']) > 20): ?>
                            <li><em>... and <?php echo count($result['errors']) - 20; ?> more errors</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($result['debug'])): ?>
                <div class="notice notice-info">
                    <p><strong>Debug Information:</strong></p>
                    <ul>
                        <?php foreach (array_slice($result['debug'], 0, 30) as $debug): ?>
                            <li><?php echo esc_html($debug); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($result['debug']) > 30): ?>
                            <li><em>... and <?php echo count($result['debug']) - 30; ?> more debug entries</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <form method="post" action="">
            <p>
                <input type="submit" name="import_holy_wells_enhanced" class="button button-primary" 
                       value="Import Holy Wells (Enhanced)" 
                       onclick="return confirm('Are you sure you want to import Holy Wells from both JSON and CSV sources? This will create new Christian Site posts.');">
            </p>
        </form>
        
        <h2>Data Source Status</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Data Source</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>JSON Directory</td>
                    <td><?php echo is_dir(ABSPATH . 'MonasticSites_JSON/') ? '<span style="color: green;">✓ Found</span>' : '<span style="color: red;">✗ Missing</span>'; ?></td>
                    <td><?php echo ABSPATH . 'MonasticSites_JSON/'; ?></td>
                </tr>
                <tr>
                    <td>CSV File</td>
                    <td><?php echo file_exists(ABSPATH . 'holy_wells_gov_data.csv') ? '<span style="color: green;">✓ Found</span>' : '<span style="color: red;">✗ Missing</span>'; ?></td>
                    <td><?php echo ABSPATH . 'holy_wells_gov_data.csv'; ?></td>
                </tr>
            </tbody>
        </table>
        
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
                $data_source = get_post_meta($well->ID, '_pilgrimirl_data_source', true);
                $source_str = $data_source ? ' [' . $data_source . ']' : '';
                echo '<li>' . esc_html($well->post_title) . $county_str . $source_str . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No Holy Wells found in database.</p>';
        }
        ?>
        
        <p><a href="<?php echo get_post_type_archive_link('christian_site'); ?>" class="button">View Christian Sites Archive</a></p>
    </div>
    <?php
}
?>
