<h1 class="mb-4"><i class="fas fa-user-circle me-2"></i>Mi Perfil</h1>
<div id="mensaje-general" class="mb-3"></div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="perfilTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="micuenta-tab" data-bs-toggle="tab" data-bs-target="#micuenta" type="button" role="tab" aria-controls="micuenta" aria-selected="true"><i class="fas fa-id-card me-2"></i>Datos Generales</button>
  </li>
  <?php if ($perfil_revisor): ?>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="revisor-tab" data-bs-toggle="tab" data-bs-target="#revisor" type="button" role="tab" aria-controls="revisor" aria-selected="false"><i class="fas fa-user-tie me-2"></i>Perfil de Revisor</button>
  </li>
  <?php endif; ?>
</ul>

<div class="tab-content" id="perfilTabsContent">

    <!-- TAB 1: DATOS GENERALES -->
    <div class="tab-pane fade show active" id="micuenta" role="tabpanel" aria-labelledby="micuenta-tab">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">Mis Datos</h5></div>
                    <div class="card-body">
                        <form id="perfilForm">
                            <?php CSRFHelper::getTokenInput(); ?>
                            <div class="mb-3"><label for="correo" class="form-label">Correo Electrónico (no editable)</label><input type="email" class="form-control" id="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled readonly></div>
                            <div class="mb-3"><label for="nombre_completo" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>"></div>
                            <div class="mb-3"><label for="institucion_procedencia" class="form-label">Institución</label><input type="text" class="form-control" id="institucion_procedencia" value="<?php echo htmlspecialchars($usuario['institucion_procedencia'] ?? ''); ?>"></div>
                            <button type="submit" id="guardarPerfilBtn" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-danger text-white"><h5 class="mb-0">Cambiar Contraseña</h5></div>
                    <div class="card-body">
                        <form id="contrasenaForm">
                            <?php CSRFHelper::getTokenInput(); ?>
                            <div class="mb-3"><label for="contrasena_actual" class="form-label">Contraseña Actual</label><input type="password" class="form-control" id="contrasena_actual" required></div>
                            <div class="mb-3"><label for="nueva_contrasena" class="form-label">Nueva Contraseña</label><input type="password" class="form-control" id="nueva_contrasena" required></div>
                            <div class="mb-3">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirmar_contrasena" required>
                                <div id="password-match-message" class="form-text mt-1"></div>
                            </div>
                            <button type="submit" id="cambiarPassBtn" class="btn btn-danger">Cambiar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: PERFIL DE REVISOR -->
    <?php if ($perfil_revisor): ?>
    <div class="tab-pane fade" id="revisor" role="tabpanel" aria-labelledby="revisor-tab">
        <div class="card shadow-sm border-info">
            <div class="card-header bg-info text-white"><h5 class="mb-0">Información Profesional y Académica</h5></div>
            <div class="card-body">
                <div class="alert alert-light border mb-4"><i class="fas fa-info-circle text-info"></i> Aquí puedes actualizar tu información profesional. Los archivos solo se reemplazarán si subes unos nuevos.</div>
                
                <form id="perfilRevisorForm" enctype="multipart/form-data">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="row">
                        <!-- Area Tematica (Readonly) -->
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Área Temática de Especialización</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($nombre_area ?? 'No asignada'); ?>" disabled readonly>
                            <div class="form-text">Si necesitas cambiar de área, contacta al administrador.</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="grado_academico" class="form-label">Grado Académico</label>
                            <input type="text" class="form-control" id="grado_academico" name="grado_academico" value="<?php echo htmlspecialchars($perfil_revisor['grado_academico']); ?>" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="afiliacion_institucional" class="form-label">Afiliación Institucional</label>
                            <input type="text" class="form-control" id="afiliacion_institucional" name="afiliacion_institucional" value="<?php echo htmlspecialchars($perfil_revisor['afiliacion_institucional']); ?>" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="cargo_actual" class="form-label">Cargo Actual</label>
                            <input type="text" class="form-control" id="cargo_actual" name="cargo_actual" value="<?php echo htmlspecialchars($perfil_revisor['cargo_actual']); ?>" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="area_especialidad" class="form-label">Área de Especialidad (Líneas de investigación)</label>
                            <textarea class="form-control" id="area_especialidad" name="area_especialidad" rows="3" required><?php echo htmlspecialchars($perfil_revisor['area_especialidad']); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orcid" class="form-label">ORCID</label>
                            <input type="text" class="form-control" id="orcid" name="orcid" value="<?php echo htmlspecialchars($perfil_revisor['orcid'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="google_scholar_id" class="form-label">Google Scholar ID</label>
                            <input type="text" class="form-control" id="google_scholar_id" name="google_scholar_id" value="<?php echo htmlspecialchars($perfil_revisor['google_scholar_id'] ?? ''); ?>">
                        </div>
                        
                        <!-- Files -->
                        <div class="col-md-6 mb-3">
                            <label for="comprobante_sni" class="form-label">Comprobante SNI (PDF)</label>
                            <?php if (!empty($perfil_revisor['comprobante_sni_ruta'])): ?>
                                <div class="mb-2">
                                    <small class="text-success"><i class="fas fa-check-circle"></i> Archivo actual: <a href="<?php echo BASE_URL . 'public/uploads/revisores_perfil/' . $perfil_revisor['comprobante_sni_ruta']; ?>" target="_blank">Ver archivo</a></small>
                                </div>
                            <?php endif; ?>
                            <input class="form-control" type="file" id="comprobante_sni" name="comprobante_sni" accept=".pdf">
                            <div class="form-text">Sube un nuevo archivo solo si deseas reemplazar el actual.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="foto" class="form-label">Fotografía</label>
                            <?php if (!empty($perfil_revisor['foto_ruta'])): ?>
                                <div class="mb-2 d-flex align-items-center">
                                    <img src="<?php echo BASE_URL . 'public/uploads/revisores_perfil/' . $perfil_revisor['foto_ruta']; ?>" alt="Foto actual" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <small class="text-success">Foto actual cargada</small>
                                </div>
                            <?php endif; ?>
                            <input class="form-control" type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png">
                            <div class="form-text">JPG o PNG. Deja vacío para conservar la foto actual.</div>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" id="btnGuardarRevisor" class="btn btn-info text-white"><i class="fas fa-save me-2"></i>Actualizar Perfil de Revisor</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
    const mensajeDiv = document.getElementById('mensaje-general');

    // --- LOGICA DATOS GENERALES ---
    const perfilForm = document.getElementById('perfilForm');
    const guardarPerfilBtn = document.getElementById('guardarPerfilBtn');
    const originalPerfilBtnText = guardarPerfilBtn.innerHTML;

    perfilForm.addEventListener('submit', function(event) {
        event.preventDefault();
        guardarPerfilBtn.disabled = true;
        guardarPerfilBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;

        const datos = {
            nombre_completo: document.getElementById('nombre_completo').value,
            institucion_procedencia: document.getElementById('institucion_procedencia').value,
            correo: document.getElementById('correo').value,
            area_id: <?php echo json_encode($usuario['area_id']); ?>,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}usuario/actualizarPerfil`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        }).then(res => res.json()).then(data => {
            mensajeDiv.innerHTML = `<div class="alert ${data.error ? 'alert-danger' : 'alert-success'}">${data.mensaje || data.error}</div>`;
            window.scrollTo(0,0);
            if (!data.error) { setTimeout(() => location.reload(), 1500); }
        }).finally(() => {
            guardarPerfilBtn.disabled = false;
            guardarPerfilBtn.innerHTML = originalPerfilBtnText;
        });
    });

    // --- LOGICA PASSWORD ---
    const contrasenaForm = document.getElementById('contrasenaForm');
    const cambiarPassBtn = document.getElementById('cambiarPassBtn');
    const originalPassBtnText = cambiarPassBtn.innerHTML;
    const nuevaContrasenaInput = document.getElementById('nueva_contrasena');
    const confirmarContrasenaInput = document.getElementById('confirmar_contrasena');
    const matchMessageDiv = document.getElementById('password-match-message');

    function validarContrasenas() {
        if (confirmarContrasenaInput.value === '') {
            matchMessageDiv.textContent = '';
            confirmarContrasenaInput.classList.remove('is-valid', 'is-invalid');
            return;
        }
        if (nuevaContrasenaInput.value === confirmarContrasenaInput.value) {
            matchMessageDiv.textContent = 'Las contraseñas coinciden';
            matchMessageDiv.className = 'form-text text-success mt-1';
            confirmarContrasenaInput.classList.remove('is-invalid');
            confirmarContrasenaInput.classList.add('is-valid');
        } else {
            matchMessageDiv.textContent = 'Las contraseñas no coinciden';
            matchMessageDiv.className = 'form-text text-danger mt-1';
            confirmarContrasenaInput.classList.remove('is-valid');
            confirmarContrasenaInput.classList.add('is-invalid');
        }
    }
    nuevaContrasenaInput.addEventListener('input', validarContrasenas);
    confirmarContrasenaInput.addEventListener('input', validarContrasenas);

    contrasenaForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const nueva = nuevaContrasenaInput.value;
        const confirmar = confirmarContrasenaInput.value;

        if (nueva !== confirmar) {
            mensajeDiv.innerHTML = '<div class="alert alert-danger">Las contraseñas no coinciden.</div>';
            return;
        }

        cambiarPassBtn.disabled = true;
        cambiarPassBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Cambiando...`;

        const datos = {
            contrasena_actual: document.getElementById('contrasena_actual').value,
            nueva_contrasena: nueva,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}usuario/cambiarContrasena`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        }).then(res => res.json()).then(data => {
            mensajeDiv.innerHTML = `<div class="alert ${data.error ? 'alert-danger' : 'alert-success'}">${data.mensaje || data.error}</div>`;
            window.scrollTo(0,0);
            if (!data.error) { this.reset(); matchMessageDiv.textContent = ''; confirmarContrasenaInput.classList.remove('is-valid'); }
        }).finally(() => {
            cambiarPassBtn.disabled = false;
            cambiarPassBtn.innerHTML = originalPassBtnText;
        });
    });

    // --- LOGICA PERFIL REVISOR ---
    const perfilRevisorForm = document.getElementById('perfilRevisorForm');
    if (perfilRevisorForm) {
        const btnGuardarRevisor = document.getElementById('btnGuardarRevisor');
        const originalBtnRevisorText = btnGuardarRevisor.innerHTML;

        perfilRevisorForm.addEventListener('submit', function(event) {
            event.preventDefault();
            btnGuardarRevisor.disabled = true;
            btnGuardarRevisor.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;

            const formData = new FormData(this);
            formData.append('csrf_token', csrfToken);

            fetch(`${baseUrl}revisorExtensos/actualizarPerfil`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                mensajeDiv.innerHTML = `<div class="alert ${data.error ? 'alert-danger' : 'alert-success'}">${data.mensaje || data.error}</div>`;
                window.scrollTo(0,0);
                if (!data.error) {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(err => {
                 mensajeDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error al procesar la solicitud.</div>`;
                 console.error(err);
            })
            .finally(() => {
                btnGuardarRevisor.disabled = false;
                btnGuardarRevisor.innerHTML = originalBtnRevisorText;
            });
        });
    }
});
</script>