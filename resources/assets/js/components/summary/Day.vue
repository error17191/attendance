<template>
    <div id="day-summary" class="container">
        <table class="table table-responsive table-hover">
            <thead>
                <tr>
                    <th colspan="4" class="text-center">Basic Day Info</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>attended</th>
                    <th>vacation</th>
                    <th>weekend</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary">
                    <td>{{name}}</td>
                    <td>{{employee.attended | info}}</td>
                    <td>{{employee.vacation | info}}</td>
                    <td>{{employee.weekend | info}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th colspan="5" class="text-center">Full Day Work Time Info For The Attended Employees</th>
                </tr>
                <tr>
                    <th>employee</th>
                    <th>time at work</th>
                    <th>actual work time</th>
                    <th>work efficiency</th>
                    <th>started work at regular hours</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary" v-if="employee.work_status">
                    <td>{{name}}</td>
                    <td>{{partitionSeconds(employee.timeAtWork).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.timeAtWork).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.timeAtWork).seconds | zeroPrefix}}
                    </td>
                    <td>{{partitionSeconds(employee.actualWork).hours | zeroPrefix}}:
                        {{partitionSeconds(employee.actualWork).minutes | zeroPrefix}}:
                        {{partitionSeconds(employee.actualWork).seconds | zeroPrefix}}
                    </td>
                    <td>{{employee.workEfficiency}} %</td>
                    <td>{{employee.regularTime | info}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table table-responsive table-hover">
            <thead>
                <tr>
                    <th colspan="3" class="text-center">Day Log For The Attended Emloyees</th>
                </tr>
                <tr>
                    <th>name</th>
                    <th>signs</th>
                    <th>flags</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="employee,name in summary" v-if="employee.work_status">
                    <td>{{name}}</td>
                    <td>
                        <template v-for="sign in employee.workTimeLog">
                            <span>started at: {{sign.start}}</span> <br>
                            <span>stopped at: {{sign.stop}}</span> <br>
                            <span>
                                duration of work sign:
                                {{partitionSeconds(sign.duration).hours | zeroPrefix}}:
                                {{partitionSeconds(sign.duration).minutes | zeroPrefix}}:
                                {{partitionSeconds(sign.duration).seconds | zeroPrefix}}
                            </span>
                            <hr>
                        </template>
                        <hr>
                        <span>
                            total work time:
                            {{partitionSeconds(employee.actualWork).hours | zeroPrefix}}:
                            {{partitionSeconds(employee.actualWork).minutes | zeroPrefix}}:
                            {{partitionSeconds(employee.actualWork).seconds | zeroPrefix}}
                        </span>
                    </td>
                    <td>
                        <template v-for="value,name in employee.flags" class="card">
                            <span>
                                {{name | capitalize}}:
                                {{partitionSeconds(value).hours | zeroPrefix}}:
                                {{partitionSeconds(value).minutes | zeroPrefix}}:
                                {{partitionSeconds(value).seconds | zeroPrefix}}
                            </span>
                            <hr>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-for="value,name in usedFlags" class="card col-md-6">
            <div class="card-header">{{name | capitalize}}</div>
            <div class="card-body"><bar-chart :data="getFlagBarData(name)"></bar-chart></div>
        </div>
    </div>
</template>

<script>
    import BarChart from '../BarChart';

    export default {
        name: "Day",
        props: {
            summary:{
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
                        flags[flag] += this.summary[employee][flag];
                    }
                }
                return flags;
            }
        },
        methods: {
            getFlagBarData(flag){
                let labels = [];
                let data = [];
                for(let employee in this.summary){
                    if(!this.summary[employee].work_status){
                        continue;
                    }
                    labels.push(employee);
                    let value = this.summary[employee].flags[flag] || 0;
                    data.push(value);
                }
                return {
                    labels: labels,
                    datasets: [
                        {
                            label: capitalize(flag),
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