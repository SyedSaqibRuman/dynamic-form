<?php
defined('ABSPATH') || exit;

class DF_AjaxHandlers
{

  public static function init()
  {
    add_action('wp_ajax_df_create_form', [self::class, 'create_form']);
    add_action('wp_ajax_df_save_form', [self::class, 'save_form']);
    add_action('wp_ajax_df_save_style', [self::class, 'save_style']);
    add_action('wp_ajax_df_delete_form', [self::class, 'delete_form']);
  }

  /* =====================================================
   * Security
   * ===================================================== */
  private static function verify(): void
  {
    if (!DF_Nonce::verify_admin($_POST['_ajax_nonce'] ?? '')) {
      error_log(DF_Nonce::create_admin() . ">>>>>>>>>>>>>>>>>>>>>>>>FAILED<<<<<<<<<<<<<<<<<<<<<<<<<<<<" . $_POST['_ajax_nonce']);
      DF_Response::security_failed();
    }

    if (!current_user_can('manage_options')) {
      DF_Response::unauthorized();
    }
  }

  /* =====================================================
   * Create Form
   * ===================================================== */
  public static function create_form(): void
  {
    self::verify();

    $name = sanitize_text_field($_POST['name'] ?? '');

    if (!$name) {
      DF_Response::validation_error(
        __('Form name is required.', 'dynamic-form')
      );
    }

    $form_id = DF_FormRepository::create($name);

    // Log creation
    error_log(sprintf(
      'DF: Form #%d created by user #%d',
      $form_id,
      get_current_user_id()
    ));

    DF_Response::success(
      __('Form created successfully.', 'dynamic-form'),
      ['form_id' => $form_id]
    );
  }

  /* =====================================================
   * Save Builder Fields
   * ===================================================== */
  public static function save_form(): void
  {
    self::verify();

    $form_id = absint($_POST['form_id'] ?? 0);
    $json    = wp_unslash($_POST['json'] ?? '');

    if (!$form_id || !$json) {
      DF_Response::error(
        __('Invalid request.', 'dynamic-form')
      );
    }

    // Verify form exists
    $form = DF_FormRepository::get($form_id);
    if (!$form) {
      DF_Response::error(
        __('Form not found.', 'dynamic-form'),
        404
      );
    }

    $fields = json_decode($json, true);

    if (!is_array($fields)) {
      DF_Response::error(
        __('Invalid JSON.', 'dynamic-form')
      );
    }

    // Validate field structure
    foreach ($fields as $field) {
      if (empty($field['id']) || empty($field['fieldName'])) {
        DF_Response::error(
          __('Invalid field data.', 'dynamic-form')
        );
      }
    }

    DF_FormRepository::save_fields($form_id, $fields);

    // Log update
    error_log(sprintf(
      'DF: Form #%d updated by user #%d (%d fields)',
      $form_id,
      get_current_user_id(),
      count($fields)
    ));

    DF_Response::success(
      __('Form saved successfully.', 'dynamic-form')
    );
  }

  /* =====================================================
   * Save Styles (Improved Security)
   * ===================================================== */
  public static function save_style(): void
  {


    self::verify();

    $form_id = absint($_POST['form_id'] ?? 0);

    if (!$form_id) {
      DF_Response::validation_error(
        __('Invalid form ID.', 'dynamic-form')
      );
    }

    if (!current_user_can('manage_options')) {
      DF_Response::unauthorized();
    }

    $json = wp_unslash($_POST['json'] ?? '');

    if (!$json) {
      DF_Response::error(
        __('Invalid request.', 'dynamic-form')
      );
    }

    $styles = json_decode($json, true);

    if (!is_array($styles)) {
      DF_Response::error(
        __('Invalid JSON.', 'dynamic-form')
      );
    }

    // Verify form exists
    $form = DF_FormRepository::get($form_id);

    if (!$form) {
      DF_Response::error(
        __('Form not found.', 'dynamic-form'),
        404
      );
    }

    // Sanitize style values
    $styles = self::sanitize_styles($styles);

    // Update settings
    $settings = json_decode($form['settings'] ?? '{}', true);
    $settings['styles'] = $styles;

    DF_FormRepository::save_settings($form_id, $settings);

    // Log update
    error_log(sprintf(
      'DF: Form #%d styles updated by user #%d',
      $form_id,
      get_current_user_id()
    ));

    DF_Response::success(
      __('Styles saved successfully.', 'dynamic-form')
    );
  }

  /* =====================================================
   * Delete Form
   * ===================================================== */
  public static function delete_form(): void
  {
    self::verify();

    $form_id = absint($_POST['form_id'] ?? 0);

    if (!$form_id) {
      DF_Response::validation_error(
        __('Invalid form ID.', 'dynamic-form')
      );
    }

    // Verify form exists before deleting
    $form = DF_FormRepository::get($form_id);
    if (!$form) {
      DF_Response::error(
        __('Form not found.', 'dynamic-form'),
        404
      );
    }

    // Log deletion before removing
    error_log(sprintf(
      'DF: Form #%d "%s" deleted by user #%d',
      $form_id,
      $form['name'],
      get_current_user_id()
    ));

    DF_FormRepository::delete($form_id);

    DF_Response::success(
      __('Form deleted successfully.', 'dynamic-form')
    );
  }

  /* =====================================================
   * Sanitize Style Values
   * ===================================================== */
  private static function sanitize_styles(array $styles): array
  {
    $sanitized = [];

    foreach ($styles as $section => $data) {
      if (!is_array($data)) continue;

      foreach ($data as $breakpoint => $values) {
        if (!is_array($values)) continue;

        foreach ($values as $key => $value) {
          // Sanitize by type
          switch ($key) {
            case 'bg':
            case 'color':
            case 'border_color':
              // Validate hex color
              $sanitized[$section][$breakpoint][$key] = sanitize_hex_color($value);
              break;

            case 'width':
            case 'height':
            case 'font_size':
            case 'padding':
            case 'margin':
            case 'field_gap':
              // Validate number
              $sanitized[$section][$breakpoint][$key] = is_numeric($value) ? abs((float)$value) : null;
              break;

            case 'align':
              // Validate alignment
              $sanitized[$section][$breakpoint][$key] = in_array($value, ['left', 'center', 'right'], true) ? $value : 'left';
              break;

            default:
              // Unknown key - sanitize as text
              $sanitized[$section][$breakpoint][$key] = sanitize_text_field($value);
          }
        }
      }
    }

    return $sanitized;
  }

  /* =====================================================
   * Save Email SMPT settings
   * ===================================================== */
  public static function save_smtp_settings(): void
  {
    self::verify();
  }
}
