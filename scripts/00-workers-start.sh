#!/bin/bash

# Starting menu worker
echo "Starting menu worker"

echo "$VPN_PASSWORD" > /var/www/html/workers/menu/password.env
unset VPN_PASSWORD

# installing python dependencies
pip install -r /var/www/html/workers/menu/requirements.txt

python /var/www/html/workers/menu/menu.py