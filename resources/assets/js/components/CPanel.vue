<template>
    <div class="container">
        <b-card no-body class="row">
            <b-tabs pills card vertical nav-wrapper-class="col-12 col-md-3">
                <b-tab title="Vacations" active>
                    <vacations></vacations>
                </b-tab>
                <b-tab title="Timing">
                    <timing></timing>
                </b-tab>
                <b-tab title="Work Flags">
                    <work-flags></work-flags>
                </b-tab>
                <b-tab title="Notifications">
                    <Notifications></Notifications>
                </b-tab>
                <b-tab title="ScanQR">
                    <q-r-code></q-r-code>
                </b-tab>
                <b-tab title="Statistics">
                    <statistics></statistics>
                </b-tab>

            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import Vacations from './Vacations';
    import Timing from './Timing';
    import WorkFlags from './WorkFlags';
    import Notifications from './Notifications';
    import QRCode from './QRCode';
    import Statistics from './Statistics';

    export default {
        components: {
            Vacations,
            Timing,
            WorkFlags,
            Notifications,
            QRCode,
            Statistics
        }
    }
</script>
