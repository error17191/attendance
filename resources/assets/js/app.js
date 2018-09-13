
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import BootstrapVue from 'bootstrap-vue';
window.Vue = require('vue');

Vue.use(BootstrapVue);

import VueElementLoading from 'vue-element-loading';

Vue.component('VueElementLoading', VueElementLoading);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import MainBoard from './components/MainBoard';
import CPanel from './components/CPanel';
import Vacations from './components/Vacations';

const routes = [
    {
        path: '/home',
        component: MainBoard,
        name: 'main_board'
    },
    {
        path: '/cpanel',
        component : CPanel,
        name: 'c_panel'
    },
];

import VueRouter from 'vue-router';
Vue.use(VueRouter);

window.router = new VueRouter({
    mode: 'history',
    routes
});


const app = new Vue({
    router,
    methods: {
        isCPanel(){
            return router.currentRoute.name == 'c_panel';
        },
        isMainBoard(){
            return router.currentRoute.name == 'main_board';
        },
    }
}).$mount('#app');
