<?php
/**
 * Standalone Enhanced Holy Wells Import Runner
 * 
 * This script can be run directly to execute the enhanced import
 * without needing to access the WordPress admin interface.
 */

// Load WordPress
require_once('../../../wp-load.php');

// Include the enhanced import functions
require_once('import-holy-wells-enhanced.php');

// Set execution time limit for large imports
set_time_limit(300); // 5 minutes

echo "<h1>Enhanced Holy Wells Import - Standalone Runner</h1>\n";
echo "<p>Starting enhanced import process...</p>\n";

// Flush output immediately
if (ob_get_level()) {
    ob_end_flush();
}
flush();

// Run the enhanced import
echo "<h2>Running Enhanced Import...</h2>\n";
$result = import_holy_wells_enhanced();

// Display results
echo "<h2>Import Results</h2>\n";
echo "<ul>\n";
echo "<li><strong>JSON Sources:</strong> {$result['json_imported']} Holy Wells imported</li>\n";
echo "<li><strong>CSV Sources:</strong> {$result['csv_imported']} Holy Wells imported</li>\n";
echo "<li><strong>Total Imported:</strong> {$result['total_imported']} Holy Wells</li>\n";
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

// Show current Holy Wells count
echo "<h2>Current Holy Wells in Database</h2>\n";
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
    echo "<p>Currently " . count($holy_wells) . " Holy Wells in database:</p>\n";
    echo "<ul>\n";
    foreach ($holy_wells as $well) {
        $counties = wp_get_post_terms($well->ID, 'county', array('fields' => 'names'));
        $county_str = !empty($counties) ? ' (' . implode(', ', $counties) . ')' : '';
        $data_source = get_post_meta($well->ID, '_pilgrimirl_data_source', true);
        $source_str = $data_source ? ' [' . $data_source . ']' : '';
        echo "<li>" . htmlspecialchars($well->post_title) . $county_str . $source_str . "</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p>No Holy Wells found in database.</p>\n";
}

echo "<h2>Data Source Status</h2>\n";
echo "<ul>\n";
echo "<li><strong>JSON Directory:</strong> " . (is_dir(ABSPATH . 'MonasticSites_JSON/') ? '✓ Found' : '✗ Missing') . " (" . ABSPATH . 'MonasticSites_JSON/' . ")</li>\n";
echo "<li><strong>CSV File:</strong> " . (file_exists(ABSPATH . 'holy_wells_gov_data.csv') ? '✓ Found' : '✗ Missing') . " (" . ABSPATH . 'holy_wells_gov_data.csv' . ")</li>\n";
echo "</ul>\n";

echo "<p><a href='" . get_post_type_archive_link('christian_site') . "'>View Christian Sites Archive</a></p>\n";

echo "<h2>Import Complete</h2>\n";
echo "<p>Enhanced Holy Wells import process finished.</p>\n";
?>
