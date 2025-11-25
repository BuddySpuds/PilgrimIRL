# Liturgical Calendar Setup Instructions - PilgrimIRL

## Overview
Added a beautiful Irish Catholic Liturgical Calendar section showing feast days, solemnities, and commemorations for 2025 and 2026, with special emphasis on Irish saints.

**Data Source:** https://gcatholic.org/calendar/

---

## Files Created

### 1. Calendar Data Files
- `includes/calendar-data-2025.php` - Complete 2025 liturgical calendar
- `includes/calendar-data-2026.php` - Complete 2026 liturgical calendar

### 2. Page Template
- `page-calendar.php` - Dynamic calendar display template

---

## Features

### ✅ Liturgical Calendar Display
- **Month-by-month layout** with all feast days
- **Color-coded by liturgical color** (white, red, green, violet, rose)
- **Ranked celebrations:**
  - Solemnities (highest rank) - gold badges
  - Feasts - green badges
  - Memorials - gray badges
  - Commemorations - purple badges

### ✅ Irish Saints Highlighted
Special emphasis on Irish patron saints:
- ★ Saint Patrick (March 17)
- ★ Saint Brigid of Kildare (February 1)
- ★ Saint Columba/Colmcille (June 9)
- ★ Saint Kevin (June 3)
- ★ Saint Oliver Plunkett (July 1)
- ★ Saint Ita of Limerick (January 15, 2026)
- ★ The Blessed Irish Martyrs (June 20)
- ★ Our Lady of Knock (August 17)
- ★ All Saints of Ireland (November 6)

### ✅ Interactive Features
- **Year selector** - Switch between 2025 and 2026
- **Color legend** - Explains liturgical colors and meanings
- **Rank badges** - Visual indicators for celebration importance
- **Hover effects** - Interactive card highlights
- **Responsive design** - Works on all devices

### ✅ Design Elements
- Beautiful gradient header with Celtic pattern
- Color-coded date indicators
- Month cards with organized feast day lists
- Irish saints section with descriptions
- Call-to-action linking to monastic sites

---

## Setup Instructions

### Step 1: Create Calendar Page

1. **Log into WordPress Admin:**
   - Go to http://localhost:10028/wp-admin/

2. **Create New Page:**
   - Navigate to **Pages → Add New**
   - **Title:** `Liturgical Calendar`
   - **Permalink:** Click "Edit" and set to `calendar`
   - **Leave content empty** (template handles everything)

3. **Select Template:**
   - In the right sidebar, find **Page Attributes**
   - **Template:** Select "Liturgical Calendar"

4. **SEO Settings (Yoast SEO):**
   - **Focus Keyphrase:** `Irish Catholic Calendar`
   - **Meta Description:**
     ```
     Explore Ireland's Catholic liturgical calendar for 2025 and 2026. Feast days, solemnities, and commemorations of Irish saints including St. Patrick, St. Brigid, and St. Columba.
     ```
   - **SEO Title:** `Irish Catholic Liturgical Calendar 2025-2026 | PilgrimIRL`

5. **Publish** the page

### Step 2: Add to Menu

1. **Go to Appearance → Menus**
2. **Select "Primary Menu"**
3. **Find "Liturgical Calendar" in Pages section**
4. **Add to Menu**
5. **Menu Label:** Change to `Calendar` (shorter is better for navigation)
6. **Position:** Suggest placing after "Counties" and before "Blog"

   Suggested menu structure:
   ```
   Home
   Monastic Sites
   Pilgrimage Routes
   Christian Sites
   Counties
   Calendar ← NEW
   Blog
   Join Forum
   ```

7. **Save Menu**

### Step 3: Verify It Works

1. **Visit the page:**
   - Go to http://localhost:10028/calendar/
   - Should default to 2025 calendar

2. **Check features:**
   - ✅ Green gradient header displays
   - ✅ Year selector buttons (2025/2026) work
   - ✅ Legend shows liturgical ranks and colors
   - ✅ 12 month cards display with feast days
   - ✅ Irish saints marked with ★ star
   - ✅ Color indicators show liturgical colors
   - ✅ Click year buttons to switch years
   - ✅ Hover over feast day cards for effect
   - ✅ Links to monastic sites work

3. **Test Year Switching:**
   - Click "2026" button
   - URL should change to `?year=2026`
   - Calendar updates to 2026 data
   - Easter dates change (April 5, 2026)

---

## Calendar Data Structure

### Liturgical Ranks (Importance)
1. **Solemnity (S)** - Highest rank
   - Major celebrations (Christmas, Easter, etc.)
   - Gold badge styling
   - Examples: Saint Patrick, Assumption, Christmas

2. **Feast (F)**
   - Important celebrations
   - Green badge styling
   - Examples: Saint Brigid, All Saints of Ireland

3. **Memorial (M)**
   - Remembrance of saints
   - Gray badge styling
   - Examples: Saint Kevin, Saint Columba, Saint Oliver Plunkett

4. **Commemoration**
   - Lower-rank observances
   - Purple badge styling
   - Example: All Souls (November 2)

### Liturgical Colors

| Color | Meaning | When Used |
|-------|---------|-----------|
| **White** | Joy, purity, glory | Christmas, Easter, Marian feasts |
| **Red** | Passion, Holy Spirit, martyrdom | Pentecost, martyrs, Palm Sunday |
| **Green** | Hope, growth | Ordinary Time |
| **Violet/Purple** | Penance, preparation | Advent, Lent |
| **Rose** | Joy amid penance | 3rd Sunday of Advent, 4th of Lent |

---

## Key Irish Celebrations

### Major Irish Solemnities
- **March 17**: Saint Patrick's Day (National Patron)
  - Solemnity
  - White vestments
  - Often moved if it falls during Holy Week

### Irish Feasts
- **February 1**: Saint Brigid of Kildare
  - Co-patron of Ireland
  - Abbess and miracle worker
  - Celtic goddess connections

- **November 6**: All Saints of Ireland
  - Celebrates all Irish saints, known and unknown
  - Highlights Ireland's missionary heritage

### Irish Memorials
- **June 3**: Saint Kevin, Abbot
  - Founder of Glendalough monastery
  - Hermit saint

- **June 9**: Saint Columba (Colmcille)
  - Founder of Iona
  - Missionary to Scotland
  - Copied manuscripts

- **June 20**: The Blessed Irish Martyrs
  - 17 Irish martyrs beatified in 1992
  - Died during Reformation persecutions

- **July 1**: Saint Oliver Plunkett
  - Archbishop of Armagh
  - Last Catholic martyr in England (1681)
  - Canonized 1975

- **August 17**: Our Lady of Knock
  - Apparition in County Mayo (1879)
  - National Marian shrine

---

## SEO Optimization

### Keywords Targeted
- Irish Catholic calendar
- Liturgical calendar Ireland
- Irish feast days
- Saint Patrick's Day 2025
- Catholic holy days Ireland
- Irish saints calendar

### On-Page SEO
✅ Title includes year and location
✅ Meta description under 160 characters
✅ Internal links to monastic sites and counties
✅ Structured content with H2/H3 hierarchy
✅ Alt text on visual elements (color dots, badges)
✅ Clean URL structure
✅ Schema markup ready (Yoast handles)

### Content Benefits
- **Long-form content** - Detailed month-by-month listings
- **Local relevance** - Ireland-specific celebrations
- **Educational value** - Explains liturgical colors and ranks
- **Internal linking** - Connects to site's monastic sites
- **Evergreen content** - Useful year after year
- **Unique data** - Not generic Catholic calendar

---

## Extending the Calendar

### Adding More Years

To add 2027, 2028, etc.:

1. **Fetch data** from https://gcatholic.org/calendar/2027/IE-en
2. **Create new file:** `includes/calendar-data-2027.php`
3. **Copy structure** from existing files
4. **Update year selector** in `page-calendar.php`:
   ```php
   <a href="?year=2027" class="year-btn">2027</a>
   ```

### Adding More Irish Saints

Edit the calendar data files to add more Irish saints:

```php
15 => array(
    'name' => 'Saint Malachy of Armagh',
    'rank' => 'memorial',
    'color' => 'white',
    'irish' => true
),
```

### Custom Fields (Future Enhancement)

Consider adding:
- Daily Mass readings
- Liturgical season indicators
- Links to saint biographies
- Prayer of the day
- Events at Irish shrines

---

## Integration with Existing Site

### Links from Calendar to Sites

The calendar includes CTAs linking to:
- **Monastic Sites** - Visit places founded by saints
- **Browse by County** - Find sites near you

### Links TO Calendar

Add calendar links on:
- **Homepage** - "View Liturgical Calendar"
- **Monastic Sites** - "See feast day of founder"
- **Individual Saint Pages** - "Feast day: [date]"
- **Footer** - "Calendar" link

### Blog Post Ideas

Create blog posts about:
1. "Celebrating Saint Patrick's Day: History & Traditions"
2. "Who Was Saint Brigid of Kildare?"
3. "Ireland's Blessed Martyrs: Stories of Faith"
4. "Understanding Liturgical Colors and Seasons"
5. "How to Observe Irish Feast Days"

---

## Mobile Experience

The calendar is fully responsive:
- ✅ Month cards stack on mobile
- ✅ Year selector buttons adapt
- ✅ Touch-friendly feast day cards
- ✅ Readable text sizes
- ✅ No horizontal scrolling

---

## Accessibility

Calendar includes:
- ✅ Semantic HTML structure
- ✅ Color is not the only indicator (text labels too)
- ✅ Keyboard navigation support
- ✅ Screen reader compatible
- ✅ High contrast color badges
- ✅ Descriptive alt text

---

## Troubleshooting

### Calendar page returns 404
- Go to **Settings → Permalinks**
- Click **Save Changes** (flush rewrite rules)
- Try again

### No data displays
- Check that data files exist in `/includes/` folder
- Verify file permissions (should be readable)
- Check PHP error log for issues

### Year switcher doesn't work
- Check JavaScript console for errors
- Verify URL parameter is being passed
- Clear browser cache

### Styling looks wrong
- Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
- Check that template is selected on page
- Verify CSS custom properties are defined in theme

---

## Future Enhancements

### Phase 1 (Immediate)
- ✅ 2025 calendar data
- ✅ 2026 calendar data
- ✅ Year switcher
- ✅ Irish saints highlighted

### Phase 2 (Near Future)
- [ ] Add 2027+ years
- [ ] Daily Mass readings
- [ ] Saint biography links
- [ ] Downloadable iCal format
- [ ] Print-friendly version

### Phase 3 (Advanced)
- [ ] User favorites (save feast days)
- [ ] Reminder notifications
- [ ] Event calendar integration
- [ ] Link to Irish shrine events
- [ ] Prayer resources for each feast

---

## Data Sources

**Primary:** https://gcatholic.org/calendar/
- Authoritative Catholic liturgical data
- Country-specific calendars
- iCal download available

**Validation:**
- Irish Catholic Bishops' Conference
- Vatican liturgical calendar
- Individual diocese calendars

---

## Summary

**Created:**
✅ Calendar page template with dynamic year switching
✅ Complete 2025 and 2026 liturgical data
✅ Beautiful month-by-month card layout
✅ Irish saints highlighted throughout
✅ Color-coded liturgical indicators
✅ Responsive, accessible design
✅ SEO-optimized content

**To Do:**
1. Create "Liturgical Calendar" page in WordPress
2. Select "Liturgical Calendar" template
3. Configure Yoast SEO settings
4. Add "Calendar" to Primary Menu
5. Test both years (2025/2026)
6. Verify mobile responsiveness

**URLs:**
- Main: http://localhost:10028/calendar/
- 2025: http://localhost:10028/calendar/?year=2025
- 2026: http://localhost:10028/calendar/?year=2026

---

**Questions?** The template is fully self-contained with inline CSS and uses existing site design tokens.

Good luck! ☘️ 🙏
