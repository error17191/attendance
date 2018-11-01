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
            <div class="card col-md-10">
                <line-chart :data="workEfficiencyLine"></line-chart>

            </div>
        </div>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';
    import LineChart from './charts/LineChart';

    export default {
        name: "Year",
        components: {
            UserSearch,
            LineChart
        },
        data(){
            return {
                form: {
                    userId: null,
                    year: null
                },
                formReady: false,
                years: [
                    {value: null,text: 'Year',selected: true,disabled:true}
                ],
                user: null,
                statistics: null,
                showAlert: true
            }
        },
        computed: {
            workEfficiencyLine: function () {
                let labels = [];
                let data = [];
                for(let month in this.statistics.workEfficiency){
                    if(month === 'total'){
                        continue;
                    }
                    labels.push(month);
                    data.push(this.statistics.workEfficiency[month].percentage);
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
            partitionSeconds(seconds){
                return partitionSeconds(seconds);
            }
        }
    }
</script>

<style scoped>

</style>