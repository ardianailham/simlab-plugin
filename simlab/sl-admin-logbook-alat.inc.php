<?php
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';
$obj = new SL_SIMLAB_LogbookAlatClass;
$user = get_current_user();
// var_dump($data);

if (isset($_GET['hapus'])) {
  $hapus = $obj->hapusLog($_GET['id']);
  if ($hapus > 0) {
?>
    <script type="text/javascript">
      alert('Data Berhasil Dihapus');
      document.location = 'admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';
    </script>
  <?php } else {
  ?>
    <script type="text/javascript">
      alert('Data Gagal dihapus');
      history.back();
    </script>
  <?php
  }
} elseif (isset($_GET['detail'])) {
  $id = $_GET['id'];
  $data = $obj->getLogAlatById($id);
  // var_dump($data);
  ?>
  <div class="container mt-5">

    <div class="card" style="width: 18rem;">
      <div class="card-body">
        <h5 class="card-title"><?= $data['Nama_Alat']; ?></h5>
        <h6 class="card-subtitle mb-2 text-muted"><?= get_userdata($data['user_id'])->user_nicename; ?></h6>
        <p class="card-text"><?= $data['qty']; ?></p>
        <p class="card-text"><?= date("Y-m-d H:i", $data['start_date']); ?> - <?= date("Y-m-d H:i", $data['end_date']); ?></p>
        <p class="card-text"><?= $data['name']; ?></p>
        <button class="card-link" onclick="document.location = 'admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';">Back</button>

      </div>
    </div>

  </div>
<?php
} else {
  $data = $obj->getLogAlat();
  $obj->getTime();

?>
  <div class="container mt-3">
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <h3 class="mt-3">Logbook Alat</h3>
        <table class="table table-bordered table-responsive table-striped" cellpadding="10" cellspacing="0">
          <thead>
            <tr cellpadding="0" cellspacing="0">
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
          <?php $i = 1; ?>
          <?php foreach ($data as $booking) : ?>
            <tr cellpadding="10" cellspacing="0">
              <td><?= $i; ?></td>
              <td><?= $booking['Nama_Alat']; ?></td>
              <td><?= get_userdata($booking['user_id'])->user_nicename; ?></td>
              <td><?= $booking['qty']; ?></td>
              <td><?= date("Y-m-d H:i", $booking['start_date']); ?></td>
              <td><?= date("Y-m-d H:i", $booking['end_date']); ?></td>
              <td><?= $booking['name']; ?></td>
              <td>
                <?php if (current_user_can('manage_options')) { ?>
                  <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>&hapus&id=<?= $booking['id']; ?>" class="badge text-bg-danger float-end ms-1" onclick="return confirm('Yakin?');">Hapus</a>
                  <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>&detail&id=<?= $booking['id']; ?>" class="badge text-bg-primary float-end ms-1">Detail</a>
                <?php } ?>
              </td>
            </tr>
            <?php $i++; ?>
          <?php endforeach; ?>


        </table>
      </div>
    </div>
  </div>
<?php } ?>