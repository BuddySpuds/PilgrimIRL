<?php
/**
 * SEO Enhancements for PilgrimIRL
 *
 * Adds structured data and meta improvements for better search engine visibility
 */

// Add structured data for sacred sites
function pilgrimirl_add_structured_data() {
    if (!is_singular(array('monastic_site', 'pilgrimage_route', 'christian_site'))) {
        return;
    }

    global $post;

    $post_type = get_post_type();
    $latitude = get_post_meta($post->ID, '_pilgrimirl_latitude', true);
    $longitude = get_post_meta($post->ID, '_pilgrimirl_longitude', true);
    $address = get_post_meta($post->ID, '_pilgrimirl_address', true);
    $counties = wp_get_post_terms($post->ID, 'county');
    $excerpt = get_the_excerpt();
    $image_url = get_the_post_thumbnail_url($post->ID, 'large');

    // Build structured data
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'TouristAttraction',
        'name' => get_the_title(),
        'description' => $excerpt ? $excerpt : wp_trim_words(get_the_content(), 30),
        'url' => get_permalink(),
    );

    // Add image
    if ($image_url) {
        $schema['image'] = $image_url;
    }

    // Add coordinates
    if ($latitude && $longitude) {
        $schema['geo'] = array(
            '@type' => 'GeoCoordinates',
            'latitude' => $latitude,
            'longitude' => $longitude,
        );
    }

    // Add address
    if ($address || !empty($counties)) {
        $schema['address'] = array(
            '@type' => 'PostalAddress',
        );

        if ($address) {
            $schema['address']['streetAddress'] = $address;
        }

        if (!empty($counties)) {
            $schema['address']['addressRegion'] = $counties[0]->name;
            $schema['address']['addressCountry'] = 'IE';
        }
    }

    // Additional properties based on post type
    if ($post_type === 'monastic_site') {
        $foundation_date = get_post_meta($post->ID, '_pilgrimirl_foundation_date', true);
        $associated_saints = get_post_meta($post->ID, '_pilgrimirl_associated_saints', true);

        $schema['touristType'] = 'Religious Site';
        $schema['additionalType'] = 'https://schema.org/ReligiousSite';

        if ($foundation_date) {
            $schema['foundingDate'] = $foundation_date;
        }

        if ($associated_saints) {
            $schema['keywords'] = $associated_saints;
        }
    } elseif ($post_type === 'pilgrimage_route') {
        $distance = get_post_meta($post->ID, '_pilgrimirl_distance', true);

        $schema['@type'] = array('TouristAttraction', 'Trail');
        $schema['additionalType'] = 'https://schema.org/Trail';
        $schema['touristType'] = 'Pilgrimage Route';

        if ($distance) {
            $schema['distance'] = $distance . ' km';
        }
    } elseif ($post_type === 'christian_site') {
        $schema['touristType'] = 'Christian Heritage Site';
        $schema['additionalType'] = 'https://schema.org/ReligiousSite';
    }

    // Add opening hours (all sites are outdoor/always accessible)
    $schema['openingHoursSpecification'] = array(
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => array(
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
        ),
        'opens' => '00:00',
        'closes' => '23:59',
    );

    // Add accessibility note
    $schema['publicAccess'] = true;
    $schema['isAccessibleForFree'] = true;

    // Output JSON-LD
    echo '<script type="application/ld+json">' . PHP_EOL;
    echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    echo PHP_EOL . '</script>' . PHP_EOL;
}
add_action('wp_head', 'pilgrimirl_add_structured_data', 15);

/**
 * Improve meta descriptions for different page types
 */
function pilgrimirl_improve_meta_descriptions() {
    // Let Yoast handle this if active
    if (defined('WPSEO_VERSION')) {
        return;
    }

    $description = '';

    if (is_singular('monastic_site')) {
        $counties = wp_get_post_terms(get_the_ID(), 'county');
        $county_name = !empty($counties) ? $counties[0]->name : 'Ireland';
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 25);
        $description .= ' | Historic monastic site in ' . $county_name . ' | PilgrimIRL';
    } elseif (is_singular('pilgrimage_route')) {
        $distance = get_post_meta(get_the_ID(), '_pilgrimirl_distance', true);
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 25);
        if ($distance) {
            $description .= ' | ' . $distance . 'km pilgrimage route';
        }
        $description .= ' | PilgrimIRL';
    } elseif (is_singular('christian_site')) {
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 25);
        $description .= ' | Christian heritage site in Ireland | PilgrimIRL';
    } elseif (is_post_type_archive('monastic_site')) {
        $description = 'Explore Ireland\'s historic monastic sites - abbeys, priories, and monasteries founded by Irish saints from the 5th century onwards.';
    } elseif (is_post_type_archive('pilgrimage_route')) {
        $description = 'Discover pilgrimage routes across Ireland - sacred walking paths connecting monastic sites and places of spiritual significance.';
    } elseif (is_post_type_archive('christian_site')) {
        $description = 'Browse Christian heritage sites across Ireland - churches, holy wells, and places of spiritual significance.';
    } elseif (is_tax('county')) {
        $term = get_queried_object();
        $description = 'Explore sacred sites, monasteries, and pilgrimage routes in County ' . $term->name . ' | PilgrimIRL Irish Heritage';
    }

    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . PHP_EOL;
    }
}
add_action('wp_head', 'pilgrimirl_improve_meta_descriptions', 5);

/**
 * Add canonical URLs
 */
function pilgrimirl_add_canonical() {
    // Let Yoast handle this if active
    if (defined('WPSEO_VERSION')) {
        return;
    }

    if (is_singular()) {
        $canonical = get_permalink();
    } elseif (is_post_type_archive()) {
        $canonical = get_post_type_archive_link(get_query_var('post_type'));
    } elseif (is_tax()) {
        $term = get_queried_object();
        $canonical = get_term_link($term);
    } elseif (is_front_page()) {
        $canonical = home_url('/');
    } else {
        return;
    }

    if ($canonical && !is_wp_error($canonical)) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . PHP_EOL;
    }
}
add_action('wp_head', 'pilgrimirl_add_canonical', 5);

/**
 * Improve page titles for SEO
 */
function pilgrimirl_improve_title($title) {
    // Let Yoast handle this if active
    if (defined('WPSEO_VERSION')) {
        return $title;
    }

    if (is_singular('monastic_site')) {
        $counties = wp_get_post_terms(get_the_ID(), 'county');
        $county_name = !empty($counties) ? $counties[0]->name : '';
        if ($county_name) {
            return get_the_title() . ' - ' . $county_name . ' | PilgrimIRL Monastic Sites';
        }
        return get_the_title() . ' | PilgrimIRL Monastic Sites';
    } elseif (is_singular('pilgrimage_route')) {
        return get_the_title() . ' | Pilgrimage Routes Ireland | PilgrimIRL';
    } elseif (is_singular('christian_site')) {
        return get_the_title() . ' | Christian Heritage Sites | PilgrimIRL';
    } elseif (is_post_type_archive('monastic_site')) {
        return 'Irish Monastic Sites - Abbeys & Monasteries | PilgrimIRL';
    } elseif (is_post_type_archive('pilgrimage_route')) {
        return 'Pilgrimage Routes in Ireland | Sacred Walking Paths | PilgrimIRL';
    } elseif (is_post_type_archive('christian_site')) {
        return 'Christian Heritage Sites in Ireland | PilgrimIRL';
    } elseif (is_tax('county')) {
        $term = get_queried_object();
        return 'County ' . $term->name . ' - Sacred Sites & Monasteries | PilgrimIRL';
    }

    return $title;
}
add_filter('pre_get_document_title', 'pilgrimirl_improve_title', 20);

/**
 * Add Open Graph improvements
 */
function pilgrimirl_improve_og_tags() {
    // Let Yoast handle this if active
    if (defined('WPSEO_VERSION')) {
        return;
    }

    if (is_singular(array('monastic_site', 'pilgrimage_route', 'christian_site'))) {
        $image = get_the_post_thumbnail_url(get_the_ID(), 'large');

        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . PHP_EOL;
            echo '<meta property="og:image:width" content="1200" />' . PHP_EOL;
            echo '<meta property="og:image:height" content="630" />' . PHP_EOL;
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . PHP_EOL;
        }

        // Add article published/modified times
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '" />' . PHP_EOL;
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '" />' . PHP_EOL;

        // Add geo tags
        $latitude = get_post_meta(get_the_ID(), '_pilgrimirl_latitude', true);
        $longitude = get_post_meta(get_the_ID(), '_pilgrimirl_longitude', true);

        if ($latitude && $longitude) {
            echo '<meta property="place:location:latitude" content="' . esc_attr($latitude) . '" />' . PHP_EOL;
            echo '<meta property="place:location:longitude" content="' . esc_attr($longitude) . '" />' . PHP_EOL;
        }
    }
}
add_action('wp_head', 'pilgrimirl_improve_og_tags', 10);

/**
 * Add hreflang tags for international SEO (future expansion)
 */
function pilgrimirl_add_hreflang() {
    if (is_singular() || is_front_page()) {
        $url = is_front_page() ? home_url('/') : get_permalink();
        echo '<link rel="alternate" hreflang="en-ie" href="' . esc_url($url) . '" />' . PHP_EOL;
        echo '<link rel="alternate" hreflang="en-gb" href="' . esc_url($url) . '" />' . PHP_EOL;
        echo '<link rel="alternate" hreflang="en-us" href="' . esc_url($url) . '" />' . PHP_EOL;
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($url) . '" />' . PHP_EOL;
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($url) . '" />' . PHP_EOL;
    }
}
add_action('wp_head', 'pilgrimirl_add_hreflang', 5);
