<?php

class SL_SIMLAB_BaseClass
{

  public function getTime()
  {
    $now_ts = current_time('timestamp');
    $then_ts = $now_ts + HOUR_IN_SECONDS;
    $now = date("Y-m-d\TH:i", $now_ts);
    $then = date("Y-m-d\TH:i", $then_ts);
    return array($now, $then);
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
