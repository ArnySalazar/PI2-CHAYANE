<template>
  <div class="login-container">
    <div class="login-card">
      <!-- Logo -->
      <div class="logo">
        <h1>CHAYANE</h1>
        <p>La Sazón de Pilar</p>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleLogin" class="login-form">
        <h2>Iniciar Sesión</h2>

        <!-- Email -->
        <div class="form-group">
          <label for="email">Email:</label>
          <input id="email" v-model="email" type="email" placeholder="admin@chayane.com" required />
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input id="password" v-model="password" type="password" placeholder="admin123" required />
        </div>

        <!-- Error message -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- Botón -->
        <button type="submit" :disabled="loading" class="btn-login">
          {{ loading ? 'Iniciando...' : 'Iniciar Sesión' }}
        </button>

        <!-- Usuarios de prueba -->
        <div class="demo-users">
          <p><strong>Usuarios de prueba:</strong></p>
          <p>👑 admin@chayane.com / admin123</p>
          <p>🍽️ mesera@chayane.com / password123</p>
          <p>👨‍🍳 cocinero@chayane.com / password123</p>
          <p>💰 cajera@chayane.com / password123</p>
          <p>📊 gerente@chayane.com / password123</p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'LoginView',
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      email: 'admin@chayane.com',
      password: 'admin123',
      loading: false,
      error: '',
    }
  },
  methods: {
    async handleLogin() {
      if (!this.email || !this.password) {
        this.error = 'Por favor completa todos los campos'
        return
      }

      this.loading = true
      this.error = ''

      try {
        await this.authStore.login(this.email, this.password)

        console.log('Login exitoso')

        // Redirigir al dashboard
        this.$router.push('/dashboard')
      } catch (error) {
        console.error('Error en login:', error)
        this.error = 'Credenciales incorrectas. Verifica tu email y contraseña.'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 400px;
  width: 100%;
}

.logo {
  text-align: center;
  margin-bottom: 30px;
}

.logo h1 {
  margin: 0;
  font-size: 2.5em;
  color: #667eea;
}

.logo p {
  margin: 5px 0 0;
  color: #666;
  font-style: italic;
}

.login-form h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #555;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.btn-login {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 15px;
  text-align: center;
  font-weight: 600;
}

.demo-users {
  margin-top: 25px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 8px;
  font-size: 14px;
  text-align: center;
}

.demo-users p {
  margin: 5px 0;
}

.demo-users strong {
  color: #667eea;
}
</style>
