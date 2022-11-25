import cal_row from "./cal-row.js";
import {isValidRep} from "./helpers.js";

export default {
    name: 'calendar',
    props: ["weeks", "allowedWeekdays"],
    template: `<div>
            
            <div class="rapid-selection">
                <div>
                    <button type="button" @click="selectAll">Tout</button>
                    <button type="button" @click="selectDej">Matins</button>
                    <button type="button" @click="selectRep1">Midis</button>
                    <button type="button" @click="selectRep2">Soirs</button>
                    <button type="button" @click="selectWeekdays">Weekends</button>
                </div>
                <span>Sélection rapide :</span>
            </div>
            
            <table>
                <tr class="cal-title">
                   <th colspan='7'><div><p>{{ months[date.getMonth()] }} {{ date.getFullYear() }}</p></div></th>
               </tr>
               <tr class="cal-header">
                   <td class='weekdayheader'><div @click="selectWDay(0)"><p>Lun</p></div></td>
                   <td class='weekdayheader'><div @click="selectWDay(1)"><p>Mar</p></div></td>
                   <td class='weekdayheader'><div @click="selectWDay(2)"><p>Mer</p></div></td>
                   <td class='weekdayheader'><div @click="selectWDay(3)"><p>Jeu</p></div></td>
                   <td class='weekdayheader'><div @click="selectWDay(4)"><p>Ven</p></div></td>
                   <td class='red weekdayheader'><div @click="selectWDay(5)"><p>Sam</p></div></td>
                   <td class='red weekdayheader'><div @click="selectWDay(6)"><p>Dim</p></div></td>
               </tr>
               
                <cal-row v-for="(week, index) in weeks" :wIndex="index+1" :wCount="weeks.length" :week="week"
                    :current="date.getDate()" :allowed-weekdays="allowedWeekdays"/>
                
            </table>
            
        </div>`,
    data(){
        return {
            date: new Date(),
            months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
        }
    },
    methods: {
        selectWDay: function(day){
            let alreadySelected = this.weeks.every((week, wCount) => {
                let index = day - (wCount === 0 ? (7 - week.length) : 0);
                if(index < 0 || !week[index]) return true;
                
                return (week[index]?.dej || !isValidRep(this.date, day, week[index].mDay, this.allowedWeekdays, 0))
                    && (week[index]?.rep1 || !isValidRep(this.date, day, week[index].mDay, this.allowedWeekdays, 1))
                    && (week[index]?.rep2 || !isValidRep(this.date, day, week[index].mDay, this.allowedWeekdays, 2))
            });
            
            this.weeks.forEach((week, wCount) => {
                let index = day - (wCount === 0 ? (7 - week.length) : 0);
                if(index < 0) return;
                if(week[index]){
                    week[index].dej = !alreadySelected;
                    week[index].rep1 = !alreadySelected;
                    week[index].rep2 = !alreadySelected;
                }
            });
        },
        selectAll: function(){
            
            let alreadySelected = this.weeks.every(week =>
                week.every(day => (day.dej || !isValidRep(this.date, day.wDay, day.mDay, this.allowedWeekdays, 0))
                    && (day.rep1 || !isValidRep(this.date, day.wDay, day.mDay, this.allowedWeekdays, 1))
                    && (day.rep2 || !isValidRep(this.date, day.wDay, day.mDay, this.allowedWeekdays, 2)))
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    day.dej = !alreadySelected;
                    day.rep1 = !alreadySelected;
                    day.rep2 = !alreadySelected;
                });
            });
        },
        selectDej: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => {
                    return day.dej || !isValidRep(this.date, day.wDay, day.mDay, this.allowedWeekdays, 0);
                })
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    day.dej = !alreadySelected;
                });
            });
        },
        selectRep1: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => day.rep1 || !isValidRep(this.date, day.wDay, day.mDay, this.allowedWeekdays, 1))
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    day.rep1 = !alreadySelected;
                });
            });
        },
        selectRep2: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => day.rep2 || !isValidRep(this.date, day.wDay, day.mDay, this.allowedWeekdays, 2))
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    day.rep2 = !alreadySelected;
                });
            });
        },
        selectWeekdays: function(){
            let alreadySelected = this.weeks.every((week, wCount) => {
                let index = 5 - (wCount === 0 ? (7 - week.length) : 0);
                if(index < -1 || !week[index]) return true;
                
                let saturday = index === -1 ? true : (week[index]?.dej || !isValidRep(this.date, 5, week[index].mDay, this.allowedWeekdays, 0))
                    && (week[index]?.rep1 || !isValidRep(this.date, 5, week[index].mDay, this.allowedWeekdays, 1))
                    && (week[index]?.rep2 || !isValidRep(this.date, 5, week[index].mDay, this.allowedWeekdays, 2))
                
                index++;
                let sunday = (week[index]?.dej || !isValidRep(this.date, 6, week[index].mDay, this.allowedWeekdays, 0))
                    && (week[index]?.rep1 || !isValidRep(this.date, 6, week[index].mDay, this.allowedWeekdays, 1))
                    && (week[index]?.rep2 || !isValidRep(this.date, 6, week[index].mDay, this.allowedWeekdays, 2))
                
                return saturday && sunday;
            });
    
            this.weeks.forEach((week, wCount) => {
                let index = 5 - (wCount === 0 ? (7 - week.length) : 0);
                
                if(index >= 0 && week[index]){
                    week[index].dej = !alreadySelected;
                    week[index].rep1 = !alreadySelected;
                    week[index].rep2 = !alreadySelected;
                }
                index++;
                if(index >= 0 && week[index]){
                    week[index].dej = !alreadySelected;
                    week[index].rep1 = !alreadySelected;
                    week[index].rep2 = !alreadySelected;
                }
            });
        }
    },
    components: {
        "cal-row": cal_row
    },
}