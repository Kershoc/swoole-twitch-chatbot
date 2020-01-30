export default {
    name: 'ChatComponent',
    template: `
        <div id="chat-component">
        <transition-group name="fade">
            <div class="chat-item" v-for="(chat, index) in chats" v-bind:key="chat.tags.id" v-bind:style="{'background-color': chat.tags.color, 'color': fontColor(chat.tags.color)}" >{{chat.options}}</div>
        </transition-group>
        </div>`,
    computed: {
        chats() {
            return this.$store.state.chat
        },
    },
    methods: {
        fontColor(backgroundHexColor) {
            // Thanks! https://24ways.org/2010/calculating-color-contrast
            let hexcolor = backgroundHexColor.replace("#", "")
            let r = parseInt(hexcolor.substr(0,2),16)
            let g = parseInt(hexcolor.substr(2,2),16)
            let b = parseInt(hexcolor.substr(4,2),16)
            let yiq = ((r*299)+(g*587)+(b*114))/1000
            return (yiq >= 128) ? 'black' : 'white'
        }
    }
}
