# Quick Start: Create Liturgical Calendar Page

The calendar template and data are ready - you just need to create the WordPress page!

---

## 🚀 Quick 3-Step Setup

### Step 1: Create Page
1. Go to: http://localhost:10028/wp-admin/post-new.php?post_type=page
2. In the title field, type: **Liturgical Calendar**
3. Click on the permalink "Edit" button and change it to: **calendar**
4. **Leave the content area completely empty** (the template handles everything)

### Step 2: Select Template
1. Look in the right sidebar for **"Page Attributes"** box
2. Find the **"Template"** dropdown
3. Select: **"Liturgical Calendar"**
4. Click **"Publish"** button (top right, blue button)

### Step 3: Verify
Visit: http://localhost:10028/calendar/

You should now see:
- ✅ Green gradient header with "Irish Catholic Liturgical Calendar 2025"
- ✅ Year selector buttons (2025/2026)
- ✅ Legend with liturgical ranks and colors
- ✅ 12 month cards showing feast days
- ✅ Irish saints marked with ★ stars

---

## 📸 Visual Guide

### What You Should See in WordPress Admin:

**Title Field:**
```
Liturgical Calendar
```

**Permalink (click Edit to change):**
```
http://localhost:10028/calendar/
```

**Content Editor:**
```
(completely empty - do not add anything)
```

**Template Dropdown (right sidebar):**
```
[Select: Liturgical Calendar]
```

**Status:**
```
Published
```

---

## 🔧 Troubleshooting

### "Template dropdown doesn't show 'Liturgical Calendar'"
- Make sure you're on the page edit screen (not a post)
- Check that `page-calendar.php` exists in your theme folder
- Try refreshing the page edit screen

### "Calendar page shows 'Calendar data not available'"
This means the page was created but the template wasn't selected.

**Fix:**
1. Go to **Pages → All Pages**
2. Find "Liturgical Calendar" page
3. Click **Edit**
4. In right sidebar, **Template** dropdown → Select "Liturgical Calendar"
5. Click **Update**

### "Page shows default template"
- Verify template is set to "Liturgical Calendar" not "Default Template"
- Click **Update** button after changing template
- Clear browser cache and refresh

---

## ✅ Success Checklist

After creating the page, verify these features work:

- [ ] Page loads at http://localhost:10028/calendar/
- [ ] Green header displays with calendar title
- [ ] Year selector shows "2025" and "2026" buttons
- [ ] 2025 button is highlighted (active)
- [ ] Legend section shows liturgical ranks
- [ ] 12 month cards display (January through December)
- [ ] Irish saints have ★ star indicator
- [ ] Color dots show next to dates (white, red, etc.)
- [ ] Click "2026" button → URL changes to ?year=2026
- [ ] 2026 calendar data loads (Easter moves to April 5)
- [ ] Hover over feast day cards shows effect
- [ ] "View Monastic Sites" button links correctly
- [ ] Page is responsive on mobile (month cards stack)

---

## 📝 Optional: Add to Menu

After the page works:

1. Go to **Appearance → Menus**
2. Find "Liturgical Calendar" in **Pages** section
3. Check the box and click **"Add to Menu"**
4. In menu structure, change label to: **Calendar**
5. Drag to position (suggest after "Counties")
6. Click **"Save Menu"**

---

## 🎯 Yoast SEO (Optional but Recommended)

If using Yoast SEO plugin:

**Focus Keyphrase:**
```
Irish Catholic Calendar
```

**SEO Title:**
```
Irish Catholic Liturgical Calendar 2025-2026 | PilgrimIRL
```

**Meta Description:**
```
Explore Ireland's Catholic liturgical calendar for 2025 and 2026. Feast days, solemnities, and commemorations of Irish saints including St. Patrick, St. Brigid, and St. Columba.
```

---

## 🎨 What You'll See

### Header
- Beautiful green gradient background
- Title: "Irish Catholic Liturgical Calendar 2025"
- Description about celebrating Ireland's Catholic tradition
- Year selector buttons

### Legend
- Liturgical ranks explained (Solemnity, Feast, Memorial)
- Color meanings (White, Red, Green, Violet, Rose)
- Irish saint indicator (★)

### Calendar Grid
12 month cards, each showing:
- Month name (e.g., "March 2025")
- Feast days with:
  - Date number
  - Liturgical color dot
  - Feast name
  - Rank badge
  - ★ for Irish saints

### Example - March 2025:
```
March 2025
─────────────────
17  ⚪  Saint Patrick, Bishop, Patron of Ireland ★
        [Solemnity]

19  ⚪  Saint Joseph, Husband of Mary
        [Solemnity]

25  ⚪  The Annunciation of the Lord
        [Solemnity]
```

### Irish Saints Section
- Grid of saint cards with descriptions
- Links to Monastic Sites
- Call-to-action buttons

---

## 💡 Tips

**If you mess up:**
- Just delete the page and start over
- Or edit the page and re-select the template
- The template and data files are permanent - they won't be affected

**Want to see 2026?**
- Click the "2026" button at the top
- URL will change to ?year=2026
- Calendar updates to show 2026 dates

**Mobile view:**
- Month cards stack vertically
- Year buttons adapt
- All features work on mobile

---

## 🗂️ Files Reference

These files are already in place:
- ✅ `page-calendar.php` - Template file
- ✅ `includes/calendar-data-2025.php` - 2025 data
- ✅ `includes/calendar-data-2026.php` - 2026 data

All you need to do is create the WordPress page!

---

## ⏱️ Time Required
- **2 minutes** to create the page
- **1 minute** to add to menu
- **1 minute** to configure Yoast SEO
- **Total: ~4 minutes**

---

## 🆘 Still Having Issues?

If the calendar still doesn't work after creating the page:

1. **Check template is selected:**
   - Edit page → Right sidebar → Template dropdown
   - Must say "Liturgical Calendar"

2. **Check files exist:**
   ```bash
   ls wp-content/themes/pilgrimirl/page-calendar.php
   ls wp-content/themes/pilgrimirl/includes/calendar-data-*.php
   ```

3. **Clear cache:**
   - Browser cache (Ctrl+F5 or Cmd+Shift+R)
   - WordPress cache if using caching plugin

4. **Check permissions:**
   - Data files should be readable by web server

---

## 📞 Need More Help?

Refer to the complete guide: `CALENDAR_SETUP.md`

---

**Now go create that page!** 🚀

Just go to: http://localhost:10028/wp-admin/post-new.php?post_type=page

It's that easy! ☘️
