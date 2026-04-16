<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if (!current_user_can('manage_options')) {
  wp_die('Unauthorized');
}
$simlab_plugin = new SL_SimlabPlugin;
$options = get_option('sl_simlab_links');
if ($options == false) $options = array('daftar-alat' => '', 'daftar-bahan' => '', 'logbook-alat' => '', 'logbook-bahan' => '', 'user-api-url' => '');

// Handle individual saves
if (isset($_POST['save_setting'])) {
  $key = sanitize_text_field($_POST['setting_key']);
  check_admin_referer('sl_save_setting_' . $key);
  $value = esc_url_raw($_POST['setting_value']);
  
  if (array_key_exists($key, $options) || $key === 'user-api-url') {
    $options[$key] = $value;
    update_option('sl_simlab_links', $options);
    echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            Setting updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
  }
}

SL_SimlabPlugin::admin_header('Settings', 'fa-cog');
?>

<div class="row">
  <div class="col-lg-12">
    <p class="text-muted mb-4">Masukkan link halaman yang sesuai untuk setiap kategori. Klik "Simpan" pada setiap baris untuk menyimpan perubahan secara individu.</p>
    
    <div class="settings-grid">
      
      <?php 
      $settings_fields = [
        'daftar-alat' => ['label' => 'Link Daftar Alat', 'id' => 'floatingDaftarAlat'],
        'daftar-bahan' => ['label' => 'Link Daftar Bahan', 'id' => 'floatingDaftarBahan'],
        'logbook-alat' => ['label' => 'Link Logbook Alat', 'id' => 'floatingLogbookAlat'],
        'logbook-bahan' => ['label' => 'Link Logbook Bahan', 'id' => 'floatingLogbookBahan'],
        'user-api-url' => ['label' => 'External User API URL', 'id' => 'floatingUserApi'],
      ];
      
      foreach ($settings_fields as $key => $field): 
      ?>
      <form method="POST" class="row mb-4 align-items-center">
        <?php wp_nonce_field('sl_save_setting_' . $key); ?>
        <input type="hidden" name="setting_key" value="<?= esc_attr($key) ?>">
        <div class="col-md-9">
          <div class="form-floating">
            <input type="url" class="form-control shadow-none" id="<?= $field['id'] ?>" name="setting_value" placeholder="<?= $field['label'] ?>" value="<?= isset($options[$key]) ? esc_attr($options[$key]) : '' ?>">
            <label for="<?= $field['id'] ?>"><?= $field['label'] ?></label>
          </div>
        </div>
        <div class="col-md-1">
          <button type="submit" name="save_setting" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 w-100 py-3 shadow-sm">
            <i class="fa fa-save"></i> Simpan
          </button>
        </div>
      </form>
      <?php endforeach; ?>
      
    </div>
  </div>
</div>

<style>
  .settings-grid form:last-child {
    margin-bottom: 0 !important;
  }
  .form-floating > .form-control:focus ~ label,
  .form-floating > .form-control:not(:placeholder-shown) ~ label {
    color: #0d6efd;
    opacity: 0.8;
  }
</style>

<?php SL_SimlabPlugin::admin_footer(); ?>
