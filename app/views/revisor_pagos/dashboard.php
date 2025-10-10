
<h1 class="mb-4">Panel de Revisión de Pagos</h1>
<?php CSRFHelper::getTokenInput(); ?> 

<div id="mensaje-pagos"></div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Tipo de Usuario</th>
                        <th>Monto</th>
                  <!--      <th>Tipo de Inscripción</th> -->
                        <th>Comprobante</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-pagos">
                    <?php if (empty($pagosPendientes)): ?>
                        <tr><td colspan="6" class="text-center">No hay pagos pendientes de revisión.</td></tr>
                    <?php else: ?>
                        <?php foreach($pagosPendientes as $pago): ?>
                        <tr id="pago-row-<?php echo $pago['id']; ?>">
                            <td><?php echo htmlspecialchars($pago['nombre_completo']); ?></td>
                            <td><?php echo htmlspecialchars($pago['roles']); ?></td>
                            <td>$<?php echo number_format($pago['monto'], 2); ?> MXN</td>
                          <!--  <td><?php /* echo htmlspecialchars($pago['tipo_pago']); */ ?></td> -->
                                <td><a href="<?php echo BASE_URL; ?>archivo/ver/pagos/<?php echo $pago['comprobante_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver Archivo</a></td>                            <td class="text-center">
                                <button class="btn btn-sm btn-success btn-aprobar" data-id="<?php echo $pago['id']; ?>">Aprobar</button>
                                <button class="btn btn-sm btn-danger btn-rechazar" data-id="<?php echo $pago['id']; ?>" data-bs-toggle="modal" data-bs-target="#rechazoModal">Rechazar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<h3 class="mt-5">Historial de Pagos Revisados</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha de Carga</th>
                        <th>Tipo de Usuario</th>
                        <th>Monto</th>
                      <!--  <th>Tipo de Inscripción</th> -->
                        <th>Estatus</th>
                        <th>Fecha de Revisión</th>
                        <th>Revisado por</th>
                    </tr>
                </thead>
            <tbody>
                <?php if (empty($pagosHistorial)): ?>
                    <tr><td colspan="8" class="text-center">Aún no has revisado ningún pago.</td></tr>
                <?php else: ?>
                    <?php foreach ($pagosHistorial as $pago): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pago['nombre_completo']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_carga'])); ?></td>
                            <td><?php echo htmlspecialchars($pago['roles']); ?></td>
                            <td>$<?php echo number_format($pago['monto'], 2); ?> MXN</td>
                          <!--  <td><?php /* echo htmlspecialchars($pago['tipo_pago']); */ ?></td> -->
                            <td>
                                <?php 
                                    $estatus = htmlspecialchars($pago['estatus_pago']);
                                    $badge_class = ($estatus == 'Aprobado') ? 'bg-success' : 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $estatus; ?></span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_revision_pago'])); ?></td>
                            <td><?php echo htmlspecialchars($pago['revisor_nombre'] ?? 'Sistema'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="rechazoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Motivo del Rechazo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <form id="rechazoForm">
        <input type="hidden" id="pago_id_rechazo">

        <div class="mb-3">
            <label class="form-label">Selecciona un motivo común (opcional):</label>
            <div id="motivos-comunes">
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">Foto borrosa o ilegible.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">Falta datos escritos en el comprobante.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">El monto del pago es incorrecto.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">El comprobante no parece ser válido en alguno de los datos.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">El tipo de archivo no es el correcto.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">Archivo dañado, por favor volver a enviarlo.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">No ha adjuntado ningún archivo.</button>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-1 motivo-btn">No ha ingresado una foto del comprobante.</button>
            </div>
        </div>

        <div class="mb-3">
            <label for="comentarios_rechazo" class="form-label">Comentarios adicionales:</label>
            <textarea class="form-control" id="comentarios_rechazo" rows="3" required></textarea>
        </div>
    </form>
</div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" form="rechazoForm" id="confirmarRechazoBtn" class="btn btn-danger">Confirmar Rechazo</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const baseUrl = '<?php echo BASE_URL; ?>';
    const tablaPagos = document.getElementById('tabla-pagos');
    const mensajeDiv = document.getElementById('mensaje-pagos');
    const rechazoModal = new bootstrap.Modal(document.getElementById('rechazoModal'));
    const rechazoForm = document.getElementById('rechazoForm');
    const pagoIdRechazoInput = document.getElementById('pago_id_rechazo');
    const confirmarRechazoBtn = document.getElementById('confirmarRechazoBtn');
    const originalRechazoBtnText = confirmarRechazoBtn.innerHTML;
    const comentariosTextarea = document.getElementById('comentarios_rechazo');
    const motivosContainer = document.getElementById('motivos-comunes');
    motivosContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('motivo-btn')) {
            const motivoTexto = event.target.textContent;
            comentariosTextarea.value += (comentariosTextarea.value ? ' ' : '') + motivoTexto;
        }
    });

    tablaPagos.addEventListener('click', function(event) {
        const target = event.target;
        const pagoId = target.getAttribute('data-id');

        if (target.classList.contains('btn-aprobar')) {
            if (confirm('¿Estás seguro de que deseas APROBAR este pago?')) {
                const approveBtn = target;
                const originalApproveBtnText = approveBtn.innerHTML;
                approveBtn.disabled = true;
                approveBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

                const rejectBtn = approveBtn.nextElementSibling;
                if(rejectBtn) rejectBtn.disabled = true;

                procesarPago(pagoId, 'Aprobado')
                    .finally(() => {
                        approveBtn.disabled = false;
                        approveBtn.innerHTML = originalApproveBtnText;
                        if(rejectBtn) rejectBtn.disabled = false;
                    });
            }
        }
        if (target.classList.contains('btn-rechazar')) {
            pagoIdRechazoInput.value = pagoId;
        }
    });

    rechazoForm.addEventListener('submit', function(event) {
        event.preventDefault();
        confirmarRechazoBtn.disabled = true;
        confirmarRechazoBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Rechazando...`;

        const pagoId = pagoIdRechazoInput.value;
        const comentarios = comentariosTextarea.value;

        procesarPago(pagoId, 'Rechazado', comentarios)
            .then(success => {
                if (success) {
                    rechazoModal.hide();
                    this.reset();
                }
            })
            .finally(() => {
                confirmarRechazoBtn.disabled = false;
                confirmarRechazoBtn.innerHTML = originalRechazoBtnText;
            });
    });

    function procesarPago(pagoId, accion, comentarios = null) {
        return fetch(`${baseUrl}revisorPagos/procesarPago`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pago_id: pagoId, nuevo_estatus: accion, comentarios: comentarios, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return false; 
            } else {
                document.getElementById(`pago-row-${pagoId}`).remove();
                mensajeDiv.innerHTML = `<div class="alert alert-success">Pago procesado con éxito.</div>`;
                return true; 
            }
        });
    }
});
</script>