<?php
/**
 * Single Monastic Site Template
 * 
 * Template for displaying individual monastic sites
 */

get_header(); ?>

<main id="main" class="site-main single-site-main">
    
    <?php while (have_posts()) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-site-article'); ?>>
            
            <!-- Site Header -->
            <header class="site-entry-header">
                <div class="container">
                    <div class="site-header-content">
                        <div class="site-meta-info">
                            <?php
                            $counties = wp_get_post_terms(get_the_ID(), 'county');
                            $religious_orders = wp_get_post_terms(get_the_ID(), 'religious_order');
                            $historical_periods = wp_get_post_terms(get_the_ID(), 'historical_period');
                            $site_status = wp_get_post_terms(get_the_ID(), 'site_status');
                            ?>
                            
                            <div class="site-breadcrumbs">
                                <a href="<?php echo home_url(); ?>">Home</a> &raquo; 
                                <a href="<?php echo get_post_type_archive_link('monastic_site'); ?>">Monastic Sites</a> &raquo;
                                <?php if (!empty($counties)) : ?>
                                    <a href="<?php echo get_term_link($counties[0]); ?>"><?php echo esc_html($counties[0]->name); ?></a> &raquo;
                                <?php endif; ?>
                                <span><?php the_title(); ?></span>
                            </div>
                            
                            <h1 class="site-entry-title"><?php the_title(); ?></h1>
                            
                            <div class="site-taxonomy-tags">
                                <?php if (!empty($counties)) : ?>
                                    <span class="site-tag county-tag">📍 <?php echo esc_html($counties[0]->name); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($religious_orders)) : ?>
                                    <span class="site-tag order-tag">⛪ <?php echo esc_html($religious_orders[0]->name); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($historical_periods)) : ?>
                                    <span class="site-tag period-tag">📅 <?php echo esc_html($historical_periods[0]->name); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($site_status)) : ?>
                                    <span class="site-tag status-tag">🏛️ <?php echo esc_html($site_status[0]->name); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="site-featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>
            
            <!-- Site Content -->
            <div class="site-entry-content">
                <div class="container">
                    <div class="site-content-grid">
                        
                        <!-- Main Content -->
                        <div class="site-main-content">
                            
                            <!-- Site Details -->
                            <section class="site-details-section">
                                <h2>About This Site</h2>
                                
                                <div class="site-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="site-content">
                                    <?php the_content(); ?>
                                </div>
                            </section>
                            
                            <!-- Historical Information -->
                            <?php
                            $foundation_date = get_post_meta(get_the_ID(), '_pilgrimirl_foundation_date', true);
                            $dissolution_date = get_post_meta(get_the_ID(), '_pilgrimirl_dissolution_date', true);
                            $communities_provenance = get_post_meta(get_the_ID(), '_pilgrimirl_communities_provenance', true);
                            $associated_saints = get_post_meta(get_the_ID(), '_pilgrimirl_associated_saints', true);
                            $alternative_names = get_post_meta(get_the_ID(), '_pilgrimirl_alternative_names', true);
                            
                            if ($foundation_date || $dissolution_date || $communities_provenance || $associated_saints || $alternative_names) :
                            ?>
                            <section class="historical-info-section">
                                <h2>Historical Information</h2>
                                
                                <div class="historical-details">
                                    <?php if ($foundation_date) : ?>
                                        <div class="historical-item">
                                            <strong>Founded:</strong> <?php echo esc_html($foundation_date); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($dissolution_date) : ?>
                                        <div class="historical-item">
                                            <strong>Dissolved:</strong> <?php echo esc_html($dissolution_date); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($associated_saints) : ?>
                                        <div class="historical-item">
                                            <strong>Associated Saints:</strong> <?php echo esc_html($associated_saints); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($alternative_names) : ?>
                                        <div class="historical-item">
                                            <strong>Alternative Names:</strong>
                                            <ul class="alternative-names-list">
                                                <?php
                                                $names = explode("\n", $alternative_names);
                                                foreach ($names as $name) {
                                                    if (trim($name)) {
                                                        echo '<li>' . esc_html(trim($name)) . '</li>';
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($communities_provenance) : ?>
                                        <div class="historical-item communities-section">
                                            <strong>Communities & Provenance:</strong>
                                            <p><?php echo esc_html($communities_provenance); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </section>
                            <?php endif; ?>
                            
                            <!-- Related Sites -->
                            <?php
                            $related_sites = new WP_Query(array(
                                'post_type' => array('monastic_site', 'christian_ruin'),
                                'posts_per_page' => 4,
                                'post__not_in' => array(get_the_ID()),
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'county',
                                        'field' => 'term_id',
                                        'terms' => wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'ids'))
                                    )
                                )
                            ));
                            
                            if ($related_sites->have_posts()) :
                            ?>
                            <section class="related-sites-section">
                                <h2>Other Sites in <?php echo !empty($counties) ? esc_html($counties[0]->name) : 'This Area'; ?></h2>
                                
                                <div class="related-sites-grid">
                                    <?php while ($related_sites->have_posts()) : $related_sites->the_post(); ?>
                                        <div class="site-card related-site-card">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="card-image">
                                                    <a href="<?php the_permalink(); ?>">
                                                        <?php the_post_thumbnail('site-card-thumb'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-content">
                                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                                <div class="card-excerpt">
                                                    <?php the_excerpt(); ?>
                                                </div>
                                                <a href="<?php the_permalink(); ?>" class="read-more-btn">Learn More</a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </section>
                            <?php
                            wp_reset_postdata();
                            endif;
                            ?>
                        </div>
                        
                        <!-- Sidebar -->
                        <aside class="site-sidebar">
                            
                            <!-- Location & Map -->
                            <?php
                            $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                            $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                            $address = get_post_meta(get_the_ID(), '_pilgrimirl_address', true);
                            
                            if ($latitude && $longitude) :
                            ?>
                            <div class="location-widget">
                                <h3>Location</h3>
                                
                                <?php if ($address) : ?>
                                    <div class="site-address">
                                        <strong>Address:</strong><br>
                                        <?php echo esc_html($address); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="site-coordinates">
                                    <strong>Coordinates:</strong><br>
                                    <?php echo esc_html($latitude); ?>, <?php echo esc_html($longitude); ?>
                                </div>
                                
                                <div class="pilgrim-site-map pilgrim-map-container" 
                                     data-lat="<?php echo esc_attr($latitude); ?>" 
                                     data-lng="<?php echo esc_attr($longitude); ?>"
                                     data-title="<?php echo esc_attr(get_the_title()); ?>">
                                    <!-- Map will be initialized by JavaScript -->
                                </div>
                                
                                <div class="map-actions">
                                    <a href="https://www.google.com/maps?q=<?php echo esc_attr($latitude); ?>,<?php echo esc_attr($longitude); ?>" 
                                       target="_blank" class="pilgrim-btn pilgrim-btn-secondary">
                                        View in Google Maps
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Quick Facts -->
                            <div class="quick-facts-widget">
                                <h3>Quick Facts</h3>
                                
                                <div class="fact-list">
                                    <?php if (!empty($counties)) : ?>
                                        <div class="fact-item">
                                            <strong>County:</strong> <?php echo esc_html($counties[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($religious_orders)) : ?>
                                        <div class="fact-item">
                                            <strong>Religious Order:</strong> <?php echo esc_html($religious_orders[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($historical_periods)) : ?>
                                        <div class="fact-item">
                                            <strong>Period:</strong> <?php echo esc_html($historical_periods[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($site_status)) : ?>
                                        <div class="fact-item">
                                            <strong>Status:</strong> <?php echo esc_html($site_status[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($foundation_date) : ?>
                                        <div class="fact-item">
                                            <strong>Founded:</strong> <?php echo esc_html($foundation_date); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Share Widget -->
                            <div class="share-widget">
                                <h3>Share This Site</h3>
                                
                                <div class="share-buttons">
                                    <button class="share-btn pilgrim-btn" data-url="<?php echo get_permalink(); ?>" data-title="<?php echo get_the_title(); ?>">
                                        📤 Share
                                    </button>
                                    <button class="print-page-btn pilgrim-btn pilgrim-btn-secondary">
                                        🖨️ Print
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Navigation -->
                            <div class="site-navigation-widget">
                                <h3>Explore More</h3>
                                
                                <div class="nav-links">
                                    <a href="<?php echo get_post_type_archive_link('monastic_site'); ?>" class="nav-link">
                                        🏛️ All Monastic Sites
                                    </a>
                                    
                                    <?php if (!empty($counties)) : ?>
                                        <a href="<?php echo get_term_link($counties[0]); ?>" class="nav-link">
                                            📍 More in <?php echo esc_html($counties[0]->name); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo get_post_type_archive_link('pilgrimage_route'); ?>" class="nav-link">
                                        🚶‍♂️ Pilgrimage Routes
                                    </a>
                                    
                                    <a href="<?php echo get_post_type_archive_link('christian_ruin'); ?>" class="nav-link">
                                        ⛪ Christian Ruins
                                    </a>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
            
        </article>
        
    <?php endwhile; ?>
    
</main>

<?php get_footer(); ?>
