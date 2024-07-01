import App from './MainApp';
import vuetify from './vuetify';
import router from './router';
import Vue from 'vue';

require('./bootstrap');

Vue.component('main-app', App);

window.Vue = new Vue({
    el: '#app',
    vuetify,
    router
});
