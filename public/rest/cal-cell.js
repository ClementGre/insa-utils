import {isPassedRep, isValidRep} from "./helpers.js";

export default {
    name: 'cal-cell',
    props: ["data", "wDay", "disabled", "allowWeekends"],
    template: `<td class="select-cell">

            <div>
                <div>
                    <h3>{{ data.mDay }}</h3>
                
                    <div class="select">
                        <div :class="[{'x-select': dej_enabled()}, {'dej-select': dej_enabled()}, {'passed': dej_passed()}, {selected: data.dej}]"
                            @click="invert_dej">
                        
                        </div>
                       
                        <div :class="[{'x-select': rep1_enabled()}, {'rep1-select': rep1_enabled()}, {'passed': rep1_passed()}, {selected: data.rep1}]"
                            @click="invert_rep1">
                        
                        </div>
                        <div :class="[{'x-select': rep2_enabled()}, {'rep2-select': rep2_enabled()}, {'passed': rep2_passed()}, {selected: data.rep2}]"
                            @click="invert_rep2">
                        
                        </div>
                    </div>
                </div>
            </div>
        
        
        </td>`,
    data(){
        return {
        }
    },
    methods: {
        dej_passed: function(){
            return isPassedRep(new Date(), this.data.mDay, this.allowWeekends, 0);
        },
        rep1_passed: function(){
            return isPassedRep(new Date(), this.data.mDay, this.allowWeekends, 1);
        },
        rep2_passed: function(){
            return isPassedRep(new Date(), this.data.mDay, this.allowWeekends, 2);
        },
        dej_enabled: function(){
            return isValidRep(new Date(), this.wDay, this.allowWeekends, 0);
        },
        rep1_enabled: function(){
            return isValidRep(new Date(), this.wDay, this.allowWeekends, 1);
        },
        rep2_enabled: function(){
            return isValidRep(new Date(), this.wDay, this.allowWeekends, 2);
        },
        
        invert_dej(){
            let updated = this.data;
            updated.dej = !this.data.dej;
            this.$emit('update:data', updated)
        },
        invert_rep1(){
            let updated = this.data;
            updated.rep1 = !this.data.rep1;
            this.$emit('update:data', updated)
        },
        invert_rep2(){
            let updated = this.data;
            updated.rep2 = !this.data.rep2;
            this.$emit('update:data', updated)
        }
    }
}
