from menu_scrapping import *
from menu_notifications import *
import schedule
import time
import subprocess

do_use_vpn = True

def update_menu():
    if do_use_vpn:
        print("Connecting to the VPN...")
        vpn_connect()
        print("Connected.")
    print("Updating the menu...")

    set_menu(get_whole_week_menu())
    write_menu_to_file(get_menu())

    print("Menu updated.")
    if do_use_vpn:
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

def print_vpn_pwd_main():
    print(get_vpn_password())

def main():
    # if do_use_vpn:
    #     print("Reading password...")
    #     path = "/var/www/html/workers/menu/password.env"
    #     with open("password.env", "r") as f:
    #         set_vpn_password(f.read())
    #
    #     print("Password read from password.env")
    #     subprocess.call(['sh', '-c', 'rm ' + path])

    print("Updating menu...")
    update_menu()

    print("Scheduling updates...")
    register_recurrent_update("11:00", False)
    register_recurrent_update("11:30", True)
    register_recurrent_update("12:00", True)
    register_recurrent_update("12:30", True)

    register_recurrent_update("17:00", False)
    register_recurrent_update("17:30", True)
    register_recurrent_update("18:00", False)
    register_recurrent_update("18:30", False)

    print("Waiting for updates...")
    while True:
        schedule.run_pending()
        time.sleep(1)


def local_test():
    set_menu(get_whole_week_menu())
    write_menu_to_file(get_menu())
    send_notifications("11:10")


if __name__ == "__main__":
    main()
