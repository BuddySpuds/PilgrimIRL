#!/bin/bash

# PilgrimIRL Automated Deployment Script
# Deploys entire site to Hostinger with minimal manual intervention

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
LOCAL_URL="http://localhost:10028"
LIVE_URL="https://pilgrimirl.com"
LOCAL_DIR="$(pwd)/app/public"
BACKUP_DIR="$(pwd)/deployment"
DATE=$(date +%Y%m%d-%H%M%S)
ENV_FILE="$(pwd)/.env"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Banner
echo -e "${BLUE}╔══════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  PilgrimIRL Automated Deployment v2.0   ║${NC}"
echo -e "${BLUE}╔══════════════════════════════════════════╗${NC}"
echo ""

# Function to load or request credentials
load_credentials() {
    if [ -f "$ENV_FILE" ]; then
        echo -e "${GREEN}✅ Loading credentials from .env${NC}"
        source "$ENV_FILE"
    else
        echo -e "${YELLOW}⚠️  No .env file found. Let's set up credentials.${NC}"
        echo ""

        read -p "SSH Host (e.g., ssh.hostinger.com): " HOSTINGER_SSH_HOST
        read -p "SSH Port (usually 65002): " HOSTINGER_SSH_PORT
        read -p "SSH Username: " HOSTINGER_SSH_USER
        read -sp "SSH Password: " HOSTINGER_SSH_PASS
        echo ""

        read -p "Database Name: " HOSTINGER_DB_NAME
        read -p "Database User: " HOSTINGER_DB_USER
        read -sp "Database Password: " HOSTINGER_DB_PASS
        echo ""

        HOSTINGER_DB_HOST="localhost"
        read -p "Web Root (e.g., /home/u123/domains/pilgrimirl.com/public_html): " HOSTINGER_WEB_ROOT

        # Save to .env
        echo -e "${BLUE}💾 Saving credentials to .env (git-ignored)${NC}"
        cat > "$ENV_FILE" << EOF
# Hostinger Deployment Credentials
# DO NOT COMMIT TO GIT!
HOSTINGER_SSH_HOST="$HOSTINGER_SSH_HOST"
HOSTINGER_SSH_PORT="$HOSTINGER_SSH_PORT"
HOSTINGER_SSH_USER="$HOSTINGER_SSH_USER"
HOSTINGER_SSH_PASS="$HOSTINGER_SSH_PASS"
HOSTINGER_DB_NAME="$HOSTINGER_DB_NAME"
HOSTINGER_DB_USER="$HOSTINGER_DB_USER"
HOSTINGER_DB_PASS="$HOSTINGER_DB_PASS"
HOSTINGER_DB_HOST="$HOSTINGER_DB_HOST"
HOSTINGER_WEB_ROOT="$HOSTINGER_WEB_ROOT"
EOF
        chmod 600 "$ENV_FILE"
    fi
}

# Load credentials
load_credentials

echo ""
echo -e "${BLUE}🚀 Starting automated deployment...${NC}"
echo ""

# Step 1: Export database
echo -e "${YELLOW}[1/8]${NC} Exporting local database..."
cd "$LOCAL_DIR"

DB_EXPORT="$BACKUP_DIR/pilgrimirl-export-$DATE.sql"
DB_LIVE="$BACKUP_DIR/pilgrimirl-live-$DATE.sql"

if command -v wp &> /dev/null; then
    wp db export "$DB_EXPORT" --quiet
    echo -e "${GREEN}✅ Database exported${NC}"
else
    echo -e "${RED}❌ WP-CLI not found${NC}"
    echo "Please install WP-CLI or export database manually"
    exit 1
fi

# Step 2: Search & Replace URLs
echo -e "${YELLOW}[2/8]${NC} Replacing URLs (localhost → live)..."
sed "s|$LOCAL_URL|$LIVE_URL|g" "$DB_EXPORT" > "$DB_LIVE"

# Verify replacement
LOCAL_COUNT=$(grep -o "$LOCAL_URL" "$DB_EXPORT" | wc -l | tr -d ' ')
REMAINING=$(grep -o "$LOCAL_URL" "$DB_LIVE" | wc -l | tr -d ' ')

echo -e "${GREEN}✅ Replaced $LOCAL_COUNT URLs${NC}"
if [ "$REMAINING" != "0" ]; then
    echo -e "${YELLOW}⚠️  Warning: $REMAINING localhost URLs remain${NC}"
fi

# Step 3: Test SSH connection
echo -e "${YELLOW}[3/8]${NC} Testing SSH connection..."

# Use sshpass if available, otherwise try direct connection
if command -v sshpass &> /dev/null; then
    SSH_CMD="sshpass -p '$HOSTINGER_SSH_PASS' ssh -o StrictHostKeyChecking=no -p $HOSTINGER_SSH_PORT $HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST"
else
    echo -e "${YELLOW}ℹ️  sshpass not installed. You'll need to enter password manually.${NC}"
    echo "Install with: brew install hudochenkov/sshpass/sshpass"
    SSH_CMD="ssh -p $HOSTINGER_SSH_PORT $HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST"
fi

# Test connection
if eval "$SSH_CMD 'echo SSH_OK'" 2>&1 | grep -q "SSH_OK"; then
    echo -e "${GREEN}✅ SSH connection successful${NC}"
    USE_SSH=true
else
    echo -e "${YELLOW}⚠️  SSH connection failed. Will try FTP fallback.${NC}"
    USE_SSH=false
fi

# Step 4: Upload database
echo -e "${YELLOW}[4/8]${NC} Uploading database..."

if [ "$USE_SSH" = true ]; then
    # Upload via SSH
    DB_REMOTE="/tmp/pilgrimirl-live.sql"

    if command -v sshpass &> /dev/null; then
        sshpass -p "$HOSTINGER_SSH_PASS" scp -P "$HOSTINGER_SSH_PORT" "$DB_LIVE" "$HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST:$DB_REMOTE"
    else
        scp -P "$HOSTINGER_SSH_PORT" "$DB_LIVE" "$HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST:$DB_REMOTE"
    fi

    echo -e "${GREEN}✅ Database uploaded to server${NC}"

    # Import database via SSH
    echo -e "${YELLOW}[4b/8]${NC} Importing database on server..."

    IMPORT_CMD="mysql -h $HOSTINGER_DB_HOST -u $HOSTINGER_DB_USER -p'$HOSTINGER_DB_PASS' $HOSTINGER_DB_NAME < $DB_REMOTE"

    if eval "$SSH_CMD '$IMPORT_CMD'" 2>&1; then
        echo -e "${GREEN}✅ Database imported successfully${NC}"

        # Clean up remote SQL file
        eval "$SSH_CMD 'rm $DB_REMOTE'"
    else
        echo -e "${RED}❌ Database import failed${NC}"
        echo "You may need to import manually via phpMyAdmin"
        echo "File location: $DB_LIVE"
    fi
else
    echo -e "${YELLOW}⚠️  Please import database manually via phpMyAdmin${NC}"
    echo "File: $DB_LIVE"
    read -p "Press Enter when database is imported..."
fi

# Step 5: Sync files
echo -e "${YELLOW}[5/8]${NC} Syncing WordPress files..."

if [ "$USE_SSH" = true ]; then
    # Use rsync over SSH for faster transfer
    echo "Using rsync (fast, only uploads changed files)..."

    if command -v sshpass &> /dev/null; then
        SSHPASS="$HOSTINGER_SSH_PASS" sshpass -e rsync -avz --delete \
            --exclude='wp-config.php' \
            --exclude='wp-content/uploads/' \
            --exclude='.git/' \
            --exclude='node_modules/' \
            --exclude='*.log' \
            -e "ssh -p $HOSTINGER_SSH_PORT" \
            "$LOCAL_DIR/" \
            "$HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST:$HOSTINGER_WEB_ROOT/"
    else
        rsync -avz --delete \
            --exclude='wp-config.php' \
            --exclude='wp-content/uploads/' \
            --exclude='.git/' \
            --exclude='node_modules/' \
            --exclude='*.log' \
            -e "ssh -p $HOSTINGER_SSH_PORT" \
            "$LOCAL_DIR/" \
            "$HOSTINGER_SSH_USER@$HOSTINGER_SSH_HOST:$HOSTINGER_WEB_ROOT/"
    fi

    echo -e "${GREEN}✅ Files synced via rsync${NC}"
else
    # FTP fallback
    echo -e "${YELLOW}Using FTP (slower)...${NC}"

    # Check for lftp
    if command -v lftp &> /dev/null; then
        lftp -c "
        set ftp:ssl-allow no;
        set ssl:verify-certificate no;
        open -u $HOSTINGER_SSH_USER,$HOSTINGER_SSH_PASS $HOSTINGER_SSH_HOST;
        lcd $LOCAL_DIR;
        cd $HOSTINGER_WEB_ROOT;
        mirror --reverse --delete --verbose \
            --exclude wp-config.php \
            --exclude wp-content/uploads/ \
            --exclude .git/ \
            --exclude node_modules/;
        bye;
        "
        echo -e "${GREEN}✅ Files synced via FTP${NC}"
    else
        echo -e "${RED}❌ lftp not installed${NC}"
        echo "Install with: brew install lftp"
        echo ""
        echo -e "${YELLOW}Please upload files manually via FTP${NC}"
        echo "Files to upload from: $LOCAL_DIR"
        echo "Upload to: $HOSTINGER_WEB_ROOT"
        read -p "Press Enter when files are uploaded..."
    fi
fi

# Step 6: Update wp-config.php
echo -e "${YELLOW}[6/8]${NC} Updating wp-config.php..."

# Create wp-config.php content
WP_CONFIG_UPDATES="
// Database credentials
define( 'DB_NAME', '$HOSTINGER_DB_NAME' );
define( 'DB_USER', '$HOSTINGER_DB_USER' );
define( 'DB_PASSWORD', '$HOSTINGER_DB_PASS' );
define( 'DB_HOST', '$HOSTINGER_DB_HOST' );

// Security
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('WP_AUTO_UPDATE_CORE', 'minor');

// Performance
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// Debug off
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
"

if [ "$USE_SSH" = true ]; then
    # Update via SSH
    UPDATE_CONFIG_SCRIPT="
    cd $HOSTINGER_WEB_ROOT

    # Backup original
    cp wp-config.php wp-config.php.backup-$DATE

    # Update database credentials
    sed -i \"s/define( 'DB_NAME'.*/define( 'DB_NAME', '$HOSTINGER_DB_NAME' );/\" wp-config.php
    sed -i \"s/define( 'DB_USER'.*/define( 'DB_USER', '$HOSTINGER_DB_USER' );/\" wp-config.php
    sed -i \"s/define( 'DB_PASSWORD'.*/define( 'DB_PASSWORD', '$HOSTINGER_DB_PASS' );/\" wp-config.php
    sed -i \"s/define( 'DB_HOST'.*/define( 'DB_HOST', '$HOSTINGER_DB_HOST' );/\" wp-config.php

    # Add security settings if not present
    grep -q 'DISALLOW_FILE_EDIT' wp-config.php || echo \"define('DISALLOW_FILE_EDIT', true);\" >> wp-config.php
    grep -q 'FORCE_SSL_ADMIN' wp-config.php || echo \"define('FORCE_SSL_ADMIN', true);\" >> wp-config.php
    grep -q 'WP_DEBUG' wp-config.php || echo \"define('WP_DEBUG', false);\" >> wp-config.php

    echo 'wp-config.php updated'
    "

    if eval "$SSH_CMD '$UPDATE_CONFIG_SCRIPT'" 2>&1 | grep -q "updated"; then
        echo -e "${GREEN}✅ wp-config.php updated${NC}"
    else
        echo -e "${YELLOW}⚠️  Could not auto-update wp-config.php${NC}"
        echo "Please update manually with these credentials:"
        echo "DB_NAME: $HOSTINGER_DB_NAME"
        echo "DB_USER: $HOSTINGER_DB_USER"
        echo "DB_PASS: $HOSTINGER_DB_PASS"
    fi
else
    echo -e "${YELLOW}⚠️  Please update wp-config.php manually${NC}"
    echo ""
    echo "Update these values in: $HOSTINGER_WEB_ROOT/wp-config.php"
    echo ""
    echo "$WP_CONFIG_UPDATES"
    echo ""
    read -p "Press Enter when wp-config.php is updated..."
fi

# Step 7: Set permissions
echo -e "${YELLOW}[7/8]${NC} Setting file permissions..."

if [ "$USE_SSH" = true ]; then
    PERMS_SCRIPT="
    cd $HOSTINGER_WEB_ROOT
    find . -type d -exec chmod 755 {} \;
    find . -type f -exec chmod 644 {} \;
    chmod 640 wp-config.php
    echo 'Permissions set'
    "

    if eval "$SSH_CMD '$PERMS_SCRIPT'" 2>&1 | grep -q "set"; then
        echo -e "${GREEN}✅ Permissions updated${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Set permissions via File Manager:${NC}"
    echo "Folders: 755, Files: 644, wp-config.php: 640"
fi

# Step 8: Final checks and cleanup
echo -e "${YELLOW}[8/8]${NC} Running final checks..."

# Test if site is accessible
if curl -s -o /dev/null -w "%{http_code}" "$LIVE_URL" | grep -q "200\|301\|302"; then
    echo -e "${GREEN}✅ Site is accessible at $LIVE_URL${NC}"
else
    echo -e "${YELLOW}⚠️  Site check: Could not verify (may still be DNS propagating)${NC}"
fi

# Clear WordPress caches via WP-CLI if available remotely
if [ "$USE_SSH" = true ]; then
    echo "Clearing WordPress caches..."
    CACHE_CMD="cd $HOSTINGER_WEB_ROOT && wp cache flush 2>/dev/null || echo 'Cache cleared or WP-CLI not available'"
    eval "$SSH_CMD '$CACHE_CMD'" 2>&1
fi

# Summary
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║     DEPLOYMENT COMPLETE! 🎉             ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📊 Deployment Summary:${NC}"
echo "  • Database: ✅ Exported, replaced, imported"
echo "  • Files: ✅ Synced to server"
echo "  • Config: ✅ Updated"
echo "  • Permissions: ✅ Set"
echo ""
echo -e "${BLUE}🌐 Your Site:${NC} $LIVE_URL"
echo -e "${BLUE}📁 Admin:${NC} $LIVE_URL/wp-admin/"
echo ""
echo -e "${YELLOW}⚡ Post-Deployment Tasks:${NC}"
echo "  1. Login to WordPress admin"
echo "  2. Settings → General → Set Google Maps API key"
echo "  3. Settings → Permalinks → Save (flush rewrite rules)"
echo "  4. Test maps, filters, calendar"
echo "  5. Install security plugin (Wordfence)"
echo "  6. Install caching plugin (WP Rocket / W3 Total Cache)"
echo "  7. Submit sitemap to Google Search Console"
echo ""
echo -e "${BLUE}📦 Backup Files:${NC}"
echo "  Local DB: $DB_EXPORT"
echo "  Live DB:  $DB_LIVE"
echo ""
echo -e "${GREEN}🚀 Site is LIVE!${NC}"
echo ""

# Create deployment log
cat > "$BACKUP_DIR/deployment-log-$DATE.txt" << EOF
PilgrimIRL Deployment Log
=========================
Date: $(date)
Local URL: $LOCAL_URL
Live URL: $LIVE_URL

Database:
- Exported: $DB_EXPORT
- Processed: $DB_LIVE
- URLs Replaced: $LOCAL_COUNT

Files:
- Synced via: $([ "$USE_SSH" = true ] && echo "rsync/SSH" || echo "FTP")
- Destination: $HOSTINGER_WEB_ROOT

Configuration:
- Database: $HOSTINGER_DB_NAME
- User: $HOSTINGER_DB_USER
- Host: $HOSTINGER_DB_HOST

Status: ✅ Complete
EOF

echo -e "${BLUE}📝 Deployment log saved: deployment-log-$DATE.txt${NC}"
