# 🚀 Quick Start Deployment Guide

**Get PilgrimIRL live in 30 minutes!**

---

## Pre-Deployment Checklist ✅

Quick checks before you start:

- [x] Theme security audit passed
- [x] SEO optimizations complete
- [x] All pages have content (About, Contact, Privacy, Terms)
- [x] Maps working with API key
- [x] CSS/JS minified
- [ ] Local site tested and working
- [ ] Hostinger account ready
- [ ] Domain (pilgrimirl.com) connected to Hostinger

---

## 🎯 Deployment in 5 Steps

### Step 1: Export Database (5 minutes)
```bash
cd /Users/robertporter/Local\ Sites/pilgrimirl
./deploy-helper.sh
```

This creates:
- `deployment/pilgrimirl-live-YYYYMMDD.sql` - Ready to upload
- `deployment/upload-checklist.txt` - What to upload

**OR manually:**
1. Open Local by Flywheel
2. Right-click site → Open Site Shell
3. Run: `wp db export ~/Desktop/pilgrimirl.sql`

---

### Step 2: Create Database on Hostinger (3 minutes)

1. Login: https://hpanel.hostinger.com
2. Go to: **Databases** → **MySQL Databases**
3. Click: **Create new database**
4. **Save these credentials:**
   ```
   Database Name: u123456789_pilgrimirl
   Username: u123456789_user
   Password: [COPY THIS]
   Host: localhost
   ```

---

### Step 3: Import Database (10 minutes)

1. In hPanel: **Databases** → **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Choose: `pilgrimirl-live-YYYYMMDD.sql`
5. Click **Go**
6. Wait for completion

---

### Step 4: Upload Files (10 minutes)

**Via FTP (FileZilla recommended):**
```
Host: ftp.pilgrimirl.com
Username: [From Hostinger FTP section]
Password: [From Hostinger]
Port: 21
```

Upload to `/public_html/`:
- ✅ `wp-admin/` folder
- ✅ `wp-includes/` folder
- ✅ `wp-content/themes/pilgrimirl/` folder
- ✅ `wp-content/plugins/` folder
- ✅ `index.php`
- ✅ All other .php files
- ✅ `.htaccess`

**DON'T upload:**
- ❌ `wp-config.php` (edit on server)
- ❌ `wp-content/uploads/` (optional)

---

### Step 5: Configure wp-config.php (2 minutes)

1. In File Manager, open `/public_html/wp-config.php`
2. Update database settings:

```php
define( 'DB_NAME', 'u123456789_pilgrimirl' );
define( 'DB_USER', 'u123456789_user' );
define( 'DB_PASSWORD', 'YOUR_PASSWORD_HERE' );
define( 'DB_HOST', 'localhost' );
```

3. Add security settings:
```php
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('WP_DEBUG', false);
```

4. Save file

---

## ✅ Test Your Site

Visit: https://pilgrimirl.com

Check:
- [x] Homepage loads
- [x] Can login: /wp-admin/
- [x] Maps work on a site
- [x] Filters work
- [x] Calendar displays
- [x] Mobile responsive

---

## 🔧 Post-Deployment (5 minutes)

### Set Google Maps API Key:
1. Login to WordPress: `/wp-admin/`
2. Go to: **Settings** → **General**
3. Find: "Google Maps API Key"
4. Enter: `AIzaSyDQNzyQIt4FvrokzST36ON_zb4Qf-tjpYs`
5. Save

### Enable SSL:
1. In hPanel: **SSL** section
2. Enable **Free SSL**
3. Force HTTPS: **ON**

### Submit to Google:
1. Visit: https://search.google.com/search-console
2. Add property: `https://pilgrimirl.com`
3. Verify ownership
4. Submit sitemap: `/sitemap_index.xml`

---

## 🆘 Quick Troubleshooting

**Database connection error:**
- Check wp-config.php credentials
- Verify database name/user/password
- Try host: `localhost` or get from hPanel

**White screen:**
- Enable debug in wp-config.php temporarily
- Check hPanel error logs
- Verify all files uploaded

**Maps not working:**
- Set API key in Settings → General
- Check browser console for errors
- Verify API enabled in Google Console

---

## 📚 Full Documentation

For detailed instructions:
- **Comprehensive Guide:** `DEPLOYMENT_GUIDE.md`
- **Security:** `SECURITY_AUDIT_REPORT.md`
- **SEO:** `SEO_AUDIT.md`

---

## 🎉 You're Live!

Once everything checks out:

1. **Secure Your Site:**
   - Install Wordfence Security plugin
   - Change admin password
   - Enable auto-backups in hPanel

2. **Optimize Performance:**
   - Install caching plugin (WP Rocket or W3 Total Cache)
   - Install image optimization (ShortPixel)
   - Enable Cloudflare in hPanel

3. **Monitor:**
   - Check Google Search Console daily
   - Set up Google Analytics
   - Review Hostinger security scans

**Congratulations! PilgrimIRL is live! 🚀**

---

**Need help?** Check:
- Hostinger 24/7 live chat
- WordPress.org forums
- Full DEPLOYMENT_GUIDE.md in theme folder
