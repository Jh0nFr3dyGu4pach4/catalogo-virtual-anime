<?php
require_once __DIR__ . '/../includes/config.php';
if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg,#1F4E79,#2E75B6); min-height:100vh; display:flex; align-items:center; }
    .card { border:none; border-radius:1rem; }
  </style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow-lg p-4">
        <h1 class="h4 text-center mb-3">Administración</h1>
        <p class="text-muted text-center small">Catálogo Virtual Anime</p>
        <div id="alert" class="alert alert-danger d-none"></div>
        <form id="form">
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" class="form-control" id="usuario" required autocomplete="username">
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" required autocomplete="current-password">
          </div>
          <button class="btn btn-primary w-100" type="submit">Ingresar</button>
        </form>
        <div class="text-center mt-3">
          <a href="../index.php" class="small">← Volver al catálogo</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('form').onsubmit = async (e)=>{
  e.preventDefault();
  const alert = document.getElementById('alert');
  alert.classList.add('d-none');
  const res = await fetch('../api/index.php?r=auth/login', {
    credentials: 'same-origin',
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({
      usuario: document.getElementById('usuario').value.trim(),
      password: document.getElementById('password').value
    })
  });
  const data = await res.json();
  if(!res.ok){
    alert.textContent = data.error || 'Error de acceso';
    alert.classList.remove('d-none');
    return;
  }
  location.href = 'index.php';
};
</script>
</body>
</html>
