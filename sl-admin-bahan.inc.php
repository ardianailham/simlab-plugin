<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
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

  SL_SimlabPlugin::admin_header('Manajemen Bahan', 'fa-flask');

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

  /* ── DETAIL BAHAN & KEMASAN ─────────────────────────────────────────── */
  if (isset($_GET['detail-bahan'])) {
    $id = intval($_GET['id']);
    $data = $obj->getBahanById($id);
    $kemasans = $obj->getKemasanByBahan($id);
    ?>
    <div class="row d-flex justify-content-center">
      <div class="col-lg-10">
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
            <div data-pubchem-panel data-pubchem-name="<?= esc_attr($data['Nama_Bahan']); ?>"></div>
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
                          <a href="<?= wp_nonce_url('?page=' . esc_attr($obj->plugin_slug . $obj->menu_slug) . '&hapus-kemasan&id_kemasan=' . intval($kem['id']) . '&id_bahan=' . intval($data['id']), 'sl_hapus_kemasan_' . intval($kem['id'])); ?>"
                            onclick="return confirm('Yakin ingin menghapus kemasan ini? Riwayat logbook yang terkait kemasan ini akan bermasalah jika ada.')"
                            class="btn btn-danger btn-sm" title="Hapus Kemasan"><i class="fa fa-trash"></i></a>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php endforeach; endif; ?>
              </tbody>
            </table>
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
      <div class="col-lg-8">
        <div class="card shadow-sm">
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
                    <input type="number" step="any" class="form-control" id="Qty" name="Qty" min="0" value="1" required>
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
                  <div data-pubchem-panel data-pubchem-name="<?= esc_attr($data['Nama_Bahan']); ?>"></div>
                </div>

                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-success" name="submit-log-bahan" value="1"><i
                      class="fa fa-check me-1"></i> Simpan Penggunaan</button>
                  <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary">Batal</a>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

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
                  <input type="text" class="form-control" name="Nama_Bahan" value="<?= esc_attr($data['Nama_Bahan']); ?>"
                    required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Alias / Rumus</label>
                  <input type="text" class="form-control" name="Alias" value="<?= esc_attr($data['Alias']); ?>">
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

    if (isset($_POST['import-bahan']) && check_admin_referer('sl_import_bahan')) {
      global $simlab_export_import;
      $count = $simlab_export_import->importBahan($_FILES['file_csv']);
      if ($count !== false) {
        echo "<script>alert('" . intval($count) . " Botol/Kemasan Berhasil Diimport dan Dipetakan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
      } else {
        echo "<script>alert('Gagal Upload!'); history.back();</script>";
      }
    }

    if (isset($_POST['submit-log-bahan']) && check_admin_referer('sl_simlab_bahan_action') && SL_SIMLAB_Auth::can_book()) {
      $obj1 = new SL_SIMLAB_LogbookBahanClass;
      if ($obj1->addLogBahan($_POST) > 0) {
        echo "<script>alert('Berhasil Dipakai!'); document.location = '?page=" . esc_js($obj1->plugin_slug . $obj1->menu_slug) . "';</script>";
      } else {
        // alert is handled natively inside addLogBahan for error logic.
      }
    }

    if (isset($_POST['submit-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
      if ($obj->tambahBahan($_POST)) {
        echo "<script>alert('Katalog Bahan berhasil didaftarkan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
      } else {
        echo "<script>alert('Gagal menambah data!'); history.back();</script>";
      }
    }

    if (isset($_POST['ubah-bahan']) && check_admin_referer('sl_simlab_bahan_action')) {
      if ($obj->ubahBahan($_POST) > 0) {
        echo "<script>alert('Perubahan Katalog Bahan berhasil disimpan!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
      } else {
        echo "<script>alert('Gagal disimpan (atau tidak ada yg berubah)!'); document.location = '?page=" . esc_js($obj->plugin_slug . $obj->menu_slug) . "';</script>";
      }
    }

    $data = $obj->getBahan();
    ?>
    <div class="row">
      <div class="col-lg-12">

        <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
          <div class="d-flex flex-wrap gap-2 mb-4 justify-content-between align-items-center">
            <div class="d-flex gap-2">
              <button id="btn-tambah-bahan" class="btn btn-primary shadow-sm"
                onclick="var t=document.getElementById('tambah-bahan'),i=document.getElementById('import-bahan'); if(t.style.display=='none'){t.style.display='block';i.style.display='none';}else{t.style.display='none';} return false;"><i
                  class="fa fa-plus me-1"></i> Katalog Bahan Baru</button>
              <button class="btn btn-info text-white shadow-sm"
                onclick="var t=document.getElementById('tambah-bahan'),i=document.getElementById('import-bahan'); if(i.style.display=='none'){i.style.display='block';t.style.display='none';}else{i.style.display='none';} return false;"><i
                  class="fa fa-upload me-1"></i> Import CSV Lama</button>
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
                  <label class="form-label small fw-bold">Merk Umum</label>
                  <input type="text" class="form-control" name="Merk">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Satuan Dasar Penghitungan</label>
                  <input type="text" class="form-control" name="Satuan_Dasar" required placeholder="Gram, ml, pcs">
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
              <?php $i = 1; ?>
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
                  <td><span
                      class="badge bg-light text-dark border"><?= esc_html($alat['StokTotal'] . ' ' . $alat['Satuan_Dasar']); ?></span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail-bahan&id=<?= intval($alat['id']); ?>"
                        class="btn btn-sm btn-outline-primary" title="Kelola Kemasan"><i class="fa fa-box"></i> Detail</a>

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

      </div>
    </div>

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
    </script>

    <?php
  } // end else (list)

  SL_SimlabPlugin::admin_footer();
} // end is_user_logged_in
?>