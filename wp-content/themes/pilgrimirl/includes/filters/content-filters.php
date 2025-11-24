<?php
/**
 * Content Filtering Functions with Caching
 *
 * Extracts saints and centuries from post content with WP transient caching
 *
 * @package PilgrimIRL
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cache duration: 12 hours (43200 seconds)
define('PILGRIMIRL_CACHE_DURATION', 12 * HOUR_IN_SECONDS);

/**
 * Extract saints from post content and metadata (with caching)
 *
 * @param bool $force_refresh Force cache refresh
 * @return array Array of saints with slug, name, and count
 */
function pilgrimirl_extract_saints_from_content($force_refresh = false) {
    $cache_key = 'pilgrimirl_saints_list';

    // Try to get from cache
    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    $saints = array();
    $saint_counts = array();

    // Get all posts
    $posts = get_posts(array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids' // Only get IDs for better performance
    ));

    // Count saints by actually checking each post with the same logic as filtering
    foreach ($posts as $post_id) {
        $post = get_post($post_id);
        $content = $post->post_content;
        $title = $post->post_title;
        $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
        $all_text = $content . ' ' . $title . ' ' . $communities_provenance;

        // Extract saint names using the same patterns as filtering
        $saint_patterns = array(
            '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
            '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
            '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
        );

        $post_saints = array();
        foreach ($saint_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches)) {
                foreach ($matches[1] as $saint_name) {
                    $saint_name = trim($saint_name);
                    $saint_name_lower = strtolower($saint_name);

                    // Use same validation as the filtering function
                    $false_positives = array(
                        'times', 'until', 'after', 'before', 'during', 'within', 'about',
                        'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
                        'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
                        'century', 'period', 'time', 'year', 'date', 'early', 'late'
                    );

                    if (strlen($saint_name) >= 3 &&
                        strlen($saint_name) <= 20 &&
                        !in_array($saint_name_lower, $false_positives) &&
                        !preg_match('/\d/', $saint_name) &&
                        !preg_match('/[^a-zA-Z\s]/', $saint_name)) {

                        $saint_key = sanitize_title($saint_name);
                        $post_saints[$saint_key] = $saint_name;
                    }
                }
            }
        }

        // Count each saint only once per post
        foreach ($post_saints as $saint_key => $saint_name) {
            if (!isset($saint_counts[$saint_key])) {
                $saint_counts[$saint_key] = array(
                    'name' => $saint_name,
                    'count' => 0
                );
            }
            $saint_counts[$saint_key]['count']++;
        }
    }

    // Convert to format expected by frontend
    foreach ($saint_counts as $slug => $data) {
        if ($data['count'] >= 2) { // Only include saints with at least 2 occurrences
            $saints[] = array(
                'slug' => $slug,
                'name' => 'St. ' . $data['name'],
                'count' => $data['count']
            );
        }
    }

    // Sort by count (descending) then by name
    usort($saints, function($a, $b) {
        if ($a['count'] == $b['count']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['count'] - $a['count'];
    });

    // Cache the results
    set_transient($cache_key, $saints, PILGRIMIRL_CACHE_DURATION);

    return $saints;
}

/**
 * Extract centuries/historical periods from post content (with caching)
 *
 * @param bool $force_refresh Force cache refresh
 * @return array Array of periods with slug, name, and count
 */
function pilgrimirl_extract_centuries_from_content($force_refresh = false) {
    $cache_key = 'pilgrimirl_centuries_list';

    // Try to get from cache
    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    $periods = array();
    $period_counts = array();

    // Get all posts
    $posts = get_posts(array(
        'post_type' => array('monastic_site', 'pilgrimage_route', 'christian_site'),
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids' // Only get IDs for better performance
    ));

    foreach ($posts as $post_id) {
        $post = get_post($post_id);
        $content = $post->post_content;
        $title = $post->post_title;

        // Get communities_provenance and foundation_date meta fields
        $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
        $foundation_date = get_post_meta($post_id, '_pilgrimirl_foundation_date', true);
        $all_text = $content . ' ' . $title . ' ' . $communities_provenance . ' ' . $foundation_date;

        // Century patterns
        $century_patterns = array(
            '/(\d{1,2})(?:st|nd|rd|th)\s+century/i',
            '/(\d{3,4})\s*(?:AD|CE)?/i', // Years
            '/founded\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/built\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/established\s+(?:c\.?\s*)?(\d{3,4})/i'
        );

        foreach ($century_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches)) {
                foreach ($matches[1] as $match) {
                    $year_or_century = intval($match);

                    if ($year_or_century > 20) { // It's a year
                        if ($year_or_century >= 400 && $year_or_century <= 2000) {
                            $century = ceil($year_or_century / 100);
                            $century_name = $century . pilgrimirl_get_ordinal_suffix($century) . ' Century';
                            $century_key = $century . 'th-century';
                        } else {
                            continue;
                        }
                    } else { // It's already a century number
                        if ($year_or_century >= 4 && $year_or_century <= 20) {
                            $century_name = $year_or_century . pilgrimirl_get_ordinal_suffix($year_or_century) . ' Century';
                            $century_key = $year_or_century . 'th-century';
                        } else {
                            continue;
                        }
                    }

                    if (!isset($period_counts[$century_key])) {
                        $period_counts[$century_key] = array(
                            'name' => $century_name,
                            'count' => 0
                        );
                    }
                    $period_counts[$century_key]['count']++;
                }
            }
        }

        // Also look for specific historical periods
        $historical_periods = array(
            'Early Christian' => 'early-christian',
            'Medieval' => 'medieval',
            'Norman' => 'norman',
            'Anglo-Norman' => 'anglo-norman',
            'Gaelic' => 'gaelic',
            'Viking' => 'viking',
            'Reformation' => 'reformation',
            'Dissolution' => 'dissolution'
        );

        foreach ($historical_periods as $period_name => $period_slug) {
            if (stripos($all_text, $period_name) !== false) {
                if (!isset($period_counts[$period_slug])) {
                    $period_counts[$period_slug] = array(
                        'name' => $period_name,
                        'count' => 0
                    );
                }
                $period_counts[$period_slug]['count']++;
            }
        }
    }

    // Convert to format expected by frontend
    foreach ($period_counts as $slug => $data) {
        if ($data['count'] > 0) {
            $periods[] = array(
                'slug' => $slug,
                'name' => $data['name'],
                'count' => $data['count']
            );
        }
    }

    // Sort by name
    usort($periods, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    // Cache the results
    set_transient($cache_key, $periods, PILGRIMIRL_CACHE_DURATION);

    return $periods;
}

/**
 * Get ordinal suffix for numbers
 *
 * @param int $number
 * @return string Ordinal suffix (st, nd, rd, th)
 */
function pilgrimirl_get_ordinal_suffix($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
        return 'th';
    } else {
        return $ends[$number % 10];
    }
}

/**
 * Check if a post contains mentions of a specific saint
 *
 * @param int $post_id
 * @param string $saint_slug
 * @return bool
 */
function pilgrimirl_post_contains_saint($post_id, $saint_slug) {
    $post = get_post($post_id);
    if (!$post) return false;

    $content = $post->post_content;
    $title = $post->post_title;
    $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
    $all_text = $content . ' ' . $title . ' ' . $communities_provenance;

    // Convert saint slug back to name for searching
    $saint_name = ucfirst(str_replace('-', ' ', $saint_slug));

    // Use the same patterns and validation as the extraction function
    $saint_patterns = array(
        '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
    );

    foreach ($saint_patterns as $pattern) {
        if (preg_match_all($pattern, $all_text, $matches)) {
            foreach ($matches[1] as $found_saint) {
                $found_saint = trim($found_saint);
                $found_saint_lower = strtolower($found_saint);
                $saint_name_lower = strtolower($saint_name);

                // Check if this matches our target saint
                if ($found_saint_lower === $saint_name_lower) {
                    // Apply same validation as extraction function
                    $false_positives = array(
                        'times', 'until', 'after', 'before', 'during', 'within', 'about',
                        'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
                        'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
                        'century', 'period', 'time', 'year', 'date', 'early', 'late'
                    );

                    if (strlen($found_saint) >= 3 &&
                        strlen($found_saint) <= 20 &&
                        !in_array($found_saint_lower, $false_positives) &&
                        !preg_match('/\d/', $found_saint) &&
                        !preg_match('/[^a-zA-Z\s]/', $found_saint)) {
                        return true;
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Check if a post contains mentions of a specific century/historical period
 *
 * @param int $post_id
 * @param string $century_slug
 * @return bool
 */
function pilgrimirl_post_contains_century($post_id, $century_slug) {
    $post = get_post($post_id);
    if (!$post) return false;

    $content = $post->post_content;
    $title = $post->post_title;
    $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
    $foundation_date = get_post_meta($post_id, '_pilgrimirl_foundation_date', true);
    $all_text = $content . ' ' . $title . ' ' . $communities_provenance . ' ' . $foundation_date;

    // Handle different century slug formats
    if (preg_match('/(\d+)th-century/', $century_slug, $matches)) {
        $target_century = intval($matches[1]);

        // Check for century mentions
        $century_patterns = array(
            '/(\d{1,2})(?:st|nd|rd|th)\s+century/i',
            '/(\d{3,4})\s*(?:AD|CE)?/i', // Years
            '/founded\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/built\s+(?:c\.?\s*)?(\d{3,4})/i',
            '/established\s+(?:c\.?\s*)?(\d{3,4})/i'
        );

        foreach ($century_patterns as $pattern) {
            if (preg_match_all($pattern, $all_text, $matches_inner)) {
                foreach ($matches_inner[1] as $match) {
                    $year_or_century = intval($match);

                    if ($year_or_century > 20) { // It's a year
                        if ($year_or_century >= 400 && $year_or_century <= 2000) {
                            $century = ceil($year_or_century / 100);
                            if ($century == $target_century) {
                                return true;
                            }
                        }
                    } else { // It's already a century number
                        if ($year_or_century >= 4 && $year_or_century <= 20) {
                            if ($year_or_century == $target_century) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
    } else {
        // Handle historical period names
        $historical_periods = array(
            'early-christian' => 'Early Christian',
            'medieval' => 'Medieval',
            'norman' => 'Norman',
            'anglo-norman' => 'Anglo-Norman',
            'gaelic' => 'Gaelic',
            'viking' => 'Viking',
            'reformation' => 'Reformation',
            'dissolution' => 'Dissolution'
        );

        if (isset($historical_periods[$century_slug])) {
            $period_name = $historical_periods[$century_slug];
            if (stripos($all_text, $period_name) !== false) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Extract saints from a specific post's content
 *
 * @param int $post_id
 * @return array Array of saint names
 */
function pilgrimirl_extract_saints_from_post_content($post_id) {
    $post = get_post($post_id);
    if (!$post) return array();

    $content = $post->post_content;
    $title = $post->post_title;
    $communities_provenance = get_post_meta($post_id, '_pilgrimirl_communities_provenance', true);
    $all_text = $content . ' ' . $title . ' ' . $communities_provenance;

    $saints = array();

    // Use same patterns as the main extraction function
    $saint_patterns = array(
        '/St\.?\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/Saint\s+([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i',
        '/founded\s+by\s+(?:St\.?\s+)?([A-Z][a-z]{2,15}(?:\s+[A-Z][a-z]{2,15})?)\b/i'
    );

    foreach ($saint_patterns as $pattern) {
        if (preg_match_all($pattern, $all_text, $matches)) {
            foreach ($matches[1] as $saint_name) {
                $saint_name = trim($saint_name);
                $saint_name_lower = strtolower($saint_name);

                // Apply same validation as main extraction function
                $false_positives = array(
                    'times', 'until', 'after', 'before', 'during', 'within', 'about',
                    'church', 'abbey', 'monastery', 'priory', 'cathedral', 'chapel',
                    'house', 'order', 'rule', 'community', 'foundation', 'dissolution',
                    'century', 'period', 'time', 'year', 'date', 'early', 'late'
                );

                if (strlen($saint_name) >= 3 &&
                    strlen($saint_name) <= 20 &&
                    !in_array($saint_name_lower, $false_positives) &&
                    !preg_match('/\d/', $saint_name) &&
                    !preg_match('/[^a-zA-Z\s]/', $saint_name)) {
                    $saints[] = 'St. ' . $saint_name;
                }
            }
        }
    }

    // Remove duplicates and return
    return array_unique($saints);
}

/**
 * Clear filter caches (call this when posts are saved/deleted)
 */
function pilgrimirl_clear_filter_caches() {
    delete_transient('pilgrimirl_saints_list');
    delete_transient('pilgrimirl_centuries_list');
}

// Clear caches when posts are saved or deleted
add_action('save_post', 'pilgrimirl_clear_filter_caches');
add_action('delete_post', 'pilgrimirl_clear_filter_caches');
