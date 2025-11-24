# Google Maps API Setup Guide

## 🗺️ How to Update Your Google Maps API Key

### **Current Issue:**
The Google Maps API key is set to a placeholder and there's a bug in the URL construction.

### **Step 1: Get Your Google Maps API Key**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing project
3. Enable the following APIs:
   - Maps JavaScript API
   - Places API
   - Geocoding API
4. Create credentials → API Key
5. Restrict the API key to your domain for security

### **Step 2: Update functions.php**

**Find this section in `wp-content/themes/pilgrimirl/functions.php` (around line 32):**

```php
/**
 * Enqueue Google Maps API
 */
function pilgrimirl_enqueue_google_maps() {
    $api_key = 'YOUR_GOOGLE_MAPS_API_KEY_HERE'; // Replace with your actual API key
    
    if (is_singular(array('monastic_site', 'pilgrimage_route', 'christian_ruin')) || is_front_page()) {
        wp_enqueue_script(
            'google-maps-api',
            'https://maps.googleapis.com/maps/api/js?key=AIzaSyAE4G7eNRxJT9QtlZNf5y1mRCrlyUtL3bc' . $api_key . '&callback=initPilgrimMaps',
            array(),
            null,
            true
        );
    }
}
```

**Replace it with this CORRECTED version:**

```php
/**
 * Enqueue Google Maps API
 */
function pilgrimirl_enqueue_google_maps() {
    // 🔑 REPLACE THIS WITH YOUR ACTUAL GOOGLE MAPS API KEY
    $api_key = 'YOUR_ACTUAL_API_KEY_HERE';
    
    // Only load maps on relevant pages
    if (is_singular(array('monastic_site', 'pilgrimage_route', 'christian_ruin')) || is_front_page() || is_page('counties') || is_tax('county')) {
        
        // Only enqueue if we have a valid API key
        if ($api_key && $api_key !== 'AIzaSyDQNzyQIt4FvrokzST36ON_zb4Qf-tjpYs') {
            wp_enqueue_script(
                'google-maps-api',
                'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=initPilgrimMaps',
                array(),
                null,
                true
            );
        }
    }
}
```

### **Step 3: What I Fixed**
1. **Removed the duplicate API key** in the URL (there was `AIzaSyAE4G7eNRxJT9QtlZNf5y1mRCrlyUtL3bc` + your key)
2. **Added proper URL construction** with just your API key
3. **Added Places library** for enhanced map functionality
4. **Added county pages** to the conditional loading
5. **Added validation** to only load if API key is set

### **Step 4: Alternative - Use WordPress Options**

For better security, you can also store the API key in WordPress options:

1. **Add this to your functions.php:**
```php
// Add Google Maps API key to WordPress admin
function pilgrimirl_add_maps_settings() {
    add_settings_section(
        'pilgrimirl_maps_settings',
        'Google Maps Settings',
        null,
        'general'
    );
    
    add_settings_field(
        'pilgrimirl_google_maps_api_key',
        'Google Maps API Key',
        'pilgrimirl_maps_api_key_callback',
        'general',
        'pilgrimirl_maps_settings'
    );
    
    register_setting('general', 'pilgrimirl_google_maps_api_key');
}
add_action('admin_init', 'pilgrimirl_add_maps_settings');

function pilgrimirl_maps_api_key_callback() {
    $api_key = get_option('pilgrimirl_google_maps_api_key', '');
    echo '<input type="text" id="pilgrimirl_google_maps_api_key" name="pilgrimirl_google_maps_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
    echo '<p class="description">Enter your Google Maps API key here.</p>';
}
```

2. **Then update the enqueue function to:**
```php
function pilgrimirl_enqueue_google_maps() {
    $api_key = get_option('pilgrimirl_google_maps_api_key', '');
    
    if (is_singular(array('monastic_site', 'pilgrimage_route', 'christian_ruin')) || is_front_page() || is_page('counties') || is_tax('county')) {
        if ($api_key) {
            wp_enqueue_script(
                'google-maps-api',
                'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=initPilgrimMaps',
                array(),
                null,
                true
            );
        }
    }
}
```

3. **Then go to:** WordPress Admin → Settings → General → Google Maps API Key

### **Step 5: Test Your Maps**
After updating the API key:
1. Visit any monastic site, pilgrimage route, or county page
2. Check browser console for any API errors
3. Maps should load properly with your locations

### **Common Issues:**
- **Billing not enabled:** Google requires billing to be enabled
- **API restrictions:** Make sure your domain is allowed
- **Quota exceeded:** Check your API usage limits
- **Wrong APIs enabled:** Ensure Maps JavaScript API is enabled

### **Security Note:**
- Always restrict your API key to your domain
- Consider using server-side geocoding for sensitive operations
- Monitor your API usage regularly

---

**Need help?** Check the browser console (F12) for any Google Maps API errors.
