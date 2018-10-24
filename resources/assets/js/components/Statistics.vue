<template>
    <div>
        <b-card no-body>
            <b-tabs card>
                <b-tab no-body title="Profile" active>
                    <profile></profile>
                </b-tab>
                <b-tab no-body title="Day">

                </b-tab>
                <b-tab no-body title="Month">
                    <month></month>
                </b-tab>
                <b-tab no-body title="Year">

                </b-tab>
                <b-tab no-body title="Summary">

                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import Profile from './Profile';
    import Month from './Month';

    export default {
        name: "Statistics",
        components : {
            Profile,
            Month
        }
    }

</script>

<style scoped>

</style>