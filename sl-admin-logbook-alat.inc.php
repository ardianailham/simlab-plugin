<?php
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';

$obj     = new SL_SIMLAB_LogbookAlatClass;
$user    = get_current_user();
$user_id = get_current_user_id();

/* ── DELETE ──────────────────────────────────────────────────────────────── */
if (isset($_GET['hapus'])) {
  $id      = intval($_GET['id']);
  $booking = $obj->getLogAlatById($id);

  // Allow delete only if admin OR the booking belongs to the current user
  if (!current_user_can('manage_options') && intval($booking['user_id']) !== $user_id) {
    wp_die(__('You do not have permission to perform this action.'));
  }

  $hapus = $obj->hapusLog($id);
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

/* ── DETAIL ──────────────────────────────────────────────────────────────── */
} elseif (isset($_GET['detail'])) {
  $id   = intval($_GET['id']);
  $data = $obj->getLogAlatById($id);
?>
  <div class="container mt-3 d-flex justify-content-center">
    <div class="col-lg-8">
      <div class="card" style="width: 18rem;">
        <div class="card-body">
          <h5 class="card-title"><?= esc_html($data['Nama_Alat']); ?></h5>
          <h6 class="card-subtitle mb-2 text-muted"><?= esc_html(get_userdata($data['user_id'])->user_nicename); ?></h6>
          <p class="card-text"><?= esc_html($data['qty']); ?></p>
          <p class="card-text">
            <?= esc_html(date('Y-m-d H:i', $data['start_date'])); ?> -
            <?= esc_html(date('Y-m-d H:i', $data['end_date'])); ?>
          </p>
          <p class="card-text"><?= esc_html($data['name']); ?></p>
          <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>
      </div>
    </div>
  </div>

<?php
/* ── LIST (default) ──────────────────────────────────────────────────────── */
} else {
  $data = $obj->getLogAlat();
?>
  <div class="container mt-3 d-flex justify-content-center">
    <div class="col-lg-8">
      <h3 class="mt-3">Logbook Alat</h3>
      <table class="table table-bordered table-responsive table-striped" cellpadding="10" cellspacing="0">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Alat</th>
            <th>Pengguna</th>
            <th>Jumlah</th>
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; ?>
          <?php foreach ($data as $booking) : ?>
            <tr>
              <td><?= $i; ?></td>
              <td><?= esc_html($booking['Nama_Alat']); ?></td>
              <td><?= esc_html(get_userdata($booking['user_id'])->user_nicename); ?></td>
              <td><?= esc_html($booking['qty']); ?></td>
              <td><?= esc_html(date('Y-m-d H:i', $booking['start_date'])); ?></td>
              <td><?= esc_html(date('Y-m-d H:i', $booking['end_date'])); ?></td>
              <td><?= esc_html($booking['name']); ?></td>
              <td>
                <?php if (current_user_can('manage_options') || intval($booking['user_id']) === $user_id) { ?>
                  <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&hapus&id=<?= intval($booking['id']); ?>"
                     class="btn btn-sm btn-danger ms-1"
                     onclick="return confirm('Yakin?');">Hapus</a>
                <?php } ?>
                <?php if (current_user_can('manage_options')) { ?>
                  <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail&id=<?= intval($booking['id']); ?>"
                     class="btn btn-sm btn-primary ms-1">Detail</a>
                <?php } ?>
              </td>
            </tr>
            <?php $i++; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php } ?>