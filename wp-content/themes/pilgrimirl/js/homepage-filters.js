/**
 * PilgrimIRL Homepage Filters JavaScript
 * Handles dynamic filtering of pilgrimage sites, saints, and centuries
 */

(function($) {
    'use strict';

    let currentFilters = {
        post_type: 'pilgrimage_route', // Default to pilgrimage routes
        county: '',
        saint: '',
        century: ''
    };
    
    let allSites = [];
    let filteredSites = [];
    let currentView = 'grid'; // 'grid' or 'list'

    /**
     * Initialize homepage filters
     */
    function initHomepageFilters() {
        console.log('Initializing homepage filters');
        
        // Set default active state for Pilgrimage Routes
        $('.post-type-filters .filter-btn').removeClass('active');
        $('.post-type-filters .filter-btn[data-type="pilgrimage_route"]').addClass('active');
        
        // Load initial filter options
        loadFilterOptions();
        
        // Bind filter button events
        bindFilterEvents();
        
        // Bind view toggle events
        bindViewToggleEvents();
        
        // Load initial results with default filter
        loadFilteredSites();
        
        // Ensure map gets updated with initial filter after a short delay
        setTimeout(function() {
            if (filteredSites.length > 0) {
                updateMapWithFilteredSites(filteredSites);
            }
        }, 1000);
    }

    /**
     * Load filter options from server
     */
    function loadFilterOptions() {
        // Load saints (dropdown)
        loadSaintsDropdown();
        
        // Load centuries (buttons)
        loadFilterOptionsByType('centuries', '#centuries-filters .filter-buttons');
        
        // Load counties (buttons) - though this might not be needed if using existing county filter
        loadFilterOptionsByType('counties', '#counties-filters .filter-buttons');
    }

    /**
     * Load saints dropdown
     */
    function loadSaintsDropdown() {
        console.log('Loading saints dropdown...');
        $.ajax({
            url: pilgrimirl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_filter_options',
                filter_type: 'saints',
                nonce: pilgrimirl_ajax.nonce
            },
            success: function(response) {
                console.log('Saints dropdown response:', response);
                if (response.success && response.data.length > 0) {
                    renderSaintsDropdown(response.data);
                } else {
                    console.log('No saints found or error loading options');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading saints:', error);
            }
        });
    }

    /**
     * Render saints dropdown options
     */
    function renderSaintsDropdown(saints) {
        console.log('Rendering saints dropdown with', saints.length, 'saints');
        const select = $('#saints-select');
        
        // Clear existing options except "All Saints"
        select.find('option:not(:first)').remove();
        
        // Add saint options
        saints.forEach(function(saint) {
            const option = $('<option>')
                .attr('value', saint.slug)
                .text(saint.name + ' (' + saint.count + ')');
            select.append(option);
        });
        
        console.log('Saints dropdown rendered, total options:', select.find('option').length);
    }

    /**
     * Load specific filter options
     */
    function loadFilterOptionsByType(filterType, containerSelector) {
        $.ajax({
            url: pilgrimirl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_filter_options',
                filter_type: filterType,
                nonce: pilgrimirl_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderFilterButtons(response.data, containerSelector, filterType);
                } else {
                    console.log('No ' + filterType + ' found or error loading options');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading ' + filterType + ':', error);
            }
        });
    }

    /**
     * Render filter buttons
     */
    function renderFilterButtons(options, containerSelector, filterType) {
        const container = $(containerSelector);
        
        // Add "All" button first
        const allButton = $('<button>')
            .addClass('filter-btn')
            .attr('data-filter-type', filterType)
            .attr('data-value', '')
            .text('All ' + capitalizeFirst(filterType))
            .addClass('active');
        
        container.append(allButton);
        
        // Add individual option buttons
        options.forEach(function(option) {
            const button = $('<button>')
                .addClass('filter-btn')
                .attr('data-filter-type', filterType)
                .attr('data-value', option.slug)
                .html(option.name + ' <span class="count">(' + option.count + ')</span>');
            
            container.append(button);
        });
    }

    /**
     * Bind filter button events
     */
    function bindFilterEvents() {
        console.log('Binding filter events...');
        
        // Post type filters
        $(document).on('click', '.post-type-filters .filter-btn', function() {
            const postType = $(this).data('type');
            console.log('Post type filter clicked:', postType);
            
            // Update active state
            $('.post-type-filters .filter-btn').removeClass('active');
            $(this).addClass('active');
            
            // Update current filters
            currentFilters.post_type = postType === 'all' ? '' : postType;
            
            // Load filtered results
            loadFilteredSites();
        });
        
        // Saints dropdown
        $(document).on('change', '#saints-select', function() {
            const value = $(this).val();
            const selectedText = $(this).find('option:selected').text();
            console.log('Saints dropdown changed! Value:', value, 'Text:', selectedText);
            console.log('Current filters before update:', currentFilters);
            
            // Update current filters
            currentFilters.saint = value;
            console.log('Current filters after update:', currentFilters);
            
            // Load filtered results
            loadFilteredSites();
        });
        
        // Taxonomy filters (centuries, counties)
        $(document).on('click', '.filter-btn[data-filter-type]', function() {
            const filterType = $(this).data('filter-type');
            const value = $(this).data('value');
            console.log('Taxonomy filter clicked:', filterType, value);
            
            // Update active state within this filter group
            $(this).siblings('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            // Update current filters
            switch(filterType) {
                case 'centuries':
                    currentFilters.century = value;
                    break;
                case 'counties':
                    currentFilters.county = value;
                    break;
            }
            
            // Load filtered results
            loadFilteredSites();
        });
        
        // Clear all filters
        $(document).on('click', '.clear-filters-btn', function() {
            clearAllFilters();
        });
        
        console.log('Filter events bound successfully');
    }

    /**
     * Bind view toggle events
     */
    function bindViewToggleEvents() {
        $(document).on('click', '.view-btn', function() {
            const view = $(this).data('view');
            
            // Update active state
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            
            // Update current view
            currentView = view;
            
            // Re-render results with new view
            renderResults(filteredSites);
        });
    }

    /**
     * Load filtered sites from server
     */
    function loadFilteredSites() {
        console.log('Loading filtered sites with filters:', currentFilters);
        
        // Show loading state
        showLoadingState();
        
        $.ajax({
            url: pilgrimirl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_filtered_sites',
                post_type: currentFilters.post_type,
                county: currentFilters.county,
                saint: currentFilters.saint,
                century: currentFilters.century,
                nonce: pilgrimirl_ajax.nonce
            },
            success: function(response) {
                console.log('Filtered sites response:', response);
                if (response.success) {
                    filteredSites = response.data;
                    console.log('Found', filteredSites.length, 'filtered sites');
                    renderResults(filteredSites);
                    updateResultsCount(filteredSites.length);
                } else {
                    console.error('Error loading filtered sites:', response);
                    showErrorState();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading filtered sites:', error);
                showErrorState();
            }
        });
    }

    /**
     * Render results
     */
    function renderResults(sites) {
        const container = $('#pilgrim-results');
        container.empty();
        
        if (sites.length === 0) {
            showEmptyState();
            return;
        }
        
        // Add view class
        container.removeClass('results-grid results-list').addClass('results-' + currentView);
        
        sites.forEach(function(site) {
            const card = createResultCard(site);
            container.append(card);
        });
        
        // Update map with filtered sites
        updateMapWithFilteredSites(sites);
    }

    /**
     * Create result card HTML
     */
    function createResultCard(site) {
        const postTypeLabels = {
            'monastic_site': 'Monastic Site',
            'pilgrimage_route': 'Pilgrimage Route',
            'christian_ruin': 'Christian Ruin'
        };
        
        const postTypeLabel = postTypeLabels[site.post_type] || site.post_type;
        const countyNames = Array.isArray(site.county) ? site.county.join(', ') : '';
        const saintNames = Array.isArray(site.saints) ? site.saints.join(', ') : '';
        const centuryNames = Array.isArray(site.century) ? site.century.join(', ') : '';
        
        const card = $('<div>').addClass('result-card');
        
        // Card image
        const cardImage = $('<div>').addClass('result-card-image');
        const cardType = $('<div>').addClass('result-card-type').text(postTypeLabel);
        cardImage.append(cardType);
        
        // Card content
        const cardContent = $('<div>').addClass('result-card-content');
        
        const cardTitle = $('<h3>').addClass('result-card-title');
        const titleLink = $('<a>').attr('href', site.permalink).text(fixHtmlEntities(site.title));
        cardTitle.append(titleLink);
        
        const cardMeta = $('<div>').addClass('result-card-meta');
        if (countyNames) {
            cardMeta.append($('<span>').addClass('result-card-county').text('County ' + countyNames));
        }
        if (saintNames) {
            cardMeta.append($('<span>').addClass('result-card-county').text('Saints: ' + saintNames));
        }
        if (centuryNames) {
            cardMeta.append($('<span>').addClass('result-card-county').text(centuryNames));
        }
        
        const cardExcerpt = $('<p>').addClass('result-card-excerpt').text(fixHtmlEntities(site.excerpt || 'Discover this historic site...'));
        
        const cardActions = $('<div>').addClass('result-card-actions');
        const cardLink = $('<a>').addClass('result-card-link').attr('href', site.permalink).text('Learn More');
        cardActions.append(cardLink);
        
        // Add map button if coordinates available
        if (site.latitude && site.longitude) {
            const mapBtn = $('<button>')
                .addClass('result-card-map-btn')
                .attr('data-lat', site.latitude)
                .attr('data-lng', site.longitude)
                .text('Show on Map')
                .on('click', function() {
                    showOnMap(this);
                });
            cardActions.append(mapBtn);
        }
        
        cardContent.append(cardTitle, cardMeta, cardExcerpt, cardActions);
        card.append(cardImage, cardContent);
        
        return card;
    }

    /**
     * Update results count
     */
    function updateResultsCount(count) {
        const countElement = $('.results-count');
        if (count === 0) {
            countElement.html('No sites found');
        } else if (count === 1) {
            countElement.html('Showing <strong>1</strong> site');
        } else {
            countElement.html('Showing <strong>' + count + '</strong> sites');
        }
    }

    /**
     * Show loading state
     */
    function showLoadingState() {
        const container = $('#pilgrim-results');
        container.html(`
            <div class="results-loading">
                <div class="loading-spinner"></div>
                <p>Loading pilgrimage sites...</p>
            </div>
        `);
    }

    /**
     * Show empty state
     */
    function showEmptyState() {
        const container = $('#pilgrim-results');
        container.html(`
            <div class="results-loading">
                <p>No sites found matching your criteria.</p>
                <button class="filter-btn clear-filters-btn">Clear All Filters</button>
            </div>
        `);
    }

    /**
     * Show error state
     */
    function showErrorState() {
        const container = $('#pilgrim-results');
        container.html(`
            <div class="results-loading">
                <p>Sorry, there was an error loading the sites. Please try again.</p>
                <button class="filter-btn" onclick="location.reload()">Reload Page</button>
            </div>
        `);
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        // Reset filter object
        currentFilters = {
            post_type: '',
            county: '',
            saint: '',
            century: ''
        };
        
        // Reset UI
        $('.filter-btn').removeClass('active');
        $('.filter-btn[data-type="all"]').addClass('active');
        $('.filter-btn[data-value=""]').addClass('active');
        $('#saints-select').val(''); // Reset saints dropdown
        
        // Reload results
        loadFilteredSites();
    }

    /**
     * Utility function to capitalize first letter
     */
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Fix HTML entities in text
     */
    function fixHtmlEntities(text) {
        const entities = {
            '&#8216;': "'", // Left single quotation mark
            '&#8217;': "'", // Right single quotation mark
            '&#8220;': '"', // Left double quotation mark
            '&#8221;': '"', // Right double quotation mark
            '&#8211;': '–', // En dash
            '&#8212;': '—', // Em dash
            '&#8230;': '…', // Horizontal ellipsis
            '&amp;': '&',   // Ampersand
            '&lt;': '<',    // Less than
            '&gt;': '>',    // Greater than
            '&quot;': '"',  // Quotation mark
            '&apos;': "'"   // Apostrophe
        };
        
        let fixedText = text;
        for (const [entity, replacement] of Object.entries(entities)) {
            fixedText = fixedText.replace(new RegExp(entity, 'g'), replacement);
        }
        return fixedText;
    }

    /**
     * Update map with filtered sites
     */
    function updateMapWithFilteredSites(sites) {
        // Check if map exists and has the update function
        if (typeof window.updateHomepageMap === 'function') {
            console.log('Updating homepage map with', sites.length, 'filtered sites');
            window.updateHomepageMap(sites);
        } else {
            console.log('Homepage map update function not available');
        }
    }

    /**
     * Show location on map (if map is available)
     */
    function showOnMap(button) {
        const lat = parseFloat($(button).data('lat'));
        const lng = parseFloat($(button).data('lng'));
        
        // Check if there's a map on the page
        const mapSection = $('#pilgrim-main-map');
        if (mapSection.length && typeof window.showOnMap === 'function') {
            window.showOnMap(button);
        } else {
            // If no map, could open in Google Maps
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(url, '_blank');
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Only initialize on pages with filter sections
        if ($('.pilgrim-filters-section').length > 0) {
            console.log('Found filter section, initializing...');
            initHomepageFilters();
        } else {
            console.log('No filter section found on this page');
        }
    });

})(jQuery);
