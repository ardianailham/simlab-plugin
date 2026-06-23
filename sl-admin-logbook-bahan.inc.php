<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once 'classes/sl-simlab-bahan-class.inc.php';
require_once 'classes/sl-simlab-logbook-bahan-class.inc.php';

$obj     = new SL_SIMLAB_LogbookBahanClass;
$user    = get_current_user();
$user_id = get_current_user_id();

SL_SimlabPlugin::admin_header('Logbook Penggunaan Bahan', 'fa-flask');

/* ── DELETE ──────────────────────────────────────────────────────────────── */
if (isset($_GET['hapus'])) {
  $id      = intval($_GET['id']);
  check_admin_referer('sl_hapus_logbook_bahan_' . $id);
  $logbook = $obj->getLogBahanById($id);

  if (!SL_SIMLAB_Auth::can_delete_log($user_id, $logbook['user_id'])) {
    wp_die(__('You do not have permission to perform this action.'));
  }

  if ($obj->hapusLog($id) > 0) {
    echo "<script>alert('Data Berhasil Dihapus'); document.location = '?page=".esc_js($obj->plugin_slug.$obj->menu_slug)."';</script>";
  } else {
    echo "<script>alert('Gagal!'); history.back();</script>";
  }

/* ── DETAIL ──────────────────────────────────────────────────────────────── */
} elseif (isset($_GET['detail'])) {
  $id   = intval($_GET['id']);
  $data = $obj->getLogBahanById($id);
?>
  <div class="row d-flex justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title fw-bold text-primary mb-4"><i class="fa fa-info-circle me-2"></i>Detail Penggunaan Bahan</h5>
          <table class="table table-sm border-0">
            <tr><th width="40%">Bahan Utama</th><td>: <?= esc_html($data['Nama_Bahan']); ?></td></tr>
            <tr><th width="40%">Label Botol/Kemasan</th><td>: <span class="badge bg-info text-dark"><?= esc_html($data['label_kemasan']); ?></span></td></tr>
            <tr><th>Pengguna</th><td>: <?= esc_html(get_userdata($data['user_id'])->display_name); ?></td></tr>
            <tr><th>Jumlah Pakai</th><td>: <?= esc_html($data['qty'] . ' ' . $data['satuan']); ?></td></tr>
            <tr><th>Tanggal</th><td>: <?= esc_html($data['date']); ?></td></tr>
          </table>
          <div class="mt-4">
             <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary btn-sm">Kembali</a>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
/* ── LIST (default) ──────────────────────────────────────────────────────── */
} else {
  $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
  $filter_tujuan = isset($_GET['filter_tujuan']) ? sanitize_text_field($_GET['filter_tujuan']) : '';
  $filter = ['tujuan' => $filter_tujuan];
  $tujuans = $obj->getDistinctTujuan();

  $limit = 10;
  $current_page = isset($_GET['sl_paged']) ? max(1, intval($_GET['sl_paged'])) : 1;
  $offset = ($current_page - 1) * $limit;
  $total_items = $obj->getLogBahanCount($search, $filter);
  $data = $obj->getLogBahan($limit, $offset, $search, $filter);
  $reset_url = esc_url(remove_query_arg(['search', 'filter_tujuan', 'sl_paged']));
?>
  <div class="row">
    <div class="col-lg-12">
      
      <form method="get" class="row g-2 mb-4 align-items-center bg-white p-3 rounded border shadow-sm mx-0">
        <?php
        foreach ($_GET as $key => $val) {
          if (!in_array($key, ['search', 'filter_tujuan', 'sl_paged'])) {
            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '">';
          }
        }
        ?>
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama bahan, pengguna, tujuan..." value="<?= esc_attr($search); ?>">
          </div>
        </div>
        <div class="col-md-4">
          <select name="filter_tujuan" class="form-select">
            <option value="">-- Semua Tujuan/Keperluan --</option>
            <?php foreach ($tujuans as $tj): ?>
              <option value="<?= esc_attr($tj); ?>" <?= $filter_tujuan === $tj ? 'selected' : ''; ?>><?= esc_html($tj); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa fa-filter"></i> Cari</button>
          <?php if (!empty($search) || !empty($filter_tujuan)): ?>
            <a href="<?= $reset_url; ?>" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset"><i class="fa fa-refresh"></i></a>
          <?php endif; ?>
        </div>
      </form>
      <div class="table-responsive">
        <table class="table table-hover align-middle border">
          <thead class="table-light">
            <tr>
              <th width="40">No</th>
              <th>Nama Bahan</th>
              <th>Pengguna</th>
              <th width="120">Jumlah</th>
              <th>Tanggal</th>
              <th width="150" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = $offset + 1; ?>
            <?php if (empty($data)): ?>
              <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat penggunaan bahan.</td></tr>
            <?php endif; ?>
            <?php foreach ($data as $logbook) : ?>
              <tr>
                <td><?= $i; ?></td>
                <td class="fw-bold"><?= esc_html($logbook['Nama_Bahan']); ?><br><small class="text-muted"><i class="fa fa-box"></i> <?= esc_html($logbook['label_kemasan']); ?></small></td>
                <td><i class="fa fa-user-circle-o me-1 text-muted"></i> <?= esc_html(get_userdata($logbook['user_id'])->display_name); ?></td>
                <td><span class="badge bg-light text-dark border"><?= esc_html($logbook['qty'] . ' ' . $logbook['satuan']); ?></span></td>
                <td class="small"><?= esc_html($logbook['date']); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail&id=<?= intval($logbook['id']); ?>"
                         class="btn btn-sm btn-outline-primary" title="Detail"><i class="fa fa-eye"></i> Detail</a>
                    <?php } ?>
                    <?php if (SL_SIMLAB_Auth::can_delete_log($user_id, $logbook['user_id'])) { ?>
                      <a href="<?= wp_nonce_url('?page=' . esc_attr($obj->plugin_slug . $obj->menu_slug) . '&hapus&id=' . intval($logbook['id']), 'sl_hapus_logbook_bahan_' . intval($logbook['id'])); ?>"
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Hapus riwayat ini?');" title="Hapus"><i class="fa fa-trash"></i> Delete</a>
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
} 
SL_SimlabPlugin::admin_footer();
?>