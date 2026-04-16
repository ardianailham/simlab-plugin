<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

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
    $query = $this->db->prepare("SELECT " . $table1 . ".id, " . $table1 . ".qty, " . $table1 . ".user_id, " . $table1 . ".start_date, " . $table1 . ".end_date, " . $table2 . ".Nama_Alat, " . $table3 . ".name FROM ((" . $table1 . " INNER JOIN " . $table2 . " ON " . $table1 . ".id_alat=" . $table2 . ".id) INNER JOIN " . $table3 . " ON " . $table1 . ".status=" . $table3 . ".id) WHERE " . $table1 . ".id=%d", $id);
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
    // Ubah data waktu ke Unix timestamp (menggunakan timezone WordPress)
    $wp_tz = wp_timezone();
    $start_dt = new DateTime($data['start_date'], $wp_tz);
    $end_dt = new DateTime($data['end_date'], $wp_tz);
    $start_ts = $start_dt->getTimestamp();
    $end_ts = $end_dt->getTimestamp();

    // cek apakah jadwal yang dimasukkan benar
    if (!$start_ts || !$end_ts || $end_ts <= $start_ts) {
      ?>
      <script type="text/javascript">
        alert("Tanggal yang dimasukkan salah!");
        history.back();
      </script>
      <?php
      return 0;
    }

    $id_alat = intval($data['id_alat']);
    $qty_requested = intval($data['Qty']);

    // Get overlapping bookings with status 5 (Accepted) or 1 (Ongoing) or 3 (Pending)
    // Actually, we should probably consider anything that isn't Rejected (4) or Completed (2) as "taking up space"
    // For now, let's follow the existing pattern but fix the quantity check.

    $overlapping = $this->getLogAlatScheduleByAlat($id_alat, $start_ts, $end_ts);

    // Get total stock
    $infoalat = new SL_SIMLAB_AlatClass;
    $alat_data = $infoalat->getAlatById($id_alat);
    $total_stock = intval($alat_data['Qty']);

    // Calculate max concurrent usage during the requested interval [start_ts, end_ts]
    $max_concurrent = 0;

    // We check usage at the start of our booking, and then at every event that starts within our booking
    $check_points = [$start_ts];
    foreach ($overlapping as $res) {
      if ($res['start_date'] > $start_ts && $res['start_date'] < $end_ts) {
        $check_points[] = $res['start_date'];
      }
    }
    $check_points = array_unique($check_points);

    foreach ($check_points as $t) {
      $usage_at_t = 0;
      foreach ($overlapping as $res) {
        // A booking is active at time 't' if start <= t AND end > t
        if ($res['start_date'] <= $t && $res['end_date'] > $t) {
          // Only count Approved or Pending or Ongoing
          if (in_array($res['status'], [1, 3, 5])) {
            $usage_at_t += intval($res['qty']);
          }
        }
      }
      if ($usage_at_t > $max_concurrent)
        $max_concurrent = $usage_at_t;
    }

    if ($max_concurrent + $qty_requested <= $total_stock) {
      $values = array(
        'id_alat' => $id_alat,
        'user_id' => get_current_user_id(),
        'qty' => $qty_requested,
        'start_date' => $start_ts,
        'end_date' => $end_ts,
        'status' => 5 // Default to Accepted
      );
      $results = $this->db->insert(
        $this->db->prefix . $this->table,
        $values
      );

      if ($results === false) {
        $error = $this->db->last_error;
        ?>
        <script type="text/javascript">
          alert("Gagal menyimpan ke database! Silakan coba lagi atau hubungi administrator.");
          history.back();
        </script>
        <?php
        return 0;
      }
      return $results;
    } else {
      ?>
      <script type="text/javascript">
        alert("Jadwal Penuh atau Stok Tidak Mencukupi pada waktu tersebut! \n\nMaksimal penggunaan bersamaan: " + <?= intval($max_concurrent); ?> + " dari " + <?= intval($total_stock); ?> + " unit.");
        history.back();
      </script>
      <?php
      return 0;
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
