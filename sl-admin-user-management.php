<?php

if (!SL_SIMLAB_Auth::is_admin()) {
    wp_die('Unauthorized');
}

$options = get_option('sl_simlab_links');
$api_url = isset($options['user-api-url']) ? $options['user-api-url'] : '';

SL_SimlabPlugin::admin_header('User Management', 'fa-users');

/* ── ROLE UPDATE ─────────────────────────────────────────────────────────── */
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $role_id = intval($_POST['role_id']);
    SL_SIMLAB_Auth::set_user_role($user_id, $role_id);
    echo '<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            User role configuration updated for <strong>' . get_userdata($user_id)->display_name . '</strong>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

/* ── API SYNC ─────────────────────────────────────────────────────────────── */
if (isset($_POST['sync_api'])) {
    if (!empty($api_url)) {
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            echo '<div class="alert alert-danger mb-4">API Sync Error: ' . $response->get_error_message() . '</div>';
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if ($data && is_array($data)) {
                $count = 0;
                foreach ($data as $ext_user) {
                    $email = isset($ext_user['email']) ? $ext_user['email'] : '';
                    $username = isset($ext_user['username']) ? $ext_user['username'] : $email;
                    
                    if (!email_exists($email)) {
                        $new_user_id = wp_create_user($username, wp_generate_password(), $email);
                        if (!is_wp_error($new_user_id)) {
                            SL_SIMLAB_Auth::set_user_role($new_user_id, 3); // Default Member
                            $count++;
                        }
                    }
                }
                echo '<div class="alert alert-success mb-4">Synced ' . $count . ' new users from API successfully.</div>';
            } else {
                echo '<div class="alert alert-warning mb-4">API returned invalid user format.</div>';
            }
        }
    } else {
        echo '<div class="alert alert-warning mb-4">Please configure the User API URL in Settings first.</div>';
    }
}

$users = get_users();
$roles = SL_SIMLAB_Auth::get_roles();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0">Kelola dan tentukan peran akses SIMLAB bagi setiap pengguna WordPress.</p>
            <form method="POST">
                <button type="submit" name="sync_api" class="btn btn-info text-white shadow-sm">
                    <i class="fa fa-refresh me-1"></i> Sync from External API
                </button>
            </form>
        </div>
        
        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-hover align-middle border mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User Info</th>
                        <th width="300">Simlab Role Assignment</th>
                        <th width="150">System Permissions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <?php $current_role = SL_SIMLAB_Auth::get_user_role($user->ID); ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                         <i class="fa fa-user-circle fa-2x text-muted opacity-50"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= esc_html($user->display_name); ?></div>
                                        <small class="text-muted"><?= esc_html($user->user_email); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="user_id" value="<?= $user->ID; ?>">
                                    <select name="role_id" class="form-select form-select-sm shadow-none">
                                        <?php foreach ($roles as $id => $name) : ?>
                                            <option value="<?= $id; ?>" <?= $current_role === $id ? 'selected' : ''; ?>>
                                                <?= esc_html($name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_role" class="btn btn-sm btn-primary">Save</button>
                                </form>
                            </td>
                            <td>
                                <?php if (user_can($user->ID, 'manage_options')) : ?>
                                    <span class="badge bg-dark">WordPress Admin</span>
                                <?php else : ?>
                                    <small class="text-muted">Standard User</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php SL_SimlabPlugin::admin_footer(); ?>
