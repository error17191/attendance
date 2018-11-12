<template>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <user-search placeHolder="Select Employee" @selected="userSelected"></user-search>
            </div>
            <div class="col-md-2">
                <select v-model="form.month" @change="validate" class="form-control">
                    <option v-for="m in months" :value="m.value" :disabled="m.disabled" :selected="m.selected">
                        {{m.text}}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
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
        <div v-if="showAlert === 'choose'" class="alert alert-info">
            Please select an employee a month and a year
        </div>
        <div v-if="showAlert === 'no_work'"  class="alert alert-info">
            No work in {{form.month < 10 ? `0${form.month}` : form.month}}-{{form.year}} for {{user.username}}
        </div>
        <div v-if="!showAlert">
                <b-tabs card>
                    <b-tab no-body title="Time" active>
                        <div class="card">
                            <div class="card-header">
                                Month work time
                            </div>
                            <div class="card-body">
                                <table class="table table-hover table-responsive">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>hours</th>
                                            <th>minutes</th>
                                            <th>seconds</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Ideal Work Time</td>
                                            <td>
                                                {{partitionSeconds(statistics.idealTime).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.idealTime).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.idealTime).seconds}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Actual Worked Time</td>
                                            <td>
                                                {{partitionSeconds(statistics.actualTime).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.actualTime).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.actualTime).seconds}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Difference</td>
                                            <td>
                                                {{partitionSeconds(statistics.diff).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.diff).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.diff).seconds}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                The work time is {{statistics.diffType}} than ideal work time
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Projects">
                        <div class="card">
                            <div class="card-header">
                                Month Projects
                            </div>
                            <div class="card-body">
                                <table class="table table-hover table-responsive">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th>hours</th>
                                            <th>minutes</th>
                                            <th>seconds</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="projWithTime in statistics.projectsWithTime">
                                            <td>{{projWithTime.project.title | capitalize}}</td>
                                            <td>
                                                {{partitionSeconds(projWithTime.time).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(projWithTime.time).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(projWithTime.time).seconds}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Flags">
                        <div class="card">
                            <div class="card-header">
                                Month used flags
                            </div>
                            <div class="card-body">
                                <table class="table table-hover table-responsive">
                                    <thead>
                                        <tr>
                                            <th>flag</th>
                                            <th>hours</th>
                                            <th>minutes</th>
                                            <th>seconds</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="value,name in statistics.flags">
                                            <td>{{name | capitalize}}</td>
                                            <td>
                                                {{partitionSeconds(value).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(value).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(value).seconds}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Absence">
                        <div class="card">
                            <div class="card-header">
                                Month absence
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-header">
                                        <span>Absence In Work Days</span>
                                    </div>
                                    <div class="card-body">
                                        <calendar class="m-2" :year="selected.year" :month="month" :days="getDays(statistics.absence.workDaysAbsence)"></calendar>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <span>Attending In Vacations</span>
                                    </div>
                                    <div class="card-body">
                                        <calendar class="m-2" :year="selected.year" :month="month" :days="getDays(statistics.absence.vacationsAttended)"></calendar>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </b-tab>
                    <b-tab no-body title="Regular Time">
                        <div class="card">
                            <div class="card-header">
                                Month Work At Regular Time
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-header">
                                        Days Of Work Off The Regular Time
                                    </div>
                                    <div class="card-body">
                                        <calendar :year="selected.year" :month="month" :days="getDays(statistics.regularTime.offDays)"></calendar>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                {{user.name}} attended {{statistics.regularTime.percentage}} off the regular time
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Work Efficiency">
                        <div class="card">
                            <div class="card-header">
                                Month Work Efficiency
                            </div>
                            <div class="card-body">
                                <table class="table table-responsive-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>hours</th>
                                            <th>minuets</th>
                                            <th>seconds</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Whole Time At Work</td>
                                            <td>
                                                {{partitionSeconds(statistics.workEfficiency.attendedTime).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.workEfficiency.attendedTime).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.workEfficiency.attendedTime).seconds}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Actual Worked Time</td>
                                            <td>
                                                {{partitionSeconds(statistics.workEfficiency.actualWork).hours}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.workEfficiency.actualWork).minutes}}
                                            </td>
                                            <td>
                                                {{partitionSeconds(statistics.workEfficiency.actualWork).seconds}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                {{user.name}} work efficiency is {{statistics.workEfficiency.percentage}}
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Charts">
                        <div class="card">
                            <div class="card col-md-6">
                                <div class="card-header text-center">
                                    Regular Time
                                </div>
                                <div class="card-body">
                                    <pie-chart :data="getRegularTimeChartData()"></pie-chart>
                                </div>
                            </div>
                            <div class="card col-md-6">
                                <div class="card-header text-center">
                                    Flags
                                </div>
                                <div class="card-body">
                                    <bar-chart :data="getFlagsChartData()"></bar-chart>
                                </div>
                            </div>
                            <div class="card col-md-6">
                                <div class="card-header text-center">
                                    Work Efficiency
                                </div>
                                <div class="card-body">
                                    <pie-chart :data="getWorkEfficiencyChartData()"></pie-chart>
                                </div>
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
    import BarChart from './BarChart';
    import PieChart from './PieChart';

    export default {
        name: "Month",
        components: {
            UserSearch,
            Calendar,
            BarChart,
            PieChart
        },
        data(){
            return {
                statistics: null,
                months: [
                    {value: null,text: 'Month',selected: true,disabled: true}
                ],
                years: [
                    {value: null,text: 'Year',selected: true,disabled:true}
                ],
                form: {
                    userId: null,
                    month: null,
                    year: null
                },
                selected: {
                    month: null,
                    year: null
                },
                user: null,
                formReady: false,
                showAlert: 'choose'
            }
        },
        computed: {
            month: function () {
                for(let i in this.months){
                    if(this.months[i].value === this.selected.month){
                        return {name: this.months[i].text,number: this.months[i].value -1};
                    }
                }
            }
        },
        filters: {
            capitalize: function (value) {
                return capitalize(value);
            }
        },
        mounted(){
           this.setYears();
           this.months = this.months.concat(this.setMonths());
        },
        methods: {
            userSelected(user){
                this.form.userId = user.id;
                this.user = user;
                this.validate();
            },
            setYears(){
                let year = 2010;
                while(year <= moment().format('YYYY')){
                    this.years.push({value: year,text: year});
                    year++;
                }
            },
            validate(){
                this.formReady = this.form.userId != null && this.form.month != null && this.form.year != null;
            },
            getStatistics(){
                let url = `/month/report/admin?userId=${this.form.userId}&month=${this.form.month}&year=${this.form.year}`;
                makeRequest({
                    method: 'get',
                    url: url
                }).then((response) => {
                    if(!response.data.monthStatistics.work_status){
                        this.showAlert = 'no_work';
                    }else{
                        this.selected.year = this.form.year;
                        this.selected.month = this.form.month;
                        this.showAlert = false;
                    }
                    this.statistics = response.data.monthStatistics;
                });
            },
            getDays(datesArray){
                let days = [];
                for(let i in datesArray){
                    days.push(datesArray[i].split('-')[2]);
                }
                return days;
            },
            getRegularTimeChartData(){
                return {
                    labels: ['on time','off time'],
                    datasets: [
                        {
                            backgroundColor: [
                                '#00D8FF',
                                '#DD1B16'
                            ],
                            data: [
                                this.statistics.regularTime.all - this.statistics.regularTime.offTimes,
                                this.statistics.regularTime.offTimes
                            ]
                        }
                    ]
                };
            },
            getFlagsChartData(){
                let labels = [];
                let data = [];
                for(let flag in this.statistics.flags){
                    if(flag === 'total'){
                        continue;
                    }
                    labels.push(flag);
                    data.push(this.partitionSeconds(this.statistics.flags[flag]).hours);
                }
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
            getWorkEfficiencyChartData(){
                return {
                    labels: ['wasted time','real work time'],
                    datasets: [
                        {
                            backgroundColor: [
                                '#DD1B16',
                                '#00D8FF'
                            ],
                            data: [
                                this.partitionSeconds(this.statistics.workEfficiency.attendedTime -  this.statistics.workEfficiency.actualWork).hours,
                                this.partitionSeconds(this.statistics.workEfficiency.actualWork).hours
                            ]
                        }
                    ]
                };
            }

        }
    }
</script>

<style scoped>

</style>
