<?php

class Auth
{
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_auth_token']);
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            self::json(['error' => 'No autorizado. Inicie sesion.'], 401);
        }
        if (!hash_equals($_SESSION['admin_auth_token'] ?? '', self::sessionToken())) {
            self::logout();
            self::json(['error' => 'Sesion invalida. Vuelva a iniciar sesion.'], 401);
        }
    }

    public static function sessionToken(): string
    {
        // Solo depende del admin_id + secreto, no del session_id,
        // para que navegar catálogo ↔ admin no invalide la sesión.
        return hash('sha256', 'catalogo_salt_v2|' . ($_SESSION['admin_id'] ?? '') . '|' . ($_SESSION['admin_usuario'] ?? ''));
    }

    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function isLocked(string $usuario): bool
    {
        $key = strtolower($usuario) . '|' . self::clientIp();
        $st = Database::pdo()->prepare('SELECT * FROM login_attempts WHERE intento_key=?');
        $st->execute([$key]);
        $info = $st->fetch();
        if (!$info || (int)$info['count_fail'] < MAX_LOGIN_ATTEMPTS) return false;
        if (!empty($info['locked_until']) && strtotime($info['locked_until']) > time()) return true;
        Database::pdo()->prepare('DELETE FROM login_attempts WHERE intento_key=?')->execute([$key]);
        return false;
    }

    public static function registerFail(string $usuario): void
    {
        $key = strtolower($usuario) . '|' . self::clientIp();
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM login_attempts WHERE intento_key=?');
        $st->execute([$key]);
        $info = $st->fetch();
        $count = (int)($info['count_fail'] ?? 0) + 1;
        $locked = $count >= MAX_LOGIN_ATTEMPTS
            ? date('Y-m-d H:i:s', time() + LOGIN_LOCK_MINUTES * 60)
            : null;
        if ($info) {
            $pdo->prepare('UPDATE login_attempts SET count_fail=?, last_at=NOW(), locked_until=? WHERE intento_key=?')
                ->execute([$count, $locked, $key]);
        } else {
            $pdo->prepare('INSERT INTO login_attempts (intento_key, count_fail, last_at, locked_until) VALUES (?,?,NOW(),?)')
                ->execute([$key, $count, $locked]);
        }
    }

    public static function clearFails(string $usuario): void
    {
        $key = strtolower($usuario) . '|' . self::clientIp();
        Database::pdo()->prepare('DELETE FROM login_attempts WHERE intento_key=?')->execute([$key]);
    }

    public static function login(string $usuario, string $password): array
    {
        $usuario = trim($usuario);
        if ($usuario === '' || $password === '') {
            return ['ok' => false, 'error' => 'Usuario y contraseña requeridos'];
        }
        if (self::isLocked($usuario)) {
            return ['ok' => false, 'error' => 'Cuenta bloqueada temporalmente. Espere ' . LOGIN_LOCK_MINUTES . ' minutos.'];
        }
        $st = Database::pdo()->prepare('SELECT * FROM administrador WHERE usuario=? AND activo=1 LIMIT 1');
        $st->execute([$usuario]);
        $found = $st->fetch();
        if (!$found || !password_verify($password, $found['contrasena_hash'])) {
            self::registerFail($usuario);
            return ['ok' => false, 'error' => 'Usuario o contraseña incorrectos'];
        }
        self::clearFails($usuario);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $found['id'];
        $_SESSION['admin_nombre'] = $found['nombre'] ?: $found['usuario'];
        $_SESSION['admin_usuario'] = $found['usuario'];
        $_SESSION['admin_auth_token'] = self::sessionToken();
        $_SESSION['login_at'] = time();
        return ['ok' => true, 'nombre' => $_SESSION['admin_nombre']];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function me(): array
    {
        if (!self::isLoggedIn()) return ['auth' => false];
        return [
            'auth' => true,
            'nombre' => $_SESSION['admin_nombre'] ?? 'Admin',
            'usuario' => $_SESSION['admin_usuario'] ?? '',
        ];
    }

    public static function changePassword(string $current, string $newPass): array
    {
        self::requireLogin();
        if (strlen($newPass) < 8) {
            return ['ok' => false, 'error' => 'La nueva contraseña debe tener al menos 8 caracteres'];
        }
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM administrador WHERE id=?');
        $st->execute([$_SESSION['admin_id']]);
        $a = $st->fetch();
        if (!$a || !password_verify($current, $a['contrasena_hash'])) {
            return ['ok' => false, 'error' => 'Contraseña actual incorrecta'];
        }
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE administrador SET contrasena_hash=? WHERE id=?')->execute([$hash, $a['id']]);
        return ['ok' => true];
    }

    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
