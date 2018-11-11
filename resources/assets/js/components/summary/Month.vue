<template>
    <div id="month-summary" class="container">

    </div>
</template>

<script>
    import BarChart from '../BarChart';

    export default {
        name: "Month",
        props: {
            summary: {
                type: Object,
                required: true
            }
        },
        components: {
            BarChart
        }
    }
</script>

<style scoped>

</style>