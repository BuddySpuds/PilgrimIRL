<?php
/**
 * Standalone CSV Holy Wells Import Runner
 * 
 * This script imports Holy Wells from the government CSV data only
 */

// Load WordPress
require_once('../../../wp-load.php');

// Set execution time limit for large imports
set_time_limit(300); // 5 minutes

echo "<h1>CSV Holy Wells Import - Standalone Runner</h1>\n";
echo "<p>Starting CSV import process...</p>\n";

// Flush output immediately
if (ob_get_level()) {
    ob_end_flush();
}
flush();

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
    $name = '';
    
    // Try FIRST_EDIT first
    if (!empty($well_data['first_edit']) && $well_data['first_edit'] !== 'Not indicated') {
        $name = $well_data['first_edit'];
        $name = str_replace(array("'", '"'), '', $name);
        $name = trim($name);
        
        if (strlen($name) >= 3 && strlen($name) <= 60) {
            return $name;
        }
    }
    
    // Try to extract from web notes
    if (!empty($well_data['web_notes'])) {
        $notes = $well_data['web_notes'];
        
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
    
    if (!empty($well_data['townland']) && !empty($well_data['county'])) {
        $description_parts[] = "Located in {$well_data['townland']}, County {$well_data['county']}.";
    } elseif (!empty($well_data['county'])) {
        $description_parts[] = "Located in County {$well_data['county']}.";
    }
    
    if (!empty($well_data['web_notes'])) {
        $notes = $well_data['web_notes'];
        $notes = str_replace(array('"', "'"), '', $notes);
        $notes = trim($notes);
        
        if (strlen($notes) > 500) {
            $notes = substr($notes, 0, 497) . '...';
        }
        
        $description_parts[] = $notes;
    }
    
    if (!empty($well_data['smrs'])) {
        $description_parts[] = "Archaeological Survey Reference: {$well_data['smrs']}";
    }
    
    $description_parts[] = "Data sourced from the Archaeological Survey of Ireland.";
    
    return implode("\n\n", $description_parts);
}

// Run the CSV import
echo "<h2>Running CSV Import...</h2>\n";
$result = import_holy_wells_from_csv();

// Display results
echo "<h2>Import Results</h2>\n";
echo "<ul>\n";
echo "<li><strong>CSV Sources:</strong> {$result['imported']} Holy Wells imported</li>\n";
echo "<li><strong>Errors:</strong> " . count($result['errors']) . "</li>\n";
echo "</ul>\n";

// Show errors if any
if (!empty($result['errors'])) {
    echo "<h3>Errors Encountered:</h3>\n";
    echo "<ul>\n";
    foreach (array_slice($result['errors'], 0, 20) as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>\n";
    }
    if (count($result['errors']) > 20) {
        echo "<li><em>... and " . (count($result['errors']) - 20) . " more errors</em></li>\n";
    }
    echo "</ul>\n";
}

// Show debug information
if (!empty($result['debug'])) {
    echo "<h3>Debug Information:</h3>\n";
    echo "<ul>\n";
    foreach (array_slice($result['debug'], 0, 30) as $debug) {
        echo "<li>" . htmlspecialchars($debug) . "</li>\n";
    }
    if (count($result['debug']) > 30) {
        echo "<li><em>... and " . (count($result['debug']) - 30) . " more debug entries</em></li>\n";
    }
    echo "</ul>\n";
}

echo "<h2>Data Source Status</h2>\n";
echo "<ul>\n";
echo "<li><strong>CSV File:</strong> " . (file_exists(ABSPATH . 'holy_wells_gov_data.csv') ? '✓ Found' : '✗ Missing') . " (" . ABSPATH . 'holy_wells_gov_data.csv' . ")</li>\n";
echo "</ul>\n";

echo "<p><a href='" . get_post_type_archive_link('christian_site') . "'>View Christian Sites Archive</a></p>\n";

echo "<h2>CSV Import Complete</h2>\n";
echo "<p>CSV Holy Wells import process finished.</p>\n";
?>
