<?php
/**
 * Archive template for Pilgrimage Routes
 * 
 * Displays all pilgrimage routes with filtering options
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <!-- Archive Header -->
    <section class="archive-header">
        <div class="container">
            <h1 class="archive-title">Pilgrimage Routes</h1>
            <p class="archive-description">Discover Ireland's sacred walking paths and ancient pilgrimage routes</p>
            
            <!-- Archive Filters -->
            <div class="archive-filters">
                <form id="archive-filter-form" class="filter-form">
                    <div class="filter-row">
                        <select id="archive-county-filter" class="filter-select">
                            <option value="">All Counties</option>
                            <?php
                            $counties = get_terms(array(
                                'taxonomy' => 'county',
                                'hide_empty' => true,
                                'orderby' => 'name'
                            ));
                            if ($counties && !is_wp_error($counties)) {
                                foreach ($counties as $county) {
                                    $selected = (isset($_GET['county']) && $_GET['county'] === $county->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($county->slug) . '" ' . $selected . '>' . esc_html($county->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        
                        <select id="archive-difficulty-filter" class="filter-select">
                            <option value="">Difficulty Level</option>
                            <?php
                            $difficulties = get_terms(array(
                                'taxonomy' => 'difficulty_level',
                                'hide_empty' => true,
                                'orderby' => 'name'
                            ));
                            if ($difficulties && !is_wp_error($difficulties)) {
                                foreach ($difficulties as $difficulty) {
                                    $selected = (isset($_GET['difficulty_level']) && $_GET['difficulty_level'] === $difficulty->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($difficulty->slug) . '" ' . $selected . '>' . esc_html($difficulty->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        
                        <button type="button" id="clear-archive-filters" class="pilgrim-btn pilgrim-btn-secondary">Clear Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Routes Grid -->
    <section class="archive-content">
        <div class="container">
            <div id="routes-grid" class="sites-grid">
                <?php
                // Get current query parameters for filtering
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                
                $args = array(
                    'post_type' => 'pilgrimage_route',
                    'posts_per_page' => 12,
                    'paged' => $paged,
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC'
                );
                
                // Add tax queries if filters are set
                $tax_query = array();
                
                if (isset($_GET['county']) && !empty($_GET['county'])) {
                    $tax_query[] = array(
                        'taxonomy' => 'county',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['county'])
                    );
                }
                
                if (isset($_GET['difficulty_level']) && !empty($_GET['difficulty_level'])) {
                    $tax_query[] = array(
                        'taxonomy' => 'difficulty_level',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['difficulty_level'])
                    );
                }
                
                if (!empty($tax_query)) {
                    $tax_query['relation'] = 'AND';
                    $args['tax_query'] = $tax_query;
                }
                
                $routes_query = new WP_Query($args);
                
                if ($routes_query->have_posts()) :
                    while ($routes_query->have_posts()) : $routes_query->the_post();
                        $counties = wp_get_post_terms(get_the_ID(), 'county');
                        $difficulties = wp_get_post_terms(get_the_ID(), 'difficulty_level');
                        $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                        $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                        $distance = get_post_meta(get_the_ID(), '_pilgrimirl_distance', true);
                        $pets_allowed = get_post_meta(get_the_ID(), '_pilgrimirl_pets_allowed', true);
                        ?>
                        <div class="site-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="card-image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('site-card-thumb'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-content">
                                <div class="card-meta">
                                    <span class="post-type-label">Pilgrimage Route</span>
                                    <?php if (!empty($counties)) : ?>
                                        <span class="county-label"><?php echo esc_html($counties[0]->name); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                
                                <?php if ($distance) : ?>
                                    <p class="route-distance">Distance: <?php echo esc_html($distance); ?> km</p>
                                <?php endif; ?>
                                
                                <?php if (!empty($difficulties)) : ?>
                                    <p class="difficulty-level">Difficulty: <?php echo esc_html($difficulties[0]->name); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($pets_allowed) : ?>
                                    <p class="pets-allowed">Pets: <?php echo esc_html(ucfirst($pets_allowed)); ?></p>
                                <?php endif; ?>
                                
                                <div class="card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="card-actions">
                                    <a href="<?php the_permalink(); ?>" class="read-more-btn">Learn More</a>
                                    <?php if ($latitude && $longitude) : ?>
                                        <button class="show-on-map-btn" data-lat="<?php echo esc_attr($latitude); ?>" data-lng="<?php echo esc_attr($longitude); ?>" onclick="showOnMap(this)">View on Map</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else :
                    ?>
                    <div class="no-results">
                        <h3>No pilgrimage routes found</h3>
                        <p>Try adjusting your filters or <a href="<?php echo get_post_type_archive_link('pilgrimage_route'); ?>">view all routes</a>.</p>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($routes_query->max_num_pages > 1) : ?>
                <div class="archive-pagination">
                    <?php
                    echo paginate_links(array(
                        'total' => $routes_query->max_num_pages,
                        'current' => $paged,
                        'format' => '?paged=%#%',
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                        'mid_size' => 2
                    ));
                    ?>
                </div>
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
    
</main>

<script>
jQuery(document).ready(function($) {
    // Archive filtering
    $('#archive-county-filter, #archive-difficulty-filter').on('change', function() {
        var county = $('#archive-county-filter').val();
        var difficulty = $('#archive-difficulty-filter').val();
        
        var url = window.location.pathname;
        var params = [];
        
        if (county) params.push('county=' + encodeURIComponent(county));
        if (difficulty) params.push('difficulty_level=' + encodeURIComponent(difficulty));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Clear filters
    $('#clear-archive-filters').on('click', function() {
        window.location.href = window.location.pathname;
    });
});
</script>

<?php get_footer(); ?>
