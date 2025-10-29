# Backend - CHAYANE ERP (Laravel)

## Instalación

```bash
# Instalar dependencias
composer install

# Copiar archivo de configuración
cp .env.example .env

# Generar key de aplicación
php artisan key:generate

# Configurar base de datos en .env
# Editar DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Ejecutar migraciones
php artisan migrate --seed

# Iniciar servidor
php artisan serve
```

## URL de desarrollo
http://localhost:8000

## Estructura de carpetas (se creará con Laravel)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/     ← Controladores aquí
│   │   └── Middleware/
│   ├── Models/              ← Modelos aquí
│   └── Services/            ← Lógica de negocio
├── database/
│   ├── migrations/          ← Migraciones
│   └── seeders/             ← Datos iniciales
├── routes/
│   ├── api.php              ← Rutas de API
│   └── web.php
└── tests/
```

## Instalación de Laravel (Primera vez)

Si esta carpeta está vacía, instalar Laravel:

```bash
cd backend
composer create-project laravel/laravel .
```

## Credenciales de prueba

- Admin: admin@chayane.com / admin123
- Cajero: cajero@chayane.com / admin123
- Mesero: mesero@chayane.com / admin123
