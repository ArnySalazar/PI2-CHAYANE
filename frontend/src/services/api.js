import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// Interceptor para agregar el user ID en cada request
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    const user = authStore.user

    // Agregar el ID del usuario autenticado en el header
    if (user && user.id) {
      config.headers['X-User-Id'] = user.id
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Interceptor para manejar errores de permisos
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 403) {
      const message = error.response.data?.message || 'No tienes permisos para realizar esta acción'

      // Mostrar alerta al usuario
      alert('⚠️ Permiso Denegado\n\n' + message)

      return Promise.reject(error)
    }

    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
    }

    return Promise.reject(error)
  },
)

export default api
