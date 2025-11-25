# Saints Page Setup Instructions

## Overview
Comprehensive directory of Irish Catholic Saints with reverse linking to:
- **Calendar feast days** - Links to liturgical calendar
- **Associated locations** - Links to monastic sites and counties
- **Site counts** - Shows number of sites associated with each saint

---

## Quick Setup (3 minutes)

### Step 1: Create WordPress Page

1. Go to: **http://localhost:10028/wp-admin/post-new.php?post_type=page**
2. **Title:** Irish Saints
3. **Permalink:** Click "Edit" → Change to `saints`
4. **Content:** Leave empty (template handles everything)
5. **Template** (right sidebar): Select "Irish Saints"
6. **Status:** Publish

### Step 2: Add to Menu (Optional)

1. Go to **Appearance → Menus**
2. Find "Irish Saints" in Pages section
3. Add to Primary Menu
4. Change label to "Saints" (shorter)
5. Position after "Calendar"
6. Save Menu

### Step 3: Verify

Visit: **http://localhost:10028/saints/**

---

## Features

### ✅ Reverse Linking

**To Calendar:**
- Each saint's feast day links to `/calendar/?year=2025&feast=saint-name`
- Example: St. Patrick → `/calendar/?year=2025&feast=saint-patrick-bishop-patron-of-ireland`

**To Sites:**
- "Explore X Sites" links to filtered monastic sites
- Example: St. Patrick → `/monastic-sites/?saint=patrick`
- Shows all 127 sites associated with St. Patrick

**To Counties:**
- Major sites include county information
- Links can connect to county archive pages

### ✅ Saint Information

Each saint card includes:
- **Icon** - Visual identifier (☘️ 📜 ⛵ etc.)
- **Name & Dates** - Full name and lifespan
- **Description** - Who they were and what they did
- **Feast Day** - Liturgical calendar date
- **Significance** - Why they're important
- **Major Sites** - Top 3 associated locations
- **Site Count** - Total number of associated sites
- **Action Links** - View feast day, explore sites

### ✅ Page Sections

1. **Header**
   - Title with gradient background
   - Statistics (saints count, sites, counties)

2. **Patron Saints (Featured)**
   - St. Patrick, St. Brigid, St. Columba
   - Large cards with detailed information
   - Direct links to feast days and sites

3. **Saints Directory**
   - Grid of all Irish saints
   - Detailed cards with descriptions
   - Multiple reverse links per saint

4. **Call to Action**
   - Links to browse all sites
   - Link to calendar
   - Link to counties

---

## Saints Included

### Patron Saints (3)
- ☘️ **Saint Patrick** - Patron of Ireland (127 sites)
- 🔥 **Saint Brigid** - Abbess of Kildare (9 sites)
- 📜 **Saint Columba (Colmcille)** - Founder of Iona (47 sites)

### Major Saints (10+)
- ⛵ **Saint Brendan** - The Navigator (9 sites)
- ⛰️ **Saint Kevin** - Glendalough (3 sites)
- ⛪ **Saint Ciarán** - Clonmacnoise (8 sites)
- 📚 **Saint Finnian** - Clonard (10 sites)
- ✝️ **Saint Oliver Plunkett** - Martyr (5 sites)
- 🏝️ **Saint Enda** - Aran Islands (42 sites)
- 🌾 **Saint Féchín** - Fore Abbey (9 sites)
- 👸 **Our Lady of Knock** - Marian apparition

---

## Linking Examples

### Example 1: Saint Patrick

**Page:** http://localhost:10028/saints/

**Links on page:**
1. **Feast Day Link:**
   → `/calendar/?year=2025&feast=saint-patrick-bishop-patron-of-ireland`
   → Opens March 17 feast day details

2. **Sites Link:**
   → `/monastic-sites/?saint=patrick`
   → Shows all 127 Patrick-related sites

3. **Major Sites Listed:**
   - Croagh Patrick (County Mayo)
   - Downpatrick Cathedral (County Down)
   - Hill of Slane (County Meath)
   - Hill of Tara (County Armagh)

### Example 2: Saint Brigid

**Feast Day Link:**
→ `/calendar/?year=2025&feast=saint-brigid-of-kildare`
→ Opens February 1 feast details with traditions

**Sites Link:**
→ `/monastic-sites/?saint=brigid`
→ Shows 9 Brigid-related sites

**Counties:** Kildare, Louth, Dublin

### Example 3: Our Lady of Knock

**Feast Day Link:**
→ `/calendar/?year=2025&feast=our-lady-of-knock`
→ Opens August 17 commemoration

**Major Site:**
- Knock Shrine (County Mayo)

---

## Adding More Saints

To add more saints to the database, edit `page-saints.php` and add to the `$saints_data` array:

```php
'saint-slug' => array(
    'name' => 'Saint Name',
    'full_name' => 'Saint Full Name with Title',
    'feast_day' => 'Month Day',
    'feast_slug' => 'feast-slug-from-calendar', // Optional
    'dates' => 'c. 000-000 AD',
    'description' => 'Who they were and what they did',
    'significance' => 'Why they matter',
    'sites_count' => 0, // Number from filter
    'major_sites' => array('Site 1', 'Site 2'),
    'counties' => array('County 1', 'County 2'),
    'icon' => '📿', // Emoji icon
),
```

---

## SEO Optimization

**Page Title:**
```
Irish Catholic Saints | PilgrimIRL
```

**Meta Description:**
```
Discover Ireland's Catholic saints - from St. Patrick and St. Brigid to lesser-known holy men and women. Explore feast days, sacred sites, and spiritual heritage across all 32 counties.
```

**Focus Keyphrase:**
```
Irish Catholic Saints
```

---

## Integration with Existing Site

### From Other Pages → To Saints Page

Add links like:
- Homepage: "Discover Irish Saints" button
- Calendar: "View All Irish Saints" link
- Monastic Sites: "Learn about Saint [Name]" links
- County Pages: "Saints of [County]" section

### From Saints Page → To Other Pages

Already included:
- ✅ Links to calendar feast days
- ✅ Links to filtered monastic sites
- ✅ Links to county pages (via major sites)
- ✅ CTA to browse all content

---

## Mobile Responsiveness

- ✅ Single column layout on mobile
- ✅ Stacked saint cards
- ✅ Touch-friendly buttons
- ✅ Optimized typography
- ✅ Full-width CTA buttons

---

## Future Enhancements

### Phase 2
- [ ] Add search/filter by feast month
- [ ] Filter by county
- [ ] Sort by number of sites
- [ ] Add saint images/icons
- [ ] Timeline view of Irish saints

### Phase 3
- [ ] Individual saint detail pages
- [ ] Interactive map of saint locations
- [ ] Saint relationships (teacher/student)
- [ ] Historical context sections
- [ ] Prayer resources

---

## URLs

- **Main Page:** http://localhost:10028/saints/
- **Example Links:**
  - St. Patrick feast: `/calendar/?year=2025&feast=saint-patrick-bishop-patron-of-ireland`
  - St. Patrick sites: `/monastic-sites/?saint=patrick`
  - St. Brigid feast: `/calendar/?year=2025&feast=saint-brigid-of-kildare`
  - St. Columba feast: `/calendar/?year=2025&feast=saint-columba-colmcille-abbot`

---

## Troubleshooting

### "Template not showing in dropdown"
- Refresh the page editor
- Make sure `page-saints.php` exists in theme folder
- Check file has proper Template Name comment

### "Links not working"
- Verify calendar page exists at `/calendar/`
- Check saint slugs match calendar data
- Ensure site filter accepts `?saint=` parameter

### "No site counts showing"
- Check database has `associated_saint` meta field
- Verify monastic sites have saint metadata
- Update site counts in $saints_data array

---

## Summary

**Created:**
✅ Saints page template with reverse linking
✅ 10+ featured saints with full details
✅ Links to calendar feast days
✅ Links to filtered monastic sites
✅ County and location information
✅ Mobile-responsive design
✅ Beautiful card-based layout

**To Do:**
1. Create "Irish Saints" page in WordPress
2. Select "Irish Saints" template
3. Configure SEO (Yoast)
4. Add to Primary Menu
5. Test all links

Ready to showcase Ireland's spiritual heritage! ☘️ 🙏
