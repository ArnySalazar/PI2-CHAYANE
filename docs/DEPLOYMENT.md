# 🚀 GUÍA DE DEPLOYMENT - ACCESO WEB 100%

## Cómo publicar CHAYANE para acceso desde cualquier dispositivo

---

## 🌐 OPCIÓN 1: SERVIDOR UPCH (Recomendado)

### **URL final:** `https://chayane.upch.edu.pe`

### Requisitos del servidor:
- ✅ Linux (Ubuntu 20.04+)
- ✅ PHP 8.1+
- ✅ PostgreSQL 14+
- ✅ Nginx o Apache
- ✅ SSL/HTTPS (Let's Encrypt)
- ✅ Node.js (solo para compilar)

---

## 📋 PASO 1: PREPARAR EL PROYECTO

### En tu computadora local:

```bash
# 1. Backend - Laravel
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Frontend - Vue.js
cd ../frontend
npm install
npm run build
# Esto genera carpeta dist/ con archivos estáticos
```

---

## 📋 PASO 2: ESTRUCTURA EN EL SERVIDOR

```
/var/www/chayane/
├── backend/              ← Código Laravel
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── .env             ← Configuración producción
│   └── public/          ← Punto de entrada API
│
└── frontend/            ← Build de Vue.js
    └── dist/            ← HTML/CSS/JS compilados
        ├── index.html
        ├── assets/
        └── favicon.ico
```

---

## 📋 PASO 3: SUBIR ARCHIVOS AL SERVIDOR

### Opción A: Git (Recomendado)

```bash
# En el servidor
cd /var/www
git clone https://github.com/tu-usuario/chayane-erp.git chayane
cd chayane

# Instalar backend
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# Compilar frontend
cd ../frontend
npm install
npm run build
```

### Opción B: FTP/SFTP

```bash
# Subir archivos vía FileZilla o similar
# Estructura:
/var/www/chayane/backend/
/var/www/chayane/frontend/dist/
```

---

## 📋 PASO 4: CONFIGURAR BASE DE DATOS

```bash
# Conectar a PostgreSQL
sudo -u postgres psql

# Crear base de datos
CREATE DATABASE chayane_production;
CREATE USER chayane_user WITH PASSWORD 'password_seguro_aqui';
GRANT ALL PRIVILEGES ON DATABASE chayane_production TO chayane_user;
\q

# Ejecutar migraciones
cd /var/www/chayane/backend
php artisan migrate --force
php artisan db:seed --force
```

---

## 📋 PASO 5: CONFIGURAR .ENV (Producción)

```bash
# backend/.env
APP_NAME=CHAYANE
APP_ENV=production
APP_KEY=base64:... (generado)
APP_DEBUG=false
APP_URL=https://chayane.upch.edu.pe

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chayane_production
DB_USERNAME=chayane_user
DB_PASSWORD=password_seguro_aqui

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# frontend/.env.production
VITE_API_URL=https://chayane.upch.edu.pe/api
VITE_APP_NAME=CHAYANE
```

---

## 📋 PASO 6: CONFIGURAR NGINX

### /etc/nginx/sites-available/chayane

```nginx
server {
    listen 80;
    server_name chayane.upch.edu.pe;
    
    # Redirigir a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name chayane.upch.edu.pe;
    
    # SSL
    ssl_certificate /etc/letsencrypt/live/chayane.upch.edu.pe/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/chayane.upch.edu.pe/privkey.pem;
    
    # Frontend - Vue.js (SPA)
    root /var/www/chayane/frontend/dist;
    index index.html;
    
    # Logs
    access_log /var/log/nginx/chayane-access.log;
    error_log /var/log/nginx/chayane-error.log;
    
    # Frontend - SPA routing
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Backend - Laravel API
    location /api {
        alias /var/www/chayane/backend/public;
        try_files $uri $uri/ @laravel;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
        }
    }
    
    location @laravel {
        rewrite /api/(.*)$ /api/index.php?/$1 last;
    }
    
    # Cache estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Seguridad
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Compresión
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
}
```

### Activar configuración:

```bash
# Enlazar configuración
sudo ln -s /etc/nginx/sites-available/chayane /etc/nginx/sites-enabled/

# Probar configuración
sudo nginx -t

# Recargar nginx
sudo systemctl reload nginx
```

---

## 📋 PASO 7: CONFIGURAR SSL (HTTPS)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado SSL gratuito
sudo certbot --nginx -d chayane.upch.edu.pe

# Renovación automática (ya viene configurada)
sudo certbot renew --dry-run
```

---

## 📋 PASO 8: PERMISOS Y SEGURIDAD

```bash
# Permisos de archivos
cd /var/www/chayane
sudo chown -R www-data:www-data backend/storage backend/bootstrap/cache
sudo chmod -R 775 backend/storage backend/bootstrap/cache

# Firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## ✅ VERIFICACIÓN

### 1. Probar Backend (API):
```bash
curl https://chayane.upch.edu.pe/api/health
# Respuesta: {"status":"ok"}
```

### 2. Probar Frontend:
```
Abrir navegador: https://chayane.upch.edu.pe
Debe cargar la aplicación Vue.js
```

### 3. Probar desde dispositivos:

**Desktop:**
- Windows: Chrome, Edge, Firefox
- Mac: Safari, Chrome
- Linux: Firefox, Chrome

**Mobile:**
- Android: Chrome
- iOS: Safari

**Tablet:**
- iPad: Safari
- Android: Chrome

---

## 🌐 OPCIÓN 2: HOSTING COMPARTIDO (Alternativa)

### Proveedores recomendados en Perú:

1. **Hostinger Perú**
   - Desde S/ 9.99/mes
   - PHP + PostgreSQL
   - SSL gratis
   - Panel cPanel

2. **DonWeb Perú**
   - Desde S/ 15/mes
   - Soporte 24/7
   - Backups automáticos

3. **Baehost**
   - Hosting peruano
   - Soporte en español
   - Desde S/ 12/mes

### Pasos para hosting compartido:

```bash
# 1. Subir archivos vía FTP
public_html/
├── index.html (del build de Vue)
├── assets/
└── api/  (código Laravel)

# 2. Crear base de datos desde cPanel
# 3. Configurar .env
# 4. Ejecutar migraciones desde terminal SSH o cron job
```

---

## 🌐 OPCIÓN 3: SERVICIOS CLOUD (Escalable)

### Vercel (Frontend) + Railway (Backend)

**Vercel (Frontend - GRATIS):**
```bash
npm install -g vercel
cd frontend
vercel deploy --prod
# URL: https://chayane.vercel.app
```

**Railway (Backend + PostgreSQL):**
1. Registrarse en railway.app
2. Conectar repositorio GitHub
3. Agregar PostgreSQL addon
4. Configurar variables de entorno
5. Deploy automático

**Costo:** ~$5-10/mes

---

## 📱 ACCESO DESDE DISPOSITIVOS

### URLs de acceso:

```
🌐 Portal Principal:
https://chayane.upch.edu.pe

🔐 Login:
https://chayane.upch.edu.pe/login

📊 Dashboard:
https://chayane.upch.edu.pe/dashboard

🍽️ Menú Público:
https://chayane.upch.edu.pe/menu

📅 Reservas:
https://chayane.upch.edu.pe/reservas
```

### Instalación como PWA (Opcional):

**Desktop (Chrome):**
1. Abrir https://chayane.upch.edu.pe
2. Barra de direcciones → Ícono de instalación
3. Click "Instalar"
4. ¡Ícono en escritorio!

**Mobile (Android/iOS):**
1. Abrir en navegador
2. Menú → "Agregar a pantalla de inicio"
3. Ícono como app nativa

---

## 🔄 ACTUALIZACIONES

```bash
# Actualizar backend
cd /var/www/chayane/backend
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache

# Actualizar frontend
cd ../frontend
git pull origin main
npm install
npm run build

# Reiniciar servicios
sudo systemctl reload nginx
sudo systemctl restart php8.1-fpm
```

---

## 📊 MONITOREO

### Herramientas recomendadas:

1. **Uptime Monitoring:**
   - UptimeRobot (gratis)
   - Pingdom

2. **Analytics:**
   - Google Analytics
   - Plausible (privacy-first)

3. **Logs:**
```bash
# Ver logs en tiempo real
tail -f /var/log/nginx/chayane-error.log
tail -f /var/www/chayane/backend/storage/logs/laravel.log
```

---

## 🆘 PROBLEMAS COMUNES

### Error 500 - Internal Server Error
```bash
# Revisar logs
tail -n 50 /var/www/chayane/backend/storage/logs/laravel.log

# Verificar permisos
sudo chown -R www-data:www-data /var/www/chayane/backend/storage
```

### No carga el frontend
```bash
# Verificar que existe dist/
ls -la /var/www/chayane/frontend/dist/

# Recompilar si es necesario
cd /var/www/chayane/frontend
npm run build
```

### Error de base de datos
```bash
# Verificar conexión
psql -U chayane_user -d chayane_production -h localhost

# Revisar .env
cat /var/www/chayane/backend/.env | grep DB_
```

---

## ✅ CHECKLIST FINAL

- [ ] Código subido al servidor
- [ ] Base de datos creada y migrada
- [ ] .env configurado correctamente
- [ ] Nginx configurado y activo
- [ ] SSL/HTTPS funcionando
- [ ] Frontend compilado en dist/
- [ ] API responde correctamente
- [ ] Se puede acceder desde navegador
- [ ] Funciona en mobile
- [ ] Funciona en desktop
- [ ] Credenciales de admin funcionan
- [ ] Backups configurados

---

## 🎉 ¡LISTO!

Tu aplicación web está publicada y accesible desde cualquier dispositivo con internet.

**NO SE REQUIERE INSTALACIÓN** - Solo abrir el navegador y entrar a la URL.

---

*Última actualización: Octubre 2025*
