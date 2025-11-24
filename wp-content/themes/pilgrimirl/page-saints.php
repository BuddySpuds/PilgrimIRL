<?php
/**
 * Template Name: Saints Overview
 *
 * Enhanced template for browsing and filtering Irish saints and their associated sites
 * Features: Featured saints grid, dual filtering (saint + location), interactive map
 */

get_header();

// Get all saints data for initial load
$saints_data = pilgrimirl_get_saints_with_metadata();
$total_saints = count($saints_data);

// Get counties for location filter
$counties = pilgrimirl_get_irish_counties();

// Calculate total sites with saint associations
$total_sites_with_saints = 0;
foreach ($saints_data as $saint) {
    $total_sites_with_saints += $saint['site_count'];
}
?>

<main id="main" class="site-main saints-page">

    <!-- Hero Section -->
    <section class="saints-hero">
        <div class="container">
            <div class="saints-hero-content">
                <h1 class="saints-hero-title">Discover Ireland's Saints</h1>
                <p class="saints-hero-subtitle">Explore the holy men and women who shaped Ireland's sacred landscape. Filter by saint or location to find pilgrimage sites across the Emerald Isle.</p>

                <div class="saints-hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $total_saints; ?></span>
                        <span class="stat-label">Saints</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $total_sites_with_saints; ?></span>
                        <span class="stat-label">Associated Sites</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">32</span>
                        <span class="stat-label">Counties</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- A-Z Quick Navigation -->
    <section class="saints-az-nav">
        <div class="container">
            <div class="az-nav-wrapper">
                <span class="az-label">Quick Jump:</span>
                <div class="az-letters" id="az-letters">
                    <?php
                    foreach (range('A', 'Z') as $letter) {
                        echo '<button class="az-letter" data-letter="' . $letter . '">' . $letter . '</button>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Saints Section -->
    <section class="featured-saints-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Saints</h2>
                <p class="section-subtitle">The most prominent saints associated with pilgrimage sites across Ireland</p>
            </div>

            <div class="featured-saints-grid" id="featured-saints-grid">
                <?php
                // Display top 12 saints
                $featured_saints = array_slice($saints_data, 0, 12);
                foreach ($featured_saints as $saint) :
                    $saint_initials = strtoupper(substr(str_replace('St. ', '', $saint['name']), 0, 2));
                ?>
                <div class="saint-card" data-saint="<?php echo esc_attr($saint['slug']); ?>">
                    <div class="saint-card-icon">
                        <span class="saint-initials"><?php echo esc_html($saint_initials); ?></span>
                    </div>
                    <div class="saint-card-content">
                        <h3 class="saint-card-name"><?php echo esc_html($saint['name']); ?></h3>
                        <div class="saint-card-meta">
                            <span class="site-count"><?php echo $saint['site_count']; ?> sites</span>
                            <?php if (!empty($saint['counties'])) : ?>
                            <span class="county-list"><?php echo esc_html(implode(', ', array_slice($saint['counties'], 0, 3))); ?><?php echo count($saint['counties']) > 3 ? '...' : ''; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="saint-card-actions">
                        <button class="saint-select-btn" data-saint="<?php echo esc_attr($saint['slug']); ?>">
                            View Sites
                        </button>
                        <button class="saint-map-btn" data-saint="<?php echo esc_attr($saint['slug']); ?>">
                            Show on Map
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="show-all-saints">
                <button class="show-all-btn" id="show-all-saints-btn">
                    View All <?php echo $total_saints; ?> Saints
                    <span class="arrow">↓</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Advanced Filters Section -->
    <section class="saints-filters-section" id="saints-filters-section">
        <div class="container">
            <div class="filters-wrapper">
                <div class="section-header">
                    <h2 class="section-title">Filter Sites by Saint & Location</h2>
                    <p class="section-subtitle">Select a saint and/or county to find specific pilgrimage sites</p>
                </div>

                <div class="dual-filters">
                    <!-- Saint Selection -->
                    <div class="filter-column saint-filter-column">
                        <h3 class="filter-column-title">Select Saint</h3>

                        <div class="saint-search-wrapper">
                            <input type="text" id="saint-search" class="saint-search-input" placeholder="Search saints...">
                            <span class="search-icon">🔍</span>
                        </div>

                        <div class="saint-dropdown-wrapper">
                            <select id="saints-filter-select" class="saints-filter-select">
                                <option value="">All Saints</option>
                                <?php foreach ($saints_data as $saint) : ?>
                                <option value="<?php echo esc_attr($saint['slug']); ?>" data-count="<?php echo $saint['site_count']; ?>">
                                    <?php echo esc_html($saint['name']); ?> (<?php echo $saint['site_count']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Selected Saint Display -->
                        <div class="selected-saint-display" id="selected-saint-display" style="display: none;">
                            <div class="selected-saint-info">
                                <span class="selected-saint-name"></span>
                                <span class="selected-saint-count"></span>
                            </div>
                            <button class="clear-saint-btn" id="clear-saint-btn">Clear</button>
                        </div>
                    </div>

                    <!-- Location Selection -->
                    <div class="filter-column location-filter-column">
                        <h3 class="filter-column-title">Select Location</h3>

                        <div class="location-dropdown-wrapper">
                            <select id="county-filter-select" class="county-filter-select">
                                <option value="">All Counties</option>
                                <?php foreach ($counties as $slug => $name) : ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Province Quick Select -->
                        <div class="province-buttons">
                            <button class="province-btn" data-province="connacht">Connacht</button>
                            <button class="province-btn" data-province="leinster">Leinster</button>
                            <button class="province-btn" data-province="munster">Munster</button>
                            <button class="province-btn" data-province="ulster">Ulster</button>
                        </div>
                    </div>
                </div>

                <!-- Site Type Filter (Secondary) -->
                <div class="site-type-filter">
                    <h3 class="filter-column-title">Site Type</h3>
                    <div class="site-type-buttons">
                        <button class="site-type-btn active" data-type="all">All Sites</button>
                        <button class="site-type-btn" data-type="monastic_site">Monastic Sites</button>
                        <button class="site-type-btn" data-type="pilgrimage_route">Pilgrimage Routes</button>
                        <button class="site-type-btn" data-type="christian_site">Holy Wells & Crosses</button>
                    </div>
                </div>

                <!-- Active Filters Summary -->
                <div class="active-filters-summary" id="active-filters-summary" style="display: none;">
                    <span class="summary-label">Active Filters:</span>
                    <div class="active-filter-tags" id="active-filter-tags"></div>
                    <button class="clear-all-filters-btn" id="clear-all-filters-btn">Clear All</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Map Section -->
    <section class="saints-map-section" id="saints-map-section">
        <div class="container">
            <div class="map-header">
                <h2 class="section-title">Interactive Map</h2>
                <div class="map-controls">
                    <button class="map-control-btn" id="fit-markers-btn">Fit All Markers</button>
                    <button class="map-control-btn" id="toggle-clusters-btn">Toggle Clusters</button>
                </div>
            </div>

            <div id="saints-map" class="saints-map-container">
                <!-- Map will be initialized by JavaScript -->
                <div class="map-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading map...</p>
                </div>
            </div>

            <!-- Map Legend -->
            <div class="map-legend">
                <div class="legend-item">
                    <span class="legend-marker monastic"></span>
                    <span class="legend-label">Monastic Sites</span>
                </div>
                <div class="legend-item">
                    <span class="legend-marker route"></span>
                    <span class="legend-label">Pilgrimage Routes</span>
                </div>
                <div class="legend-item">
                    <span class="legend-marker christian"></span>
                    <span class="legend-label">Holy Wells & Crosses</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="saints-results-section" id="saints-results-section">
        <div class="container">
            <div class="results-header">
                <div class="results-info">
                    <span class="results-count" id="results-count">Loading sites...</span>
                    <span class="results-filter-info" id="results-filter-info"></span>
                </div>
                <div class="results-controls">
                    <div class="sort-control">
                        <label for="sort-select">Sort by:</label>
                        <select id="sort-select" class="sort-select">
                            <option value="relevance">Relevance</option>
                            <option value="name">Name (A-Z)</option>
                            <option value="county">County</option>
                            <option value="type">Site Type</option>
                        </select>
                    </div>
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid" title="Grid View">
                            <span class="view-icon grid-icon">▦</span>
                        </button>
                        <button class="view-btn" data-view="list" title="List View">
                            <span class="view-icon list-icon">☰</span>
                        </button>
                    </div>
                </div>
            </div>

            <div id="saints-results" class="saints-results-grid">
                <!-- Results will be loaded by JavaScript -->
                <div class="results-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading pilgrimage sites...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="results-pagination" id="results-pagination" style="display: none;">
                <button class="pagination-btn prev-btn" id="prev-page-btn" disabled>← Previous</button>
                <span class="pagination-info" id="pagination-info">Page 1 of 1</span>
                <button class="pagination-btn next-btn" id="next-page-btn">Next →</button>
            </div>
        </div>
    </section>

    <!-- All Saints Directory (Hidden by default) -->
    <section class="all-saints-directory" id="all-saints-directory" style="display: none;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Complete Saints Directory</h2>
                <p class="section-subtitle">Browse all <?php echo $total_saints; ?> saints associated with pilgrimage sites in Ireland</p>
                <button class="close-directory-btn" id="close-directory-btn">×</button>
            </div>

            <div class="saints-directory-grid" id="saints-directory-grid">
                <?php
                // Group saints by first letter
                $grouped_saints = array();
                foreach ($saints_data as $saint) {
                    $first_letter = strtoupper(substr(str_replace('St. ', '', $saint['name']), 0, 1));
                    if (!isset($grouped_saints[$first_letter])) {
                        $grouped_saints[$first_letter] = array();
                    }
                    $grouped_saints[$first_letter][] = $saint;
                }
                ksort($grouped_saints);

                foreach ($grouped_saints as $letter => $letter_saints) :
                ?>
                <div class="saints-letter-group" id="saints-group-<?php echo $letter; ?>">
                    <h3 class="letter-heading"><?php echo $letter; ?></h3>
                    <div class="letter-saints-list">
                        <?php foreach ($letter_saints as $saint) : ?>
                        <div class="directory-saint-item" data-saint="<?php echo esc_attr($saint['slug']); ?>">
                            <span class="saint-name"><?php echo esc_html($saint['name']); ?></span>
                            <span class="saint-count"><?php echo $saint['site_count']; ?> sites</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
