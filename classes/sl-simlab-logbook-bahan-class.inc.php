<?php
if (! defined('ABSPATH')) {
  exit;
}

class SL_SIMLAB_LogbookBahanClass extends SL_SimlabPlugin
{
  private $table = 'sl_simlab_logbook_bahan';
  public $menu_slug = '-logbook-bahan';
  private $db;

  public function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }

  public function index()
  {
    ob_start();
    include_once dirname(__FILE__) . '/../sl-admin-logbook-bahan.inc.php';
    return ob_get_clean();
  }

  public function getLogBahan($limit = 0, $offset = 0, $search = '', $filter = [])
  {
    $table1 = $this->db->prefix . $this->table;
    $table_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $table2 = $this->db->prefix . 'sl_simlab_bahan';
    $table_users = $this->db->users;

    $where_clauses = [];
    if (!empty($search)) {
      $search_wildcard = '%' . $this->db->esc_like($search) . '%';
      $where_clauses[] = $this->db->prepare("(b.Nama_Bahan LIKE %s OR u.display_name LIKE %s OR u.user_login LIKE %s OR l.tujuan LIKE %s)", $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard);
    }
    if (!empty($filter['tujuan'])) {
      $where_clauses[] = $this->db->prepare("l.tujuan = %s", $filter['tujuan']);
    }

    $where = '';
    if (!empty($where_clauses)) {
      $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $query = "SELECT l.id, l.qty, l.user_id, l.date, b.Nama_Bahan, b.Satuan_Dasar, k.label_kemasan, k.satuan
              FROM {$table1} l 
              INNER JOIN {$table_kemasan} k ON l.id_kemasan = k.id 
              INNER JOIN {$table2} b ON k.id_bahan = b.id 
              INNER JOIN {$table_users} u ON l.user_id = u.ID
              {$where}
              ORDER BY l.date DESC";
    if ($limit > 0) {
      $query .= $this->db->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
    }
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }

  public function getLogBahanCount($search = '', $filter = [])
  {
    $table1 = $this->db->prefix . $this->table;
    $table_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $table2 = $this->db->prefix . 'sl_simlab_bahan';
    $table_users = $this->db->users;

    $where_clauses = [];
    if (!empty($search)) {
      $search_wildcard = '%' . $this->db->esc_like($search) . '%';
      $where_clauses[] = $this->db->prepare("(b.Nama_Bahan LIKE %s OR u.display_name LIKE %s OR u.user_login LIKE %s OR l.tujuan LIKE %s)", $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard);
    }
    if (!empty($filter['tujuan'])) {
      $where_clauses[] = $this->db->prepare("l.tujuan = %s", $filter['tujuan']);
    }

    $where = '';
    if (!empty($where_clauses)) {
      $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $query = "SELECT COUNT(*) 
              FROM {$table1} l 
              INNER JOIN {$table_kemasan} k ON l.id_kemasan = k.id 
              INNER JOIN {$table2} b ON k.id_bahan = b.id 
              INNER JOIN {$table_users} u ON l.user_id = u.ID
              {$where}";
    return intval($this->db->get_var($query));
  }

  public function getDistinctTujuan()
  {
    $table1 = $this->db->prefix . $this->table;
    $query = "SELECT DISTINCT tujuan FROM {$table1} WHERE tujuan != '' AND tujuan IS NOT NULL ORDER BY tujuan ASC";
    return $this->db->get_col($query);
  }

  public function getLogBahanById($id)
  {
    $table1 = $this->db->prefix . $this->table;
    $table_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $table2 = $this->db->prefix . 'sl_simlab_bahan';

    $query = $this->db->prepare("SELECT l.qty, l.user_id, l.date, b.Nama_Bahan, b.Satuan_Dasar, k.label_kemasan, k.satuan
              FROM {$table1} l 
              INNER JOIN {$table_kemasan} k ON l.id_kemasan = k.id 
              INNER JOIN {$table2} b ON k.id_bahan = b.id 
              WHERE l.id = %d", $id);
    $results = $this->db->get_row($query, ARRAY_A);
    return $results;
  }

  public function addLogBahan($data)
  {
    $bahan = new SL_SIMLAB_BahanClass;
    $id_kemasan = $data['id_kemasan'];
    $kemasan = $bahan->getKemasanById($id_kemasan);

    if (!$kemasan) {
      echo '<script type="text/javascript">alert("Kemasan tidak ditemukan!"); history.back();</script>';
      return 0;
    }

    $stok = $kemasan['jumlah_tersedia'];
    if ($stok <= 0) {
      echo '<script type="text/javascript">alert("Stok kemasan habis!"); history.back();</script>';
      return 0;
    } elseif ($data['Qty'] > $stok) {
      echo '<script type="text/javascript">alert("Jumlah melebihi stok kemasan!"); history.back();</script>';
      return 0;
    } else {
      $values = array(
        'id_kemasan' => intval($data['id_kemasan']),
        'user_id'    => get_current_user_id(),
        'qty'        => floatval($data['Qty']),
        'date'       => sanitize_text_field($data['tanggal']),
      );
      if (!empty($data['tujuan'])) {
        $values['tujuan'] = sanitize_text_field($data['tujuan']);
      }

      $this->db->insert(
        $this->db->prefix . $this->table,
        $values
      );

      $bahan->updateByKemasan($data['id_kemasan'], $data['Qty']);
      return $this->db->insert_id;
    }
  }


  public function hapusLog($id)
  {
    $table = $this->db->prefix . $this->table;
    $where = array('id' => $id);
    $results = $this->db->delete($table, $where, array('%d'));
    return $results;
  }
}
