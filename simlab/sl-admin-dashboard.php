<?php
$message = 'Selamat Datang di Sistem Informasi Manajemen Laboratorium';
$obj = new SL_SimlabPlugin;

?>
<div class="container mt-3">
  <div class="row d-flex">
    <h1 align="center"><?= $message; ?></h1>
    <div class="row d-flex justify-content-start mt-4">
      <h5>Gunakan Shortcode Berikut untuk menampilkan data yang diinginkan di halaman page</h5>
      <div class="col-auto">
        <table cellpadding="10" cellspacing="0">
          <thead>
            <tr>
              <th>Shortcode</th>
              <th>Fungsi</th>
            </tr>
          </thead>
          <tr>
            <td>[daftar-alat]</td>
            <td>Menampilkan daftar alat</td>
          </tr>
          <tr>
            <td>[daftar-logbook-alat]</td>
            <td>Menampilkan daftar peminjaman alat</td>
          </tr>
          <tr>
            <td>[daftar-bahan]</td>
            <td>Menampilkan daftar bahan</td>
          </tr>
          <tr>
            <td>[daftar-logbook-bahan]</td>
            <td>Menampilkan daftar penggunaan bahan</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>