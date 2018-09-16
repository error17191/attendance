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
                        @click="addVacation"
                        class="btn btn-dark">Add Vacation
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Datepicker from 'vuejs-datepicker';

    export default {
        components: {
            Datepicker
        },
        data() {
            return {
                target: 'all',
                date: null,
                adding: false
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
                    .then(() => {
                        this.adding = false;
                        this.date = null;
                    });
            }
        },
    }
</script>
<style>
    .bg-white {
        background-color: white;
    }
</style>
