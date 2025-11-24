<?php
/**
 * Single Pilgrimage Route Template
 * 
 * Displays individual pilgrimage route details
 */

get_header();

while (have_posts()) : the_post();
    $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
    $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
    $distance = get_post_meta(get_the_ID(), '_pilgrimirl_distance', true);
    $pets_allowed = get_post_meta(get_the_ID(), '_pilgrimirl_pets_allowed', true);
    $associated_saints = get_post_meta(get_the_ID(), '_pilgrimirl_associated_saints', true);
    $address = get_post_meta(get_the_ID(), '_pilgrimirl_address', true);
    
    // Get taxonomies
    $counties = get_the_terms(get_the_ID(), 'county');
    $difficulty = get_the_terms(get_the_ID(), 'difficulty_level');
    $features = get_the_terms(get_the_ID(), 'pilgrimage_features');
?>

<main id="main" class="site-main single-pilgrimage-route">
    
    <!-- Route Header -->
    <section class="route-header">
        <div class="container">
            <?php if (has_post_thumbnail()) : ?>
                <div class="route-hero-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>
            
            <div class="route-header-content">
                <div class="route-meta">
                    <span class="post-type-label">Pilgrimage Route</span>
                    <?php if ($counties) : ?>
                        <span class="county-label"><?php echo esc_html($counties[0]->name); ?></span>
                    <?php endif; ?>
                </div>
                
                <h1 class="route-title"><?php the_title(); ?></h1>
                
                <div class="route-quick-info">
                    <?php if ($distance) : ?>
                        <div class="info-item">
                            <span class="info-label">Distance:</span>
                            <span class="info-value"><?php echo esc_html($distance); ?> km</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($difficulty) : ?>
                        <div class="info-item">
                            <span class="info-label">Difficulty:</span>
                            <span class="info-value"><?php echo esc_html($difficulty[0]->name); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pets_allowed && $pets_allowed !== 'unknown') : ?>
                        <div class="info-item">
                            <span class="info-label">Pets:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($pets_allowed)); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Route Content -->
    <section class="route-content">
        <div class="container">
            <div class="content-grid">
                
                <!-- Main Content -->
                <div class="main-content">
                    <div class="route-description">
                        <?php the_content(); ?>
                    </div>
                    
                    <?php if ($features) : ?>
                        <div class="route-features">
                            <h3>Route Features</h3>
                            <div class="features-list">
                                <?php foreach ($features as $feature) : ?>
                                    <span class="feature-tag"><?php echo esc_html($feature->name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($associated_saints) : ?>
                        <div class="associated-saints">
                            <h3>Associated Saints</h3>
                            <p><?php echo esc_html($associated_saints); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="route-sidebar">
                    
                    <!-- Location Info -->
                    <div class="sidebar-section location-info">
                        <h3>Location Details</h3>
                        
                        <?php if ($address) : ?>
                            <div class="info-row">
                                <strong>Address:</strong>
                                <span><?php echo esc_html($address); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($counties) : ?>
                            <div class="info-row">
                                <strong>County:</strong>
                                <span><?php echo esc_html($counties[0]->name); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($latitude && $longitude) : ?>
                            <div class="info-row">
                                <strong>Coordinates:</strong>
                                <span><?php echo esc_html($latitude); ?>, <?php echo esc_html($longitude); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Route Map -->
                    <?php if ($latitude && $longitude) : ?>
                        <div class="sidebar-section route-map">
                            <h3>Route Location</h3>
                            <div class="pilgrim-site-map" 
                                 data-lat="<?php echo esc_attr($latitude); ?>" 
                                 data-lng="<?php echo esc_attr($longitude); ?>"
                                 data-title="<?php echo esc_attr(get_the_title()); ?>">
                                <!-- Map will be initialized by JavaScript -->
                            </div>
                            <button class="directions-btn" onclick="getDirections(<?php echo esc_attr($latitude); ?>, <?php echo esc_attr($longitude); ?>)">
                                Get Directions
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Related Routes -->
                    <div class="sidebar-section related-routes">
                        <h3>Other Routes in <?php echo $counties ? esc_html($counties[0]->name) : 'This Area'; ?></h3>
                        <?php
                        $related_args = array(
                            'post_type' => 'pilgrimage_route',
                            'posts_per_page' => 3,
                            'post__not_in' => array(get_the_ID()),
                            'post_status' => 'publish'
                        );
                        
                        if ($counties) {
                            $related_args['tax_query'] = array(
                                array(
                                    'taxonomy' => 'county',
                                    'field' => 'term_id',
                                    'terms' => $counties[0]->term_id
                                )
                            );
                        }
                        
                        $related_query = new WP_Query($related_args);
                        
                        if ($related_query->have_posts()) :
                            while ($related_query->have_posts()) : $related_query->the_post();
                                $related_distance = get_post_meta(get_the_ID(), '_pilgrimirl_distance', true);
                                ?>
                                <div class="related-route-item">
                                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                    <?php if ($related_distance) : ?>
                                        <p class="route-distance"><?php echo esc_html($related_distance); ?> km</p>
                                    <?php endif; ?>
                                </div>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                        else :
                            echo '<p>No related routes found.</p>';
                        endif;
                        ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
    
</main>

<script>
function getDirections(lat, lng) {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    window.open(url, '_blank');
}
</script>

<?php
endwhile;
get_footer();
?>
