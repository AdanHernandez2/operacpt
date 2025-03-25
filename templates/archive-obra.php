<?php
/**
 * Archive Template para Obras agrupadas por rangos exactos
 */

get_header();
?>

<div class="container mt-5">
    <?php
    // Obtener todos los términos de temporada ordenados por nombre (orden natural)
    $temporadas = get_terms([
        'taxonomy' => 'temporada',
        'orderby' => 'name',
        'order' => 'DESC',
        'hide_empty' => true,
    ]);

    if (!empty($temporadas) && !is_wp_error($temporadas)) :
        foreach ($temporadas as $temporada) :
            // Verificar si el término es un rango válido (ej: 2023-2024)
            if (preg_match('/^\d{4}-\d{4}$/', $temporada->name)) :
                ?>
                <section class="temporada-section mb-5">
                    <h2 class="display-5 mb-4">Temporada <?php echo esc_html($temporada->name); ?></h2>
                    <hr class="mb-4">
                    
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-5 space-gap">
                        <?php
                        // Query para obras de ESTE rango específico
                        $obras = new WP_Query([
                            'post_type' => 'obra',
                            'posts_per_page' => -1,
                            'tax_query' => [[
                                'taxonomy' => 'temporada',
                                'field' => 'slug',
                                'terms' => $temporada->slug,
                            ]],
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ]);

                        if ($obras->have_posts()) :
                            while ($obras->have_posts()) : $obras->the_post();
                                // Incluir template de la card
                                $template_path = plugin_dir_path(__FILE__) . 'parts/card-obra.php';
                                if (file_exists($template_path)) {
                                    include($template_path);
                                } else {
                                    // Fallback básico
                                    ?>
                                    <div class="col">
                                        <div class="card h-100 shadow-sm">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <img src="<?php the_post_thumbnail_url('medium'); ?>" 
                                                     class="card-img-top" 
                                                     alt="<?php the_title_attribute(); ?>">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h3 class="card-title h5"><?php the_title(); ?></h3>
                                                <a href="<?php the_permalink(); ?>" 
                                                   class="btn btn-outline-primary">
                                                    Ver detalles
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            endwhile;
                            wp_reset_postdata();
                        else : ?>
                            <div class="col-12">
                                <div class="alert alert-info">No hay obras en esta temporada</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif;
        endforeach;
    else : ?>
        <div class="alert alert-warning">No se encontraron temporadas</div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>