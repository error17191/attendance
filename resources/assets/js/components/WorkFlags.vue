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
                            @click.prevent="toggleHighlight(flag)"
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
                    :disabled="selectedFlags.length === 0"
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
                selectedFlags: [],
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
                let data = {flagName: this.newFlag};
                makeRequest({
                    method: 'post',
                    url: '/admin/flag',
                    data: data
                }).then((response)=>{
                    this.newFlag = '';
                    this.getFlags();
                });
            },
            deleteFlags($event,flag){
                let data = {flagsNames: flag ? [flag] : this.selectedFlags};
                this.deleting = true;
                makeRequest({
                    method: 'delete',
                    url: '/admin/flags',
                    data: data
                }).then((response)=>{
                    this.deleting = false;
                    this.removeDeletedFlags();
                });
            },
            toggleHighlight(flag){
                let index = this.flags.indexOf(flag);
                let highlighted = this.flags[index].highlighted = !this.flags[index].highlighted;
                if(highlighted){
                    this.selectedFlags.push(this.flags[index].name);
                }else{
                    this.selectedFlags.splice(this.selectedFlags.indexOf(flag.name),1);
                }
            },
            removeDeletedFlags(){
                for(let i in this.selectedFlags){
                    let index = this.getFlagIndex(this.selectedFlags[i]);
                    if(index >= 0){
                        this.flags.splice(index,1);
                    }
                }
                this.selectedFlags = [];
            },
            getFlagIndex(name){
                for(let i in this.flags){
                    if(this.flags[i].name === name){
                        return i;
                    }
                }
                return -1;
            }
        },
        watch: {
            selectAll(value){
                this.selectedFlags = [];
                if(value){
                    for(let i in this.flags){
                        this.selectedFlags.push(this.flags[i].name);
                        this.flags[i].highlighted = true;
                    }
                }else{
                    for(let i in this.flags){
                        this.flags[i].highlighted = false;
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>