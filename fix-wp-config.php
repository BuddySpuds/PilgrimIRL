#!/usr/bin/env php
<?php
/**
 * Fix wp-config.php with correct database credentials
 */

$wpConfigPath = '/home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php';

// Read the current wp-config.php
$content = file_get_contents($wpConfigPath);

// Backup first
file_put_contents($wpConfigPath . '.backup-fix', $content);

// Remove any incorrectly added defines at the end
$content = preg_replace('/\n\/\/ Security\s+define\(\'?DISALLOW_FILE_EDIT.*$/s', '', $content);
$content = preg_replace('/\ndefine\(DISALLOW_FILE_EDIT.*$/s', '', $content);
$content = preg_replace('/\ndefine\(FORCE_SSL_ADMIN.*$/s', '', $content);
$content = preg_replace('/\ndefine\(WP_DEBUG.*$/s', '', $content);

// Replace database credentials properly
$content = preg_replace(
    "/define\(\s*'DB_NAME'\s*,\s*'[^']*'\s*\);/",
    "define( 'DB_NAME', 'u338184895_pilgrimirl' );",
    $content
);

$content = preg_replace(
    "/define\(\s*'DB_USER'\s*,\s*'[^']*'\s*\);/",
    "define( 'DB_USER', 'u338184895_pilgrimirl' );",
    $content
);

$content = preg_replace(
    "/define\(\s*'DB_PASSWORD'\s*,\s*'[^']*'\s*\);/",
    "define( 'DB_PASSWORD', '!LadyLumleys99' );",
    $content
);

$content = preg_replace(
    "/define\(\s*'DB_HOST'\s*,\s*'[^']*'\s*\);/",
    "define( 'DB_HOST', 'localhost' );",
    $content
);

// Add security defines properly before the "That's all" comment
if (!preg_match('/DISALLOW_FILE_EDIT/', $content)) {
    $content = preg_replace(
        "/(\/\*.*?That's all.*?\*\/)/s",
        "// Security Settings\ndefine('DISALLOW_FILE_EDIT', true);\ndefine('FORCE_SSL_ADMIN', true);\n\n// Debug Settings\ndefine('WP_DEBUG', false);\ndefine('WP_DEBUG_LOG', false);\ndefine('WP_DEBUG_DISPLAY', false);\n\n$1",
        $content
    );
}

// Write the fixed content
file_put_contents($wpConfigPath, $content);

echo "✅ wp-config.php fixed successfully!\n";
echo "✅ Backup saved to: wp-config.php.backup-fix\n";
