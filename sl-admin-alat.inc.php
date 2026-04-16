<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';

if (!is_user_logged_in()) {
?>
  <div class="row">
    <h3>Silakan Login Terlebih dahulu atau Daftar apabila anda belum memiliki akun</h3>
  </div>
  <div class="d-flex justify-content-center">
    <?php $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request)); ?>
    <a href="<?php echo esc_url(wp_login_url($current_url)); ?>" class="btn btn-primary me-1"><?php _e('Log in'); ?></a>
    <a href="<?php echo esc_url(wp_registration_url($current_url)); ?>" class="btn btn-success ms-1"><?php _e('Register'); ?></a>
  </div>
<?php
} else {
  $obj   = new SL_SIMLAB_AlatClass;
  $nonce = wp_create_nonce('sl_simlab_alat_action');

  /* ── HANDLE POST ACTIONS ─────────────────────────────────────────────── */
  
  // 1. Handle Submit Booking Log
  if ((isset($_POST['submit-log-alat']) || (isset($_POST['action_type']) && $_POST['action_type'] === 'submit-log-alat')) && check_admin_referer('sl_simlab_alat_action') && SL_SIMLAB_Auth::can_book()) {
    $obj1   = new SL_SIMLAB_LogbookAlatClass;
    $addLog = $obj1->addLogAlat($_POST);
    if ($addLog > 0) {
?>
      <script type="text/javascript">
        alert('Data Berhasil Ditambahkan');
        document.location = '?page=<?= esc_js($obj1->plugin_slug . $obj1->menu_slug); ?>';
      </script>
<?php
      exit;
    }
  }

  // 2. Handle Import Alat
  if (isset($_POST['import-alat']) && check_admin_referer('sl_import_alat')) {
      global $simlab_export_import;
      $count = $simlab_export_import->importAlat($_FILES['file_csv']);
      if ($count !== false) {
?>
        <script type="text/javascript">
          alert('<?= intval($count); ?> Data Berhasil Diimport');
          document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
        </script>
<?php
        exit;
      } else {
?>
        <script type="text/javascript">
          alert('Data Gagal Diimport. Pastikan file benar.');
          history.back();
        </script>
<?php
        exit;
      }
  }

  // 3. Handle Add Alat
  if (isset($_POST['submit-alat']) && check_admin_referer('sl_simlab_alat_action')) {
    if (empty($_POST['Nama_Alat']) || empty($_POST['Qty'])) {
?>
      <script type="text/javascript">
        alert('Form yang anda masukkan tidak benar!');
        history.back();
      </script>
<?php
      exit;
    } else {
      $obj->tambahAlat($_POST);
?>
      <script type="text/javascript">
        document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
      </script>
<?php
      exit;
    }
  }

  // 4. Handle Edit Alat
  if (isset($_POST['ubah-alat']) && check_admin_referer('sl_simlab_alat_action')) {
    if ($obj->ubahAlat($_POST) > 0) {
?>
      <script type="text/javascript">
        document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
      </script>
<?php
      exit;
    } else {
?>
      <script type="text/javascript">
        alert('Data Gagal Diubah');
        history.back();
      </script>
<?php
      exit;
    }
  }

  SL_SimlabPlugin::admin_header('Manajemen Alat', 'fa-wrench');

  /* ── DETAIL ─────────────────────────────────────────────────────────── */
  if (isset($_GET['detail-alat'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getAlatById($id);
    if (!$data) {
        echo "<div class='alert alert-danger'>Data alat tidak ditemukan!</div>";
        return;
    }
?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold text-primary mb-3"><i class="fa fa-info-circle me-2"></i>Detail Alat</h5>
            <table class="table table-sm">
              <tr><th width="30%">Nama Alat</th><td>: <?= esc_html($data['Nama_Alat']); ?></td></tr>
              <tr><th>Merk</th><td>: <?= esc_html($data['Merk']); ?></td></tr>
              <tr><th>Stok / Qty</th><td>: <span class="badge bg-info"><?= esc_html($data['Qty']); ?> Unit</span></td></tr>
            </table>
            <div class="mt-4">
               <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── EDIT ────────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['ubah-alat'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getAlatById($id);
    if (!$data) {
        echo "<div class='alert alert-danger'>Data alat tidak ditemukan!</div>";
        return;
    }
?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold mb-4"><i class="fa fa-edit me-2 text-warning"></i>Ubah Data Alat</h5>
            <form method="post">
              <?php wp_nonce_field('sl_simlab_alat_action', '_wpnonce'); ?>
              <input type="hidden" name="id" value="<?= intval($data['id']); ?>">
              <div class="mb-3">
                <label for="nama-alat" class="form-label">Nama Alat</label>
                <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" value="<?= esc_attr($data['Nama_Alat']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="merk" class="form-label">Merk</label>
                <input type="text" class="form-control" id="merk" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
              </div>
              <div class="mb-3">
                <label for="Qty" class="form-label">Qty / Stok</label>
                <input type="number" class="form-control" id="Qty" name="Qty" min="1" value="<?= esc_attr($data['Qty']); ?>" required>
              </div>
              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" name="ubah-alat" value="1"><i class="fa fa-save me-1"></i> Simpan Perubahan</button>
                <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── BOOKING ─────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['addlog-alat'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getAlatById($id);
    if (!$data) {
        echo "<div class='alert alert-danger'>Data alat tidak ditemukan!</div>";
        return;
    }
    $time = $obj->getTime();
?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold mb-4"><i class="fa fa-calendar-plus-o me-2 text-success"></i>Booking: <?= esc_html($data['Nama_Alat']); ?></h5>
            <form method="post">
              <?php wp_nonce_field('sl_simlab_alat_action', '_wpnonce'); ?>
              <input type="hidden" name="action_type" value="submit-log-alat">
              <input type="hidden" name="submit-log-alat" value="1">
              <input type="hidden" name="id_alat" value="<?= intval($data['id']); ?>">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nama Alat</label>
                  <input type="text" class="form-control bg-light" value="<?= esc_attr($data['Nama_Alat']); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Merk</label>
                  <input type="text" class="form-control bg-light" value="<?= esc_attr($data['Merk']); ?>" readonly>
                </div>
              </div>
              <div class="mb-3">
                <label for="Qty" class="form-label">Jumlah Pinjam (Maks: <?= esc_attr($data['Qty']); ?>)</label>
                <input type="number" class="form-control" id="Qty" name="Qty" min="1" max="<?= esc_attr($data['Qty']); ?>" value="1">
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="start_date" class="form-label">Tanggal Mulai</label>
                  <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= esc_attr($time[0]); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="end_date" class="form-label">Tanggal Selesai</label>
                  <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= esc_attr($time[1]); ?>">
                </div>
              </div>
              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success" name="submit-log-alat" value="1"><i class="fa fa-check me-1"></i> Konfirmasi Booking</button>
                <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── DELETE ──────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['hapus-alat'])) {
    check_admin_referer('sl_hapus_alat_' . intval($_GET['id']));
    if (!is_user_logged_in() || !SL_SIMLAB_Auth::is_admin()) {
      wp_die(__('You do not have permission to access this page.'));
    }
    $hapus = $obj->hapusAlat(intval($_GET['id']));
    if ($hapus > 0) {
?>
      <script type="text/javascript">
        alert('Data Berhasil Dihapus');
        document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
      </script>
<?php
    } else {
?>
      <script type="text/javascript">
        alert('Data Gagal Dihapus');
        history.back();
      </script>
<?php
    }

  /* ── LIST (default) ──────────────────────────────────────────────────── */
  } else {
    $data = $obj->getAlat();
?>
    <div class="row">
      <div class="col-lg-12">

        <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
          <div class="d-flex flex-wrap gap-2 mb-4 justify-content-between align-items-center">
            <div class="d-flex gap-2">
              <button id="tambah-alat-button" class="btn btn-primary shadow-sm" onclick="return tambahAlat()"><i class="fa fa-plus me-1"></i> Tambah Alat</button>
              <button class="btn btn-info text-white shadow-sm" onclick="return toggleImport()"><i class="fa fa-upload me-1"></i> Import CSV</button>
              <a href="<?= wp_nonce_url(admin_url('admin.php?page=' . $obj->plugin_slug . $obj->menu_slug . '&action=export-alat'), 'sl_export_alat'); ?>" class="btn btn-success shadow-sm"><i class="fa fa-download me-1"></i> Export CSV</a>
            </div>
          </div>

          <div class="import-alat card mb-4 border-info shadow-sm" id="import-alat" style="display:none; border-left: 5px solid #0dcaf0;">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Import Data Alat (.csv)</h6>
              <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
                <?php wp_nonce_field('sl_import_alat', '_wpnonce'); ?>
                <div class="col-auto">
                  <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                </div>
                <div class="col-auto">
                  <button type="submit" name="import-alat" class="btn btn-info text-white">Upload & Import</button>
                  <button type="button" class="btn btn-link text-muted" onclick="toggleImport()">Batal</button>
                </div>
              </form>
            </div>
          </div>

          <div class="tambah-alat card mb-4 border-primary shadow-sm" id="tambah-alat" style="display:none; border-left: 5px solid #0d6efd;">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Formulir Tambah Alat Baru</h6>
              <form method="post" class="row g-3">
                <?php wp_nonce_field('sl_simlab_alat_action', '_wpnonce'); ?>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Nama Alat</label>
                  <input type="text" class="form-control" name="Nama_Alat" required placeholder="Contoh: Mikroskop Binokuler">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Merk</label>
                  <input type="text" class="form-control" name="Merk" placeholder="Contoh: Olympus">
                </div>
                <div class="col-md-2">
                  <label class="form-label small fw-bold">Qty / Stok</label>
                  <input type="number" class="form-control" name="Qty" min="1" required value="1">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100" name="submit-alat" value="1">Simpan</button>
                </div>
              </form>
            </div>
          </div>
        <?php } ?>

        <div class="table-responsive">
          <table class="table table-hover align-middle border">
            <thead class="table-light">
              <tr>
                <th width="50">No</th>
                <th>Nama Alat</th>
                <th>Merk</th>
                <th width="100">Jumlah</th>
                <th width="250" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; ?>
              <?php if (empty($data)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data alat.</td></tr>
              <?php endif; ?>
              <?php foreach ($data as $alat) : ?>
                <tr>
                  <td><?= $i; ?></td>
                  <td class="fw-bold"><?= esc_html($alat['Nama_Alat']); ?></td>
                  <td><?= esc_html($alat['Merk']); ?></td>
                  <td><span class="badge bg-light text-dark border"><?= esc_html($alat['Qty']); ?> Unit</span></td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail-alat&id=<?= intval($alat['id']); ?>"
                         class="btn btn-sm btn-outline-primary" title="Detail"><i class="fa fa-eye"></i> Detail</a>
                         
                      <?php if (SL_SIMLAB_Auth::can_book()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&addlog-alat&id=<?= intval($alat['id']); ?>"
                          class="btn btn-sm btn-success" title="Book"><i class="fa fa-calendar"></i> Book</a>
                      <?php } ?>
                      
                      <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&ubah-alat&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-pencil"></i> Edit</a>
                        <a href="<?= wp_nonce_url('?page=' . esc_attr($obj->plugin_slug . $obj->menu_slug) . '&hapus-alat&id=' . intval($alat['id']), 'sl_hapus_alat_' . intval($alat['id'])); ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus alat ini?');" title="Hapus"><i class="fa fa-trash"></i> Delete</a>
                      <?php } ?>
                    </div>
                  </td>
                </tr>
                <?php $i++; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <script type="text/javascript">
      function tambahAlat() {
        var tambahAlat = document.getElementById('tambah-alat');
        var importAlat = document.getElementById('import-alat');
        importAlat.style.display = 'none';
        tambahAlat.style.display = (tambahAlat.style.display === 'block') ? 'none' : 'block';
        return false;
      }

      function toggleImport() {
        var importAlat = document.getElementById('import-alat');
        var tambahAlat = document.getElementById('tambah-alat');
        tambahAlat.style.display = 'none';
        importAlat.style.display = (importAlat.style.display === 'block') ? 'none' : 'block';
        return false;
      }
    </script>

<?php
  } // end else (list)
  
  SL_SimlabPlugin::admin_footer();
} // end is_user_logged_in
?>