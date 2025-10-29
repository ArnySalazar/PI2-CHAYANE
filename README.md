# 🍽️ CHAYANE - ERP para Restaurante "La Sazón de Pilar"

## 📋 Descripción del Proyecto

Sistema web integral de gestión administrativa (ERP) para el restaurante "La Sazón de Pilar". El sistema digitalizará y automatizará los procesos de control de inventario, gestión financiera, generación de reportes, y visibilidad en línea del menú con sistema de reservas.

---

## 👥 Equipo de Desarrollo

- **Total de desarrolladores:** 4 personas
- **Duración del proyecto:** 12 semanas
- **Horas por persona:** 15 horas/semana

---

## 🛠️ Stack Tecnológico

### Backend
- **Framework:** Laravel 10.x
- **Lenguaje:** PHP 8.1+
- **Base de datos:** PostgreSQL 14+
- **API:** RESTful API

### Frontend
- **Framework:** Vue.js 3.x
- **UI Framework:** Bootstrap 5
- **Build tool:** Vite

### Control de Versiones
- **Sistema:** Git
- **Plataforma:** GitHub
- **Estrategia de branches:** Git Flow

### Infraestructura
- **Servidor:** Servidores UPCH (Universidad Peruana Cayetano Heredia)
- **Entorno de desarrollo:** Local (XAMPP/Laragon/Docker)

---

## 📦 Módulos del Sistema

### 1. 👤 Gestión de Usuarios y Autenticación
- Login/Logout
- Roles: Administrador, Cajero, Mesero
- Permisos por rol

### 2. 📦 Gestión de Inventario
- CRUD de productos/insumos
- Control de stock
- Alertas de stock mínimo
- Categorías de productos
- Historial de movimientos

### 3. 💰 Gestión Financiera
- Registro de ventas diarias
- Registro de gastos/egresos
- Cierre de caja automático
- Cálculo de utilidades
- Control de cuentas por cobrar/pagar

### 4. 📊 Dashboard Ejecutivo
- KPIs en tiempo real
- Ventas del día/semana/mes
- Estado de inventario
- Flujo de caja
- Gráficos interactivos

### 5. 📈 Reportes y Analytics
- Reportes de ventas (diario, semanal, mensual)
- Reportes de inventario
- Reportes financieros
- Productos más vendidos
- Exportación a PDF/Excel

### 6. 🌐 Portal Web Público
- Página principal del restaurante
- Menú online con precios
- Galería de platos
- Información de contacto
- Horarios de atención

### 7. 📅 Sistema de Reservas
- Calendario de reservas
- Gestión de mesas
- Confirmación de reservas
- Notificaciones por email

### 8. 🔔 Sistema de Notificaciones
- Alertas de stock bajo
- Recordatorios de tareas
- Notificaciones de nuevas reservas
- Resumen diario por email

---

## 🗂️ Estructura del Proyecto

```
chayane-erp/
├── backend/                    # API Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── config/
│   └── tests/
│
├── frontend/                   # Aplicación Vue.js
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── router/
│   │   ├── store/
│   │   └── assets/
│   ├── public/
│   └── package.json
│
├── docs/                       # Documentación
│   ├── API.md
│   ├── DATABASE.md
│   └── DEPLOYMENT.md
│
├── .gitignore
├── README.md
└── docker-compose.yml         # Para desarrollo local
```

---

## 🚀 Instalación y Configuración

### Prerrequisitos

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- PostgreSQL >= 14
- Git

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/chayane-erp.git
cd chayane-erp
```

### 2. Configurar Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configurar `.env` con credenciales de PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chayane_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

Ejecutar migraciones:
```bash
php artisan migrate --seed
php artisan serve
```

### 3. Configurar Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```

### 4. Acceder al sistema

- **Backend API:** http://localhost:8000
- **Frontend:** http://localhost:5173

---

## 👨‍💻 Guía de Contribución

### Estrategia de Branches

```
main (producción)
├── develop (desarrollo)
│   ├── feature/modulo-inventario
│   ├── feature/modulo-ventas
│   ├── feature/modulo-reportes
│   └── feature/modulo-reservas
```

### Flujo de Trabajo

1. **Crear una rama desde develop:**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/nombre-del-modulo
   ```

2. **Trabajar en tu módulo:**
   ```bash
   git add .
   git commit -m "feat: descripción del cambio"
   ```

3. **Subir cambios:**
   ```bash
   git push origin feature/nombre-del-modulo
   ```

4. **Crear Pull Request** en GitHub hacia `develop`

### Convenciones de Commits

- `feat:` Nueva funcionalidad
- `fix:` Corrección de bug
- `docs:` Documentación
- `style:` Formato de código
- `refactor:` Refactorización
- `test:` Tests
- `chore:` Tareas de mantenimiento

### Estándares de Código

#### PHP (PSR-12)
```php
<?php

namespace App\Http\Controllers;

class ProductoController extends Controller
{
    public function index()
    {
        // Código aquí
    }
}
```

#### JavaScript (ESLint)
```javascript
export default {
  name: 'ProductosList',
  data() {
    return {
      productos: []
    }
  },
  methods: {
    async fetchProductos() {
      // Código aquí
    }
  }
}
```

---

## 🗄️ Base de Datos

### Tablas Principales

- `users` - Usuarios del sistema
- `roles` - Roles de usuario
- `productos` - Productos/insumos
- `categorias` - Categorías de productos
- `inventario` - Control de stock
- `ventas` - Registro de ventas
- `detalle_ventas` - Detalles de cada venta
- `gastos` - Registro de gastos
- `cajas` - Cierres de caja
- `reservas` - Reservas de mesas
- `mesas` - Mesas del restaurante
- `notificaciones` - Sistema de notificaciones

Ver diagrama completo en `docs/DATABASE.md`

---

## 🧪 Testing

### Backend (PHPUnit)
```bash
cd backend
php artisan test
```

### Frontend (Vitest)
```bash
cd frontend
npm run test
```

---

## 📝 Asignación de Módulos por Desarrollador

### Desarrollador 1: Inventario + Productos
- CRUD de productos
- Control de stock
- Categorías
- Alertas de stock mínimo

### Desarrollador 2: Ventas + Finanzas
- Registro de ventas
- Registro de gastos
- Cierre de caja
- Reportes financieros

### Desarrollador 3: Dashboard + Reportes
- Dashboard ejecutivo
- Gráficos y KPIs
- Generación de reportes
- Exportación PDF/Excel

### Desarrollador 4: Web Pública + Reservas
- Portal web público
- Sistema de reservas
- Gestión de mesas
- Notificaciones

---

## 📅 Cronograma (12 semanas)

| Semana | Actividad |
|--------|-----------|
| 1-2 | Setup del proyecto y configuración de entornos |
| 3-4 | Módulo de Autenticación + Inventario |
| 5-6 | Módulo de Ventas + Finanzas |
| 7-8 | Dashboard + Reportes |
| 9-10 | Web Pública + Reservas |
| 11 | Integración y Testing |
| 12 | Deploy y Documentación final |

---

## 🔐 Credenciales por Defecto (Desarrollo)

**Administrador:**
- Usuario: `admin@chayane.com`
- Password: `admin123`

**Cajero:**
- Usuario: `cajero@chayane.com`
- Password: `cajero123`

**Mesero:**
- Usuario: `mesero@chayane.com`
- Password: `mesero123`

> ⚠️ **IMPORTANTE:** Cambiar estas credenciales en producción

---

## 📞 Contacto y Soporte

- **Repositorio:** https://github.com/tu-usuario/chayane-erp
- **Issues:** https://github.com/tu-usuario/chayane-erp/issues
- **Wiki:** https://github.com/tu-usuario/chayane-erp/wiki

---

## 📄 Licencia

Este proyecto es desarrollado como parte del curso de Gestión de Proyectos - UPCH

---

## ✅ Checklist de Inicio

- [ ] Clonar repositorio
- [ ] Instalar dependencias backend
- [ ] Instalar dependencias frontend
- [ ] Configurar base de datos PostgreSQL
- [ ] Ejecutar migraciones
- [ ] Verificar que backend corre en localhost:8000
- [ ] Verificar que frontend corre en localhost:5173
- [ ] Crear tu branch de feature
- [ ] Leer documentación de tu módulo asignado

---

**¡Bienvenido al equipo CHAYANE! 🚀**
