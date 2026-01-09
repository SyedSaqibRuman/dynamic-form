<?php
defined('ABSPATH') || exit;

/**
 * Class DF_Elementor
 *
 * Handles Elementor integration only.
 * - Registers widgets
 * - Loads Elementor-specific assets
 *
 * NO rendering logic
 * NO DB logic
 */
class DF_Elementor
{
  /**
   * Initialize Elementor integration
   */
  public static function init(): void
  {
    // Elementor widget registration
    add_action(
      'elementor/widgets/register',
      [self::class, 'register_widgets']
    );

    // Ensure frontend assets load inside Elementor editor & preview
    add_action(
      'elementor/frontend/after_enqueue_styles',
      [self::class, 'enqueue_styles']
    );

    add_action(
      'elementor/frontend/after_enqueue_scripts',
      [self::class, 'enqueue_scripts']
    );
  }

  public static function enqueue_styles(): void
  {
    if (!wp_style_is('df-frontend', 'enqueued')) {
      wp_enqueue_style(
        'df-frontend',
        DF_URL . 'assets/frontend.css',
        [],
        DF_VERSION
      );
    }
  }

  public static function enqueue_scripts(): void
  {
    if (!wp_script_is('df-frontend', 'enqueued')) {
      wp_enqueue_script(
        'df-frontend',
        DF_URL . 'assets/frontend.js',
        [],
        DF_VERSION,
        true
      );
    }
  }


  /**
   * Register Elementor widgets
   */
  public static function register_widgets($widgets_manager): void
  {
    // require_once DF_PATH . 'includes/elementor-widget/class-df-elementor-widget.php';

    // $widgets_manager->register(
    //   new DF_Elementor_Widget()
    // );

    error_log('DF_Elementor: register_widgets called');
    error_log('Elementor Widget_Base exists: ' . (class_exists('\Elementor\Widget_Base') ? 'YES' : 'NO'));

    $widget_file = DF_PATH . 'includes/elementor-widget/class-df-elementor-widget.php';

    // SAFETY CHECK 1: File exists
    if (!file_exists($widget_file)) {
      error_log('DF_Elementor: Widget file not found: ' . $widget_file);
      return;
    }

    // SAFETY CHECK 2: Elementor classes loaded
    if (!class_exists('\Elementor\Widget_Base')) {
      error_log('DF_Elementor: Elementor\Widget_Base not loaded');
      return;
    }

    require_once $widget_file;

    try {
      $widgets_manager->register(new DF_Elementor_Widget());
      error_log('DF_Elementor: Widget registered successfully');
    } catch (Throwable $e) {
      error_log('DF_Elementor: Widget registration failed: ' . $e->getMessage());
    }
  }
}
