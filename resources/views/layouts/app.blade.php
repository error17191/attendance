<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link rel="manifest" href="/manifest.json">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>

    <div id="app">
        <vue-snotify></vue-snotify>
        <b-navbar toggleable="md" type="light" variant="light">

            <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

            <b-navbar-brand href="#">Attendance</b-navbar-brand>

            <b-collapse is-nav id="nav_collapse">

                <b-navbar-nav>
                    <b-nav-item v-if="isMainBoard()" onclick="router.push('/cpanel')">cPanel</b-nav-item>
                    <b-nav-item v-if="isCPanel()" onclick="router.push('/home')">Home</b-nav-item>
                </b-navbar-nav>

                <!-- Right aligned nav items -->
                <b-navbar-nav class="ml-auto">
                    @guest
                    <b-nav-item href="#">cPanel</b-nav-item>
                    @else
                        <b-nav-item-dropdown right>
                            <template slot="button-content">
                                <em>{{auth()->user()->name}}</em>
                            </template>
                            <b-dropdown-item href="{{ route('logout') }}"
                             onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();"
                            >
                                Logout
                            </b-dropdown-item>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>

                        </b-nav-item-dropdown>
                    @endguest
                </b-navbar-nav>

            </b-collapse>
        </b-navbar>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <script src="https://www.gstatic.com/firebasejs/5.4.1/firebase-app.js"></script>

    <!-- Add additional services that you want to use -->
    <script src="https://www.gstatic.com/firebasejs/5.4.1/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.4.1/firebase-database.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.4.1/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.4.1/firebase-messaging.js"></script>
    <script src="https://www.gstatic.com/firebasejs/5.4.1/firebase-functions.js"></script>
    <script>
        // Initialize Firebase
        var config = {
            apiKey: "AIzaSyBEqP7_rgdd23STADNVz8vMafo3e1eKfRM",
            authDomain: "attendance-16307.firebaseapp.com",
            databaseURL: "https://attendance-16307.firebaseio.com",
            projectId: "attendance-16307",
            storageBucket: "attendance-16307.appspot.com",
            messagingSenderId: "480654102977"
        };
        firebase.initializeApp(config);

        // Retrieve Firebase Messaging object.
        const messaging = firebase.messaging();

        messaging.usePublicVapidKey("BP-G7rFXqXNDHVsZjYwt_Fc_E5JJVJX-EdelNULHfnMNZpXM300jehcuPaTuWeP3yXmim0n_VxYs7vB3cqqgI3Q");

        messaging.requestPermission().then(function() {
            console.log('Notification permission granted.');
            // TODO(developer): Retrieve an Instance ID token for use with FCM.
            // ...
            messaging.getToken().then(function(currentToken) {
                console.log(currentToken)
                if (currentToken) {
                    console.log(currentToken);
                }
            });

        }).catch(function(err) {
            console.log('Unable to get permission to notify.', err);
        });

        messaging.onMessage(function(payload) {
            console.log('Message received. ', payload);
        });

    </script>
</body>
</html>
