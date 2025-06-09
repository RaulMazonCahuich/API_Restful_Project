const API_URL = 'http://localhost:8080/api_restful_project/api/public';
const TOKEN = 'Bearer Raul12345';
let editando = { producto: null, categoria: null, proveedor: null };

const $ = id => document.getElementById(id);
const headers = { 'Authorization': TOKEN, 'Content-Type': 'application/json' };
const status = (msg, tipo = 'success') => {
  const d = $('status');
  d.innerHTML = msg;
  d.className = `status ${tipo}`;
  d.style.display = 'block';
  setTimeout(() => d.style.display = 'none', 5000);
};

const api = async (url, opt = {}) => {
  try {
    const r = await fetch(`${API_URL}/${url}`, { headers, ...opt });
    if (!r.ok) throw new Error(`Error: ${url}`);
    return opt.method === 'DELETE' || url.includes('.php') ? r : r.json();
  } catch (e) {
    status(e.message, 'error');
    return opt.method === 'DELETE' ? null : [];
  }
};

const cargarSelects = async () => {
  const [cats, provs] = await Promise.all([api('categorias'), api('proveedores')]);
  $('categoria_id').innerHTML = '<option value="">Seleccionar...</option>' + cats.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
  $('proveedor_id').innerHTML = '<option value="">Seleccionar proveedor (opcional)...</option>' + provs.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
};

const mostrar = (r, datos, campos, accs) => {
  $(r).innerHTML = datos.length ? datos.map(el => `
    <div>
      <div>${campos.map(c => `<strong>${c.label}:</strong> ${el[c.key] || 'No asignado'}`).join('<br>')}</div>
      <div>${accs.map(a => `<button onclick="${a.fn}(${el.id})">${a.label}</button>`).join('')}</div>
    </div>`).join('') : `<p>No hay ${r} registrados</p>`;
};

const toggleForm = (cid, fid, reset = true, titulo = '') => {
  const cont = $(cid), form = $(fid);
  cont.style.display = cont.style.display === 'block' ? 'none' : 'block';
  if (cont.style.display === 'block') {
    if (reset && form) form.reset();
    if (titulo) $('form-title').textContent = titulo;
  }
};

const eliminar = async (r, id) => {
  if (!confirm(`¿Eliminar este ${r.slice(0, -1)}?`)) return;
  const res = await api(`${r}/${id}`, { method: 'DELETE' });
  if (res) {
    const json = await res.json();
    json.success ? (status('Registro eliminado exitosamente'), cargarTodos()) : status('Error al eliminar', 'error');
  }
};

const cargarElemento = async (r, id, campos) => {
  const d = await api(`${r}/${id}`);
  if (d) campos.forEach(({ idCampo, key }) => $(idCampo).value = d[key] || '');
};

const config = {
  productos: {
    campos: ['nombre', 'precio', 'categoria', 'proveedor'].map(k => ({ key: k, label: k.charAt(0).toUpperCase() + k.slice(1) })),
    form: [
      { idCampo: 'nombre', key: 'nombre' },
      { idCampo: 'precio', key: 'precio', parse: parseFloat },
      { idCampo: 'categoria_id', key: 'categoria_id', parse: parseInt },
      { idCampo: 'proveedor_id', key: 'proveedor_id', parse: v => v === '' ? null : parseInt(v) }
    ]
  },
  categorias: {
    campos: [{ key: 'nombre', label: 'Nombre' }],
    form: [{ idCampo: 'categoria-nombre', key: 'nombre' }]
  },
  proveedores: {
    campos: ['nombre', 'telefono', 'email'].map(k => ({ key: k, label: k.charAt(0).toUpperCase() + k.slice(1) })),
    form: [
      { idCampo: 'proveedor-nombre', key: 'nombre' },
      { idCampo: 'proveedor-telefono', key: 'telefono' },
      { idCampo: 'proveedor-email', key: 'email' }
    ]
  }
};

const manejarSubmit = (r, fid, campos, tipo) => {
  $(fid).addEventListener('submit', async e => {
    e.preventDefault();
    const data = {};
    campos.forEach(({ idCampo, key, parse }) => {
      const val = $(idCampo).value;
      data[key] = key === 'proveedor_id' && val === '' ? null : (parse ? parse(val) : val);
    });
    const id = editando[tipo];
    const res = await api(`${r}${id ? '/' + id : ''}`, { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
    if (res?.success) {
      status(`${r.slice(0, -1)} ${id ? 'actualizado' : 'creado'} exitosamente`);
      $(fid).reset(); editando[tipo] = null;
      const c = { producto: 'formulario', categoria: 'categoria-form-container', proveedor: 'proveedor-form-container' };
      $(c[tipo]).style.display = 'none';
      cargarTodos();
    } else status('Error al guardar', 'error');
  });
};

const editar = (r, id, campos, cid, fid, tipo, titulo = '') => {
  editando[tipo] = id;
  toggleForm(cid, fid, false, titulo);
  cargarElemento(r, id, campos);
};

const cargarTodos = async () => {
  const accs = [['editarProducto', 'eliminarProducto'], ['editarCategoria', 'eliminarCategoria'], ['editarProveedor', 'eliminarProveedor']];
  const recursos = Object.keys(config);
  for (let i = 0; i < recursos.length; i++) {
    const r = recursos[i];
    mostrar(r, await api(r), config[r].campos, accs[i].map(fn => ({ fn, label: fn.includes('editar') ? 'Editar' : 'Eliminar' })));
  }
};

const generarReporte = async () => {
  const res = await api('reporte.php');
  if (res) {
    const blob = await res.blob(), url = URL.createObjectURL(blob), a = document.createElement('a');
    a.href = url; a.download = 'reporte_productos.pdf'; document.body.append(a); a.click(); a.remove();
    URL.revokeObjectURL(url);
    status('Reporte generado exitosamente');
  }
};

// Interfaz
const crear = (tipo, titulo) => {
  editando[tipo] = null;
  const f = {
    producto: ['formulario', 'product-form'],
    categoria: ['categoria-form-container', 'categoria-form'],
    proveedor: ['proveedor-form-container', 'proveedor-form']
  };
  if (tipo === 'producto') cargarSelects();
  toggleForm(f[tipo][0], f[tipo][1], true, titulo);
};

const cancelar = tipo => {
  const c = { producto: 'formulario', categoria: 'categoria-form-container', proveedor: 'proveedor-form-container' };
  $(c[tipo]).style.display = 'none';
  editando[tipo] = null;
};

window.onload = () => {
  cargarSelects(); cargarTodos();
  const ids = { productos: 'product-form', categorias: 'categoria-form', proveedores: 'proveedor-form' };
  const tipos = { productos: 'producto', categorias: 'categoria', proveedores: 'proveedor' };
  Object.keys(config).forEach(r => manejarSubmit(r, ids[r], config[r].form, tipos[r]));
};

// Funciones globales
const toggleFormulario = () => crear('producto', 'Nuevo Producto');
const toggleCategoriaForm = () => crear('categoria', 'Nueva Categoría');
const toggleProveedorForm = () => crear('proveedor', 'Nuevo Proveedor');
const cancelarFormulario = () => cancelar('producto');
const cancelarCategoriaForm = () => cancelar('categoria');
const cancelarProveedorForm = () => cancelar('proveedor');

const editarProducto = id => (cargarSelects(), editar('productos', id, config.productos.form, 'formulario', 'product-form', 'producto', 'Editar Producto'));
const editarCategoria = id => editar('categorias', id, config.categorias.form, 'categoria-form-container', 'categoria-form', 'categoria', 'Editar Categoría');
const editarProveedor = id => editar('proveedores', id, config.proveedores.form, 'proveedor-form-container', 'proveedor-form', 'proveedor', 'Editar Proveedor');
const eliminarProducto = id => eliminar('productos', id);
const eliminarCategoria = id => eliminar('categorias', id);
const eliminarProveedor = id => eliminar('proveedores', id);
