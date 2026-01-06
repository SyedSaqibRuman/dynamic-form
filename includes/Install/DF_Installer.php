<?php
defined('ABSPATH') || exit;

/**
 * Class DF_Installer
 *
 * Handles plugin installation, activation, and DB setup.
 */
class DF_Installer
{

  /**
   * Run on plugin activation
   */
  public static function activate()
  {

    self::create_tables();
    self::set_version();
  }

  /**
   * Run on plugin deactivation
   */
  public static function deactivate()
  {
    // Reserved for future use (cron cleanup, etc.)
  }

  /**
   * Set plugin version
   */
  private static function set_version()
  {
    update_option('df_plugin_version', DF_VERSION);
  }

  /**
   * Create required database tables
   */
  private static function create_tables()
  {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset_collate = $wpdb->get_charset_collate();

    $forms_table   = $wpdb->prefix . 'df_forms';
    $entries_table = $wpdb->prefix . 'df_entries';

    /* -----------------------------------------
     * Forms table
     * ----------------------------------------- */
    $sql_forms = "CREATE TABLE {$forms_table} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      name VARCHAR(255) NOT NULL,
      fields LONGTEXT NOT NULL,
      settings LONGTEXT NOT NULL,
      created_at DATETIME NOT NULL,
      PRIMARY KEY  (id)
    ) {$charset_collate};";

    /* -----------------------------------------
     * Entries table
     * ----------------------------------------- */
    $sql_entries = "CREATE TABLE {$entries_table} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      form_id BIGINT UNSIGNED NOT NULL,
      data LONGTEXT NOT NULL,
      ip_address VARCHAR(100) NULL,
      user_agent TEXT NULL,
      created_at DATETIME NOT NULL,
      PRIMARY KEY  (id),
      KEY form_id (form_id)
    ) {$charset_collate};";

    dbDelta($sql_forms);
    dbDelta($sql_entries);
  }
}
