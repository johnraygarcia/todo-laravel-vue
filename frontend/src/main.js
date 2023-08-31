import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import { createPinia } from 'pinia'
import { loadFonts } from './plugins/webfontloader'
import axios from 'axios'

loadFonts()
const pinia = createPinia();
axios.defaults.baseURL = 'http://localhost:80'
axios.interceptors.request.use(config => {
  const token = localStorage.getItem("access_token");
  config.headers["Authorization"] = `Bearer ${token}`;
  return config;
});

createApp(App)
  .use(router)
  .use(vuetify)
  .use(pinia)
  .mount('#app')
