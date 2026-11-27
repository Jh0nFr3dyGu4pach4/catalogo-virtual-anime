# Catálogo Virtual Anime — App Web (MySQL)

## Requisitos
- PHP 8+ con extensión **pdo_mysql**
- MySQL o MariaDB (XAMPP / Laragon / WAMP)

## Instalación rápida (XAMPP)

1. Copia la carpeta `app_web` a `C:\xampp\htdocs\`
2. En el panel de XAMPP, inicia **Apache** y **MySQL**
3. Abre: http://localhost/app_web/install.php
4. Deja usuario `root` y contraseña vacía (por defecto XAMPP) y pulsa **Crear base de datos**
5. Entra al catálogo: http://localhost/app_web/
6. Admin: http://localhost/app_web/admin/login.php  
   - Usuario: **admin**  
   - Clave: **admin123**

## Instalación manual de la BD

```bash
mysql -u root -p < sql/modelo_mysql.sql
```

Luego edita `includes/db_config.php` si tu usuario/clave no son los de XAMPP.

## Dónde se guarda la información

| Dato | Ubicación |
|------|-----------|
| Productos, categorías, admin, cotizaciones, pagos, config | **Base de datos MySQL** `catalogo_anime` |
| Imágenes subidas | Carpeta `assets/uploads/` |

## Credenciales por defecto
- Admin: `admin` / `admin123`
- WhatsApp vendedor: configurable en Admin → Configuración

## Estructura
```
app_web/
  install.php          Asistente de instalación MySQL
  index.php            Catálogo público
  detalle.php          Detalle + cotización WhatsApp
  admin/               Login y panel CRUD
  api/                 API REST
  includes/modules/    Database, Auth, Catalog, Orders, Settings
  sql/modelo_mysql.sql Esquema y datos iniciales
  assets/uploads/      Imágenes de productos
```

## Videos
- Video de sustentación: https://drive.google.com/file/d/1GThMjdj-0thDXYfTA9RRcBnpXq009POT/view?usp=drive_link
- video del Prototipo: https://drive.google.com/file/d/1XqHjQTBfdIT_aVUR6J5AIKgtw18wbTgJ/view?usp=drive_link
## Documentos
- Dossier de ingeniería: (si lo subes, indica la ruta, ej. docs/Dossier.docx)
