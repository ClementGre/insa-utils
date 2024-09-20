import cal_cell from "./cal-cell.js";
import cal_empty_cell from "./cal-empty-cell.js";

export default {
    name: 'cal-col',
    props: ["wIndex", "week", "wCount", "current", "allowWeekends"],
    template: `
        <tr class="cal-day">
       
            <cal-empty-cell v-for="index in getDayShift()" />
            <cal-cell v-for="(data, index) in week" :wDay="index + getDayShift()"
                :allow-weekends="allowWeekends" v-model:data="data"/>
                
            <cal-empty-cell v-for="index in getTrailingDays()" />
        </tr>`,
    data() {
        return {
        }
    },
    methods: {
        getDayShift: function(){
          if(this.wIndex === 1){
              return 7 - this.week.length;
          }
          return 0;
        },
        getTrailingDays: function(){
            if(this.wIndex === this.wCount){
                return 7 - this.week.length;
            }
        }
    },
    components: {
        "cal-cell": cal_cell,
        "cal-empty-cell": cal_empty_cell
    }
}
