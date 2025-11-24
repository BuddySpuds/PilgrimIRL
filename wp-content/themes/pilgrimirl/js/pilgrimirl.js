/**
 * PilgrimIRL Theme JavaScript
 * Modern, accessible functionality for Irish Pilgrimage and Monastic Sites
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initMobileMenu();
        initSearch();
        initMapFilters();
        initCountyCards();
        initScrollAnimations();
        initAccessibility();
    });

    /**
     * Mobile Menu Functionality
     */
    function initMobileMenu() {
        const mobileToggle = $('.mobile-menu-toggle');
        const navMenu = $('.nav-menu');
        
        if (mobileToggle.length && navMenu.length) {
            mobileToggle.on('click', function(e) {
                e.preventDefault();
                
                const isExpanded = $(this).attr('aria-expanded') === 'true';
                
                // Toggle aria-expanded
                $(this).attr('aria-expanded', !isExpanded);
                
                // Toggle menu visibility
                navMenu.toggleClass('active');
                
                // Trap focus when menu is open
                if (!isExpanded) {
                    trapFocus(navMenu[0]);
                } else {
                    releaseFocus();
                }
            });
            
            // Close menu when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.main-navigation').length) {
                    mobileToggle.attr('aria-expanded', 'false');
                    navMenu.removeClass('active');
                    releaseFocus();
                }
            });
            
            // Close menu on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && navMenu.hasClass('active')) {
                    mobileToggle.attr('aria-expanded', 'false');
                    navMenu.removeClass('active');
                    mobileToggle.focus();
                    releaseFocus();
                }
            });
        }
    }

    /**
     * Enhanced Search Functionality - Fixed IDs
     */
    function initSearch() {
        const searchForm = $('#pilgrim-search-form');
        const searchInput = $('#pilgrim-search-input');
        const countyFilter = $('#filter-county');
        const typeFilter = $('#filter-post-type');
        const resetButton = $('#reset-filters');
        const resultsContainer = $('#pilgrim-search-results');
        
        let searchTimeout;
        
        if (searchForm.length) {
            // Real-time search with debouncing
            searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        performSearch(query);
                    }, 300);
                } else {
                    hideSearchResults();
                }
            });
            
            // Filter change handlers
            countyFilter.add(typeFilter).on('change', function() {
                const query = searchInput.val().trim();
                if (query.length >= 2) {
                    performSearch(query);
                } else if (countyFilter.val() || typeFilter.val()) {
                    // Show filtered results even without search query
                    performSearch('');
                }
            });
            
            // Reset button
            resetButton.on('click', function() {
                searchInput.val('');
                countyFilter.val('');
                typeFilter.val('');
                hideSearchResults();
            });
            
            // Form submission
            searchForm.on('submit', function(e) {
                e.preventDefault();
                const query = searchInput.val().trim();
                performSearch(query);
            });
        }
        
        function performSearch(query) {
            showSearchLoading();
            
            $.ajax({
                url: pilgrimirl_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pilgrimirl_search',
                    search_term: query,
                    county: countyFilter.val(),
                    post_type: typeFilter.val(),
                    nonce: pilgrimirl_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.data);
                    } else {
                        showSearchError('No results found');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Search error:', error);
                    showSearchError('Search failed. Please try again.');
                }
            });
        }
        
        function displaySearchResults(results) {
            const container = resultsContainer;
            container.empty().show();
            
            if (results.length === 0) {
                container.html('<div class="search-loading">No results found. Try adjusting your search terms.</div>');
                return;
            }
            
            results.forEach(function(item) {
                const countyNames = Array.isArray(item.county) ? item.county.join(', ') : '';
                const resultHtml = `
                    <div class="search-result-item" data-lat="${item.latitude}" data-lng="${item.longitude}">
                        <h4><a href="${item.permalink}">${item.title}</a></h4>
                        <p class="result-meta">
                            <span class="post-type-label">${getPostTypeLabel(item.post_type)}</span>
                            ${countyNames ? `<span class="county-label">${countyNames}</span>` : ''}
                        </p>
                        <p class="result-excerpt">${item.excerpt}</p>
                        <div class="result-actions">
                            <a href="${item.permalink}" class="read-more-btn">Read More</a>
                            ${item.latitude && item.longitude ? '<button class="show-on-map-btn" onclick="showOnMap(this)">Show on Map</button>' : ''}
                        </div>
                    </div>
                `;
                container.append(resultHtml);
            });
        }
        
        function showSearchLoading() {
            resultsContainer.html('<div class="search-loading">Searching...</div>').show();
        }
        
        function showSearchError(message) {
            resultsContainer.html('<div class="search-loading">' + message + '</div>').show();
        }
        
        function hideSearchResults() {
            resultsContainer.hide().empty();
        }
        
        function getPostTypeLabel(postType) {
            const labels = {
                'monastic_site': 'Monastic Site',
                'pilgrimage_route': 'Pilgrimage Route',
                'christian_ruin': 'Christian Ruin'
            };
            return labels[postType] || postType;
        }
    }

    /**
     * Map Filter Functionality
     */
    function initMapFilters() {
        $('.map-filter-btn').on('click', function() {
            const $this = $(this);
            const filter = $this.data('type');
            
            // Update active state
            $('.map-filter-btn').removeClass('active');
            $this.addClass('active');
            
            // Here you would integrate with your map library
            console.log('Filtering map by:', filter);
            
            // Add visual feedback
            $this.addClass('loading');
            setTimeout(() => {
                $this.removeClass('loading');
            }, 500);
        });
    }

    /**
     * County Cards Interaction
     */
    function initCountyCards() {
        $('.county-card').on('click', function() {
            const countyLink = $(this).find('.county-link').attr('href');
            if (countyLink) {
                window.location.href = countyLink;
            }
        });
        
        // Add keyboard navigation
        $('.county-card').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });
        
        // Make cards focusable
        $('.county-card').attr('tabindex', '0');
    }

    /**
     * Scroll Animations
     */
    function initScrollAnimations() {
        // Intersection Observer for fade-in animations
        if ('IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe elements that should animate
            $('.feature-card, .county-card, .site-card').each(function() {
                observer.observe(this);
            });
        }
        
        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 600);
            }
        });
    }

    /**
     * Accessibility Enhancements
     */
    function initAccessibility() {
        // Skip link functionality
        $('.sr-only').on('focus', function() {
            $(this).removeClass('sr-only').addClass('skip-link-focus');
        }).on('blur', function() {
            $(this).addClass('sr-only').removeClass('skip-link-focus');
        });
        
        // Announce dynamic content changes to screen readers
        function announceToScreenReader(message) {
            const announcement = $('<div>', {
                'aria-live': 'polite',
                'aria-atomic': 'true',
                'class': 'sr-only'
            }).text(message);
            
            $('body').append(announcement);
            setTimeout(() => announcement.remove(), 1000);
        }
        
        // Make this function globally available
        window.announceToScreenReader = announceToScreenReader;
    }

    /**
     * Focus Management
     */
    function trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select'
        );
        
        const firstFocusableElement = focusableElements[0];
        const lastFocusableElement = focusableElements[focusableElements.length - 1];
        
        if (!firstFocusableElement) return;
        
        element.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        e.preventDefault();
                    }
                }
            }
        });
        
        firstFocusableElement.focus();
    }
    
    function releaseFocus() {
        // Remove any focus trapping event listeners
        // This is a simplified version - in production you'd want to track and remove specific listeners
    }

    /**
     * Global Map Function (for search results)
     */
    window.showOnMap = function(button) {
        const $button = $(button);
        const $item = $button.closest('.search-result-item');
        const lat = $item.data('lat');
        const lng = $item.data('lng');
        
        if (lat && lng) {
            // Here you would integrate with your map library
            console.log('Showing location on map:', lat, lng);
            
            // For now, scroll to map section
            const mapSection = $('.map-section');
            if (mapSection.length) {
                $('html, body').animate({
                    scrollTop: mapSection.offset().top - 100
                }, 600);
            }
            
            // Announce to screen readers
            if (window.announceToScreenReader) {
                window.announceToScreenReader('Location shown on map');
            }
        }
    };

    /**
     * Performance Optimization
     */
    
    // Debounce function for performance
    function debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // Throttle scroll events
    let ticking = false;
    function updateOnScroll() {
        // Add any scroll-based functionality here
        ticking = false;
    }
    
    $(window).on('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    });

    /**
     * Error Handling
     */
    window.addEventListener('error', function(e) {
        console.error('PilgrimIRL Theme Error:', e.error);
        // In production, you might want to send this to an error tracking service
    });

})(jQuery);

/**
 * CSS Custom Properties Support Check
 */
(function() {
    'use strict';
    
    // Check for CSS custom properties support
    if (!window.CSS || !CSS.supports('color', 'var(--fake-var)')) {
        document.documentElement.className += ' no-css-variables';
    }
    
    // Add reduced motion class if user prefers reduced motion
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.className += ' reduce-motion';
    }
})();

/**
 * Initialize Google Maps - Using Vanilla JavaScript (no jQuery dependency)
 */
window.initPilgrimMaps = function() {
    console.log('initPilgrimMaps called');
    
    // Initialize maps on single site pages
    const siteMapElements = document.querySelectorAll('.pilgrim-site-map');
    siteMapElements.forEach(function(mapElement) {
        const lat = parseFloat(mapElement.getAttribute('data-lat'));
        const lng = parseFloat(mapElement.getAttribute('data-lng'));
        const title = mapElement.getAttribute('data-title');
        
        if (lat && lng) {
            const map = new google.maps.Map(mapElement, {
                center: { lat: lat, lng: lng },
                zoom: 15,
                mapTypeId: 'terrain'
            });
            
            const marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                title: title
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: '<h4>' + title + '</h4>'
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
            
            console.log('Site map initialized for:', title);
        }
    });
    
    // Initialize main map on homepage and counties page
    const mainMapElement = document.getElementById('pilgrim-main-map');
    const countiesMapElement = document.getElementById('counties-map');
    
    if (mainMapElement || countiesMapElement) {
        const mapElement = mainMapElement || countiesMapElement;
        const irelandCenter = { lat: 53.1424, lng: -7.6921 };
        
        console.log('Creating main map on element:', mapElement.id);
        
        const mainMap = new google.maps.Map(mapElement, {
            center: irelandCenter,
            zoom: 7,
            mapTypeId: 'terrain',
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });
        
        // Add some sample markers for testing
        const sampleLocations = [
            { lat: 53.3498, lng: -6.2603, title: 'Dublin' },
            { lat: 51.8985, lng: -8.4756, title: 'Cork' },
            { lat: 53.2707, lng: -9.0568, title: 'Galway' },
            { lat: 52.6638, lng: -8.6267, title: 'Limerick' }
        ];
        
        sampleLocations.forEach(function(location) {
            const marker = new google.maps.Marker({
                position: { lat: location.lat, lng: location.lng },
                map: mainMap,
                title: location.title,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#8B4513"/>
                            <circle cx="12" cy="9" r="2.5" fill="white"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(24, 24),
                    anchor: new google.maps.Point(12, 24)
                }
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: '<div style="padding: 5px;"><strong>' + location.title + '</strong><br>Sample pilgrimage location</div>'
            });
            
            marker.addListener('click', function() {
                infoWindow.open(mainMap, marker);
            });
        });
        
        console.log('Google Maps initialized successfully on', mapElement.id);
    } else {
        console.log('No map container found');
    }
};
