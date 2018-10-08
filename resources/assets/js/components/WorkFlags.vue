<template>
    <div class="container">
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="New Flag" v-model="newFlag">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary form-control" @click="addFlag" :disabled="flagExists || !newFlag.length">
                        Add Flag
                    </button>
                </div>
                <span class="text-danger" v-if="flagExists">
                    This flag already exists
                </span>
            </div>
        </div>
        <div v-if="flags.length === 0" class="alert alert-info">No Flags</div>
        <div v-else>
            <h4>
                    <span   style="cursor: pointer"
                            v-for="flag,index in flags"
                            @click.prevent="toggleHighlight(flag)"
                            :class="`noselect badge m-2 ${flag.highlighted ? 'badge-primary' : 'badge-info'}`">{{flag.name | capitalize}}
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
                    :disabled="selectedFlags.length === 0 || deleting"
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
                deleting: false,
                flagExists: false
            }
        },
        filters: {
            capitalize: function (value) {
                if(!value){
                    return '';
                }
                value = value.toString();
                let values = value.split('_');
                for(let i in values){
                    values[i] = values[i].charAt(0).toUpperCase() + values[i].slice(1);
                }
                return values.join(' ');
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
                });
            },
            addFlag(){
                if(this.newFlag.length <= 0 || this.flagExists){
                    return;
                }
                let data = {flagName: this.formatFlagName(this.newFlag)};
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
            },
            formatFlagName(name){
                if(!name.length){
                    return '';
                }
                name = name.toString();
                let words = name.split(' ');
                for(let i in words){
                    words[i] = words[i].toLowerCase();
                }
                return words.join('_');
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
            },
            newFlag(value){
                this.flagExists = this.getFlagIndex(this.formatFlagName(value)) >= 0;
            }
        }
    }
</script>

<style scoped>

</style>