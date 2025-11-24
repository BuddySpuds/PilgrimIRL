# Map Debugging Summary - Cork County Issue

## Problem Identified
Cork county map was only showing sites up to letter "C" alphabetically, despite having 120+ sites in the JSON data.

## Root Cause Analysis
The issue was likely caused by:
1. **Query limitations** - WordPress queries might have been hitting memory or execution limits
2. **Missing error handling** - No debugging information to identify where the query was failing
3. **Response format** - JavaScript wasn't handling potential response format changes

## Fixes Implemented

### 1. Enhanced AJAX Handler (`functions.php`)
- Added comprehensive logging to track query execution
- Added memory usage monitoring
- Enhanced error handling for missing/invalid coordinates
- Added debug information in response
- Added explicit ordering (`orderby` => 'title', `order` => 'ASC`)

### 2. Updated JavaScript (`maps.js`)
- Modified to handle new response format with debug information
- Added better error logging and warnings
- Enhanced console output for debugging

### 3. Created Debug Tool (`debug-county-sites.php`)
- Comprehensive diagnostic tool to test county queries
- Shows exact query arguments and results
- Displays memory usage and performance metrics
- Allows testing different counties
- Shows sites with and without coordinates

## Testing Instructions

### 1. Test Cork County Map
1. Visit Cork county page in browser
2. Open browser console (F12 → Console)
3. Look for debug messages showing:
   - "County sites response" with sites count
   - Debug info with found_posts, processed_count, returned_count
   - Any warnings about missing coordinates

### 2. Use Debug Tool
Visit: `http://your-site.com/wp-content/themes/pilgrimirl/debug-county-sites.php?county=cork`

This will show:
- Query arguments used
- Total posts found vs. posts with coordinates
- Memory usage statistics
- Sample of returned data
- Links to test other counties

### 3. Check Server Logs
Look for entries like:
```
PilgrimIRL: Getting sites for county: cork
PilgrimIRL: County cork - Found X posts, processed Y, returned Z sites, skipped N
```

## Expected Results
- Cork county map should now show ALL 120+ sites
- Console should show debug information confirming all sites loaded
- No more alphabetical cutoff at letter "C"

## Next Steps
1. Test the Cork county page
2. Verify all sites are visible on the map
3. Check console for debug information
4. Use debug tool if issues persist
5. Test other counties to ensure fix is universal

## Files Modified
- `wp-content/themes/pilgrimirl/functions.php` - Enhanced AJAX handler
- `wp-content/themes/pilgrimirl/js/maps.js` - Updated response handling
- `wp-content/themes/pilgrimirl/debug-county-sites.php` - New debug tool

## Debugging Commands
If issues persist, run these in browser console:
```javascript
// Check if Google Maps is loaded
console.log("Google available:", typeof google !== "undefined");

// Check if AJAX object is available
console.log("AJAX object:", pilgrimirl_ajax);

// Check for map containers
console.log("County map element:", document.getElementById('county-map'));
