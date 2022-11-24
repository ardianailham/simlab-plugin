<?php

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

  public function getLogBahan()
  {
    $table1 = $this->db->prefix . $this->table;
    $table2 = $this->db->prefix . 'sl_simlab_bahan';
    $query = "SELECT " . $table1 . ".id, " . $table1 . ".qty, " . $table1 . ".user_id, " . $table1 . ".date, " . $table2 . ".Nama_Bahan, " . $table2 . ".Satuan, " . $table2 . ".Serial, " . $table2 . ".Exp, " . $table2 . ".Letak FROM (" . $table1 . " INNER JOIN " . $table2 . " ON " . $table1 . ".id_bahan=" . $table2 . ".id)";
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }

  public function getLogBahanById($id)
  {
    $table1 = $this->db->prefix . $this->table;
    $table2 = $this->db->prefix . 'sl_simlab_bahan';
    $query = $this->db->prepare("SELECT " . $table1 . ".qty, " . $table1 . ".user_id, " . $table1 . ".date, " . $table2 . ".Nama_Bahan, " . $table2 . ".Satuan, " . $table2 . ".Serial, " . $table2 . ".Exp, " . $table2 . ".Letak FROM (" . $table1 . " INNER JOIN " . $table2 . " ON " . $table1 . ".id_bahan=" . $table2 . ".id) WHERE " . $table1 . ".id=%d", $id);
    $results = $this->db->get_row($query, ARRAY_A);
    return $results;
  }

  public function addLogBahan($data)
  {
    $bahan = new SL_SIMLAB_BahanClass;
    $infobahan = $bahan->getBahanById($data['id_bahan']);
    $stok = $infobahan['Jumlah'];
    if ($stok === 0) {
?>
      <script type="text/javascript">
        alert("Stok Habis");
        history.back();
      </script>
    <?php
    } elseif ($data['Qty'] > $stok) {
    ?>
      <script type="text/javascript">
        alert("Jumlah melebihi stok!");
        history.back();
      </script>
<?php
    } else {
      $values = array(
        'id' => $data['id'],
        'id_bahan' => $data['id_bahan'],
        'user_id' => $data['user_id'],
        'qty' => $data['Qty'],
        'date' => $data['tanggal'],
      );
      $this->db->insert(
        $this->db->prefix . $this->table,
        $values
      );
      $bahan->updateByBook($data['id_bahan'], $stok, $data['Qty']);
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
