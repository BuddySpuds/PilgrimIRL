<?php
/**
 * Generic Taxonomy Archive Template
 * Fallback for taxonomy archives (mainly for /county/ overview)
 *
 * For individual county pages, use taxonomy-county.php
 */

get_header();

// Check if this is the county taxonomy archive overview
$is_county_archive = get_query_var('county_archive') == 1;
?>

<main id="main" class="site-main">

    <?php if ($is_county_archive || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/county/') !== false && $_SERVER['REQUEST_URI'] === '/county/')): ?>

        <!-- Counties Overview Header -->
        <section class="archive-header">
            <div class="container">
                <h1 class="archive-title">Explore by County</h1>
                <p class="archive-description">Discover Ireland's sacred heritage across all 32 counties, each offering unique treasures of Christian history and spiritual significance</p>

                <!-- County Stats Summary -->
                <div class="archive-stats">
                    <?php
                    // Get total counts
                    $total_sites = wp_count_posts('monastic_site')->publish +
                                   wp_count_posts('christian_site')->publish;
                    $total_routes = wp_count_posts('pilgrimage_route')->publish;
                    $total_counties = wp_count_terms(array('taxonomy' => 'county', 'hide_empty' => false));
                    ?>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo number_format($total_counties); ?></span>
                        <span class="stat-label">Counties</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo number_format($total_sites); ?></span>
                        <span class="stat-label">Sacred Sites</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo number_format($total_routes); ?></span>
                        <span class="stat-label">Pilgrimage Routes</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Counties Grid -->
        <section class="archive-content counties-overview">
            <div class="container">

                <!-- Quick Search -->
                <div class="county-search-box">
                    <input type="text" id="county-search-input" class="filter-select" placeholder="Search counties..." />
                </div>

                <!-- Counties Grid -->
                <div class="counties-grid">
                    <?php
                    // Get all counties
                    $counties = get_terms(array(
                        'taxonomy' => 'county',
                        'hide_empty' => false,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));

                    if (!empty($counties) && !is_wp_error($counties)) {
                        foreach ($counties as $county) {
                            // Get count for this county
                            $count = $county->count;

                            // Get county description
                            $description = $county->description ? $county->description : 'Discover the sacred heritage of ' . $county->name;
                            ?>
                            <div class="county-card" data-county="<?php echo esc_attr($county->slug); ?>">
                                <div class="county-card-inner">
                                    <h3 class="county-name">
                                        <a href="<?php echo get_term_link($county); ?>"><?php echo esc_html($county->name); ?></a>
                                    </h3>
                                    <div class="county-stats">
                                        <span class="site-count"><?php echo number_format($count); ?> <?php echo _n('site', 'sites', $count, 'pilgrimirl'); ?></span>
                                    </div>
                                    <p class="county-description"><?php echo esc_html($description); ?></p>
                                    <a href="<?php echo get_term_link($county); ?>" class="county-link btn-primary">
                                        Explore <?php echo esc_html($county->name); ?> →
                                    </a>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="no-results">No counties found.</p>';
                    }
                    ?>
                </div>

                <!-- Interactive Map of Ireland -->
                <div class="counties-map-section">
                    <h2>Interactive County Map</h2>
                    <p>Explore Ireland's counties on our interactive map</p>
                    <div id="counties-map" class="archive-map-container" style="height: 600px;">
                        <!-- Map will be initialized by JavaScript -->
                    </div>
                </div>

            </div>
        </section>

        <script>
        jQuery(document).ready(function($) {
            // County search functionality
            $('#county-search-input').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();

                $('.county-card').each(function() {
                    var countyName = $(this).data('county').toLowerCase();
                    var countyText = $(this).find('.county-name').text().toLowerCase();

                    if (countyName.indexOf(searchTerm) > -1 || countyText.indexOf(searchTerm) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // Show message if no results
                var visibleCards = $('.county-card:visible').length;
                if (visibleCards === 0 && searchTerm !== '') {
                    if ($('.no-search-results').length === 0) {
                        $('.counties-grid').append('<p class="no-search-results empty-state"><strong>No counties found</strong><br>Try a different search term</p>');
                    }
                } else {
                    $('.no-search-results').remove();
                }
            });
        });
        </script>

        <style>
        /* Counties Overview Specific Styles */
        .counties-overview {
            padding: 3rem 0;
        }

        .county-search-box {
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        .county-search-box input {
            width: 100%;
            padding: 1rem 1.5rem;
            font-size: 1.125rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-lg);
            transition: all 0.2s ease;
        }

        .county-search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(45, 80, 22, 0.1);
        }

        .counties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .county-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }

        .county-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .county-name {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            margin: 0 0 1rem;
        }

        .county-name a {
            color: var(--gray-900);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .county-name a:hover {
            color: var(--primary-color);
        }

        .county-stats {
            margin-bottom: 1rem;
        }

        .site-count {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            background: rgba(45, 80, 22, 0.1);
            color: var(--primary-color);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .county-description {
            font-size: 0.9375rem;
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .county-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
        }

        .county-link:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .counties-map-section {
            margin-top: 4rem;
            text-align: center;
        }

        .counties-map-section h2 {
            font-family: var(--font-heading);
            font-size: 2.25rem;
            margin-bottom: 1rem;
            color: var(--gray-900);
        }

        .counties-map-section p {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
        }

        .no-search-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            font-size: 1.125rem;
            color: var(--gray-600);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .counties-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .archive-stats {
                grid-template-columns: 1fr;
            }
        }
        </style>

    <?php else: ?>

        <!-- Default taxonomy archive (shouldn't usually hit this) -->
        <section class="archive-header">
            <div class="container">
                <h1 class="archive-title"><?php single_term_title(); ?></h1>
                <?php
                $term_description = term_description();
                if (!empty($term_description)) {
                    echo '<div class="archive-description">' . $term_description . '</div>';
                }
                ?>
            </div>
        </section>

        <section class="archive-content">
            <div class="container">
                <?php if (have_posts()): ?>
                    <div class="sites-grid">
                        <?php while (have_posts()): the_post(); ?>
                            <div class="site-card">
                                <?php get_template_part('template-parts/content', 'archive'); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php the_posts_pagination(); ?>
                <?php else: ?>
                    <p class="no-results">No posts found.</p>
                <?php endif; ?>
            </div>
        </section>

    <?php endif; ?>

</main>

<?php get_footer(); ?>
