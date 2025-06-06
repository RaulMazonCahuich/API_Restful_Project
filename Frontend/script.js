// Configuración de la API
const API_URL = 'http://localhost:8080/api_restful_project/api/public';
const TOKEN = 'Bearer Raul12345';

let editandoId = null;

// Headers para las peticiones
function getHeaders() {
    return {
        'Authorization': TOKEN,
        'Content-Type': 'application/json'
    };
}

// Mostrar mensajes de estado
function mostrarEstado(mensaje, tipo = 'success') {
    const statusDiv = document.getElementById('status');
    statusDiv.innerHTML = mensaje;
    statusDiv.className = `status ${tipo}`;
    statusDiv.style.display = 'block';
    
    setTimeout(() => {
        statusDiv.style.display = 'none';
    }, 5000);
}

// Cargar productos
async function cargarProductos() {
    try {
        const response = await fetch(`${API_URL}/index.php`, {
            method: 'GET',
            headers: getHeaders()
        });
        
        if (!response.ok) {
            throw new Error('Error al cargar productos');
        }
        
        const productos = await response.json();
        mostrarProductos(productos);
    } catch (error) {
        console.error('Error:', error);
        mostrarEstado('Error cargando productos: ' + error.message, 'error');
    }
}

// Mostrar productos en el DOM
function mostrarProductos(productos) {
    const container = document.getElementById('productos');
    
    if (productos.length === 0) {
        container.innerHTML = '<p>No hay productos registrados</p>';
        return;
    }
    
    let html = '';
    productos.forEach(producto => {
        html += `
            <div class="product-item">
                <div class="product-info">
                    <strong>${producto.nombre}</strong><br>
                    Precio: $${parseFloat(producto.precio).toFixed(2)}<br>
                    Categoría: ${producto.categoria}
                </div>
                <div class="product-actions">
                    <button class="btn btn-primary btn-small" onclick="editarProducto(${producto.id})">
                        Editar
                    </button>
                    <button class="btn btn-danger btn-small" onclick="eliminarProducto(${producto.id})">
                        Eliminar
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Mostrar/ocultar formulario
function toggleFormulario() {
    const formulario = document.getElementById('formulario');
    const isVisible = formulario.style.display === 'block';
    formulario.style.display = isVisible ? 'none' : 'block';
    
    if (!isVisible) {
        document.getElementById('form-title').textContent = 'Nuevo Producto';
        document.getElementById('product-form').reset();
        editandoId = null;
    }
}

// Cancelar formulario
function cancelarFormulario() {
    document.getElementById('formulario').style.display = 'none';
    document.getElementById('product-form').reset();
    editandoId = null;
}

// Cargar datos para editar
async function editarProducto(id) {
    try {
        const response = await fetch(`${API_URL}/index.php?id=${id}`, {
            method: 'GET',
            headers: getHeaders()
        });
        
        if (!response.ok) {
            throw new Error('Error al cargar el producto');
        }
        
        const producto = await response.json();
        
        // Llenar el formulario
        document.getElementById('product-id').value = id;
        document.getElementById('nombre').value = producto.nombre;
        document.getElementById('precio').value = producto.precio;
        document.getElementById('categoria_id').value = producto.categoria_id;
        document.getElementById('form-title').textContent = 'Editar Producto';
        document.getElementById('formulario').style.display = 'block';
        
        editandoId = id;
    } catch (error) {
        mostrarEstado('Error cargando producto: ' + error.message, 'error');
    }
}

// Eliminar producto
async function eliminarProducto(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/index.php?id=${id}`, {
            method: 'DELETE',
            headers: getHeaders()
        });
        
        if (!response.ok) {
            throw new Error('Error al eliminar producto');
        }
        
        mostrarEstado('Producto eliminado exitosamente');
        cargarProductos();
    } catch (error) {
        mostrarEstado('Error eliminando producto: ' + error.message, 'error');
    }
}

// Generar reporte PDF
async function generarReporte() {
    mostrarEstado('Generando reporte...', 'success');
    
    try {
        const response = await fetch(`${API_URL}/reporte.php`, {
            method: 'GET',
            headers: {
                'Authorization': TOKEN
            }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(errorText);
        }
        
        // Descargar el PDF
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'reporte_productos.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        mostrarEstado('Reporte generado exitosamente');
    } catch (error) {
        console.error('Error:', error);
        mostrarEstado('Error generando reporte: ' + error.message, 'error');
    }
}

// Manejar envío del formulario
document.getElementById('product-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        nombre: document.getElementById('nombre').value,
        precio: parseFloat(document.getElementById('precio').value),
        categoria_id: parseInt(document.getElementById('categoria_id').value)
    };
    
    try {
        let response;
        
        if (editandoId) {
            // Actualizar producto
            response = await fetch(`${API_URL}/index.php?id=${editandoId}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(formData)
            });
        } else {
            // Crear nuevo producto
            response = await fetch(`${API_URL}/index.php`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify(formData)
            });
        }
        
        if (!response.ok) {
            throw new Error('Error al guardar el producto');
        }
        
        const result = await response.json();
        
        if (result.success) {
            mostrarEstado(editandoId ? 'Producto actualizado exitosamente' : 'Producto creado exitosamente');
            cancelarFormulario();
            cargarProductos();
        } else {
            throw new Error('Error en la operación');
        }
    } catch (error) {
        mostrarEstado('Error guardando producto: ' + error.message, 'error');
    }
});

// Cargar productos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarProductos();
});