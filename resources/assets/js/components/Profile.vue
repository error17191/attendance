<template>
    <div class="card-body">
        <user-search placeHolder="Select Employee" @selected="userSelected"></user-search>
        <table v-if="selected != null" class="table table-hover table-responsive">
            <tbody>
                <tr>
                    <td>Name</td>
                    <td>{{selected.name}}</td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td>{{selected.username}}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{selected.email}}</td>
                </tr>
                <tr>
                    <td>Mobile</td>
                    <td>{{selected.mobile}}</td>
                </tr>
                <tr>
                    <td>Tracked</td>
                    <td>{{selected.tracked | info}}</td>
                </tr>
                <tr>
                    <td>Can Work Anywhere</td>
                    <td>{{selected.work_anywhere | info}}</td>
                </tr>
                <tr>
                    <td>Is Working</td>
                    <td>{{selected.status | info}}</td>
                </tr>
                <tr>
                    <td>Is Using Flag</td>
                    <td>{{selected.flag | info}}</td>
                </tr>
            </tbody>
        </table>
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