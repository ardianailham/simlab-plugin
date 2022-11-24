<?php
class SL_SIMLAB_AlatClass extends SL_SimlabPlugin
{
  private $table = 'sl_simlab_alat';
  public $menu_slug = '-daftar-alat';
  private $db;


  public function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }

  public function index()
  {

    ob_start();
    include(plugin_dir_path(__FILE__) . '/../sl-admin-alat.inc.php');
    return ob_get_clean();
  }

  // query alat from database
  public function getAlat()
  {

    $query = $this->db->prepare('SELECT * FROM ' . $this->db->prefix . $this->table);
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }
  // query alat from database berdasar id
  public function getAlatById($id)
  {
    $query = $this->db->prepare("SELECT * FROM " . $this->db->prefix . $this->table . " WHERE id= %s", $id);
    $results = $this->db->get_row($query, ARRAY_A);
    return $results;
  }
  // tambah alat ke database
  public function tambahAlat($data)
  {
    if ($data['Nama_Alat'] == '' && $data['Qty'] == '') {
      return $data;
    } else {
      $table = $this->db->prefix . $this->table;
      $values = array(
        'id' => '',
        'Nama_Alat' => $data['Nama_Alat'],
        'Merk' => $data['Merk'],
        'Qty' => $data['Qty']
      );
      // $results = $this->db->query($this->db->prepare($query));
      $results = $this->db->insert($table, $values);

      // return $values;
      return $results;
    }
    // return $query;
    // return $this->db->print_error();
  }
  // update alat
  public function ubahAlat($data)
  {
    $table = $this->db->prefix . $this->table;
    $value = array(
      "Nama_Alat" => $data['Nama_Alat'],
      "Merk" => $data['Merk'],
      "Qty" => $data['Qty']
    );
    $where = array('id' => $data['id']);


    $results = $this->db->update($table, $value, $where);
    return $results;
  }
  // hapus alat
  public function hapusAlat($id)
  {
    $table = $this->db->prefix . $this->table;
    $where = array('id' => $id);
    $results = $this->db->delete($table, $where, array('%d'));
    return $results;
  }

  // public function importDatabase($file)
  // {
  //   $table = $this->db->prefix . $this->table;
  //   $tmp_file = $file['url'];
  //   $query = <<<eof
  //   LOAD DATA INFILE '$tmp_file' INTO TABLE 
  //   eof . $table . ' ' . <<<eof
  // FIELDS TERMINATED BY ',' ENCLOSED BY '"'
  // LINES TERMINATED BY '\r\n'
  // IGNORE 1 LINES
  // eof;
  //   $results = $this->db->query($query);
  //   // return $results;
  //   return print_r($query);
  // }
}
