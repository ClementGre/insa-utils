from menu_config import *
from menu_utils import *

def send_notifications(time_string):
    print("Checking whether to send notifications...")
    menu = get_menu()
    if 8 < datetime.datetime.now().hour < 14:
        send_notifications_for_meal(menu["days"][datetime.date.today().weekday()], True, time_string)
    elif 13 < datetime.datetime.now().hour < 21 and datetime.date.today().weekday() != 5:
        send_notifications_for_meal(menu["days"][datetime.date.today().weekday()], False, time_string)


def send_notifications_for_meal(day_menu, is_lunch, time_string):
    print("Preparing notifications content text...")
    struct_time = 'lunch' if is_lunch else 'dinner'
    is_weekend = datetime.date.today().weekday() == 6 or datetime.date.today().weekday() == 0

    # Olivier
    menu = day_menu.get(struct_time)
    if menu is None:
        send_notifications_content("Pas de repas aujourd'hui")
        return

    if is_lunch:
        menu_olivier = menu.get('olivier')
        if menu_olivier is None:
            if datetime.date.today().weekday() <= 4:
                send_notifications_content("Pas d'Olivier aujourd'hui",
                                           "Le menu de l'Olivier n'est pas disponible ou le restaurant est fermé.",
                                           False, True, is_weekend, time_string)
        else:
            plat = menu_olivier["plat"]
            text = ''
            for el in plat:
                text += "\n- " + el.sub('<[A-Z]*>', '', el)
            send_notifications_content(f"Menu de l'Olivier disponible", text, False, True, is_weekend, time_string)

    # RI
    menu_ri = menu.get('ri')
    if menu_ri is None:
        if not is_lunch and datetime.date.today().weekday() != 5:
            send_notifications_content("Pas de RI aujourd'hui",
                                       "Le menu du RI n'est pas disponible ou le restaurant est fermé.",
                                       True, False, is_weekend, time_string)
    else:
        plat = menu_ri["plat"]
        garniture = menu_ri["garniture"]
        text = ''
        for el in plat:
            text += "\n- " + el.sub('<[A-Z]*>', '', el)
        if garniture:
            text += "\n"
            for el in garniture:
                text += "\n- " + el.sub('<[A-Z]*>', '', el)

        send_notifications_content(f"Menu du RI disponible", text, True, is_lunch, is_weekend, time_string)


def send_notifications_content(title, text, is_ri, is_lunch, is_weekend, time_string):
    if time_string == '11:10' or time_string == '17:10':
        print("Sending ntfy.sh notifications...")
        try:
            headers = {
                "Title": title + f" - {datetime.date.today().day}/{datetime.date.today().month}",
                "Tags": "plate_with_cutlery",
                "Actions": "view, Voir menu, https://menu-restaurants.insa-lyon.fr/, clear=true"
            }
            if text == '':
                requests.post("https://ntfy.sh/menu-insa",
                              headers=headers)
            else:
                requests.post("https://ntfy.sh/menu-insa",
                              data=text.encode(encoding='utf-8'),
                              headers=headers)
        except Exception as e:
            print("Unable to send ntfy.sh notifications:", e)

    print("Sending webpush notification...")

    try:
        db = db_connect()
        cursor = db.cursor()

        cursor.execute("""SELECT * FROM menu_subscriptions WHERE
                        (lunch_time = %s OR dinner_time = %s)
                        AND (
                            (olivier = True AND olivier = %s)
                            OR
                            (
                                (
                                    (ri_lunch = True AND ri_lunch = %s)
                                    OR
                                    (ri_dinner = True AND ri_dinner = %s)
                                )
                                AND (ri_weekend = True OR ri_weekend = %s)
                            )
                        )""",
                       (time_string, time_string,
                        not is_ri,
                        is_ri and is_lunch,
                        is_ri and not is_lunch,
                        is_ri and is_weekend))



        columns = cursor.description
        res = [{columns[index][0]: column for index, column in enumerate(value)} for value in cursor.fetchall()]
        for sub in res:
            try:
                pywebpush.webpush(
                    {
                        "endpoint": sub['endpoint'],
                        "keys": {"auth": sub['key_auth'], "p256dh": sub['key_p256dh']}
                    },
                    json.dumps({
                        "title": title,
                        "body": text,
                        "is_olivier": not is_ri
                    }),
                    vapid_private_key=get_secrets()['webpush']['privateKey'],
                    vapid_claims={"sub": "mailto:clement.grennerat@free.fr'}"}
                )
            except Exception as e:
                print("Unable to send individual webpush notifications:", e)

    except Exception as e:
        print("Unable to send webpush notifications:", e)
