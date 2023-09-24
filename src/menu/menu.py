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

def get_next_7_days():
    res = []
    for i in range(7):
        res.append(get_day(future(today(), i)))
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

# Obtenir le menu des 7 prochains jours
d = get_next_7_days()

# Envoyer une notification
if 8 < datetime.datetime.now().hour < 14:
    notify("midi")
elif 13 < datetime.datetime.now().hour < 21 and datetime.date.today().weekday() != 5:
    notify("soir")
# Pas de notification si exécution pendant la nuit et pas de notification le samedi soir

# Ecrire le menu dans un fichier JSON (attention, écrase le contenu précédent)
write_to_file(d)
