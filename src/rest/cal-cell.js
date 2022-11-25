export default {
    name: 'cal-cell',
    props: ["data", "wDay", "disabled", "allowedWeekdays"],
    template: `<td class="select-cell">

            <div>
                <div>
                    <h3>{{ data.mDay }}</h3>
                
                    <div class="select" v-if="!disabled">
                        <div :class="[{'x-select': dej_enabled()}, {'dej-select': dej_enabled()}, {selected: data.dej}]"
                            @click="invert_dej">
                        
                        </div>
                       
                        <div :class="[{'x-select': rep1_enabled()}, {'rep1-select': rep1_enabled()}, {selected: data.rep1}]"
                            @click="invert_rep1">
                        
                        </div>
                        <div :class="[{'x-select': rep2_enabled()}, {'rep2-select': rep2_enabled()}, {selected: data.rep2}]"
                            @click="invert_rep2">
                        
                        </div>
                    </div>
                </div>
            </div>
        
        
        </td>`,
    data(){
        return {
            isSunday: this.wDay === 6,
            isWeekend: this.wDay === 6 || this.wDay === 7
        }
    },
    methods: {
        dej_enabled: function(){
            let date = new Date();
            return (this.data.mDay != date.getDate() || date.getHours() < 8)
                && !this.isWeekend;
        },
        rep1_enabled: function(){
            let date = new Date();
            return (this.data.mDay != date.getDate() || date.getHours() < 14)
                && (this.allowedWeekdays || !this.isWeekend);
        },
        rep2_enabled: function(){
            let date = new Date();
            return (this.data.mDay != date.getDate() || date.getHours() < 20)
                && !this.isSunday && (this.allowedWeekdays || !this.isWeekend);
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