<template>
    <div>
        <b-card no-body>
            <b-tabs card>
                <b-tab no-body title="Profile" active>
                    <profile></profile>
                </b-tab>
                <b-tab no-body title="Day">
                    <day></day>
                </b-tab>
                <b-tab no-body title="Month">
                    <month></month>
                </b-tab>
                <b-tab no-body title="Year">
                    <year></year>
                </b-tab>
                <b-tab no-body title="Summary">
                    <summary2></summary2>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import Profile from './Profile';
    import Day from './Day';
    import Month from './Month';
    import Year from './Year';
    import Summary from './Summary';
    import Summary2 from './Summary2';

    export default {
        name: "Statistics",
        components : {
            Profile,
            Day,
            Month,
            Year,
            Summary,
            Summary2
        }
    }

</script>

<style scoped>

</style>