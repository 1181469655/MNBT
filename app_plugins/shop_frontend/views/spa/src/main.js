import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createVuetify } from 'vuetify';
import App from './App.vue';
import router from './router';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.use(createVuetify({
  theme: { defaultTheme: 'light' },
}));
app.mount('#app');
