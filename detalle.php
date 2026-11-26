<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --brand: #1F4E79; }
    body { background: #f4f6fa; }
    .navbar { background: var(--brand) !important; }
    .img-box { max-height: 420px; object-fit: contain; background:#fff; border-radius:.75rem; width:100%; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-collection-play me-2"></i>Catálogo Anime</a>
    <a class="nav-link text-white" href="index.php"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>
</nav>
<main class="container py-4">
  <div id="content" class="row g-4"><div class="col-12 text-center text-muted py-5">Cargando...</div></div>
</main>
<script>
const API = 'api/index.php?r=';
function money(n){ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(n); }
const id = new URLSearchParams(location.search).get('id');

async function load(){
  const res = await fetch(API + 'productos/' + id);
  const p = await res.json();
  const box = document.getElementById('content');
  if(p.error){
    box.innerHTML = `<div class="col-12 text-center"><p>Producto no disponible</p><a href="index.php" class="btn btn-primary">Volver</a></div>`;
    return;
  }
  const ok = p.stock > 0 && p.estado === 'disponible';
  const stock = ok
    ? `<span class="text-success fw-semibold"><i class="bi bi-check-circle"></i> Disponible · ${p.stock} unidades</span>`
    : `<span class="text-danger fw-semibold"><i class="bi bi-x-circle"></i> Agotado</span>`;
  const img = p.imagen_url || 'https://placehold.co/600x600?text=Producto';
  box.innerHTML = `
    <div class="col-md-6">
      <img class="img-box shadow-sm" src="${img}" onerror="this.src='https://placehold.co/600x600?text=Producto'" alt="">
    </div>
    <div class="col-md-6">
      <div class="small text-muted">${p.categoria_nombre||''}</div>
      <h1 class="h3">${p.nombre}</h1>
      <p class="display-6 text-primary fw-bold">${money(p.precio)}</p>
      <p>${stock}</p>
      <p class="text-secondary">${p.descripcion||'Sin descripción'}</p>
      <dl class="row small">
        <dt class="col-4">Material</dt><dd class="col-8">${p.material||'—'}</dd>
        <dt class="col-4">Estado</dt><dd class="col-8 text-capitalize">${p.estado}</dd>
      </dl>
      <div class="d-flex gap-2 mt-3">
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        ${ok ? `<button class="btn btn-success" id="btnWa"><i class="bi bi-whatsapp"></i> Cotizar por WhatsApp</button>` : ''}
      </div>
      <div class="mt-3" id="qtyBox" style="display:${ok?'block':'none'}">
        <label class="form-label">Cantidad</label>
        <input type="number" id="qty" class="form-control w-auto" min="1" max="${p.stock}" value="1">
      </div>
    </div>`;
  const btn = document.getElementById('btnWa');
  if(btn){
    btn.onclick = async ()=>{
      const cant = Math.min(parseInt(document.getElementById('qty').value||1,10), p.stock);
      const res = await fetch(API + 'cotizaciones', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ items:[{producto_id:p.id, cantidad:cant}] })
      });
      const data = await res.json();
      if(data.error){ alert(data.error); return; }
      window.open(data.whatsapp_url, '_blank');
    };
  }
}
load();
</script>
</body>
</html>
