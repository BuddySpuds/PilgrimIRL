# Urgent Fixes Required for PilgrimIRL

## Issue 1: County Navigation Not Working
**Problem**: Clicking on county names doesn't navigate, only "Explore..." buttons work
**Root Cause**: Both links use the same `get_term_link($county)` but county name link may have CSS preventing clicks
**Solution**: Check CSS for `.county-name a` blocking pointer events or add explicit click handling

## Issue 2: Data Import Failures
**Problem**: Cork and Down JSON parsing failed despite valid JSON structure
**Import Results**:
- ✅ Cork JSON: 120 valid monastic sites (manually verified)
- ✅ Down JSON: 54 valid monastic sites (manually verified)
- ❌ Import process: "Failed to parse JSON for Cork" and "Failed to parse JSON for Down"

**Root Cause Analysis**:
1. **File Size**: Cork (120 sites) and Down (54 sites) are among the largest files
2. **Memory Limits**: PHP may be hitting memory/execution limits during import
3. **JSON Parsing**: Large JSON arrays may exceed PHP's json_decode limits

## Immediate Solutions Required

### Fix 1: County Navigation
```css
/* Ensure county name links are clickable */
.county-name a {
    pointer-events: auto !important;
    cursor: pointer !important;
}
```

### Fix 2: Data Import - Batch Processing
Split large county files into smaller batches:
- Cork: 120 sites → 4 batches of 30 sites each
- Down: 54 sites → 2 batches of 27 sites each
- Process each batch separately to avoid memory limits

### Fix 3: PHP Memory Optimization
```php
// Increase memory limits for import process
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
```

## Priority Actions
1. **Immediate**: Fix county navigation CSS
2. **Critical**: Implement batch import for large counties
3. **Important**: Add progress tracking for imports
4. **Essential**: Verify all county data is properly imported

## Files to Modify
1. `wp-content/themes/pilgrimirl/css/` - Add county navigation fix
2. `wp-content/themes/pilgrimirl/includes/data-importer.php` - Add batch processing
3. Create batch import utility for Cork and Down specifically

---
*These fixes are critical for site functionality and data completeness*
