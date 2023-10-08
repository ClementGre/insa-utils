from menu_scrapping import *
from menu_notifications import *
from menu_utils import *

import requests
import datetime
import json
import subprocess
import schedule
import time
import os
import pywebpush
import mysql.connector

global vpn_password
global secrets
global menu


def update_menu():
    print("Connecting to the VPN...")
    vpn_connect()
    print("Connected.")
    print("Updating the menu...")

    global menu
    menu = get_whole_week_menu()
    write_menu_to_file(menu)

    print("Menu updated.")
    print("Disconnecting from the VPN...")
    vpn_disconnect()
    print("Disconnected.")


def recurrent_update(do_update, time_string):
    print(f"Recurrent update at {time_string}")
    if do_update:
        update_menu()
    send_notifications(time_string)


def register_recurrent_update(at, do_update):
    schedule.every().day.at(at).do(lambda: recurrent_update(do_update, at))


def main():
    global vpn_password
    global secrets

    print("Reading password...")
    path = "/home/clement/insa-utils/workers/menu/password.env"
    with open("password.env", "r") as f:
        vpn_password = f.read()

    print("Password read from password.env")
    subprocess.call(['sh', '-c', 'rm ' + path])

    print("Reading secrets...")
    with open("secrets.json", "r") as f:
        secrets = json.load(f)

    print("Scheduling updates...")
    register_recurrent_update("11:10", True)
    register_recurrent_update("11:30", False)
    register_recurrent_update("12:00", False)
    register_recurrent_update("12:30", False)

    register_recurrent_update("17:10", True)
    register_recurrent_update("17:30", False)
    register_recurrent_update("18:00", False)
    register_recurrent_update("18:30", False)

    print("Waiting for updates...")
    while True:
        schedule.run_pending()
        time.sleep(1)


if __name__ == "__main__":
    main()
