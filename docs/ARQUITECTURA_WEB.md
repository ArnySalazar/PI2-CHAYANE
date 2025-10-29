# Vue.js como PWA (Progressive Web App)

## Configuración para acceso web 100%

### vite.config.js

```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'logo.png'],
      manifest: {
        name: 'CHAYANE - La Sazón de Pilar',
        short_name: 'CHAYANE',
        description: 'Sistema de gestión para restaurante',
        theme_color: '#FF6B35',
        background_color: '#ffffff',
        display: 'standalone',
        icons: [
          {
            src: '/logo-192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/logo-512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      },
      workbox: {
        // Cache para funcionamiento offline (opcional)
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/api\.chayane\.com\/.*/i,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'api-cache',
              expiration: {
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 // 24 horas
              }
            }
          }
        ]
      }
    })
  ],
  build: {
    // Optimizaciones para producción
    minify: 'terser',
    sourcemap: false,
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['vue', 'vue-router', 'pinia'],
          'bootstrap': ['bootstrap']
        }
      }
    }
  }
})
```

### package.json (actualizado)

```json
{
  "name": "chayane-frontend",
  "version": "1.0.0",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "dependencies": {
    "vue": "^3.3.4",
    "vue-router": "^4.2.4",
    "pinia": "^2.1.6",
    "axios": "^1.5.0",
    "bootstrap": "^5.3.2"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^4.3.4",
    "vite": "^4.4.9",
    "vite-plugin-pwa": "^0.16.5"
  }
}
```

---

## 📱 Responsive Design (Mobile-First)

### src/assets/css/responsive.css

```css
/* Base - Mobile First */
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: 'Inter', -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
}

/* Contenedor principal */
.app-container {
  display: flex;
  min-height: 100vh;
}

/* Sidebar - Oculto en mobile */
.sidebar {
  width: 240px;
  background: white;
  box-shadow: 2px 0 8px rgba(0,0,0,0.1);
  position: fixed;
  left: -240px;
  transition: left 0.3s;
  z-index: 1000;
}

.sidebar.open {
  left: 0;
}

/* Contenido principal */
.main-content {
  flex: 1;
  padding: 20px;
  margin-left: 0;
  transition: margin-left 0.3s;
}

/* Tablet (768px+) */
@media (min-width: 768px) {
  .sidebar {
    left: 0;
  }
  
  .main-content {
    margin-left: 240px;
  }
  
  .mobile-menu-toggle {
    display: none;
  }
}

/* Desktop (1024px+) */
@media (min-width: 1024px) {
  .main-content {
    padding: 30px;
  }
  
  .kpis-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Mobile */
@media (max-width: 767px) {
  .kpis-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .card {
    padding: 15px;
  }
  
  h1 {
    font-size: 24px;
  }
}
```

---

## 🔐 Autenticación Web

### src/services/auth.service.js

```javascript
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL

class AuthService {
  async login(email, password) {
    const response = await axios.post(`${API_URL}/auth/login`, {
      email,
      password
    })
    
    if (response.data.token) {
      // Guardar token en localStorage
      localStorage.setItem('user', JSON.stringify(response.data))
    }
    
    return response.data
  }
  
  logout() {
    localStorage.removeItem('user')
    window.location.href = '/login'
  }
  
  getCurrentUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  }
  
  isAuthenticated() {
    return !!this.getCurrentUser()
  }
}

export default new AuthService()
```

---

## 🌐 Acceso desde múltiples dispositivos

### Escenarios de uso:

**Administrador (Desktop):**
- Accede desde PC en la oficina
- URL: https://chayane.upch.edu.pe
- Vista completa con todas las funcionalidades
- Puede imprimir reportes, gestionar todo el sistema

**Cajero (Tablet):**
- Accede desde tablet en caja
- Misma URL: https://chayane.upch.edu.pe
- Vista optimizada para pantalla táctil
- Enfocado en: Ventas, Caja, Productos

**Mesero (Celular):**
- Accede desde su celular personal
- Misma URL: https://chayane.upch.edu.pe
- Vista mobile optimizada
- Enfocado en: Mesas, Pedidos

**Cliente (Cualquier dispositivo):**
- Accede a portal público
- URL: https://chayane.upch.edu.pe/menu
- Ve menú, hace reservas
- No requiere login

---

## ⚡ Optimizaciones para carga rápida

### 1. Lazy Loading de rutas

```javascript
// router/index.js
const routes = [
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/Dashboard/DashboardView.vue')
      },
      {
        path: 'inventario',
        component: () => import('@/views/Inventario/InventarioView.vue')
      },
      // Cada módulo se carga solo cuando se necesita
    ]
  }
]
```

### 2. Cache de API

```javascript
// src/plugins/axios.js
import axios from 'axios'

const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 10000
})

// Interceptor para agregar token
instance.interceptors.request.use(config => {
  const user = JSON.parse(localStorage.getItem('user'))
  if (user?.token) {
    config.headers.Authorization = `Bearer ${user.token}`
  }
  return config
})

export default instance
```

---

## 📊 Offline First (Opcional)

Si quieres que funcione incluso sin internet:

```javascript
// Service Worker para cache
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request)
    })
  )
})
```

---

## ✅ Ventajas de esta arquitectura

✅ **Sin instalación** - Solo abrir el navegador
✅ **Actualizaciones automáticas** - Al recargar tiene la última versión
✅ **Multiplataforma** - Windows, Mac, Linux, Android, iOS
✅ **Un solo código** - Mismo sistema para todos
✅ **Seguro** - HTTPS + autenticación
✅ **Económico** - No necesita app stores
✅ **Mantenible** - Cambios se reflejan inmediatamente
