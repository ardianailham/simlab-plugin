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
    $filename = plugin_dir_path(__FILE__) . 'sl_simlab_sql.sql';
    $templine = '';
    $lines = file($filename);
    foreach ($lines as $line) {
      if (substr($line, 0, 2) == '--' || $line == '')
        continue;

      $templine .= $line;
      if (substr(trim($line), -1, 1) == ';') {
        $wpdb->query($templine) or do_action('admin_notices', 'Error performing query \'<strong>' . $templine . '\': ' . $wpdb->print_error() . '<br /><br />');
        $templine = '';
      }
    }
    do_action('admin_notices', _e('Table is imported successfully'));
    wp_delete_file($filename);
  }
}
