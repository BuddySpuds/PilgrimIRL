# Development Pain Points and Solutions - WildRover Project

## Overview
This document captures critical issues encountered during the WildRover site recovery and deployment on June 23, 2025, along with proven solutions to prevent these issues in future development work.

---

## 1. Security Incident & Recovery

### Pain Point
- **Issue**: Site was compromised with malware (`7c2f4f5ff1b8e45a7631e04e7b79d8a3mainhostingwsoyanz.php`)
- **Location**: Suspicious plugin directory `/wp-content.7777/plugins/410b7b89881d66e7f09a3b799cc222d221/`
- **Impact**: Lost admin access, potential data breach

### Solution
- **Clean reinstall from GitHub**: Having code in version control saved the project
- **Automated deployment pipeline**: Reduced manual FTP access points
- **Regular security scans**: Implement automated scanning

### Prevention for Future Projects
```yaml
# Add to project setup checklist:
1. Enable 2FA on all accounts (hosting, WordPress, GitHub)
2. Use strong, unique passwords (20+ characters)
3. Install security plugin from day one
4. Set up automated backups
5. Document clean recovery procedure
6. Never commit credentials to repository
```

---

## 2. FTP Deployment Creating Nested Folders

### Pain Point
- **Issue**: GitHub Actions created `/public_html/public_html/` structure
- **Root Cause**: Multiple issues:
  1. Hostinger FTP starts in public_html, but workflow specified `public_html/` in server-dir
  2. Local WordPress file structure had `app/public/` which was being replicated
  3. Deploying root WordPress files created nested structure
- **Discovery**: Files in Git were under `app/public/wp-content/` but needed to deploy to `wp-content/`
- **Impact**: Files deployed to wrong location, site broken

### Solution
```yaml
# WRONG - Creates nested folder
server-dir: public_html/wp-content/themes/...

# ALSO WRONG - Deploying root files
- name: FTP Deploy Root Files
  local-dir: ./app/public/
  server-dir: ./

# CORRECT - Deploy only specific subdirectories
server-dir: ./wp-content/themes/...
```

### Specific Fix Applied
1. **Disabled problematic workflow**: Renamed `deploy-fixed.yml` to `deploy-fixed.yml.disabled`
2. **Updated deployment paths**: Changed all `server-dir` to use `./` relative paths
3. **Removed root file deployment**: Only deploy wp-content subdirectories
4. **Key insight**: The local file structure (`app/public/`) doesn't need to match production

### Prevention for Future Projects
- Always use relative paths (`./`) in FTP deployment
- NEVER deploy WordPress root files (wp-admin, wp-includes, etc.)
- Only deploy themes, plugins, and mu-plugins directories
- Test deployment with a marker file first
- Document hosting provider's FTP structure
- Include working deployment config in project template
- Remember: Git structure doesn't need to match server structure

---

## 3. WordPress Template Recognition Issues

### Pain Point
- **Issue**: Custom page templates not appearing in WordPress admin dropdown
- **Attempts**: Multiple template files created but not recognized
- **Impact**: Couldn't use custom templates, had to use workarounds

### Solution
- Use WordPress template hierarchy correctly:
  - `front-page.php` - Highest priority for homepage
  - `page-{slug}.php` - Automatically used for specific page slugs
  - `archive-{post-type}.php` - For custom post type archives

### Prevention for Future Projects
```php
// Always include proper template header
<?php
/**
 * Template Name: Custom Template
 * Description: Template description
 */
?>

// For specific pages, use slug-based naming:
page-about.php      // Automatically used for /about/
page-contact.php    // Automatically used for /contact/
```

---

## 4. Custom Post Type Archive URL Issues

### Pain Point
- **Issue**: `/treks/` URL returned 404 despite post type registered with `has_archive`
- **Local worked**: localhost:10018/treks/ worked fine
- **Production failed**: wildrover.life/treks/ showed 404

### Solution
- Created must-use plugin to force archive recognition:
```php
<?php
// wp-content/mu-plugins/fix-trek-archive.php
add_action('init', function() {
    global $wp_post_types;
    if (isset($wp_post_types['trek'])) {
        $wp_post_types['trek']->has_archive = 'treks';
    }
}, 20);
```

### Prevention for Future Projects
- Always flush permalinks after deploying custom post types
- Include permalink flushing in deployment process
- Test archive URLs immediately after deployment
- Consider using must-use plugins for critical functionality

---

## 5. Git Command Line Issues

### Pain Point
- **Issue**: Permission denied errors when adding files
- **Terminal confusion**: Multi-line commands copied incorrectly
- **Impact**: Delayed deployments, frustration

### Solution
```bash
# Instead of complex paths:
git add -A  # Adds all files at once

# Clear, single commands:
git add -A
git commit -m "message"
git push
```

### Prevention for Future Projects
- Use simple git commands
- Document exact commands in README
- Consider GUI tools for non-technical users
- Set up proper .gitignore from start

---

## 6. Mobile UI/UX Problems

### Pain Point
- **Issue**: Poor mobile experience (green menu button, cramped filters)
- **Discovery**: Only noticed when testing on actual device
- **Impact**: Poor user experience for mobile users

### Solution
- Created dedicated mobile CSS files
- Implemented touch-friendly design (44px minimum touch targets)
- Improved spacing and visual hierarchy

### Prevention for Future Projects
```css
/* Mobile-first development approach */
@media (max-width: 768px) {
    /* Minimum touch target size */
    button, a, input, select {
        min-height: 44px;
    }
    
    /* Proper spacing */
    .element {
        margin-bottom: 20px;
        padding: 16px;
    }
}
```

---

## 7. Missing Dependencies in Deployment

### Pain Point
- **Issue**: Weather widget plugin not included in deployment workflow
- **Discovery**: Plugin missing on production after deployment
- **Impact**: Feature not working on live site

### Solution
- Updated deployment workflow to include all plugins:
```yaml
- name: FTP Deploy Weather Widget
  uses: SamKirkland/FTP-Deploy-Action@v4.3.5
  with:
    local-dir: ./app/public/wp-content/plugins/wildrover-weather-widget/
    server-dir: ./wp-content/plugins/wildrover-weather-widget/
```

### Prevention for Future Projects
- Document ALL components in deployment checklist
- Create comprehensive deployment workflow from start
- Test deployment on staging environment first
- Maintain deployment manifest file

---

## 8. Database Migration Complexity

### Pain Point
- **Issue**: No automated database deployment
- **Manual process**: Export, import, URL replacement
- **Risk**: Data inconsistency between environments

### Solution
- Used WordPress XML export/import for content
- Kept database changes minimal
- Used JSON imports for structured data

### Prevention for Future Projects
```bash
# Document database migration process
wp db export local.sql
wp search-replace 'http://localhost' 'https://domain.com' --export=production.sql

# Consider database-free solutions:
- Use JSON/API for dynamic content
- Minimize database dependencies
- Use WordPress REST API
```

---

## 9. Lost Changes Without Backup

### Pain Point
- **Issue**: No recent backup when malware found
- **Recovery**: Had to rebuild from GitHub + old data
- **Impact**: Lost recent content updates

### Solution
- Implemented multiple backup strategies:
  1. GitHub for code
  2. Regular database exports
  3. WordPress XML exports
  4. Screenshot documentation

### Prevention for Future Projects
```yaml
# Automated backup strategy
daily:
  - Database backup to cloud
  - WordPress XML export
  - Media files sync
  
weekly:
  - Full site backup
  - Test restore procedure
  
monthly:
  - Archive to cold storage
```

---

## 10. Environment Configuration Differences

### Pain Point
- **Issue**: Local environment worked, production failed
- **Differences**: PHP versions, server configurations, file paths
- **Debugging**: Difficult without SSH access

### Solution
- Created diagnostic tools:
```php
// Diagnostic shortcode for production debugging
[wildrover_diagnostic]
```

### Prevention for Future Projects
- Document all environment requirements
- Create environment parity checklist
- Build diagnostic tools from start
- Use Docker for consistent environments

---

## Project Template Checklist

Based on these pain points, here's a checklist for future projects:

```markdown
## Project Setup Checklist

### Security
- [ ] Strong passwords on all accounts
- [ ] 2FA enabled everywhere
- [ ] Security plugin installed
- [ ] .gitignore properly configured
- [ ] No credentials in repository

### Deployment
- [ ] GitHub Actions workflow tested
- [ ] FTP paths verified (use ./ not public_html/)
- [ ] All components in deployment pipeline
- [ ] Deployment documentation complete
- [ ] Test deployment with marker file

### WordPress
- [ ] Custom post types with proper permalinks
- [ ] Must-use plugins for critical features
- [ ] Template hierarchy documented
- [ ] Mobile-first CSS approach
- [ ] Diagnostic tools included

### Backup & Recovery
- [ ] Automated backup system
- [ ] Recovery procedure documented
- [ ] GitHub repository current
- [ ] Database export automated
- [ ] Clean install guide ready

### Testing
- [ ] Mobile device testing
- [ ] Production URL testing
- [ ] Security scan scheduled
- [ ] Performance benchmarks
- [ ] Cross-browser checks
```

---

## Key Lessons Learned

1. **Version Control Saves Projects**: Having GitHub deployment ready made recovery from hack possible
2. **Document Everything**: Especially deployment paths and environment differences
3. **Test on Production Early**: Don't assume local = production
4. **Mobile-First**: Always design for mobile from the start
5. **Security First**: Implement security before going live
6. **Simple Commands**: Use simple, clear commands for deployment
7. **Diagnostic Tools**: Build debugging capabilities into the project
8. **Backup Strategy**: Multiple backup methods prevent total loss
9. **Clean Architecture**: Separation of concerns makes recovery easier
10. **Community Tools**: Use established tools (WordPress standards) vs custom solutions

---

## Recommended Project Structure

```
project/
├── .github/
│   └── workflows/
│       └── deploy.yml          # Working deployment config
├── docs/
│   ├── deployment/
│   ├── security/
│   └── troubleshooting/
├── backup/
│   └── recovery-plan.md
├── app/public/                 # WordPress root
├── .gitignore                  # Properly configured
├── README.md                   # Clear setup instructions
├── DEPLOYMENT.md              # Step-by-step deployment
└── TROUBLESHOOTING.md         # Common issues & solutions
```

---

## Tools That Worked Well

1. **GitHub Actions + FTP Deploy**: Automated deployment
2. **Must-use plugins**: Reliable WordPress modifications
3. **WordPress CLI**: Database operations and testing
4. **Version Control**: Code recovery and history
5. **Diagnostic Shortcodes**: Production debugging
6. **Template Hierarchy**: Reliable page customization
7. **CSS-based Solutions**: Avoiding complex PHP when possible

---

## Final Recommendations

1. **Start with security**: It's harder to add later
2. **Test deployment early**: Not just before launch
3. **Document as you go**: Not after problems occur
4. **Use standard solutions**: WordPress conventions exist for reasons
5. **Plan for failure**: Have recovery procedures ready
6. **Monitor actively**: Don't wait for users to report issues
7. **Keep it simple**: Complex solutions break more often

---

## 11. Mobile Google Maps Rendering Issues

### Pain Point
- **Issue**: Interactive map showed only markers on mobile, no map tiles
- **Symptoms**: Map container visible but gray/blank, markers floating in space
- **Root Cause**: Map container had no height, Google Maps initialization timing issues
- **Impact**: Core feature unusable on mobile devices

### Solution
```css
/* Force map container dimensions */
#trek-map {
    width: 100% !important;
    height: 400px !important;
    min-height: 400px !important;
}
```

```javascript
// Force re-initialization on mobile
if (mapContainer.offsetHeight === 0) {
    mapContainer.style.height = '400px';
    initMap();
}
```

### Prevention for Future Projects
- Always set explicit height for map containers on mobile
- Use multiple initialization fallbacks
- Test on actual mobile devices, not just browser DevTools
- Consider using inline styles for critical dimensions
- Add orientation change handlers for map resize

---

## 12. Inline Failsafe Strategy

### Pain Point
- **Issue**: CSS/JS files not loading due to caching or deployment delays
- **Discovery**: Changes not visible despite successful deployment
- **Impact**: Users see broken UI even after fixes deployed

### Solution
- Created must-use plugin with inline CSS/JS:
```php
// wp-content/mu-plugins/mobile-ui-fixes-inline.php
add_action('wp_head', function() {
    if (wp_is_mobile()) {
        ?><style>/* Critical CSS here */</style><?php
    }
});
```

### Prevention for Future Projects
- Include inline CSS for critical fixes in mu-plugins
- Use cache-busting version numbers: `style.css?v=<?php echo time(); ?>`
- Create diagnostic pages to verify what's loaded
- Document cache clearing procedures
- Consider inline styles for emergency fixes

---

*Document created: June 23, 2025*
*Updated: June 24, 2025 - Added mobile map rendering and inline failsafe sections*
*Based on: WildRover site recovery and deployment experience*
*Purpose: Prevent similar issues in future projects*