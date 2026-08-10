import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import 'tdesign-vue-next/es/style/index.css'
import App from './App-account.vue'
import router from './account/router'
import './shared/styles/theme.scss'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
