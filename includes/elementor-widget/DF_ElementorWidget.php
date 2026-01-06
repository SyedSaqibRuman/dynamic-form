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
    require_once DF_PATH . 'includes/elementor-widget/class-df-elementor-widget.php';

    $widgets_manager->register(
      new DF_Elementor_Widget()
    );
  }
}
