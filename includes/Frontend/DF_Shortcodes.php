<?php
defined('ABSPATH') || exit;

/**
 * Class DF_Shortcodes
 *
 * Registers and handles frontend shortcodes.
 * Responsibility:
 * - Register [dynamic_form] shortcode
 * - Load frontend assets ONLY when shortcode is used
 * - Delegate rendering to DF_Renderer
 */
class DF_Shortcodes
{

  /**
   * Track whether shortcode is used on page
   */
  private static bool $shortcode_used = false;

  /**
   * Initialize shortcode
   */
  public static function init()
  {

    add_shortcode('dynamic_form', [self::class, 'render_shortcode']);
    add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
  }

  /**
   * Shortcode callback
   *
   * Usage:
   * [dynamic_form id="1"]
   */
  public static function render_shortcode($atts)
  {
    self::$shortcode_used = true;
    self::enqueue_assets();

    $atts = shortcode_atts(
      [
        'id' => 0,
      ],
      $atts,
      'dynamic_form'
    );

    $form_id = absint($atts['id']);

    if (!$form_id) {
      return '<p>' . esc_html__('Invalid form ID.', 'dynamic-form') . '</p>';
    }

    // Fetch form
    $form = DF_FormRepository::get($form_id);

    if (!$form) {
      return '<p>' . esc_html__('Form not found.', 'dynamic-form') . '</p>';
    }

    // Delegate rendering
    return DF_Renderer::render($form);
  }

  /**
   * Enqueue frontend assets ONLY if shortcode exists
   */
  public static function enqueue_assets()
  {
    error_log("---------------------------PATH " . DF_PATH);
    error_log("---------------------------URL " . DF_URL);
    if (!self::$shortcode_used) {
      return;
    }

    wp_enqueue_style(
      'df-frontend',
      DF_URL . 'includes/Assets/css/frontend.css',
      [],
      DF_VERSION
    );

    wp_enqueue_script(
      'df-frontend',
      DF_URL . 'includes/Assets/js/frontend.js',
      [],
      DF_VERSION,
      true
    );

    wp_localize_script(
      'df-frontend',
      'df-Frontend',
      [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('df_frontend_nonce'),
      ]
    );
  }
}
