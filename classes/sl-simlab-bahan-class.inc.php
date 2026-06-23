<?php
if (! defined('ABSPATH')) {
  exit;
}

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
  public function getBahan($limit = 0, $offset = 0, $search = '', $filter = [])
  {
    $t_bahan = $this->db->prefix . $this->table;
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';

    $where_clauses = [];
    if (!empty($search)) {
      $search_wildcard = '%' . $this->db->esc_like($search) . '%';
      $where_clauses[] = $this->db->prepare("(b.Nama_Bahan LIKE %s OR b.Alias LIKE %s OR b.Merk LIKE %s)", $search_wildcard, $search_wildcard, $search_wildcard);
    }
    if (!empty($filter['kategori'])) {
      $where_clauses[] = $this->db->prepare("b.Kategori = %s", $filter['kategori']);
    }

    $where = '';
    if (!empty($where_clauses)) {
      $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $having = '';
    if (!empty($filter['stock_status'])) {
      if ($filter['stock_status'] === 'in_stock') {
        $having = " HAVING StokTotal > 0";
      } elseif ($filter['stock_status'] === 'out_of_stock') {
        $having = " HAVING StokTotal = 0";
      }
    }

    $query = "SELECT b.id, b.Nama_Bahan, b.Alias, b.Kategori, b.Merk, b.Satuan_Dasar, b.ghs_code, b.hazard_statement, b.signal_word, b.created_at, 
              IFNULL(SUM(k.jumlah_tersedia), 0) as StokTotal,
              IFNULL(SUM(k.kapasitas_awal), 0) as KapasitasMax 
              FROM {$t_bahan} b 
              LEFT JOIN {$t_kemasan} k ON b.id = k.id_bahan 
              {$where}
              GROUP BY b.id
              {$having}";

    if ($limit > 0) {
      $query .= $this->db->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
    }
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }

  // count total Bahan
  public function getBahanCount($search = '', $filter = [])
  {
    $t_bahan = $this->db->prefix . $this->table;
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';

    $where_clauses = [];
    if (!empty($search)) {
      $search_wildcard = '%' . $this->db->esc_like($search) . '%';
      $where_clauses[] = $this->db->prepare("(b.Nama_Bahan LIKE %s OR b.Alias LIKE %s OR b.Merk LIKE %s)", $search_wildcard, $search_wildcard, $search_wildcard);
    }
    if (!empty($filter['kategori'])) {
      $where_clauses[] = $this->db->prepare("b.Kategori = %s", $filter['kategori']);
    }

    $where = '';
    if (!empty($where_clauses)) {
      $where = ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $having = '';
    if (!empty($filter['stock_status'])) {
      if ($filter['stock_status'] === 'in_stock') {
        $having = " HAVING StokTotal > 0";
      } elseif ($filter['stock_status'] === 'out_of_stock') {
        $having = " HAVING StokTotal = 0";
      }
    }

    if (empty($having)) {
      $query = "SELECT COUNT(*) FROM {$t_bahan} b {$where}";
      return intval($this->db->get_var($query));
    } else {
      $query = "SELECT COUNT(StokTotal) FROM (
                  SELECT IFNULL(SUM(k.jumlah_tersedia), 0) as StokTotal
                  FROM {$t_bahan} b 
                  LEFT JOIN {$t_kemasan} k ON b.id = k.id_bahan 
                  {$where}
                  GROUP BY b.id
                  {$having}
                ) as temp_table";
      return intval($this->db->get_var($query));
    }
  }

  // get distinct categories
  public function getDistinctCategories()
  {
    $t_bahan = $this->db->prefix . $this->table;
    $query = "SELECT DISTINCT Kategori FROM {$t_bahan} WHERE Kategori != '' AND Kategori IS NOT NULL ORDER BY Kategori ASC";
    return $this->db->get_col($query);
  }

  // query Bahan from database berdasar id
  public function getBahanById($id)
  {
    $t_bahan = $this->db->prefix . $this->table;
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';

    $query = $this->db->prepare("SELECT b.*, IFNULL(SUM(k.jumlah_tersedia), 0) as TotalJumlah 
              FROM {$t_bahan} b 
              LEFT JOIN {$t_kemasan} k ON b.id = k.id_bahan 
              WHERE b.id = %d GROUP BY b.id", $id);
    $results = $this->db->get_row($query, ARRAY_A);
    return $results;
  }

  // tambah Bahan ke database
  public function tambahBahan($data)
  {
    $table = $this->db->prefix . $this->table;

    $ghs_input = isset($data['ghs_code']) ? sanitize_text_field($data['ghs_code']) : '';
    $ghs_array = array_filter(array_map('trim', explode(',', $ghs_input)));

    $hazard_input = isset($data['hazard_statement']) ? sanitize_textarea_field($data['hazard_statement']) : '';
    $hazard_array = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $hazard_input))));

    $signal_word = isset($data['signal_word']) ? sanitize_text_field($data['signal_word']) : '';

    $values = array(
      'Nama_Bahan'       => sanitize_text_field($data['Nama_Bahan'] ?? ''),
      'Alias'            => sanitize_text_field($data['Alias'] ?? ''),
      'Kategori'         => sanitize_text_field($data['Kategori'] ?? ''),
      'Merk'             => sanitize_text_field($data['Merk'] ?? ''),
      'Satuan_Dasar'     => sanitize_text_field($data['Satuan_Dasar'] ?? ''),
      'ghs_code'         => maybe_serialize($ghs_array),
      'hazard_statement' => maybe_serialize($hazard_array),
      'signal_word'      => $signal_word,
      'gambar'           => isset($data['gambar']) ? esc_url_raw($data['gambar']) : ''
    );
    $results = $this->db->insert($table, $values);
    return $results;
  }

  // update Bahan
  public function ubahBahan($data)
  {
    $table = $this->db->prefix . $this->table;

    $ghs_input = isset($data['ghs_code']) ? sanitize_text_field($data['ghs_code']) : '';
    $ghs_array = array_filter(array_map('trim', explode(',', $ghs_input)));

    $hazard_input = isset($data['hazard_statement']) ? sanitize_textarea_field($data['hazard_statement']) : '';
    $hazard_array = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $hazard_input))));

    $signal_word = isset($data['signal_word']) ? sanitize_text_field($data['signal_word']) : '';

    $value = array(
      'Nama_Bahan'       => sanitize_text_field($data['Nama_Bahan'] ?? ''),
      'Alias'            => sanitize_text_field($data['Alias'] ?? ''),
      'Kategori'         => sanitize_text_field($data['Kategori'] ?? ''),
      'Merk'             => sanitize_text_field($data['Merk'] ?? ''),
      'Satuan_Dasar'     => sanitize_text_field($data['Satuan_Dasar'] ?? ''),
      'ghs_code'         => maybe_serialize($ghs_array),
      'hazard_statement' => maybe_serialize($hazard_array),
      'signal_word'      => $signal_word,
      'gambar'           => isset($data['gambar']) ? esc_url_raw($data['gambar']) : ''
    );
    $where = array('id' => intval($data['id']));

    $results = $this->db->update($table, $value, $where);
    return $results;
  }

  // hapus Bahan
  public function hapusBahan($id)
  {
    $table = $this->db->prefix . $this->table;
    $where = array('id' => $id);

    // Cascade hapus kemasan
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $this->db->delete($t_kemasan, array('id_bahan' => $id), array('%d'));

    $results = $this->db->delete($table, $where, array('%d'));
    return $results;
  }

  // --- KEMASAN FUNCTIONS ---

  public function getKemasanByBahan($id_bahan)
  {
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $query = $this->db->prepare("SELECT * FROM {$t_kemasan} WHERE id_bahan = %d ORDER BY created_at DESC", $id_bahan);
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }

  public function getKemasanById($id)
  {
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $query = $this->db->prepare("SELECT * FROM {$t_kemasan} WHERE id = %d", $id);
    return $this->db->get_row($query, ARRAY_A);
  }

  public function tambahKemasan($data)
  {
    $table = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $values = array(
      'id_bahan'        => intval($data['id_bahan']),
      'label_kemasan'   => sanitize_text_field($data['label_kemasan'] ?? ''),
      'kapasitas_awal'  => floatval(str_replace(',', '.', $data['kapasitas_awal'] ?? 0)),
      'jumlah_tersedia' => floatval(str_replace(',', '.', $data['kapasitas_awal'] ?? 0)),
      'satuan'          => sanitize_text_field($data['satuan'] ?? ''),
      'exp_date'        => (!empty($data['exp_date'])) ? sanitize_text_field($data['exp_date']) : null,
      'letak'           => sanitize_text_field($data['letak'] ?? ''),
      'catatan_kondisi' => sanitize_text_field($data['catatan_kondisi'] ?? ''),
      'is_empty'        => 0
    );
    return $this->db->insert($table, $values);
  }

  public function hapusKemasan($id)
  {
    $table = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $where = array('id' => $id);
    return $this->db->delete($table, $where, array('%d'));
  }

  // sinkronisasi dengan logbook
  public function updateByKemasan($id_kemasan, $ambil)
  {
    $table = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $kemasan = $this->getKemasanById($id_kemasan);

    if (!$kemasan) return false;

    $Jumlah = $kemasan['jumlah_tersedia'] - $ambil;
    $is_empty = ($Jumlah <= 0) ? 1 : 0;

    $query = $this->db->prepare("UPDATE {$table} SET jumlah_tersedia = %f, is_empty = %d WHERE id = %d", $Jumlah, $is_empty, $id_kemasan);
    return $this->db->query($query);
  }

  // restock kemasan
  public function restockKemasan($id_kemasan, $tambah)
  {
    $table = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $kemasan = $this->getKemasanById($id_kemasan);

    if (!$kemasan) return false;

    $Jumlah = $kemasan['jumlah_tersedia'] + $tambah;
    $is_empty = ($Jumlah <= 0) ? 1 : 0;

    $query = $this->db->prepare("UPDATE {$table} SET jumlah_tersedia = %f, is_empty = %d WHERE id = %d", $Jumlah, $is_empty, $id_kemasan);
    return $this->db->query($query);
  }

  // ubah kemasan
  public function ubahKemasan($data)
  {
    $table = $this->db->prefix . 'sl_simlab_bahan_kemasan';
    $id_kemasan = intval($data['id_kemasan']);

    $jumlah_tersedia = floatval(str_replace(',', '.', $data['jumlah_tersedia'] ?? 0));
    $is_empty = ($jumlah_tersedia <= 0) ? 1 : 0;

    $values = array(
      'label_kemasan'   => sanitize_text_field($data['label_kemasan'] ?? ''),
      'kapasitas_awal'  => floatval(str_replace(',', '.', $data['kapasitas_awal'] ?? 0)),
      'jumlah_tersedia' => $jumlah_tersedia,
      'satuan'          => sanitize_text_field($data['satuan'] ?? ''),
      'exp_date'        => (!empty($data['exp_date'])) ? sanitize_text_field($data['exp_date']) : null,
      'letak'           => sanitize_text_field($data['letak'] ?? ''),
      'catatan_kondisi' => sanitize_text_field($data['catatan_kondisi'] ?? ''),
      'is_empty'        => $is_empty
    );
    $where = array('id' => $id_kemasan);

    return $this->db->update($table, $values, $where);
  }
}

