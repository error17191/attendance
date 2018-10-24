// window._ = require('lodash');
// window.Popper = require('popper.js').default;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

// window.$ = window.jQuery = require('jquery');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

if (window.token = localStorage.getItem('token')) {
    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + window.token;
}
let userMetaTag =  document.head.querySelector('meta[name=user]');

if(userMetaTag){
    window.auth_user = JSON.parse(userMetaTag.content);
}




import Echo from 'laravel-echo'

window.io = require('socket.io-client');


window.Echo = new Echo({
    broadcaster: 'socket.io',
    auth:
        {
            headers:
                {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
        },
    host: "http://localhost:" + document.head.querySelector('meta[name=echo-port]').content
});



window.Echo.private('Admin')
    .listen('AdminChannel', (e) => {
        console.log(e.data);
        alert(e.data.data)
    });



/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

window.makeRequest = (params) => {
    if (params.method == 'get' && !params.cache) {
        // params.url = params.url + '?t=' + new Date().getTime();
    }
    return new Promise((resolve) => {
        let promise = axios.request(params)
            .then(resolve)
            .catch((error) => {
                //error.response.status
                // window.location.reload();
            })
    });
};

//window.Instascan = require('instascan');

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });

window.partitionSeconds = function (seconds) {
    let hours = Math.floor(seconds / 60 / 60);
    seconds -= hours * 60 * 60;
    let minutes = Math.floor(seconds / 60);
    seconds -= minutes * 60;
    return {hours,minutes,seconds};
};
