<?php

/**
 * Plugin Name: Dynamic Form
 * Description: Create and manage dynamic contact forms with builder and style editor.
 * Version: 1.0.0
 * Author: Syed Saqib Ruman
 */

defined('ABSPATH') || exit;

/**
 * ------------------------------------------------------------------
 * Plugin Constants
 * ------------------------------------------------------------------
 */
define('DF_VERSION', '1.0.0');
define('DF_PATH', plugin_dir_path(__FILE__));
define('DF_URL', plugin_dir_url(__FILE__));
define('DF_BASENAME', plugin_basename(__FILE__));

/**
 * ------------------------------------------------------------------
 * Autoload / Includes
 * ------------------------------------------------------------------
 */
require_once DF_PATH . 'includes/Install/DF_Installer.php';

require_once DF_PATH . 'includes/Core/DF_FormRepository.php';
require_once DF_PATH . 'includes/Core/DF_FormSanitizer.php';
require_once DF_PATH . 'includes/Core/DF_FormValidator.php';

require_once DF_PATH . 'includes/Helpers/DF_Nonce.php';
require_once DF_PATH . 'includes/Helpers/DF_Response.php';

require_once DF_PATH . 'includes/Admin/DF_AdminMenu.php';
require_once DF_PATH . 'includes/Admin/DF_AjaxHandlers.php';
require_once DF_PATH . 'includes/Admin/DF_Settings.php';
require_once DF_PATH . 'includes/Admin/DF_Mailer.php';

require_once DF_PATH . 'includes/Frontend/DF_Shortcodes.php';
require_once DF_PATH . 'includes/Frontend/DF_Renderer.php';
require_once DF_PATH . 'includes/Frontend/DF_SubmissionHandler.php';
require_once DF_PATH . 'includes/elementor-widget/DF_ElementorWidget.php';

/**
 * ------------------------------------------------------------------
 * Activation / Deactivation Hooks
 * ------------------------------------------------------------------
 */
register_activation_hook(__FILE__, ['df_Installer', 'activate']);
register_deactivation_hook(__FILE__, ['df_Installer', 'deactivate']);

/**
 * ------------------------------------------------------------------
 * Initialize Plugin
 * ------------------------------------------------------------------
 */
function df_init_plugin()
{
  DF_Mailer::init();

  // Admin
  if (is_admin()) {
    DF_AdminMenu::init();
    DF_AjaxHandlers::init();
    DF_Settings::init();

    add_action('admin_init', function () {

      if (
        !is_admin() ||
        !current_user_can('manage_options') ||
        !isset($_GET['page'], $_GET['export']) ||
        $_GET['page'] !== 'df-entries'
      ) {
        return;
      }
      $export  = sanitize_text_field($_GET['export']);
      $entries = DF_FormRepository::get_all_entries(10000);

      if ($export === 'json') {
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=df-entries.json');
        echo wp_json_encode($entries, JSON_PRETTY_PRINT);
        exit;
      }

      if ($export === 'csv') {
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=df-entries.csv');

        $out = fopen('php://output', 'w');

        // 🔑 UTF-8 BOM (fix Excel issues)
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, [
          'Entry ID',
          'Form ID',
          'Field',
          'Value',
          'IP Address',
          'User Agent',
          'Date'
        ]);

        foreach ($entries as $entry) {
          $data = json_decode($entry['data'], true) ?: [];
          // error_log($data . ">>>>>>>>>>>");
          foreach ($data as $field => $value) {
            fputcsv($out, [
              $entry['id'],
              $entry['form_id'],
              $field,
              is_array($value) ? implode(', ', $value) : $value,
              $entry['ip_address'],
              $entry['user_agent'],
              $entry['created_at'],
            ]);
          }
          // error_log($field . ">>>>>>>>>>>" . $value);
        }

        fclose($out);
        exit;
      }
    });
  }

  // Frontend
  DF_Shortcodes::init();
  DF_SubmissionHandler::init();

  if (did_action('elementor/loaded')) {
    DF_Elementor::init();
  }
}
add_action('plugins_loaded', 'df_init_plugin');

add_action('phpmailer_init', function () {
  error_log('phpmailer_init FIRED');
});


add_action('wp_enqueue_scripts', function () {
  wp_register_style(
    'df-frontend',
    DF_URL . 'includes/Assets/css/frontend.css',
    [],
    filemtime(DF_PATH . 'includes/Assets/css/frontend.css')
  );

  wp_register_script(
    'df-frontend',
    DF_URL . 'includes/Assets/js/frontend.js',
    [],
    filemtime(DF_PATH . 'includes/Assets/js/frontend.js'),
    true
  );

  wp_localize_script(
    'df-frontend',
    'dfFrontend',
    [
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('df_submit_form'),
    ]
  );

  if (!DF_Settings::get('turnstile_enabled')) {
    return;
  }

  wp_enqueue_script(
    'cf-turnstile',
    'https://challenges.cloudflare.com/turnstile/v0/api.js',
    [],
    null,
    true
  );
});
