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
                        <h3 class="text-center"> {{timePartitionsFormatted(workTime.partitions)}}</h3>
                        <div>
                            <b-card no-body class="mb-1">
                                <b-card-header header-tag="header" class="p-0" role="tab">
                                    <b-btn block v-b-toggle.today_report variant="default">Today Log</b-btn>
                                </b-card-header>
                                <b-collapse id="today_report" accordion="my-accordion">
                                    <b-card-body>
                                        <p v-for="sign in signs"
                                           :class="sign.type == 'start'? 'text-success': 'text-danger'">
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
                                        <p>
                                            By the end of Today you should have been worked
                                            {{timePartitionsFormatted(monthStats.ideal.partitions)}}
                                        </p>
                                        <p>
                                            Until now you worked
                                            {{timePartitionsFormatted(monthStats.actual.partitions)}}
                                        </p>
                                        <p v-if="monthStats.diff.type == 'more'" class="text-success">
                                            You have worked extra
                                            {{timePartitionsFormatted(monthStats.diff.partitions)}}
                                        </p>
                                        <p v-else class="text-danger">
                                            You have to work {{timePartitionsFormatted(monthStats.diff.partitions)}}
                                        </p>
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
                this.monthStats = response.data.month_report;

                if (this.status == 'on') {
                    this.startCounter(this.workTime.partitions);
                    this.startCounter(this.monthStats.actual.partitions);
                    this.startDiffCounter();
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
                monthStats: null,
            }
        },
        methods: {
            startWork() {
                axios.post('/start_work').then(response => {
                    this.status = 'on';
                    this.signs.push(response.data.sign);
                    this.startCounter(this.workTime.partitions);
                    this.startCounter(this.monthStats.actual.partitions);
                    this.startDiffCounter();
                });
            },
            stopWork() {
                axios.post('/stop_work').then(response => {
                    this.status = 'off';
                    this.signs.push(response.data.sign);
                    this.workTime = response.data.today_time;
                    this.stopCounter(this.workTime.partitions.counterInterval);
                    this.stopCounter(this.monthStats.actual.partitions.counterInterval);
                    this.stopCounter(this.monthStats.diff.partitions.counterInterval);
                });
            },
            timePartitionsFormatted(partitions) {
                return this.padWithZero(partitions.hours) + ":" +
                    this.padWithZero(partitions.minutes) + ":" +
                    this.padWithZero(partitions.seconds);
            },
            padWithZero(number) {
                if (("" + number).length == 1) {
                    return '0' + number;
                }
                return number;
            },
            startCounter(partitions) {
                partitions.counterInterval = setInterval(() => {
                    if (partitions.seconds < 59) {
                        partitions.seconds++;
                        return;
                    }
                    partitions.seconds = 0;
                    if (partitions.minutes < 59) {
                        partitions.minutes++;
                        return;
                    }
                    partitions.minutes = 0;
                    partitions.hours++;
                }, 1000);
            },
            startCounterDown(partitions) {
                partitions.counterInterval = setInterval(() => {
                    if (partitions.hours == 0 && partitions.minutes == 0 && partitions.seconds == 1) {
                        partitions.seconds = 0;
                        this.stopCounter(partitions.counterInterval);
                        this.startCounter(partitions);
                        return;
                    }

                    if (partitions.seconds > 0) {
                        partitions.seconds--;
                        return;
                    }
                    partitions.seconds = 59;
                    if (partitions.minutes > 0) {
                        partitions.minutes--;
                        return;
                    }
                    partitions.minutes = 59;
                    partitions.hours--;
                }, 1000);
            },
            stopCounter(counterInterval) {
                clearInterval(counterInterval);
            },
            startDiffCounter(){
                if (this.monthStats.diff.type == 'more') {
                    this.startCounter(this.monthStats.diff.partitions);
                } else {
                    this.startCounterDown(this.monthStats.diff.partitions);
                }
            }
        },
        watch: {
            'monthStats.diff.partitions.seconds': function () {
                if (this.monthStats.diff.partitions.hours == 0 &&
                    this.monthStats.diff.partitions.minutes == 0 &&
                    this.monthStats.diff.partitions.seconds == 0) {
                    this.monthStats.diff.type = 'more';
                }
            }
        }
    }
</script>
