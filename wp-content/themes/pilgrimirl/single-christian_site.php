<?php
/**
 * Single Christian Site Template
 * 
 * Template for displaying individual Christian heritage sites
 * including Holy Wells, High Crosses, Mass Rocks, and Ruins
 */

get_header(); ?>

<div class="container">
    <?php while (have_posts()) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-christian-site'); ?>>
            
            <!-- Site Header -->
            <header class="site-header">
                <div class="site-meta">
                    <?php
                    $counties = wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'names'));
                    $site_types = wp_get_post_terms(get_the_ID(), 'site_type', array('fields' => 'names'));
                    ?>
                    
                    <div class="breadcrumbs">
                        <a href="<?php echo home_url(); ?>">Home</a> &raquo; 
                        <a href="<?php echo get_post_type_archive_link('christian_site'); ?>">Christian Sites</a>
                        <?php if (!empty($counties)): ?>
                            &raquo; <a href="<?php echo get_term_link(get_term_by('name', $counties[0], 'county')); ?>"><?php echo $counties[0]; ?></a>
                        <?php endif; ?>
                        &raquo; <?php the_title(); ?>
                    </div>
                    
                    <div class="site-badges">
                        <?php if (!empty($site_types)): ?>
                            <span class="site-type-badge"><?php echo $site_types[0]; ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($counties)): ?>
                            <span class="county-badge"><?php echo $counties[0]; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h1 class="site-title"><?php the_title(); ?></h1>
                
                <?php
                $alternative_names = get_post_meta(get_the_ID(), '_pilgrimirl_alternative_names', true);
                if ($alternative_names): ?>
                    <div class="alternative-names">
                        <strong>Also known as:</strong> <?php echo nl2br(esc_html($alternative_names)); ?>
                    </div>
                <?php endif; ?>
            </header>
            
            <!-- Featured Image -->
            <?php if (has_post_thumbnail()): ?>
                <div class="site-featured-image">
                    <?php the_post_thumbnail('large', array('class' => 'img-responsive')); ?>
                </div>
            <?php endif; ?>
            
            <!-- Site Content Grid -->
            <div class="site-content-grid">
                
                <!-- Main Content -->
                <div class="main-content">
                    <div class="site-description">
                        <?php the_content(); ?>
                    </div>
                    
                    <!-- Historical Information -->
                    <?php
                    $foundation_date = get_post_meta(get_the_ID(), '_pilgrimirl_foundation_date', true);
                    $dissolution_date = get_post_meta(get_the_ID(), '_pilgrimirl_dissolution_date', true);
                    $communities_provenance = get_post_meta(get_the_ID(), '_pilgrimirl_communities_provenance', true);
                    
                    if ($foundation_date || $dissolution_date || $communities_provenance): ?>
                        <div class="historical-info">
                            <h3>Historical Information</h3>
                            
                            <?php if ($foundation_date): ?>
                                <div class="info-item">
                                    <strong>Foundation Date:</strong> <?php echo esc_html($foundation_date); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($dissolution_date): ?>
                                <div class="info-item">
                                    <strong>Dissolution Date:</strong> <?php echo esc_html($dissolution_date); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($communities_provenance): ?>
                                <div class="info-item">
                                    <strong>Communities & Provenance:</strong>
                                    <div class="communities-text"><?php echo nl2br(esc_html($communities_provenance)); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar Information -->
                <div class="site-sidebar">
                    
                    <!-- Location Information -->
                    <?php
                    $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
                    $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);
                    $address = get_post_meta(get_the_ID(), '_pilgrimirl_address', true);
                    ?>
                    
                    <div class="location-info">
                        <h4>Location Details</h4>
                        
                        <?php if ($address): ?>
                            <div class="address">
                                <strong>Address:</strong><br>
                                <?php echo esc_html($address); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($latitude && $longitude): ?>
                            <div class="coordinates">
                                <strong>Coordinates:</strong><br>
                                Lat: <?php echo esc_html($latitude); ?><br>
                                Lng: <?php echo esc_html($longitude); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Taxonomies -->
                    <div class="site-taxonomies">
                        
                        <!-- Site Type -->
                        <?php if (!empty($site_types)): ?>
                            <div class="taxonomy-group">
                                <h5>Site Type</h5>
                                <ul class="taxonomy-list">
                                    <?php foreach ($site_types as $type): ?>
                                        <li><?php echo esc_html($type); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Religious Orders -->
                        <?php
                        $religious_orders = wp_get_post_terms(get_the_ID(), 'religious_order', array('fields' => 'names'));
                        if (!empty($religious_orders)): ?>
                            <div class="taxonomy-group">
                                <h5>Religious Orders</h5>
                                <ul class="taxonomy-list">
                                    <?php foreach ($religious_orders as $order): ?>
                                        <li><?php echo esc_html($order); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Historical Periods -->
                        <?php
                        $historical_periods = wp_get_post_terms(get_the_ID(), 'historical_period', array('fields' => 'names'));
                        if (!empty($historical_periods)): ?>
                            <div class="taxonomy-group">
                                <h5>Historical Periods</h5>
                                <ul class="taxonomy-list">
                                    <?php foreach ($historical_periods as $period): ?>
                                        <li><?php echo esc_html($period); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Associated Saints -->
                        <?php
                        $saints = wp_get_post_terms(get_the_ID(), 'associated_saints', array('fields' => 'names'));
                        if (!empty($saints)): ?>
                            <div class="taxonomy-group">
                                <h5>Associated Saints</h5>
                                <ul class="taxonomy-list">
                                    <?php foreach ($saints as $saint): ?>
                                        <li><?php echo esc_html($saint); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Site Status -->
                        <?php
                        $site_status = wp_get_post_terms(get_the_ID(), 'site_status', array('fields' => 'names'));
                        if (!empty($site_status)): ?>
                            <div class="taxonomy-group">
                                <h5>Site Status</h5>
                                <ul class="taxonomy-list">
                                    <?php foreach ($site_status as $status): ?>
                                        <li><?php echo esc_html($status); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Centuries -->
                        <?php
                        $centuries = wp_get_post_terms(get_the_ID(), 'century', array('fields' => 'names'));
                        if (!empty($centuries)): ?>
                            <div class="taxonomy-group">
                                <h5>Historical Periods</h5>
                                <ul class="taxonomy-list">
                                    <?php foreach ($centuries as $century): ?>
                                        <li><?php echo esc_html($century); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Map Section -->
            <?php if ($latitude && $longitude): ?>
                <div class="site-map-section">
                    <h3>Location Map</h3>
                    <div id="single-site-map" class="single-site-map" 
                         data-lat="<?php echo esc_attr($latitude); ?>" 
                         data-lng="<?php echo esc_attr($longitude); ?>"
                         data-title="<?php echo esc_attr(get_the_title()); ?>"
                         data-address="<?php echo esc_attr($address); ?>">
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Related Sites -->
            <?php
            $related_args = array(
                'post_type' => 'christian_site',
                'posts_per_page' => 4,
                'post__not_in' => array(get_the_ID()),
                'orderby' => 'rand'
            );
            
            // Try to get sites from the same county first
            if (!empty($counties)) {
                $related_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'county',
                        'field' => 'name',
                        'terms' => $counties[0]
                    )
                );
            }
            
            $related_sites = new WP_Query($related_args);
            
            if ($related_sites->have_posts()): ?>
                <div class="related-sites">
                    <h3>Related Christian Sites</h3>
                    <div class="related-sites-grid">
                        <?php while ($related_sites->have_posts()): $related_sites->the_post(); ?>
                            <div class="related-site-card">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="card-image">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('site-card-thumb'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-content">
                                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                    
                                    <?php
                                    $card_counties = wp_get_post_terms(get_the_ID(), 'county', array('fields' => 'names'));
                                    $card_types = wp_get_post_terms(get_the_ID(), 'site_type', array('fields' => 'names'));
                                    ?>
                                    
                                    <div class="card-meta">
                                        <?php if (!empty($card_types)): ?>
                                            <span class="site-type"><?php echo $card_types[0]; ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($card_counties)): ?>
                                            <span class="county"><?php echo $card_counties[0]; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
            
        </article>
        
    <?php endwhile; ?>
</div>

<style>
.single-christian-site {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.site-header {
    margin-bottom: 2rem;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 1rem;
}

.breadcrumbs {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
}

.breadcrumbs a {
    color: #2c5530;
    text-decoration: none;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

.site-badges {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.site-type-badge,
.county-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.site-type-badge {
    background-color: #2c5530;
    color: white;
}

.county-badge {
    background-color: #f0f8f0;
    color: #2c5530;
    border: 1px solid #2c5530;
}

.site-title {
    font-size: 2.5rem;
    color: #2c5530;
    margin-bottom: 0.5rem;
}

.alternative-names {
    font-style: italic;
    color: #666;
    margin-top: 0.5rem;
}

.site-featured-image {
    margin: 2rem 0;
    text-align: center;
}

.site-featured-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.site-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    margin: 2rem 0;
}

.main-content {
    font-size: 1.1rem;
    line-height: 1.7;
}

.historical-info {
    background-color: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 2rem;
}

.historical-info h3 {
    color: #2c5530;
    margin-bottom: 1rem;
}

.info-item {
    margin-bottom: 1rem;
}

.communities-text {
    margin-top: 0.5rem;
    padding-left: 1rem;
}

.site-sidebar {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    height: fit-content;
}

.location-info,
.site-taxonomies {
    margin-bottom: 2rem;
}

.location-info h4,
.taxonomy-group h5 {
    color: #2c5530;
    margin-bottom: 1rem;
}

.address,
.coordinates {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background-color: white;
    border-radius: 4px;
}

.taxonomy-group {
    margin-bottom: 1.5rem;
}

.taxonomy-list {
    list-style: none;
    padding: 0;
}

.taxonomy-list li {
    padding: 0.5rem;
    background-color: white;
    margin-bottom: 0.25rem;
    border-radius: 4px;
    border-left: 3px solid #2c5530;
}

.site-map-section {
    margin: 3rem 0;
}

.site-map-section h3 {
    color: #2c5530;
    margin-bottom: 1rem;
}

.single-site-map {
    height: 400px;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.related-sites {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #e0e0e0;
}

.related-sites h3 {
    color: #2c5530;
    margin-bottom: 1.5rem;
}

.related-sites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.related-site-card {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.related-site-card:hover {
    transform: translateY(-2px);
}

.card-image img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.card-content {
    padding: 1rem;
}

.card-content h4 {
    margin-bottom: 0.5rem;
}

.card-content h4 a {
    color: #2c5530;
    text-decoration: none;
}

.card-content h4 a:hover {
    text-decoration: underline;
}

.card-meta {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.card-meta .site-type,
.card-meta .county {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
}

.card-meta .site-type {
    background-color: #2c5530;
    color: white;
}

.card-meta .county {
    background-color: #f0f8f0;
    color: #2c5530;
}

.card-excerpt {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .single-christian-site {
        padding: 1rem;
    }
    
    .site-title {
        font-size: 2rem;
    }
    
    .site-content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .related-sites-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Map functionality is handled by maps.js -->

<?php get_footer(); ?>
