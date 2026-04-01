<?php
require_once 'classes/sl-simlab-alat-class.inc.php';
require_once 'classes/sl-simlab-logbook-alat-class.inc.php';

$obj     = new SL_SIMLAB_LogbookAlatClass;
$user    = get_current_user();
$user_id = get_current_user_id();

SL_SimlabPlugin::admin_header('Logbook Peminjaman Alat', 'fa-calendar');

/* ── DELETE ──────────────────────────────────────────────────────────────── */
if (isset($_GET['hapus'])) {
  $id      = intval($_GET['id']);
  $booking = $obj->getLogAlatById($id);

  if (!SL_SIMLAB_Auth::can_delete_log($user_id, $booking['user_id'])) {
    wp_die(__('You do not have permission to perform this action.'));
  }

  if ($obj->hapusLog($id) > 0) {
    echo "<script>alert('Data Berhasil Dihapus'); document.location = '?page=".esc_js($obj->plugin_slug.$obj->menu_slug)."';</script>";
  } else {
    echo "<script>alert('Gagal!'); history.back();</script>";
  }

/* ── DETAIL ──────────────────────────────────────────────────────────────── */
} elseif (isset($_GET['detail'])) {
  $id   = intval($_GET['id']);
  $data = $obj->getLogAlatById($id);
?>
  <div class="row d-flex justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title fw-bold text-primary mb-4"><i class="fa fa-info-circle me-2"></i>Detail Peminjaman</h5>
          <table class="table table-sm border-0">
            <tr><th width="40%">Alat</th><td>: <?= esc_html($data['Nama_Alat']); ?></td></tr>
            <tr><th>Peminjam</th><td>: <?= esc_html(get_userdata($data['user_id'])->display_name); ?></td></tr>
            <tr><th>Jumlah Pinjam</th><td>: <?= esc_html($data['qty']); ?> Unit</td></tr>
            <tr><th>Waktu Mulai</th><td>: <?= esc_html(date('d M Y H:i', $data['start_date'])); ?></td></tr>
            <tr><th>Waktu Selesai</th><td>: <?= esc_html(date('d M Y H:i', $data['end_date'])); ?></td></tr>
            <tr><th>Status Saat Ini</th><td>: <span class="badge bg-info"><?= esc_html($data['name']); ?></span></td></tr>
          </table>
          <div class="mt-4">
             <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>" class="btn btn-secondary btn-sm">Kembali</a>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php
/* ── LIST (default) ──────────────────────────────────────────────────────── */
} else {
  $data = $obj->getLogAlat();
  
  // Format data for FullCalendar
  $events = [];
  foreach ($data as $booking) {
      $user_data = get_userdata($booking['user_id']);
      $user_name = $user_data ? $user_data->display_name : 'Unknown';
      
      $color = '#6c757d'; // Default secondary
      $text_color = '#ffffff';
      if ($booking['name'] == 'Accepted' || $booking['name'] == 'Completed') $color = '#198754'; // Success
      if ($booking['name'] == 'Pending') { $color = '#ffc107'; $text_color = '#000000'; } // Warning
      if ($booking['name'] == 'Rejected') $color = '#dc3545'; // Danger
      if ($booking['name'] == 'Ongoing') $color = '#0dcaf0'; // Info

      $events[] = [
          'id'      => $booking['id'],
          'title'   => '[' . $booking['qty'] . '] ' . $booking['Nama_Alat'] . ' (' . $user_name . ')',
          'start'   => date('c', $booking['start_date']),
          'end'     => date('c', $booking['end_date']),
          'backgroundColor' => $color,
          'borderColor'     => $color,
          'textColor'       => $text_color,
          'extendedProps'   => [
              'status' => $booking['name'],
              'user'   => $user_name,
              'alat'   => $booking['Nama_Alat'],
              'qty'    => $booking['qty']
          ]
      ];
  }
?>
  <!-- FullCalendar Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="btn-group shadow-sm" role="group">
      <button type="button" class="btn btn-primary active" id="view-calendar-btn" onclick="switchView('calendar')"><i class="fa fa-calendar me-1"></i> Kalender</button>
      <button type="button" class="btn btn-outline-primary" id="view-list-btn" onclick="switchView('list')"><i class="fa fa-list me-1"></i> Daftar</button>
    </div>
    <?php 
    $links = get_option('sl_simlab_links', []);
    $add_booking_url = is_admin() ? '?page=simlab-daftar-alat' : ($links['daftar-alat'] ?? '#');
    ?>
    <a href="<?= esc_url($add_booking_url); ?>" class="btn btn-success shadow-sm"><i class="fa fa-plus me-1"></i> Tambah Booking</a>
  </div>

  <!-- Calendar View -->
  <div id="calendar-view" class="bg-white p-3 rounded shadow-sm border">
    <div id="calendar"></div>
  </div>

  <!-- List View (Hidden by default) -->
  <div id="list-view" style="display: none;">
    <div class="table-responsive">
      <table class="table table-hover align-middle border bg-white">
        <thead class="table-light">
          <tr>
            <th width="40">No</th>
            <th>Nama Alat</th>
            <th>Peminjam</th>
            <th width="80">Qty</th>
            <th>Waktu Pinjam</th>
            <th width="120">Status</th>
            <th width="150" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; ?>
          <?php if (empty($data)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat peminjaman.</td></tr>
          <?php endif; ?>
          <?php foreach ($data as $booking) : ?>
            <tr>
              <td><?= $i; ?></td>
              <td class="fw-bold"><?= esc_html($booking['Nama_Alat']); ?></td>
              <td><i class="fa fa-user-circle-o me-1 text-muted"></i> <?= esc_html(get_userdata($booking['user_id'])->display_name ?? 'Unknown'); ?></td>
              <td><?= esc_html($booking['qty']); ?></td>
              <td class="small">
                <div class="text-success"><i class="fa fa-arrow-right"></i> <?= date('d M Y H:i', $booking['start_date']); ?></div>
                <div class="text-danger"><i class="fa fa-arrow-left"></i> <?= date('d M Y H:i', $booking['end_date']); ?></div>
              </td>
              <td>
                <?php 
                $status_class = 'bg-secondary';
                if ($booking['name'] == 'Accepted' || $booking['name'] == 'Completed') $status_class = 'bg-success';
                if ($booking['name'] == 'Pending') $status_class = 'bg-warning text-dark';
                if ($booking['name'] == 'Rejected') $status_class = 'bg-danger';
                if ($booking['name'] == 'Ongoing') $status_class = 'bg-info';
                ?>
                <span class="badge <?= $status_class; ?>"><?= esc_html($booking['name']); ?></span>
              </td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&detail&id=<?= intval($booking['id']); ?>"
                      class="btn btn-sm btn-outline-primary" title="Detail"><i class="fa fa-eye"></i></a>
                  <?php if (SL_SIMLAB_Auth::can_delete_log($user_id, $booking['user_id'])) { ?>
                    <a href="?page=<?= esc_attr($obj->plugin_slug . $obj->menu_slug); ?>&hapus&id=<?= intval($booking['id']); ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Hapus logbook ini?');" title="Hapus"><i class="fa fa-trash"></i></a>
                  <?php } ?>
                </div>
              </td>
            </tr>
            <?php $i++; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function switchView(view) {
        if (view === 'calendar') {
            document.getElementById('calendar-view').style.display = 'block';
            document.getElementById('list-view').style.display = 'none';
            document.getElementById('view-calendar-btn').classList.add('active', 'btn-primary');
            document.getElementById('view-calendar-btn').classList.remove('btn-outline-primary');
            document.getElementById('view-list-btn').classList.remove('active', 'btn-primary');
            document.getElementById('view-list-btn').classList.add('btn-outline-primary');
            window.calendar.render();
        } else {
            document.getElementById('calendar-view').style.display = 'none';
            document.getElementById('list-view').style.display = 'block';
            document.getElementById('view-list-btn').classList.add('active', 'btn-primary');
            document.getElementById('view-list-btn').classList.remove('btn-outline-primary');
            document.getElementById('view-calendar-btn').classList.remove('active', 'btn-primary');
            document.getElementById('view-calendar-btn').classList.add('btn-outline-primary');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      window.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?= json_encode($events); ?>,
        eventClick: function(info) {
          if (confirm('Lihat detail booking untuk ' + info.event.extendedProps.alat + '?')) {
            window.location.href = '?page=<?= esc_js($obj->plugin_slug . $obj->menu_slug); ?>&detail&id=' + info.event.id;
          }
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        height: 'auto',
        themeSystem: 'standard',
        nowIndicator: true,
        businessHours: {
          daysOfWeek: [ 1, 2, 3, 4, 5 ],
          startTime: '08:00',
          endTime: '17:00',
        }
      });
      window.calendar.render();
    });
  </script>

  <style>
    #calendar {
      max-width: 100%;
      margin: 0 auto;
      font-size: 0.9rem;
    }
    .fc-header-toolbar {
      margin-bottom: 1.5rem !important;
    }
    .fc-button-primary {
      background-color: #0d6efd !important;
      border-color: #0d6efd !important;
    }
    .fc-event {
      cursor: pointer;
      border-radius: 4px;
      padding: 2px 4px;
      font-size: 0.8rem;
    }
    .fc-v-event {
        border: none;
    }
    .fc-event-main {
        font-weight: 500;
    }
  </style>
<?php
}
SL_SimlabPlugin::admin_footer();
?>