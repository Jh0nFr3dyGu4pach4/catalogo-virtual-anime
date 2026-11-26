<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalación — Catálogo Anime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:720px">
  <h1 class="h3 mb-3">Instalación de base de datos MySQL</h1>
  <p class="text-muted">Esta página importa el esquema y los datos iniciales. Úsala una sola vez.</p>
<?php
$cfgFile = __DIR__ . '/includes/db_config.php';
$cfg = file_exists($cfgFile) ? require $cfgFile : null;
$msg = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? '127.0.0.1');
    $port = trim($_POST['port'] ?? '3306');
    $name = trim($_POST['name'] ?? 'catalogo_anime');
    $user = trim($_POST['user'] ?? 'root');
    $pass = $_POST['pass'] ?? '';

    $newCfg = "<?php\nreturn [\n    'host' => " . var_export($host, true) . ",\n    'port' => " . var_export($port, true) . ",\n    'name' => " . var_export($name, true) . ",\n    'user' => " . var_export($user, true) . ",\n    'pass' => " . var_export($pass, true) . ",\n    'charset' => 'utf8mb4',\n];\n";
    file_put_contents($cfgFile, $newCfg);

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $sql = file_get_contents(__DIR__ . '/sql/modelo_mysql.sql');
        // Ejecutar por sentencias
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt === '' || str_starts_with($stmt, '--')) continue;
            $pdo->exec($stmt);
        }
        $ok = true;
        $msg = 'Base de datos creada e importada correctamente. Ya puedes usar la aplicación.';
    } catch (Throwable $e) {
        $msg = 'Error: ' . $e->getMessage();
    }
    $cfg = require $cfgFile;
}
?>
  <?php if ($msg): ?>
    <div class="alert alert-<?= $ok ? 'success' : 'danger' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php if ($ok): ?>
      <a class="btn btn-primary" href="index.php">Ir al catálogo</a>
      <a class="btn btn-outline-secondary" href="admin/login.php">Ir al admin</a>
      <p class="small text-muted mt-3">Por seguridad, borra o renombra <code>install.php</code> después de instalar.</p>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (!$ok): ?>
  <form method="post" class="card card-body shadow-sm">
    <div class="mb-3">
      <label class="form-label">Host</label>
      <input name="host" class="form-control" value="<?= htmlspecialchars($cfg['host'] ?? '127.0.0.1') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Puerto</label>
      <input name="port" class="form-control" value="<?= htmlspecialchars($cfg['port'] ?? '3306') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Nombre BD</label>
      <input name="name" class="form-control" value="<?= htmlspecialchars($cfg['name'] ?? 'catalogo_anime') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Usuario</label>
      <input name="user" class="form-control" value="<?= htmlspecialchars($cfg['user'] ?? 'root') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input name="pass" type="password" class="form-control" value="<?= htmlspecialchars($cfg['pass'] ?? '') ?>" placeholder="(vacía en XAMPP)">
    </div>
    <button class="btn btn-primary">Crear base de datos e importar</button>
  </form>
  <p class="small text-muted mt-3">Requisitos: MySQL/MariaDB en marcha (XAMPP: inicia MySQL) y extensión PHP <code>pdo_mysql</code>.</p>
  <?php endif; ?>
</div>
</body>
</html>
