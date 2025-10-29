# 🗄️ ESQUEMA DE BASE DE DATOS - CHAYANE ERP

## Base de datos: PostgreSQL 14+

---

## 📊 DIAGRAMA ENTIDAD-RELACIÓN (Resumen)

```
users ──┬── roles
        │
        └── ventas ─── detalle_ventas ─── productos ─── categorias
                │                          │
                └── cajas                  └── inventario_movimientos
        
reservas ─── mesas

gastos ─── categorias_gastos

notificaciones ─── users
```

---

## 📋 TABLAS

### 1. users (Usuarios del Sistema)

Almacena información de los usuarios que acceden al sistema.

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT REFERENCES roles(id),
    telefono VARCHAR(20),
    direccion TEXT,
    estado BOOLEAN DEFAULT true,
    avatar VARCHAR(255),
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role_id);
```

**Campos:**
- `id`: Identificador único
- `name`: Nombre completo
- `email`: Correo electrónico (único)
- `password`: Contraseña hasheada
- `role_id`: Referencia al rol
- `estado`: true=activo, false=inactivo

---

### 2. roles (Roles de Usuario)

Define los roles y permisos en el sistema.

```sql
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    permisos JSONB, -- Array de permisos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar roles por defecto
INSERT INTO roles (nombre, descripcion, permisos) VALUES
('Administrador', 'Acceso total al sistema', '["all"]'),
('Cajero', 'Gestión de ventas y caja', '["ventas", "caja", "reportes"]'),
('Mesero', 'Toma de pedidos', '["pedidos", "mesas"]');
```

**Roles:**
- **Administrador**: Acceso completo
- **Cajero**: Ventas, caja, reportes
- **Mesero**: Pedidos y mesas

---

### 3. categorias (Categorías de Productos)

Clasificación de productos e insumos.

```sql
CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ejemplos
INSERT INTO categorias (nombre, descripcion) VALUES
('Ingredientes', 'Insumos para cocina'),
('Bebidas', 'Bebidas y licores'),
('Platos Principales', 'Platos del menú'),
('Postres', 'Postres y dulces'),
('Utensilios', 'Utensilios de cocina');
```

---

### 4. productos (Productos e Insumos)

Almacena todos los productos, tanto para venta como insumos.

```sql
CREATE TABLE productos (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    categoria_id BIGINT REFERENCES categorias(id),
    precio_compra DECIMAL(10,2),
    precio_venta DECIMAL(10,2),
    stock_actual INTEGER DEFAULT 0,
    stock_minimo INTEGER DEFAULT 5,
    unidad_medida VARCHAR(20), -- kg, litros, unidades
    tipo VARCHAR(20), -- 'producto' o 'insumo'
    imagen VARCHAR(255),
    estado BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_productos_codigo ON productos(codigo);
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_tipo ON productos(tipo);
```

**Campos importantes:**
- `stock_minimo`: Para alertas
- `tipo`: 'producto' (para venta) o 'insumo' (para cocina)

---

### 5. inventario_movimientos (Historial de Movimientos)

Registra entrada/salida de inventario.

```sql
CREATE TABLE inventario_movimientos (
    id BIGSERIAL PRIMARY KEY,
    producto_id BIGINT REFERENCES productos(id),
    tipo_movimiento VARCHAR(20), -- 'entrada', 'salida', 'ajuste'
    cantidad INTEGER NOT NULL,
    precio_unitario DECIMAL(10,2),
    motivo TEXT,
    usuario_id BIGINT REFERENCES users(id),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_movimientos_producto ON inventario_movimientos(producto_id);
CREATE INDEX idx_movimientos_fecha ON inventario_movimientos(fecha);
```

---

### 6. ventas (Registro de Ventas)

Cabecera de cada venta realizada.

```sql
CREATE TABLE ventas (
    id BIGSERIAL PRIMARY KEY,
    numero_venta VARCHAR(50) UNIQUE NOT NULL, -- V-00001
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id BIGINT REFERENCES users(id), -- Cajero
    mesa_id BIGINT REFERENCES mesas(id) NULL, -- Si es en mesa
    cliente_nombre VARCHAR(255),
    cliente_documento VARCHAR(20),
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    impuesto DECIMAL(10,2) DEFAULT 0, -- IGV 18%
    total DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(20), -- 'efectivo', 'tarjeta', 'yape'
    estado VARCHAR(20) DEFAULT 'completada', -- 'completada', 'anulada'
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ventas_fecha ON ventas(fecha);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_ventas_numero ON ventas(numero_venta);
```

---

### 7. detalle_ventas (Detalle de Ventas)

Productos vendidos en cada venta.

```sql
CREATE TABLE detalle_ventas (
    id BIGSERIAL PRIMARY KEY,
    venta_id BIGINT REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id BIGINT REFERENCES productos(id),
    cantidad INTEGER NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_detalle_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_producto ON detalle_ventas(producto_id);
```

---

### 8. cajas (Cierres de Caja)

Registro de cierres de caja diarios.

```sql
CREATE TABLE cajas (
    id BIGSERIAL PRIMARY KEY,
    numero_cierre VARCHAR(50) UNIQUE NOT NULL, -- C-00001
    usuario_id BIGINT REFERENCES users(id),
    fecha_apertura TIMESTAMP NOT NULL,
    fecha_cierre TIMESTAMP,
    monto_inicial DECIMAL(10,2) DEFAULT 0,
    total_ventas DECIMAL(10,2) DEFAULT 0,
    total_gastos DECIMAL(10,2) DEFAULT 0,
    monto_final DECIMAL(10,2),
    diferencia DECIMAL(10,2) DEFAULT 0,
    observaciones TEXT,
    estado VARCHAR(20) DEFAULT 'abierta', -- 'abierta', 'cerrada'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cajas_fecha ON cajas(fecha_apertura);
CREATE INDEX idx_cajas_usuario ON cajas(usuario_id);
```

---

### 9. gastos (Registro de Gastos)

Gastos y egresos del restaurante.

```sql
CREATE TABLE gastos (
    id BIGSERIAL PRIMARY KEY,
    numero_gasto VARCHAR(50) UNIQUE NOT NULL, -- G-00001
    categoria_gasto_id BIGINT REFERENCES categorias_gastos(id),
    concepto VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    proveedor VARCHAR(255),
    comprobante VARCHAR(100), -- Factura/Boleta
    usuario_id BIGINT REFERENCES users(id),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_gastos_fecha ON gastos(fecha);
CREATE INDEX idx_gastos_categoria ON gastos(categoria_gasto_id);
```

---

### 10. categorias_gastos (Categorías de Gastos)

```sql
CREATE TABLE categorias_gastos (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categorias_gastos (nombre) VALUES
('Servicios'), ('Salarios'), ('Compras'), ('Mantenimiento'), ('Otros');
```

---

### 11. mesas (Mesas del Restaurante)

```sql
CREATE TABLE mesas (
    id BIGSERIAL PRIMARY KEY,
    numero_mesa INTEGER UNIQUE NOT NULL,
    capacidad INTEGER NOT NULL,
    ubicacion VARCHAR(50), -- 'terraza', 'salon', 'exterior'
    estado VARCHAR(20) DEFAULT 'disponible', -- 'disponible', 'ocupada', 'reservada'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar mesas ejemplo
INSERT INTO mesas (numero_mesa, capacidad, ubicacion) VALUES
(1, 4, 'salon'),
(2, 4, 'salon'),
(3, 6, 'salon'),
(4, 2, 'terraza'),
(5, 8, 'salon');
```

---

### 12. reservas (Reservas de Mesas)

```sql
CREATE TABLE reservas (
    id BIGSERIAL PRIMARY KEY,
    numero_reserva VARCHAR(50) UNIQUE NOT NULL, -- R-00001
    mesa_id BIGINT REFERENCES mesas(id),
    cliente_nombre VARCHAR(255) NOT NULL,
    cliente_telefono VARCHAR(20) NOT NULL,
    cliente_email VARCHAR(255),
    fecha_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    numero_personas INTEGER NOT NULL,
    observaciones TEXT,
    estado VARCHAR(20) DEFAULT 'pendiente', -- 'pendiente', 'confirmada', 'cancelada', 'completada'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_reservas_fecha ON reservas(fecha_reserva);
CREATE INDEX idx_reservas_mesa ON reservas(mesa_id);
```

---

### 13. notificaciones (Sistema de Notificaciones)

```sql
CREATE TABLE notificaciones (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT REFERENCES users(id) NULL, -- NULL = todos
    tipo VARCHAR(50), -- 'stock_bajo', 'nueva_reserva', 'cierre_caja'
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT false,
    datos JSONB, -- Información adicional
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notificaciones_usuario ON notificaciones(usuario_id);
CREATE INDEX idx_notificaciones_leida ON notificaciones(leida);
```

---

### 14. configuracion (Configuración del Sistema)

```sql
CREATE TABLE configuracion (
    id BIGSERIAL PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    tipo VARCHAR(20), -- 'text', 'number', 'boolean', 'json'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Configuraciones iniciales
INSERT INTO configuracion (clave, valor, tipo, descripcion) VALUES
('nombre_restaurante', 'La Sazón de Pilar', 'text', 'Nombre del restaurante'),
('ruc', '20123456789', 'text', 'RUC del restaurante'),
('direccion', 'Av. Principal 123, Lima', 'text', 'Dirección'),
('telefono', '01-1234567', 'text', 'Teléfono'),
('email', 'contacto@lasazondepilar.com', 'text', 'Email'),
('igv', '18', 'number', 'Porcentaje de IGV'),
('moneda', 'PEN', 'text', 'Moneda (PEN, USD)'),
('stock_minimo_alerta', '5', 'number', 'Stock mínimo para alertas');
```

---

## 🔄 RELACIONES PRINCIPALES

```
users.role_id → roles.id
productos.categoria_id → categorias.id
inventario_movimientos.producto_id → productos.id
inventario_movimientos.usuario_id → users.id
ventas.usuario_id → users.id
ventas.mesa_id → mesas.id
detalle_ventas.venta_id → ventas.id
detalle_ventas.producto_id → productos.id
cajas.usuario_id → users.id
gastos.categoria_gasto_id → categorias_gastos.id
gastos.usuario_id → users.id
reservas.mesa_id → mesas.id
notificaciones.usuario_id → users.id
```

---

## 📊 VISTAS ÚTILES

### Vista: Productos con Stock Bajo

```sql
CREATE VIEW v_productos_stock_bajo AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.stock_actual,
    p.stock_minimo,
    c.nombre as categoria
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
WHERE p.stock_actual <= p.stock_minimo
  AND p.estado = true;
```

### Vista: Resumen de Ventas Diarias

```sql
CREATE VIEW v_ventas_diarias AS
SELECT 
    DATE(fecha) as fecha,
    COUNT(*) as total_ventas,
    SUM(total) as monto_total,
    AVG(total) as ticket_promedio
FROM ventas
WHERE estado = 'completada'
GROUP BY DATE(fecha)
ORDER BY fecha DESC;
```

### Vista: Productos Más Vendidos

```sql
CREATE VIEW v_productos_mas_vendidos AS
SELECT 
    p.id,
    p.nombre,
    SUM(dv.cantidad) as cantidad_vendida,
    SUM(dv.subtotal) as monto_total
FROM detalle_ventas dv
INNER JOIN productos p ON dv.producto_id = p.id
INNER JOIN ventas v ON dv.venta_id = v.id
WHERE v.estado = 'completada'
  AND v.fecha >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY p.id, p.nombre
ORDER BY cantidad_vendida DESC
LIMIT 10;
```

---

## 🔐 USUARIOS POR DEFECTO

```sql
-- Password: admin123 (bcrypt)
INSERT INTO users (name, email, password, role_id) VALUES
('Administrador', 'admin@chayane.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Cajero Principal', 'cajero@chayane.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('Mesero Juan', 'mesero@chayane.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);
```

---

## 📈 TRIGGERS

### Trigger: Actualizar stock después de venta

```sql
CREATE OR REPLACE FUNCTION actualizar_stock_venta()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE productos 
    SET stock_actual = stock_actual - NEW.cantidad
    WHERE id = NEW.producto_id;
    
    -- Insertar movimiento de inventario
    INSERT INTO inventario_movimientos (producto_id, tipo_movimiento, cantidad, motivo)
    VALUES (NEW.producto_id, 'salida', NEW.cantidad, 'Venta #' || NEW.venta_id);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_stock_venta
AFTER INSERT ON detalle_ventas
FOR EACH ROW
EXECUTE FUNCTION actualizar_stock_venta();
```

### Trigger: Crear notificación de stock bajo

```sql
CREATE OR REPLACE FUNCTION notificar_stock_bajo()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.stock_actual <= NEW.stock_minimo THEN
        INSERT INTO notificaciones (tipo, titulo, mensaje, datos)
        VALUES (
            'stock_bajo',
            'Stock Bajo',
            'El producto ' || NEW.nombre || ' tiene stock bajo (' || NEW.stock_actual || ' unidades)',
            json_build_object('producto_id', NEW.id, 'stock_actual', NEW.stock_actual)
        );
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_notificar_stock
AFTER UPDATE OF stock_actual ON productos
FOR EACH ROW
EXECUTE FUNCTION notificar_stock_bajo();
```

---

## 📝 NOTAS IMPORTANTES

1. **Índices**: Se han creado índices en columnas frecuentemente consultadas
2. **Timestamps**: Todas las tablas tienen `created_at` y `updated_at`
3. **Soft Deletes**: Usar campo `estado` en lugar de eliminar registros
4. **JSON**: PostgreSQL soporta JSONB para datos flexibles
5. **Constraints**: ON DELETE CASCADE para mantener integridad
6. **Seguridad**: Contraseñas siempre hasheadas (bcrypt)

---

## 🔍 CONSULTAS ÚTILES PARA DESARROLLO

```sql
-- Ver todas las tablas
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public' ORDER BY table_name;

-- Ver estructura de una tabla
\d+ productos

-- Resetear secuencias
SELECT setval('productos_id_seq', 1, false);

-- Backup de base de datos
pg_dump -U postgres chayane_db > backup.sql

-- Restaurar backup
psql -U postgres chayane_db < backup.sql
```

---

## ✅ CHECKLIST DE MIGRACIÓN

- [ ] Crear base de datos `chayane_db`
- [ ] Ejecutar script de tablas en orden
- [ ] Ejecutar inserts de datos iniciales
- [ ] Crear vistas
- [ ] Crear triggers
- [ ] Verificar relaciones
- [ ] Insertar usuarios de prueba
- [ ] Verificar permisos de usuario PostgreSQL

---

**Última actualización:** Fecha de creación del proyecto
