/**
 * PilgrimIRL Saints Page Filters JavaScript
 * Handles advanced filtering, map integration, and saint selection for the Saints page
 */

(function($) {
    'use strict';

    // State management
    let state = {
        filters: {
            saint: '',
            county: '',
            province: '',
            site_type: 'all'
        },
        sites: [],
        filteredSites: [],
        currentPage: 1,
        sitesPerPage: 12,
        currentView: 'grid',
        sortBy: 'relevance',
        mapInitialized: false,
        clustersEnabled: true
    };

    // Province to county mapping
    const provinceCounties = {
        connacht: ['galway', 'leitrim', 'mayo', 'roscommon', 'sligo'],
        leinster: ['carlow', 'dublin', 'kildare', 'kilkenny', 'laois', 'longford', 'louth', 'meath', 'offaly', 'westmeath', 'wexford', 'wicklow'],
        munster: ['clare', 'cork', 'kerry', 'limerick', 'tipperary', 'waterford'],
        ulster: ['antrim', 'armagh', 'cavan', 'derry', 'donegal', 'down', 'fermanagh', 'monaghan', 'tyrone']
    };

    // Map variables
    let saintsMap = null;
    let mapMarkers = [];
    let markerClusterer = null;
    let infoWindow = null;

    /**
     * Initialize the saints page
     */
    function init() {
        console.log('Saints Page: Initializing...');

        // Bind all event handlers
        bindEventHandlers();

        // Load initial sites data
        loadAllSites();

        // Initialize map when Google Maps is ready
        initMapWhenReady();

        console.log('Saints Page: Initialization complete');
    }

    /**
     * Bind all event handlers
     */
    function bindEventHandlers() {
        // A-Z Navigation
        $(document).on('click', '.az-letter', handleAZNavigation);

        // Featured saint cards
        $(document).on('click', '.saint-select-btn', handleSaintSelect);
        $(document).on('click', '.saint-map-btn', handleShowSaintOnMap);
        $(document).on('click', '.saint-card', function(e) {
            if (!$(e.target).is('button')) {
                const saint = $(this).data('saint');
                selectSaint(saint);
            }
        });

        // Show all saints button
        $('#show-all-saints-btn').on('click', toggleAllSaintsDirectory);
        $('#close-directory-btn').on('click', toggleAllSaintsDirectory);

        // Directory saint selection
        $(document).on('click', '.directory-saint-item', function() {
            const saint = $(this).data('saint');
            selectSaint(saint);
            toggleAllSaintsDirectory();
        });

        // Saint filter dropdown
        $('#saints-filter-select').on('change', function() {
            const saint = $(this).val();
            selectSaint(saint);
        });

        // Saint search
        $('#saint-search').on('input', debounce(handleSaintSearch, 300));

        // Clear saint button
        $('#clear-saint-btn').on('click', function() {
            selectSaint('');
        });

        // County filter
        $('#county-filter-select').on('change', function() {
            state.filters.county = $(this).val();
            state.filters.province = '';
            $('.province-btn').removeClass('active');
            applyFilters();
        });

        // Province buttons
        $(document).on('click', '.province-btn', handleProvinceSelect);

        // Site type buttons
        $(document).on('click', '.site-type-btn', function() {
            $('.site-type-btn').removeClass('active');
            $(this).addClass('active');
            state.filters.site_type = $(this).data('type');
            applyFilters();
        });

        // Clear all filters
        $('#clear-all-filters-btn').on('click', clearAllFilters);

        // View toggle
        $(document).on('click', '.view-btn', function() {
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            state.currentView = $(this).data('view');
            renderResults();
        });

        // Sort select
        $('#sort-select').on('change', function() {
            state.sortBy = $(this).val();
            sortAndRenderResults();
        });

        // Pagination
        $('#prev-page-btn').on('click', function() {
            if (state.currentPage > 1) {
                state.currentPage--;
                renderResults();
                scrollToResults();
            }
        });

        $('#next-page-btn').on('click', function() {
            const totalPages = Math.ceil(state.filteredSites.length / state.sitesPerPage);
            if (state.currentPage < totalPages) {
                state.currentPage++;
                renderResults();
                scrollToResults();
            }
        });

        // Map controls
        $('#fit-markers-btn').on('click', fitAllMarkers);
        $('#toggle-clusters-btn').on('click', toggleClusters);

        // Result card map buttons
        $(document).on('click', '.result-map-btn', function() {
            const lat = parseFloat($(this).data('lat'));
            const lng = parseFloat($(this).data('lng'));
            const title = $(this).data('title');
            showLocationOnMap(lat, lng, title);
        });
    }

    /**
     * Load all sites from the server
     */
    function loadAllSites() {
        showLoadingState('#saints-results');

        $.ajax({
            url: pilgrimirl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_saints_page_sites',
                nonce: pilgrimirl_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    state.sites = response.data;
                    state.filteredSites = state.sites;
                    console.log('Saints Page: Loaded', state.sites.length, 'sites');
                    renderResults();
                    updateMapMarkers();
                } else {
                    showErrorState('#saints-results', 'Failed to load sites');
                }
            },
            error: function(xhr, status, error) {
                console.error('Saints Page: Error loading sites:', error);
                showErrorState('#saints-results', 'Error loading sites. Please try again.');
            }
        });
    }

    /**
     * Apply all current filters
     */
    function applyFilters() {
        console.log('Saints Page: Applying filters:', state.filters);

        state.filteredSites = state.sites.filter(site => {
            // Saint filter
            if (state.filters.saint) {
                const siteSaints = site.saints || [];
                const saintSlug = state.filters.saint.toLowerCase();
                const hasSaint = siteSaints.some(s =>
                    s.toLowerCase().includes(saintSlug.replace(/-/g, ' ')) ||
                    sanitizeSlug(s) === saintSlug
                );
                if (!hasSaint) {
                    // Also check content-based saint matching
                    const siteContent = (site.title + ' ' + site.excerpt + ' ' + (site.communities_provenance || '')).toLowerCase();
                    const saintName = state.filters.saint.replace(/-/g, ' ');
                    if (!siteContent.includes(saintName) && !siteContent.includes('st. ' + saintName) && !siteContent.includes('saint ' + saintName)) {
                        return false;
                    }
                }
            }

            // County filter
            if (state.filters.county) {
                const siteCounties = site.county || [];
                if (!siteCounties.some(c => sanitizeSlug(c) === state.filters.county)) {
                    return false;
                }
            }

            // Province filter
            if (state.filters.province) {
                const provinceCountyList = provinceCounties[state.filters.province] || [];
                const siteCounties = site.county || [];
                const inProvince = siteCounties.some(c =>
                    provinceCountyList.includes(sanitizeSlug(c))
                );
                if (!inProvince) {
                    return false;
                }
            }

            // Site type filter
            if (state.filters.site_type && state.filters.site_type !== 'all') {
                if (site.post_type !== state.filters.site_type) {
                    return false;
                }
            }

            return true;
        });

        // Reset to page 1 when filters change
        state.currentPage = 1;

        // Update UI
        updateActiveFiltersSummary();
        sortAndRenderResults();
        updateMapMarkers();
    }

    /**
     * Select a saint
     */
    function selectSaint(saintSlug) {
        state.filters.saint = saintSlug;

        // Update dropdown
        $('#saints-filter-select').val(saintSlug);

        // Update selected saint display
        if (saintSlug) {
            const selectedOption = $('#saints-filter-select option[value="' + saintSlug + '"]');
            const saintName = selectedOption.text().split('(')[0].trim();
            const saintCount = selectedOption.data('count');

            $('#selected-saint-display').show();
            $('#selected-saint-display .selected-saint-name').text(saintName);
            $('#selected-saint-display .selected-saint-count').text(saintCount + ' sites');

            // Highlight the saint card if visible
            $('.saint-card').removeClass('selected');
            $('.saint-card[data-saint="' + saintSlug + '"]').addClass('selected');
        } else {
            $('#selected-saint-display').hide();
            $('.saint-card').removeClass('selected');
        }

        applyFilters();

        // Scroll to results if a saint is selected
        if (saintSlug) {
            scrollToResults();
        }
    }

    /**
     * Handle saint selection from cards
     */
    function handleSaintSelect(e) {
        e.stopPropagation();
        const saint = $(this).data('saint');
        selectSaint(saint);
    }

    /**
     * Handle showing saint on map
     */
    function handleShowSaintOnMap(e) {
        e.stopPropagation();
        const saint = $(this).data('saint');
        selectSaint(saint);

        // Scroll to map
        $('html, body').animate({
            scrollTop: $('#saints-map-section').offset().top - 100
        }, 500);
    }

    /**
     * Handle A-Z navigation
     */
    function handleAZNavigation() {
        const letter = $(this).data('letter');

        // Update active state
        $('.az-letter').removeClass('active');
        $(this).addClass('active');

        // If directory is open, scroll to letter
        if ($('#all-saints-directory').is(':visible')) {
            const letterGroup = $('#saints-group-' + letter);
            if (letterGroup.length) {
                $('html, body').animate({
                    scrollTop: letterGroup.offset().top - 100
                }, 500);
            }
        } else {
            // Open directory and scroll to letter
            toggleAllSaintsDirectory();
            setTimeout(function() {
                const letterGroup = $('#saints-group-' + letter);
                if (letterGroup.length) {
                    $('html, body').animate({
                        scrollTop: letterGroup.offset().top - 100
                    }, 500);
                }
            }, 300);
        }
    }

    /**
     * Handle saint search
     */
    function handleSaintSearch() {
        const searchTerm = $('#saint-search').val().toLowerCase();

        if (!searchTerm) {
            // Reset dropdown to show all options
            $('#saints-filter-select option').show();
            return;
        }

        // Filter dropdown options
        $('#saints-filter-select option').each(function() {
            const optionText = $(this).text().toLowerCase();
            if (optionText.includes(searchTerm) || $(this).val() === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    /**
     * Handle province selection
     */
    function handleProvinceSelect() {
        const province = $(this).data('province');

        if ($(this).hasClass('active')) {
            // Deselect province
            $(this).removeClass('active');
            state.filters.province = '';
            state.filters.county = '';
            $('#county-filter-select').val('');
        } else {
            // Select province
            $('.province-btn').removeClass('active');
            $(this).addClass('active');
            state.filters.province = province;
            state.filters.county = '';
            $('#county-filter-select').val('');
        }

        applyFilters();
    }

    /**
     * Toggle all saints directory
     */
    function toggleAllSaintsDirectory() {
        $('#all-saints-directory').slideToggle(300);

        const btn = $('#show-all-saints-btn');
        if ($('#all-saints-directory').is(':visible')) {
            btn.find('.arrow').text('↑');
            btn.text(btn.text().replace('View', 'Hide'));
        } else {
            btn.find('.arrow').text('↓');
            btn.text(btn.text().replace('Hide', 'View'));
        }
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        state.filters = {
            saint: '',
            county: '',
            province: '',
            site_type: 'all'
        };

        // Reset UI elements
        $('#saints-filter-select').val('');
        $('#county-filter-select').val('');
        $('#saint-search').val('');
        $('.province-btn').removeClass('active');
        $('.site-type-btn').removeClass('active');
        $('.site-type-btn[data-type="all"]').addClass('active');
        $('#selected-saint-display').hide();
        $('.saint-card').removeClass('selected');

        applyFilters();
    }

    /**
     * Update active filters summary
     */
    function updateActiveFiltersSummary() {
        const tagsContainer = $('#active-filter-tags');
        tagsContainer.empty();

        let hasFilters = false;

        if (state.filters.saint) {
            hasFilters = true;
            const saintName = $('#saints-filter-select option[value="' + state.filters.saint + '"]').text().split('(')[0].trim();
            tagsContainer.append(createFilterTag('Saint: ' + saintName, 'saint'));
        }

        if (state.filters.county) {
            hasFilters = true;
            const countyName = $('#county-filter-select option[value="' + state.filters.county + '"]').text();
            tagsContainer.append(createFilterTag('County: ' + countyName, 'county'));
        }

        if (state.filters.province) {
            hasFilters = true;
            const provinceName = state.filters.province.charAt(0).toUpperCase() + state.filters.province.slice(1);
            tagsContainer.append(createFilterTag('Province: ' + provinceName, 'province'));
        }

        if (state.filters.site_type && state.filters.site_type !== 'all') {
            hasFilters = true;
            const typeLabels = {
                'monastic_site': 'Monastic Sites',
                'pilgrimage_route': 'Pilgrimage Routes',
                'christian_site': 'Holy Wells & Crosses'
            };
            tagsContainer.append(createFilterTag(typeLabels[state.filters.site_type] || state.filters.site_type, 'site_type'));
        }

        if (hasFilters) {
            $('#active-filters-summary').show();
        } else {
            $('#active-filters-summary').hide();
        }
    }

    /**
     * Create a filter tag element
     */
    function createFilterTag(text, filterType) {
        const tag = $('<span class="filter-tag"></span>');
        tag.text(text);

        const removeBtn = $('<button class="remove-filter-btn">×</button>');
        removeBtn.on('click', function() {
            removeFilter(filterType);
        });

        tag.append(removeBtn);
        return tag;
    }

    /**
     * Remove a specific filter
     */
    function removeFilter(filterType) {
        switch (filterType) {
            case 'saint':
                selectSaint('');
                break;
            case 'county':
                state.filters.county = '';
                $('#county-filter-select').val('');
                break;
            case 'province':
                state.filters.province = '';
                $('.province-btn').removeClass('active');
                break;
            case 'site_type':
                state.filters.site_type = 'all';
                $('.site-type-btn').removeClass('active');
                $('.site-type-btn[data-type="all"]').addClass('active');
                break;
        }
        applyFilters();
    }

    /**
     * Sort and render results
     */
    function sortAndRenderResults() {
        // Sort filtered sites
        switch (state.sortBy) {
            case 'name':
                state.filteredSites.sort((a, b) => a.title.localeCompare(b.title));
                break;
            case 'county':
                state.filteredSites.sort((a, b) => {
                    const countyA = (a.county && a.county[0]) || '';
                    const countyB = (b.county && b.county[0]) || '';
                    return countyA.localeCompare(countyB);
                });
                break;
            case 'type':
                state.filteredSites.sort((a, b) => a.post_type.localeCompare(b.post_type));
                break;
            case 'relevance':
            default:
                // For relevance, sites with selected saint first
                if (state.filters.saint) {
                    state.filteredSites.sort((a, b) => {
                        const aHasSaint = (a.saints || []).some(s => sanitizeSlug(s) === state.filters.saint);
                        const bHasSaint = (b.saints || []).some(s => sanitizeSlug(s) === state.filters.saint);
                        if (aHasSaint && !bHasSaint) return -1;
                        if (!aHasSaint && bHasSaint) return 1;
                        return 0;
                    });
                }
                break;
        }

        renderResults();
    }

    /**
     * Render results to the page
     */
    function renderResults() {
        const container = $('#saints-results');
        container.empty();

        // Update count
        updateResultsCount();

        if (state.filteredSites.length === 0) {
            showEmptyState(container);
            $('#results-pagination').hide();
            return;
        }

        // Calculate pagination
        const totalPages = Math.ceil(state.filteredSites.length / state.sitesPerPage);
        const startIndex = (state.currentPage - 1) * state.sitesPerPage;
        const endIndex = startIndex + state.sitesPerPage;
        const pageSites = state.filteredSites.slice(startIndex, endIndex);

        // Set view class
        container.removeClass('saints-results-grid saints-results-list').addClass('saints-results-' + state.currentView);

        // Render cards
        pageSites.forEach(site => {
            container.append(createResultCard(site));
        });

        // Update pagination
        updatePagination(totalPages);
    }

    /**
     * Create a result card
     */
    function createResultCard(site) {
        const postTypeLabels = {
            'monastic_site': 'Monastic Site',
            'pilgrimage_route': 'Pilgrimage Route',
            'christian_site': 'Christian Site',
            'christian_ruin': 'Christian Ruin'
        };

        const postTypeLabel = postTypeLabels[site.post_type] || 'Site';
        const countyNames = Array.isArray(site.county) ? site.county.join(', ') : '';
        const saintNames = Array.isArray(site.saints) ? site.saints.slice(0, 3).join(', ') : '';

        const card = $('<div class="result-card"></div>');
        card.attr('data-type', site.post_type);

        // Card image
        const cardImage = $('<div class="result-card-image"></div>');
        if (site.thumbnail) {
            cardImage.css('background-image', 'url(' + site.thumbnail + ')');
        }
        const cardType = $('<div class="result-card-type"></div>').text(postTypeLabel);
        cardImage.append(cardType);

        // Card content
        const cardContent = $('<div class="result-card-content"></div>');

        const cardTitle = $('<h3 class="result-card-title"></h3>');
        const titleLink = $('<a></a>').attr('href', site.permalink).text(fixHtmlEntities(site.title));
        cardTitle.append(titleLink);

        const cardMeta = $('<div class="result-card-meta"></div>');
        if (countyNames) {
            cardMeta.append($('<span class="meta-county"></span>').text(countyNames));
        }
        if (saintNames) {
            cardMeta.append($('<span class="meta-saints"></span>').text(saintNames + (site.saints && site.saints.length > 3 ? '...' : '')));
        }

        const cardExcerpt = $('<p class="result-card-excerpt"></p>').text(fixHtmlEntities(site.excerpt || ''));

        const cardActions = $('<div class="result-card-actions"></div>');
        const learnMoreBtn = $('<a class="result-learn-more-btn"></a>').attr('href', site.permalink).text('Learn More');
        cardActions.append(learnMoreBtn);

        if (site.latitude && site.longitude) {
            const mapBtn = $('<button class="result-map-btn"></button>')
                .attr('data-lat', site.latitude)
                .attr('data-lng', site.longitude)
                .attr('data-title', site.title)
                .text('Show on Map');
            cardActions.append(mapBtn);
        }

        cardContent.append(cardTitle, cardMeta, cardExcerpt, cardActions);
        card.append(cardImage, cardContent);

        return card;
    }

    /**
     * Update results count display
     */
    function updateResultsCount() {
        const count = state.filteredSites.length;
        const countText = count === 1 ? '1 site' : count + ' sites';

        let filterInfo = '';
        if (state.filters.saint) {
            const saintName = $('#saints-filter-select option[value="' + state.filters.saint + '"]').text().split('(')[0].trim();
            filterInfo = ' associated with ' + saintName;
        }
        if (state.filters.county) {
            const countyName = $('#county-filter-select option[value="' + state.filters.county + '"]').text();
            filterInfo += (filterInfo ? ' in ' : ' in ') + countyName;
        }
        if (state.filters.province) {
            const provinceName = state.filters.province.charAt(0).toUpperCase() + state.filters.province.slice(1);
            filterInfo += (filterInfo ? ' in ' : ' in ') + provinceName;
        }

        $('#results-count').html('Showing <strong>' + countText + '</strong>');
        $('#results-filter-info').text(filterInfo);
    }

    /**
     * Update pagination controls
     */
    function updatePagination(totalPages) {
        if (totalPages <= 1) {
            $('#results-pagination').hide();
            return;
        }

        $('#results-pagination').show();
        $('#pagination-info').text('Page ' + state.currentPage + ' of ' + totalPages);
        $('#prev-page-btn').prop('disabled', state.currentPage <= 1);
        $('#next-page-btn').prop('disabled', state.currentPage >= totalPages);
    }

    /**
     * Initialize map when Google Maps API is ready
     */
    function initMapWhenReady() {
        if (typeof google !== 'undefined' && google.maps) {
            initSaintsMap();
        } else {
            // Wait for Google Maps to load
            const checkInterval = setInterval(function() {
                if (typeof google !== 'undefined' && google.maps) {
                    clearInterval(checkInterval);
                    initSaintsMap();
                }
            }, 500);

            // Timeout after 10 seconds
            setTimeout(function() {
                clearInterval(checkInterval);
                if (!state.mapInitialized) {
                    $('#saints-map').html('<div class="map-error"><p>Map loading failed. Please refresh the page.</p></div>');
                }
            }, 10000);
        }
    }

    /**
     * Initialize the saints map
     */
    function initSaintsMap() {
        const mapElement = document.getElementById('saints-map');
        if (!mapElement || state.mapInitialized) return;

        console.log('Saints Page: Initializing map...');

        try {
            saintsMap = new google.maps.Map(mapElement, {
                center: { lat: 53.5, lng: -7.5 }, // Center of Ireland
                zoom: 7,
                styles: [
                    {
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    }
                ],
                mapTypeControl: true,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                    position: google.maps.ControlPosition.TOP_RIGHT
                }
            });

            infoWindow = new google.maps.InfoWindow();

            state.mapInitialized = true;
            console.log('Saints Page: Map initialized');

            // Remove loading state
            $('#saints-map .map-loading').remove();

            // Add markers if we have sites
            if (state.filteredSites.length > 0) {
                updateMapMarkers();
            }
        } catch (error) {
            console.error('Saints Page: Error initializing map:', error);
            $('#saints-map').html('<div class="map-error"><p>Error loading map. Please try refreshing the page.</p></div>');
        }
    }

    /**
     * Update map markers based on filtered sites
     */
    function updateMapMarkers() {
        if (!saintsMap || !state.mapInitialized) return;

        console.log('Saints Page: Updating map markers for', state.filteredSites.length, 'sites');

        // Clear existing markers
        clearMapMarkers();

        // Add markers for filtered sites with coordinates
        const sitesWithCoords = state.filteredSites.filter(site => site.latitude && site.longitude);

        sitesWithCoords.forEach(site => {
            const marker = createMapMarker(site);
            if (marker) {
                mapMarkers.push(marker);
            }
        });

        // Fit bounds if we have markers
        if (mapMarkers.length > 0) {
            fitAllMarkers();
        }

        console.log('Saints Page: Added', mapMarkers.length, 'markers to map');
    }

    /**
     * Create a map marker for a site
     */
    function createMapMarker(site) {
        if (!site.latitude || !site.longitude) return null;

        // Determine marker color based on post type
        const markerColors = {
            'monastic_site': '#8B4513',
            'pilgrimage_route': '#2E7D32',
            'christian_site': '#1565C0',
            'christian_ruin': '#795548'
        };

        const color = markerColors[site.post_type] || '#2c5530';

        const marker = new google.maps.Marker({
            position: { lat: parseFloat(site.latitude), lng: parseFloat(site.longitude) },
            map: saintsMap,
            title: site.title,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="14" cy="14" r="12" fill="${color}" stroke="white" stroke-width="2"/>
                        <text x="14" y="18" text-anchor="middle" fill="white" font-size="14" font-weight="bold">✝</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(28, 28),
                anchor: new google.maps.Point(14, 14)
            }
        });

        // Create info window content
        const contentString = `
            <div class="map-info-window">
                <h4>${site.title}</h4>
                ${site.county && site.county.length > 0 ? `<p class="info-county">County ${site.county[0]}</p>` : ''}
                ${site.saints && site.saints.length > 0 ? `<p class="info-saints">Saints: ${site.saints.slice(0, 3).join(', ')}${site.saints.length > 3 ? '...' : ''}</p>` : ''}
                ${site.excerpt ? `<p class="info-excerpt">${site.excerpt.substring(0, 100)}${site.excerpt.length > 100 ? '...' : ''}</p>` : ''}
                <a href="${site.permalink}" class="info-link">Learn More →</a>
            </div>
        `;

        marker.addListener('click', function() {
            infoWindow.setContent(contentString);
            infoWindow.open(saintsMap, marker);
        });

        return marker;
    }

    /**
     * Clear all map markers
     */
    function clearMapMarkers() {
        mapMarkers.forEach(marker => marker.setMap(null));
        mapMarkers = [];

        if (markerClusterer) {
            markerClusterer.clearMarkers();
        }
    }

    /**
     * Fit map bounds to show all markers
     */
    function fitAllMarkers() {
        if (!saintsMap || mapMarkers.length === 0) return;

        const bounds = new google.maps.LatLngBounds();
        mapMarkers.forEach(marker => {
            bounds.extend(marker.getPosition());
        });

        saintsMap.fitBounds(bounds);

        // Don't zoom in too far for single markers
        if (mapMarkers.length === 1) {
            saintsMap.setZoom(12);
        }
    }

    /**
     * Toggle marker clustering
     */
    function toggleClusters() {
        state.clustersEnabled = !state.clustersEnabled;

        const btn = $('#toggle-clusters-btn');
        if (state.clustersEnabled) {
            btn.text('Disable Clusters');
            // Enable clustering (would need MarkerClusterer library)
        } else {
            btn.text('Enable Clusters');
            // Disable clustering
        }
    }

    /**
     * Show a specific location on the map
     */
    function showLocationOnMap(lat, lng, title) {
        if (!saintsMap) return;

        // Scroll to map
        $('html, body').animate({
            scrollTop: $('#saints-map-section').offset().top - 100
        }, 500, function() {
            // Center and zoom
            saintsMap.setCenter({ lat: lat, lng: lng });
            saintsMap.setZoom(14);

            // Find and trigger click on the marker
            const targetMarker = mapMarkers.find(marker => {
                const pos = marker.getPosition();
                return Math.abs(pos.lat() - lat) < 0.0001 && Math.abs(pos.lng() - lng) < 0.0001;
            });

            if (targetMarker) {
                google.maps.event.trigger(targetMarker, 'click');
            }
        });
    }

    /**
     * Scroll to results section
     */
    function scrollToResults() {
        $('html, body').animate({
            scrollTop: $('#saints-results-section').offset().top - 100
        }, 500);
    }

    /**
     * Show loading state
     */
    function showLoadingState(container) {
        $(container).html(`
            <div class="results-loading">
                <div class="loading-spinner"></div>
                <p>Loading pilgrimage sites...</p>
            </div>
        `);
    }

    /**
     * Show empty state
     */
    function showEmptyState(container) {
        $(container).html(`
            <div class="results-empty">
                <div class="empty-icon">🔍</div>
                <h3>No sites found</h3>
                <p>Try adjusting your filters or selecting a different saint.</p>
                <button class="clear-filters-btn" onclick="document.getElementById('clear-all-filters-btn').click()">Clear All Filters</button>
            </div>
        `);
    }

    /**
     * Show error state
     */
    function showErrorState(container, message) {
        $(container).html(`
            <div class="results-error">
                <div class="error-icon">⚠️</div>
                <h3>Error</h3>
                <p>${message}</p>
                <button class="retry-btn" onclick="location.reload()">Retry</button>
            </div>
        `);
    }

    /**
     * Utility: Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    /**
     * Utility: Sanitize string to slug
     */
    function sanitizeSlug(str) {
        if (!str) return '';
        return str.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }

    /**
     * Utility: Fix HTML entities
     */
    function fixHtmlEntities(text) {
        if (!text) return '';
        const entities = {
            '&#8216;': "'", '&#8217;': "'", '&#8220;': '"', '&#8221;': '"',
            '&#8211;': '–', '&#8212;': '—', '&#8230;': '…',
            '&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"', '&apos;': "'"
        };

        let fixedText = text;
        for (const [entity, replacement] of Object.entries(entities)) {
            fixedText = fixedText.replace(new RegExp(entity, 'g'), replacement);
        }
        return fixedText;
    }

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.saints-page').length > 0) {
            console.log('Saints Page: Document ready, initializing...');
            init();
        }
    });

    // Make certain functions globally available
    window.selectSaint = selectSaint;
    window.showLocationOnMap = showLocationOnMap;

})(jQuery);
