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


def today():
    return datetime.date.today().isoformat()


def future(date, days):
    return (datetime.date.fromisoformat(date) + datetime.timedelta(days=days)).isoformat()


def get_weekday(date):
    return datetime.datetime.fromisoformat(date).weekday()


def write_to_file(dictionnary):
    with open("./../../src/menu/data/menu.json", "w", encoding="utf8") as menu:
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
    data = get_menu(date, time_id, rest_id);
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
    res = []
    for i in range(7):
        res.append(get_menu_for_day(future(today(), i)))
    return res


def send_ntfy_notification(time):
    if time not in ["midi", "soir"]:
        return "Enter a valid time"
    d = get_menu_for_day(today())
    p = []
    try:
        p = d[time]["ri"]["plat"]
    except:
        data = "Pas de repas au RI"

    if p:
        data = "Menu :"
        for el in p:
            data += "\n- " + el["Dish"]

    requests.post("https://ntfy.sh/menu-insa",
                  data=data.encode(encoding='utf-8'),
                  headers={
                      "Title": "Menu disponible",
                      "Tags": "plate_with_cutlery",
                  })
    return data


def update_menu():
    menu = get_menu_7days()

    # Sending notification
    if 8 < datetime.datetime.now().hour < 14:
        send_ntfy_notification("midi")
    elif 13 < datetime.datetime.now().hour < 21 and datetime.date.today().weekday() != 5:
        send_ntfy_notification("soir")

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


# Getting the VPN password from password.env
with open("./password.env", "r") as f:
    password = f.read()
subprocess.call(['sh', '-c', 'rm ./password.env'])

schedule.every().day.at("10:00").do(recurrent_update)
schedule.every().day.at("16:00").do(recurrent_update)
recurrent_update()

while True:
    schedule.run_pending()
    time.sleep(1)
