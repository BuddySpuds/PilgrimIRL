#!/bin/bash

echo "=== Downloading wp-config.php from server ==="
SSHPASS='!LadyLumleys99' sshpass -e scp -P 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138:/home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php /tmp/wp-config-server.php

echo "=== Cleaning up wp-config.php ==="
php -r "
\$content = file_get_contents('/tmp/wp-config-server.php');

// Remove all the broken defines at the end
\$content = preg_replace('/\ndefine\(DISALLOW_FILE_EDIT.*$/s', '', \$content);
\$content = preg_replace('/\ndefine\(FORCE_SSL_ADMIN.*$/s', '', \$content);
\$content = preg_replace('/\ndefine\(WP_DEBUG.*$/s', '', \$content);

// Ensure proper database credentials
\$content = preg_replace(
    \"/define\(\s*'DB_NAME'\s*,\s*'[^']*'\s*\);/\",
    \"define( 'DB_NAME', 'u338184895_pilgrimirl' );\",
    \$content
);

\$content = preg_replace(
    \"/define\(\s*'DB_USER'\s*,\s*'[^']*'\s*\);/\",
    \"define( 'DB_USER', 'u338184895_pilgrimirl' );\",
    \$content
);

\$content = preg_replace(
    \"/define\(\s*'DB_PASSWORD'\s*,\s*'[^']*'\s*\);/\",
    \"define( 'DB_PASSWORD', '!LadyLumleys99' );\",
    \$content
);

\$content = preg_replace(
    \"/define\(\s*'DB_HOST'\s*,\s*'[^']*'\s*\);/\",
    \"define( 'DB_HOST', 'localhost' );\",
    \$content
);

// Add proper security defines before the 'stop editing' comment
if (!preg_match('/DISALLOW_FILE_EDIT/', \$content)) {
    \$content = preg_replace(
        \"/(\/\*.*?stop editing.*?\*\/)/si\",
        \"// Security Settings\ndefine('DISALLOW_FILE_EDIT', true);\ndefine('FORCE_SSL_ADMIN', true);\n\n// Debug Settings (off for production)\ndefine('WP_DEBUG', false);\ndefine('WP_DEBUG_LOG', false);\ndefine('WP_DEBUG_DISPLAY', false);\n\n\$1\",
        \$content
    );
}

file_put_contents('/tmp/wp-config-fixed.php', \$content);
echo \"✅ Cleaned up wp-config.php\n\";
"

echo "=== Uploading fixed wp-config.php ==="
SSHPASS='!LadyLumleys99' sshpass -e scp -P 65002 -o StrictHostKeyChecking=no /tmp/wp-config-fixed.php u338184895@141.136.33.138:/home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php

echo "=== Verifying syntax ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 'php -l /home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php'

echo ""
echo "✅ Done! Testing site..."
curl -sI https://pilgrimirl.com | head -3
