<template>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <h2>🍽️ CHAYANE</h2>
        <p>ERP Restaurant</p>
      </div>

      <nav class="menu">
        <router-link to="/dashboard" class="menu-item">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </router-link>

        <router-link to="/productos" class="menu-item">
          <span class="icon">📦</span>
          <span>Productos</span>
        </router-link>

        <router-link to="/ventas" class="menu-item">
          <span class="icon">💵</span>
          <span>Ventas</span>
        </router-link>

        <div class="menu-item disabled">
          <span class="icon">🍽️</span>
          <span>Mesas</span>
        </div>

        <div class="menu-item disabled">
          <span class="icon">👥</span>
          <span>Clientes</span>
        </div>

        <div class="menu-item disabled">
          <span class="icon">📈</span>
          <span>Reportes</span>
        </div>
      </nav>

      <div class="sidebar-footer">
        <div class="user-profile">
          <div class="avatar">{{ userInitial }}</div>
          <div class="user-details">
            <strong>{{ userName }}</strong>
            <small>{{ userRole }}</small>
          </div>
        </div>
        <button @click="logout" class="btn-logout-sidebar" title="Cerrar sesión">🚪</button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <slot></slot>
    </main>
  </div>
</template>

<script>
import authService from '@/services/auth'

export default {
  name: 'LayoutMain',
  data() {
    return {
      userName: '',
      userRole: '',
      userInitial: 'U',
    }
  },
  mounted() {
    this.loadUser()
  },
  methods: {
    loadUser() {
      const user = authService.getUser()
      if (user) {
        this.userName = user.nombre || 'Usuario'
        this.userRole = user.rol || 'Usuario'
        this.userInitial = user.nombre?.charAt(0).toUpperCase() || 'U'
      }
    },
    logout() {
      if (confirm('¿Cerrar sesión?')) {
        authService.logout()
        this.$router.push('/login')
      }
    },
  },
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.logo {
  padding: 30px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.logo h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
}

.logo p {
  margin: 5px 0 0;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 300;
}

.menu {
  flex: 1;
  padding: 20px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px 25px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s;
  cursor: pointer;
  border-left: 3px solid transparent;
}

.menu-item:hover:not(.disabled) {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: #3498db;
}

.menu-item.router-link-active {
  background: rgba(52, 152, 219, 0.2);
  color: white;
  border-left-color: #3498db;
  font-weight: 600;
}

.menu-item.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.menu-item .icon {
  font-size: 20px;
  width: 24px;
  text-align: center;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 18px;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.user-details strong {
  font-size: 14px;
}

.user-details small {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.6);
}

.btn-logout-sidebar {
  background: rgba(231, 76, 60, 0.2);
  border: 1px solid rgba(231, 76, 60, 0.5);
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.3s;
}

.btn-logout-sidebar:hover {
  background: #e74c3c;
  border-color: #e74c3c;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 260px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }

  .sidebar .logo p,
  .menu-item span:not(.icon),
  .user-details {
    display: none;
  }

  .logo h2 {
    font-size: 28px;
  }

  .menu-item {
    justify-content: center;
    padding: 15px;
  }

  .main-content {
    margin-left: 70px;
  }

  .btn-logout-sidebar {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .sidebar {
    width: 60px;
  }

  .main-content {
    margin-left: 60px;
  }
}
</style>
