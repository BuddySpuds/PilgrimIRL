# PilgrimIRL WordPress Theme

A comprehensive WordPress theme for showcasing Irish pilgrimage walks, routes, and monastic sites across all 32 counties of Ireland.

## Project Overview

This theme creates a dynamic website featuring:
- **Monastic Sites**: Historic monasteries and abbeys
- **Pilgrimage Routes**: Walking paths and sacred journeys
- **Christian Ruins**: Ancient religious structures
- **Interactive Maps**: Google Maps integration with location data
- **County-based Organization**: Content organized by Irish counties
- **Advanced Search**: Filter by county, type, and custom taxonomies

## Theme Structure

### Core Files Created
- `style.css` - Complete CSS styling with Irish-themed design
- `functions.php` - WordPress functionality, custom post types, and taxonomies
- `index.php` - Homepage template with search and featured content
- `header.php` - Site header with navigation and branding
- `footer.php` - Site footer with widgets and links
- `single-monastic_site.php` - Individual monastic site template
- `js/pilgrimirl.js` - Interactive JavaScript functionality
- `includes/data-importer.php` - JSON data import utility

### Custom Post Types
1. **Monastic Sites** (`monastic_site`)
   - Foundation and dissolution dates
   - Associated saints and religious orders
   - Historical information and provenance

2. **Pilgrimage Routes** (`pilgrimage_route`)
   - Distance and difficulty levels
   - Route features and accessibility
   - Pet-friendly information

3. **Christian Ruins** (`christian_ruin`)
   - Historical periods and status
   - Architectural details
   - Preservation information

### Custom Taxonomies
- **County**: All 32 Irish counties
- **Religious Order**: Franciscan, Cistercian, Augustinian, etc.
- **Historical Period**: Early Christian, Medieval, Norman, etc.
- **Site Status**: Active, Ruins, Extant, Dissolved, etc.
- **Difficulty Level**: Easy, Moderate, Difficult, Challenging
- **Pilgrimage Features**: Route characteristics and amenities

### Key Features Implemented

#### 1. Dynamic Search System
- Real-time AJAX search with debouncing
- Filter by county and post type
- Comprehensive results with location data
- Mobile-responsive search interface

#### 2. Interactive Maps
- Google Maps integration for individual sites
- Main overview map showing all locations
- Custom Irish-themed map styling
- Location markers with info windows

#### 3. Data Import System
- Automated JSON data processing
- County-based file organization
- Metadata tag classification
- Admin interface for data import

#### 4. Responsive Design
- Mobile-first CSS approach
- Irish heritage color scheme (greens, golds, earth tones)
- Typography using Cinzel and Open Sans fonts
- Flexible grid layouts

#### 5. Content Organization
- Hierarchical county structure
- Related content suggestions
- Breadcrumb navigation
- Taxonomy-based filtering

## Data Structure

### JSON Data Sources
- **MonasticSites_JSON/**: 32 county-specific JSON files
- **PilgrimageRoutes_JSON/**: Pilgrimage route data

### Expected JSON Schema
```json
{
  "monasticSites": [
    {
      "foundation_name": "Site Name",
      "coordinates": {"lat": 53.123, "lng": -7.456},
      "foundation_date": "12th century",
      "dissolution_date": "1540",
      "communities_provenance": "Historical description",
      "associated_saints": "Saint Name",
      "alternative_names": ["Alt Name 1", "Alt Name 2"],
      "metadata_tags": ["tag1", "tag2"]
    }
  ]
}
```

## Installation & Setup

### 1. Theme Activation
1. Upload theme to `/wp-content/themes/pilgrimirl/`
2. Activate theme in WordPress admin
3. Custom post types and taxonomies will be registered automatically
4. Default counties will be created

### 2. Data Import
1. Navigate to **Tools > PilgrimIRL Import** in WordPress admin
2. Verify JSON files are detected
3. Click "Import Data" to process all files
4. Monitor import log for any issues

### 3. Menu Configuration
1. Go to **Appearance > Menus**
2. Create primary navigation menu
3. Add custom links for:
   - Monastic Sites archive
   - Pilgrimage Routes archive
   - Christian Ruins archive
   - County pages
   - Blog and Community sections

### 4. Google Maps Setup
1. Obtain Google Maps API key
2. Update `js/pilgrimirl.js` with your API key
3. Enable Maps JavaScript API in Google Cloud Console

## Next Steps Required

### Immediate Tasks
1. **Google Maps API Integration**
   - Replace `YOUR_API_KEY` in JavaScript files
   - Test map functionality

2. **Additional Templates**
   - `single-pilgrimage_route.php`
   - `single-christian_ruin.php`
   - `archive-monastic_site.php`
   - `archive-pilgrimage_route.php`
   - `archive-christian_ruin.php`
   - `taxonomy-county.php`

3. **Content Pages**
   - About page
   - Contact page
   - Community forum integration
   - Blog setup

### Advanced Features
1. **User Accounts & Reviews**
   - Visitor reviews and ratings
   - User-generated content
   - Pilgrimage journey tracking

2. **Enhanced Search**
   - Elasticsearch integration
   - Advanced filtering options
   - Saved searches

3. **Mobile App Integration**
   - REST API endpoints
   - Offline map capabilities
   - GPS navigation

4. **Community Features**
   - Forum integration (bbPress)
   - Event calendar
   - Pilgrimage group organization

## Technical Specifications

### WordPress Requirements
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

### Dependencies
- jQuery (included with WordPress)
- Google Maps JavaScript API
- Modern browser with ES6 support

### Performance Considerations
- Image optimization recommended
- Caching plugin suggested
- CDN for map tiles

## File Structure
```
wp-content/themes/pilgrimirl/
├── style.css
├── functions.php
├── index.php
├── header.php
├── footer.php
├── single-monastic_site.php
├── js/
│   └── pilgrimirl.js
├── includes/
│   └── data-importer.php
└── README.md
```

## Support & Documentation

### Custom Fields Reference
- `_pilgrimirl_latitude` - Site latitude
- `_pilgrimirl_longitude` - Site longitude
- `_pilgrimirl_foundation_date` - Foundation date
- `_pilgrimirl_dissolution_date` - Dissolution date
- `_pilgrimirl_communities_provenance` - Historical details
- `_pilgrimirl_associated_saints` - Related saints
- `_pilgrimirl_alternative_names` - Alternative site names

### Hooks & Filters
- `pilgrimirl_search` - AJAX search action
- `after_switch_theme` - Theme activation hooks
- Custom post type and taxonomy registration

## Development Notes

### Code Standards
- WordPress Coding Standards followed
- Proper sanitization and escaping
- Responsive design principles
- Accessibility considerations

### Security Features
- Nonce verification for AJAX requests
- Input sanitization
- Capability checks for admin functions
- XSS prevention

---

**Version**: 1.0.0  
**Last Updated**: January 2025  
**Developer**: WordPress Expert with MCP Integration  
**License**: GPL v2 or later
