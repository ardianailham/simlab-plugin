<?php
require_once 'classes/sl-simlab-bahan-class.inc.php';
require_once 'classes/sl-simlab-logbook-bahan-class.inc.php';
$obj = new SL_SIMLAB_BahanClass;
$user = get_current_user();
if (!is_user_logged_in($user)) {
?> <div class="row">
    <h3> Silakan Login Terlebih dahulu atau Daftar apabila anda belum memiliki akun</h3>
  </div>
  <div class="d-flex justify-content-center">
    <?php
    $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request)); ?>
    <a href="<?php echo esc_url(wp_login_url($current_url)); ?>" class="btn btn-primary me-1"><?php _e('Log in') ?></a>
    <a href="<?php echo esc_url(wp_registration_url($current_url)) ?>" class="btn btn-success ms-1"> <?php _e('Register') ?></a>
  </div>
  <?php
} else {
  $nonce = wp_create_nonce();
  if (isset($_GET['detail-bahan'])) {
    $id = $_GET['id'];
    $data = $obj->getBahanById($id);
  ?>
    <div class="container mt-5">

      <div class="card" style="width: 18rem;">
        <div class="card-body">
          <h5 class="card-title"><?= $data['Nama_Bahan']; ?></h5>
          <h6 class="card-subtitle mb-2 text-muted"><?= $data['Merk']; ?></h6>
          <p class="card-text"><?= $data['Jumlah'] . ' ' . $data['Satuan']; ?></p>
          <p class="card-text"><?= $data['Serial']; ?></p>
          <p class="card-text"><?= $data['Exp']; ?></p>
          <p class="card-text"><?= $data['Letak']; ?></p>
          <button onclick="document.location = 'admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';" class="card-link">Back</button>

        </div>
      </div>

    </div>
  <?php } elseif (isset($_GET['addlog-bahan'])) {
    $id = $_GET['id'];
    $data = $obj->getBahanById($id);
    $time = $obj->getTime();

  ?>
    <div class="container mt-3">
      <h3>Booking <?= $data['Nama_Bahan']; ?></h3>
      <form method="post">
        <input type="hidden" name="id" id="id">
        <input type="hidden" name="id_bahan" id="id_bahan" value="<?= $data['id'] ?>">
        <input type="hidden" name="status" id="status" value="3">
        <input type="hidden" name="user_id" id="user_id" value="<?= get_current_user_id(); ?>">
        <div class="mb-3">
          <label for="nama-bahan">Nama Bahan</label>
          <input type="text" class="form-control" id="nama-bahan" name="Nama_Bahan" value="<?= $data['Nama_Bahan'] ?>">
        </div>
        <div class="mb-3">
          <label for="merk">Merk</label>
          <input type="text" class="form-control" id="merk" name="Merk" value="<?= $data['Merk'] ?>">
        </div>
        <div class="mb-3">
          <label for="Jumlah">Jumlah</label>
          <input type="number" step="0.00001" class="form-control" min="0.00001" max="<?= $data['Jumlah']; ?>" value="0.00001" id="Qty" name="Qty">
        </div>
        <div class="mb-3">
          <label for="tanggal">Tanggal</label>
          <input type="datetime-local" class="form-control" value="<?= $time[0]; ?>" id="tanggal" name="tanggal">
        </div>


        <button type="submit" class="btn btn-primary submit" id="submit-log-bahan" name="submit-log-bahan">Book</button>
      </form>
      <button class="btn btn-primary submit card-link" onclick="document.location = 'admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';">Back</button>
    </div>
  <?php
  } elseif (isset($_GET['ubah-bahan'])) {
    $data = $obj->getBahanById($_GET['id']);
  ?>
    <div class="container mt-3">
      <div class="row d-flex justify-content-center">
        <div class="col-lg-8">
          <div class="ubah-bahan" id="ubah-bahan">
            <form method="post">
              <input type="hidden" name="id" id="id" value="<?= $data['id']; ?>">
              <div class="mb-3">
                <label for="nama-bahan" class="form-label">Nama Bahan</label>
                <input type="text" class="form-control" id="nama-bahan" name="Nama_Bahan" value="<?= $data['Nama_Bahan']; ?>">
              </div>
              <div class="mb-3">
                <label for="merk" class="form-label">Merk</label>
                <input type="text" class="form-control" id="merk" name="Merk" value="<?= $data['Merk']; ?>">
              </div>
              <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="number" step="0.00001" class="form-control" min="0.00001" value="<?= $data['Jumlah']; ?>" id="Qty" name="Qty">
              </div>
              <div class="mb-3">
                <label for="satuan" class="form-label">Satuan</label>
                <input type="text" class="form-control" id="satuan" name="Satuan" value="<?= $data['Satuan']; ?>">
              </div>
              <div class="mb-3">
                <label for="serial" class="form-label">Serial</label>
                <input type="text" class="form-control" id="serial" name="Serial" value="<?= $data['Serial']; ?>">
              </div>
              <div class="mb-3">
                <label for="Exp" class="form-label">Exp</label>
                <input type="text" class="form-control" id="Exp" name="Exp" value="<?= $data['Exp']; ?>">
              </div>
              <div class="mb-3">
                <label for="letak" class="form-label">Letak</label>
                <input type="text" class="form-control" id="letak" name="Letak" value="<?= $data['Letak']; ?>">
              </div>
              <div class="col-sm-3 mb-3">
                <button type="submit" class="btn btn-primary" id="submit-bahan" name="ubah-bahan">Ubah Bahan</button>
              </div>
            </form>
            <div class="col-sm-3">
              <button class="btn btn-primary card-link" onclick="return document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';">Back</button>
            </div>
          </div>
        </div>
        <?php
      } elseif (isset($_GET['hapus-bahan'])) {
        $hapus = $obj->hapusBahan($_GET['id']);
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
      } else {
        $data = $obj->getBahan();
        ?>
        <div class="container mt-3">
          <div class="row d-flex justify-content-center">
            <div class="col-lg-8">
              <button id="tambah-bahan-button" class="btn btn-primary submit" onclick="tambahBahan()">Tambah Bahan</button>
              <div class="tambah-bahan" id="tambah-bahan">
                <form method="post">
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
                    <input type="number" class="form-control" id="jumlah" name="Jumlah" min="0.00001">
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
                  <button type="submit" id="submit-bahan" name="submit-bahan">Tambah Bahan</button>
                </form>
              </div>
            </div>
            <div class="col-lg-8">
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
                <?php $i = 1; ?>
                <?php foreach ($data as $alat) :
                ?>
                  <tr>
                    <td><?= $i; ?></td>
                    <td><?= $alat['Nama_Bahan']; ?></td>
                    <td><?= $alat['Merk']; ?></td>
                    <td><?= $alat['Jumlah'] . ' ' . $alat['Satuan']; ?></td>
                    <td><?= $alat['Letak']; ?></td>
                    <td>
                      <?php if (current_user_can('manage_options')) { ?>
                        <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&hapus-bahan&id=<?= $alat['id']; ?>" class="badge text-bg-danger float-end ms-1" onclick="return confirm('yakin?');">Hapus</a>
                        <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&ubah-bahan&id=<?= $alat['id']; ?>" class="badge text-bg-warning float-end ms-1">Ubah</a>
                      <?php }; ?>
                      <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&detail-bahan&id=<?= $alat['id']; ?>" class="badge text-bg-primary float-end ms-1">Detail</a>
                      <a href="admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&addlog-bahan&id=<?= $alat['id']; ?>" class="badge text-bg-success float-end ms-1">Book!</a>

                    </td>
                  </tr>
                  <?php $i++; ?>
                <?php endforeach;
                ?>


              </table>




            </div>




          </div>
        <?php } ?>
        <style>
          .tambah-bahan {
            display: none;
          }
        </style>
        <script type="text/javascript">
          function tambahBahan() {
            var tambahBahan = document.getElementById('tambah-bahan');
            var displaySetting = tambahBahan.style.display;
            var button = document.getElementById
            if (displaySetting == 'block') {
              tambahBahan.style.display = 'none';
            } else {
              tambahBahan.style.display = 'block';
            }
          }
        </script>
        <?php

        if (isset($_POST['submit-log-bahan'])) {
          $obj1 = new SL_SIMLAB_LogbookBahanClass;
          $obj1->addLogBahan($_POST);
          // var_dump($_POST);
        }
        if (isset($_POST['submit-bahan'])) {
          $obj->tambahBahan($_POST);
        ?>
          <script type="text/javascript">
            document.location = 'admin.php?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';
          </script>
        <?php
        }

        if (isset($_POST['ubah-bahan'])) {
          if ($obj->ubahBahan($_POST) > 0)
        ?>
          <script type="text/javascript">
            document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';
          </script>
      <?php
        }
      }
      ?>