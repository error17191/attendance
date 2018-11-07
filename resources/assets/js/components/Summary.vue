<template>
    <div class="card-body">
        <div class="form-check form-check-inline">
            <input v-model="summaryType" class="form-check-input" type="radio" id="summary-type-day" value="day"
                   name="summary_type">
            <label class="form-check-label" for="summary-type-day">Day</label>
        </div>
        <div class="form-check form-check-inline">
            <input v-model="summaryType" class="form-check-input" type="radio" id="summary-type-month" value="month"
                   name="summary_type">
            <label class="form-check-label" for="summary-type-month">Month</label>
        </div>
        <div class="form-check form-check-inline">
            <input v-model="summaryType" class="form-check-input" type="radio" id="summary-type-year" value="year"
                   name="Summary_type">
            <label class="form-check-label" for="summary-type-year">Year</label>
        </div>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';

    export default {
        name: "Summary",
        components: {
            UserSearch
        },
        data(){
            return {
                summaryType: 'day',
                dayForm: {
                    date: null,
                    users: null,
                    all: true
                },
                month: {
                    month: null,
                    year: null,
                    users: null,
                    all: true
                },
                yearForm: {
                    year: null,
                    users: null,
                    all: true
                }
            }
        },
        mounted(){
            console.log('mounted');
        }
    }
</script>

<style scoped>

</style>