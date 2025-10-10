<?php

define('FPDF_PATH', BACKEND_ROOT . '/app/lib/fpdf186/fpdf.php');

class ReporteController {

    public function generarEvaluacionPDF($evaluacion_id) {
        if (!isset($_SESSION['usuario_id'])) { die('Acceso denegado.'); }

        $evaluacion = EvaluacionExtenso::buscarPorId($evaluacion_id);
        $revisor = Usuario::buscarPorId($evaluacion['revisor_id']);

        if (!$evaluacion || $evaluacion['revisor_id'] != $_SESSION['usuario_id']) {
            die('No tienes permiso para ver esta evaluación.');
        }

        require_once FPDF_PATH;
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        $pdf->Cell(0, 10, utf8_decode('Formato de Dictamen para Artículo Extenso'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Título del Artículo:'), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, utf8_decode($evaluacion['titulo']));
        $pdf->Ln(5);

        $respuestas = json_decode($evaluacion['respuestas_formulario'], true);
        $preguntas = [
            1 => utf8_decode('1. ¿Se plantea con claridad el tema abordado en el artículo?'),
            2 => utf8_decode('2. ¿Se presenta una fundamentación teórica pertinente?'),
            3 => utf8_decode('3. ¿Se integra contenido pertinente y relevante?'),
            4 => utf8_decode('4. ¿Los aspectos teóricos son suficientes?'),
            5 => utf8_decode('5. ¿Los aspectos metodológicos son suficientes?'),
            6 => utf8_decode('6. ¿Los hallazgos contribuyen a la reflexión?'),
        ];

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Criterios de Evaluación'), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        foreach ($preguntas as $num => $pregunta) {
            $respuesta = strtoupper($respuestas['pregunta_'.$num] ?? 'N/A');
            $pdf->MultiCell(0, 10, $pregunta . " - Respuesta: " . $respuesta, 0, 'L');
        }
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Observaciones Generales:'), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, utf8_decode($evaluacion['observaciones_generales']));
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Valoración Global:'), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, utf8_decode($evaluacion['veredicto']));
        
        if ($evaluacion['veredicto'] === 'No Publicable') {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode('Argumentos del Rechazo:'), 0, 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 10, utf8_decode($evaluacion['argumento_rechazo']));
        }
        $pdf->Ln(20);

        $pdf->Cell(0, 10, utf8_decode('Dictaminó:'), 0, 1);
        $pdf->Ln(15);
        $pdf->Cell(0, 10, '_________________________________', 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, utf8_decode($revisor['nombre_completo']), 0, 1, 'C');

        $pdf->Output('D', 'dictamen_evaluacion.pdf');
    }
}