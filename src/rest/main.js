import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js'
import calendar from "./calendar.js";
import {debounce, isValidNotPassedRep, isValidPassedRep} from "./helpers.js";

createApp({
    data(){
        return {
            solde_mois: 0,
            solde: 0,
            solde_mois_input: '0',
            solde_input: '0',
            regime: 0, // 0: 7/7 | 1: 5/7 petit dej. | 2: 5/7 simple | 3: Demi-pension | 4: À l'unité
            weeks: [],
            pricing: { // index = regime
                dej: [2.06, 2.48, 2.68, 2.68, 2.68],
                rep: [4.12, 4.92, 5.06, 5.06, 5.30]
            },
            newSolde: 0
        }
    },
    components: {
        "calendar": calendar
    },
    methods: {
        maxMDay: function(date){
            return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
        },
        updateCalculations: function(){
            console.log("Updating calculations, solde_mois=", this.solde_mois)
            let solde = this.solde_mois;
            let date = new Date();

            // Passed reps
            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(day.dej && isValidPassedRep(date, day.wDay, day.mDay, true, 0)){
                        solde -= this.pricing.dej[this.regime];
                    }
                    if(day.rep1 && isValidPassedRep(date, day.wDay, day.mDay, true, 1)){
                        solde -= this.pricing.rep[this.regime];
                    }
                    if(day.rep2 && isValidPassedRep(date, day.wDay, day.mDay, true, 2)){
                        solde -= this.pricing.rep[this.regime];
                    }
                });
            });
            this.solde = solde;
            let newSolde = solde;
            // New solde
            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(day.dej && isValidNotPassedRep(date, day.wDay, day.mDay, true, 0)){
                        newSolde -= this.pricing.dej[this.regime];
                    }
                    if(day.rep1 && isValidNotPassedRep(date, day.wDay, day.mDay, true, 1)){
                        newSolde -= this.pricing.rep[this.regime];
                    }
                    if(day.rep2 && isValidNotPassedRep(date, day.wDay, day.mDay, true, 2)){
                        newSolde -= this.pricing.rep[this.regime];
                    }
                });
            });
            this.newSolde = newSolde;
            if(!this.is_solde_input_focused()) this.format_solde()
        },
        format_solde_mois(){
            this.solde_mois_input = this.solde_mois.toFixed(2)
        },
        format_solde(){
            this.solde_input = this.solde.toFixed(2)
        },
        format_soldes(){
            this.format_solde_mois()
        },
        is_solde_input_focused() {
            return document.activeElement === this.$refs.solde_input
        },
        update_solde_mois_input: function(event){
            this.solde_mois = parseFloat(event.target.value) || 0
        },
        update_solde_input: function(event){
            this.solde_mois += (parseFloat(event.target.value) || 0) - this.solde
            this.format_solde_mois();
        },
    },
    watch: {
        solde_mois: debounce(function(newSolde){
            console.log("Saving solde_mois=", newSolde)
            localStorage.setItem('solde_mois', newSolde);
        }, 1000, function(){
            this.updateCalculations()
        }),
        regime: debounce(function(newRegime){
            localStorage.setItem('regime', newRegime);
        }, 1000, function(){
            this.updateCalculations()
        }),
        weeks: {
            handler: debounce(function(newWeeks){
                let data = JSON.parse(localStorage.getItem('data'));
                if(!data) data = {};

                newWeeks.forEach(week => {
                    week.forEach(day => {
                        data[day.mDay] = {dej: day.dej, rep1: day.rep1, rep2: day.rep2};
                    });
                });
                localStorage.setItem('data', JSON.stringify(data));
            }, 2000, function(){
                this.updateCalculations()
            }),
            deep: true
        }
    },
    created(){
        let date = new Date();

        let solde_mois = parseInt(localStorage.getItem('solde_mois'), 10);
        let regime = localStorage.getItem('regime');
        let data = JSON.parse(localStorage.getItem('data'));
        if(!data){
            data = new Array(31).fill({dej: false, rep1: true, rep2: true});
            solde_mois = 0;
        }

        if(solde_mois) this.solde_mois = solde_mois;
        if(regime) this.regime = regime;

        // Building weeks object
        let weeks = [];
        let currentWeek = [];
        let wDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
        if(wDay === 0) wDay = 7;
        wDay--;
        let mDay = 1;
        while(mDay <= this.maxMDay(date)){
            currentWeek.push({
                mDay: mDay,
                wDay: wDay,
                dej: !!data[mDay]?.dej,
                rep1: !!data[mDay]?.rep1,
                rep2: !!data[mDay]?.rep2
            });
            mDay++;
            wDay++;
            if(wDay > 6){
                wDay = 0;
                weeks.push(currentWeek);
                currentWeek = [];
            }
        }
        if(currentWeek.length > 0) weeks.push(currentWeek);
        this.weeks = weeks;
        this.format_solde_mois()
    }
}).mount('#app')
