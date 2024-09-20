import cal_row from "./cal-row.js";
import {isValidNotPassedRep} from "./helpers.js";

export default {
    name: 'calendar',
    props: ["weeks", "allowWeekends"],
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
                    :current="date.getDate()" :allow-weekends="allowWeekends"/>
                
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
                const mDay = week[index].mDay
                
                return (week[index]?.dej || !isValidNotPassedRep(this.date, day, mDay, this.allowWeekends, 0))
                    && (week[index]?.rep1 || !isValidNotPassedRep(this.date, day, mDay, this.allowWeekends, 1))
                    && (week[index]?.rep2 || !isValidNotPassedRep(this.date, day, mDay, this.allowWeekends, 2))
            });
            
            this.weeks.forEach((week, wCount) => {
                let index = day - (wCount === 0 ? (7 - week.length) : 0);
                if(index < 0) return;
                const mDay = week[index].mDay

                if(week[index]){
                    if(isValidNotPassedRep(this.date, day, mDay, this.allowWeekends, 0)) week[index].dej = !alreadySelected;
                    if(isValidNotPassedRep(this.date, day, mDay, this.allowWeekends, 1)) week[index].rep1 = !alreadySelected;
                    if(isValidNotPassedRep(this.date, day, mDay, this.allowWeekends, 2)) week[index].rep2 = !alreadySelected;
                }
            });
        },
        selectAll: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => (day.dej || !isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 0))
                    && (day.rep1 || !isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 1))
                    && (day.rep2 || !isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 2)))
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 0)) day.dej = !alreadySelected;
                    if(isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 1)) day.rep1 = !alreadySelected;
                    if(isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 2)) day.rep2 = !alreadySelected;
                });
            });
        },
        selectDej: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => {
                    return day.dej || !isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 0);
                })
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 0)) day.dej = !alreadySelected;
                });
            });
        },
        selectRep1: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => day.rep1 || !isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 1))
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 1)) day.rep1 = !alreadySelected;
                });
            });
        },
        selectRep2: function(){
            let alreadySelected = this.weeks.every(week =>
                week.every(day => day.rep2 || !isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 2))
            );
            this.weeks.forEach(week => {
                week.forEach(day => {
                    if(isValidNotPassedRep(this.date, day.wDay, day.mDay, this.allowWeekends, 2)) day.rep2 = !alreadySelected;
                });
            });
        },
        selectWeekdays: function(){
            let alreadySelected = this.weeks.every((week, wCount) => {
                let index = 5 - (wCount === 0 ? (7 - week.length) : 0);
                if(index < -1 || !week[index]) return true;

                let saturday = index === -1 ? true : (week[index]?.dej || !isValidNotPassedRep(this.date, 5, week[index].mDay, this.allowWeekends, 0))
                    && (week[index]?.rep1 || !isValidNotPassedRep(this.date, 5, week[index].mDay, this.allowWeekends, 1))
                    && (week[index]?.rep2 || !isValidNotPassedRep(this.date, 5, week[index].mDay, this.allowWeekends, 2))

                index++;
                let sunday = (week[index]?.dej || !isValidNotPassedRep(this.date, 6, week[index].mDay, this.allowWeekends, 0))
                    && (week[index]?.rep1 || !isValidNotPassedRep(this.date, 6, week[index].mDay, this.allowWeekends, 1))
                    && (week[index]?.rep2 || !isValidNotPassedRep(this.date, 6, week[index].mDay, this.allowWeekends, 2))

                return saturday && sunday;
            });

            this.weeks.forEach((week, wCount) => {
                let index = 5 - (wCount === 0 ? (7 - week.length) : 0);

                if(index >= 0 && week[index]){
                    if(isValidNotPassedRep(this.date, 5, week[index].mDay, this.allowWeekends, 0)) week[index].dej = !alreadySelected;
                    if(isValidNotPassedRep(this.date, 5, week[index].mDay, this.allowWeekends, 1)) week[index].rep1 = !alreadySelected;
                    if(isValidNotPassedRep(this.date, 5, week[index].mDay, this.allowWeekends, 2)) week[index].rep2 = !alreadySelected;
                }
                index++;
                if(index >= 0 && week[index]){
                    if(isValidNotPassedRep(this.date, 6, week[index].mDay, this.allowWeekends, 0)) week[index].dej = !alreadySelected;
                    if(isValidNotPassedRep(this.date, 6, week[index].mDay, this.allowWeekends, 1)) week[index].rep1 = !alreadySelected;
                    if(isValidNotPassedRep(this.date, 6, week[index].mDay, this.allowWeekends, 2)) week[index].rep2 = !alreadySelected;
                }
            });
        }
    },
    components: {
        "cal-row": cal_row
    },
}
