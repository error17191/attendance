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
        },
        filters: {
            info: function (value) {
                if(value === 'on' || value === true || value === 1){
                    return 'yes';
                }
                return 'no';
            },
            capitalize: function (value) {
                return capitalize(value);
            },
            zeroPrefix: function (value) {
                return value < 10 ? `0${value}` : value.toString();
            }
        },
        mounted(){
            bus.$on('summary:updated',(summary)=>{
                // console.log(summary.data);
            });
        },
    }
</script>

<style scoped>

</style>