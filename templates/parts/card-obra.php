<!-- En tu archivo card-obra.php -->
 <article class="obra-card">
   <a href="<?php the_permalink(); ?>" class="card-link">
     <div class="card-image" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.3)), url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>')">
       <?php if($genero = carbon_get_post_meta(get_the_ID(), 'genero')): ?>
         <span class="card-category"><?php echo esc_html($genero); ?></span>
       <?php endif; ?>
     </div>
     
     <div class="card-content">
       <h2 class="card-title"><?php the_title(); ?></h2>
       
       <div class="card-meta">
         <?php if($autor = carbon_get_post_meta(get_the_ID(), 'autor')): ?>
           <p class="card-author">
             <i class="fa fa-user"></i>
             <?php echo esc_html($autor); ?>
           </p>
         <?php endif; ?>
 
         <?php 
           $estreno = carbon_get_post_meta(get_the_ID(), 'fecha_estreno');
           $finalizacion = carbon_get_post_meta(get_the_ID(), 'fecha_finalizacion');
         ?>
         <?php if($estreno): ?>
           <p class="card-dates">
             <i class="fa fa-calendar"></i>
             <?php echo date_i18n('j M Y', strtotime($estreno)); ?>
             <?php if($finalizacion): ?>
               - <?php echo date_i18n('j M Y', strtotime($finalizacion)); ?>
             <?php endif; ?>
           </p>
         <?php endif; ?>
       </div>
     </div>
   </a>
 </article>
 
 <!-- CSS actualizado -->
 <style>
 .obra-card {
   position: relative;
   width: 100%;
   max-width: 350px;
   border-radius: 8px;
   overflow: hidden;
   transition: transform 0.3s ease;
   background: #fff;
   box-shadow: 0 4px 12px rgba(0,0,0,0.1);
 }
 
 .obra-card:hover {
   transform: translateY(-5px);
 }
 
 .card-image {
   height: 200px;
   background-size: cover;
   background-position: top;
   position: relative;
   display: flex;
   align-items: flex-end;
   padding: 20px;
 }
 
 .card-category {
   background: rgba(255,255,255,0.9);
   color: #333;
   padding: 6px 12px;
   border-radius: 20px;
   font-size: 0.9rem;
   font-weight: 600;
   text-transform: uppercase;
   letter-spacing: 0.5px;
 }
 
 .card-content {
   padding: 20px;
   background: #fff;
 }
 
 .card-title {
   color: #2c3e50;
   margin: 0 0 12px 0;
   font-size: 1.4rem;
   line-height: 1.3;
 }
 
 .card-meta {
   display: flex;
   flex-wrap: wrap;
   gap: 15px;
   color: #7f8c8d;
   font-size: 0.9rem;
 }
 
 .card-meta i {
   margin-right: 6px;
   color: #e74c3c;
 }
 
 .card-link {
   text-decoration: none;
   color: inherit;
   display: block;
 }
 
 /* Efecto hover overlay */
 .card-image::after {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   right: 0;
   bottom: 0;
   background: rgba(0,0,0,0.3);
   opacity: 0;
   transition: opacity 0.3s ease;
 }
 
 .obra-card:hover .card-image::after {
   opacity: 1;
 }
 
 @media (max-width: 768px) {
   .card-image {
     height: 250px;
   }
   
   .card-title {
     font-size: 1.2rem;
   }
 }
 </style>