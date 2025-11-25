<?php
/**
 * PilgrimIRL Front Page Template
 * 
 * This template is used for the site's homepage
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
    
    <!-- Advanced Filters Section -->
    <section class="pilgrim-filters-section">
        <div class="pilgrim-filters-container">
            <h2 class="pilgrim-filters-title">Explore Sacred Ireland</h2>
            <p class="pilgrim-filters-subtitle">Filter by type, location, saints, or historical period to discover Ireland's spiritual heritage</p>
            
            <!-- Post Type Filters -->
            <div class="filter-group post-type-filters">
                <h3 class="filter-group-title">Site Types</h3>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-type="all">All Sites</button>
                    <button class="filter-btn" data-type="monastic_site">Monastic Sites</button>
                    <button class="filter-btn" data-type="pilgrimage_route">Pilgrimage Routes</button>
                    <button class="filter-btn" data-type="christian_site">Christian Sites</button>
                </div>
            </div>
            
            
            <!-- Saints Filters -->
            <div class="filter-group" id="saints-filters">
                <h3 class="filter-group-title">Associated Saints</h3>
                <div class="filter-dropdown">
                    <select id="saints-select" class="filter-select">
                        <option value="">All Saints</option>
                        <!-- Dynamically loaded by JavaScript -->
                    </select>
                </div>
            </div>
            
            <!-- Century Filters -->
            <div class="filter-group" id="centuries-filters">
                <h3 class="filter-group-title">Historical Periods</h3>
                <div class="filter-buttons">
                    <!-- Dynamically loaded by JavaScript -->
                </div>
            </div>
        </div>
    </section>
    
    <!-- Interactive Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Interactive Map</h2>
            <p>Explore filtered locations on our interactive map of Ireland</p>
            
            <div id="pilgrim-main-map" class="pilgrim-map-container">
                <!-- Map will be initialized by JavaScript -->
            </div>
        </div>
    </section>
    
    <!-- Results Summary Section -->
    <section class="pilgrim-results-section">
        <div class="pilgrim-results-container">
            <div class="results-header">
                <div class="results-count" id="results-count">Loading sites...</div>
                <div class="results-links">
                    <a href="<?php echo get_post_type_archive_link('monastic_site'); ?>" class="results-link">View All Monastic Sites</a>
                    <a href="<?php echo get_post_type_archive_link('pilgrimage_route'); ?>" class="results-link">View All Pilgrimage Routes</a>
                    <a href="<?php echo get_post_type_archive_link('christian_site'); ?>" class="results-link">View All Christian Sites</a>
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
