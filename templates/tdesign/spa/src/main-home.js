import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import App from './App-home.vue'
import router from './home/router'
import './shared/styles/theme.css'
import './home/styles/home.css'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
