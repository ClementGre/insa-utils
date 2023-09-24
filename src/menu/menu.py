# -*- coding: utf-8 -*-
"""
Created on Fri Sep 22 22:43:17 2023

@author: Alois
"""
import requests
import datetime
import json

def today():
    return datetime.date.today().isoformat()

def past(date, days):
    return (datetime.date.fromisoformat(date) - datetime.timedelta(days=days)).isoformat()

def future(date, days):
    return (datetime.date.fromisoformat(date) + datetime.timedelta(days=days)).isoformat()

def write_to_file(dictionnary):
    with open("menu.json", "w", encoding="utf8") as menu :
        json.dump(dictionnary, menu, ensure_ascii=False)

def get_menu(date, num_resto, num_heure):
    base_url = f"https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/{num_resto}/1/{num_heure}/{date}"
    # base_url = "https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/4/1/38/2023-09-25"
    with requests.session() as s: 
        req = s.get(base_url, headers={"Authorization": "Basic c2FsYW1hbmRyZTpzYWxhbQ=="}).json()
                
    
    return req["MenuSemaine"]

def find_label(intitule):
    liste_label =[
        {
            "TagLbl": "<BBC>",
            "LibLbl": "Bleu Blanc Coeur"
            },
        {
            "TagLbl": "<VF>",
            "LibLbl": "Viande française"
            },
        {
            "TagLbl": "<FLF>",
            "LibLbl": "Fruits et légumes de France"
            },
        {
            "TagLbl": "<FM>",
            "LibLbl": "Fait maison"
            },
        {
            "TagLbl": "<VEG>",
            "LibLbl": "Végétarien"
            },
        {
            "TagLbl": "<HVE>",
            "LibLbl": "Haute valeur environnementale"
            },
        {
            "TagLbl": "<BIO>",
            "LibLbl": "Bio"
            }
        ]
    label = None
    if "<" in intitule :
        for lel in liste_label :
            if lel['TagLbl'] in intitule :
                intitule = intitule.replace(str(lel['TagLbl']), "")
                label = lel['LibLbl']
    return label, intitule

# heure = {"midi_semaine": 32, "soir_semaine": 38, "midi_sammedi": 123, "midi_dimanche": 125, "soir_dimanche": 126}
# resto = {"olivier" : 6, "RI": 4}

def get_day(date):
    
    res = {
        "date": {"day":  datetime.datetime.fromisoformat(date).day, "month":  datetime.datetime.fromisoformat(date).month},
        "midi": {
            "ri": None,
            "olivier": None,
            },
        "soir": {
            "ri": None,
            },
        }
            
    ### RI
    if datetime.datetime.fromisoformat(date).weekday() < 5 : # jour de la semaine
        midi_r = get_menu(date, 4, 32)
        soir_r = get_menu(date, 4, 38)
    elif datetime.datetime.fromisoformat(date).weekday() < 6 : # samedi
        midi_r = get_menu(date, 4, 123)
        soir_r = {}
    else : # dimanche
        midi_r = get_menu(date, 4, 125)
        soir_r = get_menu(date, 4, 126)
        
    if midi_r :
        entree, plat, sauce, garniture, fromage,  dessert = [], [], [], [], [], []
        for el in midi_r :
            if el["DatPlaMen"] == date :
                if el["LibCatFit"] == "ENTREE":
                    label, intitule = find_label(el["LibFit"])
                    entree.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "PLAT":
                    label, intitule = find_label(el["LibFit"])
                    plat.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "SAUCE":
                    label, intitule = find_label(el["LibFit"])
                    sauce.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "GARNITURE":
                    label, intitule = find_label(el["LibFit"])
                    garniture.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "FROMAGE":
                    label, intitule = find_label(el["LibFit"])
                    fromage.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "DESSERT":
                    label, intitule = find_label(el["LibFit"])
                    dessert.append({"Dish": intitule, "Label": label})
        
        res["midi"]["ri"] = {"entree": entree, "plat": plat, "sauce": sauce, "garniture": garniture, "fromage": fromage,  "dessert": dessert}
    
    
    if soir_r :
        entree, plat, sauce, garniture, fromage,  dessert = [], [], [], [], [], []
        for el in soir_r :
            if el["DatPlaMen"] == date :
                if el["LibCatFit"] == "ENTREE":
                    label, intitule = find_label(el["LibFit"])
                    entree.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "PLAT":
                    label, intitule = find_label(el["LibFit"])
                    plat.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "SAUCE":
                    label, intitule = find_label(el["LibFit"])
                    sauce.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "GARNITURE":
                    label, intitule = find_label(el["LibFit"])
                    garniture.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "FROMAGE":
                    label, intitule = find_label(el["LibFit"])
                    fromage.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "DESSERT":
                    label, intitule = find_label(el["LibFit"])
                    dessert.append({"Dish": intitule, "Label": label})
        
        res["soir"]["ri"] = {"entree": entree, "plat": plat, "sauce": sauce, "garniture": garniture, "fromage": fromage,  "dessert": dessert}
        
        
    ### Olivier
    if datetime.datetime.fromisoformat(date).weekday() < 5 : # jour de la semaine
        midi_o = get_menu(date, 6, 2)
    elif datetime.datetime.fromisoformat(date).weekday() < 6 : # samedi
        midi_o = {}
    else : # dimanche
        midi_o = {}
        
    if midi_o :
        entree, plat, sauce, garniture, fromage,  dessert = [], [], [], [], [], []
        for el in midi_o :
            if el["DatPlaMen"] == date :
                if el["LibCatFit"] == "ENTREE":
                    label, intitule = find_label(el["LibFit"])
                    entree.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "PLAT":
                    label, intitule = find_label(el["LibFit"])
                    plat.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "SAUCE":
                    label, intitule = find_label(el["LibFit"])
                    sauce.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "GARNITURE":
                    label, intitule = find_label(el["LibFit"])
                    garniture.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "FROMAGE":
                    label, intitule = find_label(el["LibFit"])
                    fromage.append({"Dish": intitule, "Label": label})
                elif el["LibCatFit"] == "DESSERT":
                    label, intitule = find_label(el["LibFit"])
                    dessert.append({"Dish": intitule, "Label": label})
        
        res["midi"]["olivier"] = {"entree": entree, "plat": plat, "sauce": sauce, "garniture": garniture, "fromage": fromage,  "dessert": dessert}
            
    return res

def get_around_date(date, before=0, after=0, res=[]):
    if abs(before) > 0 :
        for i in range(abs(before), 0, -1):
            res.append(get_day(past(date, i)))
    for i in range(0, after, 1):
        res.append(get_day(future(date, i)))
    
    return res

def get_current_week():
    res = []
    t = datetime.datetime.now().weekday() # 0 -> lundi, 6 -> dimanche
    return get_around_date(today(), t, 7-t, res)

def get_weeks(date, nb_weeks):
    """
    Renvoie le menu de plusieurs semaines à l'avance'

    Parameters
    ----------
    date : str au format ISO (2023-09-24 pour le 24 septembre 2023)
        date d'origine du calcul, peut-être une des fonctions today(), past(date, i) ou future(date, i)
    nb_weeks : int
        Nombre de semaines à l'avance à renvoyer. 0 -> semaine de la date, 1 -> semaine de la date + semaine d'après ...

    Returns
    -------
    res
        liste des menus des jours

    """
    res = []
    t = datetime.datetime.fromisoformat(date).weekday() # 0 -> lundi, 6 -> dimanche
    for i in range(nb_weeks+1):
        get_around_date(future(date, 7*i), t, 7-t, res)
    return res

def notify(time):
    if time not in ["midi", "soir"]:
        return "Enter a valid time"
    d = get_day(today())
    p = []
    try:
        p = d[time]["ri"]["plat"]
    except:
        data = "Pas de repas au RI"
    
    if p:
        data = "Menu :"
        for el in p :
            data += "\n- " + el["Dish"]
    
    requests.post("https://ntfy.sh/menu-insa",
    data=data.encode(encoding='utf-8'),
    headers={
        "Title": "Menu disponible",
        "Tags": "plate_with_cutlery",
    })
    return data

# Obtenir le menu de la semaine actuelle et celui de la semaine suivante
d = get_weeks(today(), 1)

# Envoyer une notification
if 8 < datetime.datetime.now().hour < 14:
    notify("midi")
elif 13 < datetime.datetime.now().hour < 21 and datetime.date.today().weekday() != 5:
    notify("soir")
# Pas de notification si exécution pendant la nuit et pas de notification le samedi soir

# Ecrire le menu dans un fichier JSON (attention, écrase le contenu précédent)
write_to_file(d)

""" Exemples :
# Obtenir la date du jour au format ISO
today()

# Obtenir la date d'avant-hier au format ISO
past(today(), 2)

# Obtenir la date de demain au format ISO
future(today(), 1)

# Obtenir le menu de cette semaine (2 possibilités équivalentes)
w1 = get_current_week()
w2 = get_weeks(today(), 0)
print(w1 == w2) # -> True

# Obtenir le menu des 4 prochaines semaines
d = get_weeks(today(), 4)

# Ecrire un menu dans un fichier JSON (attention, écrase le contenu précédent)
write_to_file(d)

# Notifier les utilisateurs sur le sujet menu-insa un midi
notify("midi")
"""
