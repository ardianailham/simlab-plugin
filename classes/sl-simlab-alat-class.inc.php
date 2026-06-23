<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
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
  public function getAlat($limit = 0, $offset = 0, $search = '', $filter = [])
  {
    $where_clauses = [];
    if (!empty($search)) {
      $search_wildcard = '%' . $this->db->esc_like($search) . '%';
      $where_clauses[] = $this->db->prepare("(Nama_Alat LIKE %s OR Merk LIKE %s)", $search_wildcard, $search_wildcard);
    }
    if (!empty($filter['merk'])) {
      $where_clauses[] = $this->db->prepare("Merk = %s", $filter['merk']);
    }
    if (!empty($filter['stock_status'])) {
      if ($filter['stock_status'] === 'in_stock') {
        $where_clauses[] = "Qty > 0";
      } elseif ($filter['stock_status'] === 'out_of_stock') {
        $where_clauses[] = "Qty = 0";
      }
    }

    $where = '';
    if (!empty($where_clauses)) {
      $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $query = 'SELECT * FROM ' . $this->db->prefix . $this->table . $where;
    if ($limit > 0) {
      $query .= $this->db->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
    }
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }

  // count total alat
  public function getAlatCount($search = '', $filter = [])
  {
    $where_clauses = [];
    if (!empty($search)) {
      $search_wildcard = '%' . $this->db->esc_like($search) . '%';
      $where_clauses[] = $this->db->prepare("(Nama_Alat LIKE %s OR Merk LIKE %s)", $search_wildcard, $search_wildcard);
    }
    if (!empty($filter['merk'])) {
      $where_clauses[] = $this->db->prepare("Merk = %s", $filter['merk']);
    }
    if (!empty($filter['stock_status'])) {
      if ($filter['stock_status'] === 'in_stock') {
        $where_clauses[] = "Qty > 0";
      } elseif ($filter['stock_status'] === 'out_of_stock') {
        $where_clauses[] = "Qty = 0";
      }
    }

    $where = '';
    if (!empty($where_clauses)) {
      $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $query = 'SELECT COUNT(*) FROM ' . $this->db->prefix . $this->table . $where;
    return intval($this->db->get_var($query));
  }

  // get distinct brands
  public function getDistinctBrands()
  {
    $query = "SELECT DISTINCT Merk FROM " . $this->db->prefix . $this->table . " WHERE Merk != '' AND Merk IS NOT NULL ORDER BY Merk ASC";
    return $this->db->get_col($query);
  }
  // query alat from database berdasar id
  public function getAlatById($id)
  {
    $query = $this->db->prepare("SELECT * FROM " . $this->db->prefix . $this->table . " WHERE id= %d", $id);
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
        'Nama_Alat' => sanitize_text_field($data['Nama_Alat']),
        'Merk' => sanitize_text_field($data['Merk']),
        'Qty' => intval($data['Qty']),
        'gambar' => isset($data['gambar']) ? esc_url_raw($data['gambar']) : ''
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
      "Nama_Alat" => sanitize_text_field($data['Nama_Alat']),
      "Merk" => sanitize_text_field($data['Merk']),
      "Qty" => intval($data['Qty']),
      'gambar' => isset($data['gambar']) ? esc_url_raw($data['gambar']) : ''
    );
    $where = array('id' => intval($data['id']));


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
