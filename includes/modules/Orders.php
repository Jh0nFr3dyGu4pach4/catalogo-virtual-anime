<?php

class Orders
{
    public static function createQuote(array $body): array
    {
        $items = $body['items'] ?? [];
        if (!is_array($items) || count($items) === 0) {
            return ['ok' => false, 'error' => 'Sin productos'];
        }
        $pdo = Database::pdo();
        $total = 0;
        $lineas = [];
        foreach ($items as $it) {
            $pid = (int)($it['producto_id'] ?? 0);
            $cant = max(1, (int)($it['cantidad'] ?? 1));
            $st = $pdo->prepare('SELECT * FROM producto WHERE id=?');
            $st->execute([$pid]);
            $p = $st->fetch();
            if (!$p) continue;
            if ($p['stock'] < $cant || $p['estado'] === 'agotado') {
                return ['ok' => false, 'error' => 'Stock insuficiente para: ' . $p['nombre']];
            }
            $sub = (float)$p['precio'] * $cant;
            $total += $sub;
            $lineas[] = ['producto' => $p, 'cantidad' => $cant, 'subtotal' => $sub];
        }
        if (!$lineas) return ['ok' => false, 'error' => 'Sin productos validos'];

        $msg = "Hola, quiero cotizar:\n";
        foreach ($lineas as $l) {
            $msg .= "• " . $l['producto']['nombre'] . " x" . $l['cantidad'] . " = $" . number_format($l['subtotal'], 0, ',', '.') . "\n";
        }
        $msg .= "Total: $" . number_format($total, 0, ',', '.') . "\nGracias.";

        $waNum = Settings::get('whatsapp_vendedor', '3000000000');
        if (!empty($body['whatsapp'])) {
            $waNum = preg_replace('/\D/', '', $body['whatsapp']);
        }

        $pdo->prepare('INSERT INTO cotizacion (total, estado, telefono_cliente, mensaje_whatsapp) VALUES (?,?,?,?)')
            ->execute([$total, 'enviada', $body['telefono'] ?? '', $msg]);
        $cotId = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO item_cotizacion (cotizacion_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)');
        foreach ($lineas as $l) {
            $ins->execute([$cotId, $l['producto']['id'], $l['cantidad'], $l['producto']['precio'], $l['subtotal']]);
        }

        $wa = 'https://wa.me/57' . $waNum . '?text=' . rawurlencode($msg);
        return ['ok' => true, 'cotizacion_id' => $cotId, 'mensaje' => $msg, 'whatsapp_url' => $wa, 'total' => $total];
    }

    public static function createPayment(array $body): array
    {
        $settings = Settings::all();
        if (empty($settings['pasarela_activa'])) {
            return ['ok' => false, 'error' => 'Pasarela de pagos no activa. Use cotizacion por WhatsApp.'];
        }
        $items = $body['items'] ?? [];
        if (!is_array($items) || !$items) return ['ok' => false, 'error' => 'Sin productos'];

        $pdo = Database::pdo();
        $total = 0;
        $lineas = [];
        foreach ($items as $it) {
            $pid = (int)($it['producto_id'] ?? 0);
            $cant = max(1, (int)($it['cantidad'] ?? 1));
            $st = $pdo->prepare('SELECT * FROM producto WHERE id=?');
            $st->execute([$pid]);
            $p = $st->fetch();
            if (!$p) continue;
            if ($p['stock'] < $cant || $p['estado'] === 'agotado') {
                return ['ok' => false, 'error' => 'Stock insuficiente: ' . $p['nombre']];
            }
            $sub = (float)$p['precio'] * $cant;
            $total += $sub;
            $lineas[] = $p['nombre'] . ' x' . $cant;
        }
        if (!$lineas) return ['ok' => false, 'error' => 'Sin productos validos'];

        $ref = 'CAT-' . time() . '-' . strtoupper(bin2hex(random_bytes(3)));
        $pdo->prepare('INSERT INTO pago (referencia, total, estado, proveedor, cliente_email, cliente_nombre) VALUES (?,?,?,?,?,?)')
            ->execute([$ref, $total, 'pendiente', $settings['pasarela_proveedor'], $body['email'] ?? '', $body['nombre'] ?? '']);
        $pagoId = (int)$pdo->lastInsertId();

        return [
            'ok' => true,
            'pago_id' => $pagoId,
            'referencia' => $ref,
            'total' => $total,
            'proveedor' => $settings['pasarela_proveedor'],
            'public_key' => $settings['pasarela_public_key'],
            'mensaje' => 'Pago registrado pendiente. Configure las claves de ' . $settings['pasarela_proveedor'] . ' en produccion.',
        ];
    }
}
