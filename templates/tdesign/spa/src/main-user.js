import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import App from './App-user.vue'
import router from './user/router/index.js'
import './shared/styles/theme.css'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
