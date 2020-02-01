export default {
    name: 'ChatComponent',
    template: `
        <div id="chat-component">
        <transition-group name="fade">
            <div class="chat-item" v-for="(chat, index) in chats" v-bind:key="chat.tags.id" v-bind:style="{'background-color': chat.tags.color, 'color': fontColor(chat.tags.color)}" >
                <div class="name">{{chat.tags['display-name']}}</div>
                <p v-html="injectEmotes(chat)"></p>
            </div>
        </transition-group>
        </div>`,
    computed: {
        chats() {
            return this.$store.state.chat
        }
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
        },
        injectEmotes(chatObj) {
            let emoteUrl = 'https://static-cdn.jtvnw.net/emoticons/v1/'
            if (chatObj.tags.emotes) {
                let replacements = [];
                let emotes = chatObj.tags.emotes.split('/') // multiple unique emotes
                emotes.forEach((uniqueEmote) => {
                    let emote = uniqueEmote.split(':')
                    emote[1].split(',').forEach((parts) => {
                        let replacement = {'emote_id':emote[0], 'start':0, 'end':0 }
                        let placement = parts.split('-')
                        replacement.start = parseInt(placement[0])
                        replacement.end = parseInt(placement[1])
                        replacements.push(replacement)
                    })
                })
                // reverse sort I want to start at the last emote in the chat message
                // and replace toward the front so the start-end points given by twitch remain valid during injection
                replacements.sort((a, b) => b.start - a.start )
                let message = chatObj.options
                replacements.forEach((e) => {
                    let frontPart = message.substring(0, e.start)
                    let endPart = message.substring(e.end+1)
                    let img = '<img src="' + emoteUrl + e.emote_id + '/1.0" />'
                    message = frontPart + img + endPart
                })
                return message
            }

            return chatObj.options
        }
    }
}
