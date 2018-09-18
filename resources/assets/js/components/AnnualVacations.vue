<template>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <select
                    :class="{'form-control' : true,'is-invalid' : showMonthFeedback}"
                    v-model="month"
                >
                    <option selected :value="null">Month</option>
                    <option v-for="month in months" :value="month">{{month.name}}</option>
                </select>
                <div v-if="showMonthFeedback" class="invalid-feedback">Please select Month</div>
            </div>
            <div class="col-sm-5">
                <select
                    :class="{'form-control' : true,'is-invalid' : showDayFeedback}"
                    v-model="day"
                    :disabled="month == null"
                >
                    <option selected :value="0">Day</option>
                    <template v-if="month != null">
                        <option
                            v-for="i in month.days"
                            :disabled="annualVacationsGrouped[month.index] && annualVacationsGrouped[month.index].includes(i)"
                            :value="i">{{i}}
                        </option>
                    </template>
                </select>
                <div v-if="showDayFeedback" class="invalid-feedback">Please select day</div>
            </div>
            <div class="col-sm-2">
                <button
                    :disabled="month == null || day == 0 || adding"
                    class="btn btn-dark" @click="add">
                    {{adding ? 'Adding ..' : 'Add'}}
                </button>
            </div>
        </div>
        <hr>
        <div v-if="annualVacations.length == 0" class="alert alert-info">No Annual Vacations</div>
        <h4 v-else>
                <span
                    v-for="annualVacation in annualVacations"
                    class="badge badge-info m-2">{{annualVacation.month.name}} {{annualVacation.day}}
                    &nbsp;&nbsp;
                    <button type="button" class="close text-light"
                            :disabled="deleting"
                            @click="deleteVacation(annualVacation)"
                    >
                      <span aria-hidden="true">&times;</span>
                    </button>
                </span>
        </h4>
    </div>
</template>

<script>
    export default {
        mounted() {

            axios.get('vacations/annual?t=' + new Date().getTime())
                .then(response => {
                    this.months = response.data.months;
                    this.annualVacations = response.data.annual_vacations;
                    this.sortVacations();
                    this.annualVacations.forEach((vacation) => {
                        if (!this.annualVacationsGrouped[vacation.month.index]) {
                            this.annualVacationsGrouped[vacation.month.index] = [];
                        }
                        this.annualVacationsGrouped[vacation.month.index].push(vacation.day);
                    });
                });
        },
        data() {
            return {
                showDayFeedback: false,
                showMonthFeedback: false,
                day: 0,
                month: null,
                months: [],
                annualVacations: [],
                adding: false,
                deleting: false,
                annualVacationsGrouped: {}
            }
        },
        methods: {
            add() {
                this.adding = true;
                axios.post('vacations/annual/add', {month: this.month.index, day: this.day})
                    .then(response => {
                        this.$snotify.success('Annual Vacation Added Successfully');
                        this.adding = false;
                        this.annualVacations.push(response.data.annual_vacation);
                        this.sortVacations();
                        if (!this.annualVacationsGrouped[this.month.index]) {
                            this.annualVacationsGrouped[this.month.index] = [];
                        }
                        this.annualVacationsGrouped[this.month.index].push(this.day);
                        this.month = null;
                        this.day = 0;
                    });
            },
            deleteVacation(annualVacation) {
                this.deleting = true;
                axios.post('/vacations/annual/delete', {month: annualVacation.month.index, day: annualVacation.day})
                    .then(response => {
                        for (let i = 0; i < this.annualVacations.length; i++) {
                            if (this.annualVacations[i].month.index == annualVacation.month.index && this.annualVacations[i].day == annualVacation.day) {
                                this.annualVacations.splice(i, 1);
                                break;
                            }
                        }
                        // Make it available in the dropdown again
                        let index = this.annualVacationsGrouped[annualVacation.month.index].indexOf(annualVacation.day);
                        this.annualVacationsGrouped[annualVacation.month.index].splice(index, 1);
                        if (this.annualVacationsGrouped[annualVacation.month.index].length == 0) {
                            Vue.delete(this.annualVacationsGrouped, annualVacation.month.index);
                        }

                        this.deleting = false;
                        this.$snotify.success('Annual Vacation Deleted Successfully');
                    });
            },
            sortVacations(){
                this.annualVacations.sort((a,b) => {
                    if(a.month.index < b.month.index){
                        return -1;
                    }
                    if(a.month.index > b.month.index){
                        return 1;
                    }
                    if(a.month.index == b.month.index){
                        if(a.day < b.day){
                            return -1;
                        }else{
                            return 1;
                        }
                    }
                });
            }
        },
        watch: {
            month() {
                this.day = 0;
            }
        }
    }
</script>
