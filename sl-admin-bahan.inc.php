<?php
if (!defined('ABSPATH')) {
  exit;
}
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
    <a href="<?php echo esc_url(wp_registration_url($current_url)); ?>"
      class="btn btn-success ms-1"><?php _e('Register'); ?></a>
  </div>
  <?php
} else {
  $obj = new SL_SIMLAB_BahanClass;
  $nonce = wp_create_nonce('sl_simlab_bahan_action');

  /* ── HANDLE POST ACTIONS ─────────────────────────────────────────────── */

  // 1. Handle Submit Booking Log
  if (isset($_POST['submit-log-bahan']) || (isset($_POST['action_type']) && $_POST['action_type'] === 'submit-log-bahan') && check_admin_referer('sl_simlab_bahan_action') && SL_SIMLAB_Auth::can_book()) {
    $obj1 = new SL_SIMLAB_LogbookBahanClass;
    $addLog = $obj1->addLogBahan($_POST);
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

  // 2. Handle Import Bahan
  if (isset($_POST['import-bahan']) && check_admin_referer('sl_import_bahan')) {
    global $simlab_export_import;
    $count = $simlab_export_import->importBahan($_FILES['file_csv']);
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

  // 3. Handle Add Bahan
  if (isset($_POST['submit-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
    if ($obj->tambahBahan($_POST)) {
      echo "<script>alert('Katalog Bahan berhasil didaftarkan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
    } else {
      echo "<script>alert('Gagal menambah data!'); history.back();</script>";
    }
  }

  // 4. Handle Edit Bahan
  if (isset($_POST['ubah-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
    if ($obj->ubahBahan($_POST) > 0) {
      echo "<script>alert('Perubahan Katalog Bahan berhasil disimpan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
    } else {
      echo "<script>alert('Gagal disimpan (atau tidak ada yg berubah)!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
    }
  }

  /* ── KEMASAN ACTION HANDLERS ─────────────────────────────────────────── */
  if (isset($_POST['tambah-kemasan']) && check_admin_referer('sl_kemasan_action')) {
    if ($obj->tambahKemasan($_POST)) {
      echo "<script>alert('Kemasan/Botol baru berhasil ditambahkan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "&detail-bahan&id=" . intval($_POST['id_bahan']) . "';</script>";
    } else {
      echo "<script>alert('Gagal menambah kemasan!'); history.back();</script>";
    }
  }

  if (isset($_GET['hapus-kemasan'])) {
    check_admin_referer('sl_hapus_kemasan_' . intval($_GET['id_kemasan']));
    if (!SL_SIMLAB_Auth::is_admin()) {
      wp_die('No permission');
    }
    $id_kemasan = intval($_GET['id_kemasan']);
    $id_bahan = intval($_GET['id_bahan']);
    if ($obj->hapusKemasan($id_kemasan)) {
      echo "<script>alert('Kemasan dihapus'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "&detail-bahan&id=" . $id_bahan . "';</script>";
    }
  }

  if (isset($_POST['restock-kemasan']) && check_admin_referer('sl_restock_kemasan_action')) {
    if (!SL_SIMLAB_Auth::is_admin()) {
      wp_die('No permission');
    }
    $id_kemasan = intval($_POST['id_kemasan']);
    $tambah = floatval(str_replace(',', '.', $_POST['tambah_stok']));
    $id_bahan = intval($_POST['id_bahan']);
    if ($obj->restockKemasan($id_kemasan, $tambah)) {
      echo "<script>alert('Stok berhasil diupdate!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "&detail-bahan&id=" . $id_bahan . "';</script>";
    } else {
      echo "<script>alert('Gagal update stok!'); history.back();</script>";
    }
  }

  if (isset($_POST['edit-kemasan']) && check_admin_referer('sl_edit_kemasan_action')) {
    if (!SL_SIMLAB_Auth::is_admin()) {
      wp_die('No permission');
    }
    $id_kemasan = intval($_POST['id_kemasan']);
    $id_bahan = intval($_POST['id_bahan']);
    if ($obj->ubahKemasan($_POST) !== false) {
      echo "<script>alert('Kemasan berhasil diupdate!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "&detail-bahan&id=" . $id_bahan . "';</script>";
    } else {
      echo "<script>alert('Gagal update kemasan!'); history.back();</script>";
    }
  }


  SL_SimlabPlugin::admin_header('Manajemen Bahan', 'fa-flask');


  /* ── DETAIL BAHAN & KEMASAN ─────────────────────────────────────────── */
  if (isset($_GET['detail-bahan'])) {
    $id = intval($_GET['id']);
    $data = $obj->getBahanById($id);
    $kemasans = $obj->getKemasanByBahan($id);
    if (!$data) {
      echo "<script>alert('Data tidak ditemukan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
      return;
    }

    // Build Booking URL
    $links = get_option('sl_simlab_links', []);
    $booking_url = !empty($links['daftar-bahan']) ? add_query_arg(array('addlog-bahan' => '', 'id' => $data['id']), $links['daftar-bahan']) : admin_url('admin.php?page=simlab-daftar-bahan&addlog-bahan&id=' . $data['id']);
    ?>
    <div class="row">
      <!-- Left Column: Catalog details, Kemasan table, PubChem panel -->
      <div class="col-lg-8 mb-4">
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title fw-bold text-primary m-0"><i class="fa fa-info-circle me-2"></i>Katalog Bahan</h5>
              <div>
                <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary btn-sm"><i
                    class="fa fa-arrow-left me-1"></i> Kembali</a>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <table class="table table-sm border-0">
                  <tr>
                    <th width="40%">Nama Bahan</th>
                    <td>: <?= esc_html($data['Nama_Bahan']); ?></td>
                  </tr>
                  <tr>
                    <th>Alias/Formula</th>
                    <td>: <?= esc_html($data['Alias']); ?></td>
                  </tr>
                  <tr>
                    <th>Kategori</th>
                    <td>: <span class="badge bg-secondary"><?= esc_html($data['Kategori']); ?></span></td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6 border-start">
                <table class="table table-sm">
                  <tr>
                    <th width="40%">Merk</th>
                    <td>: <?= esc_html($data['Merk']); ?></td>
                  </tr>
                  <tr>
                    <th>Satuan Dasar</th>
                    <td>: <?= esc_html($data['Satuan_Dasar']); ?></td>
                  </tr>
                  <tr>
                    <th>Total Tersedia</th>
                    <td>: <span
                        class="badge bg-success"><?= esc_html($data['TotalJumlah'] . ' ' . $data['Satuan_Dasar']); ?></span>
                    </td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- PubChem Feature -->
            <hr class="my-3">
            <h6 class="fw-bold mb-3" style="color:#6c757d;">
              <i class="fa fa-flask me-2" style="color:#0d6efd;"></i>Informasi Kimia (PubChem)
            </h6>
            <div data-pubchem-panel data-pubchem-id="<?= intval($data['id']); ?>" data-pubchem-name="<?= esc_attr($data['Nama_Bahan']); ?>"></div>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold m-0"><i class="fa fa-box me-2"></i>Daftar Kemasan / Botol</h5>
              <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                <button class="btn btn-sm btn-primary"
                  onclick="var f=document.getElementById('form-tambah-kemasan'); f.style.display = (f.style.display === 'none') ? 'block' : 'none'; return false;">
                  <i class="fa fa-plus"></i> Tambah Kemasan
                </button>
              <?php } ?>
            </div>

            <!-- ADD KEMASAN FORM -->
            <div id="form-tambah-kemasan" class="bg-light p-3 border rounded mb-3" style="display:none;">
              <h6 class="fw-bold mb-3">Register Botol/Kemasan Baru</h6>
              <form method="post" class="row border-bottom pb-3 mb-2">
                <?php wp_nonce_field('sl_kemasan_action', '_wpnonce'); ?>
                <input type="hidden" name="id_bahan" value="<?= intval($data['id']); ?>">
                <div class="col-md-3">
                  <label class="form-label small">Label Kemasan (Botol 1, Lot A)</label>
                  <input type="text" name="label_kemasan" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Isi / Kapasitas Awal</label>
                  <input type="number" step="any" name="kapasitas_awal" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Satuan Isi</label>
                  <input type="text" name="satuan" class="form-control form-control-sm"
                    value="<?= esc_attr($data['Satuan_Dasar']) ?>" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Tgl Kadaluwarsa</label>
                  <input type="date" name="exp_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                  <label class="form-label small">Lokasi/Letak Botol</label>
                  <input type="text" name="letak" class="form-control form-control-sm">
                </div>
                <div class="col-md-9 mt-2">
                  <label class="form-label small">Catatan Kondisi (Opsional)</label>
                  <input type="text" name="catatan_kondisi" class="form-control form-control-sm"
                    placeholder="Missal: Exp dekat, Segel utuh...">
                </div>
                <div class="col-md-3 mt-2 d-flex align-items-end">
                  <button type="submit" name="tambah-kemasan" value="1" class="btn btn-success btn-sm w-100"><i
                      class="fa fa-save"></i> Simpan Botol</button>
                </div>
              </form>
            </div>

            <!-- LIST KEMASAN -->
            <table class="table table-bordered table-sm align-middle mt-3">
              <thead class="table-light">
                <tr>
                  <th>Label Kemasan</th>
                  <th>Stok Tersedia</th>
                  <th>Kadaluwarsa</th>
                  <th>Letak</th>
                  <th>Status Konidisi</th>
                  <?php if (SL_SIMLAB_Auth::is_admin())
                    echo '<th width="100">Aksi</th>'; ?>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($kemasans)): ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada kemasan yang diregistrasikan.</td>
                  </tr>
                  <?php else:
                  foreach ($kemasans as $kem): ?>
                    <tr>
                      <td><strong><?= esc_html($kem['label_kemasan']) ?></strong></td>
                      <td>
                        <?php if ($kem['is_empty'] == 1 || $kem['jumlah_tersedia'] <= 0): ?>
                          <span class="badge bg-danger">Habis</span>
                        <?php else: ?>
                          <span
                            class="badge bg-info text-dark"><?= esc_html($kem['jumlah_tersedia'] . ' ' . $kem['satuan']) ?></span>
                        <?php endif; ?>
                        <div class="small text-muted">Kapasitas awal:
                          <?= esc_html($kem['kapasitas_awal'] . ' ' . $kem['satuan']) ?>
                        </div>
                      </td>
                      <td><?= esc_html($kem['exp_date']) ?></td>
                      <td><?= esc_html($kem['letak']) ?></td>
                      <td><small><?= esc_html($kem['catatan_kondisi']) ?></small></td>
                      <?php if (SL_SIMLAB_Auth::is_admin()): ?>
                        <td>
                          <button class="btn btn-warning btn-sm" title="Restock"
                            onclick="toggleRestockForm(<?= intval($kem['id']) ?>)"><i class="fa fa-plus"></i> Restock</button>
                          <button class="btn btn-info text-white btn-sm" title="Edit Kemasan"
                            onclick="toggleEditForm(<?= intval($kem['id']) ?>)"><i class="fa fa-pencil"></i> Edit</button>
                          <a href="<?= wp_nonce_url('?page=' . esc_attr($obj->plugin_slug . $obj->menu_slug) . '&hapus-kemasan&id_kemasan=' . intval($kem['id']) . '&id_bahan=' . intval($data['id']), 'sl_hapus_kemasan_' . intval($kem['id'])); ?>"
                            onclick="return confirm('Yakin ingin menghapus kemasan ini? Riwayat logbook yang terkait kemasan ini akan bermasalah jika ada.')"
                            class="btn btn-danger btn-sm" title="Hapus Kemasan"><i class="fa fa-trash"></i> Delete</a>
                        </td>
                      <?php endif; ?>
                    </tr>
                    <?php if (SL_SIMLAB_Auth::is_admin()): ?>
                      <tr id="restock-form-<?= intval($kem['id']) ?>" style="display:none;">
                        <td colspan="6">
                          <form method="post" class="row g-2 align-items-center">
                            <?php wp_nonce_field('sl_restock_kemasan_action', '_wpnonce'); ?>
                            <input type="hidden" name="id_kemasan" value="<?= intval($kem['id']) ?>">
                            <input type="hidden" name="id_bahan" value="<?= intval($data['id']) ?>">
                            <div class="col-md-4">
                              <label class="form-label small">Tambah Stok (<?= esc_html($kem['satuan']) ?>)</label>
                              <input type="number" step="any" name="tambah_stok" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                              <button type="submit" name="restock-kemasan" class="btn btn-success btn-sm me-2"><i class="fa fa-save"></i> Update</button>
                              <button type="button" class="btn btn-secondary btn-sm" onclick="toggleRestockForm(<?= intval($kem['id']) ?>)">Batal</button>
                            </div>
                          </form>
                        </td>
                      </tr>
                      <tr id="edit-form-<?= intval($kem['id']) ?>" style="display:none;">
                        <td colspan="6">
                          <form method="post" class="row g-2">
                            <?php wp_nonce_field('sl_edit_kemasan_action', '_wpnonce'); ?>
                            <input type="hidden" name="id_kemasan" value="<?= intval($kem['id']) ?>">
                            <input type="hidden" name="id_bahan" value="<?= intval($data['id']) ?>">
                            <div class="col-md-3">
                              <label class="form-label small fw-bold">Label Kemasan</label>
                              <input type="text" name="label_kemasan" class="form-control form-control-sm" value="<?= esc_attr($kem['label_kemasan']) ?>" required>
                            </div>
                            <div class="col-md-2">
                              <label class="form-label small fw-bold">Kapasitas Awal</label>
                              <input type="number" step="any" name="kapasitas_awal" class="form-control form-control-sm" value="<?= esc_attr($kem['kapasitas_awal']) ?>" required>
                            </div>
                            <div class="col-md-2">
                              <label class="form-label small fw-bold">Stok Tersedia</label>
                              <input type="number" step="any" name="jumlah_tersedia" class="form-control form-control-sm" value="<?= esc_attr($kem['jumlah_tersedia']) ?>" required>
                            </div>
                            <div class="col-md-1">
                              <label class="form-label small fw-bold">Satuan</label>
                              <input type="text" name="satuan" class="form-control form-control-sm" value="<?= esc_attr($kem['satuan']) ?>" required>
                            </div>
                            <div class="col-md-2">
                              <label class="form-label small fw-bold">Kadaluwarsa</label>
                              <input type="date" name="exp_date" class="form-control form-control-sm" value="<?= esc_attr($kem['exp_date']) ?>">
                            </div>
                            <div class="col-md-2">
                              <label class="form-label small fw-bold">Letak</label>
                              <input type="text" name="letak" class="form-control form-control-sm" value="<?= esc_attr($kem['letak']) ?>">
                            </div>
                            <div class="col-md-9 mt-2">
                              <label class="form-label small fw-bold">Catatan Kondisi</label>
                              <input type="text" name="catatan_kondisi" class="form-control form-control-sm" value="<?= esc_attr($kem['catatan_kondisi']) ?>">
                            </div>
                            <div class="col-md-3 mt-2 d-flex align-items-end justify-content-end">
                              <button type="submit" name="edit-kemasan" class="btn btn-primary btn-sm me-2"><i class="fa fa-save"></i> Simpan</button>
                              <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= intval($kem['id']) ?>)">Batal</button>
                            </div>
                          </form>
                        </td>
                      </tr>
                    <?php endif; ?>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column: Image and QR Code -->
      <div class="col-lg-4 mb-4">
        <!-- Image Card -->
        <?php if (!empty($data['gambar'])): ?>
          <div class="card shadow-sm mb-4 border-0 text-center">
            <div class="card-body">
              <h5 class="card-title fw-bold text-primary mb-3"><i class="fa fa-image me-2"></i>Gambar Bahan</h5>
              <img src="<?= esc_url($data['gambar']); ?>" alt="<?= esc_attr($data['Nama_Bahan']); ?>" style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 8px; border: 1px solid #dee2e6; padding: 4px; background: #fff;">
            </div>
          </div>
        <?php endif; ?>

        <!-- QR Code Card -->
        <div class="card shadow-sm border-0 text-center">
          <div class="card-body d-flex flex-column justify-content-between" style="min-height: 320px;">
            <div>
              <h5 class="card-title fw-bold text-success mb-3"><i class="fa fa-qrcode me-2"></i>QR Code Penggunaan</h5>
              <p class="text-muted small mb-4">Pindai kode QR ini menggunakan HP Anda untuk membuka halaman pencatatan pemakaian bahan ini.</p>
            </div>
            <div class="d-flex justify-content-center mb-4">
              <div id="booking-qrcode" data-booking-url="<?= esc_url($booking_url); ?>" data-item-name="<?= esc_attr($data['Nama_Bahan']); ?>" style="padding: 10px; background: #fff; border: 1px solid #dee2e6; border-radius: 8px;"></div>
            </div>
            <div>
              <button id="btn-download-qrcode" class="btn btn-sm btn-outline-success w-100"><i class="fa fa-download me-1"></i> Unduh QR Code</button>
            </div>
          </div>
        </div>
      </div>
    </div>


    <script type="text/javascript">
      function toggleRestockForm(id) {
        var form = document.getElementById('restock-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
          form.style.display = 'table-row';
        } else {
          form.style.display = 'none';
        }
      }
      function toggleEditForm(id) {
        var form = document.getElementById('edit-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
          form.style.display = 'table-row';
        } else {
          form.style.display = 'none';
        }
      }
    </script>

  <?php

    /* ── EDIT BAHAN ──────────────────────────────────────────────────────── */
  } elseif (isset($_GET['ubah-bahan'])) {
    $data = $obj->getBahanById(intval($_GET['id']));
  ?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-bold mb-4 text-warning"><i class="fa fa-edit me-2"></i>Ubah Katalog Bahan Utama</h5>
            <form method="post">
              <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
              <input type="hidden" name="id" value="<?= intval($data['id']); ?>">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nama Bahan</label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="Nama_Bahan" id="edit-nama-bahan" value="<?= esc_attr($data['Nama_Bahan']); ?>"
                      required>
                    <button class="btn btn-outline-secondary" type="button" onclick="lookupEditPubChem()"><i
                        class="fa fa-search"></i> Cek PubChem</button>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Alias / Rumus</label>
                  <input type="text" class="form-control" name="Alias" value="<?= esc_attr($data['Alias']); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">GHS Code (Pemisah Koma)</label>
                  <input type="text" class="form-control" name="ghs_code" id="edit-ghs-code" value="<?= esc_attr(!empty($data['ghs_code']) ? implode(', ', maybe_unserialize($data['ghs_code'])) : ''); ?>" placeholder="Contoh: GHS02, GHS07">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Signal Word</label>
                  <input type="text" class="form-control" name="signal_word" id="edit-signal-word" value="<?= esc_attr($data['signal_word'] ?? ''); ?>" placeholder="Contoh: Danger, Warning">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Hazard Statement (Satu per baris)</label>
                  <textarea class="form-control" name="hazard_statement" id="edit-hazard-statement" rows="2" placeholder="Satu pernyataan per baris..."><?= esc_textarea(!empty($data['hazard_statement']) ? implode("\n", maybe_unserialize($data['hazard_statement'])) : ''); ?></textarea>
                </div>
              </div>

              <div class="row mb-3" id="pubchem-edit-preview-container" style="display:none;">
                <div class="col-12">
                  <div class="p-3 border rounded bg-light">
                    <div id="pubchem-edit-panel" data-pubchem-panel-manual></div>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">Merk</label>
                  <input type="text" class="form-control" name="Merk" value="<?= esc_attr($data['Merk']); ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Kategori</label>
                  <input type="text" class="form-control" name="Kategori" value="<?= esc_attr($data['Kategori']); ?>"
                    placeholder="Reagen, BHP, dll">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Satuan Dasar Katalog</label>
                  <input type="text" class="form-control" name="Satuan_Dasar"
                    value="<?= esc_attr($data['Satuan_Dasar']); ?>">
                </div>
              </div>

              <div class="mb-3">
                <label for="edit-gambar-url" class="form-label">Gambar Bahan</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="edit-gambar-url" name="gambar" value="<?= esc_attr($data['gambar'] ?? ''); ?>" placeholder="URL Gambar atau pilih dari media library">
                  <button class="btn btn-outline-secondary" type="button" id="btn-edit-pilih-gambar"><i class="fa fa-image"></i> Pilih Gambar</button>
                </div>
                <div class="mt-2" id="edit-gambar-preview-container" style="<?= empty($data['gambar']) ? 'display:none;' : '' ?>">
                  <img id="edit-gambar-preview" src="<?= esc_url($data['gambar'] ?? ''); ?>" style="max-height: 150px; border: 1px solid #dee2e6; border-radius: 8px; padding: 4px; background: #fff;" />
                </div>
              </div>

              <div class="alert alert-info py-2 small">Untuk merubah data stok spesifik dan expired date, silakan kembali
                lalu masuk melalui tombol "Detail -> Kemasan".</div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" name="ubah-bahan" value="1"><i class="fa fa-save me-1"></i>
                  Update Katalog Utama</button>
                <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  <?php

    /* ── BOOKING/USAGE ─────────────────────────────────────────────────── */
  } elseif (isset($_GET['addlog-bahan'])) {
    $id = intval($_GET['id']);
    $data = $obj->getBahanById($id);
    $kemasans = $obj->getKemasanByBahan($id);
    // Filter out empty bottles for usage
    $ready_kemasans = array_filter($kemasans, function ($k) {
      return $k['is_empty'] == 0 && $k['jumlah_tersedia'] > 0;
    });

    $time = $obj->getTime();
  ?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title fw-bold mb-4 text-success"><i class="fa fa-plus-circle me-2"></i>Pakai Bahan:
              <?= esc_html($data['Nama_Bahan']); ?>
            </h5>

            <?php if (empty($ready_kemasans)): ?>
              <div class="alert alert-danger">Stok keseluruhan untuk bahan ini sedang habis atau kemasan kosong! Silakan
                tambahkan stok kemasan baru di halaman detail.</div>
              <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary"><i
                  class="fa fa-arrow-left"></i> Kembali</a>
            <?php else: ?>
              <div class="row">
                <?php if (!empty($data['gambar'])): ?>
                  <div class="col-md-4 text-center mb-4">
                    <img src="<?= esc_url($data['gambar']); ?>" alt="<?= esc_attr($data['Nama_Bahan']); ?>" style="max-width: 100%; max-height: 250px; object-fit: contain; border-radius: 8px; border: 1px solid #dee2e6; padding: 4px; background: #fff;">
                  </div>
                <?php endif; ?>
                <div class="<?= !empty($data['gambar']) ? 'col-md-8' : 'col-md-12' ?>">
                  <form method="post">
                    <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>

                    <div class="row mb-3">
                      <div class="col-md-12">
                        <label class="form-label small fw-bold">Pilih Kemasan Botol yang Akan Diambil</label>
                        <select name="id_kemasan" class="form-select border-primary" required>
                          <option value="">-- Pilih Spesifik Botol --</option>
                          <?php foreach ($ready_kemasans as $k): ?>
                            <option value="<?= esc_attr($k['id']) ?>">Botol: <?= esc_html($k['label_kemasan']) ?> (Tersedia:
                              <?= esc_html($k['jumlah_tersedia'] . ' ' . $k['satuan']) ?> - Di <?= esc_html($k['letak']) ?>)
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label for="Qty" class="form-label small fw-bold">Jumlah Yang Diambil</label>
                        <div class="input-group">
                          <input type="number" step="any" class="form-control" id="Qty" name="Qty" min="0" value="1" required>
                          <span class="input-group-text" id="Qty-unit"><?= esc_html($k['satuan']); ?></span>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <label for="tanggal" class="form-label small fw-bold">Waktu</label>
                        <input type="datetime-local" class="form-control" id="tanggal" name="tanggal"
                          value="<?= esc_attr($time[0]); ?>" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">Keperluan / Tujuan</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Riset/Praktikum...">
                      </div>
                    </div>

                    <!-- PubChem Panel -->
                    <div class="mb-4">
                      <h6 class="fw-bold mb-2" style="color:#6c757d;font-size:13px;">
                        <i class="fa fa-shield me-2" style="color:#dc3545;"></i>Informasi Keselamatan Bahan (PubChem)
                      </h6>
                      <div data-pubchem-panel data-pubchem-id="<?= intval($data['id']); ?>" data-pubchem-name="<?= esc_attr($data['Nama_Bahan']); ?>"></div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                      <button type="submit" class="btn btn-success" name="submit-log-bahan" value="1"><i
                          class="fa fa-check me-1"></i> Simpan Penggunaan</button>
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
                    </div>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  <?php
    /* ── DELETE ──────────────────────────────────────────────────────────── */
  } elseif (isset($_GET['hapus-bahan'])) {
    check_admin_referer('sl_hapus_bahan_' . intval($_GET['id']));
    if (!SL_SIMLAB_Auth::is_admin()) {
      wp_die(__('You do not have permission to perform this action.'));
    }
    $hapus = $obj->hapusBahan(intval($_GET['id']));
    if ($hapus > 0) {
      echo "<script>alert('Data Berhasil Dihapus beserta kemasannya'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
    } else {
      echo "<script>alert('Data Gagal Dihapus'); history.back();</script>";
    }

    /* ── LIST (default) ──────────────────────────────────────────────────── */
  } else {
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $filter_kategori = isset($_GET['filter_kategori']) ? sanitize_text_field($_GET['filter_kategori']) : '';
    $filter_stock = isset($_GET['filter_stock']) ? sanitize_text_field($_GET['filter_stock']) : '';
    $filter = ['kategori' => $filter_kategori, 'stock_status' => $filter_stock];
    $categories = $obj->getDistinctCategories();

    $limit = 10;
    $current_page = isset($_GET['sl_paged']) ? max(1, intval($_GET['sl_paged'])) : 1;
    $offset = ($current_page - 1) * $limit;
    $total_items = $obj->getBahanCount($search, $filter);
    $data = $obj->getBahan($limit, $offset, $search, $filter);
    $reset_url = esc_url(remove_query_arg(['search', 'filter_kategori', 'filter_stock', 'sl_paged']));
  ?>
    <div class="row">
      <div class="col-lg-12">
        
        <form method="get" class="row g-2 mb-4 align-items-center bg-white p-3 rounded border shadow-sm mx-0">
          <?php
          foreach ($_GET as $key => $val) {
            if (!in_array($key, ['search', 'filter_kategori', 'filter_stock', 'sl_paged'])) {
              echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '">';
            }
          }
          ?>
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted"></i></span>
              <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama bahan, alias, merk..." value="<?= esc_attr($search); ?>">
            </div>
          </div>
          <div class="col-md-3">
            <select name="filter_kategori" class="form-select">
              <option value="">-- Semua Kategori --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= esc_attr($cat); ?>" <?= $filter_kategori === $cat ? 'selected' : ''; ?>><?= esc_html($cat); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <select name="filter_stock" class="form-select">
              <option value="">-- Semua Stok --</option>
              <option value="in_stock" <?= $filter_stock === 'in_stock' ? 'selected' : ''; ?>>Tersedia</option>
              <option value="out_of_stock" <?= $filter_stock === 'out_of_stock' ? 'selected' : ''; ?>>Habis</option>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa fa-filter"></i> Cari</button>
            <?php if (!empty($search) || !empty($filter_kategori) || !empty($filter_stock)): ?>
              <a href="<?= $reset_url; ?>" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset"><i class="fa fa-refresh"></i></a>
            <?php endif; ?>
          </div>
        </form>

        <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
          <div class="d-flex flex-wrap gap-2 mb-4 justify-content-between align-items-center">
            <div class="d-flex gap-2">
              <button id="btn-tambah-bahan" class="btn btn-primary shadow-sm"
                onclick="var t=document.getElementById('tambah-bahan'),i=document.getElementById('import-bahan'); if(t.style.display=='none'){t.style.display='block';i.style.display='none';}else{t.style.display='none';} return false;"><i
                  class="fa fa-plus me-1"></i> Katalog Bahan Baru</button>
              <button class="btn btn-info text-white shadow-sm"
                onclick="var t=document.getElementById('tambah-bahan'),i=document.getElementById('import-bahan'); if(i.style.display=='none'){i.style.display='block';t.style.display='none';}else{i.style.display='none';} return false;"><i
                  class="fa fa-upload me-1"></i> Import Bahan (CSV)</button>
              <a href="<?= wp_nonce_url(admin_url('admin.php?page=' . $obj->plugin_slug . $obj->menu_slug . '&action=export-bahan'), 'sl_export_bahan'); ?>"
                class="btn btn-success shadow-sm"><i class="fa fa-download me-1"></i> Export 2-Level CSV</a>
            </div>
          </div>

          <div class="import-bahan card mb-4 border-info shadow-sm" id="import-bahan"
            style="display:none; border-left: 5px solid #0dcaf0;">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Import Data Logistik (.csv)</h6>
              <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
                <?php wp_nonce_field('sl_import_bahan', '_wpnonce'); ?>
                <div class="col-auto">
                  <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                </div>
                <div class="col-auto">
                  <button type="submit" name="import-bahan" class="btn btn-info text-white">Upload & Petakan</button>
                  <button type="button" class="btn btn-link text-muted"
                    onclick="document.getElementById('import-bahan').style.display='none';">Batal</button>
                </div>
              </form>
            </div>
          </div>

          <div class="tambah-bahan card mb-4 border-primary shadow-sm" id="tambah-bahan"
            style="display:none; border-left: 5px solid #0d6efd;">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Formulir Pendaftaran Katalog Bahan</h6>
              <form method="post" class="row g-3">
                <?php wp_nonce_field('sl_simlab_bahan_action', '_wpnonce'); ?>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Nama Bahan</label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="Nama_Bahan" id="new-nama-bahan" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="lookupPubChem()"><i
                        class="fa fa-search"></i> Cek PubChem</button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Alias (C6H12O6 dsb)</label>
                  <input type="text" class="form-control" name="Alias">
                </div>
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Kategori</label>
                  <input type="text" class="form-control" name="Kategori" placeholder="Reagen / BHP">
                </div>

                <div class="col-md-4">
                  <label class="form-label small fw-bold">GHS Code (Pemisah Koma)</label>
                  <input type="text" class="form-control" name="ghs_code" id="new-ghs-code" placeholder="Contoh: GHS02, GHS07">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Signal Word</label>
                  <input type="text" class="form-control" name="signal_word" id="new-signal-word" placeholder="Contoh: Danger, Warning">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Hazard Statement (Satu per baris)</label>
                  <textarea class="form-control" name="hazard_statement" id="new-hazard-statement" rows="2" placeholder="Satu pernyataan per baris..."></textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label small fw-bold">Merk Umum</label>
                  <input type="text" class="form-control" name="Merk">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Satuan Dasar Penghitungan</label>
                  <input type="text" class="form-control" name="Satuan_Dasar" required placeholder="Gram, ml, pcs">
                </div>
                <div class="col-md-8">
                  <label class="form-label small fw-bold">Gambar Bahan</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="add-gambar-url" name="gambar" placeholder="URL Gambar atau klik Pilih Gambar">
                    <button class="btn btn-outline-secondary" type="button" id="btn-add-pilih-gambar"><i class="fa fa-image"></i> Pilih Gambar</button>
                  </div>
                </div>

                <div class="col-12 mt-3" id="pubchem-preview-container" style="display:none;">
                  <div class="p-3 border rounded bg-light">
                    <div id="pubchem-add-panel" data-pubchem-panel-manual></div>
                  </div>
                </div>

                <div class="col-md-4 d-flex align-items-end ms-auto">
                  <button type="submit" class="btn btn-primary w-100" name="submit-bahan" value="1"><i
                      class="fa fa-save me-1"></i> Buat Induk Katalog</button>
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
                <th>Nama Bahan / Katalog</th>
                <th>Kategori</th>
                <th>Merk</th>
                <th width="150">Total Tersedia</th>
                <th width="240" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = $offset + 1; ?>
              <?php if (empty($data)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">Belum ada data bahan.</td>
                </tr>
              <?php endif; ?>
              <?php foreach ($data as $alat): ?>
                <tr>
                  <td><?= $i; ?></td>
                  <td class="fw-bold"><?= esc_html($alat['Nama_Bahan']); ?><br><small
                      class="text-muted"><?= esc_html($alat['Alias']); ?></small></td>
                  <td><span class="badge bg-secondary"><?= esc_html($alat['Kategori'] ?: '-'); ?></span></td>
                  <td><?= esc_html($alat['Merk']); ?></td>
                  <td>
                    <?php
                    $stok = floatval($alat['StokTotal']);
                    $max = floatval($alat['KapasitasMax']);
                    $badge_class = 'bg-light text-dark border';
                    if ($stok <= 0) {
                      $badge_class = 'bg-danger';
                    } elseif ($max > 0 && $stok <= 0.2 * $max) {
                      $badge_class = 'bg-warning text-dark';
                    }
                    ?>
                    <span class="badge <?= $badge_class ?>"><?= esc_html($alat['StokTotal'] . ' ' . $alat['Satuan_Dasar']); ?></span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail-bahan&id=<?= intval($alat['id']); ?>"
                        class="btn btn-sm btn-outline-primary" title="Kelola Kemasan"><i class="fa fa-eye"></i> Detail</a>

                      <?php if (SL_SIMLAB_Auth::can_book()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&addlog-bahan&id=<?= intval($alat['id']); ?>"
                          class="btn btn-sm btn-success" title="Pakai Bahan"><i class="fa fa-flask"></i> Pakai</a>
                      <?php } ?>

                      <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                        <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&ubah-bahan&id=<?= intval($alat['id']); ?>"
                          class="btn btn-sm btn-warning" title="Edit Katalog"><i class="fa fa-pencil"></i> Edit</a>
                        <a href="<?= wp_nonce_url('?page=' . esc_attr($obj->plugin_slug . $obj->menu_slug) . '&hapus-bahan&id=' . intval($alat['id']), 'sl_hapus_bahan_' . intval($alat['id'])); ?>"
                          class="btn btn-sm btn-danger" onclick="return confirm('Hapus bahan berserta kemasannya?');"
                          title="Hapus"><i class="fa fa-trash"></i> Delete</a>
                      <?php } ?>
                    </div>
                  </td>
                </tr>
                <?php $i++; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php SL_SimlabPlugin::renderPagination($total_items, $limit, $current_page); ?>

      </div>
    </div>

  <?php
  } // end else (list)
  ?>

  <script type="text/javascript">
    function lookupPubChem() {
      var name = document.getElementById('new-nama-bahan').value;
      if (!name) {
        alert('Masukkan nama bahan terlebih dahulu.');
        return;
      }
      document.getElementById('pubchem-preview-container').style.display = 'block';
      var panel = document.getElementById('pubchem-add-panel');
      panel.setAttribute('data-pubchem-name', name);
      if (window.triggerPubChemLookup) {
        window.triggerPubChemLookup(panel);
      } else {
        panel.innerHTML = '<p class="text-muted small">PubChem script not ready.</p>';
      }
    }

    function lookupEditPubChem() {
      var name = document.getElementById('edit-nama-bahan').value;
      if (!name) {
        alert('Masukkan nama bahan terlebih dahulu.');
        return;
      }
      document.getElementById('pubchem-edit-preview-container').style.display = 'block';
      var panel = document.getElementById('pubchem-edit-panel');
      panel.setAttribute('data-pubchem-name', name);
      if (window.triggerPubChemLookup) {
        window.triggerPubChemLookup(panel);
      } else {
        panel.innerHTML = '<p class="text-muted small">PubChem script not ready.</p>';
      }
    }

    // Media upload bindings are handled centrally in sl-simlab-core.js
  </script>

<?php
  SL_SimlabPlugin::admin_footer();
} // end is_user_logged_in
?>