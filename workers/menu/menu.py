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


def get_menu(date, time_id, rest_id):
    base_url = f"https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/{rest_id}/1/{time_id}/{date}"
    # base_url = "https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/4/1/38/2023-09-25"
    req = None
    with requests.session() as s:
        try:
            req = s.get(base_url, headers={"Authorization": "Basic c2FsYW1hbmRyZTpzYWxhbQ=="}, timeout=(3, 5)).json();
        except requests.exceptions.ConnectTimeout:
            print("Connection Timeout, maybe the VPN password is wrong.")

    # print(f"Menu received for rest_id: {rest_id}, date: {date}, time_id: {time_id} ", req)
    return req["MenuSemaine"] if req else {}


def get_menu_for_day(date):
    print("Requesting menu for " + date)
    weekday = get_weekday(date)

    # heure = {"midi_semaine": 32, "soir_semaine": 38, "midi_samedi": 123, "midi_dimanche": 125, "soir_dimanche": 126}
    # resto = {"olivier" : 6, "RI": 4}
    if weekday <= 4:
        ri_lunch = process_menu(date, 4, 32)
        ri_dinner = process_menu(date, 4, 38)
    elif weekday == 6:
        ri_lunch = process_menu(date, 4, 123)
        ri_dinner = {}
    else:
        ri_lunch = process_menu(date, 4, 125)
        ri_dinner = process_menu(date, 4, 126)

    return {
        "date": {"day": datetime.datetime.fromisoformat(date).day,
                 "month": datetime.datetime.fromisoformat(date).month},
        "lunch": {
            "ri": ri_lunch,
            "olivier": process_menu(date, 6, 2) if weekday <= 4 else {},
        },
        "dinner": {
            "ri": ri_dinner,
        }
    }


def process_menu(date, rest_id, time_id):
    data = get_menu(date, time_id, rest_id)
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
        "last_update": datetime.date.today().isoformat(),
        "days": days
    }


def send_ntfy_notification(day_menu, is_lunch):
    print("Sending ntfy.sh notification...")
    time = 'lunch' if is_lunch else 'dinner'

    p, g, o = [], [], []
    try:
        p = day_menu[time]["ri"]["plat"]
        g = day_menu[time]["ri"]["garniture"]
        if is_lunch:
            o = day_menu[time]["olivier"]["plat"]
        data = ""
    except:
        data = "Pas de repas au RI"

    if p:
        data += "RI :"
        for el in p:
            if "<" in el:
                el = el[:el.find("<")]
            data += "\n- " + el
        if g:
            data += "\n"
            for el in g:
                if "<" in el:
                    el = el[:el.find("<")]
                data += "\n- " + el
        if o:
            data += "\n\n"
    if o:
        data += "Olivier :"
        for el in o:
            if "<" in e:
                el = el[:el.find("<")]
            data += "\n- " + el

    requests.post("https://ntfy.sh/menu-insa",
                  data=data.encode(encoding='utf-8'),
                  headers={
                      "Title": f"Menu disponible - {datetime.date.today().day}/{datetime.date.today().month}",
                      "Tags": "plate_with_cutlery",
                      "Actions": "view, Voir menu, https://menu-restaurants.insa-lyon.fr/, clear=true"
                  })
    return data


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
