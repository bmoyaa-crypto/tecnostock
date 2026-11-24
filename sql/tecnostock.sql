CREATE DATABASE IF NOT EXISTS tecnostock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tecnostock;

-- 1. Tabla Usuario
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL, -- Almacenará el hash de password_hash()
    estado TINYINT(1) NOT NULL DEFAULT 1 -- 1: Activo, 0: Inactivo
) ENGINE=InnoDB;

-- 2. Tabla Categoría
CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 3. Tabla Producto
CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE, -- Regla: código no puede repetirse
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL CHECK (precio >= 0),
    stock_actual INT NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
    stock_minimo INT NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    estado TINYINT(1) NOT NULL DEFAULT 1, -- 1: Activo, 0: Desactivado
    id_categoria INT NOT NULL,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) 
        REFERENCES categoria(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. Tabla Movimiento
CREATE TABLE movimiento (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('ENTRADA', 'SALIDA') NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0), -- Regla: cantidad positiva
    observacion VARCHAR(255),
    id_producto INT NOT NULL,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_movimiento_producto FOREIGN KEY (id_producto) 
        REFERENCES producto(id_producto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_movimiento_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuario(id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ==========================================
-- DATOS INICIALES DE PRUEBA
-- ==========================================

-- Usuario administrador (Correo: admin@tecnostock.cl | Clave: password)
INSERT INTO usuario (nombre, correo, clave, estado) VALUES 
('Encargado TecnoStock', 'admin@tecnostock.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Categorías base
INSERT INTO categoria (nombre, descripcion) VALUES 
('Cables y Adaptadores', 'Cables USB-C, HDMI, Lightning y adaptadores de corriente'),
('Periféricos', 'Teclados, mouse, audífonos y webcams'),
('Almacenamiento', 'Pendrives, discos externos y tarjetas microSD');

-- Productos de ejemplo (incluye uno con bajo stock para probar la alerta)
INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria) VALUES 
('TEC-001', 'Cable USB-C Carga Rápida 2m', 'Cable trenzado de nylon 60W', 8990, 25, 10, 1),
('TEC-002', 'Mouse Inalámbrico Ergonómico', 'Sensor óptico 1600 DPI con receptor USB', 14990, 4, 8, 2),
('TEC-003', 'Pendrive 64GB USB 3.2', 'Carcasa metálica resistente al agua', 11500, 15, 5, 3);