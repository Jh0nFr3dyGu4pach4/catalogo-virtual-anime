<?php

class Storage
{
    public static function path(string $file): string
    {
        if (!is_dir(DATA_DIR)) {
            mkdir(DATA_DIR, 0777, true);
        }
        return DATA_DIR . $file;
    }

    public static function load(string $file, $default = [])
    {
        $path = self::path($file);
        if (!file_exists($path)) {
            self::save($file, $default);
            return $default;
        }
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : $default;
    }

    public static function save(string $file, $data): void
    {
        file_put_contents(
            self::path($file),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public static function nextId(string $key): int
    {
        $meta = self::load('meta.json', []);
        $val = (int)($meta[$key] ?? 1);
        $meta[$key] = $val + 1;
        self::save('meta.json', $meta);
        return $val;
    }

    public static function ensureSeed(): void
    {
        $cats = self::load('categorias.json', null);
        if ($cats !== null && count($cats) > 0) {
            return;
        }

        self::save('categorias.json', [
            ['id' => 1, 'nombre' => 'Animes', 'descripcion' => 'Figuras y llaveros de anime'],
            ['id' => 2, 'nombre' => 'Series', 'descripcion' => 'Articulos de series famosas'],
            ['id' => 3, 'nombre' => 'Llaveros', 'descripcion' => 'Llaveros coleccionables'],
            ['id' => 4, 'nombre' => 'Figuras', 'descripcion' => 'Figuras de coleccion'],
        ]);

        self::save('productos.json', [
            ['id'=>1,'nombre'=>'Llavero Naruto Uzumaki','precio'=>18000,'stock'=>12,'estado'=>'disponible','descripcion'=>'Llavero metalico de Naruto.','material'=>'Metal y esmalte','imagen_url'=>'','categoria_id'=>1],
            ['id'=>2,'nombre'=>'Figura Goku Super Saiyan','precio'=>85000,'stock'=>3,'estado'=>'disponible','descripcion'=>'Figura articulada Goku SSJ 18cm.','material'=>'PVC','imagen_url'=>'','categoria_id'=>4],
            ['id'=>3,'nombre'=>'Llavero Stranger Things','precio'=>22000,'stock'=>0,'estado'=>'agotado','descripcion'=>'Llavero logo Stranger Things.','material'=>'Acrilico','imagen_url'=>'','categoria_id'=>2],
            ['id'=>4,'nombre'=>'Figura Nezuko Kamado','precio'=>72000,'stock'=>5,'estado'=>'disponible','descripcion'=>'Figura Nezuko Demon Slayer.','material'=>'PVC','imagen_url'=>'','categoria_id'=>1],
            ['id'=>5,'nombre'=>'Llavero One Piece Luffy','precio'=>16000,'stock'=>20,'estado'=>'disponible','descripcion'=>'Llavero Luffy sombrero de paja.','material'=>'Goma y metal','imagen_url'=>'','categoria_id'=>3],
            ['id'=>6,'nombre'=>'Figura Tanjiro Kamado','precio'=>78000,'stock'=>2,'estado'=>'disponible','descripcion'=>'Figura Tanjiro con espada 1/8.','material'=>'PVC','imagen_url'=>'','categoria_id'=>4],
        ]);

        self::save('administradores.json', [[
            'id' => 1,
            'usuario' => 'admin',
            'contrasena_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'nombre' => 'Administrador',
            'email' => 'admin@catalogo.local',
            'activo' => true,
            'creado_en' => date('c'),
        ]]);

        self::save('cotizaciones.json', []);
        self::save('pagos.json', []);
        self::save('login_attempts.json', []);
        self::save('meta.json', [
            'next_producto_id' => 7,
            'next_categoria_id' => 5,
            'next_cotizacion_id' => 1,
            'next_pago_id' => 1,
        ]);
        self::save('settings.json', [
            'nombre_tienda' => 'Catalogo Virtual Anime',
            'whatsapp_vendedor' => '3000000000',
            'pasarela_activa' => false,
            'pasarela_proveedor' => 'wompi',
            'pasarela_public_key' => '',
            'pasarela_private_key' => '',
            'moneda' => 'COP',
        ]);
    }
}
