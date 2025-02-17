import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js' // vue.esm-browser.prod.js
import toggle_group from './toggle-group.js'
import {initializePushNotifications} from './push-notifications.js'

const today_date = new Date();

const REST_INDICES = {
    OLIVIER_LUNCH: 0,
    RI_LUNCH: 1,
    RI_DINNER: 2,
}

// Scaling for BdE screens function
function adjustFontSizeToFit() {
    const html = document.documentElement;
    if (!html.classList.contains('bde')) return;

    let fontSize = 10; // Start font size in pixels
    html.style.fontSize = `${fontSize}px`;

    function decreaseFontSize() {
        if(document.documentElement.scrollHeight > window.innerHeight || document.documentElement.scrollWidth > window.innerWidth) {
            // Decrement font size and apply it
            fontSize -= .5;
            html.style.fontSize = `${fontSize}px`;

            // Check again on the next frame
            requestAnimationFrame(decreaseFontSize);
        }else{
            html.style.overflow = 'hidden';
        }
    }

    function increaseFontSize() {
        if (
            document.documentElement.scrollHeight <= window.innerHeight &&
            document.documentElement.scrollWidth <= window.innerWidth
        ) {
            if(fontSize > 50) return; // Max font size

            // Increment font size and apply it
            fontSize += 2;
            html.style.fontSize = `${fontSize}px`;

            // Check again on the next frame
            requestAnimationFrame(increaseFontSize);
        } else {
            requestAnimationFrame(decreaseFontSize);
        }
    }
    // Start the resizing loop
    increaseFontSize();
}
// Initial adjustment
adjustFontSizeToFit();
// Adjust font size on window resize
window.addEventListener('resize', adjustFontSizeToFit);



function get_default_selected_rest(disable_olivier = false){
    const storage_value = localStorage.getItem('selected_rest_index');

    const urlParams = new URLSearchParams(window.location.search);

    if((storage_value == REST_INDICES.OLIVIER_LUNCH || urlParams.get('rest') === 'olivier') && !disable_olivier){
        if(today_date.getDay() !== 0 && today_date.getDay() !== 6){ // Not weekend
            return REST_INDICES.OLIVIER_LUNCH;
        }
    }

    if(today_date.getHours() < 14 || today_date.getDay() === 6){ // Before 14h or sunday
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
                week_menu_available: !(today_date.getDay() === 1 && (today_date.getHours() <= 10 || (today_date.getHours() === 11 && today_date.getMinutes() < 10))),
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
        },
        selected_menu: function(){
            return this.data?.days?.[this.ui.selected_day_index]?.[this.time_id]?.[this.rest_id];
        },
        is_menu_empty: function(){
            const menu = this.selected_menu;
            return !menu || !(menu.plat && menu.plat.length != 0)
                && !((menu.garniture && menu.garniture.length != 0) || (menu.sauce && menu.sauce.length != 0))
                && !(menu.entree && menu.entree.length != 0)
                && !((menu.dessert && menu.dessert.length != 0) || (menu.fromage && menu.fromage.length != 0));
        },
        is_menu_outdated: function(){
            console.log(this.data?.last_update)
            const last_update = new Date(this.data?.last_update);
            console.log("Last update date:", last_update)

            const last_update_day = last_update.getDay();
            const last_update_gap = (today_date.getTime() - last_update.getTime()) / (1000. * 60. * 60. * 24.);

            console.log("Last update gap:", last_update_gap, "Last update day:", last_update_day, "Today day:", today_date.getDay())

            return last_update_gap >= 7 // More than 7 days ago
                || (today_date.getDay() < last_update_day && today_date.getDay() !== 0) // Last update in a day of the week that is after today (except if today is sunday)
                || (last_update_day === 0 && today_date.getDay() !== 0) // Last update on sunday and not sunday
                || (last_update_day === today_date.getDay() && last_update_gap > 1); // Same day, but gap > 1 day
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
                lunch_time: '11:10',
                dinner_time: '17:10',
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
        fetch('data/menu.json', {cache: "no-store"})
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
