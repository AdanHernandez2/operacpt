<?php
/**
 * Archive Template para Obras - Versión Corregida
 */

get_header();

// 1. Configurar la consulta de obras
$args = [
    'post_type' => 'obra', // Asegúrate de que el CPT se llama 'obra'
    'posts_per_page' => -1, // Mostrar todas las obras
    'meta_key' => 'fecha_estreno', // Campo personalizado para la fecha de estreno
    'orderby' => 'meta_value', // Ordenar por fecha de estreno
    'order' => 'DESC', // De más reciente a más antiguo
];

$query = new WP_Query($args);

// 2. Verificar si hay obras
if ($query->have_posts()) :
    // Agrupar obras por rango de años
    $obras_agrupadas = [];
    
    while ($query->have_posts()) : $query->the_post();
        // Obtener la fecha de estreno
        $estreno = carbon_get_post_meta(get_the_ID(), 'fecha_estreno');
        
        if ($estreno) {
            $ano = date('Y', strtotime($estreno)); // Extraer el año
            $rango = ($ano + 1) . ' - ' . $ano; // Crear el rango (ej: 2025-2024)
            
            if (!isset($obras_agrupadas[$rango])) {
                $obras_agrupadas[$rango] = []; // Inicializar el array para el rango
            }
            
            $obras_agrupadas[$rango][] = $post; // Agregar la obra al rango correspondiente
        }
    endwhile;
    
    // Ordenar los rangos de más reciente a más antiguo
    krsort($obras_agrupadas);
    
    // 3. Mostrar el contenido
    ?>
    <main class="archive-obras-container">
        <header class="archive-header">
            <h1 class="archive-title">
                <?php post_type_archive_title('Nuestro Repertorio: '); ?>
            </h1>
        </header>

        <div class="view-content" bis_skin_checked="1">
            <?php foreach ($obras_agrupadas as $rango => $obras) : ?>
                <div class="wrapfrom" id="tt<?php echo esc_attr(str_replace(' ', '', $rango)); ?>">
                    <h3 class=""><?php echo esc_html($rango); ?></h3>
                    
                    <div class="wrapfrom_in" bis_skin_checked="1">
                        <?php foreach ($obras as $index => $obra) : setup_postdata($obra); ?>
                            <div class="views-row obrabox">
                                <div class="views-field views-field-field-cartel" bis_skin_checked="1">
                                    <div class="field-content" bis_skin_checked="1">
                                        <?php if (has_post_thumbnail($obra->ID)) : ?>
                                            <?php echo get_the_post_thumbnail($obra->ID, [60, 80], [
                                                'style' => 'width: 60px; height: 80px; object-fit: cover;'
                                            ]); ?>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/images/placeholder.png'); ?>" 
                                                 width="60" height="80" alt="Placeholder">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="views-field views-field-field-genero" bis_skin_checked="1">
                                    <div class="field-content" bis_skin_checked="1">
                                        <span data-idterm="<?php echo esc_attr($obra->ID); ?>">
                                            <?php echo esc_html(carbon_get_post_meta($obra->ID, 'genero')); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="views-field views-field-title" bis_skin_checked="1">
                                    <span class="field-content">
                                        <a href="<?php echo esc_url(get_permalink($obra->ID)); ?>">
                                            <?php echo esc_html(get_the_title($obra->ID)); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <div class="views-field views-field-field-autor" bis_skin_checked="1">
                                    <div class="field-content" bis_skin_checked="1">
                                        <?php echo esc_html(carbon_get_post_meta($obra->ID, 'autor')); ?>
                                    </div>
                                </div>
                                
                                <div class="views-field views-field-view-node" bis_skin_checked="1">
                                    <span class="field-content">
                                        <a href="<?php echo esc_url(get_permalink($obra->ID)); ?>">ver</a>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php
else :
    // Si no hay obras, mostrar un mensaje
    ?>
    <main class="archive-obras-container">
        <header class="archive-header">
            <h1 class="archive-title">
                <?php post_type_archive_title('Nuestro Repertorio: '); ?>
            </h1>
        </header>

        <div class="view-content" bis_skin_checked="1">
            <p>No se encontraron obras en nuestro catálogo.</p>
        </div>
    </main>
    <?php
endif;

wp_reset_postdata(); // Restablecer la consulta principal
get_footer();