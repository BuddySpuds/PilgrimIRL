# Functions.php Refactoring Plan

## Duplicate Functions to Remove

These functions are now in modular includes and must be removed from functions.php:

### From includes/post-types/register-post-types.php
- Line 179: `pilgrimirl_register_post_types()`

### From includes/taxonomies/register-taxonomies.php
- Line 252: `pilgrimirl_register_taxonomies()`
- Line 1328: `pilgrimirl_get_irish_counties()`
- Line 1368: `pilgrimirl_create_default_counties()`

### From includes/filters/content-filters.php
- Line 937: `pilgrimirl_extract_saints_from_content()`
- Line 1027: `pilgrimirl_extract_centuries_from_content()`
- Line 1136: `pilgrimirl_get_ordinal_suffix()`
- Line 1148: `pilgrimirl_post_contains_saint()`
- Line 1203: `pilgrimirl_post_contains_century()`
- Line 1278: `pilgrimirl_extract_saints_from_post_content()`

## Keep in functions.php
- Asset helpers (pilgrimirl_asset, pilgrimirl_version)
- Enqueue functions
- Google Maps setup
- Theme setup
- Meta boxes
- AJAX handlers (get_county_sites, get_all_sites, get_filtered_sites, get_filter_options)
- Save meta box data
- Theme activation hooks
- Data importer includes
