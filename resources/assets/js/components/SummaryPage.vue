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
        <div v-if="summaryType === 'day'" class="row" id="day-form">
            <div class="col-md-4">
                <flat-pickr
                        class="form-control bg-white"
                        v-model="dayForm.date"
                        :config="dateConfig"
                ></flat-pickr>
            </div>
            <button class="btn btn-primary form-control col-md-3"
                    :disabled="formReady !== 'day'"
                    @click="getSummary"
            >
                Get Summary
            </button>
        </div>
        <div v-if="summaryType === 'month'" class="row" id="month-form">
            <div class="col-md-4">
                <select v-model="monthForm.month" class="form-control">
                    <option v-for="m in months" :value="m.value" :disabled="m.disabled" :selected="m.selected">
                        {{m.text}}
                    </option>
                </select>
            </div>
            <div class="col-md-4">
                <select v-model="monthForm.year" class="form-control">
                    <option v-for="y in years" :value="y.value" :selected="y.selected" :disabled="y.disabled">
                        {{y.text}}
                    </option>
                </select>
            </div>
            <button class="btn btn-primary form-control col-md-3"
                    @click="getSummary"
                    :disabled="formReady !== 'month'"
            >
                Get Summary
            </button>
        </div>
        <div v-if="summaryType === 'year'" class="row" id="year-form">
            <div class="col-md-4">
                <select v-model="yearForm.year" class="form-control">
                    <option v-for="y in years" :value="y.value" :selected="y.selected" :disabled="y.disabled">
                        {{y.text}}
                    </option>
                </select>
            </div>
            <button class="btn btn-primary form-control col-md-3"
                    @click="getSummary"
                    :disabled="formReady !== 'year'"
            >
                Get Summary
            </button>
        </div>
        <hr>
        <day :summary="summary.data"
             v-if="summary.type === 'day' && summary.show === 'day'"
        ></day>
        <month :summary="summary.data"
             v-if="summary.type === 'month' && summary.show === 'month'"
        ></month>
        <year :summary="summary.data"
               v-if="summary.type === 'year' && summary.show === 'year'"
        ></year>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import Day from './summary/Day';
    import Month from './summary/Month';
    import Year from './summary/Year';

    export default {
        name: "SummaryPage",
        components: {
            UserSearch,
            flatPickr,
            Day,
            Month,
            Year
        },
        data(){
            return {
                summaryType: 'day',
                searchType: 'all',
                dayForm: {
                    date: null,
                    users: null,
                },
                monthForm: {
                    month: null,
                    year: null,
                    users: null,
                },
                yearForm: {
                    year: null,
                    users: null,
                },
                dateConfig: {
                    mode: 'single'
                },
                formReady: 'none',
                users: null,
                usersIds: null,
                summary: {
                    data: null,
                    show: 'none',
                    type: 'none'
                }
            }
        },
        computed: {
            months: function() {
                let months = [{
                    value: null,
                    text: 'Month',
                    selected: true,
                    disabled: true
                }];
                return months.concat(this.setMonths());
            },
            years: function() {
                return this.setYears();
            }
        },
        methods: {
            usersSelected(users){
                this.users = users;
                this.usersIds = this.getUsersIds();
            },
            getUsersIds(){
                let ids = [];
                for(let user in this.users){
                    ids.push(this.users[user].id);
                }
                return ids;
            },
            validate(){
                let validForm = (this.searchType === 'specific' && this.usersIds != null && this.usersIds.length > 0) ||
                                (this.searchType === 'all' && this.usersIds == null);
                switch (this.summaryType){
                    case 'day':
                        validForm = validForm && this.dayForm.date != null;
                        break;
                    case 'month':
                        validForm = validForm && this.monthForm.month != null && this.monthForm.year != null;
                        break;
                    case 'year':
                        validForm = validForm && this.yearForm.year != null;
                        break;
                    default:
                        validForm = false;
                }
                this.formReady = validForm ? this.summaryType : 'none';
                return validForm;
            },
            getSummary(){
                this.formReady = 'none';
                if(!this.validate()){
                    return;
                }
                let form = this.summaryType + 'Form';
                this[form].users = this.searchType === 'specific' ? this.usersIds : null;
                let url = `/${this.summaryType}/summary/admin`;
                makeRequest({
                    method: 'post',
                    url: url,
                    data: this[form]
                }).then((response) => {
                    this.summary.data = response.data.summary;
                    this.summary.show = this.summaryType;
                    this.summary.type = response.data.summaryType;
                    this.formReady = this.summaryType;
                });
            },
            clearSummary(){
                this.summary.data = null;
                this.summary.type = 'none';
                this.summary.show = 'none';
            }
        },
        watch: {
            searchType(){
                if(this.searchType === 'all') {
                    this.users = null;
                    this.usersIds = null;
                }
                this.validate();
                this.clearSummary();
            },
            usersIds(){
                if(this.validate() && this.summary.data != null){
                    this.getSummary();
                }else{
                    this.clearSummary();
                }
            },
            summaryType(){
                this.validate();
                this.summary.show = this.summaryType;
            },
            "dayForm.date": function () {
                this.validate();
            },
            "monthForm.month": function () {
                this.validate();
            },
            "monthForm.year": function () {
                this.validate();
            },
            "yearForm.year": function () {
                this.validate();
            }
        }
    }
</script>

<style scoped>

</style>