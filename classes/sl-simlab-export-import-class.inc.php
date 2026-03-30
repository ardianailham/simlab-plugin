<?php
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

    $bahan_obj = new SL_SIMLAB_BahanClass();
    $data = $bahan_obj->getBahan();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sl_simlab_bahan_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Nama Bahan', 'Jumlah', 'Satuan', 'Merk', 'Serial', 'Exp', 'Letak'));

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
        'Nama_Bahan' => $row[1],
        'Jumlah' => isset($row[2]) ? $row[2] : 0,
        'Satuan' => isset($row[3]) ? $row[3] : '',
        'Merk' => isset($row[4]) ? $row[4] : '',
        'Serial' => isset($row[5]) ? $row[5] : '',
        'Exp' => isset($row[6]) ? $row[6] : '',
        'Letak' => isset($row[7]) ? $row[7] : ''
      );

      $bahan_obj = new SL_SIMLAB_BahanClass();
      $bahan_obj->tambahBahan($data);
      $count++;
    }

    fclose($handle);
    return $count;
  }
}
