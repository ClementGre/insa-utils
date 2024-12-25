killall -SIGINT openconnect
openconnect sslvpn.cisr.fr --protocol=anyconnect -u "$1" --authgroup=INSA --passwd-on-stdin --background
