#!/bin/bash

echo "=== Full wp-config.php content ==="
SSHPASS='!LadyLumleys99' sshpass -e ssh -p 65002 -o StrictHostKeyChecking=no u338184895@141.136.33.138 'cat /home/u338184895/domains/pilgrimirl.com/public_html/wp-config.php'
