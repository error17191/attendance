<template>
    <div class="card-body">
        <div class="row">
            <div class="container">
                <div class="form-check form-check-inline">
                    <input v-model="searchType" class="form-check-input" type="radio" id="search-type-all" value="all"
                           name="search_type">
                    <label class="form-check-label" for="search-type-all">All Employees</label>
                </div>
                <div class="form-check form-check-inline">
                    <input v-model="searchType" class="form-check-input" type="radio" id="search-type-specific" value="specific"
                           name="search_type">
                    <label class="form-check-label" for="search-type-specific">Specific Employees</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="container">
                <user-search v-if="searchType === 'specific'"
                             :multi="true"
                             place-holder="Select Employees"
                             @selected="usersSelected"
                ></user-search>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="container">
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
        </div>

    </div>
</template>

<script>
    import UserSearch from './UserSearch';

    export default {
        name: "Summary2",
        components: {
            UserSearch
        },
        computed: {
            months: ()=>{
                let months = [{
                    value: null,
                    text: 'Month',
                    selected: true,
                    disabled: true
                }];
                return months.concat(this.setMonths());
            },
            years: ()=> {
                return this.setYears();
            }
        },
        data(){
            return {
                summaryType: 'day',
                searchType: 'all',
                dayForm: {
                    date: null,
                    users: null,
                    all: true,
                    ready: false
                },
                month: {
                    month: null,
                    year: null,
                    users: null,
                    all: true,
                    ready: false
                },
                yearForm: {
                    year: null,
                    users: null,
                    all: true,
                    ready: false
                },
                users: null
            }
        },
        methods: {
            usersSelected(users){
                this.users = users;
            },
            getUsersIds(){
                let ids = [];
                for(let user in this.users){
                    ids.push(this.users[user].id);
                }
                return ids;
            }
        },
        watch: {
            searchType(){
                if(this.searchType === 'all'){
                    this.users = null;
                }
            }
        }
    }
</script>

<style scoped>

</style>