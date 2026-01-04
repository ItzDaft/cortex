<?php

define('FPDF_PATH', BACKEND_ROOT . '/app/lib/fpdf186/fpdf.php');

class ReporteController {

    /**
     * Helper para decodificar texto UTF-8 a ISO-8859-1 compatible con FPDF.
     * Reemplaza a la función obsoleta utf8_decode() usando iconv para PHP 8.2+.
     */
    private function decode($text) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }

    /**
     * Obtiene el nombre del área basada en el ID.
     * AJUSTAR ESTOS VALORES SEGÚN TU BASE DE DATOS REAL.
     */
    private function obtenerNombreArea($area_id) {
        $areas = [
            1 => 'Ingenierías y Tecnología',
            2 => 'Ciencias de la Salud',
            3 => 'Ciencias Naturales y Exactas',
            4 => 'Ciencias Sociales y Administrativas',
            5 => 'Educación y Humanidades',
            6 => 'Ciencias Agropecuarias',
            7 => 'Artes y Arquitectura'
        ];
        return $areas[$area_id] ?? 'Área no especificada';
    }

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

        $campoConocimiento = 'No especificado';
        
        if (isset($evaluacion['extenso_id'])) {
            $extenso = Extenso::buscarPorId($evaluacion['extenso_id']);
            
            if ($extenso && isset($extenso['resumen_id'])) {
                $resumen = Resumen::buscarPorId($extenso['resumen_id']);
                
                if ($resumen && isset($resumen['area_id'])) {
                    $campoConocimiento = $this->obtenerNombreArea($resumen['area_id']);
                }
            }
        }
        
        // Fallback si no se encontró en la relación anterior, intentar buscar directo en evaluación si existe el campo
        if ($campoConocimiento === 'No especificado' && isset($evaluacion['area'])) {
             $campoConocimiento = $evaluacion['area'];
        }

        // Limpiar buffer
        if (ob_get_length()) ob_clean();

        require_once FPDF_PATH;

        // Instancia FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(20, 20, 20); 
        $pdf->SetAutoPageBreak(true, 20);

        // --- 1. ENCABEZADO Y LOGO ---
        $rutaLogo = BACKEND_ROOT . '/public/assets/img/logo.png'; 
        if (file_exists($rutaLogo)) {
            $pdf->Image($rutaLogo, 20, 10, 25); 
        }

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY(20, 10); 
        $pdf->Cell(0, 10, $this->decode('FORMATO PU/07'), 0, 1, 'R');
        
        $pdf->Ln(10); 
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->MultiCell(0, 6, $this->decode('CRITERIOS PARA EL ARBITRAJE EXTERNO DE EXTENSOS QUE PRETENDEN PUBLICARSE EN LA REVISTA'), 0, 'C');
        $pdf->Ln(5);

        // --- 2. DATOS DE IDENTIFICACIÓN ---
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, $this->decode('Datos de identificación del Extenso'), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 6, $this->decode('TÍTULO DEL Extenso:'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell(0, 6, $this->decode(strtoupper($evaluacion['titulo'])));
        $pdf->SetXY($x, $pdf->GetY()); 

        $pdf->Ln(2);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 6, $this->decode('TIPO DE CONTRIBUCIÓN:'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $this->decode('Divulgación'), 0, 1); 

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(55, 6, $this->decode('CAMPO DEL CONOCIMIENTO:'), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $this->decode($campoConocimiento), 0, 1); 
        $pdf->Ln(5);

        // --- 3. CRITERIOS (TABLA) ---
        $respuestas = json_decode($evaluacion['respuestas_formulario'], true);
        
        $preguntas = [
            1 => 'Se plantea con claridad el tema abordado en el artículo',
            2 => 'Se presenta una fundamentación teórica pertinente de acuerdo con el área de conocimiento en la cual se inscribe el tema',
            3 => 'Se integra contenido pertinente y relevante para el desarrollo del área de conocimiento',
            4 => 'Los aspectos teóricos que presenta el texto son suficientes para el análisis que presenta',
            5 => 'Los aspectos metodológicos que presenta el texto son suficientes para el desarrollo del tema',
            6 => 'Los hallazgos de la investigación contribuyen a la reflexión y/o explicación del tema tratado'
        ];

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, $this->decode('Criterios generales de evaluación'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 6, $this->decode('Contenido'), 0, 1, 'L');
        $pdf->Ln(2);

        // Encabezado tabla
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(150, 6, $this->decode('Criterio'), 0, 0);
        $pdf->Cell(20, 6, $this->decode('Evaluación'), 0, 1, 'C'); 
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 170, $pdf->GetY()); 
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 10);
        foreach ($preguntas as $num => $pregunta) {
            $respuestaRaw = $respuestas['pregunta_'.$num] ?? 'N/A';
            $respuesta = ($respuestaRaw === 'si') ? 'SÍ' : (($respuestaRaw === 'no') ? 'NO' : '-');
            
            $yInicio = $pdf->GetY();
            $pdf->MultiCell(150, 5, $this->decode($num . '. ' . $pregunta), 0, 'L');
            
            $yFin = $pdf->GetY();
            $altura = $yFin - $yInicio;
            
            $pdf->SetXY(170, $yInicio); 
            $pdf->Cell(20, $altura, $this->decode($respuesta), 0, 1, 'C');
            
            $pdf->SetY($yFin); 
            $pdf->Ln(2); 
        }
        $pdf->Ln(5);

        // --- 4. OBSERVACIONES ---
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, $this->decode('Observaciones generales:'), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        
        $obs = $evaluacion['observaciones_generales'] ?? '';
        if (empty($obs)) $obs = "Sin observaciones.";
        $pdf->MultiCell(0, 6, $this->decode($obs), 'B', 'L'); 
        $pdf->Ln(10);

        // --- 5. VALORACIÓN GLOBAL ---
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, $this->decode('Valoración global'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $this->decode('Con base en los criterios anteriores, considera usted que la obra es:'), 0, 1, 'L');
        $pdf->Ln(2);

        $v = $evaluacion['veredicto'];
        $m1 = ($v === 'Favorable y Publicable') ? 'X' : ' ';
        $m2 = ($v === 'Favorable con Correcciones') ? 'X' : ' ';
        $m3 = ($v === 'No Publicable') ? 'X' : ' ';

        $pdf->Cell(10, 6, $this->decode('*'), 0, 0);
        $pdf->Cell(120, 6, $this->decode('Favorable y Publicable sin recomendaciones'), 0, 0);
        $pdf->Cell(20, 6, $this->decode('( ' . $m1 . ' )'), 0, 1);

        $pdf->Cell(10, 6, $this->decode('*'), 0, 0);
        $pdf->Cell(120, 6, $this->decode('Favorable y Publicable con correcciones y/o modificaciones'), 0, 0);
        $pdf->Cell(20, 6, $this->decode('( ' . $m2 . ' )'), 0, 1);

        $pdf->Cell(10, 6, $this->decode('*'), 0, 0);
        $pdf->Cell(120, 6, $this->decode('No se recomienda su publicación'), 0, 0);
        $pdf->Cell(20, 6, $this->decode('( ' . $m3 . ' )'), 0, 1);

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Line($pdf->GetX(), $pdf->GetY()+6, $pdf->GetX() + 170, $pdf->GetY()+6);
        $pdf->Ln(10); 

        
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->Ln(10);

        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha = "Fecha del dictamen: " . date('d')." de ".$meses[date('n')-1]. " de ".date('Y');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $this->decode($fecha), 0, 1, 'R');

        $pdf->Ln(15); 

        
        $xCenter = 65;

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX($xCenter);
        $pdf->Cell(80, 0, '', 'T'); 
        
        $pdf->Ln(2); 
        
        $pdf->SetX($xCenter);
        $pdf->MultiCell(80, 5, $this->decode($revisor['nombre_completo']), 0, 'C');
        
        $pdf->SetX($xCenter);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(80, 5, $this->decode('Firma del Revisor'), 0, 0, 'C');

        $pdf->Output('D', 'Dictamen_Favorable_' . $evaluacion_id . '.pdf');
    }
}