<?php get_header(); ?>

<article class="obra-detalle">
    <header class="obra-header">
        <?php the_post_thumbnail('full'); ?>
        <h1><?php the_title(); ?></h1>
        <div class="meta-obra">
            <p><strong>Género:</strong> <?php echo carbon_get_the_post_meta('genero'); ?></p>
            <p><strong>Autor:</strong> <?php echo carbon_get_the_post_meta('autor'); ?></p>
            <p><strong>Estreno:</strong> <?php echo date_i18n('j F Y', strtotime(carbon_get_the_post_meta('fecha_estreno'))); ?></p>
        </div>
    </header>

    <section class="ficha-artistica">
        <h2>Ficha Artística</h2>
        <?php 
        $ficha = carbon_get_the_post_meta('ficha_artistica');
        if(!empty($ficha)): ?>
            <ul class="ficha-list">
                <?php foreach($ficha as $miembro): ?>
                    <li>
                        <strong><?php echo $miembro['rol']; ?>:</strong>
                        <?php echo $miembro['nombre']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <?php if($galeria = carbon_get_the_post_meta('galeria_fotos')): ?>
    <section class="galeria">
        <h2>Galería</h2>
        <div class="grid-galeria">
            <?php foreach($galeria as $image_id): ?>
                <?php echo wp_get_attachment_image($image_id, 'large'); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="Presentacion">
        <h2>Presentacion</h2>
        <?php echo carbon_get_the_post_meta('presentacion'); ?>
    </section>

    <section class="sinopsis">
        <h2>Sinopsis</h2>
        <?php echo carbon_get_the_post_meta('sinopsis'); ?>
    </section>
</article>

<?php get_footer(); ?>