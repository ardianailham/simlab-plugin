<?php

class SL_SIMLAB_BahanClass extends SL_SimlabPlugin
{
  protected $table = 'sl_simlab_bahan';
  public $menu_slug = '-daftar-bahan';
  private $db;

  public function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }

  public function index()
  {
    ob_start();
    include_once dirname(__FILE__) . '/../sl-admin-bahan.inc.php';
    return ob_get_clean();
  }

  // query Bahan from database
  public function getBahan()
  {
    $query = $this->db->prepare('SELECT * FROM ' . $this->db->prefix . $this->table);
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }
  // query Bahan from database berdasar id
  public function getBahanById($id)
  {
    $query = $this->db->prepare("SELECT * FROM " . $this->db->prefix . $this->table . " WHERE id= %s", $id);
    $results = $this->db->get_row($query, ARRAY_A);
    return $results;
  }
  // tambah Bahan ke database
  public function tambahBahan($data)
  {
    $table = $this->db->prefix . $this->table;
    $values = array(
      'id' => '',
      'Nama_Bahan' => $data['Nama_Bahan'],
      'Jumlah' => $data['Jumlah'],
      'Satuan' => $data['Satuan'],
      'Merk' => $data['Merk'],
      'Serial' => $data['Serial'],
      'Exp' => $data['Exp'],
      'Letak' => $data['Letak']
    );
    $results = $this->db->insert($table, $values);
    return $results;
  }
  // update Bahan
  public function ubahBahan($data)
  {
    $table = $this->db->prefix . $this->table;
    $value = array(
      "Nama_Bahan" => $data['Nama_Bahan'],
      "Jumlah" => $data["Qty"],
      "Satuan" => $data['Satuan'],
      "Merk" => $data['Merk'],
      "Serial" => $data["Serial"],
      "Exp" => $data["Exp"],
      "Letak" => $data["Letak"]
    );
    $where = array('id' => $data['id']);


    $results = $this->db->update($table, $value, $where);
    return $results;
  }
  // hapus Bahan
  public function hapusBahan($id)
  {
    $table = $this->db->prefix . $this->table;
    $where = array('id' => $id);
    $results = $this->db->delete($table, $where, array('%d'));
    return $results;
  }
  // sinkronisasi dengan logbook
  public function updateByBook($id, $Stok, $ambil)
  {
    $table = $this->db->prefix . $this->table;
    $Jumlah = $Stok - $ambil;
    $query = $this->db->prepare('UPDATE ' . $table . ' SET
    Jumlah = %s WHERE id = %d', $Jumlah, $id);
    $results = $this->db->query($query);
    return $results;
  }
}
