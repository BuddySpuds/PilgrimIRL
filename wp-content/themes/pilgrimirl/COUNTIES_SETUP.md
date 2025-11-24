# Counties Page Setup Guide

## 🏛️ How to Create the Counties Page

### **Issue:** 
The Counties page is blank because it needs to be created in WordPress admin.

### **Step 1: Create the Counties Page**

1. **Go to WordPress Admin** → **Pages** → **Add New**

2. **Page Settings:**
   - **Title:** `Counties`
   - **Slug:** `counties` (WordPress will auto-generate this)
   - **Content:** You can add some intro text like:
     ```
     Explore Ireland's sacred heritage by county. Each county holds unique monastic sites, pilgrimage routes, and Christian ruins waiting to be discovered.
     ```

3. **Template Selection:**
   - In the **Page Attributes** box (right sidebar)
   - **Template:** Select `Counties Overview`
   - If you don't see this option, make sure the `page-counties.php` file is in your theme folder

4. **Publish the page**

### **Step 2: Add Counties Page to Menu**

1. **Go to:** **Appearance** → **Menus**
2. **Select your main menu** (or create one if needed)
3. **Add the Counties page** to your menu
4. **Save the menu**

### **Step 3: Google Maps API Setup**

Now you should see the **Google Maps API Key** field:

1. **Go to:** **Settings** → **General**
2. **Scroll down** to find **"Google Maps API Key"** field
3. **Enter your API key** (get one from [Google Cloud Console](https://console.cloud.google.com/))
4. **Save Changes**

### **Step 4: Test the Counties Page**

1. **Visit:** `yoursite.com/counties/`
2. **You should see:**
   - List of all Irish counties
   - Site counts for each county
   - Interactive map (once API key is added)
   - Filter buttons for different site types

### **Step 5: Populate Counties with Data**

The counties page will show more content once you:
1. **Import your JSON data** using the data importer
2. **Create some test posts** in Monastic Sites, Pilgrimage Routes, or Christian Ruins
3. **Assign counties** to those posts

### **Troubleshooting:**

**Counties page is still blank?**
- Check that the page template is set to "Counties Overview"
- Make sure the `page-counties.php` file exists in your theme
- Try refreshing permalinks: **Settings** → **Permalinks** → **Save Changes**

**No counties showing?**
- Counties are created automatically when the theme is activated
- Go to **Posts** → **Counties** to see if they exist
- If not, try switching to another theme and back to PilgrimIRL

**Map not loading?**
- Add your Google Maps API key in **Settings** → **General**
- Check browser console (F12) for JavaScript errors
- Make sure your API key has the right permissions

**Template not available?**
- Make sure `page-counties.php` is in `/wp-content/themes/pilgrimirl/`
- The file should start with the comment `Template Name: Counties Overview`

### **Next Steps:**
1. Create the Counties page ✅
2. Add Google Maps API key ✅  
3. Import your JSON data to populate the counties
4. Test the interactive map functionality

---

**Need help?** The counties should automatically populate once you import your JSON data using the data importer tool.
