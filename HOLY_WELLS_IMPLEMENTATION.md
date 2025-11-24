# Holy Wells Implementation

## Overview
This document outlines the implementation of Holy Wells extraction and import functionality for the PilgrimIRL WordPress site.

## Problem Identified
During the Christian Sites implementation, we discovered that Holy Wells were mentioned within the monastic sites data but were not being imported as separate entities. Holy Wells are an important part of Irish Christian heritage and deserve their own dedicated entries.

## Solution Implemented

### 1. Holy Wells Import Script
Created `/wp-content/themes/pilgrimirl/import-holy-wells.php` with the following functionality:

#### Key Functions:
- `import_holy_wells_from_json()` - Main import function
- `extract_holy_wells_from_site()` - Extracts Holy Wells from monastic site data
- `create_holy_well_post()` - Creates WordPress posts for Holy Wells

#### Data Sources:
The script searches for Holy Wells in two places within the monastic sites JSON data:
1. **metadata_tags** array - looks for tags containing "holy well" or "well"
2. **communities_provenance** text - uses regex patterns to find well mentions

#### Extraction Patterns:
- Searches for patterns like "Tubernacool holy well", "Well of Cormac", etc.
- Uses regex: `/([A-Za-z\s]+(?:holy well|well))/i`
- Validates well names to avoid false positives

### 2. WordPress Integration
- Added admin page under Tools → Import Holy Wells
- Integrated with existing Christian Sites post type
- Uses "Holy Well" site type taxonomy
- Preserves coordinates from source monastic sites
- Links back to source site via `_pilgrimirl_source_site` meta field

### 3. Data Structure
Each Holy Well post includes:
- **Title**: Name of the holy well
- **Content**: Description including source site context
- **Taxonomies**:
  - County (inherited from source site)
  - Site Type: "Holy Well"
  - Historical Period: "Early Christian" (default)
- **Meta Fields**:
  - Latitude/Longitude (from source site)
  - Source site reference

## Examples Found in Data

### Antrim County
- **Tubernacool holy well** (associated with Skerry Monastery)
  - Location: Near old church ruins
  - Context: "close by the church is Tubernacool holy well"

### Westmeath County
- **Well of Cormac** (Tobercormick Priory)
  - Alternative names: "Tobar-Cormac", "Fons Cormaci"
  - Historical context: Early monastic site

## Implementation Status

### ✅ Completed
1. Holy Wells extraction script created
2. Admin interface for import process
3. Integration with existing Christian Sites structure
4. Coordinate preservation from source sites
5. Proper taxonomy assignment
6. **FIXED**: Path issue in functions.php (changed get_template_directory to get_stylesheet_directory)

### 🔄 Next Steps
1. Run the import process in WordPress admin
2. Verify Holy Wells appear correctly in site listings
3. Test map integration with Holy Wells
4. Add Holy Wells to search and filter functionality

## Usage Instructions

### For Administrators:
1. Go to WordPress Admin → Tools → Import Holy Wells
2. Click "Import Holy Wells" button
3. Review import results and any errors
4. Check the "Current Holy Wells" section to see imported wells

### For Developers:
The import script can be run programmatically:
```php
$result = import_holy_wells_from_json();
echo "Imported: " . $result['imported'] . " holy wells";
```

## Technical Notes

### Data Validation
- Checks for existing Holy Wells to prevent duplicates
- Validates well names to avoid importing generic "well" references
- Preserves source site relationships for data integrity

### Error Handling
- Comprehensive error logging
- Graceful handling of missing coordinates
- Detailed import statistics

### Performance Considerations
- Processes all county JSON files in sequence
- Memory-efficient processing of large datasets
- Detailed logging for debugging

## Files Modified/Created

### New Files:
- `/wp-content/themes/pilgrimirl/import-holy-wells.php`
- `/HOLY_WELLS_IMPLEMENTATION.md`

### Modified Files:
- `/wp-content/themes/pilgrimirl/functions.php` - Added include for holy wells import

## Data Quality Notes

The Holy Wells data is extracted from historical monastic site records, so:
- Coordinates are approximate (inherited from associated monastic sites)
- Historical context is preserved in descriptions
- Some wells may have multiple alternative names
- Dating is generally Early Christian period

## Future Enhancements

1. **Enhanced Extraction**: Improve regex patterns to catch more well variations
2. **Coordinate Refinement**: Research more precise coordinates for individual wells
3. **Historical Research**: Add more detailed historical information for each well
4. **Photo Integration**: Add historical or contemporary photos where available
5. **Pilgrimage Routes**: Link Holy Wells to relevant pilgrimage routes

## Testing Checklist

- [ ] Import process completes without errors
- [ ] Holy Wells appear in Christian Sites archive
- [ ] Map markers display correctly for Holy Wells
- [ ] Search functionality includes Holy Wells
- [ ] County filtering works with Holy Wells
- [ ] Individual Holy Well pages display properly
- [ ] Source site links work correctly

---

*Last Updated: June 3, 2025*
*Status: Implementation Complete - Ready for Testing*
