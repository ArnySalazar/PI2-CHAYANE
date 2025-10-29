# 🛠️ GUÍA DE CONFIGURACIÓN DEL ENTORNO DE DESARROLLO

Esta guía te ayudará a configurar tu entorno local para trabajar en el proyecto CHAYANE.

---

## 📋 ÍNDICE

1. [Instalación de Herramientas](#1-instalación-de-herramientas)
2. [Configuración de PostgreSQL](#2-configuración-de-postgresql)
3. [Configuración del Backend](#3-configuración-del-backend)
4. [Configuración del Frontend](#4-configuración-del-frontend)
5. [Configuración de Git](#5-configuración-de-git)
6. [Verificación del Entorno](#6-verificación-del-entorno)
7. [Problemas Comunes](#7-problemas-comunes)

---

## 1. INSTALACIÓN DE HERRAMIENTAS

### Windows

#### 1.1 Instalar PHP y Composer

**Opción A: XAMPP (Recomendado para principiantes)**
- Descargar XAMPP: https://www.apachefriends.org/
- Instalar con PHP 8.1 o superior
- Agregar PHP al PATH del sistema

**Opción B: Laragon (Recomendado)**
- Descargar Laragon: https://laragon.org/download/
- Incluye PHP, Composer, Node.js y más
- Configuración automática

**Composer (si no viene incluido):**
- Descargar: https://getcomposer.org/download/
- Ejecutar el instalador
- Verificar: `composer --version`

#### 1.2 Instalar Node.js

- Descargar Node.js LTS: https://nodejs.org/
- Versión recomendada: 18.x o superior
- Verificar instalación:
  ```bash
  node --version
  npm --version
  ```

#### 1.3 Instalar PostgreSQL

- Descargar PostgreSQL 14+: https://www.postgresql.org/download/windows/
- Durante instalación, anotar:
  - Usuario: `postgres`
  - Password: (tu contraseña)
  - Puerto: `5432`
- Instalar pgAdmin 4 (viene incluido)

#### 1.4 Instalar Git

- Descargar Git: https://git-scm.com/download/win
- Usar configuración por defecto
- Verificar: `git --version`

---

### macOS

#### 1.1 Instalar Homebrew (si no lo tienes)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

#### 1.2 Instalar PHP y Composer
```bash
brew install php@8.1
brew install composer
```

#### 1.3 Instalar Node.js
```bash
brew install node@18
```

#### 1.4 Instalar PostgreSQL
```bash
brew install postgresql@14
brew services start postgresql@14
```

#### 1.5 Instalar Git
```bash
brew install git
```

---

### Linux (Ubuntu/Debian)

#### 1.1 Actualizar el sistema
```bash
sudo apt update
sudo apt upgrade -y
```

#### 1.2 Instalar PHP
```bash
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-mbstring \
  php8.1-xml php8.1-pgsql php8.1-curl php8.1-zip -y
```

#### 1.3 Instalar Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 1.4 Instalar Node.js
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

#### 1.5 Instalar PostgreSQL
```bash
sudo apt install postgresql postgresql-contrib -y
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

#### 1.6 Instalar Git
```bash
sudo apt install git -y
```

---

## 2. CONFIGURACIÓN DE POSTGRESQL

### 2.1 Crear usuario y base de datos

**Windows (pgAdmin):**
1. Abrir pgAdmin 4
2. Conectar a PostgreSQL
3. Click derecho en "Databases" → Create → Database
4. Nombre: `chayane_db`
5. Owner: `postgres`

**macOS/Linux (Terminal):**
```bash
# Entrar a PostgreSQL
sudo -u postgres psql

# Crear base de datos
CREATE DATABASE chayane_db;

# Crear usuario (opcional)
CREATE USER chayane_user WITH PASSWORD 'chayane123';

# Dar permisos
GRANT ALL PRIVILEGES ON DATABASE chayane_db TO chayane_user;

# Salir
\q
```

### 2.2 Verificar conexión
```bash
psql -U postgres -d chayane_db
# Si funciona, escribir \q para salir
```

---

## 3. CONFIGURACIÓN DEL BACKEND

### 3.1 Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/chayane-erp.git
cd chayane-erp/backend
```

### 3.2 Instalar dependencias
```bash
composer install
```

### 3.3 Configurar variables de entorno
```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 3.4 Editar archivo .env

Abrir `backend/.env` y configurar:

```env
APP_NAME=CHAYANE
APP_ENV=local
APP_KEY=base64:XXXXXXXXXXX (generado automáticamente)
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chayane_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password_aqui

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@chayane.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3.5 Ejecutar migraciones
```bash
# Crear tablas en la base de datos
php artisan migrate

# Insertar datos de prueba
php artisan db:seed
```

### 3.6 Iniciar servidor de desarrollo
```bash
php artisan serve
```

Abrir navegador en: http://localhost:8000

---

## 4. CONFIGURACIÓN DEL FRONTEND

### 4.1 Ir a la carpeta frontend
```bash
cd ../frontend
```

### 4.2 Instalar dependencias
```bash
npm install
```

### 4.3 Configurar variables de entorno

Crear archivo `frontend/.env.local`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=CHAYANE
```

### 4.4 Iniciar servidor de desarrollo
```bash
npm run dev
```

Abrir navegador en: http://localhost:5173

---

## 5. CONFIGURACIÓN DE GIT

### 5.1 Configurar tu identidad
```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tu.email@ejemplo.com"
```

### 5.2 Conectar con GitHub

**Opción A: HTTPS**
```bash
git remote add origin https://github.com/tu-usuario/chayane-erp.git
```

**Opción B: SSH (más seguro)**
```bash
# Generar clave SSH
ssh-keygen -t ed25519 -C "tu.email@ejemplo.com"

# Copiar clave pública
cat ~/.ssh/id_ed25519.pub

# Agregar en GitHub: Settings → SSH Keys → New SSH key

# Conectar repositorio
git remote add origin git@github.com:tu-usuario/chayane-erp.git
```

### 5.3 Crear tu branch de trabajo
```bash
git checkout develop
git checkout -b feature/mi-modulo
```

---

## 6. VERIFICACIÓN DEL ENTORNO

### Checklist de verificación:

```bash
# 1. Verificar PHP
php --version
# Debe mostrar: PHP 8.1.x o superior

# 2. Verificar Composer
composer --version
# Debe mostrar: Composer version 2.x

# 3. Verificar Node.js
node --version
# Debe mostrar: v18.x o superior

# 4. Verificar npm
npm --version
# Debe mostrar: 9.x o superior

# 5. Verificar PostgreSQL
psql --version
# Debe mostrar: psql (PostgreSQL) 14.x

# 6. Verificar Git
git --version
# Debe mostrar: git version 2.x

# 7. Verificar conexión a base de datos
cd backend
php artisan migrate:status
# Debe mostrar lista de migraciones

# 8. Verificar backend
curl http://localhost:8000/api/health
# Debe devolver: {"status":"ok"}

# 9. Verificar frontend
curl http://localhost:5173
# Debe devolver HTML
```

---

## 7. PROBLEMAS COMUNES

### Backend no inicia

**Problema:** Error "Address already in use"
```bash
# Solución: Cambiar puerto
php artisan serve --port=8001
```

**Problema:** Error de conexión a PostgreSQL
```bash
# Verificar que PostgreSQL esté corriendo
# Windows:
sc query postgresql-x64-14

# macOS:
brew services list

# Linux:
sudo systemctl status postgresql

# Si no está corriendo, iniciar:
# macOS:
brew services start postgresql

# Linux:
sudo systemctl start postgresql
```

### Frontend no inicia

**Problema:** `npm install` falla
```bash
# Limpiar cache y reinstalar
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

**Problema:** Error de permisos en Windows
```bash
# Ejecutar terminal como Administrador
# O usar:
npm install --no-optional
```

### Git

**Problema:** Permission denied al hacer push
```bash
# Verificar remote
git remote -v

# Si usa HTTPS, pedirá usuario/password
# Si usa SSH, verificar clave SSH:
ssh -T git@github.com
```

### PostgreSQL

**Problema:** Password authentication failed
```bash
# Resetear password de postgres
# Windows: usar pgAdmin
# Linux:
sudo -u postgres psql
ALTER USER postgres PASSWORD 'nueva_password';
```

---

## 🆘 SOPORTE

Si tienes problemas:

1. Busca en Issues del repositorio
2. Pregunta en el grupo del equipo
3. Consulta la documentación oficial:
   - Laravel: https://laravel.com/docs
   - Vue.js: https://vuejs.org/guide/
   - PostgreSQL: https://www.postgresql.org/docs/

---

## ✅ ¡LISTO!

Si todos los checks están en verde, tu entorno está listo para desarrollar. 

**Siguiente paso:** Lee el README.md principal y la documentación de tu módulo asignado.

¡Feliz codificación! 🚀
