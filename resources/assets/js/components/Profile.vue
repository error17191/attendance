<template>
    <div class="card-body">
        <user-search placeHolder="Select Employee" @selected="userSelected"></user-search>
        <div v-if="selected != null" class="container">
            <span>Name: {{selected.name}}</span>
            <br>
            <span>UserName: {{selected.username}}</span>
            <br>
            <span>Email: {{selected.email}}</span>
            <br>
            <span>Mobile: {{selected.mobile}}</span>
            <br>
            <span>Tracked: {{selected.tracked | info}}</span>
            <br>
            <span>Can Work Anywhere: {{selected.work_anywhere | info}}</span>
            <br>
            <span>Is Working Now: {{selected.status | info}}</span>
            <br>
            <span>Is Using Work Flag: {{selected.flag | info}}</span>
        </div>
        <div v-else class="alert alert-info">
            Please Select An Employee
        </div>
    </div>
</template>

<script>
    import UserSearch from './UserSearch';

    export default {
        name: "Profile",
        components: {
            UserSearch
        },
        data(){
            return {
                selected: null
            }
        },
        filters: {
            info: function (value) {
                if(value === 'on' || value === true || value === 1){
                    return 'yes';
                }
                return 'no';
            }
        },
        methods: {
            userSelected(user){
                this.selected = user;
            }
        }
    }
</script>

<style scoped>

</style>