\# 🗃️ Backup de Base de Datos - Chayane ERP



\## 📦 Contenido

Base de datos completa del sistema Chayane ERP:

\- ✅ 6 Productos del menú

\- ✅ 76 Insumos con stock

\- ✅ Categorías organizadas

\- ✅ 7 Usuarios del sistema

\- ✅ Roles y permisos configurados



\## 🔧 Instrucciones para Restaurar



\### 1️⃣ Crear la base de datos (si no existe)

Abre \*\*pgAdmin4\*\* o \*\*psql\*\* y ejecuta:

```sql

CREATE DATABASE chayane\_db;

```



\### 2️⃣ Restaurar el backup

Abre PowerShell en la carpeta del proyecto y ejecuta:

```powershell

cd C:\\Users\\\[TU\_USUARIO]\\Documents\\chayane-erp

pg\_restore -U postgres -d chayane\_db -c -v database-backup-final/chayane\_db\_backup.dump

```



Cuando pida contraseña, usa: `postgres`



\### 3️⃣ Configurar backend/.env

Asegúrate de tener esto en tu archivo `.env`:

```env

DB\_CONNECTION=pgsql

DB\_HOST=127.0.0.1

DB\_PORT=5432

DB\_DATABASE=chayane\_db

DB\_USERNAME=postgres

DB\_PASSWORD=postgres

```



\### 4️⃣ Limpiar caché

```powershell

cd backend

php artisan config:clear

php artisan cache:clear

```



\### 5️⃣ Iniciar el sistema

```powershell

\# Terminal 1 - Backend

cd backend

php artisan serve



\# Terminal 2 - Frontend  

cd frontend

npm run dev

```



\## 👤 Credenciales de Login



\*\*Administrador:\*\*

\- Email: `admin@chayane.com`

\- Password: `admin123`



\*\*Otros usuarios disponibles:\*\*

\- casero@chayane.com / casero123

\- minero@chayane.com / minero123



\## ⚠️ Solución de Problemas



\*\*Error: "database chayane\_db does not exist"\*\*

→ Crea primero la BD con `CREATE DATABASE chayane\_db;`



\*\*Error: "role postgres does not exist"\*\*  

→ Cambia `-U postgres` por tu usuario de PostgreSQL



\*\*Error de conexión en el frontend\*\*

→ Verifica que el backend esté corriendo en `http://localhost:8000`



\## 📊 Datos Incluidos



\- \*\*Productos:\*\* Arroz con Pollo, Causa Acevichada, Lomo Saltado, etc.

\- \*\*Insumos:\*\* Arroz, Pollo, Verduras, Condimentos, etc.

\- \*\*Valor Total Inventario:\*\* S/ 2,459.50

