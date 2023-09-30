import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js' // vue.esm-browser.prod.js
import toggle_group from './toggle-group.js'
const today_date = new Date();

function get_default_selected_rest(){
    const storage_value = localStorage.getItem('selected_rest');
    if(storage_value) return storage_value;

    if(today_date.getHours() < 14){
        return 'ri:lunch'
    }else return 'ri:dinner'
}

function remove_after(s, el, no_el = true){
    const n = s.indexOf(el);
    s = s.substring(0, (no_el && n !== -1) ? n : s.length);
    return s;
}
function remove_before(s, el, no_el = true){
    const n = s.indexOf(el);
    s = s.substring((no_el && n !== -1) ? n+1 : 0);
    return s;
}

createApp({
    data(){
        return {
            data: null,
            ui: {
                selected_day_index: 0, // 0 = today, 1 = tomorow
                selected_rest: get_default_selected_rest(), // 'ri:lunch' or 'ri:dinner' or 'olivier:lunch' = 'olivier'
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
            return remove_after(this.ui.selected_rest, ':').toLowerCase()
        },
        time_id: function(){
            if(this.rest_id === 'olivier') return 'lunch';
            return remove_before(this.ui.selected_rest, ':').toLowerCase()
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
