<?php
get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type'      => 'obra',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'paged'          => $paged,
    'order'          => 'DESC',
);

$query = new WP_Query( $args );
$obras_por_temporada = [];

if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        
        // Obtener los términos asociados en la taxonomía 'temporada'
        $terms = get_the_terms( get_the_ID(), 'temporada' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            // Suponiendo que solo se asocia un término por obra, usamos el primero
            $temporada = $terms[0]->name;
        } else {
            $temporada = __( 'Sin Temporada', 'progresi-obras' );
        }
        
        // Agrupar los posts por temporada
        $obras_por_temporada[ $temporada ][] = get_the_ID();
    }
    wp_reset_postdata();
}

if ( ! empty( $obras_por_temporada ) ) {
    // Ordenar las temporadas (las claves) de forma descendente
    krsort( $obras_por_temporada );
    
    foreach ( $obras_por_temporada as $temporada => $obras ) {
        echo '<h2>' . esc_html( $temporada ) . '</h2>';
        echo '<hr>';
        echo '<div class="obras-posts-grid">';
        foreach ( $obras as $post_id ) {
            setup_postdata( $post_id );
            // Definir la ruta de la plantilla de la card
            $template_path = plugin_dir_path( __FILE__ ) . 'parts/card-obra.php';
            if ( file_exists( $template_path ) ) {
                include( $template_path );
            } else {
                echo '<div class="error">Plantilla de card no encontrada</div>';
            }
        }
        wp_reset_postdata();
        echo '</div>';
    }
} else {
    echo '<p>' . __( 'No se encontraron obras.', 'progresi-obras' ) . '</p>';
}

get_footer();
