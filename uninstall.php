<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package DynamicForm
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

/**
 * ------------------------------------------------------------------
 * Delete plugin options
 * ------------------------------------------------------------------
 */
delete_option('df_plugin_version');

/**
 * ------------------------------------------------------------------
 * Delete custom database tables
 * ------------------------------------------------------------------
 */
$tables = [
  $wpdb->prefix . 'df_entries',
  $wpdb->prefix . 'df_forms',
];

foreach ($tables as $table) {
  $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

/**
 * ------------------------------------------------------------------
 * Clean up user meta (future-proof)
 * ------------------------------------------------------------------
 * If you ever store per-user settings, clean them here.
 *
 * Example:
 * delete_metadata('user', 0, 'df_user_setting', '', true);
 */

/**
 * ------------------------------------------------------------------
 * Clear scheduled events (future-proof)
 * ------------------------------------------------------------------
 */
// wp_clear_scheduled_hook('df_cron_event');
