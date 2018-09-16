<template>
    <div class="container">
        <b-card no-body class="row">
            <b-tabs pills card vertical nav-wrapper-class="col-12 col-md-3">
                <b-tab title="Vacations" active>
                    <vacations></vacations>
                </b-tab>
                <b-tab title="Tab 2">
                    Tab Contents 2
                </b-tab>
                <b-tab title="Tab 3">
                    Tab Contents 3
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import Vacations from './Vacations';

    export default {
        components: {Vacations}
    }
</script>
