<template>
    <table class="table table-striped table-bordered sm col-md-1">
        <thead>
            <tr class="table-primary">
                <th colspan="7" class="text-center">
                    <slot name="before"></slot>
                    {{month.name}} {{year}}
                    <slot name="after"></slot>
                </th>
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
                let monthFirst = moment().year(this.year).month(this.month.number).startOf('month');
                let start = monthFirst.subtract(monthFirst.day(),'days');
                let monthLast = moment().year(this.year).month(this.month.number).endOf('month');
                let end = monthLast.add(6 - monthLast.day(),'days');
                let calendarDays = [];
                let week = [];
                while (start <= end){
                    if(week.length >= 7){
                        calendarDays.push(week);
                        week = [];
                    }
                    let text = start.date() < 10 ? `0${start.date()}` : start.date().toString();
                    week.push({
                        text: text,
                        active: this.days.indexOf(text) >= 0 && start.month() === this.month.number
                    });
                    start.add(1,'days');
                }
                if(week.length){
                    calendarDays.push(week);
                }
                return calendarDays;
            }
        }
    }
</script>

<style scoped>

</style>