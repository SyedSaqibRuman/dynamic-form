<?php
defined('ABSPATH') || exit;

/**
 * Class DF_AdminMenu
 *
 * Handles WordPress admin menu registration and asset loading.
 * NO HTML, NO DB, NO page-level data here.
 */
class DF_AdminMenu
{
  public static function init()
  {
    add_action('admin_menu', [self::class, 'register_menus']);
    add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
  }

  public static function register_menus()
  {
    add_menu_page(
      __('Dynamic Form', 'dynamic-form'),
      __('Dynamic Form', 'dynamic-form'),
      'manage_options',
      'df-forms',
      [self::class, 'render_forms_page'],
      'dashicons-feedback',
      26
    );

    add_submenu_page(
      'df-forms',
      __('All Forms', 'dynamic-form'),
      __('All Forms', 'dynamic-form'),
      'manage_options',
      'df-forms',
      [self::class, 'render_forms_page']
    );

    add_submenu_page(
      'df-forms',
      __('Add New Form', 'dynamic-form'),
      __('Add New', 'dynamic-form'),
      'manage_options',
      'df-add-form',
      [self::class, 'render_add_form_page']
    );

    add_submenu_page(
      'df-forms',
      __('Form Builder', 'dynamic-form'),
      __('Builder', 'dynamic-form'),
      'manage_options',
      'df-builder',
      [self::class, 'render_builder_page']
    );

    add_submenu_page(
      'df-forms',
      __('Form Styles', 'dynamic-form'),
      __('Styles', 'dynamic-form'),
      'manage_options',
      'df-style-forms',
      [self::class, 'render_style_page']
    );

    add_submenu_page(
      'df-forms',
      __('Entries', 'dynamic-form'),
      __('Entries', 'dynamic-form'),
      'manage_options',
      'df-entries',
      [self::class, 'render_entries_page']
    );
  }

  public static function enqueue_assets($hook)
  {
    // Load ONLY on our plugin pages
    if (
      strpos($hook, 'df-forms') === false &&
      strpos($hook, 'df-add-form') === false &&
      strpos($hook, 'df-builder') === false &&
      strpos($hook, 'df-style-forms') === false &&
      strpos($hook, 'df-entries') === false
    ) {
      return;
    }

    $admin_js        = DF_PATH . 'includes/Assets/js/admin.js';
    $admin_css       = DF_PATH . 'includes/Assets/css/admin.css';
    $style_form_js   = DF_PATH . 'includes/Assets/js/style-form.js';
    $style_form_css  = DF_PATH . 'includes/Assets/css/style-form.css';

    /* ---------------------------
     * Common admin assets
     * --------------------------- */
    wp_enqueue_style(
      'df-admin',
      DF_URL . 'includes/Assets/css/admin.css',
      [],
      file_exists($admin_css) ? filemtime($admin_css) : DF_VERSION
    );

    wp_enqueue_script(
      'df-admin',
      DF_URL . 'includes/Assets/js/admin.js',
      [],
      file_exists($admin_js) ? filemtime($admin_js) : DF_VERSION,
      true
    );

    // Global AJAX data (safe everywhere)
    wp_localize_script(
      'df-admin',
      'dfAjax',
      [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => DF_Nonce::create_admin(),
      ]
    );

    /* ---------------------------
     * Style editor page assets
     * --------------------------- */
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dynamic-form_page_df-style-forms') {

      wp_enqueue_style(
        'df-style-form',
        DF_URL . 'includes/Assets/css/style-form.css',
        ['df-admin'],
        file_exists($style_form_css) ? filemtime($style_form_css) : DF_VERSION
      );

      wp_enqueue_script(
        'df-style-form',
        DF_URL . 'includes/Assets/js/style-form.js',
        ['df-admin'],
        file_exists($style_form_js) ? filemtime($style_form_js) : DF_VERSION,
        true
      );
    }
  }

  /* ===========================
   * Page renderers
   * =========================== */

  public static function render_forms_page()
  {
    require DF_PATH . 'includes/Admin/Pages/list-forms.php';
  }

  public static function render_add_form_page()
  {
    require DF_PATH . 'includes/Admin/Pages/add-form.php';
  }

  public static function render_builder_page()
  {
    require DF_PATH . 'includes/Admin/Pages/builder.php';
  }

  public static function render_style_page()
  {
    require DF_PATH . 'includes/Admin/Pages/style-form.php';
  }

  public static function render_entries_page()
  {
    require DF_PATH . 'includes/Admin/Pages/list-entries.php';
  }
}
