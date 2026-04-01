<?php
require_once 'classes/sl-simlab-bahan-class.inc.php';
require_once 'classes/sl-simlab-logbook-bahan-class.inc.php';

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
  $obj   = new SL_SIMLAB_BahanClass;
  $nonce = wp_create_nonce('sl_simlab_bahan_action');

  /* ── DETAIL ─────────────────────────────────────────────────────────── */
  if (isset($_GET['detail-bahan'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getBahanById($id);
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">
        <div class="card" style="width: 18rem;">
          <div class="card-body">
            <h5 class="card-title"><?= esc_html($data['Nama_Bahan']); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?= esc_html($data['Merk']); ?></h6>
            <p class="card-text"><?= esc_html($data['Jumlah'] . ' ' . $data['Satuan']); ?></p>
            <p class="card-text"><?= esc_html($data['Serial']); ?></p>
            <p class="card-text"><?= esc_html($data['Exp']); ?></p>
            <p class="card-text"><?= esc_html($data['Letak']); ?></p>
            <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary btn-sm">Back</a>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── BOOKING ─────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['addlog-bahan'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getBahanById($id);
    $time = $obj->getTime();
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">
        <h3>Booking <?= esc_html($data['Nama_Bahan']); ?></h3>
        <form method="post">
          <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
          <input type="hidden" name="id_bahan" id="id_bahan" value="<?= intval($data['id']); ?>">
          <input type="hidden" name="status" id="status" value="3">
          <input type="hidden" name="user_id" id="user_id" value="<?= get_current_user_id(); ?>">
          <div class="mb-3">
            <label for="nama-bahan" class="form-label">Nama Bahan</label>
            <input type="text" class="form-control" id="nama-bahan" name="Nama_Bahan" value="<?= esc_attr($data['Nama_Bahan']); ?>">
          </div>
          <div class="mb-3">
            <label for="merk" class="form-label">Merk</label>
            <input type="text" class="form-control" id="merk" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
          </div>
          <div class="mb-3">
            <label for="Qty" class="form-label">Jumlah</label>
            <input type="number" step="any" class="form-control" id="Qty" name="Qty" min="0" max="<?= esc_attr($data['Jumlah']); ?>" value="1">
          </div>
          <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" value="<?= esc_attr($time[0]); ?>">
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" name="submit-log-bahan" value="1">Book</button>
            <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Back</a>
          </div>
        </form>
      </div>
    </div>

<?php
  /* ── EDIT ────────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['ubah-bahan'])) {
    $data = $obj->getBahanById(intval($_GET['id']));
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">
        <div class="ubah-bahan" id="ubah-bahan">
          <form method="post">
            <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
            <input type="hidden" name="id" id="id" value="<?= intval($data['id']); ?>">
            <div class="mb-3">
              <label for="nama-bahan" class="form-label">Nama Bahan</label>
              <input type="text" class="form-control" id="nama-bahan" name="Nama_Bahan" value="<?= esc_attr($data['Nama_Bahan']); ?>">
            </div>
            <div class="mb-3">
              <label for="merk" class="form-label">Merk</label>
              <input type="text" class="form-control" id="merk" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
            </div>
            <div class="mb-3">
              <label for="Qty" class="form-label">Jumlah</label>
              <input type="number" step="any" class="form-control" id="Qty" name="Qty" min="0" value="<?= esc_attr($data['Jumlah']); ?>">
            </div>
            <div class="mb-3">
              <label for="satuan" class="form-label">Satuan</label>
              <input type="text" class="form-control" id="satuan" name="Satuan" value="<?= esc_attr($data['Satuan']); ?>">
            </div>
            <div class="mb-3">
              <label for="serial" class="form-label">Serial</label>
              <input type="text" class="form-control" id="serial" name="Serial" value="<?= esc_attr($data['Serial']); ?>">
            </div>
            <div class="mb-3">
              <label for="Exp" class="form-label">Exp</label>
              <input type="text" class="form-control" id="Exp" name="Exp" value="<?= esc_attr($data['Exp']); ?>">
            </div>
            <div class="mb-3">
              <label for="letak" class="form-label">Letak</label>
              <input type="text" class="form-control" id="letak" name="Letak" value="<?= esc_attr($data['Letak']); ?>">
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary" name="ubah-bahan" value="1">Ubah Bahan</button>
              <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Back</a>
            </div>
          </form>
        </div>
      </div>
    </div>

<?php
  /* ── DELETE ──────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['hapus-bahan'])) {
    if (!SL_SIMLAB_Auth::is_admin()) {
      wp_die(__('You do not have permission to perform this action.'));
    }
    $hapus = $obj->hapusBahan(intval($_GET['id']));
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
    if (isset($_POST['submit-log-bahan']) && check_admin_referer('sl_simlab_bahan_action') && SL_SIMLAB_Auth::can_book()) {
      $obj1   = new SL_SIMLAB_LogbookBahanClass;
      $addLog = $obj1->addLogBahan($_POST);
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

    /* ── Handle POST: Import bahan ────────────────────────────────────── */
    if (isset($_POST['import-bahan']) && check_admin_referer('sl_import_bahan')) {
        global $simlab_export_import;
        $count = $simlab_export_import->importBahan($_FILES['file_csv']);
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

    /* ── Handle POST: Add bahan ──────────────────────────────────────── */
    if (isset($_POST['submit-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
      if (empty($_POST['Nama_Bahan']) || !isset($_POST['Jumlah'])) {
?>
        <script type="text/javascript">
          alert('Form yang anda masukkan tidak benar!');
          history.back();
        </script>
<?php
      } else {
        $obj->tambahBahan($_POST);
?>
        <script type="text/javascript">
          document.location = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>';
        </script>
<?php
      }
    }

    /* ── Handle POST: Edit bahan ─────────────────────────────────────── */
    if (isset($_POST['ubah-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
      if ($obj->ubahBahan($_POST) > 0) {
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

    $data = $obj->getBahan();
?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-12">

        <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
          <div class="row mb-3">
            <div class="col-lg-12 d-flex gap-2 align-items-center">
              <button id="tambah-bahan-button" class="btn btn-primary" onclick="return tambahBahan()">Tambah Bahan</button>
              <a href="<?= wp_nonce_url(admin_url('admin.php?page=' . $obj->plugin_slug . $obj->menu_slug . '&action=export-bahan'), 'sl_export_bahan'); ?>" class="btn btn-success">Export CSV</a>
              <button class="btn btn-info text-white" onclick="return toggleImport()">Import CSV</button>

              <div class="import-bahan" id="import-bahan" style="display:none;">
                <form method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                  <?php wp_nonce_field('sl_import_bahan', '_wpnonce'); ?>
                  <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                  <button type="submit" name="import-bahan" class="btn btn-info text-white">Upload</button>
                </form>
              </div>

              <div class="tambah-bahan" id="tambah-bahan">
                <form method="post" class="mt-3">
                  <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
                  <input type="hidden" name="id" id="id">
                  <div class="mb-3">
                    <label for="nama-bahan" class="form-label">Nama Bahan</label>
                    <input type="text" class="form-control" id="nama-bahan" name="Nama_Bahan">
                  </div>
                  <div class="mb-3">
                    <label for="merk" class="form-label">Merk</label>
                    <input type="text" class="form-control" id="merk" name="Merk">
                  </div>
                  <div class="mb-3">
                    <label for="jumlah" class="form-label">Jumlah</label>
                    <input type="number" step="any" class="form-control" id="jumlah" name="Jumlah" min="0">
                  </div>
                  <div class="mb-3">
                    <label for="satuan" class="form-label">Satuan</label>
                    <input type="text" class="form-control" id="satuan" name="Satuan">
                  </div>
                  <div class="mb-3">
                    <label for="serial" class="form-label">Serial</label>
                    <input type="text" class="form-control" id="serial" name="Serial">
                  </div>
                  <div class="mb-3">
                    <label for="Exp" class="form-label">Exp</label>
                    <input type="text" class="form-control" id="Exp" name="Exp">
                  </div>
                  <div class="mb-3">
                    <label for="letak" class="form-label">Letak</label>
                    <input type="text" class="form-control" id="letak" name="Letak">
                  </div>
                  <button type="submit" class="btn btn-primary" name="submit-bahan" value="1">Submit</button>
                </form>
              </div>
            </div>
          </div>
        <?php } ?>

        <div class="row">
          <div class="col-lg-12">
            <h3 class="mt-3">Daftar Bahan</h3>
            <table class="table table-bordered table-responsive table-striped" cellpadding="10" cellspacing="0">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Bahan</th>
                  <th>Merk</th>
                  <th>Jumlah</th>
                  <th>Letak</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($data as $alat) : ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td><?= esc_html($alat['Nama_Bahan']); ?></td>
                    <td><?= esc_html($alat['Merk']); ?></td>
                    <td><?= esc_html($alat['Jumlah'] . ' ' . $alat['Satuan']); ?></td>
                    <td><?= esc_html($alat['Letak']); ?></td>
                    <td>
                      <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&hapus-bahan&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-danger ms-1"
                           onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&ubah-bahan&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-warning ms-1">Edit</a>
                      <?php } ?>
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail-bahan&id=<?= intval($alat['id']); ?>"
                         class="btn btn-sm btn-primary ms-1">Detail</a>
                      <?php if (SL_SIMLAB_Auth::can_book()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&addlog-bahan&id=<?= intval($alat['id']); ?>"
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
      .tambah-bahan {
        display: none;
      }
    </style>
    <script type="text/javascript">
      function tambahBahan() {
        var tambahBahan = document.getElementById('tambah-bahan');
        var importBahan = document.getElementById('import-bahan');
        importBahan.style.display = 'none';
        if (tambahBahan.style.display === 'block') {
          tambahBahan.style.display = 'none';
        } else {
          tambahBahan.style.display = 'block';
        }
        return false;
      }

      function toggleImport() {
        var importBahan = document.getElementById('import-bahan');
        var tambahBahan = document.getElementById('tambah-bahan');
        tambahBahan.style.display = 'none';
        if (importBahan.style.display === 'block') {
          importBahan.style.display = 'none';
        } else {
          importBahan.style.display = 'block';
        }
        return false;
      }
    </script>

<?php
  } // end else (list)
} // end is_user_logged_in
?>