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
    host: document.head.querySelector('meta[name=url]').content + ':' + document.head.querySelector('meta[name=echo-port]').content
});

if (window.auth_user) {
    window.Echo.private(`App.User.${auth_user.id}`)
        .listen('FlagTimeExpired', (e) => {
        });
}

