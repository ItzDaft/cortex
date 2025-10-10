<?php CSRFHelper::getTokenInput(); ?> 

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestión de Usuarios test</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">Crear Nuevo Usuario</button>
</div>

<div id="mensaje-usuarios"></div>
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total de Usuarios</h5>
                <p class="card-text fs-4"><?php echo $estadisticasRoles['Total'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <?php 
    foreach ($roles as $rol):
        $nombreRol = $rol['nombre_rol'];
        $cantidad = $estadisticasRoles[$nombreRol] ?? 0;
    ?>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-light h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($nombreRol); ?></h5>
                <p class="card-text fs-4"><?php echo $cantidad; ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php
$ordenDeRoles = ['Administrador', 'Coordinador', 'Coordinador de Area', 'Revisor de Pagos', 'Autor', 'Asistente','Asistente con Cartel'];

foreach ($ordenDeRoles as $rolNombre):
    $listaUsuarios = $usuariosPorRol[$rolNombre] ?? [];
?>

<h3 class="mt-5"><?php echo htmlspecialchars($rolNombre); ?></h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th> <th>ID</th><th>Nombre</th><th>Correo</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="tabla-usuarios-body">
                    <?php if (empty($listaUsuarios)): ?>
                        <tr><td colspan="6" class="text-center">No hay usuarios con este rol.</td></tr>
                    <?php else: ?>
                        <?php $contador = 1; ?>

                        <?php foreach ($listaUsuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $contador; ?></td>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                <td>
                                    <?php echo $usuario['activo'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-editar" data-id="<?php echo $usuario['id']; ?>">Editar</button>
                                    <button class="btn btn-sm btn-success btn-generar-pago" data-id="<?php echo $usuario['id']; ?>" data-nombre="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>">Generar Pago</button>

                                    <?php if ($usuario['activo'] == 1): ?>
                                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="<?php echo $usuario['id']; ?>">Desactivar</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-info btn-reactivar" data-id="<?php echo $usuario['id']; ?>">Reactivar</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php $contador++; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endforeach; ?>


<div class="modal fade" id="crearUsuarioModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Crear Nuevo Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="crearUsuarioForm">
            <div class="mb-3"><label for="nombre" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="nombre" required></div>
            <div class="mb-3"><label for="correo" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="correo" required></div>
            <div class="mb-3"><label for="institucion" class="form-label">Institución</label><input type="text" class="form-control" id="institucion"></div>

            <div class="mb-3">
                <label for="rol_id_crear" class="form-label">Rol</label>
                <select id="rol_id_crear" class="form-select" required>
                    <option value="">Seleccione un rol...</option>
                    <?php foreach($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>" data-rol-nombre="<?php echo htmlspecialchars($rol['nombre_rol']); ?>">
                            <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 d-none" id="area-container-crear">
                <label for="area_id_crear" class="form-label">Área de Especialización (para Coordinadores de Area)</label>
                <select id="area_id_crear" class="form-select">
                    <option value="">Seleccione un área...</option>
                    <?php foreach($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre_area']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" form="crearUsuarioForm" id="crearUsuarioBtn" class="btn btn-primary">Crear Usuario</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editarUsuarioModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Editar Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="editarUsuarioForm">
            <input type="hidden" id="edit_usuario_id">
            <div class="mb-3"><label for="edit_nombre" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="edit_nombre" required></div>
            <div class="mb-3"><label for="edit_correo" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="edit_correo" required></div>
            <div class="mb-3"><label for="edit_institucion" class="form-label">Institución</label><input type="text" class="form-control" id="edit_institucion"></div>

            <div class="mb-3">
                <label for="rol_id_editar" class="form-label">Rol</label>
                <select id="rol_id_editar" class="form-select" required>
                    <option value="">Seleccione un rol...</option>
                    <?php foreach($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>" data-rol-nombre="<?php echo htmlspecialchars($rol['nombre_rol']); ?>">
                            <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Área de Especialización (solo para Coordinador de Areaes) -->
            <div class="mb-3 d-none" id="area-container-editar">
                <label for="area_id_editar" class="form-label">Área de Especialización</label>
                <select id="area_id_editar" class="form-select">
                    <option value="">Sin área</option>
                    <?php foreach($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre_area']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="editarUsuarioForm" class="btn btn-primary">Guardar Cambios</button></div>
    </div>
  </div>
</div>
<div class="modal fade" id="generarPagoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Generar Orden de Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Estás creando una orden de pago para el usuario: <strong id="nombre_usuario_pago"></strong></p>
        <form id="generarPagoForm">
            <input type="hidden" id="usuario_id_pago">
            <div class="mb-3">
                <label for="tipo_pago_select" class="form-label">Concepto del Pago</label>
                <select id="tipo_pago_select" class="form-select" required>
                    <option value="Inscripción Asistente Estudiante">Inscripción Asistente Estudiante ($300)</option>
                    <option value="Inscripción Asistente Profesionista">Inscripción Asistente Profesionista ($1000)</option>
                    <option value="Publicacion de Resumen (Autor)">Publicación de Resumen - Autor ($1000)</option>
                    <option value="Publicacion de Resumen (Cartel)">Publicación de Resumen - Póster ($500)</option>
                    <option value="otro">Otro (especificar)</option>
                </select>
            </div>
            <div class="mb-3 d-none" id="tipo_pago_otro_container">
                <label for="tipo_pago_otro" class="form-label">Especificar Concepto</label>
                <input type="text" id="tipo_pago_otro" class="form-control">
            </div>
            <div class="mb-3">
                <label for="monto_pago" class="form-label">Monto (MXN)</label>
                <input type="number" id="monto_pago" class="form-control" step="0.01" required>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="generarPagoForm" id="confirmarGenerarPagoBtn" class="btn btn-primary">Generar Pago</button>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const mensajeDiv = document.getElementById('mensaje-usuarios');
    const crearUsuarioForm = document.getElementById('crearUsuarioForm');
    const crearModal = new bootstrap.Modal(document.getElementById('crearUsuarioModal'));
    const editarUsuarioForm = document.getElementById('editarUsuarioForm');
    const editarModal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
    const crearUsuarioBtn = document.getElementById('crearUsuarioBtn');
    const originalCrearBtnText = crearUsuarioBtn.innerHTML;
    const generarPagoModal = new bootstrap.Modal(document.getElementById('generarPagoModal'));
    const generarPagoForm = document.getElementById('generarPagoForm');
    const tipoPagoSelect = document.getElementById('tipo_pago_select');
    document.getElementById('rol_id_crear').addEventListener('change', function() {
        const rolNombre = this.options[this.selectedIndex].getAttribute('data-rol-nombre');
        const areaContainer = document.getElementById('area-container-crear');
        rolNombre === 'Coordinador de Area' ? areaContainer.classList.remove('d-none') : areaContainer.classList.add('d-none');
    });

    document.getElementById('rol_id_editar').addEventListener('change', function() {
        const rolNombre = this.options[this.selectedIndex].getAttribute('data-rol-nombre');
        const areaContainer = document.getElementById('area-container-editar');
        rolNombre === 'Coordinador de Area' ? areaContainer.classList.remove('d-none') : areaContainer.classList.add('d-none');
    });

    crearUsuarioForm.addEventListener('submit', function(event) {
        event.preventDefault();
        crearUsuarioBtn.disabled = true;
        crearUsuarioBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Creando...`;
        const rolSelect = document.getElementById('rol_id_crear');
        const esRevisor = rolSelect.options[rolSelect.selectedIndex].getAttribute('data-rol-nombre') === 'Coordinador de Area';
        const datos = {
            nombre: this.nombre.value, correo: this.correo.value, institucion: this.institucion.value,
            rol_id: rolSelect.value, area_id: esRevisor ? document.getElementById('area_id_crear').value : null,
            csrf_token: csrfToken
        };
        fetch(`${baseUrl}administrador/crearUsuario`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json()).then(data => {
            if (data.error) { alert('Error: ' + data.error); }
            else { location.reload(); } // Recargamos para ver al nuevo usuario en su tabla correcta
        })
        .catch(() => alert('Ocurrió un error de conexión.'))
        .finally(() => {
            crearUsuarioBtn.disabled = false;
            crearUsuarioBtn.innerHTML = originalCrearBtnText;
        });
    });

    document.body.addEventListener('click', function(event) {
        const target = event.target;
        const id = target.getAttribute('data-id');
        if (!id) return;

        if (target.classList.contains('btn-editar')) {
            fetch(`${baseUrl}administrador/obtenerUsuario/${id}`)
                .then(res => res.json())
                .then(usuario => {
                    document.getElementById('edit_usuario_id').value = usuario.id;
                    document.getElementById('edit_nombre').value = usuario.nombre_completo;
                    document.getElementById('edit_correo').value = usuario.correo;
                    document.getElementById('edit_institucion').value = usuario.institucion_procedencia;
                    const rolSelect = document.getElementById('rol_id_editar');
                    rolSelect.value = usuario.roles_ids?.[0] || '';
                    rolSelect.dispatchEvent(new Event('change')); // Forzamos el evento para mostrar/ocultar el área
                    const areaSelect = document.getElementById('area_id_editar');
                    areaSelect.value = usuario.area_id || "";
                    editarModal.show();
                });
        }
        
        if (target.classList.contains('btn-eliminar') || target.classList.contains('btn-reactivar')) {
            const accion = target.classList.contains('btn-reactivar') ? 'reactivar' : 'desactivar';
            const endpoint = target.classList.contains('btn-reactivar') ? 'reactivarUsuario' : 'eliminarUsuario';
            if (confirm(`¿Seguro que deseas ${accion} a este usuario?`)) {
                fetch(`${baseUrl}administrador/${endpoint}/${id}`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ csrf_token: csrfToken })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje || data.error);
                    location.reload(); // Recargamos para ver el cambio de estado
                });
            }
        }
        if (target.classList.contains('btn-generar-pago')) {
            const userId = target.getAttribute('data-id');
            const userName = target.getAttribute('data-nombre');
            document.getElementById('usuario_id_pago').value = userId;
            document.getElementById('nombre_usuario_pago').textContent = userName;
            generarPagoModal.show();
        }
    });
    
    editarUsuarioForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const id = document.getElementById('edit_usuario_id').value;
        const rolSelect = document.getElementById('rol_id_editar');
        const esRevisor = rolSelect.options[rolSelect.selectedIndex].getAttribute('data-rol-nombre') === 'Coordinador de Area';
        const datos = {
            detalles: {
                nombre_completo: document.getElementById('edit_nombre').value,
                correo: document.getElementById('edit_correo').value,
                institucion_procedencia: document.getElementById('edit_institucion').value,
                area_id: esRevisor ? document.getElementById('area_id_editar').value : null
            },
            roles_ids: [rolSelect.value],
            csrf_token: csrfToken
        };
        fetch(`${baseUrl}administrador/actualizarUsuario/${id}`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje || data.error);
            editarModal.hide();
            location.reload(); 
        });
    });
    tipoPagoSelect.addEventListener('change', function() {
        const otroContainer = document.getElementById('tipo_pago_otro_container');
        const montoInput = document.getElementById('monto_pago');

        if (this.value === 'otro') {
            otroContainer.classList.remove('d-none');
            montoInput.value = '';
        } else {
            otroContainer.classList.add('d-none');
            // Autocompletar monto según la opción
            if (this.value.includes('300')) montoInput.value = 300;
            else if (this.value.includes('1000')) montoInput.value = 1000;
            else if (this.value.includes('500')) montoInput.value = 500;
        }
    });
    generarPagoForm.addEventListener('submit', function(event) {
        event.preventDefault();

        let tipoPago = tipoPagoSelect.value;
        if (tipoPago === 'otro') {
            tipoPago = document.getElementById('tipo_pago_otro').value;
        }

        const datos = {
            usuario_id: document.getElementById('usuario_id_pago').value,
            tipo_pago: tipoPago,
            monto: document.getElementById('monto_pago').value,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}administrador/generarPagoManual`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje || data.error);
            if (!data.error) {
                generarPagoModal.hide();
                this.reset();
            }
        });
    });
});
</script>
