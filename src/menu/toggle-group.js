import {remove_before, remove_after} from './utils.js'

export default {
    name: 'calendar',
    props: ["buttons", "label", "selected_index", "disabled_indices"],
    template: `<div class="toggle-group unselectable" role="radiogroup" :aria-labelledby="label">
            <template v-for="(name, i) in buttons" :key="name">
                <div v-if="!disabled_indices?.includes(i)"
                    @click="select_button(i)"
                    role="radio"
                    :aria-checked="selected_index === i"
                    tabindex="0"
                    :aria-labelledby="name">
                    <p class="title">{{get_title(name)}}</p>
                    <p class="subtitle">{{get_subtitle(name)}}</p>
                </div>
            </template>
        </div>`,
    data(){
        return {

        }
    },
    methods: {
        select_button: function(i) {
            this.$emit('update:selected_index', i)
        },
        get_title: function(name){
            return remove_after(name, ':')
        },
        get_subtitle: function(name){
            if(name.indexOf(':') === -1) return '';
            return remove_before(name, ':')
        }
    },
}
