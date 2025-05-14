<?php

/**
 * Template para single de Obras Teatrales - Versión Mejorada
 */

get_header();

use Carbon_Fields\Field\Field;
use Carbon_Fields\Container\Container;

// Obtener campos personalizados
$genero             = carbon_get_post_meta(get_the_ID(), 'genero');
$autor              = carbon_get_post_meta(get_the_ID(), 'autor');
$fecha_estreno      = carbon_get_post_meta(get_the_ID(), 'fecha_estreno');
$fecha_finalizacion = carbon_get_post_meta(get_the_ID(), 'fecha_finalizacion');
$sinopsis           = carbon_get_post_meta(get_the_ID(), 'sinopsis');
$presentacion       = carbon_get_post_meta(get_the_ID(), 'presentacion');
$galeria            = carbon_get_post_meta(get_the_ID(), 'galeria');
$videos             = carbon_get_post_meta(get_the_ID(), 'videos');
$dossier_archivos   = carbon_get_post_meta(get_the_ID(), 'dossier_archivos');
$dossier_clave      = carbon_get_post_meta(get_the_ID(), 'dossier_clave');
$ficha_artistica    = carbon_get_post_meta(get_the_ID(), 'ficha_artistica');
?>

<!-- Page content -->
<div class="container mt-5">
    <div class="row">
        <!-- Contenido principal -->
        <div class="col-lg-8">
            <!-- Post content -->
            <article>
                <!-- Post header -->
                <header class="mb-4">
                    <!-- Post title -->
                    <h1 class="fw-bolder mb-1"><?php the_title(); ?></h1>

                </header>

                <!-- Imagen destacada -->
                <?php if (has_post_thumbnail()): ?>
                    <figure class="mb-4">
                        <?php the_post_thumbnail('large', ['class' => 'img-fluid rounded']); ?>
                    </figure>
                <?php endif; ?>

                <!-- Secciones con Accordion Bootstrap -->
                <div class="accordion mb-5" id="obraAccordion">

                    <!-- Presentación -->
                    <?php if ($presentacion): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPresentacion">
                                <button class="accordion-button " type="button" data-bs-toggle="collapse" data-bs-target="#collapsePresentacion" aria-expanded="true">
                                    Presentación
                                </button>
                            </h2>
                            <div id="collapsePresentacion" class="accordion-collapse collapse show" data-bs-parent="#obraAccordion">
                                <div class="accordion-body">
                                    <?php echo wpautop(wp_kses_post($presentacion)); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Sinopsis -->
                    <?php if ($sinopsis): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSinopsis">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSinopsis" aria-expanded="false">
                                    Sinopsis
                                </button>
                            </h2>
                            <div id="collapseSinopsis" class="accordion-collapse collapse" data-bs-parent="#obraAccordion">
                                <div class="accordion-body">
                                    <?php echo wpautop(wp_kses_post($sinopsis)); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>


                    <!-- Ficha Artística -->
                    <?php if (!empty($ficha_artistica)): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFicha">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFicha" aria-expanded="false">
                                    Ficha Artística
                                </button>
                            </h2>
                            <div id="collapseFicha" class="accordion-collapse collapse" data-bs-parent="#obraAccordion">
                                <div class="accordion-body">
                                    <?php echo wpautop(wp_kses_post($ficha_artistica)); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div> <!-- /accordion -->

                <!-- Dossier -->
                <?php if (!empty($dossier_archivos)): ?>
                    <section class="mb-5">
                        <h2 class="fw-bolder mb-3">Documentación / Dossier</h2>
                        <ul class="list-group">
                            <?php foreach ($dossier_archivos as $dossier):
                                $file_url = wp_get_attachment_url($dossier['archivo']);
                                $file_name = basename($file_url); ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo esc_html($file_name); ?>
                                    <?php if (!empty($dossier_clave)): ?>
                                        <!-- Formulario para pedir contraseña -->
                                        <form class="dossier-form" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                                            <input type="password" class="form-control form-control-sm" placeholder="Contraseña" required>
                                            <button type="submit" class="btn btn-sm btn-primary btn-archive-dossier">Descargar</button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Descarga directa si no hay contraseña -->
                                        <a href="<?php echo esc_url($file_url); ?>" class="btn btn-sm btn-primary" target="_blank" rel="noopener noreferrer">
                                            Descargar
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php if (!empty($galeria)): ?>
                    <section class="mb-5">
                        <h2 class="fw-bolder mb-3">Galería de Fotos</h2>
                        <div class="row g-3" data-masonry='{"percentPosition": true}'>
                            <?php foreach ($galeria as $image_id):
                                $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
                                <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                                    <img src="<?php echo esc_url($image_url); ?>" class="img-fluid rounded shadow-sm w-100" alt="Galería">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Videos -->
                <?php if (!empty($videos)): ?>
                    <section class="mb-5">
                        <h2 class="fw-bolder mb-3">Videos</h2>
                        <?php foreach ($videos as $video): ?>
                            <div class="mb-4">
                                <h5><?php echo esc_html($video['titulo']); ?></h5>
                                <div class="ratio ratio-16x9">
                                    <?php echo wp_oembed_get($video['url']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>

            </article>
        </div> <!-- /col-lg-8 -->

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ticket Widget -->
            <div class="ticket-card">
                <div class="ticket-header">Detalles de la Obra</div>
                <div class="ticket-body">
                    <p><strong>Género:</strong> <?php echo esc_html($genero); ?></p>
                    <p><strong>Autor:</strong> <?php echo esc_html($autor); ?></p>
                    <?php if (!empty($fecha_estreno)): ?>
                        <p><strong>Estreno:</strong> <?php echo esc_html($fecha_estreno); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($fecha_finalizacion)): ?>
                        <p><strong>Finalización:</strong> <?php echo esc_html($fecha_finalizacion); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div> <!-- /col-lg-4 -->
    </div> <!-- /row -->
</div> <!-- /container -->


<?php get_footer(); ?>