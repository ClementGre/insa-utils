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


def set_menu(menu):
    global menu_dict
    menu_dict = menu


def get_menu():
    return menu_dict
