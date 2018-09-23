<template>
    <div>
        <v-select
            lablel="username"
            :filterable="false" v-model="selected" :options="options" @search="onSearch">
            <template slot="option" slot-scope="option">
                <h5>{{option.name}}</h5>
                <h6>{{option.email}}</h6>
                <h6>{{option.mobile}}</h6>
                <h6>{{option.username}}</h6>
            </template>
            <template slot="no-options">
                {{message}}
            </template>
            <template slot="selected-option" slot-scope="option">
                <div class="selected d-center">
                    {{ option.username}}
                </div>
            </template>

        </v-select>
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
    </div>
</template>

<script>
    import Weekends from './Weekends';
    import AnnualVacations from './AnnualVacations';
    import CustomVacations from './CustomVacations';

    export default {
        components: {
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
                selected: null,
                options: [],
                weekdays: [],
                weekends: [],
                updating: false,
                initialMessage: 'Type name, email, username or mobile to search',
                noResultsMessage: 'No results matching your search',
                message: ''
            }
        },
        mounted() {
            this.message = this.initialMessage;
        },
        methods: {
            update() {
                this.updating = true;
                axios.post('/vacations', {weekends: this.weekends}).then(() => {
                    this.updating = false;
                    this.$snotify.success('Weekends Updated Successfully');
                });
            },
            onSearch(param) {
                if (!param) {
                    this.options = [];
                    this.message = this.initialMessage;
                    return;
                }
                axios.get('/users?q=' + param)
                    .then(response => {
                        this.options = response.data.users;
                        if (this.options.length == 0) {
                            this.message = this.noResultsMessage;
                        }
                    });
            }
        }
    }
</script>
