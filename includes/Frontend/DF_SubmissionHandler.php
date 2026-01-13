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

    if (DF_Settings::get('turnstile_enabled')) {

      $token = $_POST['cf-turnstile-response'] ?? '';

      if (empty($token)) {
        DF_Response::validation_error(__('Verification failed.', 'dynamic-form'));
      }

      $verify = wp_remote_post(
        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        [
          'body' => [
            'secret'   => DF_Settings::get('turnstile_secret'),
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
          ],
          'timeout' => 10,
        ]
      );

      if (is_wp_error($verify)) {
        DF_Response::error(__('Verification service unavailable.', 'dynamic-form'));
      }

      $result = json_decode(wp_remote_retrieve_body($verify), true);
      error_log("DF_SUBMISSION_HANDLER" . print_r($result, true));
      error_log("DF_SUBMISSION_HANDLER" . DF_Settings::get('turnstile_secret'));

      if (empty($result['success'])) {
        DF_Response::validation_error(__('Bot verification failed.', 'dynamic-form'));
      }
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
    $data       = [];
    $user_email = '';

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
          DF_Response::validation_error(__('Invalid email address.', 'dynamic-form'));
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

      // Detect user email dynamically
      if ($type === 'email' && is_email($value)) {
        $user_email = $value;
      }

      $data[$label] = $value;
    }

    /* =====================================================
     * Email
     * ===================================================== */

    // Recipient priority:
    // 1. User email (from form)
    // 2. Form settings email
    // 3. Admin email
    if (!empty($user_email)) {
      $to = $user_email;
    } elseif (!empty($settings['to_email']) && is_email($settings['to_email'])) {
      $to = sanitize_email($settings['to_email']);
    } else {
      $to =  DF_Settings::get('smtp_user');
    }

    $from = sanitize_email(
      $settings['from_email']
        ?? 'noreply@' . wp_parse_url(home_url(), PHP_URL_HOST)
    );

    $subject = sanitize_text_field(
      $settings['subject']
        ?? __(DF_Settings::get('admin_subject'), 'dynamic-form')
    );

    $headers = [
      'Content-Type: text/html; charset=UTF-8',
      'From: Dynamic Form <' . $from . '>',
    ];

    // CC admin if user email is primary
    $cc = [];
    $admin_email = DF_Settings::get('smtp_user');
    if ($admin_email && $admin_email !== $to) {
      // $headers[] = 'Cc: ' . $admin_email;
      $cc[] = $admin_email;
    }

    /* =====================================================
     * Email Body
     * ===================================================== */
    $message  = '<h2>' . esc_html__('New Submission', 'dynamic-form') . '</h2>';
    $message .= '<ul>';

    foreach ($data as $label => $value) {
      $value = is_array($value)
        ? esc_html(implode(', ', $value))
        : esc_html($value);

      $message .= '<li><strong>' . esc_html($label) . ':</strong> ' . $value . '</li>';
    }

    $message .= '</ul>';

    if (!DF_AjaxHandlers::send_mail($to, $subject, $message, $cc)) {
      DF_Response::error(__('Email could not be sent.', 'dynamic-form'), 500);
    }

    /* =====================================================
     * Store Entry (Optional)
     * ===================================================== */

    if ($settings) {

      error_log("DF_SUBMISSION_HANDLER::I AM SAVING AN ENTRY");
      DF_FormRepository::save_entry(
        $form_id,
        $data,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
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
