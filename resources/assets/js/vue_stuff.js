window.Vue = require('vue');
window.bus = new Vue();


loadScript('/js/bootstrap-vue.js',() => {
    Vue.use(BootstrapVue);
    bus.$emit('bootstrapVueLoaded');
});

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



Vue.mixin({
    mounted(){
        bus.$on('bootstrapVueLoaded', () => {
            this.bootstrapVueLoaded = true;
        });
    },
    methods: {
        goToRoute: name => router.push({name: name}),
        currentRouteIs: name => router.currentRoute.name == name,
        auth_user: () => window.auth_user,
    },
    data: function () {
        return {
            bootstrapVueLoaded: false,
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
