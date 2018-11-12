<template>
    <div id="month-summary" class="container">
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th colspan="2" class="text-center">Main Month Info</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>has worked this month</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary">
                    <td>{{name}}</td>
                    <td>{{employee.work_status | info}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th colspan="3" class="text-center"></th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>flag name</th>
                    <th>flag time</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="employee,name in summary" v-if="employee.work_status">
                    <tr v-for="value,flag,index in employee.flags">
                        <td v-if="index === 0" :rowspan="Object.keys(employee.flags).length">{{name}}</td>
                        <td>{{flag | capitalize}}</td>
                        <td>
                            {{partitionSeconds(value).hours | zeroPrefix}}:
                            {{partitionSeconds(value).minutes | zeroPrefix}}:
                            {{partitionSeconds(value).seconds | zeroPrefix}}
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th colspan="5" class="text-center">Work Time Details</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>ideal</th>
                    <th>actual</th>
                    <th>diff</th>
                    <th>diff type</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary" v-if="employee.work_status">
                    <td>{{name}}</td>
                    <td>
                        {{partitionSeconds(employee.idealTime).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.idealTime).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.idealTime).seconds | zeroPrefix}}
                    </td>
                    <td>
                        {{partitionSeconds(employee.actualTime).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.actualTime).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.actualTime).seconds | zeroPrefix}}
                    </td>
                    <td>
                        {{partitionSeconds(employee.diff).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.diff).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.diff).seconds | zeroPrefix}}
                    </td>
                    <td>{{employee.diffType | capitalize}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th colspan="3" class="text-center">Month Absence Log</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>work day absence</th>
                    <th>vacations attended</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary" v-if="employee.work_status">
                    <td>{{name}}</td>
                    <td>
                        <span v-for="day in employee.absence.workDaysAbsence"
                              class="btn-light"
                        >
                            |{{day}}|
                        </span>
                        <span v-if="!employee.absence.workDaysAbsence.length">
                            no work days absence
                        </span>
                    </td>
                    <td>
                        <span v-for="day in employee.absence.vacationsAttended"
                              class="btn-light"
                        >
                            |{{day}}|
                        </span>
                        <span v-if="!employee.absence.vacationsAttended.length">
                            no vacations attended
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th colspan="4" class="text-center">Regular Time</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>total attended days</th>
                    <th>total attended days off the regular time</th>
                    <th>days attended off the regular time</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary" v-if="employee.work_status">
                    <td>{{name}}</td>
                    <td>{{employee.regularTime.all}}</td>
                    <td>{{employee.regularTime.offTimes}}</td>
                    <td>
                        <span v-for="day in employee.regularTime.offDays" class="btn-light">|{{day}}|</span>
                        <span v-if="employee.regularTime.offDays.length">no days attended off the regular time</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th colspan="4" class="text-center">Work Efficiency</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>total time at work</th>
                    <th>actual work time</th>
                    <th>work efficiency percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary" v-if="employee.work_status">
                    <td>{{name}}</td>
                    <td>
                        {{partitionSeconds(employee.workEfficiency.attendedTime).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.workEfficiency.attendedTime).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.workEfficiency.attendedTime).seconds | zeroPrefix}}
                    </td>
                    <td>
                        {{partitionSeconds(employee.workEfficiency.actualWork).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.workEfficiency.actualWork).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.workEfficiency.actualWork).seconds | zeroPrefix}}
                    </td>
                    <td>{{employee.workEfficiency.percentage}} %</td>
                </tr>
            </tbody>
        </table>
        <div class="card col-md-6">
            <div class="card-header text-center">Actual Work Time</div>
            <div class="card-body"><bar-chart :data="getActualWorkTimeBarData()"></bar-chart></div>
        </div>
        <div class="card col-md-6">
            <div class="card-header text-center">Work Time Percentage</div>
            <div class="card-body"><bar-chart :data="getWorkTimePercentageBarData()"></bar-chart></div>
        </div>
        <div class="card col-md-6">
            <div class="card-header text-center"></div>
        </div>
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
        computed: {
            usedFlags: function () {
                let flags = {};
                for(let employee in this.summary){
                    if(!this.summary[employee].work_status){
                        continue;
                    }
                    for(let flag in this.summary[employee].flags){
                        if(!flags.hasOwnProperty(flag)){
                            flags[flag] = 0;
                        }
                        flags[flag] += Math.floor(this.summary[employee].flags[flag] / 60);
                    }
                }
                return flags;
            }
        },
        methods: {
            getActualWorkTimeBarData(){
                let labels = [];
                let data = [];
                for(let employee in this.summary){
                    if(!this.summary[employee].work_status){
                        continue;
                    }
                    labels.push(employee);
                    data.push(this.partitionSeconds(this.summary[employee].actualTime).hours);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'actual work',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            },
            getWorkTimePercentageBarData(){
                let labels = [];
                let data = [];
                for(let employee in this.summary){
                    if(!this.summary[employee].work_status){
                        continue;
                    }
                    labels.push(employee);
                    let value = Math.round(this.summary[employee].actualTime / this.summary[employee].idealTime * 100,2);
                    data.push(value);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Work Time Percentage',
                            backgroundColor: '#f87979',
                            data: data
                        }
                    ]
                };
            }
        }
    }
</script>

<style scoped>

</style>