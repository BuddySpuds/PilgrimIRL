#!/bin/bash

echo "=== Enabling WP_DEBUG temporarily ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 "
cd /home/u338184895/domains/pilgrimirl.com/public_html
cp wp-config.php wp-config.php.backup-debug

# Enable debug mode
sed -i \"s/define('WP_DEBUG', false);/define('WP_DEBUG', true);/\" wp-config.php
sed -i \"s/define('WP_DEBUG_LOG', false);/define('WP_DEBUG_LOG', true);/\" wp-config.php
sed -i \"s/define('WP_DEBUG_DISPLAY', false);/define('WP_DEBUG_DISPLAY', true);/\" wp-config.php

echo 'Debug enabled'
"

echo ""
echo "=== Attempting to load site ==="
curl -s https://pilgrimirl.com 2>&1 | head -50

echo ""
echo "=== Checking debug.log ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 'tail -20 /home/u338184895/domains/pilgrimirl.com/public_html/wp-content/debug.log 2>/dev/null || echo "No debug log yet"'
