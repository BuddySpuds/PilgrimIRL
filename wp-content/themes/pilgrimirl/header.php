<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover Ireland's sacred heritage through ancient monastic sites, pilgrimage routes, and Christian ruins across the Emerald Isle.">
    <meta name="keywords" content="Ireland, pilgrimage, monastic sites, Christian heritage, sacred sites, Irish history">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
    <div class="container">
        <div class="header-content">
            <!-- Site Logo/Brand -->
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
                
                <?php
                $description = get_bloginfo('description', 'display');
                if ($description || is_customize_preview()) :
                ?>
                    <p class="site-description sr-only"><?php echo $description; ?></p>
                <?php endif; ?>
            </div>

            <!-- Main Navigation -->
            <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Primary Menu', 'pilgrimirl'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'nav-menu',
                    'container'      => false,
                    'fallback_cb'    => 'pilgrimirl_fallback_menu',
                    'depth'          => 2,
                ));
                ?>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'pilgrimirl'); ?>">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </nav>
        </div>
    </div>
</header>

<?php
/**
 * Fallback menu for when no menu is assigned
 */
function pilgrimirl_fallback_menu() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
    
    // Check if custom post type archives exist
    if (post_type_exists('monastic_site')) {
        echo '<li><a href="' . esc_url(get_post_type_archive_link('monastic_site')) . '">Monastic Sites</a></li>';
    }
    
    if (post_type_exists('christian_site')) {
        echo '<li><a href="' . esc_url(get_post_type_archive_link('christian_site')) . '">Christian Sites</a></li>';
    }
    
    if (post_type_exists('pilgrimage_route')) {
        echo '<li><a href="' . esc_url(get_post_type_archive_link('pilgrimage_route')) . '">Pilgrimage Routes</a></li>';
    }
    
    if (post_type_exists('christian_ruin')) {
        echo '<li><a href="' . esc_url(get_post_type_archive_link('christian_ruin')) . '">Christian Ruins</a></li>';
    }
    
    // Counties page
    if (taxonomy_exists('county')) {
        echo '<li><a href="' . esc_url(home_url('/county/')) . '">Counties</a></li>';
    }
    
    echo '</ul>';
}
?>

<!-- Skip to content link for accessibility -->
<a class="sr-only" href="#main"><?php esc_html_e('Skip to content', 'pilgrimirl'); ?></a>
