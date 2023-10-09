import requests
import datetime
import json
import subprocess
import schedule
import time
import os
import pywebpush
import mysql.connector
import re

vpn_password_intern = None
secrets_dict = None
menu_dict = None


def set_vpn_password(pwd):
    global vpn_password_intern
    vpn_password_intern = pwd


def get_vpn_password():
    return vpn_password_intern


def set_secrets(secrets):
    global secrets_dict
    secrets_dict = secrets


def get_secrets():
    return secrets_dict


def set_menu(menu):
    global menu_dict
    menu_dict = menu


def get_menu():
    return menu_dict
