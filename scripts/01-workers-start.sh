#!/bin/bash

echo "Installing python requirements for menu worker"
pip install -r /var/www/html/workers/menu/requirements.txt

echo "Reading VPN password for menu worker"
echo "$VPN_PASSWORD" > /var/www/html/workers/menu/password.env
unset VPN_PASSWORD

echo "Starting menu worker"
python /var/www/html/workers/menu/menu.py