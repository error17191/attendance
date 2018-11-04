<template>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <user-search placeHolder="Select Employee" @selected="userSelected"></user-search>
            </div>
            <div class="col-md-3">
                <select v-model="form.year" @change="validate" class="form-control">
                    <option v-for="y in years" :value="y.value" :selected="y.selected" :disabled="y.disabled">
                        {{y.text}}
                    </option>
                </select>
            </div>
            <button class="btn btn-primary form-control col-md-3"
                    @click="getStatistics"
                    :disabled="!formReady"
            >
                Get Statistics
            </button>
        </div>
        <div v-if="showAlert" class="alert alert-info">
            Please Choose Employee And Year
        </div>
        <div v-else>
            <b-card no-body>
                <b-tabs card>
                    <b-tab no-body title="Time" active>
                        <div class="card">
                            <div class="card">
                                <div class="card-body">
                                    <table v-for="month in statistics.workTime"
                                           class="table table-hover table-responsive">
                                        <thead class="text-center">
                                            <tr>
                                                <th colspan="4">{{month.name}}</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th>hours</th>
                                                <th>minutes</th>
                                                <th>seconds</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Ideal Time</td>
                                                <td>{{partitionSeconds(month.ideal).hours}}</td>
                                                <td>{{partitionSeconds(month.ideal).minutes}}</td>
                                                <td>{{partitionSeconds(month.ideal).seconds}}</td>
                                            </tr>
                                            <tr>
                                                <td>Actual Worked Time</td>
                                                <td>{{partitionSeconds(month.actual).hours}}</td>
                                                <td>{{partitionSeconds(month.actual).minutes}}</td>
                                                <td>{{partitionSeconds(month.actual).seconds}}</td>
                                            </tr>
                                            <tr>
                                                <td>Diff Time</td>
                                                <td>{{partitionSeconds(month.diff).hours}}</td>
                                                <td>{{partitionSeconds(month.diff).minutes}}</td>
                                                <td>{{partitionSeconds(month.diff).seconds}}</td>
                                            </tr>
                                            <tr>
                                                <td>Diff Type</td>
                                                <td colspan="3" class="text-center">{{month.diffType}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Months Time
                            </div>
                            <div class="card-body">
                                <line-chart :data="workTimeLine"></line-chart>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Flags">
                        <div class="card">
                            <div class="card">
                                <div class="card-body">
                                    <table v-for="month in statistics.flags"
                                           class="table table-hover table-responsive">
                                        <thead class="text-center">
                                            <tr>
                                                <th colspan="4">{{month.name}}</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th>hours</th>
                                                <th>minutes</th>
                                                <th>seconds</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="value,name in month" v-if="name !== 'name'">
                                                <td>{{name | capitalize}}</td>
                                                <td>{{partitionSeconds(value).hours}}</td>
                                                <td>{{partitionSeconds(value).minutes}}</td>
                                                <td>{{partitionSeconds(value).seconds}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Months Flag Usage
                            </div>
                            <div class="card-body">
                                <line-chart :data="flagsLine"></line-chart>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Total Year Flags Usage
                            </div>
                            <div class="card-body">
                                <bar-chart :data="flagsBar"></bar-chart>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Absence">
                        <div class="card">
                            <div class="card-header text-center">
                                Absence
                            </div>
                            <div class="card-body">
                                <calendar :month="month('absenceIndex')"
                                          :year="form.year"
                                          :days="getDays(statistics.absence[absenceIndex + 1].workDaysAbsence)"
                                          :secondDays="getDays(statistics.absence[absenceIndex + 1].vacationsAttended)"
                                >
                                    <button slot="before" class="btn btn-primary float-left"
                                            :disabled="absenceIndex <= 0"
                                            @click.prevent="changeMonth('absenceIndex')"
                                    >
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                    <button slot="after" class="btn btn-primary float-right"
                                            :disabled="absenceIndex >= 11"
                                            @click.prevent="changeMonth('absenceIndex',true)"
                                    >
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                </calendar>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Regular Time">
                        <div class="card">
                            <div class="card-body">
                                <calendar :year="form.year"
                                          :month="month('regularTimeIndex')"
                                          :days="getDays(statistics.regularTime[regularTimeIndex + 1].offDays)"
                                >
                                    <button slot="before" class="btn btn-primary float-left"
                                            :disabled="regularTimeIndex <= 0"
                                            @click.prevent="changeMonth('regularTimeIndex')"
                                    >
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                    <button slot="after" class="btn btn-primary float-right"
                                            :disabled="regularTimeIndex >= 11"
                                            @click.prevent="changeMonth('regularTimeIndex',true)"
                                    >
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                </calendar>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Months Work At Regular Time Percentage
                            </div>
                            <div class="card-body">
                                <line-chart :data="regularTimeLine"></line-chart>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Days Of Work At Regular Time
                            </div>
                            <div class="card-body">
                                <pie-chart :data="regularTimePie"></pie-chart>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Work Efficiency">
                        <div class="card">
                            <div class="card">
                                <div class="card-body">
                                    <table v-for="month in statistics.workEfficiency"
                                           class="table table-hover table-responsive">
                                        <thead class="text-center">
                                            <tr>
                                                <th colspan="4">{{month.name}}</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th>hours</th>
                                                <th>minutes</th>
                                                <th>seconds</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Actual Worked Time</td>
                                                <td>{{partitionSeconds(month.actualWork).hours}}</td>
                                                <td>{{partitionSeconds(month.actualWork).minutes}}</td>
                                                <td>{{partitionSeconds(month.actualWork).seconds}}</td>
                                            </tr>
                                            <tr>
                                                <td>Spent Time At Work</td>
                                                <td>{{partitionSeconds(month.attendedTime).hours}}</td>
                                                <td>{{partitionSeconds(month.attendedTime).minutes}}</td>
                                                <td>{{partitionSeconds(month.attendedTime).seconds}}</td>
                                            </tr>
                                            <tr>
                                                <td>Work Efficiency Percentage</td>
                                                <td colspan="3" class="text-center">{{month.percentage}} %</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Months Work Efficiency
                            </div>
                            <div class="card-body">
                                <line-chart :data="workEfficiencyLine"></line-chart>
                            </div>
                        </div>
                        <div class="card col-md-10">
                            <div class="card-header text-center">
                                Total Year Work Efficiency
                            </div>
                            <div class="card-body">
                                <pie-chart :data="workEfficiencyPie"></pie-chart>
                            </div>
                        </div>
                    </b-tab>
                </b-tabs>
            </b-card>
        </div>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';
    import Calendar from './Calendar';
    import LineChart from './charts/LineChart';
    import PieChart from './PieChart';
    import BarChart from './BarChart';

    export default {
        name: "Year",
        components: {
            UserSearch,
            LineChart,
            PieChart,
            BarChart,
            Calendar
        },
        data(){
            return {
                form: {
                    userId: null,
                    year: null
                },
                years: [],
                formReady: false,
                user: null,
                statistics: null,
                showAlert: true,
                regularTimeIndex: 0,
                absenceIndex: 0
            }
        },
        computed: {
            workEfficiencyLine: function () {
                let labels = [];
                let data = [];
                for(let month in this.statistics.workEfficiency){
                    if(month > 12){
                        continue;
                    }
                    labels.push(this.statistics.workEfficiency[month].name);
                    data.push(this.statistics.workEfficiency[month].percentage);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Work Efficiency Percentage',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            },
            workEfficiencyPie: function () {
                return {
                    labels: ['Real Work','Wasted Time'],
                    datasets: [
                        {
                            backgroundColor: [
                                '#00D8FF',
                                '#DD1B16'
                            ],
                            data: [
                                this.statistics.workEfficiency[13].actualWork,
                                this.statistics.workEfficiency[13].attendedTime - this.statistics.workEfficiency[13].actualWork
                            ]
                        }
                    ]
                };
            },
            workTimeLine: function () {
                let labels = [];
                let data = [];
                for(let month in this.statistics.workTime){
                    if(month > 12){
                        continue;
                    }
                    labels.push(this.statistics.workTime[month].name);
                    data.push(this.partitionSeconds(this.statistics.workTime[month].actual).hours);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Work Time',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            },
            flagsLine: function () {
                let labels = [];
                let data = [];
                for(let month in this.statistics.flags){
                    if(month > 12){
                        continue;
                    }
                    labels.push(this.statistics.flags[month].name);
                    data.push(this.partitionSeconds(this.statistics.flags[month].total).hours);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Flags Usage',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            },
            flagsBar: function () {
                let labels = [];
                let data = [];
                for(let flag in this.statistics.flags[13]){
                    if(flag === 'total' || flag === 'name'){
                        continue;
                    }
                    labels.push(capitalize(flag));
                    data.push(this.partitionSeconds(this.statistics.flags[13][flag]).hours);
                }
                console.log(data);
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Flags',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            },
            regularTimeLine: function () {
                let labels = [];
                let data = [];
                for(let month in this.statistics.regularTime){
                    if(month > 12){
                        continue;
                    }
                    labels.push(this.statistics.regularTime[month].name);
                    data.push(this.statistics.regularTime[month].percentage);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Month Work At Regular Time Percentage',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            },
            regularTimePie: function () {
                return {
                    labels: ['Days At Regular Time','Days Off Regular Time'],
                    datasets: [
                        {
                            backgroundColor: [
                                '#00D8FF',
                                '#DD1B16'
                            ],
                            data: [
                                this.statistics.regularTime[13].all - this.statistics.regularTime[13].offTimes,
                                this.statistics.regularTime[13].offTimes
                            ]
                        }
                    ]
                };

            }
        },
        filters: {
            capitalize: function (value) {
                return capitalize(value);
            }
        },
        mounted(){
            this.years = this.setYears();
        },
        methods: {
            userSelected(user){
                if(!user){
                    return;
                }
                this.form.userId = user.id;
                this.user = user;
                this.validate();
            },
            validate(){
                this.formReady = this.form.userId != null && this.form.year != null;
            },
            getStatistics(){
                let url = `/year/report/admin?userId=${this.form.userId}&year=${this.form.year}`;
                makeRequest({
                    method: 'get',
                    url: url
                }).then((response) => {
                    this.statistics = response.data.statistics;
                    this.showAlert = false;
                });
            },
            getDays(datesArray){
                let days = [];
                for(let i in datesArray){
                    days.push(datesArray[i].split('-')[2]);
                }
                return days;
            },
            month(type) {
                return {name: this.setMonths()[this[type]].text,number: this.setMonths()[this[type]].value -1};
            },
            changeMonth(type,next){
                if(next){
                    if(this[type] >= 12){
                        return;
                    }
                    this[type]++;
                }else{
                    if(this[type] <= 0){
                        return;
                    }
                    this[type]--;
                }
            }
        }
    }
</script>

<style scoped>

</style>