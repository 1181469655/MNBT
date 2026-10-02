import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import App from './App-admin.vue'
import router from './admin/router'
import './shared/styles/theme.css'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
