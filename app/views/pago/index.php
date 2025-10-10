<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h1 class="mb-4">Historial y Centro de Pagos</h1>
        <div id="mensaje-general" class="mb-3"></div>

        <div class="alert alert-warning">
            <h4 class="alert-heading">¡Instrucciones Importantes! 📝</h4>
            <p>Por favor, realiza tu pago directamente en **ventanilla o cajero automático** del banco indicado. En caso de hacer transferencia bancaria subir el comprobante emitido por Banxico y poner de asunto CCTI2025</p>
            <hr>
            <p class="mb-0">Para que tu pago sea validado, es **indispensable** que escribas a mano sobre el comprobante, con letra clara, la siguiente información:</p>
            <ul>
                <li><strong>Nombre Completo</strong></li>
                <li><strong>Institución de Procedencia</strong></li>
                <li>Si el pago es por un resumen aceptado, añade también el <strong>Título del Resumen en caso de transferecia en el asunto poner CCTI025 y nombre del resumen</strong>.</li>
            </ul>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h4>Datos para Depósito </h4></div>
            <div class="card-body">
                    <h4 class="text-danger text-center fw-bold mb-3">ATENCIÓN: EN CASO DE TRANSFERENCIA SUBIR EL COMPROBANTE EMITIDO POR BANXICO</h4>

                <p><strong>Banco:</strong> BANAMEX</p>
                <p><strong>A nombre de:</strong> FACULTAD DE SISTEMAS BIOLOGICOS E INNOVACION TECNOLOGICA</p>
                <p><strong>Número de cuenta:</strong> 6254730 | <strong>Sucursal:</strong> 7018</p>
                <p><strong>CLABE Interbancaria:</strong> 002610701862547304</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Mis Transacciones (formato jpeg, png o pdf)</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr><th>Concepto</th><th>Monto</th><th>Estatus del Pago</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagos)): ?>
                                <tr><td colspan="4" class="text-center">No tienes transacciones registradas.</td></tr>
                            <?php else: ?>
                                <?php foreach($pagos as $pago): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($pago['tipo_pago']); ?>
                                            <?php if (!empty($pago['resumen_titulo'])): ?>
                                                <small class="d-block text-muted">Resumen: "<?php echo htmlspecialchars($pago['resumen_titulo']); ?>"</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                        <td>
                                            <?php 
                                                $estatus = htmlspecialchars($pago['estatus_pago']);
                                                $badge_class = 'bg-secondary';
                                                if ($estatus == 'Aprobado') $badge_class = 'bg-success';
                                                if ($estatus == 'Rechazado') $badge_class = 'bg-danger';
                                                if ($estatus == 'Pendiente') $badge_class = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $estatus; ?></span>
                                        </td>
                                        <td>
                                            <?php switch ($pago['estatus_pago']):
                                                case 'Aprobado': ?>
                                                    <div class="alert alert-success p-2 mb-0">
                                                        ¡Felicitaciones! Te esperamos en el evento del 28 al 30 de Agosto.
                                                        <?php if ($pago['resumen_id']): ?>
                                                            <hr class="my-1"><small>Gracias por tu pago :D</small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php break; ?>
                                                <?php case 'Pendiente': ?>
                                                    <?php if (empty($pago['comprobante_ruta'])): ?>
                                                        <form class="pago-form">
                                                            <?php CSRFHelper::getTokenInput(); ?>
                                                            <input type="hidden" name="pago_id" value="<?php echo $pago['id']; ?>">
                                                            <input class="form-control form-control-sm" type="file" name="comprobante" required>
                                                            <button type="submit" class="btn btn-sm btn-primary mt-1">Subir Comprobante(formato jpeg, png o pdf)</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <small class="text-muted">Comprobante en revisión...</small>
                                                    <?php endif; ?>
                                                    <?php break; ?>
                                                <?php case 'Rechazado': ?>
                                                    <form class="pago-form">
                                                        <?php CSRFHelper::getTokenInput(); ?>
                                                        <p class="text-danger small mb-1"><strong>Motivo del Rechazo:</strong> <?php echo htmlspecialchars($pago['comentarios_rechazo'] ?? 'Sin comentarios.'); ?></p>
                                                        <input type="hidden" name="pago_id" value="<?php echo $pago['id']; ?>">
                                                        <input class="form-control form-control-sm" type="file" name="comprobante" required>
                                                        <button type="submit" class="btn btn-sm btn-danger mt-1">Reintentar Envío</button>
                                                    </form>
                                                    <?php break; ?>
                                            <?php endswitch; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('submit', function(event) {
    if (event.target.matches('.pago-form')) {
        event.preventDefault();
        const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
        const baseUrl = '<?php echo BASE_URL; ?>';

        const form = event.target;
        const mensajeDiv = document.getElementById('mensaje-general');
        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const comprobanteInput = form.querySelector('input[type="file"]');
        if (!comprobanteInput.files || comprobanteInput.files.length === 0) {
            mensajeDiv.innerHTML = '<div class="alert alert-danger">Por favor, adjunta un archivo de comprobante.</div>';
            return;
        }
        
        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Subiendo...`;

        fetch(`${baseUrl}pago/subirComprobanteExistente`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje} La página se recargará...</div>`;
                setTimeout(() => { location.reload(); }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeDiv.innerHTML = '<div class="alert alert-danger">Ocurrió un error de conexión.</div>';
        })
        .finally(() => {
            if (!document.querySelector('.alert-success')) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    }
});
</script>