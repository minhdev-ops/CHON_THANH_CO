import { createApp } from 'vue'
import router from './router'
import AppLayout from './layouts/AppLayout.vue'

document.documentElement.classList.add('js-ready')

const app = createApp(AppLayout)
app.use(router)
app.mount('#app')
