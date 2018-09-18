<template>
    <div>
        <div class="card-body">
            <div class="form-check form-check-inline">
                <input v-model="target" class="form-check-input" type="radio" id="all-target" value="all" name="target">
                <label class="form-check-label" for="all-target">For All Employees</label>
            </div>
            <div class="form-check form-check-inline">
                <input v-model="target" class="form-check-input" type="radio" id="specific-target" value="specific"
                       name="target">
                <label class="form-check-label" for="specific-target">For Specific Employees</label>
            </div>
            <hr>
            <div class="row">
                <datepicker
                    input-class="form-control bg-white"
                    class="col"
                    v-model="date"
                ></datepicker>
                <div class="col">
                    <button
                        :disabled="date == null"
                        @click="addVacation"
                        class="btn btn-dark">Add Vacation
                    </button>
                </div>
            </div>
            <hr>
            <div v-if="customVacations.length == 0" class="alert alert-info">No Annual Vacations</div>
            <h4 v-else>
                <span
                    style="cursor: pointer"
                    v-for="customVacation,index in customVacations"
                    @click="highlight(index)"
                    :class="`badge m-2 ${customVacation.highlight ? 'badge-primary' : 'badge-info'}`">{{customVacation.date}}
                    &nbsp;&nbsp;
                    <button type="button" class="close text-light"
                            :disabled="deleting"
                            @click.stop="deleteVacation(customVacation,index)"
                    >
                      <span aria-hidden="true">&times;</span>
                    </button>
                </span>
            </h4>

        </div>
    </div>
</template>

<script>
    import Datepicker from 'vuejs-datepicker';

    export default {
        components: {
            Datepicker
        },
        mounted() {
            axios.get('/vacations/custom?t=' + new Date().getTime())
                .then(response => {
                    this.customVacations = [];
                    response.data.custom_vacations.forEach((cv) => {
                        cv.highlight = false;
                        this.customVacations.push(cv);
                    });
                    this.sortVacations();
                });
        },
        data() {
            return {
                target: 'all',
                date: null,
                adding: false,
                customVacations: [],
                deleting: false,

            }
        },
        methods: {
            addVacation() {
                this.adding = true;
                let data = {};
                if (this.target == 'all') {
                    data.global = 1;
                }
                let date = new Date(this.date);
                let dateFinal = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                data.date = dateFinal;
                axios.post('/vacations/custom', data)
                    .then((response) => {
                        this.adding = false;
                        this.date = null;
                        this.$snotify.success('Vacation Added Successfully');
                        this.customVacations.push(response.data.custom_vacation);
                        this.sortVacations();
                    });
            },
            deleteVacation(customVacation, index) {
                this.deleting = true;
                axios.post('/vacations/custom/delete', {id: customVacation.id})
                    .then(() => {
                        this.customVacations.splice(index, 1);
                        this.deleting = false;
                        this.$snotify.success('Vacation Deleted Successfully');
                    });
            },
            sortVacations() {
                this.customVacations.sort((a, b) => {
                    if (new Date(a.date) < new Date(b.date)) {
                        return -1;
                    }
                    if (new Date(a.date) > new Date(b.date)) {
                        return 1;
                    }
                    return 0;
                });
            },
            highlight(index){
                this.customVacations[index].highlight = ! this.customVacations[index].highlight;
            }
        },
    }
</script>
<style>
    .bg-white {
        background-color: white;
    }
</style>
