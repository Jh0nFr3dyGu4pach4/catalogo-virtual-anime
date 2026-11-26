<?php
require_once __DIR__ . '/../includes/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['r']) ? trim($_GET['r'], '/') : '';
$parts = $path ? explode('/', $path) : [];
$resource = $parts[0] ?? '';
$id = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$action = isset($parts[1]) && !is_numeric($parts[1]) ? $parts[1] : null;
$body = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    if ($resource === 'categorias') {
        if ($method === 'GET') Auth::json(Catalog::categories());
        Auth::requireLogin();
        if ($method === 'POST') {
            $r = Catalog::saveCategory(null, $body);
            Auth::json($r, $r['ok'] ? 201 : 400);
        }
        if ($method === 'PUT' && $id) {
            $r = Catalog::saveCategory($id, $body);
            Auth::json($r, $r['ok'] ? 200 : 400);
        }
        if ($method === 'DELETE' && $id) {
            $r = Catalog::deleteCategory($id);
            Auth::json($r, $r['ok'] ? 200 : 400);
        }
    }

    if ($resource === 'productos') {
        if ($method === 'GET' && !$id) {
            Auth::json(Catalog::products([
                'q' => $_GET['q'] ?? '',
                'categoria_id' => $_GET['categoria_id'] ?? 0,
            ]));
        }
        if ($method === 'GET' && $id) {
            $p = Catalog::product($id);
            if (!$p) Auth::json(['error' => 'No encontrado'], 404);
            Auth::json($p);
        }
        Auth::requireLogin();
        if ($method === 'POST') {
            $r = Catalog::saveProduct(null, $body);
            Auth::json($r, $r['ok'] ? 201 : 400);
        }
        if ($method === 'PUT' && $id) {
            $r = Catalog::saveProduct($id, $body);
            Auth::json($r, $r['ok'] ? 200 : 400);
        }
        if ($method === 'DELETE' && $id) {
            Auth::json(Catalog::deleteProduct($id));
        }
    }

    if ($resource === 'auth') {
        if ($action === 'login' && $method === 'POST') {
            $r = Auth::login($body['usuario'] ?? '', $body['password'] ?? '');
            Auth::json($r, $r['ok'] ? 200 : 401);
        }
        if ($action === 'logout' && $method === 'POST') {
            Auth::logout();
            Auth::json(['ok' => true]);
        }
        if ($action === 'me' && $method === 'GET') {
            Auth::json(Auth::me());
        }
        if ($action === 'password' && $method === 'POST') {
            Auth::requireLogin();
            $r = Auth::changePassword($body['actual'] ?? '', $body['nueva'] ?? '');
            Auth::json($r, $r['ok'] ? 200 : 400);
        }
    }

    if ($resource === 'cotizaciones' && $method === 'POST') {
        $r = Orders::createQuote($body);
        Auth::json($r, $r['ok'] ? 200 : 400);
    }

    if ($resource === 'pagos' && $method === 'POST') {
        $r = Orders::createPayment($body);
        Auth::json($r, $r['ok'] ? 200 : 400);
    }


    if ($resource === 'upload' && $method === 'POST') {
        Auth::requireLogin();
        if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            Auth::json(['error' => 'No se recibio ninguna imagen valida'], 400);
        }
        $file = $_FILES['imagen'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowed[$mime])) {
            Auth::json(['error' => 'Formato no permitido. Use JPG, PNG, WEBP o GIF'], 400);
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            Auth::json(['error' => 'La imagen no debe superar 5 MB'], 400);
        }
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
        $name = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $dest = UPLOAD_DIR . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Auth::json(['error' => 'No se pudo guardar la imagen'], 500);
        }
        $url = 'assets/uploads/' . $name;
        Auth::json(['ok' => true, 'url' => $url, 'path' => $url]);
    }

    if ($resource === 'settings') {
        if ($method === 'GET') {
            Auth::json(Settings::publicSettings());
        }
        if ($method === 'PUT') {
            Auth::requireLogin();
            Auth::json(Settings::save($body));
        }
    }

    Auth::json(['error' => 'Ruta no encontrada'], 404);
} catch (Exception $e) {
    Auth::json(['error' => $e->getMessage()], 500);
}
