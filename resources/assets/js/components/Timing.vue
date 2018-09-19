<template>
    <div>
        <span>regular time</span>

        <div class="row">
            <span>from</span>
            <div>
                <b-dropdown id="ddown1" :text="regularTime.from" size="sm" class="m-md-2">
                    <b-dropdown-item
                            v-for="i in limitFrom"
                            @click="chooseTime(i,'from')"
                            :key="i+'f'">
                        {{formatTime(i)}}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
            <span>to</span>
            <div>
                <b-dropdown id="ddown1" :text="regularTime.to" size="sm" class="m-md-2">
                    <b-dropdown-item
                            v-for="i in limitTo"
                            @click="chooseTime(i,'to')"
                            :key="i+'t'">
                        {{formatTime(i,'to')}}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <br>
        <span>regular hours</span>
    </div>
</template>

<script>
    export default {
        name: "Timing",
        data(){
            return {
                limitFrom: 48,
                regularTime:{
                    from: '00:00',
                    to: '00:30'
                },
                regularHours: 8
            }
        },
        computed: {
            limitTo: function () {
                let limitTo = this.limitFrom;
                limitTo -= this.regularTime.from.split(':')[0] * 2;
                limitTo -= this.regularTime.from.split(':')[1] / 30;
                return limitTo;
            }
        },
        methods: {
            chooseTime(number,type){
                let num1 = number;
                num1 = this.formatTime(num1,type);
                this.regularTime[type] = num1;
                if(type === 'from'){
                    let num2 = number + 1;
                    num2 = this.formatTime(num2);
                    this.regularTime.to = num2;
                }
            },
            formatTime(number,type){
                if(type === 'to'){
                    number += this.limitFrom - this.limitTo +1;
                }
                number -= 1;
                let hour = Math.floor(number/2);
                let minute = number%2 === 0 ? '00' : '30';
                if(hour < 10){
                    hour = `0${hour}`;
                }
                return `${hour}:${minute}`;
            }
        }
    }
</script>

<style scoped>

</style>