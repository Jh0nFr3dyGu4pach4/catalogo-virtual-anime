<?php

class Catalog
{
    public static function categories(): array
    {
        return Database::pdo()->query('SELECT * FROM categoria ORDER BY nombre')->fetchAll();
    }

    public static function categoryName(int $id): string
    {
        $st = Database::pdo()->prepare('SELECT nombre FROM categoria WHERE id=?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ? $r['nombre'] : '';
    }

    public static function saveCategory(?int $id, array $data): array
    {
        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') return ['ok' => false, 'error' => 'Nombre requerido'];
        $desc = $data['descripcion'] ?? '';
        $pdo = Database::pdo();
        if ($id) {
            $st = $pdo->prepare('UPDATE categoria SET nombre=?, descripcion=? WHERE id=?');
            $st->execute([$nombre, $desc, $id]);
            if ($st->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM categoria WHERE id=?');
                $check->execute([$id]);
                if (!$check->fetch()) return ['ok' => false, 'error' => 'No encontrada'];
            }
            return ['ok' => true];
        }
        $pdo->prepare('INSERT INTO categoria (nombre, descripcion) VALUES (?,?)')->execute([$nombre, $desc]);
        return ['ok' => true, 'id' => (int)$pdo->lastInsertId()];
    }

    public static function deleteCategory(int $id): array
    {
        $st = Database::pdo()->prepare('SELECT COUNT(*) FROM producto WHERE categoria_id=?');
        $st->execute([$id]);
        if ((int)$st->fetchColumn() > 0) {
            return ['ok' => false, 'error' => 'No se puede eliminar: tiene productos asociados'];
        }
        Database::pdo()->prepare('DELETE FROM categoria WHERE id=?')->execute([$id]);
        return ['ok' => true];
    }

    public static function products(array $filters = []): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria_nombre FROM producto p
                LEFT JOIN categoria c ON c.id = p.categoria_id WHERE 1=1';
        $params = [];
        if (!empty($filters['categoria_id'])) {
            $sql .= ' AND p.categoria_id = ?';
            $params[] = (int)$filters['categoria_id'];
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ? OR c.nombre LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q; $params[] = $q; $params[] = $q;
        }
        $sql .= ' ORDER BY p.id DESC';
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function product(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT p.*, c.nombre AS categoria_nombre FROM producto p
             LEFT JOIN categoria c ON c.id = p.categoria_id WHERE p.id=?'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function saveProduct(?int $id, array $data): array
    {
        $nombre = trim($data['nombre'] ?? '');
        $cat = (int)($data['categoria_id'] ?? 0);
        if ($nombre === '' || $cat <= 0) return ['ok' => false, 'error' => 'Datos incompletos'];
        $stock = (int)($data['stock'] ?? 0);
        $estado = $stock <= 0 ? 'agotado' : ($data['estado'] ?? 'disponible');
        $precio = (float)($data['precio'] ?? 0);
        $desc = $data['descripcion'] ?? '';
        $mat = $data['material'] ?? '';
        $img = $data['imagen_url'] ?? '';
        $pdo = Database::pdo();

        if ($id) {
            $st = $pdo->prepare(
                'UPDATE producto SET nombre=?, precio=?, stock=?, estado=?, descripcion=?, material=?, imagen_url=?, categoria_id=? WHERE id=?'
            );
            $st->execute([$nombre, $precio, $stock, $estado, $desc, $mat, $img, $cat, $id]);
            return ['ok' => true];
        }
        $pdo->prepare(
            'INSERT INTO producto (nombre, precio, stock, estado, descripcion, material, imagen_url, categoria_id)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([$nombre, $precio, $stock, $estado, $desc, $mat, $img, $cat]);
        return ['ok' => true, 'id' => (int)$pdo->lastInsertId()];
    }

    public static function deleteProduct(int $id): array
    {
        Database::pdo()->prepare('DELETE FROM producto WHERE id=?')->execute([$id]);
        return ['ok' => true];
    }
}
