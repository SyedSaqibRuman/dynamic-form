<?php
defined('ABSPATH') || exit;

/**
 * Class DF_FormValidator
 *
 * Centralized validation rules for Dynamic Form plugin.
 * Responsibility:
 * - Validate required fields
 * - Validate field types (email, checkbox, etc.)
 * - Return meaningful validation errors
 *
 * NO DB
 * NO AJAX
 * NO OUTPUT (delegates errors to caller)
 */
class DF_FormValidator
{

  /**
   * Validate a single field value
   *
   * @param array $field   Field definition from builder
   * @param mixed $value   Sanitized value
   *
   * @return string|null   Error message or null if valid
   */
  public static function validate_field(array $field, $value): ?string
  {

    $label    = $field['fieldName'] ?? __('Field', 'dynamic-form');
    $type     = $field['fieldType'] ?? 'text';
    $required = !empty($field['required']);

    /* ---------------------------
     * Required validation
     * --------------------------- */
    if ($required) {

      if ($type === 'checkbox' && empty($value)) {
        return sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label));
      }

      if ($value === '' || $value === null) {
        return sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label));
      }
    }

    /* ---------------------------
     * Type-specific validation
     * --------------------------- */
    switch ($type) {

      case 'email':
        if ($value && !is_email($value)) {
          return __('Please enter a valid email address.', 'dynamic-form');
        }
        break;

      case 'number':
        if ($value !== '' && !is_numeric($value)) {
          return sprintf(__('"%s" must be a number.', 'dynamic-form'), esc_html($label));
        }
        break;

      case 'tel':
        if ($value && !preg_match('/^[0-9+\-\s]+$/', $value)) {
          return sprintf(__('"%s" is not a valid phone number.', 'dynamic-form'), esc_html($label));
        }
        break;
    }

    return null;
  }

  /**
   * Validate all submitted fields
   *
   * @param array $fields     Field definitions
   * @param array $sanitized  Sanitized values keyed by label
   *
   * @return string|null
   */
  public static function validate_submission(array $fields, array $sanitized): ?string
  {

    foreach ($fields as $field) {

      $id    = $field['id'] ?? '';
      $label = $field['fieldName'] ?? $id;

      if (!array_key_exists($label, $sanitized)) {
        if (!empty($field['required'])) {
          return sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label));
        }
        continue;
      }

      $error = self::validate_field($field, $sanitized[$label]);

      if ($error !== null) {
        return $error;
      }
    }

    return null;
  }
}
