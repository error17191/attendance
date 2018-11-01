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
        partitionSeconds: (seconds) => {
            let hours = Math.floor(seconds / 60 / 60);
            seconds -= hours * 60 * 60;
            let minutes = Math.floor(seconds / 60);
            seconds -= minutes * 60;
            return {hours,minutes,seconds};
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

            });
        },
        logout() {
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

if(window.auth_user){
    window.Echo.private(`App.User.${auth_user.id}`)
        .listen('FlagTimeExpired', (e) => {
        });
}

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
