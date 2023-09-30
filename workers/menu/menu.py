# -*- coding: utf-8 -*-
"""
Created on Fri Sep 22 22:43:17 2023

@author: Alois & Clément
"""
import requests
import datetime
import json
import subprocess
import schedule
import time
import os


def today():
    return datetime.date.today().isoformat()


def future(date, days):
    return (datetime.date.fromisoformat(date) + datetime.timedelta(days=days)).isoformat()


def get_weekday(date):
    return datetime.datetime.fromisoformat(date).weekday()


def write_to_file(dictionnary):
    path = "./../../src/menu/data/"
    if not os.path.exists(path):
        os.makedirs(path)
    with open(path + "menu.json", "w", encoding="utf8") as menu:
        json.dump(dictionnary, menu, ensure_ascii=False)


def fetch_menu_data(date, time_id, rest_id):
    base_url = f"https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/{rest_id}/1/{time_id}/{date}"
    # base_url = "https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/4/1/38/2023-09-25"
    req = None
    with requests.session() as s:
        try:
            req = s.get(base_url, headers={"Authorization": "Basic c2FsYW1hbmRyZTpzYWxhbQ=="}, timeout=(3, 5)).json();
        except requests.exceptions.ConnectTimeout:
            print("Connection Timeout, maybe the VPN password is wrong.")

    # print(f"Menu received for rest_id: {rest_id}, date: {date}, time_id: {time_id} ", req)
    return req["MenuSemaine"] if req else None


def get_menu_for_day(date):
    print("Requesting menu for " + date)
    weekday = get_weekday(date)

    # heure = {"midi_semaine": 32, "soir_semaine": 38, "midi_samedi": 123, "midi_dimanche": 125, "soir_dimanche": 126}
    # resto = {"olivier" : 6, "RI": 4}
    if weekday <= 4:
        ri_lunch = get_menu(date, 4, 32)
        ri_dinner = get_menu(date, 4, 38)
    elif weekday == 6:
        ri_lunch = get_menu(date, 4, 123)
        ri_dinner = None
    else:
        ri_lunch = get_menu(date, 4, 125)
        ri_dinner = get_menu(date, 4, 126)

    return {
        "date": {"day": datetime.datetime.fromisoformat(date).day,
                 "month": datetime.datetime.fromisoformat(date).month},
        "lunch": {
            "ri": ri_lunch,
            "olivier": get_menu(date, 6, 2) if weekday <= 4 else None,
        },
        "dinner": {
            "ri": ri_dinner,
            "olivier": None
        }
    }


def get_menu(date, rest_id, time_id):
    data = fetch_menu_data(date, time_id, rest_id)
    if data is None:
        return None

    entree, plat, sauce, garniture, fromage, dessert = [], [], [], [], [], []

    for el in data:
        if el["DatPlaMen"] == date:
            match el["LibCatFit"]:
                case "ENTREE":
                    entree.append(el["LibFit"])
                case "PLAT":
                    plat.append(el["LibFit"])
                case "SAUCE":
                    sauce.append(el["LibFit"])
                case "GARNITURE":
                    garniture.append(el["LibFit"])
                case "FROMAGE":
                    fromage.append(el["LibFit"])
                case "DESSERT":
                    dessert.append(el["LibFit"])

    return {"entree": entree, "plat": plat, "sauce": sauce, "garniture": garniture, "fromage": fromage,
            "dessert": dessert}


def get_menu_7days():
    days = []
    for i in range(7):
        days.append(get_menu_for_day(future(today(), i)))

    return {
        "last_update": datetime.datetime.now().isoformat(),
        "days": days
    }


def send_ntfy_notification(day_menu, is_lunch):
    print("Sending ntfy.sh notification...")
    struct_time = 'lunch' if is_lunch else 'dinner'

    menu = day_menu.get(struct_time)
    if menu is None:
        send_ntfy_notification_content("Pas de repas aujourd'hui")
        return

    if is_lunch:
        menu_olivier = menu.get('olivier')
        if menu_olivier is None:
            send_ntfy_notification_content("Pas d'Olivier aujourd'hui")
        else:
            plat = menu_olivier["plat"]
            text = ''
            for el in plat:
                text += "\n- " + el.split('<', 1)[0]
            send_ntfy_notification_content(f"Menu de l'Olivier disponible", text)

    menu_ri = menu.get('ri')
    if menu_ri is None:
        send_ntfy_notification_content("Pas de RI aujourd'hui")
    else:
        plat = menu_ri["plat"]
        garniture = menu_ri["garniture"]
        text = ''
        for el in plat:
            text += "\n- " + el.split('<', 1)[0]
        if garniture:
            text += "\n"
            for el in garniture:
                text += "\n- " + el.split('<', 1)[0]

        send_ntfy_notification_content(f"Menu du RI disponible", text)


def send_ntfy_notification_content(title, text=' '):
    requests.post("https://ntfy.sh/menu-insa",
                  data=text.encode(encoding='utf-8'),
                  headers={
                      "Title": title + f" - {datetime.date.today().day}/{datetime.date.today().month}",
                      "Tags": "plate_with_cutlery",
                      "Actions": "view, Voir menu, https://menu-restaurants.insa-lyon.fr/, clear=true"
                  })


def update_menu():
    menu = get_menu_7days()

    # Sending notification
    print(f"Checking to send notification... hour: {datetime.datetime.now().hour}")
    if 8 < datetime.datetime.now().hour < 14:
        send_ntfy_notification(menu["days"][0], True)
    elif 13 < datetime.datetime.now().hour < 21 and datetime.date.today().weekday() != 5:
        send_ntfy_notification(menu["days"][0], False)

    # Writing to file
    write_to_file(menu)


password = ""


def vpn_connect():
    subprocess.call(['sh', '-c', "echo \"" + password + "\" | sudo ./vpn_connect.sh"])
    # Waiting 5 seconds to make sure the VPN is connected
    time.sleep(5)


def vpn_disconnect():
    subprocess.call(['sh', '-c', 'sudo ./vpn_disconnect.sh'])


def recurrent_update():
    print("Connecting to the VPN...")
    vpn_connect()
    print("Connected.")
    print("Updating the menu...")
    update_menu()
    print("Menu updated.")
    print("Disconnecting from the VPN...")
    vpn_disconnect()
    print("Disconnected.")


print("Reading password...")
# Getting the VPN password from password.env
path = "/home/clement/insa-utils/workers/menu/password.env"
with open("password.env", "r") as f:
    password = f.read()

print("Password read from password.env")
subprocess.call(['sh', '-c', 'rm ' + path])

print("Scheduling updates...")
schedule.every().day.at("11:00").do(recurrent_update)
schedule.every().day.at("17:00").do(recurrent_update)
recurrent_update()

print("Waiting for updates...")
while True:
    schedule.run_pending()
    time.sleep(1)
