<template>
    <div class="container">

        <multiselect
                v-if="isAdmin"
                placeholder="Search for employees"
                @search-change="updateEmployees"
                :close-on-select="false"
                :searchable="true"
                :internal-search="false"
                :show-labels="false"
                track-by="id"
                :hide-selected="true"
                label="username"
                :loading="loadingSearch"
                :multiple="false"
                v-model="selectedEmployee"
                :options="employees"
        >
            <template slot="option" slot-scope="props">
                <div>
                    <h5>{{props.option.name}}</h5>
                    <h6>{{props.option.email}}</h6>
                    <h6>{{props.option.username}}</h6>
                    <h6>{{props.option.mobile}}</h6>
                </div>
            </template>
        </multiselect>
    </div>
</template>

<script>
    export default {
        name: "Statistics",
        data(){
            return {
                isAdmin: false,
                loadingSearch: false,
                selectedEmployee: null,
                employees: []
            }
        },
        mounted(){
            this.isAdmin = auth_user.isAdmin;
        },
        methods: {
            updateEmployees(query){
                let url = '/users?q=' + query;
                this.loadingSearch = true;
                makeRequest({
                    method: 'get',
                    url: url
                }).then((response)=>{
                    this.loadingSearch = false;
                    this.employees = response.data.users;
                });
            }
        }
    }

</script>

<style scoped>

</style>