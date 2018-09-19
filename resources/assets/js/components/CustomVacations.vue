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
            <div class="form-check form-check-inline">
                <input v-model="dateType" class="form-check-input" type="radio" id="date-type-sinle" value="single"
                       name="date_type">
                <label class="form-check-label" for="date-type-sinle">Single Date</label>
            </div>
            <div class="form-check form-check-inline">
                <input v-model="dateType" class="form-check-input" type="radio" id="date-type-range" value="range"
                       name="date_type">
                <label class="form-check-label" for="date-type-range">Date Range</label>
            </div>
            <div class="form-check form-check-inline">
                <input v-model="dateType" class="form-check-input" type="radio" id="date-type-multiple" value="multiple"
                       name="date_type">
                <label class="form-check-label" for="date-type-multiple">Multiple Dates</label>
            </div>
            <div class="row">
                <div class="col">
                    <flat-pickr
                        class="form-control bg-white"
                        v-model="date"
                        :config="dateConfig"
                    ></flat-pickr>
                </div>
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
            <div v-else>
                <h4>
                    <span
                        style="cursor: pointer"
                        v-for="customVacation,index in customVacations"
                        @click.prevent="highlight($event,index)"
                        :class="`noselect badge m-2 ${customVacation.highlight ? 'badge-primary' : 'badge-info'}`">{{customVacation.date}}
                        &nbsp;&nbsp;
                        <button type="button" class="close text-light"
                                :disabled="deleting"
                                @click.stop="deleteVacation(customVacation,index)"
                        >
                          <span aria-hidden="true">&times;</span>
                        </button>
                    </span>
                </h4>
                <div class="form-check-inline" v-if="customVacations.length > 1">
                    <input v-model="selectAll" id="select-all-custom-vacations" class="form-check-input"
                           type="checkbox">
                    <label for="select-all-custom-vacations" class="form-check-label">Select All</label>
                </div>
            </div>
            <div class="text-right" v-if="customVacations.length > 1">
                <button
                    @click="deleteVacations"
                    :disabled="highlightedVacations.length == 0"
                    class="btn btn-danger"><i class="far fa-trash-alt"></i></button>
            </div>
        </div>
    </div>
</template>

<script>
    import flatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';

    export default {
        components: {
            flatPickr
        },
        mounted() {
            window.aa = this.addRangeVacations;
            makeRequest({
                method: 'get',
                url: '/vacations/custom',
            }).then(response => {
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
                dateType: 'single',
                date: null,
                dateConfig: {
                    mode: 'single'
                },
                adding: false,
                customVacations: [],
                deleting: false,
                highlightedVacations: [],
                lastHighlighted: null,
                selectAll: false,
            }
        },
        methods: {
            addVacation() {
                this.adding = true;
                let data = {};
                switch (this.target){
                    case 'all':
                        data.global = 1;
                        break;
                }
                switch (this.dateType) {
                    case 'single':
                        data.dates =[this.date];
                        break;
                    case 'range':
                        data.dates = this.getRangeVacations();
                        break;
                }

                makeRequest({
                    method: 'post',
                    url: '/vacations/custom',
                    data: data
                }).then((response) => {
                    this.adding = false;
                    this.date = null;
                    this.$snotify.success('Vacation Added Successfully');
                    this.customVacations = response.data.custom_vacations;
                    this.updateDisabledDates();
                    this.sortVacations();
                });
            },
            getRangeVacations() {
                let [startDate, endDate] = this.date.split(/\s*to\s*/);
                let diffDays = moment(endDate).diff(moment(startDate), 'days');
                let dates = [startDate];
                let date = moment(startDate);
                for (let i = 1; i < diffDays; i++) {
                    dates.push(date.add(1, 'days').format('YYYY-MM-DD'));
                }
                dates.push(endDate);
                return dates;
            },
            deleteVacation(customVacation, index) {
                this.deleting = true;
                makeRequest({
                    method: 'post',
                    url: '/vacations/custom/delete',
                    data: {ids: [customVacation.id]}
                }).then(() => {
                    this.lastHighlighted = null;
                    if (customVacation.highlight) {
                        let toRemoveIndex = this.highlightedVacations.indexOf(customVacation.id);
                        this.highlightedVacations.splice(toRemoveIndex, 1);
                    }
                    this.customVacations.splice(index, 1);
                    this.updateDisabledDates();
                    this.deleting = false;
                    this.$snotify.success('Vacation Deleted Successfully');
                });
            },
            deleteVacations(){
                this.deleting = true;
                makeRequest({
                    method: 'post',
                    url: '/vacations/custom/delete',
                    data: {ids: this.highlightedVacations}
                }).then((response) => {
                    this.lastHighlighted = null;
                    this.highlightedVacations = [];
                    this.customVacations = response.data.custom_vacations;
                    this.updateDisabledDates();
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
            highlight($event,index) {
                if (this.customVacations.length < 2) {
                    return;
                }
                let highlighted = this.customVacations[index].highlight = !this.customVacations[index].highlight;
                if (highlighted) {
                    if(this.lastHighlighted && $event.shiftKey){
                        let min = Math.min(this.lastHighlighted,index);
                        let max = Math.max(this.lastHighlighted,index);
                        for(let i=min+1;i<=max;i++){
                            this.customVacations[i].highlight = true;
                            this.highlightedVacations.push(this.customVacations[i].id);
                        }
                    }else{
                        this.highlightedVacations.push(this.customVacations[index].id);
                    }
                    this.lastHighlighted = index;
                } else {
                    this.lastHighlighted = false;
                    let toRemoveIndex = this.highlightedVacations.indexOf(this.customVacations[index].id);
                    this.highlightedVacations.splice(toRemoveIndex, 1);
                }
            },
            updateDisabledDates(){
                this.dateConfig.disable = [];
                this.customVacations.forEach(el => {
                    this.dateConfig.disable.push(el.date);
                });
            }
        },
        watch: {
            selectAll() {
                if (this.selectAll) {
                    this.highlightedVacations = [];
                    this.customVacations.forEach(cv => {
                        cv.highlight = true;
                        this.highlightedVacations.push(cv.id);
                    });
                } else {
                    this.highlightedVacations = [];
                    this.customVacations.forEach(cv => {
                        cv.highlight = false;
                    });
                }
            },
            highlightedVacations() {
                if (this.highlightedVacations.length == 0) {
                    this.selectAll = false;
                }
                if (this.highlightedVacations.length == this.customVacations.length) {
                    this.selectAll = true;
                }
            },
            dateType() {
                this.dateConfig.mode = this.dateType;
            }
        }
    }
</script>
<style>
    .bg-white {
        background-color: white;
    }
    .noselect {
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: none; /* Safari */
        -khtml-user-select: none; /* Konqueror HTML */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
        user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome and Opera */
    }
</style>
