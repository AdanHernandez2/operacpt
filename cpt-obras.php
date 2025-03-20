<?php
/*
Plugin Name: CPT Obras
Description: Gestión de obras de ópera con campos personalizados + Elementor
Version: 1.2
Author: Progresi
*/

// 1. Cargar Composer Autoloader para Carbon Fields
require_once __DIR__ . '/vendor/autoload.php';

use Carbon_Fields\Container;
use Carbon_Fields\Field;

// 2. Registrar CPT y Campos
add_action('init', 'registrar_cpt_obra');
add_action('carbon_fields_register_fields', 'registrar_campos_obra');
add_action('after_setup_theme', 'cargar_carbon_fields');

function cargar_carbon_fields() {
    \Carbon_Fields\Carbon_Fields::boot();
}

function registrar_cpt_obra() {
    register_post_type('obra', [
        'labels' => [
            'name' => __('Obras'),
            'singular_name' => __('Obra'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'obras'],
        'supports' => ['title', 'thumbnail', 'elementor'],
        'menu_icon' => 'dashicons-format-audio',
        'show_in_rest' => true,
    ]);
}

function registrar_campos_obra() {
    Container::make('post_meta', 'Detalles de la Obra')
        ->where('post_type', '=', 'obra')
        ->add_fields([
            Field::make('text', 'genero', 'Género')->set_required(true),
            Field::make('text', 'autor', 'Autor')->set_required(true),
            Field::make('date', 'fecha_estreno', 'Fecha de Estreno')->set_storage_format('Y-m-d'),
            Field::make('date', 'fecha_finalizacion', 'Fecha de Finalización')->set_storage_format('Y-m-d'),
            Field::make('complex', 'ficha_artistica', 'Ficha Artística')
                ->add_fields([
                    Field::make('text', 'rol', 'Rol'),
                    Field::make('text', 'nombre', 'Nombre'),
                ])
                ->set_layout('tabbed-horizontal'),
            Field::make('media_gallery', 'galeria_fotos', 'Galería de Fotos')->set_type('image'),
            Field::make('complex', 'videos', 'Videos')
                ->add_fields([
                    Field::make('text', 'titulo_video', 'Título'),
                    Field::make('oembed', 'url_video', 'URL del Video'),
                ]),
            // Campos modificados para protección PDF
            Field::make('complex', 'dossier_archivos', 'Archivos del Dossier')
                ->add_fields([
                    Field::make('file', 'archivo', 'Archivo PDF')
                        ->set_type('application/pdf')
                        ->set_required(true)
                ])
                ->set_layout('tabbed-horizontal')
                ->set_header_template('
                    <% if (archivo) { %>
                        Archivo: <%= archivo.split("/").pop() %>
                    <% } %>
                '),
            Field::make('text', 'dossier_clave', 'Contraseña de acceso')
                ->set_attribute('type', 'password')
                ->help_text('Clave única para todos los archivos del dossier'),
            Field::make('rich_text', 'sinopsis', 'Sinopsis')->set_required(true),
            Field::make('rich_text', 'presentacion', 'Presentación'),
        ]);
}

// 3. Sistema de protección PDF
add_action('template_redirect', 'manejar_descarga_protegida');
add_action('wp_ajax_verificar_clave_dossier', 'verificar_clave_dossier');
add_action('wp_ajax_nopriv_verificar_clave_dossier', 'verificar_clave_dossier');

function manejar_descarga_protegida() {
    if (isset($_GET['descargar_dossier']) && isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
        $clave = sanitize_text_field($_GET['clave'] ?? '');

        if (validar_clave_dossier($post_id, $clave)) {
            $file_id = carbon_get_post_meta($post_id, 'dossier');
            $file_path = get_attached_file($file_id);

            if ($file_path && file_exists($file_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit;
            }
        }
        
        wp_die('Acceso no autorizado', 'Error de autenticación', ['response' => 403]);
    }
}

// En la función de validación actualiza:
function validar_clave_dossier($post_id, $clave) {
  $clave_almacenada = carbon_get_post_meta($post_id, 'dossier_clave');
  
  // Si no hay clave configurada, permitir acceso
  if (empty($clave_almacenada)) {
      return true;
  }
  
  // Si hay clave, verificar coincidencia
  return $clave === $clave_almacenada;
}

function verificar_clave_dossier() {
    check_ajax_referer('seguridad_dossier', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    $clave = sanitize_text_field($_POST['clave']);
    
    if (validar_clave_dossier($post_id, $clave)) {
        wp_send_json_success([
            'url' => add_query_arg([
                'descargar_dossier' => 1,
                'post_id' => $post_id,
                'clave' => $clave
            ], home_url('/'))
        ]);
    }
    
    wp_send_json_error('Contraseña incorrecta');
}

// 4. Compatibilidad con Elementor
add_action('elementor/theme/register_locations', 'registrar_elementor_locations');
add_filter('elementor_pro/utils/post_type_supports_elementor', 'habilitar_elementor_para_obra', 10, 2);

function registrar_elementor_locations($elementor_theme_manager) {
    $elementor_theme_manager->register_all_core_location();
}

function habilitar_elementor_para_obra($supports, $post_type) {
    if ('obra' === $post_type) {
        $supports = true;
    }
    return $supports;
}

// 5. Shortcodes actualizados
add_shortcode('obra_meta', 'shortcode_obra_meta');
add_shortcode('obra_ficha', 'shortcode_obra_ficha');
add_shortcode('obra_descarga_pdf', 'shortcode_descarga_pdf');

function shortcode_obra_meta($atts) {
    $atts = shortcode_atts(['field' => ''], $atts);
    return carbon_get_post_meta(get_the_ID(), $atts['field']);
}

function shortcode_obra_ficha() {
    $ficha = carbon_get_post_meta(get_the_ID(), 'ficha_artistica');
    if (empty($ficha)) return '';

    $output = '<div class="ficha-artistica elementor-grid">';
    foreach ($ficha as $miembro) {
        $output .= sprintf(
            '<div class="miembro"><h4>%s</h4><p>%s</p></div>',
            esc_html($miembro['rol']),
            esc_html($miembro['nombre'])
        );
    }
    return $output . '</div>';
}

// Modifica el shortcode para manejar ambos casos:
  function shortcode_descarga_pdf() {
    $post_id = get_the_ID();
    $tiene_clave = !empty(carbon_get_post_meta($post_id, 'dossier_clave'));
    $file_id = carbon_get_post_meta($post_id, 'dossier');
    
    if (!$file_id) return ''; // No hay PDF subido

    ob_start();
    ?>
    <div class="descarga-pdf-protected">
        <?php if ($tiene_clave) : ?>
            <form class="form-descarga-pdf" data-post-id="<?php echo $post_id; ?>">
                <div class="form-group">
                    <input type="password" name="clave" placeholder="Ingrese la clave del documento" required>
                    <button type="submit" class="elementor-button">Desbloquear PDF</button>
                </div>
                <div class="descarga-feedback"></div>
            </form>
        <?php else : ?>
            <a href="<?php echo wp_get_attachment_url($file_id); ?>" 
               class="descarga-directa elementor-button" 
               download>
                Descargar Dossier
            </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Filtrar las plantillas para el CPT 'obra'
add_filter('template_include', 'custom_obra_templates', 99);

function custom_obra_templates($template) {
    // Template para un post individual (single-obra.php)
    if (is_singular('obra') && !defined('ELEMENTOR_PRO_VERSION')) {
        $single_template = plugin_dir_path(__FILE__) . 'templates/single-obra.php';
        if (file_exists($single_template)) {
            return $single_template;
        }
    }

    // Template para el archivo (archive-obra.php)
    if (is_post_type_archive('obra') && !defined('ELEMENTOR_PRO_VERSION')) {
        $archive_template = plugin_dir_path(__FILE__) . 'templates/archive-obra.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }

    return $template; // Si no se cumple ninguna condición, usa la plantilla predeterminada
}

add_action('wp_enqueue_scripts', 'obras_scripts');
function obras_scripts() {
    // CSS
    wp_enqueue_style(
        'obras-css', 
        plugins_url('assets/css/obras.css', __FILE__),
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/obras.css')
    );

    // JS
    wp_enqueue_script(
        'obras-js',
        plugins_url('assets/js/obras.js', __FILE__),
        ['jquery'],
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/obras.js'),
        true
    );

    // Localización
    wp_localize_script('obras-js', 'obrasData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('seguridad_dossier')
    ]);
}