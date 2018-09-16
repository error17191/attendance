<template>
    <b-card no-body>
        <b-tabs card>
            <b-tab no-body title="Weekends" active>
                <weekends></weekends>
            </b-tab>
            <b-tab no-body title="Annual Vacations">
                <annual-vacations></annual-vacations>
            </b-tab>
            <b-tab no-body title="Custom">
                <custom-vacations></custom-vacations>
            </b-tab>
        </b-tabs>
    </b-card>
</template>

<script>
    import Weekends from './Weekends';
    import AnnualVacations from './AnnualVacations';
    import CustomVacations from './CustomVacations';
    export default {
        components : {
            Weekends,
            AnnualVacations,
            CustomVacations
        },
        mounted() {
            axios.get('/vacations?t=' + new Date().getTime())
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
                axios.post('/vacations', {weekends: this.weekends}).then(() => {
                    this.updating = false;
                    this.$snotify.success('Weekends Updated Successfully');
                });
            }
        }
    }
</script>
