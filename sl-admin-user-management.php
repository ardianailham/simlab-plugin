<?php

if (!SL_SIMLAB_Auth::is_admin()) {
    wp_die('Unauthorized');
}

$options = get_option('sl_simlab_links');
$api_url = isset($options['user-api-url']) ? $options['user-api-url'] : '';

/* ── ROLE UPDATE ─────────────────────────────────────────────────────────── */
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $role_id = intval($_POST['role_id']);
    SL_SIMLAB_Auth::set_user_role($user_id, $role_id);
    echo '<div class="notice notice-success is-dismissible"><p>User role updated.</p></div>';
}

/* ── API SYNC ─────────────────────────────────────────────────────────────── */
if (isset($_POST['sync_api'])) {
    if (!empty($api_url)) {
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            echo '<div class="notice notice-error is-dismissible"><p>API Sync Error: ' . $response->get_error_message() . '</p></div>';
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if ($data && is_array($data)) {
                $count = 0;
                foreach ($data as $ext_user) {
                    // Assuming API returns an array of objects with {email, username, name}
                    $email = isset($ext_user['email']) ? $ext_user['email'] : '';
                    $username = isset($ext_user['username']) ? $ext_user['username'] : $email;
                    
                    if (email_exists($email)) {
                        // User exists, maybe update something?
                    } else {
                        $user_id = wp_create_user($username, wp_generate_password(), $email);
                        if (!is_wp_error($user_id)) {
                            // Default all synced users to Member (3)
                            SL_SIMLAB_Auth::set_user_role($user_id, 3);
                            $count++;
                        }
                    }
                }
                echo '<div class="notice notice-success is-dismissible"><p>Synced ' . $count . ' new users from API.</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>API returned invalid format.</p></div>';
            }
        }
    } else {
        echo '<div class="notice notice-warning is-dismissible"><p>Please configure the User API URL in Settings first.</p></div>';
    }
}

$users = get_users();
$roles = SL_SIMLAB_Auth::get_roles();
?>

<div class="row mt-4">
    <div class="col-lg-10">
        <h1>User Management</h1>
        <p>Assign specific Simlab roles to WordPress users.</p>
        
        <form method="POST" class="mb-4">
            <button type="submit" name="sync_api" class="btn btn-info">Sync Users from External API</button>
            <small class="text-muted d-block mt-1">API URL: <?= esc_html($api_url ?: 'None configured'); ?></small>
        </form>

        <table class="table table-bordered table-striped" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Simlab Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <?php 
                    $current_role = SL_SIMLAB_Auth::get_user_role($user->ID); 
                    ?>
                    <tr>
                        <td><?= esc_html($user->user_login); ?></td>
                        <td><?= esc_html($user->user_email); ?></td>
                        <td>
                            <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                                <input type="hidden" name="user_id" value="<?= $user->ID; ?>">
                                <select name="role_id" class="form-select form-select-sm" style="width: auto;">
                                    <?php foreach ($roles as $id => $name) : ?>
                                        <option value="<?= $id; ?>" <?= $current_role === $id ? 'selected' : ''; ?>>
                                            <?= esc_html($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_role" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                        <td>
                            <?php if (user_can($user->ID, 'manage_options')) : ?>
                                <span class="badge bg-secondary">System Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
