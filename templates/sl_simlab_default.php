<?php
/**
 * Template Name: SIMLAB Default
 * Self-contained page template – no active theme header/footer required.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="container">
  <?php while (have_posts()) : the_post();
    the_content();
  endwhile; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>