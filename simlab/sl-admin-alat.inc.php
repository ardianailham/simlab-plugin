<?php
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';
$user = get_current_user();
// var_dump(wp_get_upload_dir());
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
  $obj = new SL_SIMLAB_AlatClass;
  $nonce = wp_create_nonce();

  if (isset($_GET['detail-alat'])) {
    $id = $_GET['id'];
    $data = $obj->getAlatById($id);
    // var_dump($data);
  ?>
    <div class="container mt-5">

      <div class="card" style="width: 18rem;">
        <div class="card-body">
          <h5 class="card-title"><?= $data['Nama_Alat']; ?></h5>
          <h6 class="card-subtitle mb-2 text-muted"><?= $data['Merk']; ?></h6>
          <p class="card-text"><?= $data['Qty']; ?></p>
          <button class="card-link" onclick="document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';">Back</button>

        </div>
      </div>

    </div>
  <?php
  } elseif (isset($_GET['ubah-alat'])) {
    $id = $_GET['id'];
    $data = $obj->getAlatById($id);
  ?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-8">
        <div class="ubah-alat" id="ubah-alat">
          <form method="post">
            <input type="hidden" name="id" id="id" value="<?= $data['id']; ?>">
            <div class="mb-3">
              <label for="nama-alat" class="form-label">Nama Alat</label>
              <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" value="<?= $data['Nama_Alat']; ?>" required>
            </div>
            <div class="mb-3">
              <label for="merk" class="form-label">Merk</label>
              <input type="text" class="form-control" id="merk" name="Merk" value="<?= $data['Merk']; ?>">
            </div>
            <div class="mb-3">
              <label for="Qty" class="form-label">Qty</label>
              <input type="number" class="form-control" id="Qty" name="Qty" min="1" value="<?= $data['Qty']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary submit" id="ubah-alat" name="ubah-alat">Ubah Alat</button>
          </form>
        </div>
      </div>
    </div>
    </div>
  <?php
  } elseif (isset($_GET['addlog-alat'])) {
    $id = $_GET['id'];
    $data = $obj->getAlatById($id);
    $time = $obj->getTime();

  ?>
    <div class="container mt-3 d-flex justify-content-center">
      <div class="col-lg-8">
        <h3>Booking <?= $data['Nama_Alat']; ?></h3>
        <form action="" method="post">
          <input type="hidden" name="id" id="id">
          <input type="hidden" name="id_alat" id="id_alat" value="<?= $data['id'] ?>">
          <input type="hidden" name="user_id" id="user_id" value="<?= get_current_user_id() ?>">
          <div class="mb-3">
            <label for="nama-alat">Nama Alat</label>
            <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" value="<?= $data['Nama_Alat'] ?>">
          </div>
          <div class="mb-3">
            <label for="merk">Merk</label>
            <input type="text" class="form-control" id="merk" name="Merk" value="<?= $data['Merk'] ?>">
          </div>
          <div class="mb-3">
            <label for="Qty">Qty</label>
            <input type="number" class="form-control" min="1" max="<?= $data['Qty']; ?>" id="Qty" name="Qty" value="1">
          </div>
          <div class="mb-3">
            <label for="start_date">Start Date</label>
            <input type="datetime-local" class="form-control" name="start_date" id="start_date" value="<?= $time[0]; ?>">
          </div>
          <div class="mb-3">
            <label for="end_date">End Date</label>
            <input type="datetime-local" class="form-control" name="end_date" id="end_date" value="<?= $time[1]; ?>">
          </div>
          <div class="col-sm-3 mb-1">
            <button type="submit" class="btn btn-primary submit" id="submit-log-alat" name="submit-log-alat">Book</button>
          </div>
        </form>
        <div class="col-sm-3">
          <button class="btn btn-primary card-link" onclick="return document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';">Back</button>
        </div>
      </div>
    </div>
    <?php } elseif (isset($_GET['hapus-alat'])) {
    $hapus = $obj->hapusAlat($_GET['id']);
    if ($hapus > 0) {
    ?>
      <script type="text/javascript">
        alert('Data Berhasil Dihapus');
        document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';
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
    $data = $obj->getAlat();
    ?>
    <div class="container mt-3">
      <div class="row d-flex justify-content-center">
        <!-- <?php if (is_admin()) { ?>
          <div class="col-lg-8 float-end">
            <button id="import-alat-button" class="btn btn-primary submit" onclick="return importAlat()">Import Alat</button>
            <div class="import-alat" id="import-alat">
              <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="file" class="form-label">File</label>
                  <input type="file" class="form-control" id="import-file" name="file" required>
                </div>
                <button type="submit" class="btn btn-primary submit" id="import-alat-submit" name="import-alat-submit">Import Alat</button>
              </form>
            </div>
          </div>
        <?php } ?> -->
        <?php if (current_user_can('manage_options')) { ?>
          <div class="col-lg-8">
            <!-- Button trigger modal -->
            <!-- <button type="button" class="btn btn-primary tambahAlat" data-bs-toggle="modal" data-bs-target="#formModal">
              Tambah Alat
            </button> -->
            <button id="tambah-alat-button" class="btn btn-primary submit" onclick="return tambahAlat()">Tambah Alat</button>
            <div class="tambah-alat" id="tambah-alat">
              <form method="post">
                <input type="hidden" name="id" id="id">
                <div class="mb-3">
                  <label for="nama-alat" class="form-label">Nama Alat</label>
                  <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" required>
                </div>
                <div class="mb-3">
                  <label for="merk" class="form-label">Merk</label>
                  <input type="text" class="form-control" id="merk" name="Merk">
                </div>
                <div class="mb-3">
                  <label for="Qty" class="form-label">Qty</label>
                  <input type="number" class="form-control" id="Qty" name="Qty" min="1" required>
                </div>
                <button type="submit" class="btn btn-primary submit" id="submit-alat" name="submit-alat">Tambah Alat</button>
              </form>
            </div>
          </div>
        <?php } ?>

        <!-- Modal -->
        <!-- <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="judulModal" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="judulModal">Tambah Alat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="post">
                  <input type="hidden" name="id" id="id">
                  <div class="mb-3">
                    <label for="nama-alat" class="form-label">Nama Alat</label>
                    <input type="text" class="form-control" id="nama-alat" name="Nama_Alat" required>
                  </div>
                  <div class="mb-3">
                    <label for="merk" class="form-label">Merk</label>
                    <input type="text" class="form-control" id="merk" name="Merk">
                  </div>
                  <div class="mb-3">
                    <label for="Qty" class="form-label">Qty</label>
                    <input type="number" class="form-control" id="Qty" name="Qty" min="1" required>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary submit" id="submit">Tambah</button>
                </form>
              </div>
            </div>
          </div>
        </div> -->

        <div class="col-lg-8">
          <h3 class="mt-3">Daftar Alat</h3>
          <table class="table table-bordered table-responsive table-striped" cellpadding="10" cellspacing="0">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Merk</th>
                <th>Jumlah</th>
                <th colspan="4">Aksi</th>
              </tr>
            </thead>
            <?php $i = 1; ?>
            <?php foreach ($data as $alat) : ?>
              <tr>
                <td><?= $i; ?></td>
                <td><?= $alat['Nama_Alat']; ?></td>
                <td><?= $alat['Merk']; ?></td>
                <td><?= $alat['Qty']; ?></td>
                <?php if (current_user_can('manage_options')) { ?>
                  <td style="border: none;">
                    <a href="?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&hapus-alat&id=<?= $alat['id']; ?>" class="badge text-bg-danger float-end ms-1" onclick="return confirm('yakin?');">Hapus</a>
                  </td>
                  <td style="border: none;">
                    <a href="?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&ubah-alat&id=<?= $alat['id']; ?>" class="badge text-bg-warning float-end ms-1">Ubah</a>
                  <?php }; ?>
                  </td>
                  <td style="border: none;">
                    <a href=" ?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&detail-alat&id=<?= $alat['id']; ?>" class="badge text-bg-primary float-end ms-1">Detail</a>
                  </td>
                  <td style="border-left: none;">
                    <a href="?page=<?= $obj->plugin_slug . $obj->menu_slug ?>&addlog-alat&id=<?= $alat['id']; ?>" class="badge text-bg-success float-end ms-1">Book!</a>
                  </td>
              </tr>
              <?php $i++; ?>
            <?php endforeach; ?>


          </table>
        </div>
      </div>
    <?php } ?>
    <style>
      .tambah-alat {
        display: none;
      }
    </style>
    <script type="text/javascript">
      function tambahAlat() {
        var tambahAlat = document.getElementById('tambah-alat');
        var displaySetting = tambahAlat.style.display;
        var button = document.getElementById
        if (displaySetting == 'block') {
          tambahAlat.style.display = 'none';
        } else {
          tambahAlat.style.display = 'block';
        }
      }
    </script>
    <?php

    if (isset($_POST['submit-log-alat'])) {
      $obj1 = new SL_SIMLAB_LogbookAlatClass;
      $addLog = $obj1->addLogAlat($_POST);
      if ($addLog > 0) {
    ?>
        <script type="text/javascript">
          alert('Data Berhasil Ditambahkan');
          document.location = '?page=<?= $obj1->plugin_slug . $obj1->menu_slug; ?>';
        </script>
      <?php
      }
    }


    if (isset($_POST['submit-alat'])) {
      // cek Apakah data yang ditambahkan benar
      if ($_POST['Nama_Alat'] == '' && $_POST['Qty'] == '') {
      ?>
        <script type="text/javascript">
          alert("Form yang anda masukkan tidak benar!");
        </script>
      <?php
      } else {
        $obj->tambahAlat($_POST);
      ?>
        <script type="text/javascript">
          document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';
        </script>
      <?php
      }
    }

    if (isset($_POST['ubah-alat'])) {
      if ($obj->ubahAlat($_POST) > 0)
      ?>
      <script type="text/javascript">
        document.location = '?page=<?= $obj->plugin_slug . $obj->menu_slug; ?>';
      </script>
  <?php
    }
    // if (isset($_POST['import-alat-submit'])) {
    //   if (!function_exists('wp_handle_upload')) {
    //     require_once(ABSPATH . 'wp-admin/includes/file.php');
    //   }

    //   $uploadedfile = $_FILES['file'];

    //   $upload_overrides = array(
    //     'test_form' => false
    //   );

    //   $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    //   if (
    //     $movefile && !isset($movefile['error'])
    //   ) {
    //     echo __('File was successfully uploaded.', 'textdomain') . "\n";
    //     $obj->importDatabase($movefile);
    //   } else {
    //     /*
    //  * Error generated by _wp_handle_upload()
    //  * @see _wp_handle_upload() in wp-admin/includes/file.php
    //  */
    //     echo $movefile['error'];
    //   }
    // }
  }
  ?>