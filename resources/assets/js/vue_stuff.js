window.Vue = require('vue');

import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue);

import VueElementLoading from 'vue-element-loading';
Vue.component('VueElementLoading', VueElementLoading);

import 'vue-snotify/styles/material.css';
import Snotify from 'vue-snotify';
Vue.use(Snotify, {
    toast: {
        showProgressBar: false,
        position: 'centerTop'
    }
});

import Multiselect from 'vue-multiselect';
Vue.component('multiselect', Multiselect)


window.bus = new Vue();

Vue.mixin({
    methods: {
        goToRoute: name => router.push({name: name}),
        currentRouteIs: name => router.currentRoute.name == name,
        auth_user: () => window.auth_user,
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

require('./routes');
window.app = new Vue({
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
                if (!error.response || !error.response.data || error.response.status != 422) {
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

                if (error.response.data.status == 'invalid_login') {
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
