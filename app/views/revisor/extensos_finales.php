<div class="container-fluid px-4 mt-4">
    <h2 class="mt-4">Extensos finales por área</h2>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>revisor/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Extensos finales</li>
    </ol>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-journal-check me-1"></i> Gestión de versiones finales
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Estado</th>
                            <th>Archivo final</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($extensosFinales)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay extensos finales en esta etapa para tu área.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($extensosFinales as $extenso): ?>
                                <tr>
                                    <td class="fw-semibold text-primary"><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($extenso['autor_nombre']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($extenso['autor_correo']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badge = $extenso['estatus_extenso'] === 'Corregir extenso final'
                                            ? 'bg-warning text-dark'
                                            : 'bg-success';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($extenso['estatus_extenso']); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($extenso['archivo_final_ruta'])): ?>
                                            <div class="small mb-1"><?php echo date('d/m/Y H:i', strtotime($extenso['fecha_envio_final'])); ?></div>
                                            <a href="<?php echo BASE_URL; ?>archivo/ver/extensos_finales/<?php echo htmlspecialchars($extenso['archivo_final_ruta']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-download"></i> Ver archivo
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Sin archivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?php echo !empty($extenso['comentarios_formato']) ? nl2br(htmlspecialchars($extenso['comentarios_formato'])) : '<span class="text-muted">Sin observaciones</span>'; ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-warning btn-sm btn-recordatorio-final"
                                                    data-id="<?php echo (int)$extenso['id']; ?>"
                                                    data-autor="<?php echo htmlspecialchars($extenso['autor_nombre']); ?>">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                            <?php if (!empty($extenso['archivo_final_ruta'])): ?>
                                                <button class="btn btn-outline-danger btn-sm btn-devolver-final"
                                                        data-id="<?php echo (int)$extenso['id']; ?>"
                                                        data-titulo="<?php echo htmlspecialchars($extenso['titulo']); ?>">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<div class="modal fade" id="modalDevolverFinal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Devolver extenso final</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">Artículo: <strong id="tituloDevolverFinal"></strong></p>
                <input type="hidden" id="extensoIdDevolverFinal" value="">
                <textarea id="observacionesDevolverFinal" class="form-control" rows="5" placeholder="Indica observaciones para que el autor corrija su archivo final..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmarDevolverFinal">Devolver con observaciones</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    const modalEl = document.getElementById('modalDevolverFinal');
    const modal = new bootstrap.Modal(modalEl);

    function postJson(url, payload) {
        payload.csrf_token = csrfToken;
        return fetch(`${baseUrl}${url}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        }).then(r => r.json());
    }

    document.querySelectorAll('.btn-recordatorio-final').forEach(btn => {
        btn.addEventListener('click', function() {
            const extensoId = this.dataset.id;
            const autor = this.dataset.autor;
            if (!confirm(`Enviar recordatorio por correo a ${autor}?`)) return;
            postJson('revisor/enviarRecordatorioExtensoFinal', {extenso_id: extensoId})
                .then(resp => alert(resp.error || resp.mensaje || 'Operación completada.'))
                .catch(() => alert('No se pudo enviar el recordatorio.'));
        });
    });

    document.querySelectorAll('.btn-devolver-final').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('extensoIdDevolverFinal').value = this.dataset.id;
            document.getElementById('tituloDevolverFinal').textContent = this.dataset.titulo;
            document.getElementById('observacionesDevolverFinal').value = '';
            modal.show();
        });
    });

    document.getElementById('btnConfirmarDevolverFinal').addEventListener('click', function() {
        const extensoId = document.getElementById('extensoIdDevolverFinal').value;
        const comentarios = document.getElementById('observacionesDevolverFinal').value.trim();
        if (!comentarios) {
            alert('Debes escribir observaciones.');
            return;
        }
        postJson('revisor/devolverExtensoFinal', {extenso_id: extensoId, comentarios})
            .then(resp => {
                if (resp.error) {
                    alert(resp.error);
                    return;
                }
                alert(resp.mensaje || 'Extenso devuelto con observaciones.');
                modal.hide();
                window.location.reload();
            })
            .catch(() => alert('No se pudo devolver el extenso.'));
    });
});
</script>
