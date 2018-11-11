// First of All hide the loader
document.querySelector('#loader-container').style.display = 'none';
document.querySelector('html').classList.remove('loader');

// AXIOS and AUTH Stuff
window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Fetching and setting token if it exists
if (window.token = localStorage.getItem('token')) {
    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + window.token;
}
// Fetching and setting CSRF token
let token = document.head.querySelector('meta[name="csrf-token"]');

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


