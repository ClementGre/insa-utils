import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js' // vue.esm-browser.prod.js
import toggle_group from './toggle-group.js'
import {initializePushNotifications} from './push-notifications.js'

const today_date = new Date();

const REST_INDICES = {
    OLIVIER_LUNCH: 0,
    RI_LUNCH: 1,
    RI_DINNER: 2,
}

function get_default_selected_rest(disable_olivier = false){
    const storage_value = localStorage.getItem('selected_rest_index');
    if(storage_value == REST_INDICES.OLIVIER_LUNCH && !disable_olivier) return REST_INDICES.OLIVIER_LUNCH;

    if(today_date.getHours() < 14){
        return REST_INDICES.RI_LUNCH
    }else return REST_INDICES.RI_DINNER
}

createApp({
    data(){
        return {
            data: null,
            ui: {
                selected_day_index: today_date.getDay() === 0 ? 6 : today_date.getDay() - 1,
                selected_rest_index: get_default_selected_rest(),
                week_menu_available: !(today_date.getDay() === 1 && (today_date.getHours() <= 10 || (today_date.getHours() === 11 && today_date.getMinutes() < 10)))
            },
            selected_date: {
                month: today_date.getMonth(),
                day: today_date.getDay()
            }
        }
    },
    components: {
        "toggle-group": toggle_group
    },
    computed: {
        rest_id: function(){
            if(this.ui.selected_rest_index === REST_INDICES.OLIVIER_LUNCH) return 'olivier';
            return 'ri'
        },
        time_id: function(){
            if(this.ui.selected_rest_index === REST_INDICES.RI_DINNER) return 'dinner';
            return 'lunch'
        },
        disabled_rest_indices: function(){
            let indices = [];
            if(this.ui.selected_day_index >= 5){
                indices.push(REST_INDICES.OLIVIER_LUNCH)
                if(this.ui.selected_day_index === 5){
                    indices.push(REST_INDICES.RI_DINNER)
                }
            }
            return indices;
        }
    },
    methods: {
        get_day_buttons_names: function(){
            let data = [];
            const current_week_index = today_date.getDay() === 0 ? 6 : today_date.getDay() - 1;

            for(let i = -current_week_index; i < 7 - current_week_index; i++){
                const date = new Date(today_date.getFullYear(), today_date.getMonth(), today_date.getDate() + i)

                let title = date.toLocaleDateString('fr', {weekday: 'short'});
                title = title.substring(0, 1).toUpperCase() + title.substring(1, title.length - 1)
                let subtitle = date.toLocaleDateString('fr', {month: 'short', day: 'numeric'}).toLowerCase();

                data.push(title + ':' + subtitle);
            }
            return data;
        },
        convert_labels_to_icons: function(title){
            title = out(title)
            for(const [key, value] of LABELS.entries()){
                title = title.replaceAll('&lt;' + key + '&gt;', ` <img src="labels/${key}.png" alt="${value}" title="${value}"> `);
            }
            return title;
        },
        get_dish_html: function(dish, prefix = false){
            let html = '';

            if(prefix){
                html += `<span class="prefix">${prefix} :</span>`
            }
            return html + this.convert_labels_to_icons(dish);
        },
        enable_notifications: function(){
            let form_data = {
                ri_lunch: true,
                ri_dinner: true,
                ri_weekend: true,
                olivier: true
            }
            initializePushNotifications(form_data);
        }
    },
    watch: {
        'ui.selected_day_index': function(new_selected_day_index){

        },
        'ui.selected_rest_index': function(new_selected_rest_index){
            localStorage.setItem('selected_rest_index', new_selected_rest_index);
        },
        disabled_rest_indices: function(new_disabled_indices){
            if(new_disabled_indices.includes(this.ui.selected_rest_index)){
                const def = get_default_selected_rest(true)
                if(new_disabled_indices.includes(def)){
                    console.log(REST_INDICES.RI_LUNCH)
                    this.ui.selected_rest_index = REST_INDICES.RI_LUNCH; // always available
                }else{
                    console.log(def)
                    this.ui.selected_rest_index = def;
                }
            }
        },
    },
    created(){
        console.log("Fetching menu...")
        fetch('data/menu.json')
            .then(response => response.json())
            .then(data => {
                console.log("Menu fetched.", data)
                this.data = data
            });

    }
}).mount('#app')


const LABELS = new Map([
    ['BBC', 'Bleu Blanc Coeur'],
    ['VF', 'Viande française'],
    ['FLF', 'Fruits et légumes de France'],
    ['FM', 'Fait maison'],
    ['VEG', 'Végétarien'],
    ['HVE', 'Haute valeur environnementale'],
    ['BIO', 'Bio']
]);
