<template>
    <div class="container">
        <div class="container-fluid mx-4">
            <b-card no-body class="row">
                <b-tabs pills card vertical nav-wrapper-class="col-12 col-md-2">
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
    </div>
</template>

<script>
    import Vacations from './Vacations';
    export default {
        components: {Vacations}
    }
</script>
