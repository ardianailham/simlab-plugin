<?php
if (! defined('ABSPATH')) {
  exit;
}
class SL_SIMLAB_ExportImportClass extends SL_SimlabPlugin
{
  private $db;

  public function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }

  public function exportAlat()
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have permission to perform this action.'));
    }

    $alat_obj = new SL_SIMLAB_AlatClass();
    $data = $alat_obj->getAlat();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sl_simlab_alat_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Nama Alat', 'Merk', 'Qty'));

    if (!empty($data)) {
      foreach ($data as $row) {
        fputcsv($output, $row);
      }
    }
    fclose($output);
    exit;
  }

  public function exportBahan()
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have permission to perform this action.'));
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sl_simlab_bahan_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Nama Bahan', 'Alias', 'Kategori', 'Label Botol', 'Jumlah', 'Satuan', 'Merk', 'Exp', 'Letak'));

    $query = "SELECT b.id, b.Nama_Bahan, b.Alias, b.Kategori, k.label_kemasan, k.jumlah_tersedia, k.satuan, b.Merk, k.exp_date, k.letak 
              FROM {$this->db->prefix}sl_simlab_bahan b 
              LEFT JOIN {$this->db->prefix}sl_simlab_bahan_kemasan k ON b.id = k.id_bahan";
    $data = $this->db->get_results($query, ARRAY_N);

    if (!empty($data)) {
      foreach ($data as $row) {
        fputcsv($output, $row);
      }
    }
    fclose($output);
    exit;
  }

  public function importAlat($file)
  {
    if (!current_user_can('manage_options')) {
      return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
      return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
      return false;
    }

    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
      return false;
    }

    // Skip the header row
    fgetcsv($handle);

    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
      if (empty($row[1])) continue; // Skip if name is empty

      $data = array(
        'Nama_Alat' => $row[1],
        'Merk' => isset($row[2]) ? $row[2] : '',
        'Qty' => isset($row[3]) ? intval($row[3]) : 0
      );

      $alat_obj = new SL_SIMLAB_AlatClass();
      $alat_obj->tambahAlat($data);
      $count++;
    }

    fclose($handle);
    return $count;
  }

  public function importBahan($file)
  {
    if (!current_user_can('manage_options')) {
      return false;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
      return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
      return false;
    }

    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
      return false;
    }

    fgetcsv($handle); // Skip header

    $count = 0;
    $bahan_obj = new SL_SIMLAB_BahanClass();

    // Format CSV logistik baru:
    while (($row = fgetcsv($handle)) !== false) {
      if (empty($row[1])) continue;

      $nama_bahan = $row[1];
      $alias      = isset($row[2]) ? $row[2] : '';
      $kategori   = isset($row[3]) ? $row[3] : '';
      $label_botol = isset($row[4]) ? $row[4] : '';
      $jumlah     = isset($row[5]) ? str_replace(',', '.', $row[5]) : 0;
      $satuan     = isset($row[6]) ? $row[6] : '';
      $merk       = isset($row[7]) ? $row[7] : '';
      $exp        = isset($row[8]) ? $row[8] : null;
      $letak      = isset($row[9]) ? $row[9] : '';

      // Cari apakah bahan (katalog) sudah ada
      $q = $this->db->prepare("SELECT id FROM {$this->db->prefix}sl_simlab_bahan WHERE Nama_Bahan = %s LIMIT 1", $nama_bahan);
      $existing_bahan_id = $this->db->get_var($q);

      if (!$existing_bahan_id) {
        $bahan_data = array(
          'Nama_Bahan'   => $nama_bahan,
          'Alias'        => $alias,
          'Kategori'     => $kategori,
          'Merk'         => $merk,
          'Satuan_Dasar' => $satuan
        );
        $bahan_obj->tambahBahan($bahan_data);
        $existing_bahan_id = $this->db->insert_id;
      }

      // Sisipkan kemasan/botol
      // Parse exp date from DD/MM/YYYY to YYYY-MM-DD
      $exp_date = null;
      if (!empty($exp)) {
        $date = DateTime::createFromFormat('d/m/Y', $exp);
        if ($date) {
          $exp_date = $date->format('Y-m-d');
        }
      }

      $kemasan_data = array(
        'id_bahan'        => $existing_bahan_id,
        'label_kemasan'   => !empty($label_botol) ? $label_botol : 'Botol Import',
        'kapasitas_awal'  => $jumlah,
        'satuan'          => $satuan,
        'exp_date'        => $exp_date,
        'letak'           => $letak,
        'catatan_kondisi' => 'Sistem Logistik CSV'
      );
      $bahan_obj->tambahKemasan($kemasan_data);
      $count++;
    }

    fclose($handle);
    return $count;
  }
}
