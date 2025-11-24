#!/bin/bash

# Check wp-config.php syntax
echo "=== Checking PHP syntax ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 'php -l /home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php'

echo ""
echo "=== Checking wp-config.php database settings ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 'head -40 /home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php'
