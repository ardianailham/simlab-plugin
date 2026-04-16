<?php

/*
 * Plugin Name: SIMLAB Plugin
 * Description: Plugin for "Sistem Informasi dan Manajemen Laboratorium"
 * Author: Ardiana Ilham Nurrohman
 * Author URI: https://github.com/ardianailham
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define('SL_SIMLAB_PATH', plugin_dir_url(__FILE__));

include_once dirname(__FILE__) . '/classes/sl-simlab-base-class.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-auth.inc.php';
include_once dirname(__FILE__) . '/sl-main-class.inc.php';
include_once dirname(__FILE__) . '/sl-simlab-template.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-alat-class.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-bahan-class.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-logbook-alat-class.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-logbook-bahan-class.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-export-import-class.inc.php';
include_once dirname(__FILE__) . '/classes/sl-simlab-pubchem-class.inc.php';
$simlab_plugin = new SL_SimlabPlugin;
$simlab_plugin1 = new SL_SIMLAB_AlatClass;
$simlab_plugin2 = new SL_SIMLAB_BahanClass;
$simlab_plugin3 = new SL_SIMLAB_logbookAlatClass;
$simlab_plugin4 = new SL_SIMLAB_LogbookBahanClass;
$simlab_export_import = new SL_SIMLAB_ExportImportClass;

// initialization
register_activation_hook(__FILE__, array($simlab_plugin, 'install'));
add_action('admin_enqueue_scripts', 'sl_simlab_scripts');
add_action('wp_enqueue_scripts', 'sl_simlab_scripts');
add_action('admin_enqueue_scripts', 'sl_simlab_styles');
add_action('wp_enqueue_scripts', 'sl_simlab_styles');
add_action('admin_menu', array($simlab_plugin, 'simlab_admin_menu'));
add_filter('theme_page_templates', 'sl_template_register', 10, 3);
add_filter('template_include', 'sl_template_include', 99);
add_action('wp_footer', array($simlab_plugin, 'simlab_fixed_button'));
add_action('admin_init', 'sl_simlab_handle_export');
SL_SIMLAB_PubChemClass::register_ajax();

add_action('plugins_loaded', 'sl_simlab_update_db_check');
function sl_simlab_update_db_check() {
    $current_version = '1.0.2';
    if (get_option('sl_simlab_db_version') !== $current_version) {
        global $simlab_plugin;
        $simlab_plugin->_install();
        update_option('sl_simlab_db_version', $current_version);
    }
}

function sl_simlab_handle_export() {
    global $simlab_export_import;
    if (isset($_GET['action']) && $_GET['action'] == 'export-alat' && check_admin_referer('sl_export_alat')) {
        $simlab_export_import->exportAlat();
    }
    if (isset($_GET['action']) && $_GET['action'] == 'export-bahan' && check_admin_referer('sl_export_bahan')) {
        $simlab_export_import->exportBahan();
    }
}

// Url redirects
function setting_page()
{
  if (!is_user_logged_in() || !SL_SIMLAB_Auth::is_admin()) {
    wp_die(__('You do not have permission to access this page.'));
  }
  global $simlab_plugin;
  if ($_GET['page'] == $simlab_plugin->plugin_slug)
    include_once dirname(__FILE__) . '/sl-admin-dashboard.php';
  elseif ($_GET['page'] == $simlab_plugin->plugin_slug . '-daftar-alat')
    include_once dirname(__FILE__) . '/sl-admin-alat.inc.php';
  elseif ($_GET['page'] == $simlab_plugin->plugin_slug . '-daftar-bahan')
    include_once dirname(__FILE__) . '/sl-admin-bahan.inc.php';
  elseif ($_GET['page'] == $simlab_plugin->plugin_slug . '-logbook-alat')
    include_once dirname(__FILE__) . '/sl-admin-logbook-alat.inc.php';
  elseif ($_GET['page'] == $simlab_plugin->plugin_slug . '-logbook-bahan')
    include_once dirname(__FILE__) . '/sl-admin-logbook-bahan.inc.php';
  elseif ($_GET["page"] == $simlab_plugin->plugin_slug . '-settings')
    include_once dirname(__FILE__) . '/sl-admin-settings.inc.php';
  elseif ($_GET["page"] == $simlab_plugin->plugin_slug . '-user-management')
    include_once dirname(__FILE__) . '/sl-admin-user-management.php';
  else include_once dirname(__FILE__) . '/sl-admin-dashboard.php';
}

// Script
function sl_simlab_scripts()
{
  wp_register_script('sl_simlab_bootstrap_js', SL_SIMLAB_PATH . '/js/bootstrap.js', array('jquery'), '1.0', true);
  wp_enqueue_script('sl_simlab_bootstrap_js');

  // PubChem integration script
  wp_register_script(
    'sl_simlab_pubchem_js',
    SL_SIMLAB_PATH . '/js/sl-simlab-pubchem.js',
    array('jquery'),
    '1.0.0',
    true
  );
  wp_enqueue_script('sl_simlab_pubchem_js');
  wp_localize_script('sl_simlab_pubchem_js', 'sl_simlab_pubchem', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('sl_pubchem_lookup'),
  ));
}

// Style
function sl_simlab_styles()
{
  wp_register_style('sl_simlab_bootstrap', SL_SIMLAB_PATH . '/css/bootstrap.css');
  wp_enqueue_style('sl_simlab_bootstrap');
  wp_register_style('sl_simlab_css', SL_SIMLAB_PATH . '/css/simlab.css');
  wp_enqueue_style('sl_simlab_css');
  wp_register_style('sl_simlab_icon', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
  wp_enqueue_style('sl_simlab_icon');
}



// Add Shortcode for classes
add_shortcode('daftar-alat', array($simlab_plugin1, 'index'));
add_shortcode('daftar-bahan', array($simlab_plugin2, 'index'));
add_shortcode('daftar-logbook-alat', array($simlab_plugin3, 'index'));
add_shortcode('daftar-logbook-bahan', array($simlab_plugin4, 'index'));
