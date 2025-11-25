<?php
/**
 * PilgrimIRL Homepage Template
 * 
 * Main landing page for Irish Pilgrimage and Monastic Sites
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="container">
                <h1 class="hero-title">Discover Ireland's Sacred Heritage</h1>
                <p class="hero-subtitle">Explore ancient monastic sites, pilgrimage routes, and Christian ruins across the Emerald Isle</p>
                
                <!-- Search Section -->
                <div class="pilgrim-search-container">
                    <form id="pilgrim-search-form" class="search-form">
                        <div class="search-input-group">
                            <input type="text" id="pilgrim-search-input" placeholder="Search for sites, routes, or locations..." />
                            <button type="submit" class="pilgrim-btn">Search</button>
                        </div>
                        
                        <div class="search-filters">
                            <select id="filter-county" class="filter-select">
                                <option value="">All Counties</option>
                                <?php
                                $counties = pilgrimirl_get_irish_counties();
                                foreach ($counties as $slug => $name) {
                                    echo '<option value="' . esc_attr($slug) . '">' . esc_html($name) . '</option>';
                                }
                                ?>
                            </select>
                            
                            <select id="filter-post-type" class="filter-select">
                                <option value="">All Types</option>
                                <option value="monastic_site">Monastic Sites</option>
                                <option value="pilgrimage_route">Pilgrimage Routes</option>
                                <option value="christian_site">Christian Sites</option>
                            </select>
                            
                            <button type="button" id="reset-filters" class="pilgrim-btn pilgrim-btn-secondary">Reset</button>
                        </div>
                    </form>
                    
                    <div id="search-loading" class="search-loading" style="display: none;">
                        <p>Searching...</p>
                    </div>
                    
                    <div id="pilgrim-search-results" class="search-results"></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Content Section -->
    <section class="featured-content">
        <div class="container">
            <h2>Explore by Category</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏛️</div>
                    <h3>Monastic Sites</h3>
                    <p>Discover ancient monasteries and abbeys that shaped Ireland's spiritual landscape</p>
                    <a href="<?php echo get_post_type_archive_link('monastic_site'); ?>" class="pilgrim-btn">Explore Sites</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🚶‍♂️</div>
                    <h3>Pilgrimage Routes</h3>
                    <p>Walk in the footsteps of saints along Ireland's sacred pathways</p>
                    <a href="<?php echo get_post_type_archive_link('pilgrimage_route'); ?>" class="pilgrim-btn">View Routes</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⛪</div>
                    <h3>Christian Sites</h3>
                    <p>Explore the remnants of Ireland's early Christian heritage</p>
                    <a href="<?php echo get_post_type_archive_link('christian_site'); ?>" class="pilgrim-btn">Explore Christian Sites</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Counties Overview -->
    <section class="counties-overview">
        <div class="container">
            <h2>Explore by County</h2>
            <p class="section-intro">Ireland's 32 counties each hold unique treasures of Christian heritage</p>
            
            <div class="county-grid">
                <?php
                $counties = get_terms(array(
                    'taxonomy' => 'county',
                    'hide_empty' => false,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ));
                
                if ($counties && !is_wp_error($counties)) {
                    foreach ($counties as $county) {
                        $county_count = wp_count_posts_by_county($county->slug);
                        ?>
                        <div class="county-card" data-county="<?php echo esc_attr($county->slug); ?>">
                            <h3><?php echo esc_html($county->name); ?></h3>
                            <div class="county-stats">
                                <span class="site-count"><?php echo $county_count; ?> sites</span>
                            </div>
                            <p class="county-description"><?php echo esc_html($county->description ?: 'Discover the sacred heritage of ' . $county->name); ?></p>
                            <a href="<?php echo get_term_link($county); ?>" class="county-link">Explore <?php echo esc_html($county->name); ?></a>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
    
    <!-- Interactive Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Interactive Map</h2>
            <p>Explore all locations on our interactive map of Ireland</p>
            
            <div id="pilgrim-main-map" class="pilgrim-map-container">
                <!-- Map will be initialized by JavaScript -->
            </div>
            
            <div class="map-controls">
                <button class="map-filter-btn" data-type="all">All Sites</button>
                <button class="map-filter-btn" data-type="monastic_site">Monastic Sites</button>
                <button class="map-filter-btn" data-type="pilgrimage_route">Pilgrimage Routes</button>
                <button class="map-filter-btn" data-type="christian_site">Christian Sites</button>
            </div>
        </div>
    </section>
    
    <!-- Recent Additions -->
    <section class="recent-additions">
        <div class="container">
            <h2>Recently Added</h2>
            
            <div class="recent-grid">
                <?php
                $recent_posts = new WP_Query(array(
                    'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
                    'posts_per_page' => 6,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'post_status' => 'publish'
                ));
                
                if ($recent_posts->have_posts()) {
                    while ($recent_posts->have_posts()) {
                        $recent_posts->the_post();
                        $post_type_obj = get_post_type_object(get_post_type());
                        $counties = wp_get_post_terms(get_the_ID(), 'county');
                        ?>
                        <div class="site-card recent-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="card-image">
                                    <?php the_post_thumbnail('site-card-thumb'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-content">
                                <div class="card-meta">
                                    <span class="post-type-label"><?php echo $post_type_obj->labels->singular_name; ?></span>
                                    <?php if (!empty($counties)) : ?>
                                        <span class="county-label"><?php echo esc_html($counties[0]->name); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                
                                <div class="card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="card-actions">
                                    <a href="<?php the_permalink(); ?>" class="read-more-btn">Learn More</a>
                                    <?php
                                    $lat = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                                    $lng = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                                    if ($lat && $lng) :
                                    ?>
                                        <button class="show-on-map-btn" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>">View on Map</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    wp_reset_postdata();
                }
                ?>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Join Our Community</h2>
                <p>Connect with fellow pilgrims, share your experiences, and discover new sacred sites</p>
                <div class="cta-buttons">
                    <a href="/community" class="pilgrim-btn">Join Forum</a>
                    <a href="/blog" class="pilgrim-btn pilgrim-btn-secondary">Read Blog</a>
                </div>
            </div>
        </div>
    </section>
    
</main>

<?php
get_footer();

/**
 * Helper function to count posts by county
 */
function wp_count_posts_by_county($county_slug) {
    $args = array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'county',
                'field' => 'slug',
                'terms' => $county_slug
            )
        )
    );
    
    $query = new WP_Query($args);
    return $query->found_posts;
}
?>
