<?php
/**
 * Direct Data Import Debug Runner
 * Navigate to this file in your browser to see data import status
 */

// Load WordPress
require_once('../../../wp-config.php');

// Include the debug script
include('debug-data-import.php');

echo "<hr><h2>Quick Actions:</h2>";
echo "<p><a href='" . admin_url('tools.php?page=pilgrimirl-import') . "' target='_blank'>Go to WordPress Admin Import Page</a></p>";
echo "<p><a href='" . home_url() . "' target='_blank'>Return to Site Homepage</a></p>";
?>
