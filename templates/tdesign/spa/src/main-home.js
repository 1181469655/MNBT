import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import 'tdesign-vue-next/es/style/index.css'
import App from './App-home.vue'
import router from './home/router'
import './shared/styles/theme.scss'
import './home/styles/home.scss'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
