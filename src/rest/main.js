import {createApp} from 'https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js'
import calendar from "./calendar.js";
import {debounce, isValidRep} from "./helpers.js";

createApp({
    data(){
        return {
            solde: 0,
            regime: 0, // 0: 7/7 | 1: 5/7 | 2: 5/7 lib | 3: 15 | 4: unité
            weeks: [],
            pricing: { // index = regime
                dej: [2.00, 2.29, 2.41, 2.41, 2.60],
                rep: [4.00, 4.53, 4.78, 4.91, 5.15]
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
            let newSolde = this.solde;
            let date = new Date();

            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(day.dej && isValidRep(date, day.wDay, day.mDay, this.regime != 1, 0)){
                        newSolde -= this.pricing.dej[this.regime];
                    }
                    if(day.rep1 && isValidRep(date, day.wDay, day.mDay, this.regime != 1, 1)){
                        newSolde -= this.pricing.rep[this.regime];
                    }
                    if(day.rep2 && isValidRep(date, day.wDay, day.mDay, this.regime != 1, 2)){
                        newSolde -= this.pricing.rep[this.regime];
                    }
                });
            });

            this.newSolde = newSolde;

        },
        twoDecimals: function(number){
            return (Math.round(number * 100) / 100).toFixed(2);
        }
    },
    watch: {
        solde: debounce(function(newSolde){
            localStorage.setItem('solde', newSolde);
        }, 1000, function(){
            this.updateCalculations();
        }),
        regime: debounce(function(newRegime){
            localStorage.setItem('regime', newRegime);
        }, 1000, function(){
            this.updateCalculations();
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
                this.updateCalculations();
            }),
            deep: true
        }
    },
    created(){
        let date = new Date();
        let lastMonth = localStorage.getItem('lastMonth');
        let lastVisitDate = new Date(localStorage.getItem('lastVisit'));
        console.log("Last visit date:", lastVisitDate.toString());
        let solde = localStorage.getItem('solde');
        let regime = localStorage.getItem('regime');
        let data = JSON.parse(localStorage.getItem('data'));
        if(!data || lastMonth != date.getMonth()){
            data = new Array(31).fill({dej: false, rep1: true, rep2: true});
            solde = 0;
        }

        if(solde) this.solde = solde;
        if(regime) this.regime = regime;

        let toRemoveDej = 0, toRemoveRep1 = 0, toRemoveRep2 = 0;

        let weeks = [];
        let currentWeek = [];
        let wDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
        if(wDay === 0) wDay = 7;
        wDay--;
        let mDay = 1;
        while(mDay <= this.maxMDay(date)){

            // Count passed dejs/reps to decrease solde
            if(data[mDay]?.dej && !isValidRep(date, wDay, mDay, this.regime != 1, 0)
                && isValidRep(lastVisitDate, wDay, mDay, this.regime != 1, 0)){
                toRemoveDej++;
            }
            if(data[mDay]?.rep1 && !isValidRep(date, wDay, mDay, this.regime != 1, 1)
                && isValidRep(lastVisitDate, wDay, mDay, this.regime != 1, 1)){
                toRemoveRep1++;
            }
            if(data[mDay]?.rep2 && !isValidRep(date, wDay, mDay, this.regime != 1, 2)
                && isValidRep(lastVisitDate, wDay, mDay, this.regime != 1, 2)){
                toRemoveRep2++;
            }

            currentWeek.push({mDay: mDay, wDay: wDay, dej: data[mDay]?.dej, rep1: data[mDay]?.rep1, rep2: data[mDay]?.rep2});
            mDay++;
            wDay++;
            if(wDay > 6){
                wDay = 0;
                weeks.push(currentWeek);
                currentWeek = [];
            }
        }
        if(currentWeek.length > 0) weeks.push(currentWeek);

        console.log("Should decrease (dej/rep1/rep2):", toRemoveDej, toRemoveRep1, toRemoveRep2);

        let doDecrease = false;
        let toDecrease = toRemoveDej * this.pricing.dej[this.regime] + (toRemoveRep1 + toRemoveRep2) * this.pricing.rep[this.regime];
        if(toRemoveDej !== 0 && toRemoveRep1+toRemoveRep2 === 0){
            doDecrease = confirm("Vous avez mangé " + toRemoveDej + " déjeuners depuis votre dernière visite.\n" +
                "Souhaitez-vous déduire votre solde de " + this.twoDecimals(toDecrease) + " points ?");
        }else if(toRemoveDej === 0 && toRemoveRep1+toRemoveRep2 !== 0){
            doDecrease = confirm("Vous avez mangé " + (toRemoveRep1+toRemoveRep2) + " repas depuis votre dernière visite.\n" +
                "Souhaitez-vous déduire votre solde de " + this.twoDecimals(toDecrease) + " points ?");
        }else if(toRemoveDej !== 0 && toRemoveRep1+toRemoveRep2 !== 0){
            doDecrease = confirm("Vous avez mangé " + toRemoveDej + " déjeuners et " + (toRemoveRep1+toRemoveRep2) + " repas depuis votre dernière visite.\n" +
                "Souhaitez-vous déduire votre solde de " + this.twoDecimals(toDecrease) + " points ?");
        }

        if(doDecrease){
            setTimeout(() => {
                this.solde = solde - toDecrease;
            }, 1000)
        }

        localStorage.setItem('lastMonth', date.getMonth().toString());
        localStorage.setItem('lastVisit', date.toString());
        this.weeks = weeks;
    }
}).mount('#app')
