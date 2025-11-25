# PilgrimIRL - Hostinger Deployment Guide

**Domain:** pilgrimirl.com
**Hosting:** Hostinger
**Date:** November 24, 2025

---

## 🚀 Deployment Checklist

### Pre-Flight Checks ✈️

#### Local Environment
- [x] Theme security audit passed
- [x] All pages have content
- [x] Google Maps working
- [x] SEO optimization complete
- [x] All CSS/JS minified
- [ ] Test all forms work
- [ ] Test all links work
- [ ] Check all images load
- [ ] Test on mobile devices
- [ ] Backup local database

#### Content Verification
- [ ] All sacred sites have coordinates
- [ ] All images have alt text
- [ ] All pages reviewed for typos
- [ ] Contact information correct
- [ ] Privacy Policy dated correctly
- [ ] Terms of Use reviewed

#### Technical Preparation
- [ ] Database backup created
- [ ] Files prepared for upload
- [ ] wp-config.php ready for editing
- [ ] Google Maps API key ready
- [ ] SSL certificate ready on Hostinger

---

## 📋 Step-by-Step Deployment

### Step 1: Backup Everything Locally

**Create Full Backup:**
```bash
# In your Local by Flywheel folder
cd /Users/robertporter/Local Sites/pilgrimirl/app/public

# Backup database
wp db export backup-$(date +%Y%m%d).sql

# Create files backup
cd ..
tar -czf pilgrimirl-files-$(date +%Y%m%d).tar.gz public/
```

**✅ Verification:**
- Database .sql file created
- Files .tar.gz archive created
- Store backups safely

---

### Step 2: Prepare Hostinger Environment

**Login to Hostinger:**
1. Go to https://hpanel.hostinger.com
2. Navigate to your pilgrimirl.com hosting

**Create MySQL Database:**
1. Go to **Databases** → **MySQL Databases**
2. Click **Create new database**
3. Database name: `u123456789_pilgrimirl` (Hostinger will add prefix)
4. **Save these credentials:**
   ```
   Database Name: u123456789_pilgrimirl
   Database User: u123456789_user
   Database Password: [STRONG PASSWORD]
   Database Host: localhost
   ```

**Enable SSL Certificate:**
1. Go to **SSL** section
2. Enable **Free SSL** for pilgrimirl.com
3. Force HTTPS redirect: **Enabled**
4. Wait 5-10 minutes for activation

---

### Step 3: Export Local Database

**Option A: Via Local by Flywheel:**
1. Open Local app
2. Right-click your site → **Open Site Shell**
3. Run:
```bash
wp db export pilgrimirl-export.sql
```

**Option B: Via phpMyAdmin:**
1. Go to http://localhost:10028/wp-admin/
2. Install **WP Migrate DB** plugin
3. Go to Tools → Migrate DB
4. Click **Export**
5. Download SQL file

**✅ Verification:**
- SQL file size > 50MB (you have lots of sites)
- Open in text editor - should see SQL commands

---

### Step 4: Search & Replace URLs

**CRITICAL: Replace localhost URLs with live domain**

**Using WP-CLI (Recommended):**
```bash
# Make a copy first
cp pilgrimirl-export.sql pilgrimirl-live.sql

# Search and replace
wp search-replace 'http://localhost:10028' 'https://pilgrimirl.com' \
  --export=pilgrimirl-live.sql \
  --all-tables
```

**Using Search-Replace-DB Script:**
1. Download: https://github.com/interconnectit/Search-Replace-DB
2. Upload to Hostinger public_html/srdb/ (temporary folder)
3. Visit: https://pilgrimirl.com/srdb/
4. Enter:
   - Search: `http://localhost:10028`
   - Replace: `https://pilgrimirl.com`
5. Run search/replace
6. **DELETE srdb folder immediately after!**

**Manual Method (if needed):**
```bash
# On your Mac
sed 's|http://localhost:10028|https://pilgrimirl.com|g' \
  pilgrimirl-export.sql > pilgrimirl-live.sql
```

**✅ Verification:**
```bash
grep -c "localhost:10028" pilgrimirl-live.sql
# Should return: 0
```

---

### Step 5: Upload Files to Hostinger

**Method A: FTP (Recommended for large sites)**

**Install FileZilla:**
```
Host: ftp.pilgrimirl.com (or IP from Hostinger)
Username: Your Hostinger FTP username
Password: Your Hostinger FTP password
Port: 21
```

**Upload:**
1. Connect via FTP
2. Navigate to `/public_html/`
3. **Delete default Hostinger WordPress if exists**
4. Upload your entire `/public/` folder contents to `/public_html/`
   - **EXCLUDE:** wp-config.php (will edit later)
   - **EXCLUDE:** /wp-content/uploads/ (optional, keep Hostinger's)
   - **INCLUDE:** /wp-content/themes/pilgrimirl/
   - **INCLUDE:** All other WordPress files

**Method B: File Manager (Hostinger hPanel)**
1. Go to **Files** → **File Manager**
2. Upload files.zip to `/public_html/`
3. Extract files
4. Delete files.zip

**⏱️ Time Estimate:** 30-60 minutes depending on connection

---

### Step 6: Import Database

**Via Hostinger phpMyAdmin:**
1. Go to **Databases** → **phpMyAdmin**
2. Select your database: `u123456789_pilgrimirl`
3. Click **Import** tab
4. Choose file: `pilgrimirl-live.sql`
5. Click **Go**
6. Wait for import (may take 5-10 minutes)

**⚠️ If file too large:**
1. Split the SQL file:
```bash
split -l 50000 pilgrimirl-live.sql pilgrimirl-part-
```
2. Import each part separately

**✅ Verification:**
- Import successful message
- Check table count (should be 12+ tables)
- Browse a table (like `wp_posts`) to see data

---

### Step 7: Configure wp-config.php

**Edit on Hostinger:**
1. Go to **File Manager**
2. Navigate to `/public_html/`
3. Find `wp-config.php`
4. Right-click → **Edit**

**Update Database Settings:**
```php
/** The name of the database for WordPress */
define( 'DB_NAME', 'u123456789_pilgrimirl' );

/** MySQL database username */
define( 'DB_USER', 'u123456789_user' );

/** MySQL database password */
define( 'DB_PASSWORD', 'YOUR_HOSTINGER_DB_PASSWORD' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );
```

**Add These Security Keys:**
Get from: https://api.wordpress.org/secret-key/1.1/salt/

Replace:
```php
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');
```

**Add Performance & Security Settings:**
```php
/** Disable file editing in dashboard */
define('DISALLOW_FILE_EDIT', true);

/** Enable auto-updates */
define('WP_AUTO_UPDATE_CORE', 'minor');

/** Increase memory limit */
define('WP_MEMORY_LIMIT', '256M');

/** Force HTTPS */
define('FORCE_SSL_ADMIN', true);

/** Debug off for production */
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
```

**Save File**

---

### Step 8: Set File Permissions

**Correct Permissions:**
```
Directories: 755
Files: 644
wp-config.php: 640
```

**Via File Manager:**
1. Select `/public_html/`
2. Right-click → **Permissions**
3. Set: `755` for folders, `644` for files
4. **Apply recursively**

**Via SSH (if available):**
```bash
cd /public_html
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 640 wp-config.php
```

---

### Step 9: DNS Configuration

**Update Domain DNS (if needed):**
1. Go to your domain registrar (where you bought pilgrimirl.com)
2. Update nameservers to Hostinger's:
   ```
   ns1.dns-parking.com
   ns2.dns-parking.com
   ```
3. **Wait 24-48 hours for DNS propagation**

**Or use A Record:**
- Get Hostinger IP address from hPanel
- Add A record pointing to that IP

---

### Step 10: Post-Deployment Checks

**Test Website:**
```
✅ https://pilgrimirl.com loads
✅ Homepage displays correctly
✅ Can login to /wp-admin/
✅ All images display
✅ Google Maps work
✅ Filters work on homepage
✅ Calendar displays
✅ Individual sites load
✅ County pages work
✅ Search works
✅ Footer links work
✅ Mobile responsive
```

**Check Admin Area:**
```
✅ Login to https://pilgrimirl.com/wp-admin/
✅ Check Settings → General
✅ Verify permalinks: /%postname%/
✅ Test creating a post
✅ Check theme is active
✅ Verify plugins active
```

**Set Google Maps API Key:**
1. Go to **Settings** → **General**
2. Find "Google Maps API Key"
3. Enter: `AIzaSyDQNzyQIt4FvrokzST36ON_zb4Qf-tjpYs`
4. Save Changes
5. Test a site with map

---

### Step 11: Security Hardening

**Install Security Plugin:**
1. Go to **Plugins** → **Add New**
2. Search for **Wordfence Security**
3. Install and activate
4. Run initial scan

**Hostinger Security:**
1. Enable **Cloudflare** (free tier)
2. Enable **DDoS Protection**
3. Enable **Malware Scanner** (daily scans)
4. Set up **Auto-backups** (weekly minimum)

**Change Admin Password:**
1. Go to **Users** → Your profile
2. Change to strong password
3. Enable **Two-Factor Authentication** (plugin)

---

### Step 12: Performance Optimization

**Install Caching Plugin:**
1. **WP Rocket** (premium, recommended) OR
2. **W3 Total Cache** (free)
3. Configure:
   - Page caching: ✅
   - Browser caching: ✅
   - Minify CSS/JS: ✅ (already minified, but enable)
   - Lazy load images: ✅

**Image Optimization:**
1. Install **ShortPixel** or **Imagify**
2. Bulk optimize existing images
3. Set to auto-optimize uploads

**Database Optimization:**
1. Install **WP-Optimize**
2. Clean post revisions
3. Clean transients
4. Optimize tables

---

### Step 13: SEO Configuration

**Google Search Console:**
1. Go to https://search.google.com/search-console
2. Add property: `https://pilgrimirl.com`
3. Verify ownership (HTML file upload or DNS)
4. Submit sitemap: `https://pilgrimirl.com/sitemap_index.xml`

**Google Analytics:**
1. Go to https://analytics.google.com
2. Create property for pilgrimirl.com
3. Get tracking ID
4. Add to WordPress (via plugin or header)

**Bing Webmaster Tools:**
1. Go to https://www.bing.com/webmasters
2. Add site
3. Submit sitemap

---

### Step 14: Final Verification

**Run These Tests:**

**🔍 Google Tests:**
- [ ] https://search.google.com/test/mobile-friendly
- [ ] https://pagespeed.web.dev/
- [ ] https://search.google.com/test/rich-results

**🔒 Security:**
- [ ] https://www.ssllabs.com/ssltest/ (A+ grade)
- [ ] Check HTTP redirects to HTTPS
- [ ] Test wp-admin login

**⚡ Performance:**
- [ ] GTmetrix.com speed test
- [ ] Check loading time < 3 seconds
- [ ] Test from multiple locations

**🗺️ Functionality:**
- [ ] Test 5 random monastic sites
- [ ] Test 2 pilgrimage routes
- [ ] Test county pages
- [ ] Test calendar
- [ ] Test saints directory
- [ ] Test search
- [ ] Test filters
- [ ] Test maps on 3 sites

---

## 🔧 Troubleshooting

### Site Not Loading
```
1. Check DNS propagation: whatsmydns.net
2. Clear browser cache
3. Check .htaccess file exists
4. Verify WordPress files uploaded
```

### White Screen of Death
```
1. Enable WP_DEBUG in wp-config.php
2. Check error logs in hPanel
3. Verify database connection
4. Check file permissions
```

### Database Connection Error
```
1. Double-check wp-config.php credentials
2. Verify database exists in phpMyAdmin
3. Check database host (should be 'localhost')
4. Test database connection via phpMyAdmin
```

### Images Not Loading
```
1. Check /wp-content/uploads/ permissions (755)
2. Verify images uploaded correctly
3. Check image URLs in database
4. Run search-replace again if needed
```

### Maps Not Working
```
1. Verify Google Maps API key in Settings → General
2. Check browser console for errors
3. Verify API key has no restrictions or allows your domain
4. Check Maps JavaScript API is enabled in Google Console
```

---

## 📱 Mobile Testing

**Test on:**
- [ ] iPhone (Safari)
- [ ] Android (Chrome)
- [ ] iPad (Safari)
- [ ] Desktop (Chrome, Firefox, Safari)

**Check:**
- [ ] Navigation menu works
- [ ] Filters work on mobile
- [ ] Maps are scrollable
- [ ] Images resize properly
- [ ] Text is readable
- [ ] Buttons are tappable

---

## 🎯 Post-Launch Tasks

### Week 1
- [ ] Monitor error logs daily
- [ ] Check Google Search Console for issues
- [ ] Monitor site speed
- [ ] Test all functionality
- [ ] Collect user feedback

### Week 2
- [ ] Review Google Analytics
- [ ] Check search rankings
- [ ] Monitor server resources
- [ ] Review security scans
- [ ] Optimize slow pages

### Month 1
- [ ] Review and respond to any site issues
- [ ] Add new content (blog posts)
- [ ] Build backlinks
- [ ] Improve SEO based on data
- [ ] Plan feature enhancements

---

## 🆘 Emergency Contacts

**Hosting Support:**
- Hostinger Live Chat: 24/7
- Ticket System: hPanel → Support

**DNS Issues:**
- Domain registrar support
- DNS propagation: 24-48 hours normal

**WordPress Issues:**
- WordPress.org forums
- Theme support documentation

---

## ✅ Deployment Complete Checklist

- [ ] Database imported successfully
- [ ] All files uploaded
- [ ] wp-config.php configured
- [ ] SSL certificate active
- [ ] DNS pointing to Hostinger
- [ ] Site loads on https://pilgrimirl.com
- [ ] Can login to wp-admin
- [ ] All pages load correctly
- [ ] Maps work
- [ ] Filters work
- [ ] Mobile responsive
- [ ] Security plugin installed
- [ ] Caching enabled
- [ ] Google Search Console verified
- [ ] Analytics tracking active
- [ ] Backups scheduled
- [ ] Error logs monitoring set up

---

## 🎉 Launch!

When all checks pass:

1. **Announce Launch:**
   - Social media posts
   - Email to interested parties
   - Update any old links

2. **Monitor Closely:**
   - First 24 hours: Check every few hours
   - First week: Check daily
   - First month: Check every few days

3. **Celebrate!** 🎊
   You've successfully launched PilgrimIRL!

---

**Need Help?** Refer to:
- SECURITY_AUDIT_REPORT.md
- SEO_AUDIT.md
- Hostinger documentation
- WordPress Codex

**Deployment Date:** _______________
**Deployed By:** _______________
**Issues Encountered:** _______________
**Resolution:** _______________
