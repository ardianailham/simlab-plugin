<?php
if (! defined('ABSPATH')) {
  exit;
}

class SL_SIMLAB_BaseClass
{

  public function getTime($timezone = null)
  {
    $tz = $timezone ? new DateTimeZone($timezone) : wp_timezone();
    $now = new DateTime('now', $tz);
    $then = clone $now;
    $then->modify('+1 hour');

    return [
      $now->format('Y-m-d\TH:i'),
      $then->format('Y-m-d\TH:i'),
    ];
  }

  /** installation functions */
  public function install($networkwide)
  {
    global $wpdb;

    if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if ($networkwide) {
        $old_blog = $wpdb->blogid;
        // Get all blog ids
        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blogids as $blog_id) {
          switch_to_blog($blog_id);
          $this->_install();
        }
        switch_to_blog($old_blog);
        return;
      }
    }
    $this->_install();
  }

  // function verify_nonce($nonce, $action)
  // {
  //   $verify_nonce = wp_verify_nonce($nonce, $action);
  //   if (!$verify_nonce) {
  //     echo 'Error: Action cannot be authenticated (nonce failed). Please contact our support service if this problem persists.';
  //     exit;
  //   }
  // }
}
