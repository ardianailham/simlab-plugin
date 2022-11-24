<?php

function sl_template_list()
{
  $temps = [];
  $temps['sl_simlab_default.php'] = 'SIMLAB Default';

  return $temps;
}

function sl_template_register($page_templates, $theme, $post)
{
  $templates = sl_template_list();
  foreach ($templates as $tk => $tv) {
    $page_templates[$tk] = $tv;
  }
  return $page_templates;
}


function sl_template_include($template)
{
  global $post, $wp_query, $wpdb;
  if (isset($post->ID)) {
    $page_temp_slug = get_page_template_slug($post->ID);
    $templates = sl_template_list();
    if (isset($templates[$page_temp_slug])) {
      $template = plugin_dir_path(__FILE__) . 'templates/' . $page_temp_slug;
    }
  }
  return $template;
}
