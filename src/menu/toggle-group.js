export default {
    name: 'calendar',
    props: ["buttons"],
    template: `<div class="toggle-group">
            <button v-for="name in buttons" @click="select_button(name)">
                {{name}}
            </button>
        </div>`,
    data(){
        return {
            date: new Date(),
            months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
        }
    },
    methods: {
        select_button: function(name) {
            console.log("Clicked", name)
        }
    },
}
