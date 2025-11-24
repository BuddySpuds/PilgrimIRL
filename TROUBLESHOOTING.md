# PilgrimIRL Troubleshooting Guide

## 🚨 Current Issues & Solutions

### Issue 1: County Filtering Not Working

**Problem**: When selecting a county from the dropdown, no filtering occurs.

**Root Cause**: The JavaScript is looking for different element IDs than what's in the HTML.

**Solution**:
1. The front-page.php uses `#filter-county` but JavaScript looks for `#county-filter`
2. Need to ensure AJAX search handler is properly registered
3. Need to verify data has been imported

**Quick Fix**:
- Check if data has been imported via WordPress admin
- Verify AJAX is working by checking browser console for errors

### Issue 2: Menu Items Not Working

**Problem**: Navigation menu items don't work properly.

**Root Cause**: WordPress menus need to be configured in admin.

**Solution**:
1. Go to WordPress Admin → Appearance → Menus
2. Create a new menu called "Primary Navigation"
3. Add these items:
   - Home (link to homepage)
   - Monastic Sites (link to `/monastic-sites/`)
   - Pilgrimage Routes (link to `/pilgrimage-routes/`)
   - Christian Ruins (link to `/christian-ruins/`)
4. Assign menu to "Primary Menu" location
5. Save menu

### Issue 3: Maps Showing Blank

**Problem**: Google Maps not displaying.

**Status**: ✅ **FIXED** - Complete map integration implemented

**Solution Applied**:
- Created dedicated `maps.js` file with full Google Maps integration
- Added AJAX handlers for dynamic site data loading
- Implemented filter functionality for map markers
- Added custom marker icons for different post types

**What's Now Working**:
- County maps display all sites for that county
- Filter buttons work (All Sites, Monastic Sites, Routes, Ruins)
- Custom markers with info windows
- Auto-fit bounds to show all county sites
- Main interactive map with all sites

**Remaining Step**: Add Google Maps API Key
1. Go to WordPress Admin → Settings → General
2. Add your Google Maps API key in the "Google Maps API Key" field
3. Get API key from: https://console.cloud.google.com/

**Files Created/Modified**:
- `wp-content/themes/pilgrimirl/js/maps.js` (NEW)
- `wp-content/themes/pilgrimirl/functions.php` (UPDATED)

See `MAP_FIXES_SUMMARY.md` for complete details.

### Issue 4: Archive Pages Empty

**Problem**: Monastic sites archive shows no content.

**Root Cause**: No data has been imported yet.

**Solution**:
1. First, import your JSON data:
   - Go to WordPress Admin → Tools → PilgrimIRL Import
   - Click "Import Data" button
   - Wait for import to complete

2. If import tool isn't visible:
   - Check that `includes/data-importer.php` file exists
   - Verify functions.php includes the importer
   - Check file permissions

### Issue 5: Search Not Working

**Problem**: Search functionality doesn't return results.

**Root Causes**:
1. AJAX handler not properly registered
2. No data to search through
3. JavaScript errors

**Solutions**:
1. Check browser console for JavaScript errors
2. Verify data has been imported
3. Test AJAX endpoint manually

## 🔧 Step-by-Step Debugging

### 1. Check Data Import Status
```
WordPress Admin → Tools → PilgrimIRL Import
```
- Should show "32 JSON files found" for Monastic Sites
- Should show "1 file found" for Pilgrimage Routes
- Click "Import Data" if not done yet

### 2. Verify Post Types Exist
```
WordPress Admin → Posts → Monastic Sites
```
- Should show imported sites
- If empty, data import failed

### 3. Check Taxonomies
```
WordPress Admin → Monastic Sites → Counties
```
- Should show all 32 Irish counties
- If empty, taxonomies not created properly

### 4. Test Search AJAX
Open browser console and run:
```javascript
jQuery.post(pilgrimirl_ajax.ajax_url, {
    action: 'pilgrimirl_search',
    search_term: 'abbey',
    nonce: pilgrimirl_ajax.nonce
}, function(response) {
    console.log(response);
});
```

### 5. Check Menu Configuration
```
WordPress Admin → Appearance → Menus
```
- Create menu if doesn't exist
- Assign to "Primary Menu" location

## 🚀 Quick Fixes

### Fix 1: Force Rewrite Rules Flush
Add this to functions.php temporarily:
```php
add_action('init', function() {
    flush_rewrite_rules();
});
```
Visit site, then remove the code.

### Fix 2: Enable WordPress Debug
Add to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Fix 3: Check File Permissions
Ensure theme files are readable:
```bash
chmod -R 755 wp-content/themes/pilgrimirl/
```

## 📋 Verification Checklist

After implementing fixes:

- [ ] Theme is activated
- [ ] Data has been imported (check post counts)
- [ ] Counties taxonomy populated
- [ ] Navigation menu configured
- [ ] Google Maps API key added
- [ ] Search returns results
- [ ] Archive pages show content
- [ ] Individual site pages display properly
- [ ] Maps display on site pages

## 🆘 If Still Having Issues

1. **Check WordPress Error Logs**
   - Look in `/wp-content/debug.log`
   - Check server error logs

2. **Browser Console Errors**
   - Open Developer Tools
   - Check Console tab for JavaScript errors

3. **Plugin Conflicts**
   - Temporarily deactivate all plugins
   - Test if issues persist

4. **Theme Conflicts**
   - Switch to default WordPress theme
   - Check if custom post types still exist

## 📞 Common Error Messages

### "No results found"
- Data not imported
- Search term too specific
- AJAX not working

### "Page not found" for archives
- Rewrite rules need flushing
- Post type not registered properly

### Maps not loading
- Missing API key
- API key restrictions too strict
- JavaScript errors preventing initialization

### Menu items not clickable
- Menu not assigned to location
- CSS conflicts
- JavaScript errors

Remember: Most issues stem from data not being imported or WordPress configuration not being complete. Start with the data import and menu setup first!
