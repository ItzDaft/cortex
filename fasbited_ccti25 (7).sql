-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 200.52.83.42:3306
-- Tiempo de generación: 01-02-2026 a las 02:30:32
-- Versión del servidor: 8.0.27
-- Versión de PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fasbited_ccti25`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas_tematicas`
--

CREATE TABLE `areas_tematicas` (
  `id` int NOT NULL,
  `nombre_area` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `areas_tematicas`
--

INSERT INTO `areas_tematicas` (`id`, `nombre_area`, `descripcion`) VALUES
(1, 'Fibras ópticas y sus aplicaciones', 'Área dedicada a la transmisión de información y señales mediante luz, con aplicaciones en telecomunicaciones, medicina y sensores avanzados.'),
(2, 'Materiales y energía', 'Investigación orientada al desarrollo de nuevos materiales y tecnologías que mejoren la generación, almacenamiento y uso eficiente de la energía.'),
(3, 'Matemáticas', 'Espacio para el estudio de modelos, teorías y métodos que permiten analizar, explicar y resolver problemas en distintas disciplinas científicas y tecnológicas.'),
(4, 'Tecnologías inteligentes', 'Área enfocada en sistemas innovadores que integran inteligencia artificial, robótica y automatización para optimizar procesos y mejorar la vida cotidiana.'),
(5, 'Ciencias biológicas y salud', 'Investigación sobre los procesos de la vida y su aplicación en la prevención, diagnóstico y tratamiento de enfermedades para mejorar la salud y el bienestar.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones_extensos`
--

CREATE TABLE `evaluaciones_extensos` (
  `id` int NOT NULL,
  `extenso_version_id` int NOT NULL,
  `revisor_id` int NOT NULL,
  `respuestas_formulario` json DEFAULT NULL,
  `observaciones_generales` text,
  `veredicto` enum('Pendiente','Favorable y Publicable','Favorable con Correcciones','No Publicable') DEFAULT 'Pendiente',
  `argumento_rechazo` text,
  `pdf_firmado_ruta` varchar(255) DEFAULT NULL,
  `estatus_evaluacion` enum('Pendiente','Pendiente de Firma','Pendiente de Validación','Validada','Rechazada por Coordinador') DEFAULT 'Pendiente',
  `comentarios_coordinador` text,
  `fecha_asignacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_evaluacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `evaluaciones_extensos`
--

INSERT INTO `evaluaciones_extensos` (`id`, `extenso_version_id`, `revisor_id`, `respuestas_formulario`, `observaciones_generales`, `veredicto`, `argumento_rechazo`, `pdf_firmado_ruta`, `estatus_evaluacion`, `comentarios_coordinador`, `fecha_asignacion`, `fecha_evaluacion`) VALUES
(17, 19, 345, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'El tema abordado es actual y pertinente. La estructura general del documento es clara y adecuada, se apoyan en buenas fuentes de consulta y las conclusiones se presentan de manera coherente.\r\nComentarios Mayores\r\nEn la sección de Resumen hace falta mencionar de forma explícita los aspectos más relevantes de la metodología, así como presentar los resultados más sobresalientes del estudio, de manera que el texto resulte más atractiv. Además, sería conveniente que, al final del resumen, se incluyera una breve referencia a las aplicaciones o principales conclusiones del trabajo.\r\nEn la Introducción, se recomienda describir la estructura del artículo, indicando cuáles son los temas y secciones que se presentan a lo largo del documento.\r\nEn cuanto a la Metodología, se esperaría que se detallaran los pasos y métodos seguidos para llevar a cabo la experimentación del trabajo. Sin embargo, en su lugar se presenta un marco teórico que describe los elementos utilizados en el estudio. Falta describir el diseño experimental o de evaluación (dispositivos empleados, versiones de software, criterios de medición, número de repeticiones de las pruebas, etc.). En consecuencia, el documento no explica cómo se obtuvieron los resultados, por ejemplo, las calificaciones de la Tabla 1. También hacen falta detalles sobre la aplicación práctica de la visión por computadora, ya que no se muestra ningún ejemplo concreto de aplicación en el que se haya probado cada framework. Se sugiere agregar un breve apartado sobre el diseño de evaluación en el que se describan claramente los criterios considerados para la comparación y elección de los frameworks.\r\nEn el subapartado dedicado a Flutter, bastaría con una sola explicación bien estructurada acerca de FFI (Foreign Function Interface), evitando repeticiones. En el subapartado correspondiente a Qt, sería recomendable incluir un ejemplo breve del tipo de aplicaciones en las que suele emplearse este framework.\r\nLa sección de Pruebas y Resultados se percibe incompleta debido a la falta de una metodología claramente descrita, ya que no se indica de dónde provienen los valores numéricos ni cuáles fueron las pruebas realizadas para decidir si una característica recibe una calificación de 2, 3 o 4. Por ello, se solicita explicar con claridad el origen de los datos y el procedimiento seguido para asignar dichas calificaciones.\r\nComentarios menores\r\nSe recomienda una revisión a profundidad del texto debido a diversos detalles de escritura. Por ejemplo, en el título, la palabra Desarrollo debe llevar mayúscula inicial. Asimismo, se identifican varias frases demasiado extensas, uso incorrecto de comas y algunos errores tipográficos (como “Multiplataformaa” ó “Q t”). También es aconsejable evitar la repetición frecuente de expresiones como “como resultado”, “por otro lado” o “sin embargo”. En su lugar, se sugiere alternar conectores como “además”, “no obstante” o “en consecuencia” para mejorar la fluidez del texto.\r\n', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2025-12-22 03:38:20', '2026-02-01 02:05:19'),
(18, 19, 349, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"si\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', '1. Revisar errores de escritura, ya que deja espacio o no después de los puntos seguidos y puntos finales.\r\n2. En la introducción presentar los trabajos relacionados más importantes.\r\n3. En Metodología revisar el tipo de letra en los subtítulos \r\n4. En Metodología, subtítulo \"C. QT\", revisar el final del primer párrafo que sí sea la información correcta.\r\n5. En Metodología, subtítulo \"C. QT\", sección \"1. Arquitectura\" revisar el primer párrafo \"... concurrencia y hilo\"\r\n6. En Resultados, ¿Qué características presentaban los elementos con los que se realizaron los análisis y comparativas de la tabla presentada? ¿Qué tipo de cámara?¿Se implementó en algún dispositivo?\r\n7. Aumentar la cantidad de referencias y expandir la información de las referencias. ', 'No Publicable', 'Se considera que lo presentado requiere un análisis más profundo, con mayores referencias, en el que se expongan claramente las características que llevaron a obtener los resultados.', NULL, 'Validada', NULL, '2025-12-22 03:38:20', '2026-02-01 02:05:19'),
(32, 7, 343, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'Los autores brindan claramente el contexto del problema a resolver y proponen e implementan un sistema fotovoltaico de seguimiento solar y lo comparan contra un sistema fijo. Enmarcan adecuadamente su experimentación y realizan una comparativa basada en gráficas que permite ver ventajas de su propuesta.', 'Favorable y Publicable', NULL, 'evaluacion_32_firmada_1769583006.pdf', 'Validada', NULL, '2026-01-06 01:12:25', '2026-02-01 02:05:19'),
(33, 7, 346, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"si\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'Este trabajo propone un sistema de panel solar móvil que busca aprovechar la mayor cantidad de irradiancia solar para obtener la mayor cantidad de energía necesaria. El trabajo requiere incluir más aspectos para fortalecer su contribución.\r\n\r\n1. Se deben incluir fundamentos teóricos asociados al diseño del panel solar.\r\n2. No hay un formalismo matemático para el diseño del panel solar tanto en el aspecto de la física mecánica como en el diseño eléctrico. Algunos de los detalles son: ¿qué tipo de controlador aplicó o implementó? \r\n3. Sería importante realizar una comparación con otros trabajos abordados en la literatura. Para resaltar la diferencia entre este trabajo y los ya publicados en la literatura, también hay que indicar la zona (Coordenadas) donde se captaron los datos.\r\n4. Incluir una Sección de Discusión.\r\n5. Extender las conclusiones incluyendo trabajos futuros\r\n7. Hay muy pocas referencias, incluir más referencias que al menos se llegue a 15 referencias\r\n\r\n\r\n', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-06 01:12:25', '2026-02-01 02:05:19'),
(34, 34, 346, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'Este trabajo propone implementar una red de Long Short-Term Memory para la traducción automática del lenguaje de señas mexicano mediante Leap Motion. Este  trabajo requiere incluir algunos aspectos para la mejora de su contribución:\r\n\r\n1. El título está mal redactado porque hay dos redundancias pleonasémicas: \"señas del lenguaje de señas mexicano\" y una redundancia técnica: \"Leap Motion basado en visión infrarroja\". Se supone que el dispositivo funciona por defecto y utiliza la luz infrarroja para operar; no es configurable. \r\n\r\n2.  Falta argumentar teóricamente qué es importante describir, como, por ejemplo, la configuración básica de una red LSTM y justificar su uso, porque se uso la LSTM y no otra técnica.\r\n3. Faltó especificar el proceso de selección de las características. Por otro lado, hay un detalle importante que no queda claro, el trabajo indica que hay 244 características y hay 346 muestras. Si es así, el modelo podría tener serios problemas con el tiempo de entrenamiento. La cantidad de características representa el 70% de las muestras, lo que puede resultar contraproducente para el entrenamiento del modelo.  \r\n4. Respecto al punto 2. Es altamente recomendable comparar con otras técnicas de ML, incluidas las tradicionales. Incluyendo tiempo computacional.\r\n\r\n\r\n\r\nAunque es un resultado preliminar, en general, hay muchos detalles técnicos básicos del aprendizaje automático que hay que explicar con más detalle y mejorar la redacción. El artículo, en su presentación actual, resulta marginalmente recomendable para su publicación. Propongo que cambie el título y amplíe los experimentos con diferentes algoritmos de aprendizaje automático, como: Random Forest, SVM, LDA, y también vincule técnicas de reducción/selección de características como PCA.\r\n\r\nConsidero: Revisión mayor\r\n\r\n\r\n\r\n \r\n\r\n\r\n\r\n', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-06 01:24:38', '2026-02-01 02:05:19'),
(35, 34, 345, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', '1. En la Figura 1, en la etapa 4 del procesamiento de datos, la palabra\r\npreprocesamiento aparece separada incorrectamente (“preprocesamient-o”).\r\nDebe corregirse la separación o, en su defecto, ampliarse el tamaño del\r\nrecuadro para que la palabra se muestre completa.\r\n2. Se sugiere incluir la referencia correspondiente al repositorio de GitHub\r\nmencionado en el párrafo de la página 4.\r\n3. Considere incluir la referencia correspondiente a la librería Tkinter en el\r\npárrafo de la página 5.\r\n4. En la etapa de preprocesamiento (página 7), no se especifica si la partición de\r\nlos datos se realizó a nivel de secuencia (secuencia_ID), garantizando que\r\ntodos los frames de una misma muestra permanezcan en un único\r\nsubconjunto (entrenamiento o prueba).\r\nDado que un mismo sujeto podría aparecer tanto en entrenamiento como en\r\nprueba, en caso de no haberse considerado este aspecto, se recomienda\r\nrepetir los experimentos. Idealmente, las 22 secuencias de cada seña\r\ncorrespondientes a un sujeto deberían pertenecer exclusivamente a\r\nentrenamiento o exclusivamente a prueba.\r\n5. Considere incluir curvas ROC como parte de los resultados del entrenamiento\r\ndel modelo, ya que podrían aportar información adicional sobre su\r\ndesempeño.\r\n6. En la etapa de evaluación del modelo (página 8), especifique las fórmulas\r\nutilizadas para las métricas reportadas o, alternativamente, citar un trabajo\r\ndonde estas se describan formalmente.\r\n7. En términos generales, la redacción del artículo es buena.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-06 01:24:38', '2026-02-01 02:05:19'),
(36, 29, 343, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"si\"}', 'Los autores proponen un sistema de activación de semáforo basado en Redes LSTM. El tema está bien introducido, se entiende, el contexto e importancia de la propuesta. Sin embargo, la estructura y redacción del artículo posterior a la sección introductoria debe ser mejorada, ya que no se siente fluida. Establecer claramente la sección de marco teórico, metodología propuesta, experimentos, resultados y conclusiones. También hay que mejorar las gráficas para que sean más legibles. Además, se debe tener cuidado en los elementos insertados, por ejemplo en el apartado IV se hace referencia a una tabla III, cuando solamente hay una tabla en el documento. El trabajo hace referencia a dos metodologías del estado del arte, es necesario comparar sus resultados contra la propuesta en términos del dataset planteado para finalmente realizar una comparativa o análisis estadístico y así argumentar mejoras o ventajas.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-06 01:26:55', '2026-02-01 02:05:19'),
(37, 29, 349, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', '1. En el párrafo donde se presenta \"Esto trae como consecuencia que, cada año, millones de horas y toneladas de combustible se pierdan mientras los conductores esperan en intersecciones controladas...\", falta referencia donde se presentan esos análisis.\r\n2. Mejorar calidad de Fig. 2 y Fig. 3.\r\n3. Revisar la ausencia de espacios entre palabras en algunas partes del artículo.\r\n4. Presenta \"Figura 3.a\" al parecer debe ser \"Fig. 3.a\".\r\n5. Revisar variables. ¿Deben ir todas en itálica? En caso de que sí, estandarizarlas, ya que hay unas que faltan.\r\n6. Estandarizar la presentación de los incisos en las figuras, ¿Fig. 5(a) o Fig. 3.a?\r\n7. Agregar títulos en los ejes de las gráficas de la Fig. 5.\r\n8. Incrementar la cantidad de Referencias y revisarlas.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-06 01:26:55', '2026-02-01 02:05:19'),
(38, 25, 337, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'De las fortalezas de este trabajo destaco lo siguiente:\r\nTema pertinente y bien alineado con fotónica basada en silicio\r\nEl trabajo aborda un problema relevante y actual: la obtención de emisión luminosa eficiente en estructuras compatibles con tecnología CMOS, integrando SiP, SRO, OG y ZnO. La combinación de materiales está bien justificada y se apoya en literatura sólida.\r\nLas rutas de fabricación están claramente descritas (anodización, spin coating, HFCVD, SPU), con parámetros detallados que permiten reproducibilidad. El uso de varias densidades de corriente de anodizado añade un componente comparativo valioso.\r\nEl análisis de fotoluminiscencia es profundo, incluyendo normalización de espectros, discusión física de los mecanismos de emisión y una deconvolución bien fundamentada que conecta picos espectrales con defectos y centros emisores específicos.\r\nDe las cosas a mejorar:\r\nInconsistencia entre el color observado y los espectros de electroluminiscencia\r\nLa emisión se describe visualmente como azul, mientras que los máximos de los espectros EL se localizan en la región verde–amarilla (≈570–600 nm). Es necesario aclarar esta discrepancia mediante:\r\nuna explicación basada en la superposición espectral y la percepción visual, o\r\nun análisis colorimétrico (por ejemplo, coordenadas CIE) hay un parche para origin con el cual hacerlo fácilmente.\r\nSegundo punto a mejorar:\r\nLos dispositivos requieren polarizaciones elevadas (–150 a –210 V). Falta una discusión que:\r\ncompare estos valores con trabajos similares,\r\nanalice los mecanismos de inyección dominantes, y\r\nevalúe la viabilidad práctica del dispositivo.\r\nY por último, ausencia de evidencia estructural directa de la infiltración del OG\r\nSe asume una infiltración homogénea del óxido de grafeno en el SiP, pero no se presentan técnicas complementarias (SEM, AFM, TEM, Raman) que lo confirmen de manera directa.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:09:28', '2026-02-01 02:05:19'),
(39, 25, 339, '{\"pregunta_1\": \"no\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"si\"}', 'Los autores presentan resultados del trabajo “Luminiscencia de Óxido de Grafeno con Nanoestructuras de Óxido Rico en Silicio”, en el cual se trató de analizar la luminiscencia de heteroestructuras basadas en silicio poroso, óxido de grafeno y películas de óxido de silicio rico en silicio (SRO), sin embargo, el documento presenta muchas fallas en su planteamiento, metodología, detalles experimentales y presentación y discusión de resultados. A continuación, se dan algunos detalles que el revisor considera fallas de concepto o mala redacción cuando menos en la presentación del trabajo:\r\nPágina 3. Los autores dicen que se utilizaron sustratos de Si tipo n, sin embargo, para la formación de poros se requiere de huecos en la interfaz Si/electrolito, y los sustratos de Si tipo n tienen una muy baja concentración de huecos, por lo que se requiere usar una fuente de luz (iluminación) para generarlos.\r\nPágina 3. Por otro lado, la resistividad utilizada de la oblea de Si tipo n, es muy baja (0.005 .cm2), por lo que deberían revisar y contemplar el uso de obleas con una mayor resistividad. Por lo que deberían aclarar el objetivo de usar obleas con una resistividad tan baja. Algunas veces puede llevar a la degradación de la capa de SiP, o no formarse.\r\nPágina 3. No se aclara si el etanol utilizado es grado industrial o grado reactivo (de alta pureza)\r\nPágina 3. Los autores dicen “La infiltración de OG en las capas de SiP se realizó mediante Spin Coating”, ¿pero no muestran evidencias de la infiltración? ¿Cómo saben si sólo se quedó el OG en la superficie?\r\nPágina 3. Los autores dicen “Para encapsular el OG dentro del SiP y proporcionar estabilidad adicional, se depositaron películas de óxido de silicio rico en silicio (SRO) utilizando…” sin embargo, solo dan por hecho de que la infiltración ocurre, pero no se muestran resultados de este hecho.\r\nPágina 3. Los autores dicen “Durante la deposición, se mantuvo un flujo constante de 100 sccm de hidrógeno molecular (H2) y…”, pero no dicen cuál es el papel que juega el H2 molecular en el crecimiento del SRO, ni de cómo se formó, y de donde se obtuvo el oxígeno, esto, por un lado. Por otro lado, no se dice que gas reactivo se usó para el depósito de nc-Si, que precursor de silicio. Tampoco se da información sobre las concentraciones usadas.\r\nPágina 4. No se dan detalles experimentales para el depósito de películas de ZnO, no se mencionan los parámetros de depósito, solo se dan algunos detalles sobre la disolución, pero no se reportan las concentraciones molares, se dan cantidades en gramos, algo poco común: “La solución de ZnO se preparó disolviendo 2,195 g de acetato de zinc deshidratado Zn(O2CCH3)2(H2O)2 en 50 ml de metanol, con una concentración de 0,2 M.” \r\nPágina 4. Las cantidades en gramos se expresan por ejemplo como 2,195 g, así que no queda claro si son en realidad 2195 gramos o 2.195 gramos.\r\nPágina 4. Se dice que se usó un nebulizador citizen CUN-60, para el depósito.  De que? de las películas? Buscando en internet, encontré la siguiente información en Amazon.  https://www.amazon.com.mx/Citizen-CUN-60-Nebulizador-Ultrasonico-blanco/dp/B00D6RHXV8, entonces la pregunta es, es este el equipo utilizado?\r\nPágina 4. Sobre el depósito de los contactos de oro, no se dan detalles del sistema de sputtering, ¿qué cantidad de oro?, ¿qué pureza?  ¿qué espesores? ¿a qué temperatura se realizó el depósito? etc.\r\nPágina 4. Los autores dicen “Finalmente, las heteroestructuras se sinterizaron a 450 °C en atmósfera de nitrógeno.” ¿Cuál es el objetivo de tratar térmicamente toda la estructura? ¿Porque se realizó el TT a 450°C?\r\nLa figura 3 citada en el texto no corresponde a la Figura 3, esta repetida.\r\nPágina 6. Los autores dicen “…se identifica una banda adicional situada entre 400 nm y 500 nm.”, pero no se ofrece explicación alguna sobre su origen.\r\nPágina 6. Los autores dicen “… El efecto de confinamiento cuántico en los nanocristales de silicio (nc-Si) y los procesos asociados con defectos dentro del material”, pero no se ha demostrado con resultados la existencia o formación de tales nc-Si, es necesario presentar imágenes de SEM de alta resolución y estudios adicionales como de respuesta óptica para que se corrobore este dicho.\r\nPágina 6. “La Figura 5 presenta los espectros de FL de las estructuras Si-n/SiP/OG/SRO,…” pero se hace necesario mostrar resultados por separado de cada capa para observar la contribución de la respuesta de FT  en la estructura completa.\r\nPágina 7. Los autores dicen “Este procedimiento permitió distinguir los diferentes fenómenos que intervienen en el proceso de emisión, los cuales se resumen en la Tabla 2.” Sin embargo, toman una referencia para tratar de explicar sus resultados de FL, sin más resultados de caracterización. Es necesario probar que todos los defectos realmente estén presentes en la emisión de FL de las estructuras Si-n/SiP/OG/SRO, tal como se estan listados en la tabla 2.\r\nPágina 7. El texto citado en la tabla 2, aparece antes y después de la tabla.\r\nPágina 8. Toda la discusión del primer párrafo, es aplicable para las referencias citadas, sin embargo, los autores de este trabajo deberán dar prueba con sus propios resultados de la caracterización.\r\nPágina 9. El texto antes y después de la Figura 6 es lo mismo, se repite.\r\nPágina 9. La luz azul observada en la Figura 7, es debido a una respuesta real o a un alto voltaje aplicado, aclarar y justificar.\r\nPágina 10. Es importante realizar el estudio de EL para cada capa por separado, así se obtendrá una respuesta clara de cual material es el responsable de la emisión de esa luz azul en los resultados presentados.\r\n\r\nPor todas las observaciones aquí citadas el manuscrito NO es aceptable para su publicación.\r\n\r\n', 'No Publicable', 'Los autores presentan resultados del trabajo “Luminiscencia de Óxido de Grafeno con Nanoestructuras de Óxido Rico en Silicio”, en el cual se trató de analizar la luminiscencia de heteroestructuras basadas en silicio poroso, óxido de grafeno y películas de óxido de silicio rico en silicio (SRO), sin embargo, el documento presenta muchas fallas en su planteamiento, metodología, detalles experimentales y presentación y discusión de resultados. ', NULL, 'Validada', NULL, '2026-01-10 17:09:28', '2026-02-01 02:05:19'),
(40, 8, 329, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'El desarrollo de partículas de SiO2 hidrofóbicas no es nuevo, sin embargo, un reporte presentando resultados de manera lógica y sistemática puede ser aceptable para publicación en el proceedings del congreso. Para ello tengo las siguientes observaciones, que deben ser atendidas para poder recomendar el artículo para publicación:\r\n1. El SiO2 puede ser tanto hidrofóbico como hidrofílico, dependiendo de los grupos funcionales que queden expuestos. De hecho, lo más común es que el material sea hidrofílico. Por favor citar varios trabajos de ambos casos. \r\n2. ¿Qué característica le da la hidrofobicidad al SiO2 preparado? En la metodología se habla de que se funcionalizan las partículas, pero no se dice qué le pasa a las partículas con ese proceso. ¿Qué grupos funcionales se forman?\r\n3. Recomiendo orientar la introducción al objetivo señalado al final de esa sección: \"...la síntesis de NPs de SiO2 hidrofóbicas por un método sol-gel alternativo...\". Tocar de manera lógica todos los puntos descritos en el objetivo, yendo de lo general a lo particular. No es necesario empezar hablando desde qué es \"nanotecnología\". Hay que justificar mejor el trabajo (¿por qué se quiere realizar? y ¿qué problemática se quiere atender?).\r\n4. Hablando de las pruebas de hidrofobicidad, por favor indicar cómo se comporta en esas pruebas el papel filtro sin NPs.\r\n5. Cuidar acentos, gramática y las unidades usadas para las diferentes variables.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:10:34', '2026-02-01 02:05:19'),
(41, 8, 327, NULL, NULL, 'Pendiente', NULL, NULL, 'Pendiente', NULL, '2026-01-10 17:10:34', NULL),
(42, 35, 326, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'Reporte de revisión\r\n1. Recomendación: revisión menor\r\n\r\n2. Comentarios para los autores:\r\n\r\nTítulo del manuscrito: Desempeño de concretos con sustitución parcial de vidrio expuestos en el puerto de Telchac, Yucatán\r\n\r\nAuthors: No visibles\r\n\r\nPlataforma CORTEX: Universidad Autónoma Benito Juárez de Oaxaca \r\n\r\nRecomendaciones generales y comentarios:\r\n\r\n2.1. Comentarios mayores:\r\n\r\nLos autores proponen reciclar el vidrio de botellas en proporciones iguales de colores: verde, azul, ámbar y transparente, al incorporarse parcialmente como material cementario en pastas, morteros y concretos. Por esta razón, los autores preparan muestras de agua (a)/cemento (c) en una proporción a/c=0.4, 0.7, sustituyendo el 15 % de cemento CPC-30R por vidrio pulverizado y tamizado con tamaño de partícula entre 20 a 30 micras. Posteriormente, se realizaron ensayos de RMC a los 28 y 90 días de curado, siguiendo la norma ASTM C39/C39M-23 con la finalidad de evaluar la durabilidad en un ambiente marino y altamente corrosivo (130.5 mg de iones Cl al día por metro cuadrado). Los resultados de las pruebas de resistencia mecánica a la compresión de las muestras con a/c=0.4 tienen un menor desgaste a la corrosión a los 24 meses con mediciones cada 6 meses. \r\n\r\nEl manuscrito propuesto por los autores tiene aplicabilidad inmediata en el cuidado al medio ambiente, así como la eliminación y aprovechamiento de residuos sólidos en la industria del cemento. La estructura del manuscrito está bien planteada, la metodología bien organizada y los resultados presentados son claros y concisos para soportar las conclusiones. El manuscrito bajo escrutinio, es interesante y ofrece una alternativa de reciclado de residuos de botellas de vidrio en la industria cementera. Aunque, el manuscrito tiene varios detalles por corregir, recomiendo ampliamente su publicación después de algunas correcciones menores y comentarios que los autores deben atender. \r\n\r\n    1. Se solicita a los autores incluir en el resumen los datos duros de la metodología, así como los datos obtenidos en los resultados. Por ejemplo, algunos de los datos presentados en el primer párrafo de la sección 2.1 Comentarios mayores de este reporte de revisión.\r\n    2. Los autores en la sección: II. DESARROLLO, en la sub-sección: A. METODOLOGÍA, en el primer párrafo de la linea: “proceso de molienda durante 8 horas en un molino de bolas casero.” se hace referencia a un “molino de bolas casero” y no se proporciona mas información sobre dicho equipo. Describir con todo detalle el molino de bolas casero. El conocimiento científico debe ser registrado de tal forma que pueda ser reproducible por cualquier lector. Más aún, en el caso de tener un equipo que no está registrado. La información puede ser registrada en un Apéndice anexo al manuscrito y debidamente referenciada dentro de este párrafo en la linea: ... un molino de bolas casero (ver Apéndice).\r\n    3. En la sección: III. RESULTADOS Y DISCUSIÓN, sub-sección: A. CARACTERIZACIÓN DE VIDRIO, en el primer párrafo y sus lineas finales se hace referencia al tamaño de partícula promedio. Sin embargo, no se especifica si se refiere al agregado fino que se registra en la Tabla 1. Y tampoco se da información sobre el agregado grueso. Agregar el tamaño de partícula promedio del agregado grueso que se muestra en la Tabla1. Especificar estos datos para eliminar estas ambigüedades y agregar esta información en la Tabla 1 para completar la información.\r\n    4. Los autores no presentan los datos de las Figuras 5 y 6 dentro del manuscrito. Agregar una tabla con los valores de las mediciones obtenidas incluyendo el error de ajuste. Incluir la función de ajuste y el valor de sus coeficientes de ajuste. \r\n\r\n2.2. Comentarios menores:\r\n    • Se sugiere a los autores eliminar todos puntos finales de los títulos de las secciones y sub-secciones a lo largo de todo el manuscrito.\r\n    • En la página 3, primer párrafo de la sub-sección, linea: “cuales 2.8 corresponderán a RSU (Maalouf et al., 2022; World Bank, 2018). Por otro lado,”, se utilizan las palabras “Por otro lado,” y se sigue escribiendo con “punto y seguido”. Estas palabras se emplean para indicar un nuevo párrafo con otra idea central. Así que iniciar un nuevo párrafo con las palabras “Por otro lado,”.\r\n    • En la página 8, linea: Cinvestav-Mérida). La tasa promedio de … corregir  “130.5 mg Cl-/m2.día” como 130.5 mg Cl-/m² por día.\r\n    • Ampliar la foto como una figura con a) en la Fig. 3. La figura como foto de recuadro no se observa con detalle.\r\n    • En la sub-sección: B. RESISTENCIA MECÁNICA A LA COMPRESIÓN, primer párrafo, linea: En el caso de la relación 0.70, las muestras con contenido de vidrio aumentaron … sustituir la palabra “aumentaron” por “disminuyeron”.\r\n    • En la misma sub-sección, mismo párrafo y linea: las partículas de cemento, lo cual contribuye a la formación de C-S-H, producto de reacción … especificar el significado de las siglas “C-S-H”. Tal vez los autores quisieron decir: enlaces Carbono, Azufre e Hidrógeno. Eliminar dicha ambigüedad.\r\n    • Especificar el significado de las siglas Ecorr y SCE en toda la sección: D. DURABILIDAD.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:11:53', '2026-02-01 02:05:19'),
(43, 35, 333, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'Favorable y Publicable sin recomendaciones', 'Favorable y Publicable', NULL, 'evaluacion_43_firmada_1769448513.pdf', 'Validada', NULL, '2026-01-10 17:11:53', '2026-02-01 02:05:19'),
(44, 20, 330, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'Se sugiere utilizar un mismo tipo de letra en las referencias.\r\nSe sugiere adicionar una figura esquemática o diagrama de bloques donde se presenten de manera integrada los principales elementos del sistema (subsistema fotovoltaico, unidad de control ESP32, sensores, actuadores y almacenamiento de datos).', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:14:50', '2026-02-01 02:05:19'),
(45, 20, 357, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"si\"}', 'El revisor considera que se debe mejorar los resultados obtenidos, no se observan gráficas del monitoreo y/o control realizado, no se presenta evidencia de que la propuesta sea realmente de bajo costo, no hay un análisis de costo, tampoco se presenta un estudio de trabajos a futuro, para optimizar el diseño actual y que permita evaluar la calidad del secado con la propuesta tecnológica presentada.\r\n\r\nSe recomienda a los autores considerar los comentarios del revisor y presentar una versión actualizada, para su revisión y posible aceptación.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:14:50', '2026-02-01 02:05:19'),
(48, 28, 365, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'En la introducción falta mencionar la problemática que da origen al análisis del Corredor Interoceánico del Istmo de Tehuantepec.\r\n\r\nSe recomienda incluir un breve diagnóstico sobre el nivel actual de madurez digital de las PyMEs locales en la región del Istmo, para contextualizar el reto de implementar plataformas de alto costo.\r\n\r\nEn la página 5, se menciona primero el acrónimo ASIPONAS, sin haber puesto el nombre completo, el cual está líneas más abajo. Colocar el nombre en la primera mención del acrónimo.\r\n\r\nEn la misma página 5 hay que mencionar que significa T-MEC.\r\n\r\nEn la figura 4, poner el nombre en inglés de CSV.\r\n\r\nPágina 11, corregir la palabra realientizar\r\n\r\nSería interesante que se discutiera la disponibilidad de la conectividad (5G o fibra óptica) en la zona geográfica del Istmo, que es un habilitador crítico para la Industria 4.0\r\n\r\nLa bibliografía lleva numeración\r\n1.	Libro Formato: Autor, A. A. (Año). Título del libro en cursiva. Editorial.  \r\n', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:50:03', '2026-02-01 02:05:19'),
(49, 28, 344, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', '- Se debe de ser explicito en la metodología usada en el articulo, sería bueno incorporar una sección. \r\n- Se sugiere incorporar la aportación del articulo actual con respecto a los estudios previos. \r\n- Hay que hacer una revisión de estilo.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:50:03', '2026-02-01 02:05:19'),
(50, 18, 360, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'El artículo presenta el diseño, construcción y análisis cinemático de un robot móvil compuesto por un brazo antropomórfico y una base con orugas, fabricado mediante impresión 3D y controlado con servomotores y Arduino. Se emplea el algoritmo de Denavit-Hartenberg para modelar la cinemática directa del brazo y se describe el proceso de diseño e integración mecánica. El sistema está orientado a tareas de recolección de residuos en terrenos irregulares, aunque el trabajo se centra en la implementación física y modelado matemático básico, y presenta inconsistencias en la definición del sistema, hay una confusión en el resumen se dice que tiene 5, y en el abstract me mencionan 3 GDL). Existe una falta de claridad en el alcance del análisis cinemático, ya que no se aclara si el análisis cinemático incluye solo al brazo o al brazo y la base móvil. El articulo carece de resultados concretos, validación experimental o comparación con el estado del arte, no se muestra como al robot resolviendo un problema real, se podría por ejemplo, calcular la posición del efector final para una configuración articular dada, e incluir gráficas de simulación, trayectorias y espacio de trabajo. Las referencias bibliográficas están incompletas y repetidas. La discusión no aporta hallazgos sino dificultades del proceso, ya que solo es una lista de problemas, se debe cambiar a un análisis de resultados y que podría mostrar una comparación con soluciones existentes.', 'No Publicable', 'No se recomienda su publicación en el estado actual, ya que el artículo presenta deficiencias sustanciales en rigor metodológico, claridad expositiva y aportaciones. Se detectan inconsistencias fundamentales en la especificación técnica, falta de resultados cuantitativos o simulaciones validadas, ausencia de contextualización dentro del estado del arte, y referencias bibliográficas repetidas y desactualizadas. Además, la discusión se limita a enumerar dificultades prácticas sin ofrecer hallazgos, análisis comparativo o reflexión sobre la contribución real del sistema. ', NULL, 'Validada', NULL, '2026-01-10 17:54:43', '2026-02-01 02:05:19'),
(51, 18, 365, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'El trabajo presenta una propuesta interesante de integración de hardware accesible (impresión 3D y Arduino) con modelado matemático formal. Sin embargo, para que el artículo sea publicado, se requieren mejoras significativas en la estructura, la claridad técnica y la presentación de resultados:\r\n\r\nConsistencia en los Grados De Libertad: El resumen menciona 5 grados de libertad (GDL) , pero el abstract mencionan 3 grados de libertad. No obstante, el análisis de Denavit-Hartenberg (D-H) utiliza 5 matrices (A1 a A5). Se debe unificar esta información en todo el texto\r\n\r\nLa Fig. 1 es difícil de leer debido a la superposición de etiquetas. Se recomienda redibujar el diagrama, por ejemplo, el sistema de coordenadas del actuador cilíndrico: q1,  tiene elementos sobrepuestos que dificultan su vista.\r\nEn la Tabla I y las ecuaciones, aparecen valores como L1, L2, L3, L4. Se debe incluir una tabla con los valores numéricos reales (por ejemplo, mm o cm) utilizados en el prototipo físico, con el objetivo de que el lector pueda replicar los cálculos.\r\nSe debe poner una imagen con el prototipo final (brazo y oruga), en caso de que no se tenga espacio, se pueden juntar las piezas de la figura 9 en una línea y poner 9(a), 9(b), 9(c). \r\nSe menciona que la impresión 3D obtuvo un bajo rendimiento por lo que hubo una “frustración” considerable, en este caso se recomienda eliminar esta observación mediante un análisis técnico, por ejemplo, ¿hubo algún problema con los servomotores?, ¿cuál fue el error en milímetros entre la posición teórica y la real que se alcanza con la garra?\r\nEn general, revisar la ortografía, por ejemplo, en la página 2 se tiene 2 puntos:  por un dispositivo automatizado. [1]. , en la página 7 la palabra mas no tiene el acento, así como CONSLUSIONES.  \r\nSe debe evitar el uso de la primera persona como \"analizamos\" o \"nos apoyamos\".\r\nLa figura 2 se ve en baja resolución, mejorar.\r\nLa forma de citar dentro del texto se apegará al formato APA7. Ejemplo: (García, 1985). \r\n', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-10 17:54:43', '2026-02-01 02:05:19'),
(52, 23, 366, NULL, NULL, 'Pendiente', NULL, NULL, 'Pendiente', NULL, '2026-01-10 23:33:12', NULL),
(53, 23, 369, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'El trabajo presenta el diseño, modelado y construcción de un brazo robótico de tres grados de libertad (3-GDL), fabricado mediante impresión 3D utilizando material PLA y controlado mediante protocolo serial. Además, describen que en el documento desarrollaron un modelo dinámico del sistema empleando el método de Euler-Lagrange, lo que permite describir el comportamiento dinámico de los eslabones en función de sus energías cinética y potencial.\r\n', 'No Publicable', 'El trabajo presenta el diseño, modelado y construcción de un brazo robótico de tres grados de libertad (3-GDL), fabricado mediante impresión 3D utilizando material PLA y controlado mediante protocolo serial. Además, describen que en el documento desarrollaron un modelo dinámico del sistema empleando el método de Euler-Lagrange, lo que permite describir el comportamiento dinámico de los eslabones en función de sus energías cinética y potencial.\r\nSin embargo, en el documento no presenta un fundamento teórico del desarrollo del modelo dinámico del sistema empleando el método de Euler-Lagrange de acuerdo con el área de conocimiento en la cual se inscribe el tema y  son suficientes para el análisis que presenta del diseño con el modelo dinámico no presentado, así como no están claros los parámetros del diseño con base en la teórica. \r\nFinalmente, en las referencias bibliográficas no se presentan apropiadamente y no se citan correctamente, así como muy pocas referencias agregadas.', NULL, 'Validada', NULL, '2026-01-10 23:33:12', '2026-02-01 02:05:19'),
(54, 26, 344, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', '- El presente articulo presenta un desarrollo de prototipo tecnológico con validación cualitativa, pero no delimita explícitamente si se trata de un estudio exploratorio, de diseño, de validación tecnológica o de investigación aplicada.\r\n- La validación del prototipo se basa en la retroalimentación de únicamente dos profesionales en psicología, sería conveniente agregar más y evitar generalizaciones sobre la efectividad terapéutica del sistema.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-13 02:57:23', '2026-02-01 02:05:19'),
(55, 26, 370, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"no\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'El tema es relevante, sin embargo considero que es posible mejorarlo.\r\n1) Falto un análisis de los enfoques terapéuticos mas relevantes y concluir esta sección con una tabla comparativa, esto daría sustento y permitirá justificar porque se enfocaron o seleccionaron la terapia Gestlt.\r\n2) Falto justificar porque experiencias inmersas, realizar análisis y tabla comparativa. \r\n3) Sustentar porque entornos naturales virtuales versus entornos naturales.\r\n4) En la sección de Validación terapéutica falto explicar las pruebas y mostrar resultados de la estimulación visual. Respecto a los ejercicios de respiración guiada bien pero si muestran l proceso de las mejoras que fueron realizando y los resultados que fueron obteniendo quedaría mejor sustentado. \r\n5) Falta describir la parte técnica del desarrollo del prototipo, un diagrama a bloques o esquema general del sistema ayudaría a describir el proceso de desarrollo.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-13 02:57:23', '2026-02-01 02:05:19'),
(62, 10, 347, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'En el resumen incluir los resultados más importantes y el aporte principal del trabajo. Mejorar la redacción del abstract en inglés. En el primer párrafo de la introducción se habla de estudios recientes pero las referencias no lo son. Dejar claro la diferencia de este trabajo con trabajos anteriores. Poner referencias concretas en donde se afirma que los resultados fueron muy similares a lo reportado en la literatura. La referencia 1 aparece sin título. La referencia 6 incluye información que no se entiende.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-14 20:02:33', '2026-02-01 02:05:19'),
(63, 10, 375, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"no\"}', 'Revisar escritura para dar uniformidad al texto. parafos de casi una cuartilla. Por favir revisar el formato de referencias', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-14 20:02:33', '2026-02-01 02:05:19'),
(66, 30, 347, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"no\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'El artículo constituye un aporte introductorio y pertinente al área de la mecatrónica aplicada a la seguridad y la educación. En el resumen se menciona que el sistema tiene “dos eslabones con una base rotativa, configurado para ofrecer tres grados de libertad”, pero más adelante se habla de “cinco grados de libertad mediante servomotores”. en las matrices de Denavit-Hartenberg aparecen términos como “l1 = 11” y “l2 = 12”, que parecen errores tipográficos (probablemente deberían ser valores o símbolos consistentes con L1, L2, L3).', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-14 20:05:31', '2026-02-01 02:05:19'),
(67, 30, 348, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"si\", \"pregunta_4\": \"no\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'No se encuentra una relación adecuada de lo que se pretende entre el alcance, como lo es el modelado, la dinámica y el control, respecto a lo que se documenta. Esta situación se reconoce abiertamente en las propias conclusiones.\r\nLa sección de resultados no aporta validación del desempeño del manipulador ni del caso de uso propuesto. \r\nEs necesario cuidar la parte de las referencias ya que se observan inconsistencias y demerita la confiabilidad del manuscrito.', 'No Publicable', 'El artículo tiene una intención técnica pertinente, pero no es suficiente al pretender un aporte de modelado y validación que no se observa. Aunado a ello, la evidencia presentada no permite sostener, con trazabilidad y reproducibilidad, que el sistema funcione y cumpla el caso de uso planteado.', NULL, 'Validada', NULL, '2026-01-14 20:05:31', '2026-02-01 02:05:19'),
(68, 39, 366, NULL, NULL, 'Pendiente', NULL, NULL, 'Pendiente', NULL, '2026-01-14 20:06:56', NULL),
(69, 39, 369, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'El trabajo desarrollado acerca del estudio de la clasificación de distintos tipos de agarre de la mano a partir de señales EEG obtenidas durante tareas de imaginación motora podría ser considerado para publicar y la contribución podría ser la metodología para obtener al desarrollo de sistemas de control más intuitivas para prótesis de mano, sin embargo, se recomienda revisar los lineamientos internos de la revista o congreso para adecuar o ajustar el formato verificando que cumpla con estructura y pueda publicarse. Esto es, debido a que en el articulo, se omiten secciones importantes tal como agregar una sección de RESULTADOS, y observándose que en la sección de DESARROLLO se reportaron el análisis y resultados. Se sugiere ordenar por secciones, por ejemplo, en la sección de MÉTODOS Y MATERIALES contiene información como definiciones, fundamentos, formulas, etc, y este podría ser la sección que se denomine \"FUNDAMENTOS TEÓRICOS\". \r\nFinalmente, en bibliográficas se sugiere agregar otros tres trabajos de su búsqueda de base datos, artículos de temas similares a este trabajo y relevantes de los últimos 5 años.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-14 20:06:56', '2026-02-01 02:05:19'),
(70, 41, 348, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"no\"}', 'El trabajo se observa con solidez ya que parte de una necesidad real con solución automatizada. Se cuenta con una metodología con capacidades de reproducir.\r\nEs necesario especificar cómo se determinó adecuadamente la matriz de confusión. También es necesario evitar contradicciones respecto al etiquetado robusto menciona como trabajo futuro ya que esto da la pauta de que el trabajo no se encuentra bien validado, lo que lleva a la necesidad de qué es lo que realmente se validó en el artículo y qué es lo que se validará posteriormente.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-14 20:08:30', '2026-02-01 02:05:19'),
(71, 41, 370, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'El trabajo es interesante y esta presentado de forma entendible.\r\nObservación:\r\nMostrar imágenes originales y procesadas para dar mayor relevancia al proceso realizado.   ', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-14 20:08:30', '2026-02-01 02:05:19'),
(72, 33, 328, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"no\", \"pregunta_3\": \"no\", \"pregunta_4\": \"no\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'Observaciones\r\nEl manuscrito mantiene coherencia lógica entre sus secciones. El enfoque in silico está claramente delimitado y los autores evitan conclusiones extrapoladas. \r\nComo observaciones, la introducción carece de sustento en otros estudios de vacunas  proteicas con propiedades antimicrobianas; o por qué se plantea que la vacuna Abdala pueda tener propiedades PAMs, cuál es el antecedente o motivación. La información sobre la proteína en estudio es miníma: ¿cual es su longiud, sus dominios, etc?La metodología esta pobremente descrita, en kos resultados hay datos que no se describen en la metodología como el índice Boman. En la discusión no se compara con estudios similares sobre péptidos derivados de proteínas virales o vacunales, para ampliar el impacto conceptual del estudio.\r\n\r\n', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-15 17:36:10', '2026-02-01 02:05:19'),
(73, 33, 383, '{\"pregunta_1\": \"si\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"si\", \"pregunta_6\": \"si\"}', 'La propuesta del artículo es buena y presenta una buena oportunidad de temas a investigar. Se le solicita a los autores discutir y ampliar el panorama sobre los posibles efectos reportados de las 3 regiones de la vacuna que presentaron similitud con PAMS.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-15 17:36:10', '2026-02-01 02:05:19'),
(76, 15, 386, NULL, NULL, 'Pendiente', NULL, NULL, 'Pendiente', NULL, '2026-01-18 02:28:33', NULL),
(77, 15, 375, '{\"pregunta_1\": \"no\", \"pregunta_2\": \"si\", \"pregunta_3\": \"si\", \"pregunta_4\": \"si\", \"pregunta_5\": \"no\", \"pregunta_6\": \"no\"}', 'Es ub muy buen trabajo, con productos para la enseñanza-aprendizajeuctos aplicables de impacto directo a la sociedad. Sería comeniente el espandir la  discusión relacionada con relacion al uso de XP. Tambien fundamentar las desiciones que se tomaron el desarrollo de su programa.', 'Favorable con Correcciones', NULL, NULL, 'Validada', NULL, '2026-01-18 02:28:33', '2026-02-01 02:05:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extensos`
--

CREATE TABLE `extensos` (
  `id` int NOT NULL,
  `resumen_id` int NOT NULL,
  `estatus_extenso` enum('No Enviado','Pendiente de Filtro','Rechazado por Formato','Pendiente de Asignación','En Revisión','Aceptado con Correcciones','Aceptado Final','Rechazado','Conflicto') DEFAULT 'No Enviado',
  `comentarios_formato` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extenso_versiones`
--

CREATE TABLE `extenso_versiones` (
  `id` int NOT NULL,
  `extenso_id` int NOT NULL,
  `intento` int NOT NULL,
  `archivo_ruta` varchar(255) NOT NULL,
  `fecha_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `resumen_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `comprobante_ruta` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estatus_pago` enum('Pendiente','Aprobado','Rechazado') COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `comentarios_rechazo` text COLLATE utf8mb4_general_ci,
  `revisor_pago_id` int DEFAULT NULL,
  `fecha_carga` timestamp NULL DEFAULT NULL,
  `fecha_revision_pago` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumenes`
--

CREATE TABLE `resumenes` (
  `id` int NOT NULL,
  `autor_id` int NOT NULL,
  `autor_principal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `coautores` text COLLATE utf8mb4_general_ci,
  `adscripcion1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `adscripcion2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `resumen_texto` text COLLATE utf8mb4_general_ci NOT NULL,
  `area_id` int NOT NULL,
  `estatus` enum('Borrador','Pendiente de Asignacion','En Revision','Aceptado','Rechazado') COLLATE utf8mb4_general_ci DEFAULT 'Borrador',
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `fecha_ultima_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `intento_envio` int DEFAULT '1',
  `palabras_clave` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `revisiones`
--

CREATE TABLE `revisiones` (
  `id` int NOT NULL,
  `resumen_id` int NOT NULL,
  `revisor_id` int NOT NULL,
  `veredicto` enum('Pendiente','Aceptado','Rechazado') COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `comentarios` text COLLATE utf8mb4_general_ci,
  `fecha_asignacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `revisores_extensos_perfil`
--

CREATE TABLE `revisores_extensos_perfil` (
  `usuario_id` int NOT NULL,
  `grado_academico` varchar(100) DEFAULT NULL,
  `afiliacion_institucional` varchar(255) DEFAULT NULL,
  `cargo_actual` varchar(255) DEFAULT NULL,
  `area_especialidad` text,
  `orcid` varchar(255) DEFAULT NULL,
  `google_scholar_id` varchar(255) DEFAULT NULL,
  `comprobante_sni_ruta` varchar(255) DEFAULT NULL,
  `foto_ruta` varchar(255) DEFAULT NULL,
  `acepta_terminos` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre_rol` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre_rol`) VALUES
(1, 'Administrador'),
(5, 'Asistente'),
(7, 'Asistente con Cartel'),
(4, 'Autor'),
(2, 'Coordinador'),
(3, 'Coordinador de Area'),
(8, 'Revisor de Extensos'),
(6, 'Revisor de Pagos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre_completo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `institucion_procedencia` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  `area_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_roles`
--

CREATE TABLE `usuario_roles` (
  `usuario_id` int NOT NULL,
  `rol_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areas_tematicas`
--
ALTER TABLE `areas_tematicas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_area` (`nombre_area`);

--
-- Indices de la tabla `evaluaciones_extensos`
--
ALTER TABLE `evaluaciones_extensos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extenso_version_id` (`extenso_version_id`),
  ADD KEY `revisor_id` (`revisor_id`);

--
-- Indices de la tabla `extensos`
--
ALTER TABLE `extensos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resumen_id` (`resumen_id`);

--
-- Indices de la tabla `extenso_versiones`
--
ALTER TABLE `extenso_versiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extenso_id` (`extenso_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `resumen_id` (`resumen_id`),
  ADD KEY `revisor_pago_id` (`revisor_pago_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indices de la tabla `resumenes`
--
ALTER TABLE `resumenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor_id` (`autor_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `revisiones`
--
ALTER TABLE `revisiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resumen_id` (`resumen_id`,`revisor_id`),
  ADD KEY `revisor_id` (`revisor_id`);

--
-- Indices de la tabla `revisores_extensos_perfil`
--
ALTER TABLE `revisores_extensos_perfil`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD PRIMARY KEY (`usuario_id`,`rol_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areas_tematicas`
--
ALTER TABLE `areas_tematicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `evaluaciones_extensos`
--
ALTER TABLE `evaluaciones_extensos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT de la tabla `extensos`
--
ALTER TABLE `extensos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `extenso_versiones`
--
ALTER TABLE `extenso_versiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resumenes`
--
ALTER TABLE `resumenes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `revisiones`
--
ALTER TABLE `revisiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `evaluaciones_extensos`
--
ALTER TABLE `evaluaciones_extensos`
  ADD CONSTRAINT `evaluaciones_extensos_ibfk_1` FOREIGN KEY (`extenso_version_id`) REFERENCES `extenso_versiones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluaciones_extensos_ibfk_2` FOREIGN KEY (`revisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `extensos`
--
ALTER TABLE `extensos`
  ADD CONSTRAINT `extensos_ibfk_1` FOREIGN KEY (`resumen_id`) REFERENCES `resumenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `extenso_versiones`
--
ALTER TABLE `extenso_versiones`
  ADD CONSTRAINT `extenso_versiones_ibfk_1` FOREIGN KEY (`extenso_id`) REFERENCES `extensos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`resumen_id`) REFERENCES `resumenes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`revisor_pago_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `resumenes`
--
ALTER TABLE `resumenes`
  ADD CONSTRAINT `resumenes_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resumenes_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas_tematicas` (`id`);

--
-- Filtros para la tabla `revisiones`
--
ALTER TABLE `revisiones`
  ADD CONSTRAINT `revisiones_ibfk_1` FOREIGN KEY (`resumen_id`) REFERENCES `resumenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revisiones_ibfk_2` FOREIGN KEY (`revisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `revisores_extensos_perfil`
--
ALTER TABLE `revisores_extensos_perfil`
  ADD CONSTRAINT `revisores_extensos_perfil_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas_tematicas` (`id`);

--
-- Filtros para la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD CONSTRAINT `usuario_roles_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_roles_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
