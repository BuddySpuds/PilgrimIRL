# 🚀 Automated Deployment for PilgrimIRL

**Deploy your entire WordPress site to Hostinger in 5 minutes!**

---

## 📋 What You Need from Hostinger

Open `HOSTINGER_INFO_NEEDED.md` for detailed instructions, but here's the quick list:

### 1. SSH Access (in hPanel → Advanced → SSH Access)
```
Host: ssh.hostinger.com
Port: 65002
Username: u123456789
Password: [your hosting password]
```

### 2. Create MySQL Database (in hPanel → Databases)
```
Database Name: u123456789_pilgrimirl
Database User: u123456789_dbuser
Database Password: [create strong password]
Host: localhost
```

### 3. Web Root Path
Usually: `/home/u123456789/domains/pilgrimirl.com/public_html`

---

## ⚡ Quick Start

### Step 1: Install Tools (one-time setup)
```bash
cd /Users/robertporter/Local\ Sites/pilgrimirl
./install-deploy-tools.sh
```

This installs:
- `sshpass` - automated SSH authentication
- `lftp` - automated FTP (fallback)
- `rsync` - fast file syncing
- `wp-cli` - WordPress CLI tools

### Step 2: Run Automated Deployment
```bash
./auto-deploy.sh
```

The script will:
1. Ask for Hostinger credentials (or load from .env)
2. Export local database ✅
3. Replace all localhost URLs ✅
4. Upload database to server ✅
5. Sync all files via rsync/SSH ✅
6. Update wp-config.php ✅
7. Set correct permissions ✅
8. Test site accessibility ✅

**Total time: ~5 minutes** (depending on connection speed)

---

## 🔐 Secure Credentials Storage

### Option A: Interactive (asks each time)
Just run `./auto-deploy.sh` - it will ask for credentials

### Option B: Save to .env (recommended)
Create `.env` file:
```bash
cat > .env << 'EOF'
HOSTINGER_SSH_HOST="ssh.hostinger.com"
HOSTINGER_SSH_PORT="65002"
HOSTINGER_SSH_USER="u123456789"
HOSTINGER_SSH_PASS="your_password_here"
HOSTINGER_DB_NAME="u123456789_pilgrimirl"
HOSTINGER_DB_USER="u123456789_dbuser"
HOSTINGER_DB_PASS="your_db_password_here"
HOSTINGER_DB_HOST="localhost"
HOSTINGER_WEB_ROOT="/home/u123456789/domains/pilgrimirl.com/public_html"
EOF
```

Then just run: `./auto-deploy.sh`

**The .env file is git-ignored for security!**

---

## 🎯 What Gets Automated

| Task | Automated? | Method |
|------|------------|--------|
| Database export | ✅ Yes | WP-CLI |
| URL replacement | ✅ Yes | sed |
| Database upload | ✅ Yes | SSH + MySQL |
| Files upload | ✅ Yes | rsync over SSH |
| wp-config.php update | ✅ Yes | SSH + sed |
| File permissions | ✅ Yes | SSH + chmod |
| Cache clearing | ✅ Yes | WP-CLI remote |
| SSL verification | ✅ Yes | curl check |

---

## 📊 Deployment Features

### Intelligent Methods
- **Primary:** SSH + rsync (fastest, most reliable)
- **Fallback:** FTP if SSH unavailable
- **Database:** Direct MySQL import via SSH
- **Fallback:** Manual phpMyAdmin instructions

### Safety Features
- ✅ Creates backups before deployment
- ✅ Doesn't upload wp-config.php (edits on server)
- ✅ Doesn't overwrite uploads folder (optional)
- ✅ Logs all deployment steps
- ✅ Verifies site accessibility after deployment
- ✅ Stores credentials securely (not in git)

### Smart Sync
- Only uploads changed files (rsync delta transfer)
- Excludes unnecessary files (.git, node_modules, etc.)
- Preserves file permissions
- Handles large databases efficiently

---

## 🔧 Troubleshooting

### "SSH connection failed"
- Check SSH is enabled in hPanel → Advanced → SSH Access
- Verify host, port, username, password
- Script will fall back to FTP automatically

### "Database import failed"
- Check database credentials
- Ensure database exists in hPanel
- Manual fallback: Use phpMyAdmin with exported SQL file

### "sshpass not found"
```bash
brew install hudochenkov/sshpass/sshpass
```

### "Permission denied"
```bash
chmod +x auto-deploy.sh install-deploy-tools.sh
```

---

## 📝 Post-Deployment Checklist

After `auto-deploy.sh` completes:

1. **Login to WordPress**
   - URL: https://pilgrimirl.com/wp-admin/
   - Use your existing credentials

2. **Set Google Maps API Key**
   - Settings → General
   - Find "Google Maps API Key"
   - Enter: `AIzaSyDQNzyQIt4FvrokzST36ON_zb4Qf-tjpYs`

3. **Flush Permalinks**
   - Settings → Permalinks
   - Click "Save Changes" (doesn't need to change anything)

4. **Test Site**
   - ✅ Homepage loads
   - ✅ Maps work on a site
   - ✅ Filters work
   - ✅ Calendar displays
   - ✅ Mobile responsive

5. **Install Plugins (optional but recommended)**
   - **Wordfence Security** (security scans)
   - **WP Rocket** or **W3 Total Cache** (caching)
   - **ShortPixel** or **Imagify** (image optimization)

6. **Submit to Google**
   - Google Search Console: Add property
   - Submit sitemap: `/sitemap_index.xml`
   - Set up Google Analytics

---

## 📂 What Gets Deployed

### Included:
```
✅ /wp-admin/
✅ /wp-includes/
✅ /wp-content/themes/
✅ /wp-content/plugins/
✅ /wp-content/mu-plugins/
✅ /.htaccess
✅ /index.php
✅ All WordPress core files
✅ Database (with URL replacement)
```

### Excluded:
```
❌ /wp-config.php (edited on server instead)
❌ /wp-content/uploads/ (optional, can be kept)
❌ /.git/
❌ /node_modules/
❌ /.env (credentials file)
❌ /deployment/ (backup folder)
```

---

## 🆘 Manual Deployment (if automation fails)

If the automated script doesn't work, follow:
1. **Comprehensive:** `DEPLOYMENT_GUIDE.md`
2. **Quick Manual:** `QUICK_START_DEPLOY.md`

Or run individual scripts:
- Export only: `./deploy-helper.sh`
- Pre-flight checks: `./preflight-check.sh`

---

## 🔄 Re-Deployment / Updates

To update your live site after making changes:

```bash
./auto-deploy.sh
```

The script will:
- Only upload changed files (fast!)
- Update database with new content
- Preserve your live settings

**Safe to run multiple times!**

---

## 🎉 Success!

When you see:
```
╔══════════════════════════════════════════╗
║     DEPLOYMENT COMPLETE! 🎉             ║
╚══════════════════════════════════════════╝
```

Your site is LIVE at: **https://pilgrimirl.com** 🚀

---

## 📞 Support

**Hostinger Issues:**
- 24/7 Live Chat in hPanel
- Ticket system for technical issues

**Script Issues:**
- Check deployment logs in `deployment/`
- Review error messages
- Fall back to manual deployment

**WordPress Issues:**
- Check error logs in hPanel
- Enable WP_DEBUG temporarily
- Check browser console for JS errors

---

**Built with ❤️ for Irish Heritage**

*Last Updated: November 24, 2025*
