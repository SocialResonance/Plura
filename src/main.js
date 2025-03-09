import Vue from 'vue'
import VueRouter from 'vue-router'
import App from './App.vue'
import Dashboard from './views/Dashboard.vue'
import Proposals from './views/Proposals.vue'
import CreditHistory from './views/CreditHistory.vue'
import { generateUrl } from '@nextcloud/router'

// Load the localization functions
Vue.mixin({ methods: { t, n } })

// Set up the router
Vue.use(VueRouter)

const routes = [
    { path: '/', redirect: '/dashboard' },
    { path: '/dashboard', name: 'dashboard', component: Dashboard },
    { path: '/proposals', name: 'proposals', component: Proposals },
    { path: '/credits', name: 'credits', component: CreditHistory },
]

const router = new VueRouter({
    routes,
    base: generateUrl('/apps/plura/')
})

// Create the Vue instance
const View = Vue.extend(App)
new View({
    router
}).$mount('#plura')
