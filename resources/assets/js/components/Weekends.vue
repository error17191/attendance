<template>
    <div>
        <div class="card-body">
            <div v-for="weekday in weekdays" class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" :id="`weekday_${weekday.name}`" v-model="weekends"
                       :value="weekday.index">
                <label class="form-check-label" :for="`weekday_${weekday.name}`">{{weekday.name}}</label>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" @click="update" :disabled="updating">
                {{updating ? 'Updating ..': 'Update'}}
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            axios.get('/vacations/weekends?t=' + new Date().getTime())
                .then(response => {
                    this.weekdays = response.data.weekdays;
                    this.weekends = response.data.weekends;
                });
        },
        data() {
            return {
                weekdays: [],
                weekends: [],
                updating: false
            }
        },
        methods: {
            update() {
                this.updating = true;
                axios.post('/vacations/weekends', {weekends: this.weekends}).then(() => {
                    this.updating = false;
                    this.$snotify.success('Weekends Updated Successfully');
                });
            }
        }
    }
</script>
<style>

    .container-fluid {
        width: auto;
    }

</style>
