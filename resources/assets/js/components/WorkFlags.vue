<template>
    <div class="container">
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="New Flag" v-model="newFlag">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary form-control" @click="addFlag">
                        Add Flag
                    </button>
                </div>
            </div>
        </div>
        <div v-if="flags.length === 0" class="alert alert-info">No Flags</div>
        <div v-else>
            <h4>
                    <span   style="cursor: pointer"
                            v-for="flag,index in flags"
                            @click.prevent="toggleHighlight($event,flag)"
                            :class="`noselect badge m-2 ${flag.highlighted ? 'badge-primary' : 'badge-info'}`">{{flag.name}}
                        &nbsp;
                        <button type="button" class="close text-light"
                                :disabled="deleting"
                                @click.stop="deleteFlags(flag.name)"
                        >
                          <span aria-hidden="true">&times;</span>
                        </button>
                    </span>
            </h4>
            <div class="form-check-inline" v-if="flags.length > 1">
                <input v-model="selectAll" id="select-all-custom-vacations" class="form-check-input"
                       type="checkbox">
                <label for="select-all-custom-vacations" class="form-check-label">Select All</label>
            </div>
        </div>
        <div class="text-right" v-if="flags.length > 1">
            <button
                    @click="deleteFlags"
                    :disabled="selected.length === 0"
                    class="btn btn-danger"><i class="far fa-trash-alt"></i></button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "WorkFlags",
        data(){
            return {
                flags: [],
                selected: [],
                newFlag: '',
                selectAll: false,
                deleting: false
            }
        },
        mounted(){
            this.getFlags();
        },
        methods: {
            getFlags(){
                makeRequest({
                    method: 'get',
                    url: '/admin/flags'
                }).then((response)=>{
                    this.flags = response.data.flags;
                    console.log(response.data.flags);
                });
            },
            addFlag(){
                if(this.newFlag.length <= 0){
                    return;
                }
                let data = {flag: this.newFlag};
                makeRequest({
                    method: 'POST',
                    url: '/admin/flag',
                    data: data
                }).then((response)=>{
                    this.getFlags();
                });
            },
            deleteFlags(flag){

            },
            toggleHighlight(event,flag){
                let index = this.flags.indexOf(flag);
                if(flag.highlighted && this.selected.indexOf(flag.name) >= 0){
                    this.selected.splice(this.selected.indexOf(flag.name),1);
                    this.flags[index].highlighted = false;
                }else if(!flag.highlighted && this.selected.indexOf(flag.name) < 0){
                    this.selected.push(flag.name);
                    this.flags[index].highlighed = true;
                }

            }
        }
    }
</script>

<style scoped>

</style>