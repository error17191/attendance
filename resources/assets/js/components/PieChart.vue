<script>
    import {Pie} from 'vue-chartjs';

    export default {
        name: "PieChart",
        extends: Pie,
        props: ['data'],
        mounted() {
            this.renderChart(this.data);
        }
    }
</script>

