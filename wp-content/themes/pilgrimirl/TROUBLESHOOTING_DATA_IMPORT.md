# PilgrimIRL Data Import Troubleshooting

## Issue: Cork and Cavan Data Not Uploading

### Quick Diagnosis Steps

1. **Run the Debug Script**
   - Navigate to: `your-site.local/wp-content/themes/pilgrimirl/debug-data-import.php`
   - This will show you exactly what's happening with the file paths and import process

2. **Check File Paths**
   The data importer has been updated to check multiple path locations:
   - `ABSPATH . 'MonasticSites_JSON/'` (WordPress root)
   - `get_home_path() . 'MonasticSites_JSON/'` (Alternative path)

3. **Manual Import via WordPress Admin**
   - Go to: **Tools > PilgrimIRL Import** in your WordPress admin
   - Click "Import Data" button
   - Check the import log for specific error messages

### Common Issues and Solutions

#### Issue 1: File Path Problems
**Symptoms:** Debug script shows "Exists: NO" for JSON directories
**Solution:** 
- Ensure JSON folders are in the correct location
- The folders should be in your WordPress root directory (same level as wp-config.php)

#### Issue 2: File Permission Issues
**Symptoms:** Files exist but can't be read
**Solution:**
```bash
# Fix file permissions (run in terminal)
chmod 644 MonasticSites_JSON/*.json
chmod 644 PilgrimageRoutes_JSON/*.json
```

#### Issue 3: JSON Format Issues
**Symptoms:** "Valid JSON: NO" in debug script
**Solution:**
- Check if the JSON files are properly formatted
- Look for syntax errors in the JSON files

#### Issue 4: Duplicate Posts
**Symptoms:** Import says "0 imported" but files are valid
**Solution:**
- Posts might already exist
- Check existing posts in WordPress admin
- Delete existing posts if you want to re-import

### Manual Import Process

If the automatic import isn't working, you can manually import specific counties:

1. **Access WordPress Admin**
   - Go to Tools > PilgrimIRL Import

2. **Check Import Information**
   - Look at the file count at the bottom of the page
   - Should show "32 JSON files found" for Monastic Sites

3. **Run Import**
   - Click "Import Data"
   - Watch the import log for specific county results

### Debugging Specific Counties

To check if Cork and Cavan data specifically is being processed:

1. **Check File Existence**
   ```php
   // Files should exist at:
   // MonasticSites_JSON/Cork-enriched.json
   // MonasticSites_JSON/Cavan-enriched.json
   ```

2. **Verify JSON Structure**
   Both files should contain an array of objects with these fields:
   - `foundation_name`
   - `county`
   - `coordinates` (with `latitude` and `longitude`)
   - `communities_provenance`
   - `metadata_tags`

3. **Check for Existing Posts**
   - Go to WordPress Admin > Monastic Sites
   - Filter by County: Cork or Cavan
   - If posts already exist, they won't be re-imported

### Advanced Troubleshooting

#### Enable WordPress Debug Mode
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### Check Error Logs
- Look in `/wp-content/debug.log` for PHP errors
- Check server error logs for file permission issues

#### Manual File Check
```bash
# Check if files exist (run in terminal from WordPress root)
ls -la MonasticSites_JSON/Cork-enriched.json
ls -la MonasticSites_JSON/Cavan-enriched.json

# Check file contents
head -20 MonasticSites_JSON/Cork-enriched.json
```

### Expected Import Results

After successful import, you should see:
- **Cork:** ~120+ monastic sites
- **Cavan:** ~10 monastic sites

### Contact Information

If issues persist:
1. Run the debug script and save the output
2. Check the WordPress admin import log
3. Note any specific error messages
4. Check if other counties imported successfully

### Files Modified for This Fix

- `wp-content/themes/pilgrimirl/includes/data-importer.php` - Updated path checking
- `wp-content/themes/pilgrimirl/debug-data-import.php` - New debug script

### Next Steps After Fix

1. Verify all counties have imported correctly
2. Check that county taxonomy terms are created
3. Test the search functionality with Cork and Cavan sites
4. Verify maps are displaying correctly for these counties

---

**Last Updated:** January 24, 2025
**Status:** Path checking improved, debug tools added
