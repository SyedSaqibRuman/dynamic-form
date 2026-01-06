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

  // Admin
  if (is_admin()) {
    DF_AdminMenu::init();
    DF_AjaxHandlers::init();
  }

  // Frontend
  DF_Shortcodes::init();
  DF_SubmissionHandler::init();

  if (did_action('elementor/loaded')) {
    DF_Elementor::init();
  }
}
add_action('plugins_loaded', 'df_init_plugin');


add_action('wp_enqueue_scripts', function () {
  wp_register_style(
    'df-frontend',
    DF_URL . 'includes/Assets/css/frontend.css',
    [],
    DF_VERSION
  );

  wp_register_script(
    'df-frontend',
    DF_URL . 'includes/Assets/js/frontend.js',
    [],
    DF_VERSION,
    true
  );
});
