<?php
$simlab_plugin = new SL_SimlabPlugin;
$options = get_option('sl_simlab_links');
if ($options == false) $options = array('daftar-alat' => '', 'daftar-bahan' => '', 'logbook-alat' => '', 'logbook-bahan' => '');
?>

<div class="row mt-4 d-flex">
  <h1>Halaman Inventory</h1>
  <form method="POST">
    <div class="form-floating mb-3 col-lg-5">
      <input type="url" class="form-control" id="floatingDaftarAlat" name="daftar-alat" placeholder="Link Daftar Alat" value="<?= $options['daftar-alat'] ?>">
      <label for="floatingDaftarAlat" class="form-label">Link Daftar Alat</label>
    </div>

    <div class="form-floating mb-3 col-lg-5">
      <input type="url" class="form-control" name="daftar-bahan" id="floatingDaftarBahan" placeholder="Link Daftar Bahan" value="<?= $options['daftar-bahan'] ?>">
      <label for="floatingDaftarBahan" class="form-label">Link Daftar Bahan</label>
    </div>

    <div class="form-floating mb-3 col-lg-5">
      <input type="url" class="form-control" name="logbook-alat" id="floatingLogbookAlat" placeholder="Link Logbook Alat" value="<?= $options['logbook-alat'] ?>">
      <label for="floatingLogbookAlat" class="form-label">Link Logbook Alat</label>
    </div>

    <div class="form-floating mb-3 col-lg-5">
      <input type="url" class="form-control" name="logbook-bahan" id="floatingLogbookBahan" placeholder="Link Logboook Bahan" value="<?= $options['logbook-bahan'] ?>">
      <label for="floatingLogbookBahan" class="form-label">Link Logbook Bahan</label>
    </div>
    <div class="col-lg-3">
      <button type="submit" class="btn btn-primary" id="submit-link" name="submit-link">Simpan</button>
    </div>
  </form>
</div>
<?php
if (isset($_POST['submit-link'])) {
  global $wp;
  $values = array(
    'daftar-alat' => $_POST['daftar-alat'], 'daftar-bahan' => $_POST['daftar-bahan'], 'logbook-alat' => $_POST['logbook-alat'], 'logbook-bahan' => $_POST['logbook-bahan']
  );
  if (!$options) {
    add_option('sl_simlab_links', $values);

?>
    <script>
      location.reload();
    </script>
  <?php
  } else {
    update_option('sl_simlab_links', $values);

  ?>
    <script>
      location.reload();
    </script>
<?php
  }
}
?>