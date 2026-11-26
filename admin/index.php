<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --brand: #1F4E79; }
    body { background: #f4f6fa; }
    .navbar { background: var(--brand) !important; }
    .table thead { background: var(--brand); color:#fff; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark sticky-top">
  <div class="container-fluid px-4">
    <span class="navbar-brand mb-0"><i class="bi bi-gear me-2"></i>Admin · <span id="adminName"></span></span>
    <div>
      <a href="../index.php" class="btn btn-sm btn-outline-light me-2">Ver catálogo</a>
      <button class="btn btn-sm btn-light" id="btnLogout">Salir</button>
    </div>
  </div>
</nav>

<main class="container-fluid px-4 py-4">
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabProd">Productos</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCat">Categorías</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCfg">Configuración</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSec">Seguridad</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabProd">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Gestión de productos</h2>
        <button class="btn btn-primary btn-sm" id="btnNewProd"><i class="bi bi-plus-lg"></i> Nuevo</button>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead><tr>
              <th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th class="text-end">Acciones</th>
            </tr></thead>
            <tbody id="tbodyProd"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="tabCat">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Categorías</h2>
        <button class="btn btn-primary btn-sm" id="btnNewCat"><i class="bi bi-plus-lg"></i> Nueva</button>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th class="text-end">Acciones</th></tr></thead>
            <tbody id="tbodyCat"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="tabCfg">
      <h2 class="h5 mb-3">Configuración de la tienda</h2>
      <div class="card border-0 shadow-sm p-4" style="max-width:640px">
        <div class="mb-3">
          <label class="form-label">Nombre de la tienda</label>
          <input type="text" class="form-control" id="cfgNombre">
        </div>
        <div class="mb-3">
          <label class="form-label">WhatsApp del vendedor (solo números, sin +57)</label>
          <input type="text" class="form-control" id="cfgWa" placeholder="3001234567">
          <div class="form-text">Se usa en las cotizaciones del catálogo público.</div>
        </div>
        <hr>
        <h3 class="h6">Pasarela de pagos</h3>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="cfgPasarela">
          <label class="form-check-label" for="cfgPasarela">Activar pasarela de pagos</label>
        </div>
        <div class="mb-3">
          <label class="form-label">Proveedor</label>
          <select class="form-select" id="cfgProveedor">
            <option value="wompi">Wompi</option>
            <option value="mercadopago">Mercado Pago</option>
            <option value="paypal">PayPal</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Public Key</label>
          <input type="text" class="form-control" id="cfgPubKey" placeholder="pub_test_...">
        </div>
        <div class="mb-3">
          <label class="form-label">Private Key</label>
          <input type="password" class="form-control" id="cfgPrivKey" placeholder="prv_test_...">
        </div>
        <button class="btn btn-primary" id="btnSaveCfg">Guardar configuración</button>
        <div id="cfgMsg" class="mt-2 small"></div>
      </div>
    </div>

    <div class="tab-pane fade" id="tabSec">
      <h2 class="h5 mb-3">Seguridad — Cambiar contraseña</h2>
      <div class="card border-0 shadow-sm p-4" style="max-width:420px">
        <div class="mb-3">
          <label class="form-label">Contraseña actual</label>
          <input type="password" class="form-control" id="secActual">
        </div>
        <div class="mb-3">
          <label class="form-label">Nueva contraseña (mín. 8 caracteres)</label>
          <input type="password" class="form-control" id="secNueva">
        </div>
        <button class="btn btn-warning" id="btnPass">Actualizar contraseña</button>
        <div id="secMsg" class="mt-2 small"></div>
        <p class="text-muted small mt-3 mb-0">La autenticación usa hash bcrypt, sesión regenerada al login, bloqueo tras 5 intentos fallidos (15 min) y cookie HttpOnly.</p>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalProd" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="titleProd">Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="prodId">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Nombre *</label>
            <input type="text" class="form-control" id="prodNombre" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Categoría *</label>
            <select class="form-select" id="prodCat" required></select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Precio *</label>
            <input type="number" class="form-control" id="prodPrecio" min="0" step="100" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Stock *</label>
            <input type="number" class="form-control" id="prodStock" min="0" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select class="form-select" id="prodEstado">
              <option value="disponible">Disponible</option>
              <option value="agotado">Agotado</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Material</label>
            <input type="text" class="form-control" id="prodMaterial">
          </div>
          <div class="col-md-6">
            <label class="form-label">Imagen del producto</label>
            <input type="file" class="form-control" id="prodImgFile" accept="image/jpeg,image/png,image/webp,image/gif">
            <div class="form-text">JPG, PNG, WEBP o GIF · máx. 5 MB</div>
            <input type="hidden" id="prodImg" value="">
            <img id="prodImgPreview" src="" alt="" class="img-thumbnail mt-2 d-none" style="max-height:120px">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" id="prodDesc" rows="3"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnSaveProd">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCat" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="titleCat">Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="catId">
        <div class="mb-3">
          <label class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="catNombre" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <input type="text" class="form-control" id="catDesc">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnSaveCat">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = '../api/index.php?r=';
async function api(url, opts={}) {
  opts.credentials = 'same-origin';
  if (opts.body && !(opts.body instanceof FormData) && typeof opts.body === 'object') {
    opts.headers = Object.assign({'Content-Type':'application/json'}, opts.headers||{});
    opts.body = JSON.stringify(opts.body);
  }
  return fetch(url, opts);
}

const modalProd = new bootstrap.Modal('#modalProd');
const modalCat = new bootstrap.Modal('#modalCat');
function money(n){ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(n); }

async function checkAuth(){
  const res = await api(API + 'auth/me');
  const data = await res.json();
  if(!data.auth){ location.href = 'login.php'; return; }
  document.getElementById('adminName').textContent = data.nombre;
}

async function loadCatsSelect(){
  const res = await api(API + 'categorias');
  const cats = await res.json();
  const sel = document.getElementById('prodCat');
  sel.innerHTML = '';
  cats.forEach(c=>{
    const o = document.createElement('option');
    o.value = c.id; o.textContent = c.nombre; sel.appendChild(o);
  });
  return cats;
}

async function loadProds(){
  const res = await api(API + 'productos');
  const list = await res.json();
  const tb = document.getElementById('tbodyProd');
  tb.innerHTML = '';
  list.forEach(p=>{
    const badge = (p.estado==='disponible' && p.stock>0)
      ? '<span class="badge bg-success">Disponible</span>'
      : '<span class="badge bg-danger">Agotado</span>';
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${p.id}</td><td>${p.nombre}</td><td>${p.categoria_nombre||''}</td>
      <td>${money(p.precio)}</td><td>${p.stock}</td><td>${badge}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-outline-primary me-1" onclick='editProd(${JSON.stringify(p).replace(/'/g,"&#39;")})'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick="delProd(${p.id})"><i class="bi bi-trash"></i></button>
      </td>`;
    tb.appendChild(tr);
  });
}

async function loadCatsTable(){
  const res = await api(API + 'categorias');
  const list = await res.json();
  const tb = document.getElementById('tbodyCat');
  tb.innerHTML = '';
  list.forEach(c=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${c.id}</td><td>${c.nombre}</td><td>${c.descripcion||''}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-outline-primary me-1" onclick="editCat(${c.id},'${(c.nombre||'').replace(/'/g,"\\'")}','${(c.descripcion||'').replace(/'/g,"\\'")}')"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick="delCat(${c.id})"><i class="bi bi-trash"></i></button>
      </td>`;
    tb.appendChild(tr);
  });
}

function editProd(p){
  document.getElementById('titleProd').textContent = 'Editar producto';
  document.getElementById('prodId').value = p.id;
  document.getElementById('prodNombre').value = p.nombre;
  document.getElementById('prodCat').value = p.categoria_id;
  document.getElementById('prodPrecio').value = p.precio;
  document.getElementById('prodStock').value = p.stock;
  document.getElementById('prodEstado').value = p.estado;
  document.getElementById('prodMaterial').value = p.material||'';
  document.getElementById('prodImg').value = p.imagen_url||'';
  document.getElementById('prodImgFile').value = '';
  const prev = document.getElementById('prodImgPreview');
  if (p.imagen_url) {
    prev.src = p.imagen_url.startsWith('http') ? p.imagen_url : '../' + p.imagen_url;
    prev.classList.remove('d-none');
  } else {
    prev.src = '';
    prev.classList.add('d-none');
  }
  document.getElementById('prodDesc').value = p.descripcion||'';
  modalProd.show();
}

document.getElementById('btnNewProd').onclick = async ()=>{
  await loadCatsSelect();
  document.getElementById('titleProd').textContent = 'Nuevo producto';
  document.getElementById('prodId').value = '';
  document.getElementById('prodNombre').value = '';
  document.getElementById('prodPrecio').value = '';
  document.getElementById('prodStock').value = '0';
  document.getElementById('prodEstado').value = 'disponible';
  document.getElementById('prodMaterial').value = '';
  document.getElementById('prodImg').value = '';
  document.getElementById('prodImgFile').value = '';
  document.getElementById('prodImgPreview').classList.add('d-none');
  document.getElementById('prodDesc').value = '';
  modalProd.show();
};

document.getElementById('btnSaveProd').onclick = async ()=>{
  const id = document.getElementById('prodId').value;
  let imagen_url = document.getElementById('prodImg').value.trim();
  const fileInput = document.getElementById('prodImgFile');
  if (fileInput && fileInput.files && fileInput.files[0]) {
    const fd = new FormData();
    fd.append('imagen', fileInput.files[0]);
    const up = await api(API + 'upload', { method: 'POST', body: fd });
    const upData = await up.json();
    if (!up.ok) { alert(upData.error || 'Error al subir imagen'); return; }
    imagen_url = upData.url;
  }
  const payload = {
    nombre: document.getElementById('prodNombre').value.trim(),
    categoria_id: +document.getElementById('prodCat').value,
    precio: +document.getElementById('prodPrecio').value,
    stock: +document.getElementById('prodStock').value,
    estado: document.getElementById('prodEstado').value,
    material: document.getElementById('prodMaterial').value.trim(),
    imagen_url: imagen_url,
    descripcion: document.getElementById('prodDesc').value.trim()
  };
  if(!payload.nombre || !payload.categoria_id){ alert('Completa nombre y categoría'); return; }
  const url = API + 'productos' + (id ? '/'+id : '');
  const res = await api(url, {
    method: id ? 'PUT' : 'POST',
    body: payload
  });
  const data = await res.json();
  if(!res.ok){ alert(data.error||'Error'); return; }
  modalProd.hide();
  loadProds();
};

async function delProd(id){
  if(!confirm('¿Eliminar este producto?')) return;
  await api(API + 'productos/' + id, { method:'DELETE' });
  loadProds();
}

document.getElementById('btnNewCat').onclick = ()=>{
  document.getElementById('titleCat').textContent = 'Nueva categoría';
  document.getElementById('catId').value = '';
  document.getElementById('catNombre').value = '';
  document.getElementById('catDesc').value = '';
  modalCat.show();
};

function editCat(id, nombre, desc){
  document.getElementById('titleCat').textContent = 'Editar categoría';
  document.getElementById('catId').value = id;
  document.getElementById('catNombre').value = nombre;
  document.getElementById('catDesc').value = desc;
  modalCat.show();
}

document.getElementById('btnSaveCat').onclick = async ()=>{
  const id = document.getElementById('catId').value;
  const payload = {
    nombre: document.getElementById('catNombre').value.trim(),
    descripcion: document.getElementById('catDesc').value.trim()
  };
  if(!payload.nombre){ alert('Nombre requerido'); return; }
  const url = API + 'categorias' + (id ? '/'+id : '');
  const res = await api(url, {
    method: id ? 'PUT' : 'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  if(!res.ok){ alert(data.error||'Error'); return; }
  modalCat.hide();
  loadCatsTable();
  loadCatsSelect();
};

async function delCat(id){
  if(!confirm('¿Eliminar categoría?')) return;
  const res = await api(API + 'categorias/' + id, { method:'DELETE' });
  const data = await res.json();
  if(!res.ok){ alert(data.error||'Error'); return; }
  loadCatsTable();
}

document.getElementById('btnLogout').onclick = async ()=>{
  await api(API + 'auth/logout', { method:'POST' });
  location.href = 'login.php';
};


document.getElementById('prodImgFile').addEventListener('change', function(){
  const prev = document.getElementById('prodImgPreview');
  if (this.files && this.files[0]) {
    prev.src = URL.createObjectURL(this.files[0]);
    prev.classList.remove('d-none');
  }
});

checkAuth().then(async ()=>{
  await loadCatsSelect();
  loadProds();
  loadCatsTable();
  loadCfg();
});

async function loadCfg(){
  const res = await api(API + 'settings');
  const s = await res.json();
  document.getElementById('cfgNombre').value = s.nombre_tienda || '';
  document.getElementById('cfgWa').value = s.whatsapp_vendedor || '';
  document.getElementById('cfgPasarela').checked = !!s.pasarela_activa;
  document.getElementById('cfgProveedor').value = s.pasarela_proveedor || 'wompi';
  document.getElementById('cfgPubKey').value = s.pasarela_public_key || '';
}
document.getElementById('btnSaveCfg').onclick = async ()=>{
  const payload = {
    nombre_tienda: document.getElementById('cfgNombre').value.trim(),
    whatsapp_vendedor: document.getElementById('cfgWa').value.trim(),
    pasarela_activa: document.getElementById('cfgPasarela').checked,
    pasarela_proveedor: document.getElementById('cfgProveedor').value,
    pasarela_public_key: document.getElementById('cfgPubKey').value.trim(),
    pasarela_private_key: document.getElementById('cfgPrivKey').value.trim()
  };
  const res = await api(API + 'settings', { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
  const data = await res.json();
  const msg = document.getElementById('cfgMsg');
  if(res.ok){ msg.className='mt-2 small text-success'; msg.textContent='Configuración guardada.'; }
  else { msg.className='mt-2 small text-danger'; msg.textContent=data.error||'Error'; }
};
document.getElementById('btnPass').onclick = async ()=>{
  const res = await api(API + 'auth/password', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ actual: document.getElementById('secActual').value, nueva: document.getElementById('secNueva').value })
  });
  const data = await res.json();
  const msg = document.getElementById('secMsg');
  if(res.ok){ msg.className='mt-2 small text-success'; msg.textContent='Contraseña actualizada.'; document.getElementById('secActual').value=''; document.getElementById('secNueva').value=''; }
  else { msg.className='mt-2 small text-danger'; msg.textContent=data.error||'Error'; }
};

</script>
</body>
</html>
