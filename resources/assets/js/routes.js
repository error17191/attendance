import MainBoard from './components/MainBoard';
import CPanel from './components/CPanel';
import ChangePassword from './components/ChangePassword';

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

window.router = new VueRouter({
    mode: 'history',
    routes
});
