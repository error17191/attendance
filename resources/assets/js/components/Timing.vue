<template>
    <div class="container">
        <b-form-group label="Regular Time" horizontal>
            <b-form-select
                    v-model="form.regularTime.from"
                    :options="fromOptions"
            >
            </b-form-select>
            <b-form-select
                    v-model="form.regularTime.to"
                    :options="toOptions"
            >
            </b-form-select>
        </b-form-group>
        <b-form-group label="Regular Hours" horizontal>
            <div class="col-md-3">
                <b-form-select
                    v-model="form.regularHours"
                    :options="hoursOptions"
                >
                </b-form-select>
            </div>
        </b-form-group>
        <b-form-group horizontal label="Notifications">
            <b-form-checkbox
                v-model="form.notifyMe.late_attendance"
            >
                <span>Notify me of late attendance</span>
            </b-form-checkbox>
            <b-collapse id="c1" :visible="form.notifyMe.late_attendance">
                <b-form-select
                        v-model="form.notifyMe.late_attendance_time"
                        :options="lateOptions"
                >
                </b-form-select>
            </b-collapse>
            <br>
            <b-form-checkbox
                    v-model="form.notifyMe.early_checkout"
            >
                <span>Notify me of early checkout</span>
            </b-form-checkbox>
            <b-collapse id="c2" :visible="form.notifyMe.early_checkout">
                <b-form-select
                        v-model="form.notifyMe.early_checkout_time"
                        :options="earlyOptions"
                >
                </b-form-select>
            </b-collapse>
        </b-form-group>
        <b-form-group horizontal>
            <b-button
                    :disabled="saving"
                    size="lg" variant="primary" @click.prevent="save">
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
                        to: 1
                    },
                    regularHours: 8,
                    notifyMe: {
                        late_attendance: false,
                        late_attendance_time:0,
                        early_checkout: false,
                        early_checkout_time: 1
                    }
                },
                saving:false
            }
        },
        computed: {
            fromOptions: function () {
                return this.generateTimeOptions('regularTime','from');
            },
            toOptions: function () {
                return this.generateTimeOptions('regularTime','to');
            },
            lateOptions: function() {
                return this.generateTimeOptions('notifyMe','late_attendance_time');
            },
            earlyOptions: function () {
                return this.generateTimeOptions('notifyMe','early_checkout_time');
            },
            hoursOptions: function () {
                let hoursOptions = [];
                for(let number = 0 ; number <= 24; number++){
                    hoursOptions.push({
                        text: number,
                        value: number,
                        selected: number === this.form.regularHours
                    });
                    hoursOptions.push({
                        text: number + 0.5,
                        value: number + 0.5,
                        selected: number + 0.5 === this.form.regularHours
                    })
                }
                return hoursOptions;
            }
        },
        mounted(){
            this.getData();
        },
        methods: {
            generateTimeOptions(type,field){
                let number = 0;
                let options = [];
                if(field === 'to'){
                    number = this.form.regularTime.from + 1;
                }else if(field === 'early_checkout_time'){
                    number = this.form.notifyMe.late_attendance_time + 1;
                }
                for(number; number < 48; number++){
                    options.push({
                        text: this.formatTime(number),
                        value: number,
                        selected: number === this.form[type][field]
                    });
                }
                return options;
            },
            formatTime(number) {
                let hour = Math.floor(number / 2);
                let minute = number % 2 === 0 ? '00' : '30';
                if (hour < 10) {
                    hour = `0${hour}`;
                }
                return `${hour}:${minute}`;
            },
            getData(){
                makeRequest({
                    method: 'get',
                    url: '/regular/time'
                }).then((response)=>{
                    this.form.regularTime.from = response.data[0].regularTime.from;
                    this.form.regularTime.to = response.data[0].regularTime.to;
                    this.form.regularHours = response.data[0].regularHours;
                    this.form.notifyMe.late_attendance = response.data[0].notifications.late_attendance;
                    this.form.notifyMe.late_attendance_time = response.data[0].notifications.late_attendance_time;
                    this.form.notifyMe.early_checkout = response.data[0].notifications.early_checkout;
                    this.form.notifyMe.early_checkout_time = response.data[0].notifications.early_checkout_time;
                });
            },
            save(){
                this.saving = true;
                makeRequest({
                    method: 'post',
                    url: 'regular/time',
                    data: this.form
                }).then((response)=>{
                    this.saving = false;
                    this.$snotify.success('Settings Saved Successfully');
                    console.log(response.data);
                });
            }
        },
        watch: {
            'form.regularTime.from': {
                handler(number){
                    if(number >= this.form.regularTime.to){
                        this.form.regularTime.to = number + 1;
                    }
                }
            },
            'form.notifyMe.late_attendance_time': {
                handler(number){
                    if(number >= this.form.notifyMe.early_checkout_time){
                        this.form.notifyMe.early_checkout_time = number + 1;
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>