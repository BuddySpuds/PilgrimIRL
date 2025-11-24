# Counties Page Troubleshooting Guide

## 🚨 **Current Issues & Solutions**

### **Issue 1: Wrong URL**
**Problem:** You're visiting `localhost:10028/county/` instead of `localhost:10028/counties/`

**Solution:**
- **Correct URL:** `http://localhost:10028/counties/` (with 's')
- **Wrong URL:** `http://localhost:10028/county/` (without 's')

The `/county/` URL is for individual county archives (like County Cork, County Dublin)
The `/counties/` URL is for the overview page showing all counties

### **Issue 2: Page Not Found**
**If `/counties/` shows 404 error:**

1. **Check Page Exists:**
   - WordPress Admin → Pages
   - Look for "Counties" page
   - Make sure it's **Published** (not Draft)

2. **Check Template:**
   - Edit the Counties page
   - In **Page Attributes** → **Template**
   - Should be set to **"Counties Overview"**

3. **Refresh Permalinks:**
   - WordPress Admin → Settings → Permalinks
   - Click **"Save Changes"** (don't change anything, just save)

### **Issue 3: Template Not Available**
**If "Counties Overview" template doesn't appear:**

1. **Check File Location:**
   - File should be: `/wp-content/themes/pilgrimirl/page-counties.php`
   - Make sure it exists and has the right name

2. **Check Template Header:**
   - File should start with:
   ```php
   <?php
   /**
    * Template Name: Counties Overview
    */
   ```

### **Issue 4: Google Maps Not Loading**

**Check API Key:**
1. WordPress Admin → Settings → General
2. Scroll to "Google Maps API Key" field
3. Make sure it's filled in with your actual API key

**Check Browser Console:**
1. Press F12 to open developer tools
2. Go to Console tab
3. Look for Google Maps API errors
4. Common errors:
   - "InvalidKeyMapError" = Wrong API key
   - "RefererNotAllowedMapError" = Domain not allowed
   - "RequestDeniedMapError" = API not enabled

**Check API Settings:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Make sure these APIs are enabled:
   - Maps JavaScript API
   - Places API (optional)
   - Geocoding API (optional)
3. Check API key restrictions
4. Make sure billing is enabled

### **Issue 5: Counties Page is Blank**

**If page loads but shows no content:**

1. **Check Counties Exist:**
   - WordPress Admin → Posts → Counties
   - Should see 32 Irish counties listed
   - If empty, try switching themes and back to PilgrimIRL

2. **Check for PHP Errors:**
   - Look at error logs
   - Or add this to wp-config.php temporarily:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

3. **Check Theme Files:**
   - Make sure `page-counties.php` exists
   - Check that CSS file exists: `/css/county-pages.css`

## 🔧 **Quick Fix Checklist**

1. ✅ **Visit correct URL:** `localhost:10028/counties/` (with 's')
2. ✅ **Page exists** and is published in WordPress admin
3. ✅ **Template is set** to "Counties Overview"
4. ✅ **Permalinks refreshed** (Settings → Permalinks → Save)
5. ✅ **Google Maps API key** added in Settings → General
6. ✅ **Browser console** checked for errors (F12)

## 🆘 **Still Not Working?**

**Try this step-by-step:**

1. **Delete and recreate the page:**
   - Delete the Counties page
   - Create new page with title "Counties"
   - Set template to "Counties Overview"
   - Publish

2. **Check file permissions:**
   - Make sure WordPress can read the theme files
   - Check that `page-counties.php` has proper permissions

3. **Test with simple content:**
   - Edit the Counties page
   - Add some simple text content
   - Save and view
   - If text shows but template doesn't work, there's a template issue

4. **Check parent theme:**
   - Make sure you're using a compatible parent theme
   - Try switching to Twenty Twenty-Four temporarily to test

## 📞 **Debug Information**

**When asking for help, provide:**
- Exact URL you're visiting
- What you see (blank page, 404, error message)
- Browser console errors (F12 → Console)
- WordPress debug log errors
- Whether the Counties page exists in admin
- What template is selected

---

**Most Common Fix:** Visit `localhost:10028/counties/` instead of `localhost:10028/county/`
