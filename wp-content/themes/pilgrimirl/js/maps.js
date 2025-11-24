/**
 * PilgrimIRL Maps JavaScript
 * Handles Google Maps integration for Christian Sites
 */

// Global variables
let pilgrimMaps = {
    singleSiteMap: null,
    archiveMap: null,
    allSites: [],
    markers: []
};

/**
 * Initialize all maps when Google Maps API is loaded
 */
function initPilgrimMaps() {
    console.log('PilgrimIRL: Initializing maps...');
    
    // Initialize single site map if on single site page
    initSingleSiteMap();
    
    // Initialize archive map if on archive page
    initArchiveMap();
}

/**
 * Initialize single site map
 */
function initSingleSiteMap() {
    const mapElement = document.getElementById('single-site-map');
    if (!mapElement) {
        return;
    }
    
    const lat = parseFloat(mapElement.dataset.lat);
    const lng = parseFloat(mapElement.dataset.lng);
    const title = mapElement.dataset.title;
    const address = mapElement.dataset.address;
    
    console.log('PilgrimIRL: Single site map data:', { lat, lng, title, address });
    
    if (isNaN(lat) || isNaN(lng)) {
        console.log('PilgrimIRL: Invalid coordinates for single site map');
        mapElement.innerHTML = '<div class="map-error"><p>Location coordinates not available for this site.</p></div>';
        return;
    }
    
    // Check if Google Maps is available
    if (typeof google === 'undefined' || !google.maps) {
        console.log('PilgrimIRL: Google Maps not available');
        mapElement.innerHTML = '<div class="map-error"><p>Map loading... Please ensure Google Maps API is configured.</p></div>';
        return;
    }
    
    try {
        // Create map
        pilgrimMaps.singleSiteMap = new google.maps.Map(mapElement, {
            center: { lat: lat, lng: lng },
            zoom: 15,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });
        
        // Create marker
        const marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: pilgrimMaps.singleSiteMap,
            title: title,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="12" fill="#2c5530" stroke="white" stroke-width="2"/>
                        <text x="16" y="20" text-anchor="middle" fill="white" font-size="16" font-weight="bold">✝</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 32)
            }
        });
        
        // Create info window
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div style="max-width: 200px;">
                    <h4 style="margin: 0 0 8px 0; color: #2c5530;">${title}</h4>
                    ${address ? `<p style="margin: 0; font-size: 0.9rem;">${address}</p>` : ''}
                </div>
            `
        });
        
        // Add click listener
        marker.addListener('click', () => {
            infoWindow.open(pilgrimMaps.singleSiteMap, marker);
        });
        
        // Open info window by default
        infoWindow.open(pilgrimMaps.singleSiteMap, marker);
        
        console.log('PilgrimIRL: Single site map initialized successfully');
    } catch (error) {
        console.error('PilgrimIRL: Error initializing single site map:', error);
        mapElement.innerHTML = '<div class="map-error"><p>Error loading map. Please try refreshing the page.</p></div>';
    }
}

/**
 * Initialize archive overview map
 */
function initArchiveMap() {
    const mapElement = document.getElementById('archive-overview-map');
    if (!mapElement) {
        return;
    }
    
    console.log('PilgrimIRL: Initializing archive overview map');
    
    // Check if Google Maps is available
    if (typeof google === 'undefined' || !google.maps) {
        console.log('PilgrimIRL: Google Maps not available for archive map');
        mapElement.innerHTML = '<div class="map-error"><p>Map loading... Please ensure Google Maps API is configured.</p></div>';
        return;
    }
    
    try {
        // Create map centered on Ireland
        pilgrimMaps.archiveMap = new google.maps.Map(mapElement, {
            center: { lat: 53.1424, lng: -7.6921 }, // Center of Ireland
            zoom: 7,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });
        
        // Load all sites and add markers
        loadAllSitesForMap();
        
        console.log('PilgrimIRL: Archive overview map initialized successfully');
    } catch (error) {
        console.error('PilgrimIRL: Error initializing archive map:', error);
        mapElement.innerHTML = '<div class="map-error"><p>Error loading overview map. Please try refreshing the page.</p></div>';
    }
}

/**
 * Load all sites for the overview map
 */
function loadAllSitesForMap() {
    if (!pilgrimMaps.archiveMap) {
        return;
    }
    
    // Use AJAX to get all sites with coordinates
    jQuery.ajax({
        url: pilgrimirl_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'get_all_sites',
            nonce: pilgrimirl_ajax.nonce
        },
        success: function(response) {
            if (response.success && response.data) {
                pilgrimMaps.allSites = response.data;
                addMarkersToArchiveMap(response.data);
                console.log('PilgrimIRL: Loaded ' + response.data.length + ' sites for overview map');
            } else {
                console.error('PilgrimIRL: Failed to load sites for map');
            }
        },
        error: function(xhr, status, error) {
            console.error('PilgrimIRL: AJAX error loading sites:', error);
        }
    });
}

/**
 * Add markers to the archive overview map
 */
function addMarkersToArchiveMap(sites) {
    if (!pilgrimMaps.archiveMap || !sites) {
        return;
    }
    
    // Clear existing markers
    pilgrimMaps.markers.forEach(marker => marker.setMap(null));
    pilgrimMaps.markers = [];
    
    // Add markers for each site
    sites.forEach(site => {
        if (site.latitude && site.longitude) {
            const marker = new google.maps.Marker({
                position: { lat: site.latitude, lng: site.longitude },
                map: pilgrimMaps.archiveMap,
                title: site.title,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" fill="#2c5530" stroke="white" stroke-width="2"/>
                            <text x="12" y="16" text-anchor="middle" fill="white" font-size="12" font-weight="bold">✝</text>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(24, 24)
                }
            });
            
            // Create info window
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="max-width: 250px;">
                        <h4 style="margin: 0 0 8px 0; color: #2c5530;">
                            <a href="${site.permalink}" style="color: #2c5530; text-decoration: none;">${site.title}</a>
                        </h4>
                        ${site.county && site.county.length > 0 ? `<p style="margin: 0 0 8px 0; font-size: 0.9rem; color: #666;">County: ${site.county[0]}</p>` : ''}
                        ${site.excerpt ? `<p style="margin: 0; font-size: 0.9rem;">${site.excerpt}</p>` : ''}
                        <p style="margin: 8px 0 0 0;">
                            <a href="${site.permalink}" style="color: #2c5530; font-weight: 500;">Learn More →</a>
                        </p>
                    </div>
                `
            });
            
            // Add click listener
            marker.addListener('click', () => {
                infoWindow.open(pilgrimMaps.archiveMap, marker);
            });
            
            pilgrimMaps.markers.push(marker);
        }
    });
    
    console.log('PilgrimIRL: Added ' + pilgrimMaps.markers.length + ' markers to overview map');
}

/**
 * Show specific site on archive map (called from "View on Map" buttons)
 */
function showSiteOnArchiveMap(lat, lng, title) {
    if (!pilgrimMaps.archiveMap) {
        // If map isn't initialized, scroll to map section and initialize
        const mapSection = document.getElementById('archive-map-section');
        if (mapSection) {
            mapSection.scrollIntoView({ behavior: 'smooth' });
            // Try to initialize map after scroll
            setTimeout(() => {
                initArchiveMap();
                setTimeout(() => {
                    if (pilgrimMaps.archiveMap) {
                        showSiteOnArchiveMap(lat, lng, title);
                    }
                }, 1000);
            }, 500);
        }
        return;
    }
    
    // Center map on the site
    const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
    pilgrimMaps.archiveMap.setCenter(position);
    pilgrimMaps.archiveMap.setZoom(15);
    
    // Find and open the marker's info window
    const targetMarker = pilgrimMaps.markers.find(marker => {
        const markerPos = marker.getPosition();
        return Math.abs(markerPos.lat() - position.lat) < 0.0001 && 
               Math.abs(markerPos.lng() - position.lng) < 0.0001;
    });
    
    if (targetMarker) {
        google.maps.event.trigger(targetMarker, 'click');
    }
    
    // Scroll to map
    const mapSection = document.getElementById('archive-map-section');
    if (mapSection) {
        mapSection.scrollIntoView({ behavior: 'smooth' });
    }
}

/**
 * Filter markers on archive map based on current filters
 */
function filterArchiveMapMarkers() {
    if (!pilgrimMaps.archiveMap || !pilgrimMaps.allSites) {
        return;
    }
    
    // Get current filter values
    const countyFilter = document.getElementById('archive-county-filter')?.value || '';
    const siteTypeFilter = document.getElementById('archive-site-type-filter')?.value || '';
    const periodFilter = document.getElementById('archive-period-filter')?.value || '';
    
    // Filter sites based on current filters
    const filteredSites = pilgrimMaps.allSites.filter(site => {
        let matches = true;
        
        if (countyFilter && site.county && site.county.length > 0) {
            matches = matches && site.county.some(county => 
                county.toLowerCase().replace(/\s+/g, '-') === countyFilter
            );
        }
        
        // Add more filter logic as needed
        
        return matches;
    });
    
    // Update markers
    addMarkersToArchiveMap(filteredSites);
}

// Initialize when DOM is ready
jQuery(document).ready(function($) {
    console.log('PilgrimIRL: DOM ready, checking for Google Maps...');
    
    // Try to initialize immediately if Google Maps is already loaded
    if (typeof google !== 'undefined' && google.maps) {
        console.log('PilgrimIRL: Google Maps already loaded, initializing...');
        initPilgrimMaps();
    } else {
        console.log('PilgrimIRL: Waiting for Google Maps to load...');
        // The callback will be handled by the Google Maps API load
    }
    
    // Set up filter change handlers for archive page
    $('#archive-county-filter, #archive-site-type-filter, #archive-period-filter').on('change', function() {
        // Small delay to allow map to update
        setTimeout(filterArchiveMapMarkers, 100);
    });
});

// Make functions globally available
window.initPilgrimMaps = initPilgrimMaps;
window.showSiteOnArchiveMap = showSiteOnArchiveMap;
