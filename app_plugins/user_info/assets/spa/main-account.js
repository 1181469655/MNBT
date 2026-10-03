import { createApp } from 'vue'
import TDesign from 'tdesign-vue-next'
import App from './App-account.vue'
import router from './account/router'
// 共享样式在主题侧（spa/src/shared/），经 boot.srcAliases 别名解析
import '@/shared/styles/theme.css'

const app = createApp(App)
app.use(TDesign)
app.use(router)
app.mount('#app')
