/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
document.querySelector('#loader-container').style.display = 'none';
document.querySelector('html').classList.remove('loader');

require('./bootstrap');

import BootstrapVue from 'bootstrap-vue';

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


const app = new Vue({
    router,
    data: {
        email: null,
        password: null
    },
    methods: {
        isCPanel() {
            return router.currentRoute.name == 'c_panel';
        },
        isMainBoard() {
            return router.currentRoute.name == 'main_board';
        },
        login(){
            axios.post('/login',{
                email : this.email,
                password: this.password
            }).then(response => {
                console.log(response.data.user);
                localStorage.setItem('token', response.data.access_token);
                localStorage.setItem('auth_user',JSON.stringify(response.data.user));
                window.location.href = response.data.url;
            });
        },
        logout(){
            axios.post('/logout').then(response => {
                localStorage.removeItem('token');
                localStorage.removeItem('auth_user');
                window.location.href = response.data.url;
            });
        }
    }
}).$mount('#app');




import moment from 'moment';
window.moment = moment;
// import firebase from 'firebase/app';
//
// require('firebase/messaging');
//
// var config = {
//     apiKey: "AIzaSyBEqP7_rgdd23STADNVz8vMafo3e1eKfRM",
//     authDomain: "attendance-16307.firebaseapp.com",
//     databaseURL: "https://attendance-16307.firebaseio.com",
//     projectId: "attendance-16307",
//     storageBucket: "attendance-16307.appspot.com",
//     messagingSenderId: "480654102977"
// };
//
// firebase.initializeApp(config);
//
// // Retrieve Firebase Messaging object.
// const messaging = firebase.messaging();
//
// messaging.usePublicVapidKey("BP-G7rFXqXNDHVsZjYwt_Fc_E5JJVJX-EdelNULHfnMNZpXM300jehcuPaTuWeP3yXmim0n_VxYs7vB3cqqgI3Q");
//
// messaging.requestPermission().then(function() {
//     console.log('Notification permission granted.');
//     // TODO(developer): Retrieve an Instance ID token for use with FCM.
//     // ...
//     messaging.getToken().then(function(currentToken) {
//         if (currentToken) {
//             axios.post('/browser/token',{token: currentToken})
//                 .then((response) => {
//                     console.log(response.data.status);
//                 });
//         }
//     });
//
// }).catch(function(err) {
//     console.log('Unable to get permission to notify.', err);
// });
//
// messaging.onMessage(function(payload) {
//     console.log('Message received. ', payload);
// });
//
