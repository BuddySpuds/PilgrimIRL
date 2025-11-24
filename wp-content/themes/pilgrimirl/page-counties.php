<?php
/**
 * Template Name: Counties Overview
 * 
 * Template for displaying all Irish counties with their pilgrimage sites
 */

get_header(); ?>

<div class="counties-overview">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Irish Counties</h1>
            <p class="page-description">Explore Ireland's sacred heritage by county. Each county holds unique monastic sites, pilgrimage routes, and Christian ruins waiting to be discovered.</p>
        </header>

        <div class="counties-grid">
            <?php
            // Get all counties
            $counties = get_terms(array(
                'taxonomy' => 'county',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ));

            if (!empty($counties) && !is_wp_error($counties)) :
                foreach ($counties as $county) :
                    // Get count of sites in this county
                    $site_count = wp_count_posts('monastic_site');
                    $route_count = wp_count_posts('pilgrimage_route');
                    $ruin_count = wp_count_posts('christian_site');
                    
                    // Get posts for this county
                    $county_posts = get_posts(array(
                        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
                        'posts_per_page' => -1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'county',
                                'field' => 'term_id',
                                'terms' => $county->term_id
                            )
                        )
                    ));
                    
                    $total_sites = count($county_posts);
                    ?>
                    <div class="county-card">
                        <div class="county-card-header">
                            <h3 class="county-name">
                                <a href="<?php echo get_term_link($county); ?>">
                                    County <?php echo esc_html($county->name); ?>
                                </a>
                            </h3>
                            <div class="county-stats">
                                <span class="site-count"><?php echo $total_sites; ?> sites</span>
                            </div>
                        </div>
                        
                        <div class="county-card-body">
                            <?php if ($county->description) : ?>
                                <p class="county-description"><?php echo esc_html($county->description); ?></p>
                            <?php endif; ?>
                            
                            <div class="site-breakdown">
                                <?php
                                $monastic_count = 0;
                                $route_count = 0;
                                $ruin_count = 0;
                                
                                foreach ($county_posts as $post) {
                                    switch ($post->post_type) {
                                        case 'monastic_site':
                                            $monastic_count++;
                                            break;
                                        case 'pilgrimage_route':
                                            $route_count++;
                                            break;
                                        case 'christian_site':
                                            $ruin_count++;
                                            break;
                                    }
                                }
                                ?>
                                
                                <?php if ($monastic_count > 0) : ?>
                                    <span class="site-type monastic"><?php echo $monastic_count; ?> Monastic Sites</span>
                                <?php endif; ?>
                                
                                <?php if ($route_count > 0) : ?>
                                    <span class="site-type routes"><?php echo $route_count; ?> Pilgrimage Routes</span>
                                <?php endif; ?>
                                
                                <?php if ($ruin_count > 0) : ?>
                                    <span class="site-type ruins"><?php echo $ruin_count; ?> Christian Sites</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="county-card-footer">
                            <a href="<?php echo get_term_link($county); ?>" class="explore-county-btn">
                                Explore <?php echo esc_html($county->name); ?>
                                <span class="arrow">→</span>
                            </a>
                        </div>
                    </div>
                    <?php
                endforeach;
            else :
                ?>
                <div class="no-counties">
                    <h3>No Counties Found</h3>
                    <p>Counties will appear here once they are created and populated with sites.</p>
                </div>
                <?php
            endif;
            ?>
        </div>

        <!-- Interactive Map Section -->
        <section class="counties-map-section">
            <h2>Interactive Map of Ireland</h2>
            <p>Explore all pilgrimage sites across Ireland on our interactive map.</p>
            <div id="counties-map" class="interactive-map"></div>
            
            <div class="map-filters">
                <button class="filter-btn active" data-filter="all">All Sites</button>
                <button class="filter-btn" data-filter="monastic_site">Monastic Sites</button>
                <button class="filter-btn" data-filter="pilgrimage_route">Pilgrimage Routes</button>
                <button class="filter-btn" data-filter="christian_site">Christian Sites</button>
            </div>
        </section>
    </div>
</div>

<script>
// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initPilgrimMaps === 'function') {
        initPilgrimMaps();
    }
});
</script>

<?php get_footer(); ?>
