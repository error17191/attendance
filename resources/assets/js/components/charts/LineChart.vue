
<script>
    import { Line } from 'vue-chartjs'

    export default {
        name: 'line-chart',
        extends: Line,
        props: ['data'],
        mounted () {
            this.renderChart(this.data);
        }
    }
</script>

<style scoped>

</style>