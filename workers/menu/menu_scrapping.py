from menu_utils import *


def fetch_menu_data(date, time_id, rest_id):
    url = f"https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/{rest_id}/1/{time_id}/{date}"
    # url = "https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/4/1/38/2023-09-25"
    req = None
    with requests.session() as s:
        try:
            req = s.get(url, headers={"Authorization": "Basic c2FsYW1hbmRyZTpzYWxhbQ=="}, timeout=(3, 5)).json();
        except requests.exceptions.ConnectTimeout:
            print("Connection Timeout, maybe the VPN password is wrong.")

    # print(f"Menu received for rest_id: {rest_id}, date: {date}, time_id: {time_id} ", req)
    return req["MenuSemaine"] if req else None


def get_menu_for_day(date):
    weekday = get_weekday(date)
    print(f"Requesting menu for {date} (weekday: {weekday})")

    # heure = {"midi_semaine": 32, "soir_semaine": 38, "midi_samedi": 123, "midi_dimanche": 125, "soir_dimanche": 126}
    # resto = {"olivier" : 6, "RI": 4}
    if weekday <= 4:
        ri_lunch = get_menu_for_meal(date, 4, 32)
        ri_dinner = get_menu_for_meal(date, 4, 38)
    elif weekday == 5:
        ri_lunch = get_menu_for_meal(date, 4, 123)
        ri_dinner = None
    else:
        ri_lunch = get_menu_for_meal(date, 4, 125)
        ri_dinner = get_menu_for_meal(date, 4, 126)

    return {
        "date": {"day": datetime.datetime.fromisoformat(date).day,
                 "month": datetime.datetime.fromisoformat(date).month},
        "lunch": {
            "ri": ri_lunch,
            "olivier": get_menu_for_meal(date, 6, 2) if weekday <= 4 else None,
        },
        "dinner": {
            "ri": ri_dinner,
            "olivier": None
        }
    }


def get_menu_for_meal(date, rest_id, time_id):
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


def get_whole_week_menu():
    days = []
    before = get_weekday(get_date_iso())
    after = 7 - before

    if before > 0:
        for i in range(before, 0, -1):
            days.append(get_menu_for_day(get_future_date_iso(get_date_iso(), -i)))
    for i in range(0, after, 1):
        days.append(get_menu_for_day(get_future_date_iso(get_date_iso(), i)))

    return {
        "last_update": datetime.datetime.now().isoformat(),
        "days": days
    }

