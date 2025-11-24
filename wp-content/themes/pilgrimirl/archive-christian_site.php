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
                        
                        <select id="archive-site-type-filter" class="filter-select">
                            <option value="">All Site Types</option>
                            <?php
                            // Only show High Crosses and Holy Wells
                            $allowed_types = array('high-cross', 'holy-well');
                            $site_types = get_terms(array(
                                'taxonomy' => 'site_type',
                                'hide_empty' => true,
                                'orderby' => 'name',
                                'slug' => $allowed_types
                            ));
                            if ($site_types && !is_wp_error($site_types)) {
                                foreach ($site_types as $type) {
                                    $selected = (isset($_GET['site_type']) && $_GET['site_type'] === $type->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($type->slug) . '" ' . $selected . '>' . esc_html($type->name) . '</option>';
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
    
    <!-- Overview Map Section -->
    <section id="archive-map-section" class="archive-map-section">
        <div class="container">
            <h2>Locations Overview</h2>
            <p>Explore all Christian Sites across Ireland on this interactive map</p>
            <div id="archive-overview-map" class="archive-overview-map"></div>
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

<style>
.archive-header {
    background: linear-gradient(135deg, #2c5530 0%, #4a7c59 100%);
    color: white;
    padding: 3rem 0;
    text-align: center;
}

.archive-title {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.archive-description {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.archive-filters {
    max-width: 800px;
    margin: 0 auto;
}

.filter-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 25px;
    background-color: white;
    color: #2c5530;
    font-size: 0.9rem;
    min-width: 150px;
    cursor: pointer;
}

.filter-select:focus {
    outline: 2px solid #fff;
    outline-offset: 2px;
}

.pilgrim-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.pilgrim-btn-secondary {
    background-color: transparent;
    color: white;
    border: 2px solid white;
}

.pilgrim-btn-secondary:hover {
    background-color: white;
    color: #2c5530;
}

/* Map Section Styles */
.archive-map-section {
    background-color: #f8f9fa;
    padding: 3rem 0;
    border-bottom: 1px solid #e9ecef;
}

.archive-map-section h2 {
    text-align: center;
    color: #2c5530;
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.archive-map-section p {
    text-align: center;
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.archive-overview-map {
    width: 100%;
    height: 500px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
}

.map-error {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.archive-content {
    padding: 3rem 0;
}

.sites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.site-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.site-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.card-image {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.site-card:hover .card-image img {
    transform: scale(1.05);
}

.card-content {
    padding: 1.5rem;
}

.card-meta {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.site-type-label,
.post-type-label,
.county-label {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.site-type-label,
.post-type-label {
    background-color: #2c5530;
    color: white;
}

.county-label {
    background-color: #f0f8f0;
    color: #2c5530;
    border: 1px solid #2c5530;
}

.card-content h3 {
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.card-content h3 a {
    color: #2c5530;
    text-decoration: none;
    transition: color 0.3s ease;
}

.card-content h3 a:hover {
    color: #4a7c59;
}

.foundation-date,
.historical-period,
.site-status {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.card-excerpt {
    color: #555;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.card-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.read-more-btn {
    background-color: #2c5530;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.read-more-btn:hover {
    background-color: #4a7c59;
}

.show-on-map-btn {
    background-color: transparent;
    color: #2c5530;
    border: 1px solid #2c5530;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.show-on-map-btn:hover {
    background-color: #2c5530;
    color: white;
}

.no-results {
    text-align: center;
    padding: 3rem;
    grid-column: 1 / -1;
}

.no-results h3 {
    color: #2c5530;
    margin-bottom: 1rem;
}

.archive-pagination {
    text-align: center;
    margin-top: 2rem;
}

.archive-pagination .page-numbers {
    display: inline-block;
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    background-color: #f8f9fa;
    color: #2c5530;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.archive-pagination .page-numbers:hover,
.archive-pagination .page-numbers.current {
    background-color: #2c5530;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .archive-title {
        font-size: 2rem;
    }
    
    .filter-row {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .filter-select {
        width: 100%;
        max-width: 300px;
    }
    
    .archive-map-section h2 {
        font-size: 2rem;
    }
    
    .archive-overview-map {
        height: 400px;
    }
    
    .sites-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .card-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .read-more-btn,
    .show-on-map-btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Archive filtering
    $('#archive-county-filter, #archive-site-type-filter, #archive-period-filter').on('change', function() {
        var county = $('#archive-county-filter').val();
        var siteType = $('#archive-site-type-filter').val();
        var period = $('#archive-period-filter').val();
        
        var url = window.location.pathname;
        var params = [];
        
        if (county) params.push('county=' + encodeURIComponent(county));
        if (siteType) params.push('site_type=' + encodeURIComponent(siteType));
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
