import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import App from './App-account.vue'
import router from './account/router'
import './shared/styles/theme.css'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
