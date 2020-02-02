import Vue from 'https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.esm.browser.js'
import Vuex from 'https://cdn.jsdelivr.net/npm/vuex@3.1.2/dist/vuex.esm.browser.js'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        chat: [],
        popup: [],
        WarframeAnomaly: {
            status: false
        },
    },
    mutations: {
        chat (state, payload) {
            state.chat.push(payload)
            setTimeout(() => {state.chat.shift()}, 10000)
        },
        popup (state, payload) {
            state.popup.push(payload)
        },
        WarframeAnomaly (state, payload) {
            state.WarframeAnomaly.status = payload.status
        },
        connection (state, payload) {
            // Reserved for future use
        }
    }
})


new Vue({
    el: '#overlay',
    store,
    components: {
        'WarframeAnomaly': () => import('./WarframeAnomaly.vue.js'),
        'ChatComponent': () => import('./ChatComponent.vue.js'),
    },
})

const ws = new WebSocket('ws://127.0.0.1:1337/')

ws.onopen = function(event) {
}

ws.onerror = function(error) {
    console.log('ERR: ', error)
}
ws.onclose = function() {
    console.log('INFO: Socket Closed')
}
ws.onmessage = function(event) {
    console.log('RECV: ', event.data)
    let msg = JSON.parse(event.data)
    if (msg.event === 'popup') {
        store.commit(msg.payload.command, msg.payload)
    }
    if (msg.event === 'chat') {
        store.commit(msg.event, msg.payload)
    }
}
