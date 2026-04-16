<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $wpdb;
$p = $wpdb->prefix;

// Get stats
$total_alat    = $wpdb->get_var("SELECT COUNT(*) FROM {$p}sl_simlab_alat");
$total_bahan   = $wpdb->get_var("SELECT COUNT(*) FROM {$p}sl_simlab_bahan");
$active_alat   = $wpdb->get_var("SELECT COUNT(*) FROM {$p}sl_simlab_logbook_alat WHERE status != 2"); // status 2 is Completed
$active_bahan  = $wpdb->get_var("SELECT COUNT(*) FROM {$p}sl_simlab_logbook_bahan");

SL_SimlabPlugin::admin_header('Dashboard SIMLAB', 'fa-th-large');
?>

<div class="welcome-section mb-5">
  <h2 class="fw-bold text-dark">Selamat Datang, <?= wp_get_current_user()->display_name; ?>!</h2>
  <p class="text-muted">Kelola inventaris laboratorium Anda dengan lebih mudah dan efisien.</p>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-5">
  <div class="col-md-3">
    <div class="card h-100 border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
      <div class="card-body p-4 text-center">
        <i class="fa fa-wrench fa-3x mb-3 opacity-50"></i>
        <h3 class="display-6 fw-bold mb-0"><?= $total_alat ?></h3>
        <p class="mb-0 small text-uppercase fw-bold opacity-75">Total Alat</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100 border-0 shadow-sm bg-gradient-success text-white" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
      <div class="card-body p-4 text-center">
        <i class="fa fa-flask fa-3x mb-3 opacity-50"></i>
        <h3 class="display-6 fw-bold mb-0"><?= $total_bahan ?></h3>
        <p class="mb-0 small text-uppercase fw-bold opacity-75">Total Bahan</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100 border-0 shadow-sm bg-gradient-warning text-dark" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
      <div class="card-body p-4 text-center">
        <i class="fa fa-calendar-check-o fa-3x mb-3 opacity-50"></i>
        <h3 class="display-6 fw-bold mb-0"><?= $active_alat ?></h3>
        <p class="mb-0 small text-uppercase fw-bold opacity-75">Peminjaman Aktif</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100 border-0 shadow-sm bg-gradient-info text-white" style="background: linear-gradient(135deg, #0dcaf0 0%, #0bacce 100%);">
      <div class="card-body p-4 text-center">
        <i class="fa fa-history fa-3x mb-3 opacity-50"></i>
        <h3 class="display-6 fw-bold mb-0"><?= $active_bahan ?></h3>
        <p class="mb-0 small text-uppercase fw-bold opacity-75">Riwayat Bahan</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <h4 class="mb-4 fw-bold"><i class="fa fa-code me-2 text-primary"></i> Panduan Shortcode</h4>
    <p class="text-muted mb-4">Gunakan shortcode berikut untuk menampilkan data SIMLAB pada halaman website Anda.</p>
    
    <div class="table-responsive bg-white rounded shadow-sm">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th class="ps-4">Shortcode</th>
            <th>Kegunaan</th>
            <th class="text-end pe-4">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $shortcodes = [
            ['code' => '[daftar-alat]', 'desc' => 'Menampilkan tabel daftar alat yang tersedia'],
            ['code' => '[daftar-bahan]', 'desc' => 'Menampilkan daftar bahan kimia/habis pakai'],
            ['code' => '[daftar-logbook-alat]', 'desc' => 'Halaman peminjaman dan pengembalian alat'],
            ['code' => '[daftar-logbook-bahan]', 'desc' => 'Riwayat penggunaan bahan oleh user'],
          ];
          foreach ($shortcodes as $s):
          ?>
          <tr>
            <td class="ps-4">
              <code class="bg-light px-2 py-1 rounded text-primary fw-bold" style="font-size: 1.1rem;"><?= $s['code'] ?></code>
            </td>
            <td><?= $s['desc'] ?></td>
            <td class="text-end pe-4">
              <button class="btn btn-sm btn-outline-secondary copy-btn" onclick="copyToClipboard('<?= $s['code'] ?>', this)">
                <i class="fa fa-copy"></i> Salin
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card bg-light border-0">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="fa fa-info-circle me-2 text-primary"></i> Info Cepat</h5>
        <ul class="list-unstyled mb-0">
          <li class="mb-3 d-flex align-items-start">
            <i class="fa fa-check text-success mt-1 me-2"></i>
            <span>Jangan lupa untuk melakukan pengecekan berkala terhadap stok bahan.</span>
          </li>
          <li class="mb-3 d-flex align-items-start">
            <i class="fa fa-check text-success mt-1 me-2"></i>
            <span>Admin dapat mengelola user melalui menu <strong>User Management</strong>.</span>
          </li>
          <li class="d-flex align-items-start">
            <i class="fa fa-check text-success mt-1 me-2"></i>
            <span>Konfigurasi link halaman dapat diatur di menu <strong>Settings</strong>.</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
function copyToClipboard(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-check"></i> Tersalin!';
    btn.classList.replace('btn-outline-secondary', 'btn-success');
    btn.classList.add('text-white');
    setTimeout(() => {
      btn.innerHTML = originalText;
      btn.classList.replace('btn-success', 'btn-outline-secondary');
      btn.classList.remove('text-white');
    }, 2000);
  });
}
</script>

<?php SL_SimlabPlugin::admin_footer(); ?>