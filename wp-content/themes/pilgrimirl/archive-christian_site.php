<?php
/**
 * Archive template for Christian Sites
 * 
 * Displays all Christian heritage sites including Holy Wells, High Crosses, Mass Rocks, and Ruins
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <!-- Archive Header -->
    <section class="archive-header">
        <div class="container">
            <h1 class="archive-title">Christian Sites</h1>
            <p class="archive-description">Explore Ireland's sacred Christian heritage sites including Holy Wells and High Crosses</p>
            
            <!-- Archive Filters -->
            <div class="archive-filters">
                <form id="archive-filter-form" class="filter-form">
                    <div class="filter-row">
                        <select id="archive-county-filter" class="filter-select">
                            <option value="">All Counties</option>
                            <?php
                            // Get counties that have christian_site posts
                            global $wpdb;
                            $counties_with_sites = $wpdb->get_col("
                                SELECT DISTINCT t.slug
                                FROM {$wpdb->terms} t
                                JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                                JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                                WHERE tt.taxonomy = 'county'
                                AND p.post_type = 'christian_site'
                                AND p.post_status = 'publish'
                            ");

                            $counties = get_terms(array(
                                'taxonomy' => 'county',
                                'hide_empty' => true,
                                'orderby' => 'name',
                                'slug' => $counties_with_sites
                            ));
                            if ($counties && !is_wp_error($counties)) {
                                foreach ($counties as $county) {
                                    $selected = (isset($_GET['county']) && $_GET['county'] === $county->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($county->slug) . '" ' . $selected . '>' . esc_html($county->name) . '</option>';
                                }
                            }
                            ?>
                        </select>

                        <select id="archive-site-type-filter" class="filter-select">
                            <option value="">All Site Types</option>
                            <?php
                            // Show all site types for christian_site post type (not just high-cross and holy-well)
                            $site_types = get_terms(array(
                                'taxonomy' => 'site_type',
                                'hide_empty' => true,
                                'orderby' => 'name'
                            ));
                            if ($site_types && !is_wp_error($site_types)) {
                                foreach ($site_types as $type) {
                                    $selected = (isset($_GET['site_type']) && $_GET['site_type'] === $type->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($type->slug) . '" ' . $selected . '>' . esc_html($type->name) . '</option>';
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
    
    <!-- Overview Map Section -->
    <section id="archive-map-section" class="archive-map-section">
        <div class="container">
            <h2>Locations Overview</h2>
            <p>Explore all Christian Sites across Ireland on this interactive map</p>
            <div id="archive-overview-map" class="archive-overview-map"
                 data-post-type="christian_site"
                 data-county="<?php echo isset($_GET['county']) ? esc_attr($_GET['county']) : ''; ?>"
                 data-site-type="<?php echo isset($_GET['site_type']) ? esc_attr($_GET['site_type']) : ''; ?>"></div>
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
                    'post_type' => 'christian_site',
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

                if (isset($_GET['site_type']) && !empty($_GET['site_type'])) {
                    $tax_query[] = array(
                        'taxonomy' => 'site_type',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($_GET['site_type'])
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
                        $site_types = wp_get_post_terms(get_the_ID(), 'site_type');
                        $periods = wp_get_post_terms(get_the_ID(), 'historical_period');
                        $statuses = wp_get_post_terms(get_the_ID(), 'site_status');
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
                                    <?php if (!empty($site_types)) : ?>
                                        <span class="site-type-label"><?php echo esc_html($site_types[0]->name); ?></span>
                                    <?php else : ?>
                                        <span class="post-type-label">Christian Site</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($counties)) : ?>
                                        <span class="county-label"><?php echo esc_html($counties[0]->name); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                
                                <?php if ($foundation_date) : ?>
                                    <p class="foundation-date">Founded: <?php echo esc_html($foundation_date); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($periods)) : ?>
                                    <p class="historical-period">Period: <?php echo esc_html($periods[0]->name); ?></p>
                                <?php endif; ?>
                                
                                <div class="card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="card-actions">
                                    <a href="<?php the_permalink(); ?>" class="read-more-btn">Learn More</a>
                                    <?php if ($latitude && $longitude) : ?>
                                        <button class="show-on-map-btn" data-lat="<?php echo esc_attr($latitude); ?>" data-lng="<?php echo esc_attr($longitude); ?>" onclick="showSiteOnArchiveMap('<?php echo esc_attr($latitude); ?>', '<?php echo esc_attr($longitude); ?>', '<?php echo esc_attr(get_the_title()); ?>')">View on Map</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else :
                    ?>
                    <div class="no-results">
                        <h3>No Christian sites found</h3>
                        <p>Try adjusting your filters or <a href="<?php echo get_post_type_archive_link('christian_site'); ?>">view all sites</a>.</p>
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
    // Archive filtering (simplified - county and site type only)
    $('#archive-county-filter, #archive-site-type-filter').on('change', function() {
        var county = $('#archive-county-filter').val();
        var siteType = $('#archive-site-type-filter').val();

        var url = window.location.pathname;
        var params = [];

        if (county) params.push('county=' + encodeURIComponent(county));
        if (siteType) params.push('site_type=' + encodeURIComponent(siteType));

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
