import Vue from 'vue';
import VueRouter from 'vue-router';

var routes = [
    {
        name: 'home',
        path: '',
        component: require('./components/Home.vue').default,
    }
];

Vue.use(VueRouter);

export default new VueRouter({
    routes,
    mode: 'history'
});