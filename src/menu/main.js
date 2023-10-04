import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js' // vue.esm-browser.prod.js
import toggle_group from './toggle-group.js'
import {remove_before, remove_after} from './utils.js'
const today_date = new Date();

const REST_IDS = {
    RI_LUNCH: 0,
    OLIVIER_LUNCH: 1,
    RI_DINNER: 2
}
function get_default_selected_rest(){
    const storage_value = localStorage.getItem('selected_rest_id');
    if(storage_value === REST_IDS.OLIVIER_LUNCH) return REST_IDS.OLIVIER_LUNCH;

    if(today_date.getHours() < 14){
        return REST_IDS.RI_LUNCH
    }else return REST_IDS.RI_DINNER
}

createApp({
    data(){
        return {
            data: null,
            ui: {
                selected_day_index: today_date.getDay() === 0 ? 6 : today_date.getDay() - 1,
                selected_rest_index: get_default_selected_rest(), // 'ri:lunch' or 'ri:dinner' or 'olivier:lunch' = 'olivier'
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
            if(this.ui.selected_rest_index === REST_IDS.OLIVIER_LUNCH) return 'olivier';
            return 'ri'
        },
        time_id: function(){
            if(this.ui.selected_rest_index === REST_IDS.RI_DINNER) return 'dinner';
            return 'lunch'
        }
    },
    methods: {

    },
    watch: {
        'ui.selected_day_index': function(new_selected_day_index){

        },
        'ui.selected_rest': function(new_selected_rest){
            localStorage.setItem('selected_rest', new_selected_rest);
        },
        data: function(data){
        }
    },
    created(){
        console.log("Fetching menu...")
        fetch('data/menu.json')
            .then(response => response.json())
            .then(data => {
                console.log("Menu fetched.", data)
                console.log(this.data)
                this.data = data
            });

    }
}).mount('#app')
