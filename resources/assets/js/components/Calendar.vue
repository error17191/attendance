<template>
    <table class="table table-striped table-bordered sm col-md-1">
        <thead>
            <tr class="table-primary">
                <th colspan="7" class="text-center">{{month.name}} {{year}}</th>
            </tr>
            <tr class="table-dark">
                <th>Sun</th>
                <th>Mon</th>
                <th>Tus</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="week in calendarDays">
                <td v-for="day in week" :class="{'btn-primary': day.active}">{{day.text}}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        name: "Calendar",
        props: ['month','year','days'],
        computed: {
            calendarDays: function () {
                let monthFirst = moment().month(this.month.number).year(this.year).date(1);
                let day = moment().month(this.month.number).year(this.year).date(1).subtract(monthFirst.day(),'days');
                let days = [];
                let counter = 1;
                let week = [];
                while (!(day.month() > this.month.number && day.day() === 0)){
                    if(counter > 7){
                        counter = 1;
                        days.push(week);
                        week = [];
                    }
                    let text = day.date() < 10 ? `0${day.date()}` : day.date().toString();
                    week.push({
                        text: text,
                        active: this.days.indexOf(text) >= 0 && day.month() === this.month.number
                    });
                    counter++;
                    day.add(1,'days');
                }
                if(week.length){
                    days.push(week);
                }
                return days;
            }
        }
    }
</script>

<style scoped>

</style>