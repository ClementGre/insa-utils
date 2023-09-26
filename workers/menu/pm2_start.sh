echo -n Password:
read -s password
echo
echo "$password" > password.env
pm2 start menu.py --name INSApetitScrapper --time
