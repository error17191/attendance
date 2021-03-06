<!DOCTYPE html>
<html class="loader" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth()
        <meta name="user" content="{{json_encode(auth()->user()->toArray()) }}">
    @endauth
    <meta name="echo-port" content="{{config('app.echo_server_port')}}">
    <meta name="url" content="{{config('app.url')}}">
    <title>{{ config('app.name', 'Laravel') }}</title>
{{--    <script src="{{asset('vendor/instascan.min.js')}}"></script>--}}
    <!-- Scripts -->
{{--    <script defer src="{{asset('vendor/fontawesome/js/all.js')}}"></script>--}}
    <script src="{{ mix('js/app.js') }}" defer></script>
    {{--<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js" integrity="sha384-kW+oWsYx3YpxvjtZjFXqazFpA7UP/MbiY4jvs+RWZo2+N94PFZ36T6TFkc9O3qoB" crossorigin="anonymous"></script>--}}
    <!-- Fonts -->
    {{--<link rel="dns-prefetch" href="https://fonts.gstatic.com">--}}
    {{--<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">--}}
    {{--<link rel="manifest" href="/manifest.json">--}}

    <!-- Styles -->
    {{--<link rel="stylesheet" href="https://cdn.rawgit.com/ConnorAtherton/loaders.css/master/loaders.min.css">--}}
    <link href="{{asset('vendor/fontawesome/css/all.css')}}" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="loader-container">
        <div class="loader">
            <div class="ball-grid-pulse">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <div id="app">
        <vue-snotify></vue-snotify>
        <b-navbar toggleable="md" type="light" variant="light">

            <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

            <b-navbar-brand href="#">Attendance</b-navbar-brand>

            <b-collapse is-nav id="nav_collapse">

                <b-navbar-nav>
                    <b-nav-item v-if="auth_user() && auth_user().is_admin &&  ! currentRouteIs('c_panel')" onclick="router.push('/cpanel')">cPanel</b-nav-item>
                    <b-nav-item v-if="!currentRouteIs('main_board')" onclick="router.push('/home')">Home</b-nav-item>
                </b-navbar-nav>

                <!-- Right aligned nav items -->
                <b-navbar-nav class="ml-auto">
                    @auth('web')
                        <b-nav-item-dropdown right>
                            <template slot="button-content">
                                <em>{{auth()->user()->name}}</em>
                            </template>
                            <b-dropdown-item @click="goToRoute('change_password')">
                                Change Password
                            </b-dropdown-item>
                            <b-dropdown-item @click="logout">
                                Logout
                            </b-dropdown-item>

                        </b-nav-item-dropdown>
                    @endauth
                </b-navbar-nav>

            </b-collapse>
        </b-navbar>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
