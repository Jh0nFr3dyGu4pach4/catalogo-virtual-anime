<?php

class Settings
{
    public static function all(): array
    {
        $rows = Database::pdo()->query('SELECT clave, valor FROM configuracion')->fetchAll();
        $out = [
            'nombre_tienda' => 'Catalogo Virtual Anime',
            'whatsapp_vendedor' => '3000000000',
            'pasarela_activa' => false,
            'pasarela_proveedor' => 'wompi',
            'pasarela_public_key' => '',
            'pasarela_private_key' => '',
            'moneda' => 'COP',
        ];
        foreach ($rows as $r) {
            $k = $r['clave'];
            $v = $r['valor'];
            if ($k === 'pasarela_activa') $v = ($v === '1' || $v === 'true');
            $out[$k] = $v;
        }
        return $out;
    }

    public static function get(string $key, $default = null)
    {
        $s = self::all();
        return $s[$key] ?? $default;
    }

    public static function save(array $data): array
    {
        $allowed = [
            'nombre_tienda', 'whatsapp_vendedor', 'pasarela_activa',
            'pasarela_proveedor', 'pasarela_public_key', 'pasarela_private_key', 'moneda'
        ];
        $pdo = Database::pdo();
        $st = $pdo->prepare('INSERT INTO configuracion (clave, valor) VALUES (?,?)
            ON DUPLICATE KEY UPDATE valor=VALUES(valor)');
        foreach ($allowed as $k) {
            if (!array_key_exists($k, $data)) continue;
            $v = $data[$k];
            if ($k === 'pasarela_activa') $v = $v ? '1' : '0';
            elseif ($k === 'whatsapp_vendedor') $v = preg_replace('/\D/', '', (string)$v);
            else $v = is_string($v) ? trim($v) : $v;
            $st->execute([$k, (string)$v]);
        }
        return ['ok' => true, 'settings' => self::publicSettings()];
    }

    public static function publicSettings(): array
    {
        $s = self::all();
        return [
            'nombre_tienda' => $s['nombre_tienda'],
            'whatsapp_vendedor' => $s['whatsapp_vendedor'],
            'pasarela_activa' => (bool)$s['pasarela_activa'],
            'pasarela_proveedor' => $s['pasarela_proveedor'],
            'pasarela_public_key' => $s['pasarela_public_key'],
            'moneda' => $s['moneda'],
        ];
    }
}
