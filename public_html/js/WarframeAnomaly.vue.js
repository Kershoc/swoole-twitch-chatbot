export default {
    name: 'WarframeAnomaly',
    template: `
        <div id="warframe-anomaly" v-if="status !== false">
            <video src="vid/redalert.webm" autoplay="autoplay" loop="loop" />
            <p>Anomaly up in {{status}}</p>
        </div>`,
    computed: {
        status() {
            return this.$store.state.WarframeAnomaly.status
        },
    }
}
