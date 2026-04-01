<?php
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';

$obj     = new SL_SIMLAB_LogbookAlatClass;
$user    = get_current_user();
$user_id = get_current_user_id();

SL_SimlabPlugin::admin_header('Logbook Peminjaman Alat', 'fa-calendar');

/* ── DELETE ──────────────────────────────────────────────────────────────── */
if (isset($_GET['hapus'])) {
  $id      = intval($_GET['id']);
  $booking = $obj->getLogAlatById($id);

  if (!SL_SIMLAB_Auth::can_delete_log($user_id, $booking['user_id'])) {
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
  $data = $obj->getLogAlatById($id);
?>
  <div class="row d-flex justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title fw-bold text-primary mb-4"><i class="fa fa-info-circle me-2"></i>Detail Peminjaman</h5>
          <table class="table table-sm border-0">
            <tr><th width="40%">Alat</th><td>: <?= esc_html($data['Nama_Alat']); ?></td></tr>
            <tr><th>Peminjam</th><td>: <?= esc_html(get_userdata($data['user_id'])->display_name); ?></td></tr>
            <tr><th>Jumlah Pinjam</th><td>: <?= esc_html($data['qty']); ?> Unit</td></tr>
            <tr><th>Waktu Mulai</th><td>: <?= esc_html(date('d M Y H:i', $data['start_date'])); ?></td></tr>
            <tr><th>Waktu Selesai</th><td>: <?= esc_html(date('d M Y H:i', $data['end_date'])); ?></td></tr>
            <tr><th>Status Saat Ini</th><td>: <span class="badge bg-info"><?= esc_html($data['name']); ?></span></td></tr>
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
  $data = $obj->getLogAlat();
?>
  <div class="row">
    <div class="col-lg-12">
      <div class="table-responsive">
        <table class="table table-hover align-middle border">
          <thead class="table-light">
            <tr>
              <th width="40">No</th>
              <th>Nama Alat</th>
              <th>Peminjam</th>
              <th width="80">Qty</th>
              <th>Waktu Pinjam</th>
              <th width="120">Status</th>
              <th width="150" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; ?>
            <?php if (empty($data)): ?>
              <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat peminjaman.</td></tr>
            <?php endif; ?>
            <?php foreach ($data as $booking) : ?>
              <tr>
                <td><?= $i; ?></td>
                <td class="fw-bold"><?= esc_html($booking['Nama_Alat']); ?></td>
                <td><i class="fa fa-user-circle-o me-1 text-muted"></i> <?= esc_html(get_userdata($booking['user_id'])->display_name); ?></td>
                <td><?= esc_html($booking['qty']); ?></td>
                <td class="small">
                  <div class="text-success"><i class="fa fa-arrow-right"></i> <?= date('d/m/y H:i', $booking['start_date']); ?></div>
                  <div class="text-danger"><i class="fa fa-arrow-left"></i> <?= date('d/m/y H:i', $booking['end_date']); ?></div>
                </td>
                <td>
                  <?php 
                  $status_class = 'bg-secondary';
                  if ($booking['name'] == 'Accepted' || $booking['name'] == 'Completed') $status_class = 'bg-success';
                  if ($booking['name'] == 'Pending') $status_class = 'bg-warning text-dark';
                  if ($booking['name'] == 'Rejected') $status_class = 'bg-danger';
                  ?>
                  <span class="badge <?= $status_class; ?>"><?= esc_html($booking['name']); ?></span>
                </td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <?php if (SL_SIMLAB_Auth::is_admin()) { ?>
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail&id=<?= intval($booking['id']); ?>"
                         class="btn btn-sm btn-outline-primary" title="Detail"><i class="fa fa-eye"></i> Detail</a>
                    <?php } ?>
                    <?php if (SL_SIMLAB_Auth::can_delete_log($user_id, $booking['user_id'])) { ?>
                      <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&hapus&id=<?= intval($booking['id']); ?>"
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Hapus logbook ini?');" title="Hapus"><i class="fa fa-trash"></i> Delete</a>
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

<?php 
} 
SL_SimlabPlugin::admin_footer();
?>