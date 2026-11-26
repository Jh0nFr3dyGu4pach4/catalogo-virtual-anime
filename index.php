<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catálogo Virtual Anime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --brand: #1F4E79; --brand2: #2E75B6; }
    body { background: #f4f6fa; }
    .navbar { background: var(--brand) !important; }
    .hero { background: linear-gradient(135deg, var(--brand), var(--brand2)); color: #fff; border-radius: 1rem; }
    .card-product { border: none; transition: .15s; cursor: pointer; }
    .card-product:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(31,78,121,.18); }
    .card-product img { height: 180px; object-fit: cover; }
    footer { background: #163a5c; color: #bcd; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark navbar-expand-lg sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-collection-play me-2"></i>Catálogo Anime</a>
    <div class="navbar-nav ms-auto">
      <a class="nav-link" href="admin/index.php"><i class="bi bi-shield-lock"></i> Admin</a>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="hero p-4 p-md-5 mb-4">
    <h1 class="h3 mb-1">Artículos coleccionables y llaveros</h1>
    <p class="mb-0 opacity-75">Anime y series · Stock en tiempo real · Sin registro</p>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-7">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="search" id="q" class="form-control" placeholder="Buscar producto o serie...">
      </div>
    </div>
    <div class="col-md-5">
      <select id="cat" class="form-select">
        <option value="">Todas las categorías</option>
      </select>
    </div>
  </div>
  <div id="chips" class="d-flex flex-wrap gap-2 mb-4"></div>
  <div id="grid" class="row g-3"></div>
  <div id="empty" class="text-center text-muted py-5 d-none">
    <i class="bi bi-inbox display-5 d-block mb-2"></i>
    <p>No hay productos para mostrar.</p>
  </div>
</main>

<footer class="py-3 mt-5">
  <div class="container text-center small">Catálogo Virtual Anime · Ingeniería de Software 1</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = 'api/index.php?r=';
function money(n){ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(n); }

async function loadCats(){
  const res = await fetch(API + 'categorias');
  const cats = await res.json();
  const sel = document.getElementById('cat');
  const chips = document.getElementById('chips');
  chips.innerHTML = '';
  const all = document.createElement('button');
  all.className = 'btn btn-sm btn-primary';
  all.textContent = 'Todas';
  all.onclick = ()=>{ sel.value=''; loadProducts(); document.querySelectorAll('#chips .btn').forEach(b=>b.className='btn btn-sm btn-outline-primary'); all.className='btn btn-sm btn-primary'; };
  chips.appendChild(all);
  cats.forEach(c=>{
    const o = document.createElement('option');
    o.value = c.id; o.textContent = c.nombre; sel.appendChild(o);
    const b = document.createElement('button');
    b.className = 'btn btn-sm btn-outline-primary';
    b.textContent = c.nombre;
    b.onclick = ()=>{ sel.value=c.id; loadProducts(); document.querySelectorAll('#chips .btn').forEach(x=>x.className='btn btn-sm btn-outline-primary'); b.className='btn btn-sm btn-primary'; };
    chips.appendChild(b);
  });
}

async function loadProducts(){
  const q = document.getElementById('q').value.trim();
  const cat = document.getElementById('cat').value;
  let url = API + 'productos';
  const p = [];
  if(q) p.push('q='+encodeURIComponent(q));
  if(cat) p.push('categoria_id='+cat);
  if(p.length) url += '&' + p.join('&');
  const res = await fetch(url);
  const list = await res.json();
  const grid = document.getElementById('grid');
  const empty = document.getElementById('empty');
  grid.innerHTML = '';
  if(!list.length){ empty.classList.remove('d-none'); return; }
  empty.classList.add('d-none');
  list.forEach(p=>{
    const badge = (p.stock>0 && p.estado==='disponible')
      ? `<span class="badge bg-success">Stock: ${p.stock}</span>`
      : `<span class="badge bg-danger">Agotado</span>`;
    const col = document.createElement('div');
    col.className = 'col-6 col-md-4 col-lg-3';
    const img = p.imagen_url || 'https://placehold.co/400x400?text=Producto';
    col.innerHTML = `<a href="detalle.php?id=${p.id}" class="text-decoration-none text-dark">
      <div class="card card-product h-100 shadow-sm">
        <img src="${img}" class="card-img-top" alt="" onerror="this.src='https://placehold.co/400x400?text=Producto'">
        <div class="card-body">
          <div class="small text-muted">${p.categoria_nombre||''}</div>
          <h2 class="h6">${p.nombre}</h2>
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold text-primary">${money(p.precio)}</span>
            ${badge}
          </div>
        </div>
      </div></a>`;
    grid.appendChild(col);
  });
}

document.getElementById('q').addEventListener('input', loadProducts);
document.getElementById('cat').addEventListener('change', loadProducts);
loadCats().then(loadProducts);
</script>
</body>
</html>
