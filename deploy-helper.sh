#!/bin/bash

# PilgrimIRL Deployment Helper Script
# Automates database export and URL replacement

set -e  # Exit on error

echo "🚀 PilgrimIRL Deployment Helper"
echo "================================"
echo ""

# Configuration
LOCAL_URL="http://localhost:10028"
LIVE_URL="https://pilgrimirl.com"
EXPORT_DIR="$(pwd)/deployment"
DATE=$(date +%Y%m%d-%H%M%S)

# Create export directory
mkdir -p "$EXPORT_DIR"

echo "📁 Export directory: $EXPORT_DIR"
echo ""

# Step 1: Export database
echo "Step 1: Exporting database..."
cd "app/public"

if ! command -v wp &> /dev/null; then
    echo "❌ WP-CLI not found. Using Local's WP-CLI..."
    # Try to use Local's WP-CLI
    if [ -f "../../conf/mysql/my.cnf" ]; then
        echo "✅ Detected Local by Flywheel environment"
        # Export using Local's mysql
        echo "Exporting database (this may take a few minutes)..."
        wp db export "$EXPORT_DIR/pilgrimirl-export-$DATE.sql" 2>/dev/null || {
            echo "❌ Failed to export database"
            echo "Please export manually via Local app or phpMyAdmin"
            exit 1
        }
    else
        echo "❌ Could not find Local environment"
        echo "Please export database manually"
        exit 1
    fi
else
    wp db export "$EXPORT_DIR/pilgrimirl-export-$DATE.sql"
fi

echo "✅ Database exported: pilgrimirl-export-$DATE.sql"
echo ""

# Step 2: Search and replace URLs
echo "Step 2: Replacing URLs..."
echo "  From: $LOCAL_URL"
echo "  To:   $LIVE_URL"
echo ""

INPUT_FILE="$EXPORT_DIR/pilgrimirl-export-$DATE.sql"
OUTPUT_FILE="$EXPORT_DIR/pilgrimirl-live-$DATE.sql"

# Use sed to replace URLs
sed "s|$LOCAL_URL|$LIVE_URL|g" "$INPUT_FILE" > "$OUTPUT_FILE"

# Count replacements
LOCAL_COUNT=$(grep -o "$LOCAL_URL" "$INPUT_FILE" | wc -l | tr -d ' ')
LIVE_COUNT=$(grep -o "$LOCAL_URL" "$OUTPUT_FILE" | wc -l | tr -d ' ')

echo "✅ URL replacement complete"
echo "   Original occurrences: $LOCAL_COUNT"
echo "   Remaining (should be 0): $LIVE_COUNT"
echo ""

if [ "$LIVE_COUNT" != "0" ]; then
    echo "⚠️  Warning: Some localhost URLs remain!"
fi

# Step 3: Create compressed archive
echo "Step 3: Creating compressed SQL file..."
gzip -c "$OUTPUT_FILE" > "$OUTPUT_FILE.gz"
echo "✅ Compressed: $(basename $OUTPUT_FILE.gz)"
echo ""

# Step 4: Create file list for upload
echo "Step 4: Listing files to upload..."
cd "../.."

cat > "$EXPORT_DIR/upload-checklist.txt" << EOF
Files to Upload to Hostinger:
==============================

Database:
- Upload: deployment/pilgrimirl-live-$DATE.sql.gz
- Extract and import via phpMyAdmin

WordPress Files (via FTP to /public_html/):
✅ /wp-admin/
✅ /wp-includes/
✅ /wp-content/themes/pilgrimirl/
✅ /wp-content/plugins/
✅ /wp-content/mu-plugins/
✅ /index.php
✅ /wp-blog-header.php
✅ /wp-load.php
✅ /wp-settings.php
✅ /wp-config-sample.php
✅ /.htaccess

⚠️  DO NOT UPLOAD:
❌ /wp-config.php (edit on server)
❌ /wp-content/uploads/ (optional, or use existing)

Manual Steps:
1. Create database on Hostinger
2. Import SQL file via phpMyAdmin
3. Upload files via FTP
4. Edit wp-config.php with new database credentials
5. Test site at https://pilgrimirl.com
EOF

echo "✅ Created upload checklist"
echo ""

# Step 5: Summary
echo "📊 Deployment Package Summary"
echo "============================="
echo ""
echo "Location: $EXPORT_DIR/"
echo ""
echo "Files created:"
ls -lh "$EXPORT_DIR/" | grep -E "\.(sql|gz|txt)$" | awk '{print "  - " $9 " (" $5 ")"}'
echo ""

# Calculate sizes
DB_SIZE=$(du -h "$OUTPUT_FILE" | cut -f1)
ZIP_SIZE=$(du -h "$OUTPUT_FILE.gz" | cut -f1)

echo "Database sizes:"
echo "  Original SQL: $DB_SIZE"
echo "  Compressed:   $ZIP_SIZE"
echo ""

echo "✅ Deployment package ready!"
echo ""
echo "Next steps:"
echo "1. Read: app/public/wp-content/themes/pilgrimirl/DEPLOYMENT_GUIDE.md"
echo "2. Review: deployment/upload-checklist.txt"
echo "3. Upload database: deployment/pilgrimirl-live-$DATE.sql.gz"
echo "4. Upload files via FTP (see checklist)"
echo ""
echo "🎯 Ready to deploy!"
