<?php

// Asegúrate de que esta ruta sea correcta en tu estructura
define('FPDF_PATH', BACKEND_ROOT . '/app/lib/fpdf186/fpdf.php');

class ReporteController {

    public function generarEvaluacionPDF($evaluacion_id) {
        if (!isset($_SESSION['usuario_id'])) { die('Acceso denegado.'); }

        $evaluacion = EvaluacionExtenso::buscarPorId($evaluacion_id);
        
        if (!$evaluacion || $evaluacion['revisor_id'] != $_SESSION['usuario_id']) {
            die('No tienes permiso para ver esta evaluación o no existe.');
        }

        if ($evaluacion['veredicto'] !== 'Favorable y Publicable') {
            die('Este documento solo está disponible para evaluaciones con veredicto Favorable y Publicable.');
        }

        $revisor = Usuario::buscarPorId($evaluacion['revisor_id']);

        require_once FPDF_PATH;

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(20, 20, 20); 
        $pdf->SetAutoPageBreak(true, 20);

        $rutaLogo = BACKEND_ROOT . '/assets/img/logo.png'; 
        if (file_exists($rutaLogo)) {
            $pdf->Image($rutaLogo, 20, 15, 30); // X, Y, Ancho
            $pdf->Ln(5);
        }
        
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('DICTAMEN DE ARTÍCULO EXTENSO'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, utf8_decode('Fecha de emisión: ' . date('d/m/Y')), 0, 1, 'C');
        $pdf->Ln(10);

        // --- DATOS DEL ARTÍCULO ---
        $pdf->SetFillColor(240, 240, 240); // Gris claro para encabezados de sección
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode(' DATOS DEL ARTÍCULO'), 1, 1, 'L', true);
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 10, utf8_decode('Título:'), 0, 0);
        
        $pdf->SetFont('Arial', '', 11);
        // MultiCell para títulos largos
        $pdf->MultiCell(0, 10, utf8_decode($evaluacion['titulo']));
        $pdf->Ln(5);

        // --- RESPUESTAS DEL CUESTIONARIO ---
        $respuestas = json_decode($evaluacion['respuestas_formulario'], true);
        
        // Estas preguntas coinciden con tu HTML
        $preguntas = [
            1 => '¿Se plantea con claridad el tema abordado en el artículo?',
            2 => '¿Se presenta una fundamentación teórica pertinente?',
            3 => '¿Se integra contenido pertinente y relevante?',
            4 => '¿Los aspectos teóricos son suficientes?',
            5 => '¿Los hallazgos contribuyen a la reflexión/explicación?',
            6 => '¿Se presentan las referencias bibliográficas apropiadas?'
        ];

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode(' CRITERIOS DE EVALUACIÓN'), 1, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 11);
        foreach ($preguntas as $num => $pregunta) {
            $respuestaRaw = $respuestas['pregunta_'.$num] ?? 'N/A';
            $respuesta = ($respuestaRaw === 'si') ? 'SÍ' : (($respuestaRaw === 'no') ? 'NO' : 'N/A');
            
            // Imprimimos Pregunta
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(140, 6, utf8_decode($num . '. ' . $pregunta), 0, 'L');
            
            // Imprimimos Respuesta al lado (truco de cursor)
            $yActual = $pdf->GetY();
            $pdf->SetXY(160, $yActual - 6); // Movemos cursor a la derecha
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(30, 6, utf8_decode($respuesta), 0, 1, 'C');
            
            $pdf->Ln(2); // Espacio entre preguntas
        }
        $pdf->Ln(5);

        // --- OBSERVACIONES Y VEREDICTO ---
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode(' RESULTADO DE LA EVALUACIÓN'), 1, 1, 'L', true);
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, utf8_decode('Observaciones Generales:'), 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 6, utf8_decode($evaluacion['observaciones_generales'] ?? 'Sin observaciones.'));
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, utf8_decode('Veredicto Final:'), 0, 1);
        $pdf->SetFont('Arial', 'B', 14); // Más grande para resaltar
        // Ya sabemos que es "Favorable y Publicable", pero usamos el dato de la BD por consistencia
        $pdf->Cell(0, 10, utf8_decode(strtoupper($evaluacion['veredicto'])), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);

        // Se eliminó la lógica de "No Publicable" y argumentos de rechazo
        // ya que este documento está restringido solo para dictámenes favorables.
        
        $pdf->Ln(25); // Espacio para firma

        // --- FIRMA ---
        $pdf->Cell(0, 5, '_________________________________', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, utf8_decode($revisor['nombre_completo']), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 5, utf8_decode('Firma del Revisor'), 0, 1, 'C');

        // Nombre del archivo de descarga
        $pdf->Output('D', 'Dictamen_Favorable_' . $evaluacion_id . '.pdf');
    }
}