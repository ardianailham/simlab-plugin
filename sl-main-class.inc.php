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
    // add_options_page();
    add_menu_page($this->plugin_name, 'SIMLAB', 'manage_options', $this->plugin_slug, 'setting_page', '', 200);
    add_submenu_page($this->plugin_slug, 'Settings', 'Settings', 'edit_pages', $this->plugin_slug . '-settings', 'setting_page');
    add_submenu_page($this->plugin_slug, 'Daftar Alat', 'Daftar Alat', 'edit_pages', $this->plugin_slug . '-daftar-alat', 'setting_page');
    add_submenu_page($this->plugin_slug, 'Daftar Bahan', 'Daftar Bahan', 'edit_pages', $this->plugin_slug . '-daftar-bahan', 'setting_page');
    add_submenu_page($this->plugin_slug, 'Logbook Alat', 'Logbook Alat', 'edit_pages', $this->plugin_slug . '-logbook-alat', 'setting_page');
    add_submenu_page($this->plugin_slug, 'Logbook Bahan', 'Logbook Bahan', 'edit_pages', $this->plugin_slug . '-logbook-bahan', 'setting_page');
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
  `Nama_Bahan` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Jumlah` decimal(10,5) NOT NULL,
  `Satuan` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Merk` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Serial` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Exp` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Letak` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
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
}
