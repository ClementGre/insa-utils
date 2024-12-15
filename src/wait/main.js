import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js' // vue.esm-browser.prod.js
import toggle_group from './toggle-group.js'

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

    function increaseFontSize() {
        if (
            document.documentElement.scrollHeight <= window.innerHeight &&
            document.documentElement.scrollWidth <= window.innerWidth
        ) {
            if(fontSize > 50) return; // Max font size

            // Increment font size and apply it
            fontSize += 0.5;
            html.style.fontSize = `${fontSize}px`;

            // Check again on the next frame
            requestAnimationFrame(increaseFontSize);
        } else {
            // Backtrack by a small amount to fit within the viewport
            fontSize -= 0.5;
            html.style.fontSize = `${fontSize}px`;
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
                data_available: this.data !== null,
            },
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
        selected_restaurant: function(){
            return this.data?.[this.rest_id]?.[this.time_id];
        },
        is_waitingTime_empty: function(){
            const restaurant = this.selected_restaurant;
            return !restaurant 
        },
        is_work_in_progress: function(){
            const restaurant = this.selected_restaurant;
            if (restaurant?.["workInProgress"]){
                return true
            }
            return false 
        },
        prediction_is_not_null: function(){
            if (!this.selected_restaurant?.["predictionTime"]){
                return false;
            }else if(!this.selected_restaurant?.["predictionTime"].every(e => e !==null)){
                return false;
            }
            return true;
        }
    },
    methods: {
        renderHistogram() {
            if (this.prediction_is_not_null){
                const canvas = document.getElementById('histogramCanvas');
                if (!canvas) {
                    console.error("Canvas element not found!");
                    return;
                }
                if (this.chart) {
                    // Destroy the existing chart instance
                    this.chart.destroy();
                }
                const ctx = canvas.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.time_id === 'lunch' ? ["11:30", "11:40", "11:50", "12:00", "12:10", "12:20", "12:30", "12:40", "12:50", "13:00", "13:10", "13:20", "13:30"] : ["18:00", "18:10", "18:20", "18:30", "18:40", "18:50","19:00", "19:10", "19:20", "19:30"],
                        datasets: [
                            {
                                label: 'Temps d\'attente prédictif',
                                data: this.selected_restaurant?.["predictionTime"],
                                backgroundColor: '#D32F2F',
                                borderRadius: 5,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#e0e0e0',
                                },
                            },
                        },
                    },
                });
            }
        },
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
        get_dish_html: function(dish, prefix = false){
            let html = '';

            if(prefix){
                html += `<span class="prefix">${prefix} :</span>`
            }
            return html;
        },
    },
    watch: {
        'ui.selected_rest_index': function(new_selected_rest_index){
            localStorage.setItem('selected_rest_index', new_selected_rest_index);
            this.$nextTick(() => {
                this.renderHistogram();
            });
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
            this.$nextTick(() => {
                this.renderHistogram();
            });
        },
    },
    created(){
        console.log("Fetching waiting data...")
        fetch('https://script.google.com/macros/s/AKfycbzSSfJYbMKFx35IHz_aI7nBTyX5mbdvoKxHIydY9eg1M1p21xBbUfRgIzKfMvBkAf0/exec', {cache: "no-store"})
            .then(response => response.json())
            .then(data => {
                console.log("Waiting data fetched.", data)
                this.data = data
                this.$nextTick(() => {
                    this.renderHistogram();
                })
            });

    },
}).mount('#app')