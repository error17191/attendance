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

    </div>
</template>

<script>
    import UserSearch from './UserSearch';

    export default {
        name: "Year",
        components: {
            UserSearch
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
                statistics: null
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
            },
        }
    }
</script>

<style scoped>

</style>