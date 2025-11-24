<?php
/**
 * Template Name: Counties Debug
 * 
 * Simple debug version to test if template is working
 */

get_header(); ?>

<div style="padding: 20px; background: #f0f0f0; margin: 20px;">
    <h1>Counties Debug Page</h1>
    <p><strong>✅ Template is working!</strong></p>
    <p>Current URL: <?php echo home_url($_SERVER['REQUEST_URI']); ?></p>
    <p>Page ID: <?php echo get_the_ID(); ?></p>
    <p>Page Title: <?php echo get_the_title(); ?></p>
    
    <h2>Counties Test</h2>
    <?php
    // Test getting counties
    $counties = get_terms(array(
        'taxonomy' => 'county',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));

    if (!empty($counties) && !is_wp_error($counties)) {
        echo '<p><strong>✅ Found ' . count($counties) . ' counties:</strong></p>';
        echo '<ul>';
        foreach ($counties as $county) {
            echo '<li>' . esc_html($county->name) . ' (ID: ' . $county->term_id . ')</li>';
        }
        echo '</ul>';
    } else {
        echo '<p><strong>❌ No counties found or error occurred</strong></p>';
        if (is_wp_error($counties)) {
            echo '<p>Error: ' . $counties->get_error_message() . '</p>';
        }
    }
    ?>
    
    <h2>Google Maps API Test</h2>
    <?php
    $api_key = get_option('pilgrimirl_google_maps_api_key', '');
    if (!empty($api_key)) {
        echo '<p><strong>✅ Google Maps API Key is set</strong></p>';
        echo '<p>Key starts with: ' . substr($api_key, 0, 10) . '...</p>';
    } else {
        echo '<p><strong>❌ Google Maps API Key is NOT set</strong></p>';
        echo '<p>Go to Settings → General to add it.</p>';
    }
    ?>
    
    <h2>Theme Files Test</h2>
    <?php
    $css_file = get_stylesheet_directory() . '/css/county-pages.css';
    $js_file = get_stylesheet_directory() . '/js/pilgrimirl.js';
    
    echo '<p>CSS File exists: ' . (file_exists($css_file) ? '✅ Yes' : '❌ No') . '</p>';
    echo '<p>JS File exists: ' . (file_exists($js_file) ? '✅ Yes' : '❌ No') . '</p>';
    ?>
    
    <h2>Instructions</h2>
    <p>If you see this page, the template system is working. Now:</p>
    <ol>
        <li>Change the Counties page template back to "Counties Overview"</li>
        <li>Make sure you're visiting <code>/counties/</code> (with 's')</li>
        <li>Check that counties exist in WordPress admin</li>
        <li>Add your Google Maps API key if not already done</li>
    </ol>
</div>

<?php get_footer(); ?>
