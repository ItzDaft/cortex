# Cortex - Sistema de Gestión de Congresos y Conferencias (CCTI 2025)

**Cortex** es una plataforma web desarrollada en **PHP nativo** bajo una arquitectura **MVC** (Modelo-Vista-Controlador), diseñada específicamente para la gestión integral de congresos, simposios y eventos académicos (como el CCTI 2025). Permite administrar todo el ciclo de vida de las propuestas científicas, desde el envío y revisión por pares hasta la validación de pagos, gestión de artículos completos (*extensos*) y publicación de memorias.

---

## 🚀 Características Principales

*   **Gestión Multi-Rol (RBAC)**: Roles diferenciados para *Administradores*, *Coordinadores de Área*, *Revisores de Resúmenes*, *Revisores de Extensos*, *Revisores de Pagos* y *Autores / Participantes*.
*   **Ciclo de Resúmenes**: Envío de propuestas, validación de áreas temáticas y dictamen (Aceptado/Rechazado) con generación automática de órdenes de pago y registros de artículos completos.
*   **Gestión de Artículos Completos (Extensos)**: Carga de documentos, validación de formato por coordinadores, asignación de doble ciego a revisores, formularios de evaluación detallada, firma digital y carga de PDF firmado.
*   **Módulo Financiero y de Pagos**: Carga de comprobantes de transferencia/depósito por parte de los usuarios, validación con aprobación/rechazo y opciones de condonación con auditoría.
*   **Seguridad Robusta**: Protección contra ataques CSRF, control de acceso basado en sesiones y consultas PDO seguras contra inyecciones SQL.
*   **Notificaciones y Reportes**: Envío automatizado de correos transaccionales vía PHPMailer y generación de reportes y constancias en PDF con FPDF.

---

## 🛠️ Pila Tecnológica

*   **Backend**: PHP 8.x (POO, PDO)
*   **Base de Datos**: MySQL / MariaDB
*   **Dependencias (Composer)**:
    *   `vlucas/phpdotenv` (Manejo de variables de entorno `.env`)
    *   `phpmailer/phpmailer` (Correos transaccionales SMTP)
*   **Librerías Embebidas**: FPDF 1.8.6 (Generación de PDFs)

---

## 📦 Instalación y Configuración Local

1. **Clonar el repositorio y ubicarte en el proyecto**:
   ```bash
   cd cortex
   ```

2. **Instalar dependencias con Composer**:
   ```bash
   composer install
   ```

3. **Configurar el entorno**:
   Crea un archivo `.env` en la raíz del proyecto basado en tus credenciales locales:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=tu_contrasena
   DB_NAME=fasbited_ccti25
   MAIL_HOST=smtp.tuservidor.com
   MAIL_USER=tu_correo@dominio.com
   MAIL_PASS=tu_password
   ```

4. **Importar la Base de Datos**:
   Importa el archivo SQL (`fasbited_ccti25.sql`) ubicado en la raíz del proyecto en tu servidor MySQL/MariaDB.

5. **Iniciar el Servidor de Desarrollo**:
   ```bash
   php -S localhost:8000 -t public
   ```
   Abre `http://localhost:8000` en tu navegador.

---

## 📚 Documentación Adicional

Para más detalles técnicos, diagramas de casos de uso, diagrama entidad-relación (ER) y arquitectura detallada, consulta el archivo [DOCUMENTACION_TECNICA.md](./DOCUMENTACION_TECNICA.md).
