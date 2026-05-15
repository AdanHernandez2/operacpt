# CPT Obras y Eventos

Plugin de WordPress especializado en la gestión de obras teatrales, óperas y eventos culturales. Permite la creación y administración de un Custom Post Type (CPT) dedicado, taxonomías personalizadas.

---

## Características Principales

* **Custom Post Type (`obra`):** Registro de obras con soporte nativo para títulos, imágenes destacadas y edición avanzada.
* **Gestión de Metadatos:** Campos personalizados diseñados para cubrir las necesidades de producciones culturales:
    * Género y Autor.
    * Fechas de presentación.
    * Ficha artística y Sinopsis detallada.
    * Galerías de imágenes y vídeos.
* **Protección de Documentación:** Sistema integrado para la restricción de acceso a archivos PDF mediante contraseñas.
* **Shortcodes Dinámicos:** Herramientas para desplegar metadatos y galerías en cualquier parte del sitio de forma sencilla.
* **Optimización de Interfaz:** Carga selectiva de scripts (JS) y estilos (CSS) para garantizar una experiencia de usuario fluida sin afectar el rendimiento global.

---

## Taxonomías Incluidas

El plugin registra taxonomías específicas para clasificar el contenido según el tipo de evento, permitiendo filtrar fácilmente entre obras de teatro, funciones de ópera u otros eventos culturales.

---

## Instalación

1.  **Obtener el plugin:**
    Clona el repositorio directamente en tu carpeta `wp-content/plugins/` o descarga el archivo comprimido.

    ```bash
    git clone git@github.com:AdanHernandez2/operacpt.git
    ```

2.  **Activar:**
    Ve al panel de administración de WordPress > **Plugins** y activa "CPT Obras".

3.  **Configurar Enlaces Permanentes:**
    Se recomienda ir a **Ajustes > Enlaces permanentes** y hacer clic en "Guardar cambios" para refrescar las reglas de reescritura del nuevo CPT.
