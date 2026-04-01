<?php
class SL_SimlabPlugin extends SL_SIMLAB_BaseClass
{
  private $plugin_name = 'SIMLAB';
  public $plugin_slug = 'simlab';
  public $table_1 = 'sl_simlab_alat';
  public $table_2 = 'sl_simlab_bahan';
  public $table_3 = 'sl_simlab_logbook_alat';
  public $table_4 = 'sl_simlab_logbook_bahan';
  public $table_5 = 'sl_simlab_status';

  function simlab_admin_menu()
  {
    if (SL_SIMLAB_Auth::is_admin()) {
      $cap = 'read'; // Base capability for Simlab Admins
      add_menu_page($this->plugin_name, 'SIMLAB', $cap, $this->plugin_slug, 'setting_page', '', 200);

      // Only WP Super Admins (manage_options) see the general Settings page
      if (current_user_can('manage_options')) {
        add_submenu_page($this->plugin_slug, 'Settings', 'Settings', 'manage_options', $this->plugin_slug . '-settings', 'setting_page');
      }

      add_submenu_page($this->plugin_slug, 'Daftar Alat', 'Daftar Alat', $cap, $this->plugin_slug . '-daftar-alat', 'setting_page');
      add_submenu_page($this->plugin_slug, 'Daftar Bahan', 'Daftar Bahan', $cap, $this->plugin_slug . '-daftar-bahan', 'setting_page');
      add_submenu_page($this->plugin_slug, 'Logbook Alat', 'Logbook Alat', $cap, $this->plugin_slug . '-logbook-alat', 'setting_page');
      add_submenu_page($this->plugin_slug, 'Logbook Bahan', 'Logbook Bahan', $cap, $this->plugin_slug . '-logbook-bahan', 'setting_page');
      add_submenu_page($this->plugin_slug, 'User Management', 'User Management', $cap, $this->plugin_slug . '-user-management', 'setting_page');
    }
  }

  function simlab_fixed_button()
  {
    $option = get_option('sl_simlab_links');
    if (!$option) {
      $option['daftar-alat'] = '#';
      $option['daftar-bahan'] = '#';
    }
?>
    <a href="<?= $option['daftar-alat']; ?>" id="buttonDaftarAlat" class="ms-3"></a>
    <a href="<?= $option['daftar-bahan']; ?>" id="buttonDaftarBahan" class="ms-3"></a>
<?php
  }
  public function _install()
  {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    $p = $wpdb->prefix;

    // --- Table definitions (dbDelta handles CREATE vs ALTER automatically) ---

    $sql_alat = "CREATE TABLE `{$p}sl_simlab_alat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Nama_Alat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Merk` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Qty` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB {$charset_collate};";

    $sql_bahan = "CREATE TABLE `{$p}sl_simlab_bahan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Nama_Bahan` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Jumlah` decimal(10,5) NOT NULL,
  `Satuan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Merk` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Serial` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Exp` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Letak` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB {$charset_collate};";

    $sql_logbook_alat = "CREATE TABLE `{$p}sl_simlab_logbook_alat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_alat` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `start_date` bigint(20) NOT NULL,
  `end_date` bigint(20) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `id_alat` (`id_alat`)
) ENGINE=InnoDB {$charset_collate};";

    $sql_logbook_bahan = "CREATE TABLE `{$p}sl_simlab_logbook_bahan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_bahan` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` decimal(10,5) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `id_bahan` (`id_bahan`)
) ENGINE=InnoDB {$charset_collate};";

    $sql_status = "CREATE TABLE `{$p}sl_simlab_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB {$charset_collate};";

    dbDelta($sql_alat);
    dbDelta($sql_bahan);
    dbDelta($sql_logbook_alat);
    dbDelta($sql_logbook_bahan);
    dbDelta($sql_status);

    // --- Seed data (INSERT IGNORE skips duplicates safely on re-activation) ---
    $statuses = [
      [1, 'Ongoing'],
      [2, 'Completed'],
      [3, 'Pending'],
      [4, 'Rejected'],
      [5, 'Accepted'],
    ];
    foreach ($statuses as [$id, $name]) {
      $wpdb->query(
        $wpdb->prepare(
          "INSERT IGNORE INTO `{$p}sl_simlab_status` (`id`, `name`) VALUES (%d, %s)",
          $id,
          $name
        )
      );
    }

    // --- Auto-create frontend pages ---
    $this->_create_pages();
  }

  /**
   * Create (or locate) the 4 SIMLAB frontend pages and persist their URLs
   * in the sl_simlab_links option so Settings & fixed buttons work out of the box.
   */
  private function _create_pages()
  {
    $pages = [
      'daftar-alat'        => ['title' => 'Daftar Alat',        'shortcode' => '[daftar-alat]'],
      'daftar-bahan'       => ['title' => 'Daftar Bahan',       'shortcode' => '[daftar-bahan]'],
      'logbook-alat'       => ['title' => 'Logbook Alat',       'shortcode' => '[daftar-logbook-alat]'],
      'logbook-bahan'      => ['title' => 'Logbook Bahan',      'shortcode' => '[daftar-logbook-bahan]'],
    ];

    $links = get_option('sl_simlab_links', []);

    foreach ($pages as $key => $info) {
      // Skip if we already have a valid URL saved for this key
      if (!empty($links[$key])) {
        continue;
      }

      // Check if a published page with this title already exists
      $existing = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'title'          => $info['title'],
      ]);

      if (!empty($existing)) {
        $page_id = $existing[0]->ID;
      } else {
        // Create a fresh page
        $page_id = wp_insert_post([
          'post_title'     => $info['title'],
          'post_name'      => $key,
          'post_content'   => $info['shortcode'],
          'post_status'    => 'publish',
          'post_type'      => 'page',
          'comment_status' => 'closed',
          'ping_status'    => 'closed',
          'page_template'  => 'sl_simlab_default.php',
        ]);

        if (is_wp_error($page_id)) {
          continue;
        }

        // Assign the SIMLAB template to the new page
        update_post_meta($page_id, '_wp_page_template', 'sl_simlab_default.php');
      }

      $links[$key] = get_permalink($page_id);
    }

    // Persist the collected links
    if (get_option('sl_simlab_links') !== false) {
      update_option('sl_simlab_links', $links);
    } else {
      add_option('sl_simlab_links', $links);
    }
  }
  public static function admin_header($title, $icon = 'fa-dashboard')
  {
?>
    <div class="simlab-admin-wrapper mt-4 px-3">
      <div class="container-fluid">
        <div class="row mb-4 align-items-center">
          <div class="col-md-8">
            <h1 class="wp-heading-inline m-0" style="font-weight: 700; color: #2c3e50;">
              <i class="fa <?= $icon ?> text-primary me-2"></i> <?= $title ?>
            </h1>
          </div>
          <div class="col-md-4 text-md-end mt-2 mt-md-0">
            <div class="d-inline-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm border">
               <span class="dot bg-success me-2" style="width: 8px; height: 8px; border-radius: 50%;"></span>
               <small class="text-muted fw-bold">SIMLAB</small>
               <span class="mx-2 text-muted">|</span>
               <small class="text-primary fw-bold">v1.0.1</small>
            </div>
          </div>
        </div>
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
          <div class="card-body p-4">
    <?php
  }

  public static function admin_footer()
  {
    ?>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center px-1 py-3 mt-2 border-top">
          <p class="mb-0 text-muted small">&copy; <?= date('Y') ?> <strong>SIMLAB</strong>. Built for excellence in lab management.</p>
          <div class="social-links d-flex gap-3">
            <a href="#" class="text-muted"><i class="fa fa-github"></i></a>
            <a href="#" class="text-muted"><i class="fa fa-globe"></i></a>
          </div>
        </div>
      </div>
    </div>
    <style>
      .simlab-admin-wrapper { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
      .bg-primary { background-color: #0d6efd !important; }
      .text-primary { color: #0d6efd !important; }
      .simlab-admin-wrapper .card { transition: all 0.3s ease; border-radius: 12px !important; max-width: none !important; width: 100% !important; }
      .btn-primary { background-color: #0d6efd; border-color: #0d6efd; border-radius: 8px; padding: 10px 20px; font-weight: 600; }
      .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; }
      .form-control, .form-select { border-radius: 8px; border: 1px solid #dee2e6; padding: 12px; }
      .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); border-color: #86b7fe; }
      .table th { background-color: #f8f9fa; border-bottom-width: 1px; color: #495057; font-weight: 600; }
      .badge { border-radius: 6px; font-weight: 600; }
    </style>
<?php
  }
}
