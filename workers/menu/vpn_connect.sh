sudo killall -SIGINT openconnect
sudo openconnect sslvpn.cisr.fr --protocol=anyconnect -u "$1" --authgroup=INSA --passwd-on-stdin --background
