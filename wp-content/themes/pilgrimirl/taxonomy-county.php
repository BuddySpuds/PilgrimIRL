<?php
/**
 * County Taxonomy Archive Template
 * 
 * Displays all sites and routes for a specific county
 */

get_header();

$current_term = get_queried_object();
$county_name = $current_term->name;
$county_description = $current_term->description;
?>

<main id="main" class="site-main county-archive">
    
    <!-- County Header -->
    <section class="county-header">
        <div class="container">
            <div class="county-header-content">
                <h1 class="county-title">County <?php echo esc_html($county_name); ?></h1>
                <?php if ($county_description) : ?>
                    <p class="county-description"><?php echo esc_html($county_description); ?></p>
                <?php else : ?>
                    <p class="county-description">Discover the sacred heritage of County <?php echo esc_html($county_name); ?></p>
                <?php endif; ?>
                
                <!-- County Stats -->
                <div class="county-stats">
                    <?php
                    $monastic_count = get_posts(array(
                        'post_type' => 'monastic_site',
                        'posts_per_page' => -1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'county',
                                'field' => 'term_id',
                                'terms' => $current_term->term_id
                            )
                        ),
                        'fields' => 'ids'
                    ));
                    
                    $route_count = get_posts(array(
                        'post_type' => 'pilgrimage_route',
                        'posts_per_page' => -1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'county',
                                'field' => 'term_id',
                                'terms' => $current_term->term_id
                            )
                        ),
                        'fields' => 'ids'
                    ));
                    
                    $ruin_count = get_posts(array(
                        'post_type' => 'christian_ruin',
                        'posts_per_page' => -1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'county',
                                'field' => 'term_id',
                                'terms' => $current_term->term_id
                            )
                        ),
                        'fields' => 'ids'
                    ));
                    ?>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($monastic_count); ?></span>
                        <span class="stat-label">Monastic Sites</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($route_count); ?></span>
                        <span class="stat-label">Pilgrimage Routes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($ruin_count); ?></span>
                        <span class="stat-label">Christian Ruins</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Content Filters -->
    <section class="county-filters">
        <div class="container">
            <div class="filter-tabs">
                <button class="filter-tab active" data-type="all">All Sites</button>
                <button class="filter-tab" data-type="monastic_site">Monastic Sites</button>
                <button class="filter-tab" data-type="pilgrimage_route">Pilgrimage Routes</button>
                <button class="filter-tab" data-type="christian_ruin">Christian Ruins</button>
            </div>
        </div>
    </section>
    
    <!-- County Content -->
    <section class="county-content">
        <div class="container">
            <div id="county-sites-grid" class="sites-grid">
                <?php
                // Get all content for this county
                $args = array(
                    'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_ruin'),
                    'posts_per_page' => 20,
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'county',
                            'field' => 'term_id',
                            'terms' => $current_term->term_id
                        )
                    )
                );
                
                $county_query = new WP_Query($args);
                
                if ($county_query->have_posts()) :
                    while ($county_query->have_posts()) : $county_query->the_post();
                        $post_type = get_post_type();
                        $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                        $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                        $foundation_date = get_post_meta(get_the_ID(), '_pilgrimirl_foundation_date', true);
                        $distance = get_post_meta(get_the_ID(), '_pilgrimirl_distance', true);
                        
                        // Get post type label
                        $post_type_labels = array(
                            'monastic_site' => 'Monastic Site',
                            'pilgrimage_route' => 'Pilgrimage Route',
                            'christian_ruin' => 'Christian Ruin'
                        );
                        $post_type_label = $post_type_labels[$post_type] ?? $post_type;
                        ?>
                        <div class="site-card" data-type="<?php echo esc_attr($post_type); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="card-image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('site-card-thumb'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-content">
                                <div class="card-meta">
                                    <span class="post-type-label"><?php echo esc_html($post_type_label); ?></span>
                                    <span class="county-label"><?php echo esc_html($county_name); ?></span>
                                </div>
                                
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                
                                <?php if ($foundation_date) : ?>
                                    <p class="foundation-date">Founded: <?php echo esc_html($foundation_date); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($distance && $post_type === 'pilgrimage_route') : ?>
                                    <p class="route-distance">Distance: <?php echo esc_html($distance); ?> km</p>
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
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="no-results">
                        <h3>No sites found in County <?php echo esc_html($county_name); ?></h3>
                        <p>Check back later as we continue to add more content.</p>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            
            <!-- County Map Section -->
            <div class="county-map-section">
                <h2>County <?php echo esc_html($county_name); ?> Map</h2>
                <div id="county-map" class="county-map-container" data-county="<?php echo esc_attr($current_term->slug); ?>">
                    <!-- Map will be initialized by JavaScript -->
                </div>
            </div>
        </div>
    </section>
    
</main>

<script>
jQuery(document).ready(function($) {
    // Filter tabs functionality
    $('.filter-tab').on('click', function() {
        const filterType = $(this).data('type');
        
        // Update active tab
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        
        // Filter cards
        if (filterType === 'all') {
            $('.site-card').show();
        } else {
            $('.site-card').hide();
            $('.site-card[data-type="' + filterType + '"]').show();
        }
    });
});
</script>

<?php get_footer(); ?>
