# 🚀 CI/CD Setup Guide - PilgrimIRL

**Automated Deployment: Local → GitHub → Hostinger**

---

## 📋 Overview

This guide sets up automated deployment from GitHub to Hostinger hosting whenever you push code to the `main` branch.

### Workflow:
```
1. Develop locally in Local by Flywheel
2. Commit changes to Git
3. Push to GitHub
4. GitHub Actions automatically deploys to Hostinger
5. Site updates live at https://pilgrimirl.com
```

---

## 🔧 Initial Setup (One-Time)

### Step 1: Create GitHub Repository

1. Go to https://github.com/new
2. Create repository: `pilgrimirl` (or your preferred name)
3. Make it **Private** (recommended for production sites)
4. Don't initialize with README (we already have files)
5. Click **Create repository**

### Step 2: Add GitHub Remote

In your Local shell or terminal:

```bash
cd /Users/robertporter/Local\ Sites/pilgrimirl

# Add GitHub remote (replace USERNAME with your GitHub username)
git remote add origin https://github.com/USERNAME/pilgrimirl.git

# Verify remote
git remote -v
```

### Step 3: Configure GitHub Secrets

GitHub Secrets store your Hostinger credentials securely for GitHub Actions.

1. Go to your GitHub repository
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret** and add these secrets:

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `HOSTINGER_SSH_HOST` | `141.136.33.138` | Hostinger SSH host |
| `HOSTINGER_SSH_PORT` | `65002` | Hostinger SSH port |
| `HOSTINGER_SSH_USER` | `u338184895` | Your Hostinger username |
| `HOSTINGER_SSH_PASS` | `!LadyLumleys99` | Your Hostinger password |
| `HOSTINGER_WEB_ROOT` | `/home/u338184895/domains/pilgrimirl.com/public_html` | Web root path |
| `SITE_URL` | `https://pilgrimirl.com` | Your live site URL |

**⚠️ Security Note:** These secrets are encrypted and only accessible to GitHub Actions. Never commit credentials to your repository!

### Step 4: Initial Commit and Push

```bash
cd /Users/robertporter/Local\ Sites/pilgrimirl

# Stage all files
git add .

# Create initial commit
git commit -m "Initial commit: PilgrimIRL WordPress site"

# Push to GitHub
git push -u origin main
```

This will trigger your first automated deployment! 🎉

---

## 🔄 Daily Development Workflow

### 1. Develop Locally

- Open **Local by Flywheel**
- Start the **pilgrimirl** site
- Make changes to your theme/plugins
- Test locally at http://localhost:10028

### 2. Commit Changes

```bash
cd /Users/robertporter/Local\ Sites/pilgrimirl

# Check what changed
git status

# Stage changes
git add app/public/wp-content/themes/pilgrimirl/
git add app/public/wp-content/plugins/  # if plugins changed

# Commit with descriptive message
git commit -m "feat: add new site filter functionality"
```

**Commit Message Conventions:**
- `feat:` New feature
- `fix:` Bug fix
- `style:` CSS/design changes
- `docs:` Documentation updates
- `refactor:` Code refactoring

### 3. Push to GitHub

```bash
git push origin main
```

**This automatically deploys to production!** 🚀

### 4. Monitor Deployment

1. Go to your GitHub repository
2. Click **Actions** tab
3. Watch the deployment progress
4. Green checkmark ✅ = Successful deployment
5. Red X ❌ = Failed deployment (check logs)

---

## 📊 What Gets Deployed

### Automatically Deployed:
- ✅ Theme files (`/wp-content/themes/pilgrimirl/`)
- ✅ Plugin files (`/wp-content/plugins/`)
- ✅ MU-plugins (`/wp-content/mu-plugins/`)
- ✅ JavaScript/CSS changes
- ✅ PHP template changes

### NOT Deployed (by default):
- ❌ Database changes (requires manual migration)
- ❌ Media uploads (managed directly on server)
- ❌ wp-config.php (server-specific)
- ❌ WordPress core files (unless modified)

---

## 🗃️ Database Migrations

For database changes (new posts, settings, content):

### Option A: Manual Export/Import (Current)

```bash
# In Local shell
cd /Users/robertporter/Local\ Sites/pilgrimirl
./auto-deploy.sh  # Deploys theme + database
```

### Option B: WP Migrate DB Plugin (Future Enhancement)

Install WP Migrate DB Pro for push/pull database sync.

---

## 🔄 Rollback Strategy

If deployment breaks something:

### Quick Rollback:

1. **Revert in GitHub:**
   ```bash
   # Revert to previous commit
   git revert HEAD
   git push origin main
   ```

2. **Or roll back to specific commit:**
   ```bash
   git log  # Find commit hash
   git revert <commit-hash>
   git push origin main
   ```

3. **Manual rollback via SSH:**
   ```bash
   # SSH into Hostinger
   ssh -p 65002 u338184895@141.136.33.138

   # Restore from backup
   cd /home/u338184895/domains/pilgrimirl.com/public_html
   cp -r wp-content/themes/pilgrimirl.backup wp-content/themes/pilgrimirl
   ```

---

## 🧪 Testing Before Production

### Recommended: Staging Environment

Create a staging subdomain: `staging.pilgrimirl.com`

1. Create subdomain in Hostinger hPanel
2. Duplicate workflow: `.github/workflows/deploy-to-staging.yml`
3. Use separate database: `u338184895_staging`
4. Test on staging before pushing to `main`

### Branch Strategy:

```bash
# Create development branch
git checkout -b dev

# Make changes
git add .
git commit -m "feat: experimental feature"
git push origin dev

# When ready for production
git checkout main
git merge dev
git push origin main  # Triggers production deployment
```

---

## 📈 Advanced Features

### 1. Deployment Notifications

Add Slack/Discord/Email notifications to `.github/workflows/deploy-to-hostinger.yml`:

```yaml
- name: Notify Slack
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    text: 'Deployment completed!'
  env:
    SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

### 2. Automated Testing

Add PHPUnit tests before deployment:

```yaml
- name: Run Tests
  run: |
    composer install
    vendor/bin/phpunit
```

### 3. Build Assets (JS/CSS)

If using build tools:

```yaml
- name: Build Assets
  run: |
    cd app/public/wp-content/themes/pilgrimirl
    npm install
    npm run build
```

---

## 🔐 Security Best Practices

### GitHub Repository:
- ✅ Keep repository **Private**
- ✅ Enable **branch protection** on `main`
- ✅ Require **pull request reviews**
- ✅ Enable **2FA** on GitHub account

### Secrets Management:
- ✅ Use GitHub Secrets (never commit credentials)
- ✅ Rotate passwords periodically
- ✅ Use SSH keys instead of passwords (advanced)

### Hostinger Server:
- ✅ Keep WordPress/plugins updated
- ✅ Use Wordfence Security plugin
- ✅ Enable automatic backups
- ✅ Monitor failed login attempts

---

## 🐛 Troubleshooting

### Deployment Fails with "Permission Denied"

**Solution:**
```bash
# Test SSH connection
ssh -p 65002 u338184895@141.136.33.138

# If password changed, update GitHub Secret:
# Settings → Secrets → HOSTINGER_SSH_PASS
```

### Files Not Updating on Server

**Causes:**
- Caching plugin (clear cache)
- CDN caching (purge CDN)
- Browser cache (hard refresh: Cmd+Shift+R)

**Solution:**
```bash
# SSH to server and check files
ssh -p 65002 u338184895@141.136.33.138
cd /home/u338184895/domains/pilgrimirl.com/public_html
ls -la wp-content/themes/pilgrimirl/  # Check file timestamps
```

### GitHub Actions Quota Exceeded

Free tier: 2,000 minutes/month

**Solution:**
- Deploy only on `main` branch
- Use manual trigger (`workflow_dispatch`)
- Upgrade to GitHub Pro if needed

---

## 📊 Monitoring & Analytics

### Deployment History

View all deployments:
1. GitHub → Actions tab
2. See commit, time, duration, status
3. Download logs for debugging

### Site Monitoring

Recommended tools:
- **UptimeRobot** (free): Monitor uptime
- **Google Search Console**: Monitor SEO
- **Google Analytics**: Track visitors
- **Wordfence**: Security monitoring

---

## 🎯 Quick Reference

### Common Commands:

```bash
# Daily workflow
git status              # Check changes
git add .               # Stage all
git commit -m "message" # Commit
git push origin main    # Deploy!

# View history
git log --oneline       # Commit history
git diff                # See changes

# Branch management
git branch              # List branches
git checkout -b dev     # Create new branch
git merge dev           # Merge branch

# Undo changes
git revert HEAD         # Undo last commit
git reset --hard HEAD~1 # Remove last commit (dangerous!)
```

### Workflow Files:
- `.github/workflows/deploy-to-hostinger.yml` - Production deployment
- `.gitignore` - Files to never commit
- `deployment/` - Local deployment backups

---

## ✅ Setup Checklist

### Initial Setup:
- [ ] Created GitHub repository
- [ ] Added all GitHub Secrets
- [ ] Configured git remote
- [ ] Made initial commit and push
- [ ] Verified deployment succeeded
- [ ] Tested live site works

### Optional Enhancements:
- [ ] Created staging environment
- [ ] Set up branch protection
- [ ] Added deployment notifications
- [ ] Configured automated testing
- [ ] Set up monitoring tools

---

## 📞 Support & Resources

- **GitHub Actions Docs:** https://docs.github.com/en/actions
- **Git Cheat Sheet:** https://education.github.com/git-cheat-sheet-education.pdf
- **WordPress CLI:** https://wp-cli.org/
- **Hostinger Support:** 24/7 live chat in hPanel

---

## 🎉 Success!

You now have a professional CI/CD pipeline! Every push to GitHub automatically deploys to your live site.

**Workflow Summary:**
```
Local Development → Git Commit → GitHub Push → Automated Deployment → Live Site ✅
```

*Setup completed: November 24, 2025*
