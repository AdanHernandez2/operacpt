<?php

/**
 * Plugin Name: Gestión de Obras Teatrales
 * Description: Sistema completo para gestión de obras con taxonomías y plantillas optimizadas
 * Version: 3.3
 * Author: Progresi
 * Text Domain: progresi-obras
 * License: GPLv2 or later
 */

namespace Progresi\Obras;

defined('ABSPATH') || exit;

// Autoload Composer (si usas Carbon Fields via Composer)
require_once __DIR__ . '/vendor/autoload.php';

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Plugin
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'verificar_dependencias']);
        add_action('init', [$this, 'registrar_estructura']);
        add_action('carbon_fields_register_fields', [$this, 'registrar_campos']);
        add_filter('template_include', [$this, 'manejar_templates']);
        add_action('wp_enqueue_scripts', [$this, 'cargar_recursos']);
        add_action('template_redirect', [$this, 'manejar_descargas_protegidas']); // Añade este hook
        add_action('wp_ajax_verificar_clave_dossier', [$this, 'verificar_clave_dossier']);
        add_action('wp_ajax_nopriv_verificar_clave_dossier', [$this, 'verificar_clave_dossier']);
    }

    public function manejar_descargas_protegidas()
    {
        if (isset($_GET['descargar_dossier'], $_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
            $clave = sanitize_text_field($_GET['clave'] ?? '');

            // Verificar si la clave es válida
            $clave_valida = $this->validar_clave_dossier($post_id, $clave);
            $dossier_archivos = carbon_get_post_meta($post_id, 'dossier_archivos');

            if ($clave_valida && !empty($dossier_archivos)) {
                // Obtener el primer archivo del dossier (asumiendo que solo hay uno)
                $dossier = $dossier_archivos[0];
                $file_id = $dossier['archivo'];
                $file_path = get_attached_file($file_id);

                if ($file_path && file_exists($file_path)) {
                    // Forzar la descarga del archivo
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file_path));
                    readfile($file_path);
                    exit;
                } else {
                    wp_die(__('Archivo no encontrado', 'progresi-obras'), 404);
                }
            } else {
                wp_die(__('Acceso no autorizado', 'progresi-obras'), 403);
            }
        }
    }

    private function validar_clave_dossier($post_id, $clave)
    {
        $clave_almacenada = carbon_get_post_meta($post_id, 'dossier_clave');
        return empty($clave_almacenada) || $clave === $clave_almacenada;
    }

    public function verificar_clave_dossier()
    {
        check_ajax_referer('seguridad_dossier', 'nonce');

        $post_id = intval($_POST['post_id']);
        $clave = sanitize_text_field($_POST['clave']);

        if ($this->validar_clave_dossier($post_id, $clave)) {
            // Obtener el archivo del dossier
            $dossier_archivos = carbon_get_post_meta($post_id, 'dossier_archivos');

            if (!empty($dossier_archivos)) {
                // Obtener el primer archivo del dossier (asumiendo que solo hay uno)
                $dossier = $dossier_archivos[0];
                $file_id = $dossier['archivo'];
                $file_url = wp_get_attachment_url($file_id);

                if ($file_url) {
                    wp_send_json_success(['url' => $file_url]);
                } else {
                    wp_send_json_error(['message' => __('Archivo no encontrado', 'progresi-obras')]);
                }
            } else {
                wp_send_json_error(['message' => __('No se encontró el archivo adjunto', 'progresi-obras')]);
            }
        } else {
            wp_send_json_error(['message' => __('Clave incorrecta', 'progresi-obras')]);
        }
    }

    public function verificar_dependencias()
    {
        if (!class_exists('\Carbon_Fields\Carbon_Fields')) {
            add_action('admin_notices', function () {
                echo '<div class="error"><p>';
                printf(
                    __('Se requiere Carbon Fields para el plugin de obras. Instala con: %s', 'progresi-obras'),
                    '<code>composer require htmlburger/carbon-fields</code>'
                );
                echo '</p></div>';
            });
        }
    }

    public function registrar_estructura()
    {
        // Registrar CPT Obra
        register_post_type('obra', $this->config_cpt());

        // Registrar Taxonomía Temporadas
        register_taxonomy('temporada', 'obra', $this->config_taxonomia());
    }

    private function config_cpt()
    {
        return [
            'labels' => [
                'name' => __('Obras', 'progresi-obras'),
                'singular_name' => __('Obra', 'progresi-obras'),
                'menu_name' => __('Obras', 'progresi-obras'),
                'all_items' => __('Todas las Obras', 'progresi-obras'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'obras'],
            'supports' => ['title', 'thumbnail', 'excerpt', 'custom-fields'],
            'show_in_rest' => true,
            'taxonomies' => ['temporada'],
            'menu_icon' => 'dashicons-tickets-alt',
        ];
    }

    private function config_taxonomia()
    {
        return [
            'labels' => [
                'name' => __('Temporadas', 'progresi-obras'),
                'singular_name' => __('Temporada', 'progresi-obras'),
                'search_items' => __('Buscar Temporadas', 'progresi-obras'),
                'all_items' => __('Todas las Temporadas', 'progresi-obras'),
            ],
            'hierarchical' => false, // Importante: false para términos planos
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'temporadas'],
            'show_in_rest' => true,
        ];
    }

    public function registrar_campos()
    {
        Container::make('post_meta', __('Detalles de la Obra', 'progresi-obras'))
            ->where('post_type', '=', 'obra')
            ->add_tab(__('Información Básica', 'progresi-obras'), [
                Field::make('text', 'genero', __('Género', 'progresi-obras'))
                    ->set_required(true)
                    ->set_width(30),

                Field::make('text', 'autor', __('Autor', 'progresi-obras'))
                    ->set_required(true)
                    ->set_width(30),

                Field::make('date', 'fecha_estreno', __('Estreno', 'progresi-obras'))
                    ->set_storage_format('Y-m-d')
                    ->set_width(20),

                Field::make('date', 'fecha_finalizacion', __('Finalización', 'progresi-obras'))
                    ->set_storage_format('Y-m-d')
                    ->set_width(20),

                Field::make('rich_text', 'sinopsis', __('Sinopsis', 'progresi-obras'))
                    ->set_required(true),

                Field::make('rich_text', 'presentacion', __('Presentación', 'progresi-obras')),
            ])
            ->add_tab(__('Multimedia', 'progresi-obras'), [
                Field::make('media_gallery', 'galeria', __('Galería de Fotos', 'progresi-obras'))
                    ->set_type('image')
                    ->set_duplicates_allowed(false),

                Field::make('complex', 'videos', __('Videos', 'progresi-obras'))
                    ->add_fields([
                        Field::make('text', 'titulo', __('Título del Video'))
                            ->set_width(30),
                        Field::make('oembed', 'url', __('URL del Video'))
                            ->set_width(70),
                    ])
                    ->set_layout('tabbed-horizontal')
            ])
            ->add_tab(__('Documentación', 'progresi-obras'), [
                Field::make('complex', 'dossier_archivos', __('Archivos del Dossier', 'progresi-obras'))
                    ->add_fields([
                        Field::make('file', 'archivo', __('Archivo PDF', 'progresi-obras'))
                            ->set_type('application/pdf')
                            ->set_required(true),
                    ])
                    ->set_layout('tabbed-horizontal')
                    ->set_header_template('
                        <% if (archivo) { %>
                            Archivo: <%= archivo.split("/").pop() %>
                        <% } %>
                    '),

                Field::make('text', 'dossier_clave', __('Contraseña de acceso', 'progresi-obras'))
                    ->set_attribute('type', 'password')
                    ->help_text(__('Clave única para todos los archivos del dossier', 'progresi-obras')),
            ])
            ->add_tab(__('Ficha Artística', 'progresi-obras'), [
                Field::make('rich_text', 'ficha_artistica', __('Equipo Técnico', 'progresi-obras')),
            ]);
    }

    public function manejar_templates($template)
    {
        if (is_post_type_archive('obra')) {
            return plugin_dir_path(__FILE__) . 'templates/archive-obra.php';
        }

        if (is_singular('obra')) {
            return plugin_dir_path(__FILE__) . 'templates/single-obra.php';
        }

        return $template;
    }

    public function cargar_recursos()
    {
        if (is_singular('obra')) {
            wp_enqueue_style(
                'bootstrap5-css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                '5.3.3'
            );

            wp_enqueue_script(
                'bootstrap5-js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                [],
                '5.3.3',
                true
            );

            wp_enqueue_style(
                'progresi-obras',
                plugins_url('assets/css/obras.css', __FILE__),
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/obras.css')
            );

            wp_enqueue_script(
                'progresi-obras',
                plugins_url('assets/js/obras.js', __FILE__),
                ['jquery'],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/obras.js'),
                true
            );

            wp_enqueue_script(
                'masonry',
                'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js',
                ['jquery'],
                '4.2.2',
                true
            );

            // 🔥 Pasar variables PHP a JavaScript
            wp_localize_script(
                'progresi-obras',  // Mismo handle que usaste en wp_enqueue_script()
                'progresiObrasVars', // Objeto JS que contendrá las variables
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('seguridad_dossier'),
                ]
            );
        }

        // Carga recursos SOLO en el archive de 'obra'
        if (is_post_type_archive('obra')) {
            wp_enqueue_style(
                'bootstrap5-css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                '5.3.3'
            );

            wp_enqueue_script(
                'bootstrap5-js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                [],
                '5.3.3',
                true
            );
            wp_enqueue_style(
                'progresi-obras-archive',
                plugins_url('assets/css/archive.css', __FILE__),
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/archive.css')
            );
            wp_enqueue_script(
                'progresi-obras-archive',
                plugins_url('assets/js/app.js', __FILE__),
                ['jquery'],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/app.js'),
                true
            );
        }
    }
}


// Inicialización
add_action('plugins_loaded', function () {
    Carbon_Fields::boot();
    new Plugin();
});
