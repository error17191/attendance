<template>
    
</template>

<script>
    import BarChart from '../BarChart';

    export default {
        name: "Year",
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