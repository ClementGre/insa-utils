#!/bin/bash

echo ">>> Installing python requirements for menu worker"
pip install -r /var/www/html/workers/menu/requirements.txt

#echo "Reading VPN password for menu worker"
cd /var/www/html/workers/menu/ || exit;
#echo "$VPN_PASSWORD" > password.env
#unset VPN_PASSWORD

echo ">>> Starting menu worker as job"
python -u menu.py 2>&1 &