#!/bin/bash

start_script() {
  local passwd="$1"

  while true; do
    read -n1 char < openconnect-pipe

    if [[ $char == "c" ]]; then
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] Received 'c'. Connecting to VPN..." | tee -a openconnect-log
      sudo killall -SIGINT openconnect 2>&1 | sudo tee -a openconnect-log
      echo "$passwd" | sudo openconnect sslvpn.cisr.fr --protocol=anyconnect \
        -u "$OPENCONNECT_USER" --authgroup=INSA --passwd-on-stdin \
        --background | sudo tee -a openconnect-log &

    elif [[ $char == "d" ]]; then
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] Received 'd'. Disconnecting from the VPN..." | tee -a openconnect-log
      sudo killall -SIGINT openconnect 2>&1 | sudo tee -a openconnect-log
    else
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] Unknown character received: $char"
    fi
  done
}


# Create FIFO if not exists
mkfifo openconnect-pipe

# Trap INT to exit cleanly
trap "rm openconnect-pipe; exit" INT

# Prompt for password
read -s -p "Enter your VPN password: " passwd
echo

# Create log file
rm -f openconnect-log
touch openconnect-log

# Store the password as an environment variable
export PASSWD="$passwd"

# Start the script in the background and disown it
start_script "${passwd}" > /dev/null &
disown -h %1

echo "Script started successfully. To stop the script, use kill <PID> or find its PID with ps aux | grep openconnect-worker.sh"
