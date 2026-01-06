<?php
defined('ABSPATH') || exit;

class DF_SubmissionHandler
{

  public static function init(): void
  {
    add_action('wp_ajax_df_submit_form', [self::class, 'handle']);
    add_action('wp_ajax_nopriv_df_submit_form', [self::class, 'handle']);
  }

  public static function handle(): void
  {

    /* =====================================================
     * Security
     * ===================================================== */
    if (!DF_Nonce::verify_frontend($_POST['_df_nonce'] ?? '')) {
      DF_Response::security_failed();
    }

    $form_id = absint($_POST['df_form_id'] ?? 0);
    if (!$form_id) {
      DF_Response::validation_error(__('Invalid form.', 'dynamic-form'));
    }

    /* =====================================================
     * Load Form
     * ===================================================== */
    $form = DF_FormRepository::get($form_id);
    if (!$form) {
      DF_Response::error(__('Form not found.', 'dynamic-form'));
    }

    $fields   = json_decode($form['fields'] ?? '[]', true);
    $settings = json_decode($form['settings'] ?? '{}', true);

    if (!is_array($fields)) {
      DF_Response::error(__('Invalid form configuration.', 'dynamic-form'));
    }

    /* =====================================================
     * Sanitize & Validate Input
     * ===================================================== */
    $data = [];

    foreach ($fields as $field) {

      $id       = $field['id'] ?? '';
      $label    = $field['fieldName'] ?? $id;
      $type     = $field['fieldType'] ?? 'text';
      $required = !empty($field['required']);
      $key      = 'df_field_' . $id;

      if (!isset($_POST[$key])) {
        if ($required) {
          DF_Response::validation_error(
            sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label))
          );
        }
        continue;
      }

      $raw   = $_POST[$key];
      $value = DF_FormSanitizer::sanitize_field($type, $raw);

      // Field-level validation
      if ($required) {
        if ($type === 'email' && !is_email($value)) {
          DF_Response::validation_error(
            __('Invalid email address.', 'dynamic-form')
          );
        }

        if ($type === 'checkbox' && empty($value)) {
          DF_Response::validation_error(
            sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label))
          );
        }

        if ($value === '' || $value === null) {
          DF_Response::validation_error(
            sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label))
          );
        }
      }

      $data[$label] = $value;
    }

    /* =====================================================
     * Email
     * ===================================================== */
    $to = sanitize_email(
      $settings['to_email'] ?? get_option('admin_email')
    );

    $from = sanitize_email(
      $settings['from_email']
        ?? 'noreply@' . wp_parse_url(home_url(), PHP_URL_HOST)
    );

    $subject = sanitize_text_field(
      $settings['subject']
        ?? __('New Form Submission', 'dynamic-form')
    );

    $headers = [
      'Content-Type: text/html; charset=UTF-8',
      'From: Dynamic Form <' . $from . '>',
    ];

    $message = '<h2>' . esc_html__('New Submission', 'dynamic-form') . '</h2><ul>';

    foreach ($data as $label => $value) {
      $value = is_array($value)
        ? esc_html(implode(', ', $value))
        : esc_html($value);

      $message .= '<li><strong>' . esc_html($label) . ':</strong> ' . $value . '</li>';
    }

    $message .= '</ul>';

    if (!wp_mail($to, $subject, $message, $headers)) {
      DF_Response::error(
        __('Email could not be sent.', 'dynamic-form'),
        500
      );
    }

    /* =====================================================
     * Store Entry (Optional)
     * ===================================================== */
    if (!empty($settings['store_entries'])) {
      DF_FormRepository::save_entry(
        $form_id,
        $data,
        sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
        sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '')
      );
    }

    /* =====================================================
     * Success
     * ===================================================== */
    DF_Response::success(
      $settings['success_message']
        ?? __('Thank you! Your submission has been received.', 'dynamic-form')
    );
  }
}
