<?php
require_once 'classes/sl-simlab-bahan-class.inc.php';
require_once 'classes/sl-simlab-logbook-bahan-class.inc.php';
$obj = new SL_SIMLAB_LogbookBahanClass;
$data = $obj->getLogBahan();
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
  $data = $obj->getLogBahanById($id);
  ?>
  <div class="container mt-5">

    <div class="card" style="width: 18rem;">
      <div class="card-body">
        <h5 class="card-title"><?= $data['Nama_Bahan']; ?></h5>
        <h6 class="card-subtitle mb-2 text-muted"><?= get_userdata($data['user_id'])->user_nicename; ?></h6>
        <p class="card-text"><?= $data['qty'] . ' ' . $data['Satuan']; ?></p>
        <p class="card-text"><?= $data['date']; ?></p>
        <button class="card-link" onclick="document.location = 'admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';">Back</button>

      </div>
    </div>

  </div>
<?php
} else {
?>
  <div class="container mt-3">
    <div class="row d-flex justify-content-center">
      <div class="col-lg-8">
        <h3 class="mt-3">Logbook Bahan</h3>
        <table class="table table-bordered table-responsive table-striped" cellpadding="10" cellspacing="0">
          <thead>
            <tr cellpadding="10" cellspacing="0">
              <th>No</th>
              <th>Nama Bahan</th>
              <th>User</th>
              <th>Jumlah</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <?php $i = 1; ?>
          <?php foreach ($data as $logbook) : ?>
            <tr cellpadding="10" cellspacing="0">
              <td><?= $i; ?></td>
              <td><?= $logbook['Nama_Bahan']; ?></td>
              <td><?= get_userdata($logbook['user_id'])->user_nicename; ?></td>
              <td><?= $logbook['qty'] . ' ' . $logbook['Satuan']; ?></td>
              <td><?= $logbook['date']; ?></td>
              <td>
                <?php if (current_user_can('manage_options')) { ?>
                  <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>&hapus&id=<?= $logbook['id']; ?>" class="badge text-bg-danger float-end ms-1" onclick="return confirm('Yakin?');">Hapus</a>
                  <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>&detail&id=<?= $logbook['id']; ?>" class="badge text-bg-primary float-end ms-1">Detail</a>
                <?php }; ?>
              </td>
            </tr>
            <?php $i++; ?>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
<?php } ?>