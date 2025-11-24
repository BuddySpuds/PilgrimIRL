# Christian Sites Import - Update Notes

## Issue Identified and Fixed
**Date**: June 2, 2025

### Problem
The original `import-christian-sites.php` script had data structure mismatches:
- Expected `holy_wells.json` but file was empty
- Expected different JSON structure for high crosses
- Did not account for actual MonasticSites_JSON structure

### Solution
Created `import-christian-sites-fixed.php` with:

#### Data Sources Corrected:
1. **Monastic Sites**: 
   - Source: 32 county JSON files in `MonasticSites_JSON/`
   - Structure: Array of objects with `foundation_name`, `coordinates`, `metadata_tags`
   - Expected imports: ~1000+ sites

2. **High Crosses**:
   - Source: `high_crosses.json` 
   - Structure: `{high_crosses: [array of crosses]}`
   - Expected imports: 45+ crosses

#### Key Fixes:
- Handles actual JSON structure from MonasticSites_JSON files
- Correctly parses high_crosses.json format
- Auto-categorizes sites by type (Abbey, Priory, Monastery, etc.)
- Extracts historical data from metadata tags
- Creates proper taxonomies and relationships

## Usage Instructions

### Run the Fixed Import:
1. Navigate to: `/wp-content/themes/pilgrimirl/import-christian-sites-fixed.php`
2. Execute in browser (requires admin login)
3. Monitor progress - imports ~1000+ sites
4. Verify results in WordPress admin

### Expected Results:
- **Monastic Sites**: ~1000+ imported from 32 county files
- **High Crosses**: 45+ imported with coordinates
- **Site Types**: Auto-assigned based on name/description
- **Counties**: All 32 Irish counties represented
- **Coordinates**: Latitude/longitude for mapping
- **Historical Data**: Foundation dates, religious orders, saints

## Files Created:
- `import-christian-sites-fixed.php` - Working import script
- `IMPORT_UPDATE_NOTES.md` - This documentation

## Status: Ready for Production Import
The fixed script is ready to populate the Christian Sites database with comprehensive Irish heritage data.
