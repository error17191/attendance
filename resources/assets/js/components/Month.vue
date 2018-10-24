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
        <div v-if="showData === 'not ready'" class="alert alert-info">
            Please select an employee a month and a year
        </div>
        <div v-if="showData === 'no work'"  class="alert alert-info">
            No work in {{form.month < 10 ? `0${form.month}` : form.month}}-{{form.year}} for {{user.username}}
        </div>
        <div v-if="showData === 'show statistics'">
            <b-card no-body>
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
                    <b-tab no-body title="Work Status">
                        <div class="card">
                            <div class="card-header">
                                Month work status
                            </div>
                            <div class="card-body">
                                <table class="table table-hover table-responsive">
                                    <thead>
                                        <tr>
                                            <th>status</th>
                                            <th>hours</th>
                                            <th>minutes</th>
                                            <th>seconds</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="value,name in statistics.status">
                                            <td>{{name}}</td>
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
                                            <td>{{name}}</td>
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
                                        <tr>
                                            <td>to</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab no-body title="Absence">

                    </b-tab>
                    <b-tab no-body title="Regular Time">

                    </b-tab>
                    <b-tab no-body title="Work Efficiency">

                    </b-tab>
                </b-tabs>
            </b-card>

        </div>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';

    export default {
        name: "Month",
        components: {
            UserSearch
        },
        data(){
            return {
                statistics: null,
                months: [
                    {value: null,text: 'Month',selected: true,disabled: true},
                    {value: 1,text: 'January'},
                    {value: 2,text: 'february'},
                    {value: 3,text: 'March'},
                    {value: 4,text: 'April'},
                    {value: 5,text: 'May'},
                    {value: 6,text: 'June'},
                    {value: 7,text: 'July'},
                    {value: 8,text: 'August'},
                    {value: 9,text: 'September'},
                    {value: 10,text: 'October'},
                    {value: 11,text: 'November'},
                    {value: 12,text: 'December'}
                ],
                years: [
                    {value: null,text: 'Year',selected: true,disabled:true}
                ],
                form: {
                    userId: null,
                    month: null,
                    year: null
                },
                user: null,
                formReady: false,
                showData: 'not ready'
            }
        },
        mounted(){
           this.setYears();
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
                    if(response.data.monthStatistics == null){
                        this.showData = 'no work';
                    }else{
                        this.showData = 'show statistics';
                    }
                    this.statistics = response.data.monthStatistics;
                });
            },
            partitionSeconds(seconds){
                return partitionSeconds(seconds);
            }
        }
    }
</script>

<style scoped>

</style>