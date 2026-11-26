CREATE DATABASE IF NOT EXISTS catalogo_anime
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalogo_anime;

CREATE TABLE IF NOT EXISTS categoria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS administrador (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  contrasena_hash VARCHAR(255) NOT NULL,
  nombre VARCHAR(100) NULL,
  email VARCHAR(120) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS producto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  precio DECIMAL(12,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  estado ENUM('disponible','agotado') NOT NULL DEFAULT 'disponible',
  descripcion TEXT NULL,
  material VARCHAR(100) NULL,
  imagen_url VARCHAR(255) NULL,
  categoria_id INT NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_producto_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cotizacion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('enviada','pendiente') NOT NULL DEFAULT 'pendiente',
  telefono_cliente VARCHAR(20) NULL,
  mensaje_whatsapp TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS item_cotizacion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cotizacion_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_item_cot FOREIGN KEY (cotizacion_id) REFERENCES cotizacion(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_prod FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pago (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referencia VARCHAR(50) NOT NULL UNIQUE,
  total DECIMAL(12,2) NOT NULL,
  estado ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  proveedor VARCHAR(50) NULL,
  cliente_email VARCHAR(120) NULL,
  cliente_nombre VARCHAR(120) NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(50) PRIMARY KEY,
  valor TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  intento_key VARCHAR(120) NOT NULL UNIQUE,
  count_fail INT NOT NULL DEFAULT 0,
  last_at DATETIME NULL,
  locked_until DATETIME NULL
) ENGINE=InnoDB;

DELETE FROM configuracion;
INSERT INTO configuracion (clave, valor) VALUES
('nombre_tienda', 'Catalogo Virtual Anime'),
('whatsapp_vendedor', '3000000000'),
('pasarela_activa', '0'),
('pasarela_proveedor', 'wompi'),
('pasarela_public_key', ''),
('pasarela_private_key', ''),
('moneda', 'COP');

DELETE FROM administrador;
INSERT INTO administrador (usuario, contrasena_hash, nombre, email, activo) VALUES
('admin', '$2y$10$cFpknzN3RW55C9z96x4uF.E8eASAQO.2/VTENz38pQAgxC/reEwum', 'Administrador', 'admin@catalogo.local', 1);

DELETE FROM producto;
DELETE FROM categoria;
INSERT INTO categoria (id, nombre, descripcion) VALUES
(1, 'Animes', 'Figuras y llaveros de anime'),
(2, 'Series', 'Articulos de series famosas'),
(3, 'Llaveros', 'Llaveros coleccionables'),
(4, 'Figuras', 'Figuras de coleccion');

INSERT INTO producto (nombre, precio, stock, estado, descripcion, material, imagen_url, categoria_id) VALUES
('Llavero Naruto Uzumaki', 18000, 12, 'disponible', 'Llavero metalico de Naruto.', 'Metal y esmalte', '', 1),
('Figura Goku Super Saiyan', 85000, 3, 'disponible', 'Figura articulada Goku SSJ 18cm.', 'PVC', '', 4),
('Llavero Stranger Things', 22000, 0, 'agotado', 'Llavero logo Stranger Things.', 'Acrilico', '', 2),
('Figura Nezuko Kamado', 72000, 5, 'disponible', 'Figura Nezuko Demon Slayer.', 'PVC', '', 1),
('Llavero One Piece Luffy', 16000, 20, 'disponible', 'Llavero Luffy sombrero de paja.', 'Goma y metal', '', 3),
('Figura Tanjiro Kamado', 78000, 2, 'disponible', 'Figura Tanjiro con espada 1/8.', 'PVC', '', 4);
