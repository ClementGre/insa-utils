from menu_config import *
import datetime
import json
import os
import subprocess
import time
import mysql.connector

# Dates

def get_date_iso():
    return datetime.date.today().isoformat()


def get_future_date_iso(date_iso, days):
    return (datetime.date.fromisoformat(date_iso) + datetime.timedelta(days=days)).isoformat()


def get_weekday(date_iso):
    return datetime.datetime.fromisoformat(date_iso).weekday()


# Menu writing/reading

def write_menu_to_file(menu_dict):
    path = "./../../src/menu/data/"
    if not os.path.exists(path):
        os.makedirs(path)
    with open(path + "menu.json", "w", encoding="utf8") as f:
        json.dump(menu_dict, f, ensure_ascii=False)


def read_menu_from_file():
    path = "./../../src/menu/data/menu.json"
    if not os.path.exists(path):
        return None
    with open(path, "r", encoding="utf8") as f:
        return json.load(f)


# VPN

def vpn_connect():
    subprocess.call(['sh', '-c', f"echo \"{get_vpn_password()}\" | ./vpn_connect.sh {os.environ['VPN_USER']}"])
    # Waiting 5 seconds to make sure the VPN is connected
    time.sleep(5)


def vpn_disconnect():
    subprocess.call(['sh', '-c', './vpn_disconnect.sh'])


def print_vpn_pwd():
    print(get_vpn_password())


# Database

def db_connect():
    return mysql.connector.connect(
        host=os.environ['DB_HOST'],
        user="insa-utils",
        password=os.environ['DB_PASSWORD'],
        database="insa-utils"
    )
