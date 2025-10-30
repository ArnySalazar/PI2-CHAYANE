import api from './api'

export default {
  async login(email, password) {
    const response = await api.post('/login', { email, password })

    // Guardar solo el usuario
    localStorage.setItem('user', JSON.stringify(response.data.user))

    return response.data
  },

  logout() {
    localStorage.removeItem('user')
  },

  getUser() {
    const userStr = localStorage.getItem('user')
    return userStr ? JSON.parse(userStr) : null
  },

  isAuthenticated() {
    return !!this.getUser()
  },
}
