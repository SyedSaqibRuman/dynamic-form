<?php
defined('ABSPATH') || exit;

/**
 * Class DF_FormSanitizer
 *
 * Centralized sanitization helper for Dynamic Form plugin.
 * Responsibility:
 * - Sanitize values based on field type
 * - Sanitize settings arrays
 * - Sanitize style values
 *
 * NO DB
 * NO AJAX
 * NO OUTPUT
 */
class DF_FormSanitizer
{

  /**
   * Sanitize a single field value based on type
   *
   * @param string $type
   * @param mixed  $value
   * @return mixed
   */
  public static function sanitize_field(string $type, $value)
  {

    switch ($type) {

      case 'email':
        return sanitize_email($value);

      case 'textarea':
        return sanitize_textarea_field($value);

      case 'checkbox':
        return array_map('sanitize_text_field', (array) $value);

      case 'number':
        return is_numeric($value) ? $value : '';

      case 'tel':
        return preg_replace('/[^0-9+\-\s]/', '', (string) $value);

      default:
        return sanitize_text_field($value);
    }
  }

  /**
   * Sanitize builder fields array
   *
   * @param array $fields
   * @return array
   */
  public static function sanitize_fields(array $fields): array
  {

    $clean = [];

    foreach ($fields as $field) {

      $clean[] = [
        'id'        => sanitize_text_field($field['id'] ?? ''),
        'fieldName' => sanitize_text_field($field['fieldName'] ?? ''),
        'fieldType' => sanitize_text_field($field['fieldType'] ?? 'text'),
        'required'  => !empty($field['required']),
        'values'    => array_map(
          'sanitize_text_field',
          (array) ($field['values'] ?? [])
        ),
      ];
    }

    return $clean;
  }

  /**
   * Sanitize form settings (email, messages, flags)
   *
   * @param array $settings
   * @return array
   */
  public static function sanitize_settings(array $settings): array
  {

    return [
      'to_email'        => sanitize_email($settings['to_email'] ?? ''),
      'from_email'      => sanitize_email($settings['from_email'] ?? ''),
      'subject'         => sanitize_text_field($settings['subject'] ?? ''),
      'success_message' => sanitize_text_field($settings['success_message'] ?? ''),
      'store_entries'   => !empty($settings['store_entries']),
      'captcha'         => sanitize_text_field($settings['captcha'] ?? ''),
    ];
  }

  /**
   * Sanitize style settings
   *
   * @param array $styles
   * @return array
   */
  public static function sanitize_styles(array $styles): array
  {

    return [
      'button_color'  => sanitize_hex_color($styles['button_color'] ?? '#2271b1'),
      'border_radius' => absint($styles['border_radius'] ?? 4),
    ];
  }
}
