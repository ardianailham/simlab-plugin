<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class SL_SIMLAB_Auth
{
    private static $roles = [
        1 => 'Admin',
        2 => 'Leader',
        3 => 'Member'
    ];

    public static function get_roles()
    {
        return self::$roles;
    }

    public static function get_user_role($user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Standard WP administrator is always Admin
        if (user_can($user_id, 'manage_options')) {
            return 1;
        }

        $role = get_user_meta($user_id, 'sl_simlab_role', true);
        return $role ? intval($role) : 3; // Default to Member
    }

    public static function set_user_role($user_id, $role_id)
    {
        if (array_key_exists($role_id, self::$roles)) {
            return update_user_meta($user_id, 'sl_simlab_role', $role_id);
        }
        return false;
    }

    public static function is_admin($user_id = null)
    {
        return self::get_user_role($user_id) === 1;
    }

    public static function is_leader($user_id = null)
    {
        return self::get_user_role($user_id) === 2;
    }

    public static function is_member($user_id = null)
    {
        return self::get_user_role($user_id) === 3;
    }

    public static function can_book($user_id = null)
    {
        $role = self::get_user_role($user_id);
        return in_array($role, [1, 2, 3]);
    }

    public static function can_delete_log($user_id, $log_owner_id)
    {
        if (self::is_admin($user_id)) {
            return true;
        }
        return intval($user_id) === intval($log_owner_id);
    }
}
