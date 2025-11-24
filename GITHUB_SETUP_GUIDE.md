# GitHub Setup Guide for PilgrimIRL

## Current Status
✅ Local Git repository initialized (6 commits)
✅ GitHub remote added: https://github.com/BuddySpuds/PilgrimIRL
⏳ Need to authenticate and push

---

## Authentication Setup

### Option 1: Personal Access Token (Recommended)

#### Step 1: Create Personal Access Token
1. Go to: https://github.com/settings/tokens
2. Click **"Generate new token"** → **"Generate new token (classic)"**
3. Give it a name: `PilgrimIRL Local Development`
4. Select expiration: **90 days** (or your preference)
5. Select scopes:
   - ✅ **repo** (Full control of private repositories)
   - ✅ **workflow** (if you plan to use GitHub Actions)
6. Click **"Generate token"**
7. **COPY THE TOKEN** - you won't see it again!

#### Step 2: Push to GitHub Using Token
```bash
cd "/Users/robertporter/Local Sites/pilgrimirl/app/public"

# Rename branch to 'main' (GitHub default)
git branch -M main

# Push using token (will prompt for username and password)
git push -u origin main

# When prompted:
# Username: BuddySpuds
# Password: <paste your Personal Access Token>
```

#### Step 3: Cache Credentials (Optional)
```bash
# Store credentials for 1 hour (safer)
git config --global credential.helper 'cache --timeout=3600'

# OR store permanently in macOS Keychain (most convenient)
git config --global credential.helper osxkeychain
```

---

### Option 2: SSH Keys (More Secure Long-term)

#### Step 1: Check for Existing SSH Key
```bash
ls -la ~/.ssh
# Look for: id_rsa.pub or id_ed25519.pub
```

#### Step 2: Generate New SSH Key (if needed)
```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
# Press Enter to accept default location
# Choose a passphrase (recommended)
```

#### Step 3: Add SSH Key to GitHub
```bash
# Copy your public key
cat ~/.ssh/id_ed25519.pub | pbcopy

# Then:
# 1. Go to: https://github.com/settings/keys
# 2. Click "New SSH key"
# 3. Title: "MacBook Local Development"
# 4. Paste the key
# 5. Click "Add SSH key"
```

#### Step 4: Update Remote URL to SSH
```bash
cd "/Users/robertporter/Local Sites/pilgrimirl/app/public"

# Change from HTTPS to SSH
git remote set-url origin git@github.com:BuddySpuds/PilgrimIRL.git

# Verify
git remote -v

# Push
git branch -M main
git push -u origin main
```

---

### Option 3: GitHub CLI (Easiest)

#### Step 1: Install GitHub CLI
```bash
# Via Homebrew
brew install gh

# OR download from: https://cli.github.com/
```

#### Step 2: Authenticate
```bash
gh auth login
# Follow the prompts:
# - GitHub.com
# - HTTPS
# - Authenticate with web browser
```

#### Step 3: Push
```bash
cd "/Users/robertporter/Local Sites/pilgrimirl/app/public"
git branch -M main
git push -u origin main
```

---

## Verification

After successful push, verify at:
https://github.com/BuddySpuds/PilgrimIRL

You should see:
- ✅ 6 commits
- ✅ All documentation files
- ✅ Theme files
- ✅ Build configuration

---

## Future Workflow

After initial setup, pushing is simple:

```bash
# Make changes and commit
git add .
git commit -m "Your commit message"

# Push to GitHub
git push
```

---

## Troubleshooting

### "Authentication failed"
- Verify your Personal Access Token hasn't expired
- Check you're using the token as password, not your GitHub password
- Ensure the token has `repo` scope

### "Permission denied (publickey)"
- Your SSH key isn't added to GitHub
- Follow SSH setup instructions above

### "Everything up-to-date"
- You've already pushed all commits
- No new changes to push

---

## Security Best Practices

1. **Never commit the Personal Access Token** to your repository
2. **Set token expiration dates** and renew regularly
3. **Use SSH keys for long-term development**
4. **Enable 2FA** on your GitHub account
5. **Review .gitignore** to ensure sensitive files are excluded

---

## Next Steps After Push

1. **Setup Branch Protection**
   - Go to: Settings → Branches
   - Add rule for `main` branch
   - Require pull request reviews (optional)

2. **Add Repository Description**
   - Go to: Repository home
   - Click edit (pencil icon)
   - Add: "WordPress directory website for Irish pilgrimage and monastic sites"

3. **Add Topics/Tags**
   - wordpress
   - php
   - pilgrimage
   - ireland
   - heritage

4. **Consider GitHub Actions** (later)
   - Automated testing with Playwright
   - Automated deployment to production
   - Code quality checks

---

*For more help, see: https://docs.github.com/en/authentication*
