<template>
    <div v-if="show" class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Good Morning</div>

                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <button v-if="status == 'on'"
                                        @click="stopWork()"
                                        class="btn btn-lg btn-block btn-danger"
                                >Stop Work
                                </button>
                                <button v-else
                                        @click="startWork()"
                                        class="btn btn-lg btn-block btn-success"
                                >Start Work
                                </button>
                            </div>
                        </div>
                        <hr>
                        <h5 class="text-center">You worked today</h5>
                        <h3 class="text-center"> {{workTimeFormatted()}}</h3>
                        <div>
                            <b-card no-body class="mb-1">
                                <b-card-header header-tag="header" class="p-0" role="tab">
                                    <b-btn block v-b-toggle.today_report variant="default">Today Log</b-btn>
                                </b-card-header>
                                <b-collapse id="today_report" accordion="my-accordion">
                                    <b-card-body>
                                        <p v-for="sign in signs" :class="sign.type == 'start'? 'text-success': 'text-danger'">
                                            You {{sign.type == 'start' ? 'Started': 'Stopped'}} Work at {{sign.time}}
                                        </p>
                                    </b-card-body>
                                </b-collapse>
                            </b-card>
                            <b-card no-body class="mb-1">
                                <b-card-header header-tag="header" class="p-0" role="tab">
                                    <b-btn block v-b-toggle.month_report variant="default">Month Report</b-btn>
                                </b-card-header>
                                <b-collapse visible id="month_report" accordion="my-accordion">
                                    <b-card-body class="text-center">

                                    </b-card-body>
                                </b-collapse>
                            </b-card>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            axios.get('/init_state').then(response => {
                this.status = response.data.status;
                this.workTime = response.data.today_time;
                this.signs = response.data.signs;
                if(this.status == 'on'){
                    this.startCounter();
                }
                this.show = true;
            });
        },
        data() {
            return {
                show: false,
                status: null,
                workTime: null,
                signs: null,
                counterInterval : null
            }
        },
        methods: {
            startWork() {
                axios.post('/start_work').then(response => {
                    this.status = 'on';
                    this.signs.push(response.data.sign);
                    this.startCounter();
                });
            },
            stopWork() {
                axios.post('/stop_work').then(response => {
                    this.status = 'off';
                    this.signs.push(response.data.sign);
                    this.workTime = response.data.today_time;
                    this.stopCounter();
                });
            },
            workTimeFormatted() {
                return this.padWithZero(this.workTime.partitions.hours) + ":" +
                    this.padWithZero(this.workTime.partitions.minutes) + ":" +
                    this.padWithZero(this.workTime.partitions.seconds);
            },
            padWithZero(number) {
                if (("" + number).length == 1) {
                    return '0' + number;
                }
                return number;
            },
            startCounter() {
                this.counterInterval = setInterval(() => {
                    if (this.workTime.partitions.seconds < 59) {
                        this.workTime.partitions.seconds++;
                        return;
                    }
                    this.workTime.partitions.seconds = 0;
                    if(this.workTime.partitions.minutes < 59){
                        this.workTime.partitions.minutes++;
                        return;
                    }
                    this.workTime.partitions.minutes = 0;
                    this.workTime.partitions.hours++;
                }, 1000);
            },
            stopCounter(){
                clearInterval(this.counterInterval);
            }
        }
    }
</script>
