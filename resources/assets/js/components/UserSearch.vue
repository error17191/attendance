<template>
    <div class="container">
        <multiselect
                :placeholder="placeHolder"
                @search-change="updateEmployees"
                :close-on-select="false"
                :searchable="true"
                :internal-search="false"
                :show-labels="false"
                track-by="id"
                :hide-selected="true"
                label="username"
                :loading="loading"
                :multiple="multi"
                v-model="selected"
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
        name: "UserSearch",
        props: {
            multi:{
                type: Boolean,
                default: false
            },
            placeHolder:{
                type: String,
                default: 'Search for employees'
            }
        },
        data(){
            return {
                loading: false,
                selected: null,
                employees: []
            }
        },
        methods: {
            updateEmployees(query){
                let url = '/users?q=' + query;
                this.loading = true;
                makeRequest({
                    method: 'get',
                    url: url
                }).then((response)=>{
                    this.loading = false;
                    this.employees = response.data.users;
                });
            }
        },
        watch: {
            selected(){
                this.$emit('selected',this.selected);
            }
        }
    }
</script>

<style scoped>

</style>