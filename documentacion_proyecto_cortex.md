# Documentación Técnica del Proyecto Cortex

## 1. Información General

*   **Nombre del Proyecto:** Cortex
*   **Propósito Inferido:** Sistema de gestión integral para congresos científicos o académicos. Permite el envío, revisión y administración de trabajos (resúmenes y artículos extensos), gestión de pagos y coordinación de revisores.
*   **Lenguajes:** PHP (Backend), HTML/JS/CSS (Frontend inferido por vistas).
*   **Frameworks/Librerías:**
    *   **Backend:** PHP nativo con estructura MVC personalizada.
    *   **Librerías:** `phpmailer/phpmailer` (correos), `vlucas/phpdotenv` (variables de entorno), `fpdf` (generación de PDFs).
*   **Arquitectura:** MVC (Modelo-Vista-Controlador) Monolítico.
    *   **Cliente-Servidor:** El backend sirve vistas HTML y APIs JSON.

## 2. Estructura y Módulos

### Árbol de Carpetas Principal
```
/
├── app/
│   ├── controllers/      # Lógica de negocio (Controladores)
│   ├── models/           # Acceso a datos y entidades
│   ├── views/            # Interfaz de usuario (HTML/PHP)
│   ├── lib/              # Librerías auxiliares (FPDF, etc.)
│   └── helpers/          # Funciones de ayuda
├── config/               # Configuración (BD, App)
├── public/               # Punto de entrada (index.php) y assets públicos
└── core/                 # Núcleo del framework (Router, Database)
```

### Módulos Importantes
1.  **Módulo de Usuarios (Autenticación/Roles):** Gestiona registro, login, recuperación de contraseñas y asignación de roles (Autor, Revisor, Coordinador, Administrador).
2.  **Módulo de Resúmenes:** Permite a los autores enviar resúmenes, y a los coordinadores gestionarlos. Incluye lógica de intentos de envío y validación.
3.  **Módulo de Evaluaciones (Revisiones):**
    *   **Resúmenes:** Asignación de revisores y dictamen (Aceptado/Rechazado).
    *   **Extensos:** Gestión de artículos completos, con flujos de revisión más complejos (rondas, consenso, conflicto).
4.  **Módulo de Pagos:** Control de pagos de inscripción asociados a los trabajos aceptados.
5.  **Módulo de Coordinación:** Dashboard para coordinadores de área y administradores para supervisar el flujo de trabajos.

### Relación entre Módulos
*   **Usuarios** es el módulo central; todos los demás dependen de `usuario_id`.
*   **Resúmenes** es el punto de entrada del contenido científico.
*   **Pagos** y **Extensos** dependen de que un **Resumen** haya sido aceptado.
*   **Evaluaciones** vincula **Usuarios** (Revisores) con **Resúmenes** o **Extensos**.

## 3. Funcionalidades Inferidas (Historias de Usuario)

### Gestión de Usuarios y Acceso
*   **HU-01:** Como **Usuario/Autor**, quiero registrarme e iniciar sesión para poder enviar mis trabajos. (`UsuarioController.php`, `Usuario.php`)
*   **HU-02:** Como **Usuario**, quiero recuperar mi contraseña mediante un token por correo para restablecer mi acceso. (`PasswordResetController.php`)
*   **HU-03:** Como **Administrador**, quiero asignar roles a los usuarios para delegar responsabilidades (Coordinador, Revisor). (`AdministradorController.php`, `Usuario.php`)

### Gestión de Resúmenes (Abstracts)
*   **HU-04:** Como **Autor**, quiero enviar un resumen (título, texto, autores) asignándolo a un área temática para su evaluación. (`ResumenController.php` -> `crear`)
*   **HU-05:** Como **Autor**, quiero ver el estado de mis resúmenes y si han sido aceptados o rechazados. (`ResumenController.php` -> `misResumenes`)
*   **HU-06:** Como **Autor**, quiero corregir y reenviar un resumen rechazado (segundo intento) para intentar ser aceptado nuevamente. (`ResumenController.php` -> `procesarReenvio`)

### Evaluación y Coordinación
*   **HU-07:** Como **Coordinador**, quiero asignar revisores a los resúmenes de mi área para iniciar el proceso de evaluación. (`CoordinadorController.php`)
*   **HU-08:** Como **Revisor**, quiero ver los trabajos asignados y emitir un veredicto (Aceptado/Rechazado) con comentarios. (`RevisorController.php`)
*   **HU-09:** Como **Revisor de Extensos**, quiero evaluar artículos completos, llenar rúbricas y, si es favorable, firmar el dictamen digitalmente. (`RevisorExtensosController.php`)

### Pagos e Inscripción
*   **HU-10:** Como **Autor**, quiero subir mi comprobante de pago para un trabajo aceptado para formalizar mi inscripción. (`PagoController.php`)
*   **HU-11:** Como **Revisor de Pagos/Admin**, quiero validar los comprobantes de pago y aprobarlos o rechazarlos con comentarios. (`RevisorPagosController.php`)

## 4. Modelo de Datos

### Base de Datos
*   **Motor:** MySQL / MariaDB (inferido por `PDO` y sintaxis SQL).
*   **Diagrama ER Principal:**

| Entidad | Descripción / Propósito |
| :--- | :--- |
| **usuarios** | Tabla central. Almacena credenciales, datos personales y área temática. |
| **roles** | Catálogo de roles (Autor, Revisor, Coordinador, Admin). |
| **usuario_roles** | Tabla pivote para relación N:M entre usuarios y roles. |
| **areas_tematicas** | Catálogo de áreas de conocimiento del congreso. |
| **resumenes** | Almacena la información de los trabajos enviados (título, texto, autores). Estado (`estatus`) maneja el flujo. |
| **revisiones** | Guarda las evaluaciones de los resúmenes por parte de los revisores. |
| **pagos** | Registro de comprobantes de pago vinculados a un resumen y usuario. |
| **password_resets** | Tokens temporales para recuperación de contraseñas. |

*(Nota: El código sugiere tablas adicionales para el manejo de artículos extensos como `extensos`, `extenso_versiones`, `evaluaciones_extensos`, `revisores_extensos_perfil` que extienden este modelo base).*

## 5. Metodología Scrum (Propuesta)

Basado en la modularidad del código, el desarrollo pudo organizarse en los siguientes Sprints:

### Product Backlog (Inferido)
Lista priorizada de todas las historias de usuario mencionadas anteriormente.

### Organización de Sprints

#### **Sprint 1: Núcleo y Gestión de Usuarios**
*   **Objetivo:** Permitir el registro y gestión básica de usuarios.
*   **Incremento:** Sistema de login, registro, recuperación de password y gestión de roles.
*   **Funcionalidades:** HU-01, HU-02, HU-03.

#### **Sprint 2: Envío de Resúmenes**
*   **Objetivo:** Habilitar la recepción de trabajos académicos.
*   **Incremento:** Formularios de envío de resúmenes y dashboard de autor.
*   **Funcionalidades:** HU-04, HU-05.

#### **Sprint 3: Evaluación de Resúmenes**
*   **Objetivo:** Implementar el flujo de revisión por pares.
*   **Incremento:** Dashboard de coordinadores y revisores, asignación y emisión de veredictos.
*   **Funcionalidades:** HU-07, HU-08, HU-06 (Reenvío).

#### **Sprint 4: Pagos e Inscripciones**
*   **Objetivo:** Monetización y formalización.
*   **Incremento:** Módulo de carga y validación de comprobantes de pago.
*   **Funcionalidades:** HU-10, HU-11.

#### **Sprint 5: Artículos Extensos (Full Papers)**
*   **Objetivo:** Gestión avanzada de contenido científico final.
*   **Incremento:** Flujo complejo de evaluación de extensos, firmas digitales y perfiles de revisores expertos.
*   **Funcionalidades:** HU-09.

## 6. Pruebas y Despliegue

*   **Pruebas:** No se detectó un framework de pruebas automatizadas (e.g., PHPUnit) en el repositorio. Se infiere que las pruebas son **manuales** y exploratorias realizadas por el equipo de QA o desarrolladores.
*   **Despliegue:**
    *   **Método:** Transferencia de archivos vía **FTP (FileZilla)**.
    *   **Entorno:** Hosting compartido o servidor dedicado con soporte PHP/MySQL (LAMP/LEMP Stack).
    *   **Configuración:** Archivo `.env` (gestionado por `phpdotenv`) para credenciales sensibles y `config/database.php`.

## 7. Información Pendiente (Para redacción manual)

La siguiente información **NO** se encuentra en el repositorio y debe ser redactada o solicitada externamente para completar el trabajo académico:

1.  **Stakeholders reales:** ¿Quién solicitó el sistema? (Cliente, Universidad, Comité organizador).
2.  **Definition of Done (DoD):** Criterios exactos que usaron para marcar una tarea como "lista" (ej. ¿Validación con cliente? ¿Code review?).
3.  **Equipo Scrum:** Nombres y roles de las personas reales que desarrollaron el software.
4.  **Artefactos físicos:** Fotos de pizarrones, post-its o capturas de herramientas de gestión (Trello, Jira) si existieron.
