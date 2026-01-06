<?php
defined('ABSPATH') || exit;

/**
 * Class DF_FormRepository
 *
 * Database access layer for Dynamic Form plugin.
 * NO HTML
 * NO AJAX
 * NO BUSINESS LOGIC
 */
class DF_FormRepository
{

  /* =====================================================
   * TABLE HELPERS
   * ===================================================== */

  private static function forms_table()
  {
    global $wpdb;
    return $wpdb->prefix . 'df_forms';
  }

  private static function entries_table()
  {
    global $wpdb;
    return $wpdb->prefix . 'df_entries';
  }

  /* =====================================================
   * FORMS
   * ===================================================== */

  /**
   * Get all forms
   */
  public static function get_all(): array
  {
    global $wpdb;

    return $wpdb->get_results(
      "SELECT id, name, created_at FROM " . self::forms_table() . " ORDER BY id DESC",
      ARRAY_A
    );
  }

  /**
   * Get single form by ID
   */
  public static function get(int $form_id): ?array
  {
    global $wpdb;

    return $wpdb->get_row(
      $wpdb->prepare(
        "SELECT * FROM " . self::forms_table() . " WHERE id = %d",
        $form_id
      ),
      ARRAY_A
    );
  }

  /**
   * Create a new form
   */
  public static function create(string $name, array $settings = []): int
  {
    global $wpdb;

    $wpdb->insert(
      self::forms_table(),
      [
        'name'       => sanitize_text_field($name),
        'fields'     => wp_json_encode([]),
        'settings'   => wp_json_encode($settings),
        'created_at' => current_time('mysql'),
      ],
      ['%s', '%s', '%s', '%s']
    );

    return (int) $wpdb->insert_id;
  }

  /**
   * Save builder fields JSON
   */
  public static function save_fields(int $form_id, array $fields): bool
  {
    global $wpdb;

    return (bool) $wpdb->update(
      self::forms_table(),
      ['fields' => wp_json_encode($fields)],
      ['id' => $form_id],
      ['%s'],
      ['%d']
    );
  }

  /**
   * Save form settings (email, styles, captcha, messages)
   */
  public static function save_settings(int $form_id, array $settings): bool
  {
    global $wpdb;

    return (bool) $wpdb->update(
      self::forms_table(),
      ['settings' => wp_json_encode($settings)],
      ['id' => $form_id],
      ['%s'],
      ['%d']
    );
  }

  /**
   * Delete a form and all its entries
   */
  public static function delete(int $form_id): bool
  {
    global $wpdb;

    // Delete entries first (GDPR-safe)
    $wpdb->delete(
      self::entries_table(),
      ['form_id' => $form_id],
      ['%d']
    );

    // Delete form
    return (bool) $wpdb->delete(
      self::forms_table(),
      ['id' => $form_id],
      ['%d']
    );
  }

  /* =====================================================
   * FORM ENTRIES
   * ===================================================== */

  /**
   * Save a form submission
   */
  public static function save_entry(
    int $form_id,
    array $data,
    string $ip = '',
    string $user_agent = ''
  ): bool {
    global $wpdb;

    return (bool) $wpdb->insert(
      self::entries_table(),
      [
        'form_id'    => $form_id,
        'data'       => wp_json_encode($data),
        'ip_address' => sanitize_text_field($ip),
        'user_agent' => sanitize_text_field($user_agent),
        'created_at' => current_time('mysql'),
      ],
      ['%d', '%s', '%s', '%s', '%s']
    );
  }

  /**
   * Get entries for a form
   */
  public static function get_entries(
    int $form_id,
    int $limit = 50,
    int $offset = 0
  ): array {
    global $wpdb;

    return $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM " . self::entries_table() . "
         WHERE form_id = %d
         ORDER BY id DESC
         LIMIT %d OFFSET %d",
        $form_id,
        $limit,
        $offset
      ),
      ARRAY_A
    );
  }

  /**
   * Delete a single entry (GDPR)
   */
  public static function delete_entry(int $entry_id): bool
  {
    global $wpdb;

    return (bool) $wpdb->delete(
      self::entries_table(),
      ['id' => $entry_id],
      ['%d']
    );
  }

  public static function get_form_options(): array
  {
    global $wpdb;

    $table = $wpdb->prefix . 'df_forms';

    $rows = $wpdb->get_results(
      "SELECT id, name FROM {$table} ORDER BY name ASC",
      ARRAY_A
    );

    if (empty($rows)) {
      return [];
    }

    $options = [];

    foreach ($rows as $row) {
      $options[(int) $row['id']] = esc_html($row['name']);
    }

    return $options;
  }


  /**
   * Delete all entries for a form (GDPR)
   */
  public static function delete_entries_by_form(int $form_id): bool
  {
    global $wpdb;

    return (bool) $wpdb->delete(
      self::entries_table(),
      ['form_id' => $form_id],
      ['%d']
    );
  }
}
