<?php
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';

$user = get_current_user();

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

  /* ── DETAIL ─────────────────────────────────────────────────────────── */
  if (isset($_GET['detail-alat'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getAlatById($id);
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">
        <div class="card" style="width: 18rem;">
          <div class="card-body">
            <h5 class="card-title"><?= esc_html($data['Nama_Alat']); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?= esc_html($data['Merk']); ?></h6>
            <p class="card-text"><?= esc_html($data['Qty']); ?></p>
            <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary btn-sm">Back</a>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── EDIT ────────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['ubah-alat'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getAlatById($id);
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">
        <div class="ubah-alat" id="ubah-alat">
          <form method="post">
            <?php wp_nonce_field('sl_simlab_alat_action', '_wpnonce'); ?>
            <input type="hidden" name="id" id="id" value="<?= intval($data['id']); ?>">
            <div class="mb-3">
              <label for="nama-alat" class="form-label">Nama Alat</label>
              <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" value="<?= esc_attr($data['Nama_Alat']); ?>" required>
            </div>
            <div class="mb-3">
              <label for="merk" class="form-label">Merk</label>
              <input type="text" class="form-control" id="merk" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
            </div>
            <div class="mb-3">
              <label for="Qty" class="form-label">Qty</label>
              <input type="number" class="form-control" id="Qty" name="Qty" min="1" value="<?= esc_attr($data['Qty']); ?>" required>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary" name="ubah-alat" value="1">Ubah Alat</button>
              <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Back</a>
            </div>
          </form>
        </div>
      </div>
    </div>

<?php
  /* ── BOOKING ─────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['addlog-alat'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getAlatById($id);
    $time = $obj->getTime();
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">
        <h3>Booking <?= esc_html($data['Nama_Alat']); ?></h3>
        <form method="post">
          <?php wp_nonce_field('sl_simlab_alat_action', '_wpnonce'); ?>
          <input type="hidden" name="id_alat" id="id_alat" value="<?= intval($data['id']); ?>">
          <input type="hidden" name="user_id" id="user_id" value="<?= get_current_user_id(); ?>">
          <div class="mb-3">
            <label for="nama-alat" class="form-label">Nama Alat</label>
            <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" value="<?= esc_attr($data['Nama_Alat']); ?>">
          </div>
          <div class="mb-3">
            <label for="merk" class="form-label">Merk</label>
            <input type="text" class="form-control" id="merk" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
          </div>
          <div class="mb-3">
            <label for="Qty" class="form-label">Qty</label>
            <input type="number" class="form-control" id="Qty" name="Qty" min="1" max="<?= esc_attr($data['Qty']); ?>" value="1">
          </div>
          <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= esc_attr($time[0]); ?>">
          </div>
          <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= esc_attr($time[1]); ?>">
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" name="submit-log-alat" value="1">Book</button>
            <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Back</a>
          </div>
        </form>
      </div>
    </div>

<?php
  /* ── DELETE ──────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['hapus-alat'])) {
    if (!SL_SIMLAB_Auth::is_admin()) {
      wp_die(__('You do not have permission to perform this action.'));
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

    /* ── Handle POST: Submit booking log ─────────────────────────────── */
    if (isset($_POST['submit-log-alat']) && check_admin_referer('sl_simlab_alat_action') && SL_SIMLAB_Auth::can_book()) {
      $obj1   = new SL_SIMLAB_LogbookAlatClass;
      $addLog = $obj1->addLogAlat($_POST);
      if ($addLog > 0) {
?>
        <script type="text/javascript">
          alert('Data Berhasil Ditambahkan');
          document.location = '?page=<?= esc_js($obj1->plugin_slug . $obj1->menu_slug); ?>';
        </script>
<?php
      } else {
?>
        <script type="text/javascript">
          alert('Data Gagal Ditambahkan');
          history.back();
        </script>
<?php
      }
    }

    /* ── Handle POST: Import alat ────────────────────────────────────── */
    if (isset($_POST['import-alat']) && check_admin_referer('sl_import_alat')) {
        global $simlab_export_import;
        $count = $simlab_export_import->importAlat($_FILES['file_csv']);
        if ($count !== false) {
?>
          <script type="text/javascript">
            alert('<?= $count; ?> Data Berhasil Diimport');
            document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
          </script>
<?php
        } else {
?>
          <script type="text/javascript">
            alert('Data Gagal Diimport. Pastikan file benar.');
            history.back();
          </script>
<?php
        }
    }

    /* ── Handle POST: Add alat ───────────────────────────────────────── */
    if (isset($_POST['submit-alat']) && check_admin_referer('sl_simlab_alat_action')) {
      if (empty($_POST['Nama_Alat']) || empty($_POST['Qty'])) {
?>
        <script type="text/javascript">
          alert('Form yang anda masukkan tidak benar!');
          history.back();
        </script>
<?php
      } else {
        $obj->tambahAlat($_POST);
?>
        <script type="text/javascript">
          document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
        </script>
<?php
      }
    }

    /* ── Handle POST: Edit alat ──────────────────────────────────────── */
    if (isset($_POST['ubah-alat']) && check_admin_referer('sl_simlab_alat_action')) {
      if ($obj->ubahAlat($_POST) > 0) {
?>
        <script type="text/javascript">
          document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
        </script>
<?php
      } else {
?>
        <script type="text/javascript">
          alert('Data Gagal Diubah');
          history.back();
        </script>
<?php
      }
    }

    $data = $obj->getAlat();
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">

        <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
          <div class="row mb-3">
            <div class="col-lg-12 d-flex gap-2 align-items-center">
              <button id="tambah-alat-button" class="btn btn-primary" onclick="return tambahAlat()">Tambah Alat</button>
              <a href="<?= wp_nonce_url(admin_url('admin.php?page=' . $obj->plugin_slug . $obj->menu_slug . '&action=export-alat'), 'sl_export_alat'); ?>" class="btn btn-success">Export CSV</a>
              <button class="btn btn-info text-white" onclick="return toggleImport()">Import CSV</button>

              <div class="import-alat" id="import-alat" style="display:none;">
                <form method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                  <?php wp_nonce_field('sl_import_alat', '_wpnonce'); ?>
                  <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                  <button type="submit" name="import-alat" class="btn btn-info text-white">Upload</button>
                </form>
              </div>

              <div class="tambah-alat" id="tambah-alat">
                <form method="post" class="mt-3">
                  <?php wp_nonce_field('sl_simlab_alat_action', '_wpnonce'); ?>
                  <input type="hidden" name="id" id="id">
                  <div class="mb-3">
                    <label for="nama-alat" class="form-label">Nama Alat</label>
                    <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" required>
                  </div>
                  <div class="mb-3">
                    <label for="merk" class="form-label">Merk</label>
                    <input type="text" class="form-control" id="merk" name="Merk">
                  </div>
                  <div class="mb-3">
                    <label for="Qty" class="form-label">Qty</label>
                    <input type="number" class="form-control" id="Qty" name="Qty" min="1" required>
                  </div>
                  <button type="submit" class="btn btn-primary" name="submit-alat" value="1">Submit</button>
                </form>
              </div>
            </div>
          </div>
        <?php } ?>

        <div class="row">
          <div class="col-lg-12">
            <h3 class="mt-3">Daftar Alat</h3>
            <table class="table table-bordered table-responsive table-striped" cellpadding="10" cellspacing="0">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Alat</th>
                  <th>Merk</th>
                  <th>Jumlah</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($data as $alat) : ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td><?= esc_html($alat['Nama_Alat']); ?></td>
                    <td><?= esc_html($alat['Merk']); ?></td>
                    <td><?= esc_html($alat['Qty']); ?></td>
                    <td>
                      <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&hapus-alat&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-danger ms-1"
                           onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&ubah-alat&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-warning ms-1">Edit</a>
                      <?php } ?>
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail-alat&id=<?= intval($alat['id']); ?>"
                         class="btn btn-sm btn-primary ms-1">Detail</a>
                      <?php if (SL_SIMLAB_Auth::can_book()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&addlog-alat&id=<?= intval($alat['id']); ?>"
                          class="btn btn-sm btn-success ms-1">Book</a>
                      <?php } ?>
                    </td>
                  </tr>
                  <?php $i++; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

    <style>
      .tambah-alat {
        display: none;
      }
    </style>
    <script type="text/javascript">
      function tambahAlat() {
        var tambahAlat = document.getElementById('tambah-alat');
        var importAlat = document.getElementById('import-alat');
        importAlat.style.display = 'none';
        if (tambahAlat.style.display === 'block') {
          tambahAlat.style.display = 'none';
        } else {
          tambahAlat.style.display = 'block';
        }
        return false;
      }

      function toggleImport() {
        var importAlat = document.getElementById('import-alat');
        var tambahAlat = document.getElementById('tambah-alat');
        tambahAlat.style.display = 'none';
        if (importAlat.style.display === 'block') {
          importAlat.style.display = 'none';
        } else {
          importAlat.style.display = 'block';
        }
        return false;
      }
    </script>

<?php
  } // end else (list)
} // end is_user_logged_in
?>