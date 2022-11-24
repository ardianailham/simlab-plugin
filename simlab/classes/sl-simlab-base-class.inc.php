<?php

class SL_SIMLAB_BaseClass
{

  public function getTime()
  {
    date_default_timezone_set("Asia/Jakarta");
    $now = strtotime("Now");
    $then = strtotime("+1 hour", $now);
    $now = date("Y-m-d H:i", $now);
    $then = date("Y-m-d H:i", $then);
    // $interval = date_interval_create_from_date_string('1 hour');
    // $then = date_add($now, $interval);
    // $then = $then->format("H:i");
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
