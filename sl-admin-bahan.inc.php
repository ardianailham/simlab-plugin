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

  SL_SimlabPlugin::admin_header('Manajemen Bahan', 'fa-flask');

  /* ── DETAIL ─────────────────────────────────────────────────────────── */
  if (isset($_GET['detail-bahan'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getBahanById($id);
?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold text-primary mb-4"><i class="fa fa-info-circle me-2"></i>Detail Bahan</h5>
            <div class="row">
              <div class="col-md-6">
                <table class="table table-sm border-0">
                  <tr><th width="40%">Nama Bahan</th><td>: <?= esc_html($data['Nama_Bahan']); ?></td></tr>
                  <tr><th>Merk</th><td>: <?= esc_html($data['Merk']); ?></td></tr>
                  <tr><th>Stok</th><td>: <span class="badge bg-success"><?= esc_html($data['Jumlah'] . ' ' . $data['Satuan']); ?></span></td></tr>
                </table>
              </div>
              <div class="col-md-6 border-start">
                <table class="table table-sm">
                  <tr><th width="40%">Serial No</th><td>: <?= esc_html($data['Serial']); ?></td></tr>
                  <tr><th>Tgl Exp</th><td>: <?= esc_html($data['Exp']); ?></td></tr>
                  <tr><th>Letak</th><td>: <?= esc_html($data['Letak']); ?></td></tr>
                </table>
              </div>
            </div>

            <!-- ── PubChem Chemical Information Panel ── -->
            <hr class="my-3">
            <h6 class="fw-bold mb-3" style="color:#6c757d;">
              <i class="fa fa-flask me-2" style="color:#0d6efd;"></i>Informasi Kimia (PubChem)
            </h6>
            <div data-pubchem-panel
                 data-pubchem-name="<?= esc_attr($data['Nama_Bahan']); ?>">
              <!-- Loaded by sl-simlab-pubchem.js -->
            </div>
            <!-- ── End PubChem Panel ── -->

            <div class="mt-4">
               <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── BOOKING/USAGE ─────────────────────────────────────────────────── */
  } elseif (isset($_GET['addlog-bahan'])) {
    $id   = intval($_GET['id']);
    $data = $obj->getBahanById($id);
    $time = $obj->getTime();
?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold mb-4 text-success"><i class="fa fa-plus-circle me-2"></i>Catat Penggunaan Bahan: <?= esc_html($data['Nama_Bahan']); ?></h5>
            <form method="post">
              <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
              <input type="hidden" name="id_bahan" value="<?= intval($data['id']); ?>">
              <input type="hidden" name="status" value="3">
              <input type="hidden" name="user_id" value="<?= get_current_user_id(); ?>">
              
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Nama Bahan</label>
                  <input type="text" class="form-control bg-light" value="<?= esc_attr($data['Nama_Bahan']); ?>" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Stok Tersedia</label>
                  <input type="text" class="form-control bg-light" value="<?= esc_attr($data['Jumlah'] . ' ' . $data['Satuan']); ?>" readonly>
                </div>
              </div>
              
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="Qty" class="form-label small fw-bold">Jumlah Digunakan (Maks: <?= esc_attr($data['Jumlah']); ?>)</label>
                  <input type="number" step="any" class="form-control" id="Qty" name="Qty" min="0" max="<?= esc_attr($data['Jumlah']); ?>" value="1" required>
                </div>
                <div class="col-md-6">
                  <label for="tanggal" class="form-label small fw-bold">Tanggal Penggunaan</label>
                  <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" value="<?= esc_attr($time[0]); ?>" required>
                </div>
              </div>

              <!-- ── PubChem Chemical Safety Panel ── -->
              <div class="mb-4">
                <h6 class="fw-bold mb-2" style="color:#6c757d;font-size:13px;">
                  <i class="fa fa-shield me-2" style="color:#dc3545;"></i>Informasi Keselamatan Bahan (PubChem)
                </h6>
                <div data-pubchem-panel
                     data-pubchem-name="<?= esc_attr($data['Nama_Bahan']); ?>">
                  <!-- Loaded by sl-simlab-pubchem.js -->
                </div>
              </div>
              <!-- ── End PubChem Panel ── -->
              
              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success" name="submit-log-bahan" value="1"><i class="fa fa-check me-1"></i> Simpan Penggunaan</button>
                <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

<?php
  /* ── EDIT ────────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['ubah-bahan'])) {
    $data = $obj->getBahanById(intval($_GET['id']));
?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold mb-4 text-warning"><i class="fa fa-edit me-2"></i>Ubah Data Bahan</h5>
            <form method="post">
              <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
              <input type="hidden" name="id" value="<?= intval($data['id']); ?>">
              
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="nama-bahan" class="form-label">Nama Bahan</label>
                  <input type="text" class="form-control" id="nama-bahan" name="Nama_Bahan" value="<?= esc_attr($data['Nama_Bahan']); ?>" required>
                </div>
                <div class="col-md-6">
                   <label for="merk" class="form-label">Merk</label>
                   <input type="text" class="form-control" id="merk" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-3">
                  <label for="Qty" class="form-label">Jumlah Stok</label>
                  <input type="number" step="any" class="form-control" id="Qty" name="Qty" min="0" value="<?= esc_attr($data['Jumlah']); ?>" required>
                </div>
                <div class="col-md-3">
                  <label for="satuan" class="form-label">Satuan</label>
                  <input type="text" class="form-control" id="satuan" name="Satuan" value="<?= esc_attr($data['Satuan']); ?>" placeholder="Contoh: ml, gr, pcs">
                </div>
                <div class="col-md-6">
                  <label for="serial" class="form-label">Serial / Batch Number</label>
                  <input type="text" class="form-control" id="serial" name="Serial" value="<?= esc_attr($data['Serial']); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="Exp" class="form-label">Tanggal Kedaluwarsa (Exp)</label>
                  <input type="text" class="form-control" id="Exp" name="Exp" value="<?= esc_attr($data['Exp']); ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-md-6">
                  <label for="letak" class="form-label">Letak / Penyimpanan</label>
                  <input type="text" class="form-control" id="letak" name="Letak" value="<?= esc_attr($data['Letak']); ?>">
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" name="ubah-bahan" value="1"><i class="fa fa-save me-1"></i> Simpan Perubahan</button>
                <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
              </div>
            </form>
          </div>
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

    /* --- Handles for POST stays the same as before --- */
    if (isset($_POST['submit-log-bahan']) && check_admin_referer('sl_simlab_bahan_action') && SL_SIMLAB_Auth::can_book()) {
      $obj1   = new SL_SIMLAB_LogbookBahanClass;
      if ($obj1->addLogBahan($_POST) > 0) {
          echo "<script>alert('Berhasil!'); document.location = '?page=".esc_js($obj1->plugin_slug.$obj1->menu_slug)."';</script>";
      } else {
          echo "<script>alert('Gagal!'); history.back();</script>";
      }
    }

    if (isset($_POST['import-bahan']) && check_admin_referer('sl_import_bahan')) {
        global $simlab_export_import;
        $count = $simlab_export_import->importBahan($_FILES['file_csv']);
        if ($count !== false) {
           echo "<script>alert('$count Data Berhasil Diimport'); document.location = '?page=".esc_js($obj->plugin_slug.$obj->menu_slug)."';</script>";
        } else {
           echo "<script>alert('Gagal!'); history.back();</script>";
        }
    }

    if (isset($_POST['submit-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
        if ($obj->tambahBahan($_POST)) {
            echo "<script>document.location = '?page=".esc_js($obj->plugin_slug.$obj->menu_slug)."';</script>";
        } else {
            echo "<script>alert('Gagal menambah data! Periksa apakah data sudah benar.'); history.back();</script>";
        }
    }

    if (isset($_POST['ubah-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
        if ($obj->ubahBahan($_POST) > 0) {
           echo "<script>document.location = '?page=".esc_js($obj->plugin_slug.$obj->menu_slug)."';</script>";
        } else {
           echo "<script>alert('Gagal!'); history.back();</script>";
        }
    }

    $data = $obj->getBahan();
?>
    <div class="row">
      <div class="col-lg-12">

        <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
          <div class="d-flex flex-wrap gap-2 mb-4 justify-content-between align-items-center">
            <div class="d-flex gap-2">
              <button id="tambah-bahan-button" class="btn btn-primary shadow-sm" onclick="return tambahBahan()"><i class="fa fa-plus me-1"></i> Tambah Bahan</button>
              <button class="btn btn-info text-white shadow-sm" onclick="return toggleImport()"><i class="fa fa-upload me-1"></i> Import CSV</button>
              <a href="<?= wp_nonce_url(admin_url('admin.php?page=' . $obj->plugin_slug . $obj->menu_slug . '&action=export-bahan'), 'sl_export_bahan'); ?>" class="btn btn-success shadow-sm"><i class="fa fa-download me-1"></i> Export CSV</a>
            </div>
          </div>

          <!-- Forms -->
          <div class="import-bahan card mb-4 border-info shadow-sm" id="import-bahan" style="display:none; border-left: 5px solid #0dcaf0;">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Import Data Bahan (.csv)</h6>
              <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
                <?php wp_nonce_field('sl_import_bahan', '_wpnonce'); ?>
                <div class="col-auto">
                  <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                </div>
                <div class="col-auto">
                  <button type="submit" name="import-bahan" class="btn btn-info text-white">Upload & Import</button>
                  <button type="button" class="btn btn-link text-muted" onclick="toggleImport()">Batal</button>
                </div>
              </form>
            </div>
          </div>

          <div class="tambah-bahan card mb-4 border-primary shadow-sm" id="tambah-bahan" style="display:none; border-left: 5px solid #0d6efd;">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Formulir Tambah Bahan Baru</h6>
              <form method="post" class="row g-3">
                <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Nama Bahan</label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="Nama_Bahan" id="new-nama-bahan" required placeholder="Contoh: Ethanol, Sulfuric Acid">
                    <button class="btn btn-outline-secondary" type="button" onclick="lookupPubChem()"><i class="fa fa-search"></i> Cek PubChem</button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Merk</label>
                  <input type="text" class="form-control" name="Merk" placeholder="Merk Pabrikan">
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Letak / Penyimpanan</label>
                  <input type="text" class="form-control" name="Letak" placeholder="Lemari A, Rak 2">
                </div>

                <div class="col-md-3">
                  <label class="form-label small fw-bold">Jumlah</label>
                  <input type="number" step="any" class="form-control" name="Jumlah" min="0" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Satuan</label>
                  <input type="text" class="form-control" name="Satuan" required placeholder="ml, g, box">
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Serial / Batch</label>
                  <input type="text" class="form-control" name="Serial" placeholder="Batch No.">
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Tgl Exp</label>
                  <input type="text" class="form-control" name="Exp" placeholder="YYYY-MM-DD">
                </div>

                <!-- ── PubChem Preview Section (Hidden by default) ── -->
                <div class="col-12 mt-3" id="pubchem-preview-container" style="display:none;">
                  <div class="p-3 border rounded bg-light">
                    <div id="pubchem-add-panel" data-pubchem-panel-manual>
                       <!-- Manually triggered lookup results here -->
                    </div>
                  </div>
                </div>

                <div class="col-md-3 d-flex align-items-end ms-auto">
                  <button type="submit" class="btn btn-primary w-100" name="submit-bahan" value="1"><i class="fa fa-save me-1"></i> Simpan Data</button>
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
                <th>Nama Bahan</th>
                <th>Merk</th>
                <th width="150">Stok Akhir</th>
                <th>Letak</th>
                <th width="200" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; ?>
              <?php if (empty($data)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data bahan.</td></tr>
              <?php endif; ?>
              <?php foreach ($data as $alat) : ?>
                <tr>
                  <td><?= $i; ?></td>
                  <td class="fw-bold"><?= esc_html($alat['Nama_Bahan']); ?></td>
                  <td><?= esc_html($alat['Merk']); ?></td>
                  <td><span class="badge bg-light text-dark border"><?= esc_html($alat['Jumlah'] . ' ' . $alat['Satuan']); ?></span></td>
                  <td class="small"><?= esc_html($alat['Letak']); ?></td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail-bahan&id=<?= intval($alat['id']); ?>"
                         class="btn btn-sm btn-outline-primary" title="Detail"><i class="fa fa-eye"></i> Detail</a>
                         
                      <?php if (SL_SIMLAB_Auth::can_book()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&addlog-bahan&id=<?= intval($alat['id']); ?>"
                          class="btn btn-sm btn-success" title="Pakai Bahan"><i class="fa fa-flask"></i> Pakai</a>
                      <?php } ?>
                      
                      <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&ubah-bahan&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-pencil"></i> Edit</a>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&hapus-bahan&id=<?= intval($alat['id']); ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Hapus bahan ini?');" title="Hapus"><i class="fa fa-trash"></i> Delete</a>
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
      function tambahBahan() {
        var tb = document.getElementById('tambah-bahan');
        var ib = document.getElementById('import-bahan');
        ib.style.display = 'none';
        tb.style.display = (tb.style.display === 'block') ? 'none' : 'block';
        return false;
      }
      function toggleImport() {
        var ib = document.getElementById('import-bahan');
        var tb = document.getElementById('tambah-bahan');
        tb.style.display = 'none';
        ib.style.display = (ib.style.display === 'block') ? 'none' : 'block';
        return false;
      }
      function lookupPubChem() {
        var name = document.getElementById('new-nama-bahan').value;
        if (!name) {
          alert('Masukkan nama bahan terlebih dahulu.');
          return;
        }
        document.getElementById('pubchem-preview-container').style.display = 'block';
        var panel = document.getElementById('pubchem-add-panel');
        panel.setAttribute('data-pubchem-name', name);
        // Force re-run the PubChem init if available
        if (window.triggerPubChemLookup) {
          window.triggerPubChemLookup(panel);
        } else {
          panel.innerHTML = '<p class="text-muted small">PubChem script not ready.</p>';
        }
      }
    </script>

<?php
  } // end else (list)
  
  SL_SimlabPlugin::admin_footer();
} // end is_user_logged_in
?>