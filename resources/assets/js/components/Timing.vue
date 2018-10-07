<template>
    <div class="container">
        <b-form-group label="Regular Time" horizontal>
            <div class="col-md-3">
                <b-form-select
                        v-model="form.regularTime.from"
                        :options="fromOptions"
                >
                </b-form-select>
            </div>
            <div class="col-md-3">
                <b-form-select
                        v-model="form.regularTime.to"
                        :options="toOptions"
                >
                </b-form-select>
            </div>
        </b-form-group>
        <b-form-group label="Regular Hours" horizontal>
            <div class="col-md-3">
                <b-form-select
                    v-model="form.regularTime.regularHours"
                    :options="hoursOptions"
                >
                </b-form-select>
            </div>
        </b-form-group>
        <b-form-group label="Lost Time Flag Limit" horizontal>
            <div class="col-md-3">
                <input type="number" class="form-control" v-model="form.lostTime">
            </div>
        </b-form-group>
        <b-form-group horizontal label="Notifications">
            <b-form-checkbox
                v-model="form.notifyMe.late_attendance"
            >
                <span>Notify me of late attendance</span>
            </b-form-checkbox>
            <b-collapse id="c1" :visible="form.notifyMe.late_attendance">
                <div class="col-md-3">
                    <b-form-select
                            v-model="form.notifyMe.late_attendance_time"
                            :options="lateOptions"
                    >
                    </b-form-select>
                </div>
            </b-collapse>
        </b-form-group>
        <b-form-group horizontal>
            <b-form-checkbox
                    v-model="form.notifyMe.early_checkout"
            >
                <span>Notify me of early checkout</span>
            </b-form-checkbox>
            <b-collapse id="c2" :visible="form.notifyMe.early_checkout">
                <div class="col-md-3">
                    <b-form-select
                            v-model="form.notifyMe.early_checkout_time"
                            :options="earlyOptions"
                    >
                    </b-form-select>
                </div>
            </b-collapse>
        </b-form-group>
        <b-form-group horizontal>
            <b-button
                    :disabled="saving"
                    variant="primary" @click.prevent="save">
                {{saving ? 'Saving ..' : 'Save Changes'}}
            </b-button>
        </b-form-group>
    </div>
</template>

<script>
    export default {
        name: "Timing",
        data() {
            return {
                form: {
                    regularTime: {
                        from: 0,
                        to: 0.5,
                        regularHours: 8
                    },
                    notifyMe: {
                        late_attendance: false,
                        late_attendance_time: 0,
                        early_checkout: false,
                        early_checkout_time: 0.5
                    },
                    lostTime: 0
                },
                saving:false
            }
        },
        computed: {
            fromOptions: function () {
                return this.generateTimeOptions(0,24,'regularTime','from');
            },
            toOptions: function () {
                let start = this.form.regularTime.from + 0.5;
                return this.generateTimeOptions(start,24,'regularTime','to');
            },
            lateOptions: function() {
                let start = this.form.regularTime.from + 0.5;
                return this.generateTimeOptions(start,24,'notifyMe','late_attendance_time');
            },
            earlyOptions: function () {
                let end = this.form.regularTime.from + this.form.regularTime.regularHours;
                return this.generateTimeOptions(0,end,'notifyMe','early_checkout_time');
            },
            hoursOptions: function () {
                return this.generateTimeOptions(0,24,'regularTime','regularHours');
            }
        },
        mounted(){
            this.getData();
        },
        methods: {
            generateTimeOptions(start,end,type,field){
                let options = [];
                for(let number = start; number < end; number += 0.5){
                    options.push({
                        text: this.formatTime(number),
                        value: number,
                        selectedFlags: number === this.form[type][field]
                    });
                }
                return options;
            },
            formatTime(number) {
                let hour = Math.floor(number) >= 10 ? JSON.stringify(Math.floor(number)) : '0' + JSON.stringify(Math.floor(number));
                let minute = number - Math.floor(number) > 0 ? '30' : '00';
                return `${hour}:${minute}`;
            },
            getData(){
                makeRequest({
                    method: 'get',
                    url: '/regular/time'
                }).then((response)=>{
                    this.form.regularTime.from = response.data[0].regularTime.from;
                    this.form.regularTime.to = response.data[0].regularTime.to;
                    this.form.regularTime.regularHours = response.data[0].regularTime.regularHours;
                    this.form.notifyMe.late_attendance = response.data[0].notifications.late_attendance;
                    this.form.notifyMe.late_attendance_time = response.data[0].notifications.late_attendance_time;
                    this.form.notifyMe.early_checkout = response.data[0].notifications.early_checkout;
                    this.form.notifyMe.early_checkout_time = response.data[0].notifications.early_checkout_time;
                    this.form.lostTime = response.data[0].lostTime / 60;
                });
            },
            save(){
                this.saving = true;
                this.form.lostTime *= 60;
                makeRequest({
                    method: 'post',
                    url: 'regular/time',
                    data: this.form
                }).then((response)=>{
                    this.form.lostTime /= 60;
                    this.saving = false;
                    this.$snotify.success('Settings Saved Successfully');
                });
            }
        },
        watch: {
            'form.regularTime.from': {
                handler(number){
                    if(number >= this.form.regularTime.to){
                        this.form.regularTime.to = number + 0.5;
                    }
                    if(number >= this.form.notifyMe.late_attendance_time){
                        this.form.notifyMe.late_attendance_time = number + 0.5;
                    }
                    if(number + this.form.regularTime.regularHours < this.form.notifyMe.early_checkout_time){
                        this.form.notifyMe.early_checkout_time = number + this.form.regularTime.regularHours;
                    }
                }
            },
            'form.regularTime.regularHours': {
                handler(number){
                    if(number + this.form.regularTime.from < this.form.notifyMe.early_checkout_time){
                        this.form.notifyMe.early_checkout_time = number + this.form.regularTime.from;
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>
