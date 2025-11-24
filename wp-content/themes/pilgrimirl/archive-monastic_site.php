<?php
/**
 * Archive template for Monastic Sites
 * 
 * Displays all monastic sites with filtering options
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <!-- Archive Header -->
    <section class="archive-header">
        <div class="container">
            <h1 class="archive-title">Monastic Sites</h1>
            <p class="archive-description">Discover Ireland's ancient monasteries and abbeys that shaped the spiritual landscape</p>
            
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
                        
                        <select id="archive-order-filter" class="filter-select">
                            <option value="">Religious Order</option>
                            <?php
                            $orders = get_terms(array(
                                'taxonomy' => 'religious_order',
                                'hide_empty' => true,
                                'orderby' => 'name'
                            ));
                            if ($orders && !is_wp_error($orders)) {
                                foreach ($orders as $order) {
                                    $selected = (isset($_GET['religious_order']) && $_GET['religious_order'] === $order->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($order->slug) . '" ' . $selected . '>' . esc_html($order->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        
                        <select id="archive-period-filter" class="filter-select">
                            <option value="">Historical Period</option>
                            <?php
                            $periods = get_terms(array(
                                'taxonomy' => 'historical_period',
                                'hide_empty' => true,
                                'orderby' => 'name'
                            ));
                            if ($periods && !is_wp_error($periods)) {
                                foreach ($periods as $period) {
                                    $selected = (isset($_GET['historical_period']) && $_GET['historical_period'] === $period->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($period->slug) . '" ' . $selected . '>' . esc_html($period->name) . '</option>';
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
    
    <!-- Sites Grid -->
    <section class="archive-content">
        <div class="container">
            <div id="sites-grid" class="sites-grid">
                <?php
                // Get current query parameters for filtering
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                
                $args = array(
                    'post_type' => 'monastic_site',
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
                
                if (isset($_GET['religious_order']) && !empty($_GET['religious_order'])) {
                    $tax_query[] = array(
                        'taxonomy' => 'religious_order',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['religious_order'])
                    );
                }
                
                if (isset($_GET['historical_period']) && !empty($_GET['historical_period'])) {
                    $tax_query[] = array(
                        'taxonomy' => 'historical_period',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['historical_period'])
                    );
                }
                
                if (!empty($tax_query)) {
                    $tax_query['relation'] = 'AND';
                    $args['tax_query'] = $tax_query;
                }
                
                $sites_query = new WP_Query($args);
                
                if ($sites_query->have_posts()) :
                    while ($sites_query->have_posts()) : $sites_query->the_post();
                        $counties = wp_get_post_terms(get_the_ID(), 'county');
                        $orders = wp_get_post_terms(get_the_ID(), 'religious_order');
                        $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                        $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                        $foundation_date = get_post_meta(get_the_ID(), '_pilgrimirl_foundation_date', true);
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
                                    <span class="post-type-label">Monastic Site</span>
                                    <?php if (!empty($counties)) : ?>
                                        <span class="county-label"><?php echo esc_html($counties[0]->name); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                
                                <?php if ($foundation_date) : ?>
                                    <p class="foundation-date">Founded: <?php echo esc_html($foundation_date); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($orders)) : ?>
                                    <p class="religious-order">Order: <?php echo esc_html($orders[0]->name); ?></p>
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
                        <h3>No monastic sites found</h3>
                        <p>Try adjusting your filters or <a href="<?php echo get_post_type_archive_link('monastic_site'); ?>">view all sites</a>.</p>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($sites_query->max_num_pages > 1) : ?>
                <div class="archive-pagination">
                    <?php
                    echo paginate_links(array(
                        'total' => $sites_query->max_num_pages,
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
    $('#archive-county-filter, #archive-order-filter, #archive-period-filter').on('change', function() {
        var county = $('#archive-county-filter').val();
        var order = $('#archive-order-filter').val();
        var period = $('#archive-period-filter').val();
        
        var url = window.location.pathname;
        var params = [];
        
        if (county) params.push('county=' + encodeURIComponent(county));
        if (order) params.push('religious_order=' + encodeURIComponent(order));
        if (period) params.push('historical_period=' + encodeURIComponent(period));
        
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
