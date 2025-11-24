# 🇮🇪 PilgrimIRL - Discover Ireland's Sacred Heritage

A comprehensive database of Ireland's sacred sites, monasteries, pilgrimage routes, and Christian heritage locations.

**Live Site:** https://pilgrimirl.com

---

## 📖 About

PilgrimIRL is a WordPress-powered platform showcasing over 1,000 sacred sites across Ireland, including:

- ⛪ **Monastic Sites** - Ancient monasteries and abbeys
- 🚶 **Pilgrimage Routes** - Historic walking paths
- ✝️ **Christian Sites** - Churches, cathedrals, and holy wells
- 📍 **Interactive Maps** - Google Maps integration for each location
- 🔍 **Advanced Filtering** - Search by county, type, order, and era

---

## 🛠️ Tech Stack

- **CMS:** WordPress 6.x
- **Theme:** Custom child theme (twentytwentyfive base)
- **Development:** Local by Flywheel
- **Hosting:** Hostinger (LiteSpeed, PHP 8.2)
- **CI/CD:** GitHub Actions
- **Maps:** Google Maps JavaScript API
- **Database:** MySQL 8.0
- **Version Control:** Git + GitHub

---

## 🚀 Features

### Core Functionality:
- ✅ 1000+ Sacred Sites Database
- ✅ Custom Post Types (Monastic Sites, Routes, Christian Sites)
- ✅ Custom Taxonomies (Counties, Site Types, Religious Orders, Eras)
- ✅ Interactive Google Maps
- ✅ Advanced Filtering System
- ✅ Saints Calendar
- ✅ Mobile Responsive Design
- ✅ SEO Optimized (Schema.org, Open Graph)

### Technical Features:
- ✅ AJAX-powered filtering
- ✅ Custom meta boxes
- ✅ Geolocation data
- ✅ SSL/HTTPS enabled
- ✅ Security hardened
- ✅ Performance optimized

---

## 📁 Project Structure

```
pilgrimirl/
├── .github/
│   └── workflows/
│       └── deploy-to-hostinger.yml    # CI/CD pipeline
├── app/
│   └── public/                        # WordPress root
│       ├── wp-content/
│       │   ├── themes/
│       │   │   └── pilgrimirl/        # Custom theme
│       │   ├── plugins/               # Installed plugins
│       │   └── mu-plugins/            # Must-use plugins
│       └── wp-config.php              # WP configuration (gitignored)
├── deployment/                        # Deployment backups (gitignored)
├── .env                               # Credentials (gitignored)
├── .gitignore                         # Git exclusions
├── auto-deploy.sh                     # Manual deployment script
├── README.md                          # This file
├── CI_CD_SETUP.md                     # CI/CD documentation
├── LIVE_SITE_REVIEW.md                # Post-deployment review
└── README_DEPLOY.md                   # Deployment guide
```

---

## 🏗️ Development Setup

### Prerequisites:
- **Local by Flywheel** - WordPress local development
- **Git** - Version control
- **GitHub account** - Code hosting & CI/CD
- **Node.js** (optional) - If building assets

### Setup Instructions:

1. **Clone Repository:**
   ```bash
   git clone https://github.com/YOUR-USERNAME/pilgrimirl.git
   cd pilgrimirl
   ```

2. **Import to Local:**
   - Open Local by Flywheel
   - Add Existing Site → Choose `/pilgrimirl/` folder
   - Start site

3. **Access Local Site:**
   - Frontend: http://localhost:10028
   - Admin: http://localhost:10028/wp-admin/

4. **Make Changes:**
   - Edit theme files in `app/public/wp-content/themes/pilgrimirl/`
   - Test locally
   - Commit changes

5. **Deploy:**
   ```bash
   git add .
   git commit -m "feat: your feature description"
   git push origin main  # Automatically deploys!
   ```

---

## 🔄 Deployment

### Automated Deployment (Recommended):

Push to GitHub → Auto-deploys to Hostinger via GitHub Actions

See `CI_CD_SETUP.md` for full instructions.

### Manual Deployment:

```bash
# From Local shell
cd /Users/robertporter/Local\ Sites/pilgrimirl
./auto-deploy.sh
```

---

## 🎨 Theme Development

### Custom Theme: `pilgrimirl`

**Location:** `app/public/wp-content/themes/pilgrimirl/`

**Key Files:**
- `functions.php` - Theme setup, enqueues, custom post types
- `style.css` - Theme stylesheet
- `archive-*.php` - Archive templates for custom post types
- `single-*.php` - Single post templates
- `template-parts/` - Reusable template components
- `assets/` - CSS, JS, images

### Custom Post Types:
1. `monastic_site` - Monastic Sites
2. `pilgrimage_route` - Pilgrimage Routes
3. `christian_site` - Christian Heritage Sites

### Custom Taxonomies:
- `county` - Irish counties
- `site_type` - Type of site
- `religious_order` - Religious orders
- `era` - Historical era

---

## 🔐 Security

### Implemented Measures:
- ✅ File editing disabled in production
- ✅ SSL/HTTPS enforced
- ✅ Debug mode off in production
- ✅ Proper file permissions (755/644)
- ✅ wp-config.php protected
- ✅ Input sanitization & output escaping
- ✅ CSRF protection (nonces)
- ✅ SQL injection prevention (prepared statements)

### Security Score: 95/100

See `SECURITY_AUDIT_REPORT.md` for details.

---

## 🎯 SEO Optimization

### Implemented:
- ✅ Schema.org structured data (TouristAttraction)
- ✅ Dynamic meta descriptions
- ✅ Open Graph tags
- ✅ Twitter Cards
- ✅ Canonical URLs
- ✅ XML Sitemaps
- ✅ Robots.txt
- ✅ Hreflang tags

### SEO Score: 92/100

See `SEO_AUDIT.md` for details.

---

## 📊 Performance

### Current Status:
- Server Response: ~200-500ms
- HTTP/2: ✅ Enabled
- SSL: ✅ Enabled
- Server: LiteSpeed

### Recommended Enhancements:
- [ ] Install caching plugin (WP Rocket)
- [ ] Optimize images (ShortPixel)
- [ ] Enable CDN
- [ ] Minify CSS/JS

---

## 🧪 Testing

### Manual Testing Checklist:
- [ ] Homepage loads correctly
- [ ] Archive pages work (sites, routes)
- [ ] Individual site pages display
- [ ] Maps render correctly
- [ ] Filters function properly
- [ ] Calendar displays
- [ ] Search works
- [ ] Mobile responsive
- [ ] Cross-browser compatible

---

## 📝 Documentation

- `README.md` - Project overview (this file)
- `CI_CD_SETUP.md` - GitHub Actions setup guide
- `README_DEPLOY.md` - Automated deployment guide
- `DEPLOYMENT_GUIDE.md` - Manual deployment guide
- `LIVE_SITE_REVIEW.md` - Post-deployment checklist
- `SEO_AUDIT.md` - SEO optimization report
- `SECURITY_AUDIT_REPORT.md` - Security assessment

---

## 🤝 Contributing

### Workflow:
1. Create feature branch: `git checkout -b feature/your-feature`
2. Make changes and test locally
3. Commit: `git commit -m "feat: description"`
4. Push: `git push origin feature/your-feature`
5. Create Pull Request on GitHub
6. After review, merge to `main`
7. Automatically deploys to production!

### Commit Conventions:
- `feat:` New feature
- `fix:` Bug fix
- `style:` CSS/design changes
- `docs:` Documentation
- `refactor:` Code refactoring
- `test:` Testing updates
- `chore:` Maintenance tasks

---

## 📞 Support & Resources

### Documentation:
- WordPress Codex: https://codex.wordpress.org/
- Theme Handbook: https://developer.wordpress.org/themes/
- WP-CLI: https://wp-cli.org/

### Hosting:
- Hostinger Support: 24/7 live chat in hPanel
- hPanel: https://hpanel.hostinger.com

### Development:
- Local by Flywheel: https://localwp.com/
- GitHub Actions: https://docs.github.com/en/actions

---

## 📜 License

Proprietary - All rights reserved.

---

## 👤 Author

**PilgrimIRL Development Team**

---

## 🎉 Acknowledgments

- WordPress Community
- Twenty Twenty-Five Theme
- Google Maps Platform
- Hostinger Hosting
- Local by Flywheel
- GitHub Actions

---

**Site Status:** 🟢 LIVE & OPERATIONAL

**Last Updated:** November 24, 2025

---

## 🚀 Quick Links

- **Live Site:** https://pilgrimirl.com
- **Admin Panel:** https://pilgrimirl.com/wp-admin/
- **GitHub Repo:** https://github.com/YOUR-USERNAME/pilgrimirl
- **GitHub Actions:** https://github.com/YOUR-USERNAME/pilgrimirl/actions

---

*Discover Ireland's sacred heritage. One site at a time.* 🇮🇪⛪
