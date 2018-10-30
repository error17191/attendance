<template>
    <div class="container">
        <vue-element-loading :active="!show" spinner="bar-fade-scale" color="#FF6700"/>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Hello, {{auth_user().name}}</div>
                    <div v-if="canWorkAnywhere">
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <multiselect  tag-placeholder="Project you are working on"
                                                 placeholder="Project you are working on"
                                                 label="title"
                                                 v-model="project"
                                                 :options="projects"
                                                 :close-on-select="true"
                                                 :multiple="false"
                                                 :searchable="false"
                                                 :trackBy="'id'"
                                                  :disabled="!!flagInUse"
                                    ></multiselect>
                                    <multiselect tag-placeholder="Task you are working on"
                                                 placeholder="Task you are working on"
                                                 :value="task"
                                                 :disabled="!!flagInUse || project == null"
                                                 @tag="addTask"
                                                 label="content"
                                                 @search-change="filterTasks"
                                                 v-model="task"
                                                 :options="tasks"
                                                 :taggable="true"
                                                 :close-on-select="true"
                                                 :searchable="true"
                                                 :internal-search="false"
                                                 :loading="loadingSearch"
                                                 :multiple="false"
                                                 :trackBy="'content'"
                                    >
                                        <template slot="option" slot-scope="props">
                                            <span v-html="props.option.content ? mark(props.option.content) : props.search ">{props}</span>
                                        </template>
                                    </multiselect>
                                    <button v-if="status == 'on'"
                                            @click="stopWork"
                                            :disabled="!!flagInUse || stopping"
                                            class="btn btn-lg btn-block btn-danger"
                                    >Stop Work
                                    </button>
                                    <button v-else
                                            @click="startWork"
                                            :disabled="starting || task == null || project == null"
                                            class="btn btn-lg btn-block btn-success"
                                    >Start Work
                                    </button>
                                </div>
                            </div>
                            <br>

                            <div class="row justify-content-center" v-if="status === 'on'">
                                <button v-for="flag in flags"
                                        class="btn mr-2 mb-2"
                                        :class="{'btn-default': !flagInUse,'btn-dark': flagInUse == flag.type}"
                                        @click.prevent="toggleFlag(flag.type)"
                                        :disabled="togglingFlag || (flagInUse != null && flag.type != flagInUse)|| flag.remainingSeconds === 0 && flag.timelimit !== 'no time limit'"
                                >
                                    {{flag.type | capitalize}}
                                </button>
                            </div>


                            <hr>
                            <h5 class="text-center">You worked today</h5>
                            <h3 class="text-center"> {{workTime ? timePartitionsFormatted(workTime.partitions) :
                                null}}</h3>
                            <div v-if="bootstrapVueLoaded">
                                <b-card no-body class="mb-1">
                                    <b-card-header header-tag="header" class="p-0" role="tab">
                                        <b-btn block v-b-toggle.today_report variant="default">Today Log</b-btn>
                                    </b-card-header>
                                    <b-collapse id="today_report" accordion="my-accordion">
                                        <b-card-body>
                                            <p v-for="sign in signs">
                                                <span class="text-success"> You Started Work on {{sign.status}} at {{sign.started_at}}</span>
                                                <br>
                                                <span v-if="sign.stopped_at" class="text-danger"> You Stopped Work on {{sign.status}} at {{sign.stopped_at}}</span>
                                            </p>
                                        </b-card-body>
                                    </b-collapse>
                                </b-card>
                                <b-card no-body class="mb-1">
                                    <b-card-header header-tag="header" class="p-0" role="tab">
                                        <b-btn block v-b-toggle.month_report variant="default">Month Report</b-btn>
                                    </b-card-header>
                                    <b-collapse visible id="month_report" accordion="my-accordion">
                                        <b-card-body class="text-center">
                                            <p>
                                                By the end of Today you should have been worked
                                                {{monthStats ? timePartitionsFormatted(monthStats.ideal.partitions) :
                                                null}}
                                            </p>
                                            <p>
                                                Until now you worked
                                                {{monthStats ? timePartitionsFormatted(monthStats.actual.partitions) :
                                                null}}
                                            </p>
                                            <p v-if="monthStats && monthStats.diff.type == 'more'" class="text-success">
                                                You have worked extra
                                                {{monthStats ? timePartitionsFormatted(monthStats.diff.partitions) :
                                                null}}
                                            </p>
                                            <p v-else class="text-danger">
                                                You have to work {{monthStats ?
                                                timePartitionsFormatted(monthStats.diff.partitions): null}}
                                            </p>
                                        </b-card-body>
                                    </b-collapse>
                                </b-card>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="card-body">
                            Start and stop only from Desktop
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                flags: [],
                show: false,
                status: null,
                workTime: null,
                signs: null,
                monthStats: null,
                task: null,
                projectTasks: [],
                tasks: [],
                taskQuery: '',
                project: {title: 'hamada'},
                projects: [],
                loadingSearch: false,
                flagInUse: null,
                canWorkAnywhere: true,
                projectInitialized: false,
                taskInitialized: false,
                starting: false,
                stopping: false,
                togglingFlag: false,
            }
        },
        mounted() {
            this.getStats();

            window.Echo.private(`App.User.${auth_user.id}`)
                .listen('FlagTimeExpired', (e) => {
                    this.getStats();
                });

        },
        created() {
            this.checkIfUserCanWorkAnyWhere();
        },
        filters: {
            capitalize: function (value) {
               return capitalize(value);
            }
        },
        methods: {
            getStats() {
                let url = '/init_state?t=' + new Date().getTime();
                makeRequest({
                    method: 'get',
                    url: url
                }).then((response) => {
                    this.status = response.data.status;
                    this.workTime = response.data.today_time;
                    this.task = this.workTime.task;
                    this.projects = response.data.projects;
                    this.project = this.workTime.project;
                    this.tasks = this.projectTasks = this.workTime.project_tasks;
                    this.signs = response.data.workTimeSigns;
                    this.monthStats = response.data.month_report;
                    this.flags = response.data.flags;
                    this.flagInUse = null;
                    for (let i in this.flags) {
                        if (this.flags[i].inUse === true) {
                            this.flagInUse = this.flags[i].type;
                            break;
                        }
                    }
                    if (this.status == 'on') {
                        this.startCounter(this.workTime.partitions);
                        this.startCounter(this.monthStats.actual.partitions);
                        this.startDiffCounter();
                    }
                    this.show = true;
                });
            },
            startWork() {
                this.starting = true;
                let data = {task: this.task, project_id: this.project.id};
                return makeRequest({
                    method: 'post',
                    url: '/start_work',
                    data: data
                }).then((response) => {
                    this.status = 'on';
                    this.signs.push(response.data.workTimeSign);
                    this.startCounter(this.workTime.partitions);
                    this.startCounter(this.monthStats.actual.partitions);
                    this.startDiffCounter();
                    this.addIdToTask(response.data.task_id);
                    this.starting = false;
                });
            },
            stopWork() {
                this.stopping = true;
                return makeRequest({
                    method: 'post',
                    url: '/stop_work'
                }).then((response) => {
                    if (this.flagInUse !== null) {
                        this.getStats();
                    }
                    this.status = 'off';
                    this.flagInUse = null;
                    // this.updateSign(response.data.workTimeSign);
                    this.signs[this.signs.length - 1].stopped_at = response.data.workTimeSign.stopped_at;
                    this.workTime = response.data.today_time;
                    this.stopCounter(this.workTime.partitions.counterInterval);
                    this.stopCounter(this.monthStats.actual.partitions.counterInterval);
                    this.stopCounter(this.monthStats.diff.partitions.counterInterval);
                    this.stopping = false;
                });
            },
            toggleFlag(type) {
                this.togglingFlag = true;
                if (this.flagInUse) {
                    this.endFlag();
                }else{
                    this.startFlag(type);
                }
            },
            startFlag(type) {
                let data = {type: type};
                return makeRequest({
                    method: 'post',
                    url: '/flag/start',
                    data: data
                }).then((response) => {
                    this.flagInUse = type;
                    this.togglingFlag = false;
                });
            },
            endFlag() {
                return makeRequest({
                    method: 'post',
                    url: '/flag/end'
                }).then((response) => {
                    this.flagInUse = null;
                    this.togglingFlag = false;
                });
            },
            timePartitionsFormatted(partitions) {
                return this.padWithZero(partitions.hours) + ":" +
                    this.padWithZero(partitions.minutes) + ":" +
                    this.padWithZero(partitions.seconds);
            },
            padWithZero(number) {
                if (("" + number).length == 1) {
                    return '0' + number;
                }
                return number;
            },
            startCounter(partitions) {
                partitions.counterInterval = setInterval(() => {
                    if (partitions.seconds < 59) {
                        partitions.seconds++;
                        return;
                    }
                    partitions.seconds = 0;
                    if (partitions.minutes < 59) {
                        partitions.minutes++;
                        return;
                    }
                    partitions.minutes = 0;
                    partitions.hours++;
                }, 1000);
            },
            startCounterDown(partitions) {
                partitions.counterInterval = setInterval(() => {
                    if (partitions.hours == 0 && partitions.minutes == 0 && partitions.seconds == 1) {
                        partitions.seconds = 0;
                        this.stopCounter(partitions.counterInterval);
                        this.startCounter(partitions);
                        return;
                    }

                    if (partitions.seconds > 0) {
                        partitions.seconds--;
                        return;
                    }
                    partitions.seconds = 59;
                    if (partitions.minutes > 0) {
                        partitions.minutes--;
                        return;
                    }
                    partitions.minutes = 59;
                    partitions.hours--;
                }, 1000);
            },
            stopCounter(counterInterval) {
                clearInterval(counterInterval);
            },
            startDiffCounter() {
                if (this.monthStats.diff.type == 'more') {
                    this.startCounter(this.monthStats.diff.partitions);
                } else {
                    this.startCounterDown(this.monthStats.diff.partitions);
                }
            },
            filterTasks(query) {
                this.taskQuery = query;
                this.tasks = this.projectTasks.filter((task) => {
                    return task.content.includes(query);
                });
            },
            addTask(taskContent) {
                this.task = {
                    content: taskContent,
                };
                this.projectTasks.push(this.task);
                this.tasks.push(this.task);
            },
            getSignIndex(startTime) {
                for (let i in this.signs) {
                    if (this.signs[i].started_at === startTime) {
                        return i;
                    }
                }
                return -1;
            },
            updateSign(newSign) {
                let signIndex = this.getSignIndex(newSign.started_at);
                if (signIndex < 0) {
                    return null;
                }
                this.signs.splice(signIndex, 1, newSign);
            },

            checkIfUserCanWorkAnyWhere() {
                this.canWorkAnywhere = auth_user.work_anywhere;
            },
            updateProjectTasks() {
                makeRequest({
                    method: "GET",
                    url: '/tasks?project=' + this.project.id
                }).then(response => {
                    this.projectTasks = response.data.tasks;
                    this.tasks = response.data.tasks;
                });
            },
            mark(content){
                if(!this.taskQuery || !content){
                    return content;
                }
                let startpos = content.indexOf(this.taskQuery);
                let beforeMark = content.substr(0,startpos);
                let afterMark = content.substr(startpos + this.taskQuery.length);
                let toMark = content.substr(startpos, this.taskQuery.length);

                return `${beforeMark}<span class="text-warning">${toMark}</span>${afterMark}`;
            },
            addIdToTask(id){
                this.projectTasks.forEach((task,index) => {
                    if(task.content == this.task.content){
                        this.projectTasks[index].id = id;
                        this.tasks = this.projectTasks;
                        return;
                    }
                });
            }
        },
        watch: {
            'monthStats.diff.partitions.seconds': function () {
                if (this.monthStats.diff.partitions.hours == 0 &&
                    this.monthStats.diff.partitions.minutes == 0 &&
                    this.monthStats.diff.partitions.seconds == 0) {
                    this.monthStats.diff.type = 'more';
                }
            },
            project: function (project, prevProject) {
                if (!this.projectInitialized) {
                    this.projectInitialized = true;
                    return;
                }
                this.task = null;
                this.tasks = [];
                this.projectTasks = [];
                if(project){
                    this.updateProjectTasks();
                }
            },
            task: function (task, prevTask) {
                if (!this.taskInitialized) {
                    this.taskInitialized = true;
                    return;
                }
                if(!task && this.status == 'on'){
                    this.stopWork();
                    return;
                }
                if (this.status == 'on') {
                    this.stopWork().then(() => {
                        this.startWork();
                    });
                }
            }
        }
    }
</script>
