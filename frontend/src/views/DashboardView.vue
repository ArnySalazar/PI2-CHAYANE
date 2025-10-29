<template>
  <div class="dashboard">
    <header>
      <h1>Dashboard CHAYANE</h1>
      <div class="user-info">
        <span>👤 {{ user?.nombre }} ({{ user?.rol }})</span>
        <button @click="handleLogout" class="btn-logout">Cerrar Sesión</button>
      </div>
    </header>

    <div class="dashboard-content">
      <h2>¡Bienvenido al Sistema!</h2>
      <p>Has iniciado sesión correctamente.</p>

      <div class="user-card">
        <h3>Información del Usuario:</h3>
        <p><strong>Nombre:</strong> {{ user?.nombre }}</p>
        <p><strong>Email:</strong> {{ user?.email }}</p>
        <p><strong>Rol:</strong> {{ user?.rol }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import authService from '@/services/auth'

export default {
  name: 'DashboardView',
  data() {
    return {
      user: null,
    }
  },
  created() {
    this.user = authService.getUser()

    // Si no hay usuario, redirigir al login
    if (!this.user) {
      this.$router.push('/login')
    }
  },
  methods: {
    handleLogout() {
      authService.logout()
      this.$router.push('/login')
    },
  },
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f5f5f5;
}

header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

header h1 {
  margin: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.btn-logout {
  background: white;
  color: #667eea;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.btn-logout:hover {
  background: #f0f0f0;
}

.dashboard-content {
  padding: 40px;
  max-width: 800px;
  margin: 0 auto;
}

.user-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-top: 30px;
}

.user-card h3 {
  margin-top: 0;
  color: #667eea;
}

.user-card p {
  margin: 10px 0;
  font-size: 16px;
}
</style>
