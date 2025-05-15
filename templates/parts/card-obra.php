<!-- En tu archivo card-obra.php -->
<article class="obra-card">
  <a href="<?php the_permalink(); ?>" class="card-link">
    <div class="card-image" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>')">
      <?php if ($genero = carbon_get_post_meta(get_the_ID(), 'genero')): ?>
        <span class="card-category"><?php echo esc_html($genero); ?></span>
      <?php endif; ?>
    </div>

    <div class="card-content">
      <h2 class="card-title"><?php the_title(); ?></h2>

      <div class="card-meta">
        <?php if ($autor = carbon_get_post_meta(get_the_ID(), 'autor')): ?>
          <p class="card-author">
            <i class="fa fa-user"></i>
            <?php echo esc_html($autor); ?>
          </p>
        <?php endif; ?>

        <?php
        $estreno = carbon_get_post_meta(get_the_ID(), 'fecha_estreno');
        $finalizacion = carbon_get_post_meta(get_the_ID(), 'fecha_finalizacion');
        ?>
        <?php if ($estreno): ?>
          <p class="card-dates">
            <i class="fa fa-calendar"></i>
            <?php echo date_i18n('j M Y', strtotime($estreno)); ?>
            <?php if ($finalizacion): ?>
              - <?php echo date_i18n('j M Y', strtotime($finalizacion)); ?>
            <?php endif; ?>
          </p>
        <?php endif; ?>
      </div>
    </div>
  </a>
</article>