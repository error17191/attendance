// First of All hide the loader
document.querySelector('#loader-container').style.display = 'none';
document.querySelector('html').classList.remove('loader');

// AXIOS and AUTH Stuff
window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

<<<<<<< HEAD
// Fetching and setting token if it exists
if (window.token = localStorage.getItem('token')) {
    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + window.token;
}
// Fetching and setting CSRF token
let token = document.head.querySelector('meta[name="csrf-token"]');
=======
window.Vue = require('vue');

window.bus = new Vue();

window.moment = require('moment');

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
import ChangePassword from './components/ChangePassword';
import Statistics from './components/Statistics';

const routes = [
    {
        path: '/home',
        component: MainBoard,
        name: 'main_board'
    },
    {
        path: '/cpanel',
        component: CPanel,
        name: 'c_panel'
    },
    {
        path: '/change_password',
        component: ChangePassword,
        name: 'change_password'
    },
];

import VueRouter from 'vue-router';

Vue.use(VueRouter);

import Snotify from 'vue-snotify';

Vue.use(Snotify, {
    toast: {
        showProgressBar: false,
        position: 'centerTop'
    }
});

import vSelect from 'vue-select';

Vue.component('v-select', vSelect);

import Multiselect from 'vue-multiselect';

Vue.component('multiselect', Multiselect)


import 'vue-snotify/styles/material.css';

window.router = new VueRouter({
    mode: 'history',
    routes
});

Vue.mixin({
    methods: {
        goToRoute: name => router.push({name: name}),
        currentRouteIs : name => router.currentRoute.name == name,
        auth_user : () => window.auth_user,
        //TODO: remove all functions that i added here from the components
        partitionSeconds: (seconds) => {
            let hours = Math.floor(seconds / 60 / 60);
            seconds -= hours * 60 * 60;
            let minutes = Math.floor(seconds / 60);
            seconds -= minutes * 60;
            return {hours,minutes,seconds};
        },
        setYears: () => {
            let years = [
                {
                    value: null,
                    text: 'year',
                    selected: true,
                    disabled: true
                }
            ];
            let year = 2010;
            while(year <= moment().format('YYYY')){
                years.push({value: year,text: year});
                year++;
            }
            return years;
        },
        setMonths: () => {
            return [
                {value: 1,text: 'January'},
                {value: 2,text: 'february'},
                {value: 3,text: 'March'},
                {value: 4,text: 'April'},
                {value: 5,text: 'May'},
                {value: 6,text: 'June'},
                {value: 7,text: 'July'},
                {value: 8,text: 'August'},
                {value: 9,text: 'September'},
                {value: 10,text: 'October'},
                {value: 11,text: 'November'},
                {value: 12,text: 'December'}
            ];
        }
    }
});


const app = new Vue({
    router,
    data: {
        email: null,
        password: null,
        errors: {
            email: null,
            password: null
        }
    },
    methods: {
        login() {
            axios.post('/login', {
                email: this.email,
                password: this.password
            }).then(response => {
                localStorage.setItem('token', response.data.access_token);
                window.location.href = response.data.url;
            }).catch(error => {
                if(!error.response ||! error.response.data ||  error.response.status != 422){
                    this.$snotify.error('Something went worng');
                    return;
                }
                let responseData = error.response.data;
                if (responseData.status == 'validation_errors') {
                    if (responseData.errors.email) {
                        if (responseData.errors.email.includes('missing')) {
                            this.errors.email = 'Please enter your email';
                        }
                        if (responseData.errors.email.includes('invalid')) {
                            this.errors.email = 'Please enter a valid email';
                        }
                        if (responseData.errors.email.includes('not_found')) {
                            this.errors.email = 'This email doesn\'t exist';
                        }
                    }
                    if (responseData.errors.password) {
                        if (responseData.errors.password.includes('missing')) {
                            this.errors.password = 'Please enter your password';
                        }
                    }
                }

                if(error.response.data.status == 'invalid_login'){
                    this.errors.password = 'The password you entered is incorrect';
                }
>>>>>>> master

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
// Fetching and setting User Object
let userMetaTag = document.head.querySelector('meta[name=user]');

if (userMetaTag) {
    window.auth_user = JSON.parse(userMetaTag.content);
}

window.moment = require('moment');

require('./helpers');
require('./echo_stuff');
require('./vue_stuff');


