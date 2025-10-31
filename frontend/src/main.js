import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './plugins/echo'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Solicitar permiso para notificaciones del navegador
if ('Notification' in window && Notification.permission === 'default') {
  Notification.requestPermission()
}

app.mount('#app')
