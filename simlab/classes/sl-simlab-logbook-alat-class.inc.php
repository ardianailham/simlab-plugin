<?php

class SL_SIMLAB_LogbookAlatClass extends SL_SimlabPlugin
{
  private $table = 'sl_simlab_logbook_alat';
  public $menu_slug = '-logbook-alat';
  private $db;

  public function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }

  public function index()
  {
    ob_start();
    include_once dirname(__FILE__) . '/../sl-admin-logbook-alat.inc.php';
    return ob_get_clean();
  }

  public function getLogAlat()
  {
    $table1 = $this->db->prefix . $this->table;
    $table2 = $this->db->prefix . 'sl_simlab_alat';
    $table3 = $this->db->prefix . 'sl_simlab_status';
    $query = "SELECT " . $table1 . ".id, " . $table1 . ".qty, " . $table1 . ".user_id, " . $table1 . ".start_date, " . $table1 . ".end_date, " . $table2 . ".Nama_Alat, " . $table3 . ".name FROM ((" . $table1 . " INNER JOIN " . $table2 . " ON " . $table1 . ".id_alat=" . $table2 . ".id) INNER JOIN " . $table3 . " ON " . $table1 . ".status=" . $table3 . ".id)";
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
    // return $query;
  }

  public function getLogAlatById($id)
  {
    $table1 = $this->db->prefix . $this->table;
    $table2 = $this->db->prefix . 'sl_simlab_alat';
    $table3 = $this->db->prefix . 'sl_simlab_status';
    $query = $this->db->prepare("SELECT " . $table1 . ".id, " . $table1 . ".qty, " . $table1 . ".user_id, " . $table1 . ".start_date, " . $table1 . ".end_date, " . $table2 . ".Nama_Alat, " . $table3 . ".name FROM ((" . $table1 . " INNER JOIN " . $table2 . " ON " . $table1 . ".id_alat=" . $table2 . ".id) INNER JOIN " . $table3 . " ON " . $table1 . ".status=" . $table3 . ".id) WHERE " . $table1 . ".id=" . $id);
    $results = $this->db->get_row($query, ARRAY_A);
    return $results;
  }
  public function getLogAlatQtyByAlat($id_alat)
  {
    // $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id_alat=:id_alat');
    $table = $this->db->prefix . $this->table;
    $query = $this->db->prepare('SELECT SUM(qty) AS Qty FROM ' . $table . ' WHERE id_alat=%d AND status=5', $id_alat); #status belum disertakan
    $results = $this->db->get_row($query, ARRAY_A);

    // return $this->db->show_errors($results);
    return $results;
  }

  public function getLogAlatScheduleByAlat($id_alat, $sd, $ed)
  {
    $table = $this->db->prefix . $this->table;
    $query = $this->db->prepare("SELECT * FROM " . $table . " WHERE id_alat = %d AND NOT (start_date > %d OR end_date < %d )", $id_alat, $ed, $sd);
    $results = $this->db->get_results($query, ARRAY_A);
    return $results;
  }


  // Tambah Log Pinjam Alat
  public function addLogAlat($data)
  {
    // Ubah data waktu ke Unix timestamp
    $data['start_date'] = strtotime($data['start_date']);
    $data['end_date'] = strtotime($data['end_date']);

    // cek apakah jadwal yang dimasukkan benar
    if ($data['end_date'] < $data['start_date']) {
?>
      <script type="text/javascript">
        alert("Tanggal yang dimasukkan salah!");
        history.back();
      </script>
      <?php
    }

    // cek apakah ada jadwal yang telah diambil 
    $cekjadwal = $this->getLogAlatScheduleByAlat($data['id_alat'], $data['start_date'], $data['end_date']);

    if (!empty($cekjadwal)) {
      // var_dump($cekjadwal);
      $var1 = $data['Qty'];

      $cekQty = $this->getLogAlatQtyByAlat($data['id_alat']);
      $var2 = $cekQty['Qty'];

      $infoalat = new SL_SIMLAB_AlatClass;
      $infoalat = $infoalat->getAlatById($data['id_alat']);
      $var3 = $infoalat['Qty'];
      if ($var1 + $var2 <= $var3) {
        $values = array(
          'id' => $data['id'],
          'id_alat' => $data['id_alat'],
          'user_id' => $data['user_id'],
          'qty' => $data['Qty'],
          'start_date' => $data['start_date'],
          'end_date' => $data['end_date'],
          'status' => 5
        );
        $results = $this->db->insert(
          $this->db->prefix . $this->table,
          $values
        );
        return $results;
      } else {

      ?>
        <script type="text/javascript">
          alert("Jadwal Penuh");
          history.back();
        </script>
<?php
      }
    } else {
      $values = array(
        'id' => $data['id'],
        'id_alat' => $data['id_alat'],
        'user_id' => $data['user_id'],
        'qty' => $data['Qty'],
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'status' => 5
      );
      $results = $this->db->insert(
        $this->db->prefix . $this->table,
        $values
      );
      return $results;
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
