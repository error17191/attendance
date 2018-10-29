<template>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <user-search @selected="updateSelectedUser"></user-search>
                </div>
                <div class="col-md-4">
                    <flat-pickr
                            class="form-control bg-white"
                            v-model="form.date"
                            :config="dateConfig"
                    ></flat-pickr>
                </div>
                <div class="col-md-3">
                    <button :disabled="!formReady"
                            @click="getStatistics"
                            class="btn btn-primary"
                    >Get Statistics</button>
                </div>
            </div>
            <div v-if="showAlert" class="alert alert-info">
                Please Select An Employee And A Day
            </div>
            <b-card v-else no-body>
                <b-tabs card>
                    <b-tab no-body title="Attendance" active>
                        <div class="card">
                            <table class="table table-responsive table-hover">
                                <tbody>
                                    <tr>
                                        <td>Attended</td>
                                        <td>{{statistics.attended | info}}</td>
                                    </tr>
                                    <tr>
                                        <td>Day is Weekend</td>
                                        <td>{{statistics.weekend | info}}</td>
                                    </tr>
                                    <tr>
                                        <td>Day is Vacation</td>
                                        <td>{{statistics.vacation | info}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-tab>
                    <b-tab v-if="statistics.attended" no-body title="Work Time">
                        <div class="card">
                            <table class="table table-responsive table-hover">
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
                                        <td>Time At Work</td>
                                        <td>{{partitionSeconds(statistics.timeAtWork).hours}}</td>
                                        <td>{{partitionSeconds(statistics.timeAtWork).minutes}}</td>
                                        <td>{{partitionSeconds(statistics.timeAtWork).seconds}}</td>
                                    </tr>
                                    <tr>
                                        <td>Actual Worked Time</td>
                                        <td>{{partitionSeconds(statistics.actualWork).hours}}</td>
                                        <td>{{partitionSeconds(statistics.actualWork).minutes}}</td>
                                        <td>{{partitionSeconds(statistics.actualWork).seconds}}</td>
                                    </tr>
                                    <tr>
                                        <td>Work Efficiency</td>
                                        <td colspan="3" class="text-center">{{statistics.workEfficiency}}&percnt;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-tab>
                    <b-tab v-if="statistics.attended" no-body title="Work Flags">
                        <div class="card">
                            <table class="table table-responsive table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>hours</th>
                                        <th>minutes</th>
                                        <th>seconds</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="value,name in statistics.flags">
                                        <td>{{name | capitalize}}</td>
                                        <td>{{partitionSeconds(value).hours}}</td>
                                        <td>{{partitionSeconds(value).minutes}}</td>
                                        <td>{{partitionSeconds(value).seconds}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-tab>
                    <b-tab v-if="statistics.attended" no-body title="Regular Time">
                        <div class="card">
                            <table class="table table-responsive table-hover">
                                <tbody>
                                    <tr>
                                        <td>Attending In Regular Time</td>
                                        <td>{{statistics.regularTime | info}}</td>
                                    </tr>
                                    <tr>
                                        <td>Completed Day Regular Hours</td>
                                        <td>{{statistics.regularHours | info}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-tab>
                    <b-tab v-if="statistics.attended" no-body title="Day Work Log">
                        <div class="card">
                            <table class="table table-responsive table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Start Time</th>
                                        <th>Stop Time</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="log,index in statistics.workTimeLog">
                                        <td>{{index + 1}}</td>
                                        <td>{{log.start}}</td>
                                        <td>{{log.stop}}</td>
                                        <td>{{partitionSeconds(log.duration).hours | zeroPrefix}}:{{partitionSeconds(log.duration).minutes | zeroPrefix}}:{{partitionSeconds(log.duration).seconds | zeroPrefix}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-tab>
                </b-tabs>
            </b-card>
        </div>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';
    import PieChart from './PieChart';

    export default {
        name: "Day",
        components: {
            UserSearch,
            flatPickr,
            PieChart
        },
        data(){
            return {
                form: {
                    userId: 0,
                    date: ''
                },
                formReady: false,
                dateConfig: {
                    mode: 'single'
                },
                statistics: null,
                showAlert: true
            }
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

        },
        methods: {
            updateSelectedUser(user){
                this.form.userId = user.id;
            },
            formValidate(){
                this.formReady = this.form.date != '' && this.form.userId != '';
            },
            getStatistics(){
                let url = `/day/report/admin?userId=${this.form.userId}&date=${this.form.date}`;
                this.formReady = false;
                makeRequest({
                    method: 'get',
                    url: url
                }).then((response) => {
                    this.statistics = response.data.statistics;
                    this.formReady = true;
                    this.showAlert = response.data.status !== 'success';
                });
            },
            partitionSeconds(seconds){
                return partitionSeconds(seconds);
            }
        },
        watch: {
            "form.date": function () {
                this.formValidate();
            },
            "form.userId": function () {
                this.formValidate();
            }
        }
    }
</script>

<style scoped>

</style>