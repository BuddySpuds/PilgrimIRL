<?php
/**
 * Cleanup Christian Sites - Remove Abbey/Monastery data
 * 
 * This script removes Abbey and Monastery data from Christian Sites
 * since they should only be in Monastic Sites post type
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove Abbey/Monastery data from Christian Sites
 */
function cleanup_christian_sites_data() {
    $results = array(
        'removed' => 0,
        'errors' => array(),
        'debug' => array()
    );
    
    // Get all Christian Sites
    $christian_sites = get_posts(array(
        'post_type' => 'christian_site',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
    
    $results['debug'][] = "Found " . count($christian_sites) . " Christian Sites to check";
    
    foreach ($christian_sites as $site) {
        $title = $site->post_title;
        $content = $site->post_content;
        $site_types = wp_get_post_terms($site->ID, 'site_type', array('fields' => 'names'));
        
        // Check if this is Abbey/Monastery data that should be removed
        $is_abbey_data = false;
        
        // Check title for abbey/monastery keywords
        $abbey_keywords = array('abbey', 'monastery', 'priory', 'friary', 'nunnery');
        foreach ($abbey_keywords as $keyword) {
            if (stripos($title, $keyword) !== false) {
                $is_abbey_data = true;
                break;
            }
        }
        
        // Check site types for abbey/monastery types
        if (!$is_abbey_data && !empty($site_types)) {
            $abbey_site_types = array('Abbey Ruin', 'Monastery', 'Priory Ruin', 'Friary');
            foreach ($site_types as $site_type) {
                if (in_array($site_type, $abbey_site_types)) {
                    $is_abbey_data = true;
                    break;
                }
            }
        }
        
        // Check content for abbey/monastery indicators
        if (!$is_abbey_data) {
            foreach ($abbey_keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    // Additional check to make sure it's not just mentioning an abbey
                    if (preg_match('/\b' . $keyword . '\b/i', $title) || 
                        preg_match('/founded.*' . $keyword . '/i', $content) ||
                        preg_match('/' . $keyword . '.*founded/i', $content)) {
                        $is_abbey_data = true;
                        break;
                    }
                }
            }
        }
        
        if ($is_abbey_data) {
            $results['debug'][] = "Removing Abbey/Monastery data: " . $title;
            
            // Delete the post
            $deleted = wp_delete_post($site->ID, true);
            
            if ($deleted) {
                $results['removed']++;
            } else {
                $results['errors'][] = "Failed to delete: " . $title;
            }
        }
    }
    
    return $results;
}

/**
 * Admin page for cleanup
 */
function add_cleanup_christian_sites_admin_page() {
    add_management_page(
        'Cleanup Christian Sites',
        'Cleanup Christian Sites',
        'manage_options',
        'cleanup-christian-sites',
        'cleanup_christian_sites_admin_page'
    );
}
add_action('admin_menu', 'add_cleanup_christian_sites_admin_page');

function cleanup_christian_sites_admin_page() {
    ?>
    <div class="wrap">
        <h1>Cleanup Christian Sites</h1>
        <p>This tool removes Abbey and Monastery data from Christian Sites (they should only be in Monastic Sites).</p>
        
        <?php if (isset($_POST['cleanup_christian_sites'])): ?>
            <div class="notice notice-info">
                <p>Running cleanup process...</p>
            </div>
            
            <?php
            $results = cleanup_christian_sites_data();
            ?>
            
            <div class="notice notice-success">
                <p><strong>Cleanup completed!</strong></p>
                <ul>
                    <li>Removed: <?php echo $results['removed']; ?> Abbey/Monastery entries</li>
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
                <input type="submit" name="cleanup_christian_sites" class="button button-primary" 
                       value="Remove Abbey/Monastery Data from Christian Sites" 
                       onclick="return confirm('Are you sure you want to remove Abbey/Monastery data from Christian Sites? This action cannot be undone.');">
            </p>
        </form>
        
        <h2>Current Christian Sites</h2>
        <?php
        $current_sites = get_posts(array(
            'post_type' => 'christian_site',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        if ($current_sites) {
            echo "<p>Currently " . count($current_sites) . " Christian Sites in database:</p>";
            echo "<ul>";
            foreach ($current_sites as $site) {
                $site_types = wp_get_post_terms($site->ID, 'site_type', array('fields' => 'names'));
                $site_type_str = !empty($site_types) ? ' (' . implode(', ', $site_types) . ')' : '';
                echo "<li>" . esc_html($site->post_title) . $site_type_str . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No Christian Sites found in database.</p>";
        }
        ?>
    </div>
    <?php
}
?>
