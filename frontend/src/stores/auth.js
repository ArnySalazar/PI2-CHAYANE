import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    permissions: [],
    isAuthenticated: false,
  }),

  getters: {
    userName: (state) => state.user?.name || 'Usuario',
    userRole: (state) => state.user?.role_name || 'Sin rol',

    // Verificar si tiene permiso de lectura para un módulo
    canView(state) {
      return (module) => {
        if (!state.permissions || state.permissions.length === 0) return false
        const perm = state.permissions.find((p) => p.module === module)
        return perm ? perm.can_read : false
      }
    },

    // Verificar si tiene permiso de creación
    canCreate(state) {
      return (module) => {
        if (!state.permissions || state.permissions.length === 0) return false
        const perm = state.permissions.find((p) => p.module === module)
        return perm ? perm.can_create : false
      }
    },

    // Verificar si tiene permiso de edición
    canEdit(state) {
      return (module) => {
        if (!state.permissions || state.permissions.length === 0) return false
        const perm = state.permissions.find((p) => p.module === module)
        return perm ? perm.can_edit : false
      }
    },

    // Verificar si tiene permiso de eliminación
    canDelete(state) {
      return (module) => {
        if (!state.permissions || state.permissions.length === 0) return false
        const perm = state.permissions.find((p) => p.module === module)
        return perm ? perm.can_delete : false
      }
    },
  },

  actions: {
    async login(email, password) {
      try {
        console.log('🔐 Intentando login...')
        const response = await api.post('/login', { email, password })

        console.log('📥 Respuesta del backend:', response.data)

        const userData = response.data.user

        // Guardar en el store
        this.user = userData
        this.isAuthenticated = true
        this.permissions = userData.permissions || []

        // Guardar en localStorage
        localStorage.setItem('user', JSON.stringify(userData))
        localStorage.setItem('isAuthenticated', 'true')

        console.log('✅ Login exitoso')
        console.log('✅ Usuario:', this.user)
        console.log('✅ Permisos:', this.permissions)

        return response.data
      } catch (error) {
        console.error('❌ Error en login:', error)
        throw error
      }
    },

    logout() {
      console.log('🚪 Cerrando sesión...')

      this.user = null
      this.isAuthenticated = false
      this.permissions = []

      localStorage.removeItem('user')
      localStorage.removeItem('isAuthenticated')

      console.log('✅ Sesión cerrada')
    },

    // Restaurar sesión desde localStorage
    restoreSession() {
      const userStr = localStorage.getItem('user')
      const isAuth = localStorage.getItem('isAuthenticated')

      console.log('🔄 Restaurando sesión...')

      if (userStr && isAuth === 'true') {
        try {
          this.user = JSON.parse(userStr)
          this.isAuthenticated = true
          this.permissions = this.user.permissions || []

          console.log('✅ Sesión restaurada')
          console.log('✅ Usuario:', this.user)
          console.log('✅ Permisos:', this.permissions)
        } catch (e) {
          console.error('❌ Error al restaurar sesión:', e)
          this.logout()
        }
      } else {
        console.log('⚠️ No hay sesión guardada')
      }
    },
  },
})
