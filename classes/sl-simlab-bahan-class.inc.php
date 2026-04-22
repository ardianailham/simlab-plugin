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
  public function getBahan()
  {
    $t_bahan = $this->db->prefix . $this->table;
    $t_kemasan = $this->db->prefix . 'sl_simlab_bahan_kemasan';

    $query = "SELECT b.id, b.Nama_Bahan, b.Alias, b.Kategori, b.Merk, b.Satuan_Dasar, b.created_at, 
              IFNULL(SUM(k.jumlah_tersedia), 0) as StokTotal,
              IFNULL(SUM(k.kapasitas_awal), 0) as KapasitasMax 
              FROM {$t_bahan} b 
              LEFT JOIN {$t_kemasan} k ON b.id = k.id_bahan 
              GROUP BY b.id";
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
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
    $values = array(
      'Nama_Bahan'   => sanitize_text_field($data['Nama_Bahan'] ?? ''),
      'Alias'        => sanitize_text_field($data['Alias'] ?? ''),
      'Kategori'     => sanitize_text_field($data['Kategori'] ?? ''),
      'Merk'         => sanitize_text_field($data['Merk'] ?? ''),
      'Satuan_Dasar' => sanitize_text_field($data['Satuan_Dasar'] ?? '')
    );
    $results = $this->db->insert($table, $values);
    return $results;
  }

  // update Bahan
  public function ubahBahan($data)
  {
    $table = $this->db->prefix . $this->table;
    $value = array(
      'Nama_Bahan'   => sanitize_text_field($data['Nama_Bahan'] ?? ''),
      'Alias'        => sanitize_text_field($data['Alias'] ?? ''),
      'Kategori'     => sanitize_text_field($data['Kategori'] ?? ''),
      'Merk'         => sanitize_text_field($data['Merk'] ?? ''),
      'Satuan_Dasar' => sanitize_text_field($data['Satuan_Dasar'] ?? '')
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
}
