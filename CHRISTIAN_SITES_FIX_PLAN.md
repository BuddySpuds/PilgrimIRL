# Christian Sites Fix Implementation Plan

## Problem Identified
The Christian Sites archive page is showing Abbey/Monastery data when it should only show:
- Holy Wells
- High Crosses

## Root Cause
1. **Abbey data was incorrectly imported** into the Christian Sites post type (should only be in Monastic Sites)
2. **Holy Wells import hasn't been run yet**
3. **High Crosses need to be extracted** from the monastic sites data

## Solution Implementation

### Phase 1: Data Cleanup ✅
- **Created cleanup script**: `cleanup-christian-sites.php`
- **Purpose**: Remove Abbey/Monastery data from Christian Sites post type
- **Location**: Tools → Cleanup Christian Sites in WordPress admin

### Phase 2: High Crosses Import ✅
- **Created import script**: `import-high-crosses.php`
- **Purpose**: Extract High Cross data from monastic sites JSON files
- **Features**:
  - Searches metadata_tags for cross references
  - Analyzes communities_provenance text for cross mentions
  - Identifies sites with "cross" in the name
  - Creates proper taxonomies (Site Type: High Cross, Period: Early Christian)
- **Location**: Tools → Import High Crosses in WordPress admin

### Phase 3: Holy Wells Import ✅
- **Existing script**: `import-holy-wells.php`
- **Status**: Ready to run
- **Location**: Tools → Import Holy Wells in WordPress admin

### Phase 4: Archive Template Verification ✅
- **Template**: `archive-christian_site.php`
- **Filter Configuration**: Already correctly set to only show:
  ```php
  $allowed_types = array('high-cross', 'holy-well');
  ```

## Execution Steps

### Step 1: Run Cleanup
1. Go to WordPress Admin → Tools → Cleanup Christian Sites
2. Click "Remove Abbey/Monastery Data from Christian Sites"
3. Verify removal in the debug output

### Step 2: Import High Crosses
1. Go to WordPress Admin → Tools → Import High Crosses
2. Click "Import High Crosses"
3. Review imported crosses in the debug output

### Step 3: Import Holy Wells
1. Go to WordPress Admin → Tools → Import Holy Wells
2. Click "Import Holy Wells"
3. Review imported wells in the debug output

### Step 4: Verify Results
1. Visit `/christian-sites/` archive page
2. Confirm only High Crosses and Holy Wells are displayed
3. Test filtering by county and site type
4. Verify map functionality

## Expected Outcome

After running all three steps:
- **Christian Sites archive** will show only High Crosses and Holy Wells
- **Home page Monastic Sites** will continue to show Abbey/Monastery data
- **Proper separation** between content types maintained
- **Filtering and search** will work correctly for each content type

## Files Modified
- ✅ `cleanup-christian-sites.php` - Created
- ✅ `import-high-crosses.php` - Created  
- ✅ `functions.php` - Updated to include new scripts
- ✅ `archive-christian_site.php` - Already correctly configured

## Issues Fixed ✅

### 1. Holy Wells Import Error Fixed
- **Problem**: Undefined array key "imported" error in import script
- **Solution**: Rewrote `import-holy-wells.php` with proper return structure
- **Status**: ✅ Fixed - script now returns proper array with 'imported', 'errors', and 'debug' keys

### 2. Christian Sites Missing from Menu Fixed
- **Problem**: Christian Sites not appearing in navigation menu
- **Solution**: Added Christian Sites to fallback menu in `header.php`
- **Status**: ✅ Fixed - Christian Sites now appears between Monastic Sites and Pilgrimage Routes

### 3. Holy Wells Import Enhanced
- **Improved pattern matching** for better Holy Well detection
- **Better error handling** and debug information
- **Proper taxonomy creation** for Site Type and Historical Period
- **Duplicate prevention** based on post title

## Next Steps
1. Execute the cleanup and import steps in WordPress admin
2. Test the Christian Sites archive page
3. Verify map functionality
4. Confirm Holy Wells appear in filter dropdown
5. Update documentation with final results
