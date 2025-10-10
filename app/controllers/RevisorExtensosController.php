<?php

class RevisorExtensosController {

    public function completarPerfil() {
        if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
            redirect('');
        }
        if (Usuario::perfilRevisorEstaCompleto($_SESSION['usuario_id'])) {
            redirect('revisorExtensos/dashboard');
        }

        CSRFHelper::generateToken();
        $areas = AreaTematica::obtenerTodas();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor_extensos/completar_perfil.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function guardarPerfil() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
            http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return;
        }
        
        // --- INICIA LÓGICA DE VALIDACIÓN Y SUBIDA DE ARCHIVOS ---
        
        // Validación de campos de texto
        $campos_requeridos = ['grado_academico', 'afiliacion_institucional', 'cargo_actual', 'area_especialidad'];
        foreach ($campos_requeridos as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400); echo json_encode(['error' => 'Todos los campos son obligatorios.']); return;
            }
        }
        
        $comprobante_sni_ruta = null;
        $foto_ruta = null;
        $directorio_revisores = BACKEND_ROOT . '/uploads/revisores_perfil/';
        if (!is_dir($directorio_revisores)) { mkdir($directorio_revisores, 0777, true); }

        // Procesar comprobante SNI (si se subió)
        if (isset($_FILES['comprobante_sni']) && $_FILES['comprobante_sni']['error'] === UPLOAD_ERR_OK) {
            $archivo_sni = $_FILES['comprobante_sni'];
            if ($archivo_sni['type'] !== 'application/pdf') {
                http_response_code(400); echo json_encode(['error' => 'El comprobante SNI debe ser un archivo PDF.']); return;
            }
            $extension = pathinfo($archivo_sni['name'], PATHINFO_EXTENSION);
            $comprobante_sni_ruta = 'sni_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
            move_uploaded_file($archivo_sni['tmp_name'], $directorio_revisores . $comprobante_sni_ruta);
        }

        // Procesar foto de perfil (si se subió)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $archivo_foto = $_FILES['foto'];
            $tipos_permitidos = ['image/jpeg', 'image/png'];
            if (!in_array($archivo_foto['type'], $tipos_permitidos)) {
                http_response_code(400); echo json_encode(['error' => 'La foto debe ser un archivo JPG o PNG.']); return;
            }
            $extension = pathinfo($archivo_foto['name'], PATHINFO_EXTENSION);
            $foto_ruta = 'foto_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
            move_uploaded_file($archivo_foto['tmp_name'], $directorio_revisores . $foto_ruta);
        }

        $datos = [
            'usuario_id'                => $_SESSION['usuario_id'],
            'grado_academico'           => $_POST['grado_academico'],
            'afiliacion_institucional'  => $_POST['afiliacion_institucional'],
            'cargo_actual'              => $_POST['cargo_actual'],
            'area_especialidad'         => $_POST['area_especialidad'],
            'orcid'                     => $_POST['orcid'] ?? null,
            'google_scholar_id'         => $_POST['google_scholar_id'] ?? null,
            'comprobante_sni_ruta'      => $comprobante_sni_ruta,
            'foto_ruta'                 => $foto_ruta
        ];
        
        if (Usuario::guardarPerfilRevisorExtenso($datos)) {
            echo json_encode(['mensaje' => 'Perfil completado con éxito. ¡Gracias por tu colaboración!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el perfil.']);
        }
    }
    
public function dashboard() {
    if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
        redirect('');
    }
    if (!Usuario::perfilRevisorEstaCompleto($_SESSION['usuario_id'])) {
        redirect('revisorExtensos/completarPerfil');
    }
    $evaluacionesAsignadas = EvaluacionExtenso::buscarAsignadasPorRevisor($_SESSION['usuario_id']);
    $evaluacionesCompletadas = EvaluacionExtenso::buscarCompletadasPorRevisor($_SESSION['usuario_id']);

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/revisor_extensos/dashboard.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
public function evaluar($evaluacion_id) {
    if (!isset($_SESSION['usuario_id'])) { redirect(''); }

    $evaluacion = EvaluacionExtenso::buscarPorId($evaluacion_id);

    if (!$evaluacion || $evaluacion['revisor_id'] != $_SESSION['usuario_id']) {
        redirect('revisorExtensos/dashboard');
    }

    CSRFHelper::generateToken();

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/revisor_extensos/evaluar.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
/**
 * (API) Procesa el envío del formulario de evaluación de un extenso.
 */
public function procesarEvaluacion($evaluacion_id) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
        http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return;
    }

    $datos_post = $_POST;
    $respuestas = [];
    for ($i = 1; $i <= 6; $i++) {
        $respuestas['pregunta_'.$i] = $datos_post['pregunta_'.$i] ?? 'no';
    }
    $datos_guardar = [
        'respuestas_formulario'   => json_encode($respuestas),
        'observaciones_generales' => $datos_post['observaciones_generales'],
        'veredicto'               => $datos_post['veredicto'],
        'argumento_rechazo'       => ($datos_post['veredicto'] === 'No Publicable') ? $datos_post['argumento_rechazo'] : null
    ];

    if (EvaluacionExtenso::guardarEvaluacion($evaluacion_id, $datos_guardar)) {
        $pdf_url = BASE_URL . 'reporte/generarEvaluacionPDF/' . $evaluacion_id;
        echo json_encode([
            'mensaje' => 'Evaluación guardada. Ahora descarga el PDF para firmarlo.',
            'pdf_url' => $pdf_url
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo guardar la evaluación.']);
    }
}
/**
 * (API) Procesa la subida del PDF de evaluación firmado.
 */
public function subirPdfFirmado($evaluacion_id) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
        http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return;
    }

    if (!isset($_FILES['pdf_firmado']) || $_FILES['pdf_firmado']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); echo json_encode(['error' => 'No se recibió el archivo firmado.']); return;
    }

    $archivo = $_FILES['pdf_firmado'];
    if ($archivo['type'] !== 'application/pdf') {
        http_response_code(400); echo json_encode(['error' => 'El archivo debe ser un PDF.']); return;
    }

    $directorioSubida = BACKEND_ROOT . '/uploads/evaluaciones_firmadas/';
    if (!is_dir($directorioSubida)) { mkdir($directorioSubida, 0777, true); }

    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreUnico = 'evaluacion_' . $evaluacion_id . '_firmada_' . time() . '.' . $extension;
    $rutaDestino = $directorioSubida . $nombreUnico;

    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        if (EvaluacionExtenso::guardarPdfFirmado($evaluacion_id, $nombreUnico)) {
            echo json_encode(['mensaje' => 'Evaluación firmada subida con éxito.']);
        } else {
            http_response_code(500); echo json_encode(['error' => 'No se pudo actualizar la base de datos.']);
        }
    } else {
        http_response_code(500); echo json_encode(['error' => 'No se pudo guardar el archivo firmado.']);
    }
}
}