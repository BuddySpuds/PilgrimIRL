<?php
/**
 * Fix Missing Counties - Cork, Down, Clare
 * 
 * Targeted script to import specific counties that are missing
 */

// Load WordPress
require_once('../../../wp-config.php');

echo "<h1>Fix Missing Counties: Cork, Down, Clare</h1>";

// Include the data importer functions - use direct path to pilgrimirl theme
require_once(WP_CONTENT_DIR . '/themes/pilgrimirl/includes/data-importer.php');

// Counties to fix - map display name to file name
$counties_to_fix = array(
    'Cork' => 'Cork',
    'Down' => 'Down', 
    'Clare' => 'Claire'  // Note: file is actually "Claire-enriched.json"
);

echo "<h2>Current Status Check:</h2>";

// Check current status
foreach ($counties_to_fix as $county_name => $file_name) {
    $posts = get_posts(array(
        'post_type' => 'monastic_site',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => array(
            array(
                'key' => 'county',
                'value' => $county_name,
                'compare' => '='
            )
        )
    ));
    
    // Also check by taxonomy
    $tax_posts = get_posts(array(
        'post_type' => 'monastic_site',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'tax_query' => array(
            array(
                'taxonomy' => 'county',
                'field' => 'name',
                'terms' => $county_name
            )
        )
    ));
    
    echo "<p><strong>{$county_name}:</strong><br>";
    echo "Posts by meta: " . count($posts) . "<br>";
    echo "Posts by taxonomy: " . count($tax_posts) . "<br>";
    echo "File to check: {$file_name}-enriched.json<br>";
    echo "</p>";
}

echo "<h2>Force Import Process:</h2>";

// Force import each county
foreach ($counties_to_fix as $county_name => $file_name) {
    echo "<h3>Processing {$county_name}:</h3>";
    
    $file_path = ABSPATH . 'MonasticSites_JSON/' . $file_name . '-enriched.json';
    
    echo "<p>Looking for file: {$file_path}</p>";
    
    if (!file_exists($file_path)) {
        echo "<p style='color: red;'>❌ File not found: {$file_path}</p>";
        continue;
    }
    
    $content = file_get_contents($file_path);
    if (!$content) {
        echo "<p style='color: red;'>❌ Could not read file: {$file_path}</p>";
        continue;
    }
    
    $data = json_decode($content, true);
    
    if (!$data) {
        echo "<p style='color: red;'>❌ Invalid JSON in {$county_name}</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
        echo "<p>File size: " . strlen($content) . " bytes</p>";
        echo "<p>First 200 chars: " . substr($content, 0, 200) . "...</p>";
        continue;
    }
    
    echo "<p>✅ Found " . count($data) . " sites in {$county_name} JSON</p>";
    
    $imported = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($data as $site) {
        // Check if post already exists by title
        $existing = get_posts(array(
            'post_type' => 'monastic_site',
            'title' => $site['foundation_name'],
            'post_status' => 'any',
            'posts_per_page' => 1
        ));
        
        if (!empty($existing)) {
            $skipped++;
            continue;
        }
        
        // Create the post
        $post_data = array(
            'post_title' => $site['foundation_name'],
            'post_content' => $site['communities_provenance'],
            'post_status' => 'publish',
            'post_type' => 'monastic_site'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            $errors++;
            continue;
        }
        
        // Add metadata - use the display county name (Clare, not Claire)
        update_post_meta($post_id, 'county', $county_name);
        update_post_meta($post_id, 'alternative_names', $site['alternative_names']);
        update_post_meta($post_id, 'communities_provenance', $site['communities_provenance']);
        update_post_meta($post_id, 'metadata_tags', $site['metadata_tags']);
        
        // Add coordinates if available
        if (isset($site['coordinates']) && $site['coordinates']) {
            update_post_meta($post_id, 'latitude', $site['coordinates']['latitude']);
            update_post_meta($post_id, 'longitude', $site['coordinates']['longitude']);
        }
        
        // Set county taxonomy - use display name
        wp_set_object_terms($post_id, $county_name, 'county');
        
        $imported++;
    }
    
    echo "<p><strong>Results for {$county_name}:</strong><br>";
    echo "✅ Imported: {$imported}<br>";
    echo "⏭️ Skipped (already exist): {$skipped}<br>";
    echo "❌ Errors: {$errors}<br>";
    echo "</p>";
}

echo "<h2>Final Status Check:</h2>";

// Check final status
foreach ($counties_to_fix as $county_name => $file_name) {
    $posts = get_posts(array(
        'post_type' => 'monastic_site',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'tax_query' => array(
            array(
                'taxonomy' => 'county',
                'field' => 'name',
                'terms' => $county_name
            )
        )
    ));
    
    echo "<p><strong>{$county_name}:</strong> " . count($posts) . " posts now exist</p>";
}

echo "<hr>";
echo "<h2>Quick Links:</h2>";
echo "<p><a href='" . admin_url('edit.php?post_type=monastic_site') . "' target='_blank'>View All Monastic Sites</a></p>";
echo "<p><a href='" . home_url('/counties/') . "' target='_blank'>View Counties Page</a></p>";

?>
