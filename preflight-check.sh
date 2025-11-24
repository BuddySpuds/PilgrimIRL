#!/bin/bash

# PilgrimIRL Pre-Flight Deployment Checks
# Run this before deploying to catch issues early

echo "✈️  PilgrimIRL Pre-Flight Deployment Checks"
echo "=========================================="
echo ""

ERRORS=0
WARNINGS=0

# Color codes
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

# Helper functions
pass() {
    echo -e "${GREEN}✅ PASS${NC}: $1"
}

warn() {
    echo -e "${YELLOW}⚠️  WARN${NC}: $1"
    ((WARNINGS++))
}

fail() {
    echo -e "${RED}❌ FAIL${NC}: $1"
    ((ERRORS++))
}

echo "🔍 Checking theme files..."
echo ""

# Check 1: Theme directory exists
if [ -d "app/public/wp-content/themes/pilgrimirl" ]; then
    pass "Theme directory exists"
else
    fail "Theme directory not found"
fi

# Check 2: Required theme files
REQUIRED_FILES=(
    "app/public/wp-content/themes/pilgrimirl/style.css"
    "app/public/wp-content/themes/pilgrimirl/functions.php"
    "app/public/wp-content/themes/pilgrimirl/page.php"
    "app/public/wp-content/themes/pilgrimirl/header.php"
    "app/public/wp-content/themes/pilgrimirl/footer.php"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        pass "Found: $(basename $file)"
    else
        fail "Missing: $file"
    fi
done

echo ""
echo "🔍 Checking security..."
echo ""

# Check 3: Security audit report exists
if [ -f "app/public/wp-content/themes/pilgrimirl/SECURITY_AUDIT_REPORT.md" ]; then
    pass "Security audit completed"
else
    warn "Security audit report not found"
fi

# Check 4: No debug scripts in production
DEBUG_FILES=(
    "app/public/wp-content/themes/pilgrimirl/*-debug.php"
    "app/public/wp-content/themes/pilgrimirl/test-*.php"
    "app/public/wp-content/themes/pilgrimirl/test-*.js"
)

for pattern in "${DEBUG_FILES[@]}"; do
    if ls $pattern 2>/dev/null | grep -q .; then
        warn "Debug/test files found - should be removed before deployment"
    fi
done

echo ""
echo "🔍 Checking content..."
echo ""

# Check 5: Standard pages exist
cd app/public
PAGES=("about" "contact" "privacy-policy" "terms")
for page in "${PAGES[@]}"; do
    if wp post list --post_type=page --name="$page" --format=count 2>/dev/null | grep -q "1"; then
        pass "Page exists: $page"
    else
        fail "Page missing: $page"
    fi
done

# Check 6: Check for empty content
echo ""
echo "Checking for empty pages..."
EMPTY_COUNT=$(wp post list --post_type=page --post_status=publish --format=count --fields=post_content 2>/dev/null | grep -c "^$" || echo "0")
if [ "$EMPTY_COUNT" = "0" ]; then
    pass "No empty pages found"
else
    warn "$EMPTY_COUNT pages have no content"
fi

cd ../..

echo ""
echo "🔍 Checking assets..."
echo ""

# Check 7: Minified CSS exists
if [ -f "app/public/wp-content/themes/pilgrimirl/css/homepage-filters.min.css" ]; then
    pass "Minified CSS files exist"
else
    warn "Minified CSS not found - run npm build"
fi

# Check 8: Minified JS exists
if [ -f "app/public/wp-content/themes/pilgrimirl/js/maps.min.js" ]; then
    pass "Minified JS files exist"
else
    warn "Minified JS not found - run npm build"
fi

echo ""
echo "🔍 Checking configuration..."
echo ""

# Check 9: Check for localhost URLs in theme files
LOCALHOST_COUNT=$(grep -r "localhost:10028" app/public/wp-content/themes/pilgrimirl/ 2>/dev/null | wc -l | tr -d ' ')
if [ "$LOCALHOST_COUNT" = "0" ]; then
    pass "No hardcoded localhost URLs in theme"
else
    warn "Found $LOCALHOST_COUNT hardcoded localhost URLs - will be fixed in database"
fi

# Check 10: Google Maps API key configured
cd app/public
API_KEY=$(wp option get pilgrimirl_google_maps_api_key 2>/dev/null || echo "")
if [ ! -z "$API_KEY" ]; then
    pass "Google Maps API key is configured"
else
    warn "Google Maps API key not set - configure after deployment"
fi
cd ../..

echo ""
echo "🔍 Checking documentation..."
echo ""

# Check 11: Deployment guide exists
if [ -f "app/public/wp-content/themes/pilgrimirl/DEPLOYMENT_GUIDE.md" ]; then
    pass "Deployment guide available"
else
    fail "Deployment guide missing"
fi

# Check 12: SEO audit exists
if [ -f "app/public/wp-content/themes/pilgrimirl/SEO_AUDIT.md" ]; then
    pass "SEO audit documentation available"
else
    warn "SEO audit not found"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 PRE-FLIGHT CHECK SUMMARY"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅ ALL CHECKS PASSED${NC}"
    echo ""
    echo "🚀 Ready for deployment!"
    echo ""
    echo "Next steps:"
    echo "1. Run: ./deploy-helper.sh"
    echo "2. Follow: DEPLOYMENT_GUIDE.md"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠️  $WARNINGS WARNING(S)${NC}"
    echo ""
    echo "🟡 Deploy with caution"
    echo ""
    echo "Review warnings above before deploying."
    echo "Most warnings are non-critical but should be addressed."
    exit 1
else
    echo -e "${RED}❌ $ERRORS ERROR(S), $WARNINGS WARNING(S)${NC}"
    echo ""
    echo "🔴 NOT READY FOR DEPLOYMENT"
    echo ""
    echo "Fix errors above before deploying."
    echo "Site may not work correctly if deployed now."
    exit 2
fi
