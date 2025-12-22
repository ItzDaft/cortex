<?php

class RevisorExtensosController {

    public function dashboard() {
        if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
            redirect('');
        }
        if (!Usuario::perfilRevisorEstaCompleto($_SESSION['usuario_id'])) {
            redirect('revisorExtensos/completarPerfil');
        }
        $evaluacionesPorEvaluar = EvaluacionExtenso::buscarAsignadasPorRevisor($_SESSION['usuario_id']);
        
        $evaluacionesRealizadas = EvaluacionExtenso::buscarRealizadasPorRevisor($_SESSION['usuario_id']);

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor_extensos/dashboard.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function firmar($evaluacion_id) {
        if (!isset($_SESSION['usuario_id'])) { redirect(''); }

        $evaluacion = EvaluacionExtenso::buscarPorId($evaluacion_id);

        // Validaciones de seguridad
        if (!$evaluacion || $evaluacion['revisor_id'] != $_SESSION['usuario_id']) {
            redirect('revisorExtensos/dashboard');
        }
        
        // Solo permitir entrar si es favorable y requiere firma
        if ($evaluacion['veredicto'] !== 'Favorable y Publicable') {
            redirect('revisorExtensos/dashboard');
        }

        CSRFHelper::generateToken();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor_extensos/firmar.php';
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

    public function procesarEvaluacion($evaluacion_id) {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) { http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return; }

        $datos_post = $_POST;
        // Validaciones básicas
        for ($i = 1; $i <= 6; $i++) {
            if (empty($datos_post['pregunta_'.$i])) { http_response_code(400); echo json_encode(['error' => 'Responde todas las preguntas.']); return; }
        }
        if (empty($datos_post['veredicto'])) { http_response_code(400); echo json_encode(['error' => 'Selecciona un veredicto.']); return; }

        $veredicto = $datos_post['veredicto'];
        $respuestas = [];
        for ($i = 1; $i <= 6; $i++) { $respuestas['pregunta_'.$i] = $datos_post['pregunta_'.$i]; }
        
        $datos_guardar = [
            'respuestas_formulario'   => json_encode($respuestas),
            'observaciones_generales' => $datos_post['observaciones_generales'],
            'veredicto'               => $veredicto,
            'argumento_rechazo'       => ($veredicto === 'No Publicable') ? $datos_post['argumento_rechazo'] : null
        ];

        // Guardamos. El modelo pone "Pendiente de Firma" por defecto en estatus_evaluacion si usamos guardarEvaluacion
        if (EvaluacionExtenso::guardarEvaluacion($evaluacion_id, $datos_guardar)) {
            
            if ($veredicto === 'Favorable y Publicable') {
                // Caso: Requiere Firma. Enviamos la URL para redirigir a la vista de firma.
                echo json_encode([
                    'mensaje' => 'Evaluación guardada. Redirigiendo a firma...',
                    'redirect_url' => BASE_URL . 'revisorExtensos/firmar/' . $evaluacion_id
                ]);
            } else {
                // Caso: No requiere firma. Finalizamos inmediatamente cambiando el estatus a Validada.
                EvaluacionExtenso::validarEvaluacion($evaluacion_id, 'Validada', null);
                // Verificar consenso por si esto cierra el ciclo
                $ev = EvaluacionExtenso::buscarPorId($evaluacion_id);
                EvaluacionExtenso::verificarConsenso($ev['extenso_version_id']);

                echo json_encode([
                    'mensaje' => 'Evaluación finalizada correctamente.',
                    'redirect_url' => BASE_URL . 'revisorExtensos/dashboard'
                ]);
            }
        } else {
            http_response_code(500); echo json_encode(['error' => 'Error al guardar.']);
        }
    }

    public function subirPdfFirmado($evaluacion_id) {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) { http_response_code(403); echo json_encode(['error' => 'Permisos.']); return; }
        
        if (!isset($_FILES['pdf_firmado']) || $_FILES['pdf_firmado']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400); echo json_encode(['error' => 'Archivo no recibido.']); return;
        }
        
        // Validar tipo PDF
        if ($_FILES['pdf_firmado']['type'] !== 'application/pdf') {
            http_response_code(400); echo json_encode(['error' => 'Solo PDF.']); return;
        }

        $dir = BACKEND_ROOT . '/uploads/evaluaciones_firmadas/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        $ext = pathinfo($_FILES['pdf_firmado']['name'], PATHINFO_EXTENSION);
        $name = 'evaluacion_' . $evaluacion_id . '_firmada_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['pdf_firmado']['tmp_name'], $dir . $name)) {
            // Actualizar BD: Pone estado 'Pendiente de Validación'
            if (EvaluacionExtenso::guardarPdfFirmado($evaluacion_id, $name)) {
                echo json_encode(['mensaje' => 'Documento firmado subido. Pendiente de validación.']);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Error BD.']);
            }
        } else {
            http_response_code(500); echo json_encode(['error' => 'Error al mover archivo.']);
        }
    }

    // ... (guardarBorrador, completarPerfil, etc. siguen igual)
    public function completarPerfil() {
        if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
            redirect('');
        }
        if (Usuario::perfilRevisorEstaCompleto($_SESSION['usuario_id'])) {
            redirect('revisorExtensos/dashboard');
        }
        CSRFHelper::generateToken();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor_extensos/completar_perfil.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
    
    public function guardarPerfil() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id']) || !in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
            http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return;
        }
        
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

        if (isset($_FILES['comprobante_sni']) && $_FILES['comprobante_sni']['error'] === UPLOAD_ERR_OK) {
            $archivo_sni = $_FILES['comprobante_sni'];
            if ($archivo_sni['type'] !== 'application/pdf') {
                http_response_code(400); echo json_encode(['error' => 'El comprobante SNI debe ser un archivo PDF.']); return;
            }
            $extension = pathinfo($archivo_sni['name'], PATHINFO_EXTENSION);
            $comprobante_sni_ruta = 'sni_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
            move_uploaded_file($archivo_sni['tmp_name'], $directorio_revisores . $comprobante_sni_ruta);
        }

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
        
        if (Usuario::guardarPerfilRevisorExtenso($datos, $_POST['area_id'])) {
                echo json_encode(['mensaje' => 'Perfil completado con éxito. ¡Gracias por tu colaboración!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el perfil.']);
        }
    }

    public function guardarBorradorEvaluacion($evaluacion_id) {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) { http_response_code(403); echo json_encode(['error'=>'Permisos']); return; }
        $d = $_POST;
        $resp = []; for($i=1;$i<=6;$i++) $resp['pregunta_'.$i]=$d['pregunta_'.$i]??null;
        $datos = ['respuestas_formulario'=>json_encode($resp), 'observaciones_generales'=>$d['observaciones_generales']??null, 'veredicto'=>$d['veredicto']??'Pendiente', 'argumento_rechazo'=>$d['argumento_rechazo']??null];
        if(EvaluacionExtenso::guardarBorrador($evaluacion_id, $datos)) echo json_encode(['mensaje'=>'Borrador guardado']);
        else { http_response_code(500); echo json_encode(['error'=>'Error']); }
    }
}