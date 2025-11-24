#!/bin/bash

echo "=== Creating production wp-config.php ==="

# Copy local wp-config.php and modify for production
php -r "
\$content = file_get_contents('app/public/wp-config.php');

// Replace database credentials
\$content = preg_replace(
    \"/define\( 'DB_NAME', 'local' \);/\",
    \"define( 'DB_NAME', 'u338184895_pilgrimirl' );\",
    \$content
);

\$content = preg_replace(
    \"/define\( 'DB_USER', 'root' \);/\",
    \"define( 'DB_USER', 'u338184895_pilgrimirl' );\",
    \$content
);

\$content = preg_replace(
    \"/define\( 'DB_PASSWORD', 'root' \);/\",
    \"define( 'DB_PASSWORD', '!LadyLumleys99' );\",
    \$content
);

// Remove local environment type
\$content = preg_replace(
    \"/define\( 'WP_ENVIRONMENT_TYPE', 'local' \);/\",
    \"define( 'WP_ENVIRONMENT_TYPE', 'production' );\",
    \$content
);

// Change WP_DEBUG to false (ensure it)
\$content = preg_replace(
    \"/define\( 'WP_DEBUG', true \);/\",
    \"define( 'WP_DEBUG', false );\",
    \$content
);

// Add security settings before 'stop editing' comment
\$securityBlock = \"
// Security Settings
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('WP_AUTO_UPDATE_CORE', 'minor');

// Performance
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

\";

\$content = preg_replace(
    \"/(\/\* That's all, stop editing! Happy publishing\. \*\/)/\",
    \$securityBlock . \"\$1\",
    \$content
);

file_put_contents('/tmp/wp-config-production.php', \$content);
echo \"✅ Production wp-config.php created\n\";
"

echo "=== Uploading to server ==="
SSHPASS='!LadyLumleys99' sshpass -e scp -P 65002 -o StrictHostKeyChecking=no /tmp/wp-config-production.php u338184895@141.136.33.138:/home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php

echo "=== Verifying syntax ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 'php -l /home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php'

echo ""
echo "=== Testing site ==="
sleep 2
curl -sI https://pilgrimirl.com | head -3

echo ""
echo "✅ Done!"
