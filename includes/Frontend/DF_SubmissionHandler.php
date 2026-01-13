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
     * Security: Nonce
     * ===================================================== */
    if (!DF_Nonce::verify_frontend($_POST['_df_nonce'] ?? '')) {
      DF_Response::security_failed();
    }

    /* =====================================================
     * Cloudflare Turnstile
     * ===================================================== */
    if (DF_Settings::get('turnstile_enabled')) {

      $token = $_POST['cf-turnstile-response'] ?? '';

      if (!$token) {
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

      if (empty($result['success'])) {
        DF_Response::validation_error(__('Bot verification failed.', 'dynamic-form'));
      }
    }

    /* =====================================================
     * Form ID
     * ===================================================== */
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
     * STORE AS: Label => Value
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

      /* ---------- Required checks ---------- */
      if ($required) {

        if ($type === 'email' && !is_email($value)) {
          DF_Response::validation_error(__('Invalid email address.', 'dynamic-form'));
        }

        if ($value === '' || $value === null) {
          DF_Response::validation_error(
            sprintf(__('"%s" is required.', 'dynamic-form'), esc_html($label))
          );
        }
      }

      /* ---------- Capture user email ---------- */
      if ($type === 'email' && is_email($value)) {
        $user_email = $value;
      }

      /* ---------- Store as Label => Value ---------- */
      $label_key = trim(wp_strip_all_tags($label));

      // Prevent overwriting if duplicate labels exist
      if (isset($data[$label_key])) {
        $label_key .= ' (' . $id . ')';
      }

      $data[$label_key] = is_array($value)
        ? array_map('sanitize_text_field', $value)
        : $value;
    }

    if (empty($data)) {
      DF_Response::validation_error(__('No form data received.', 'dynamic-form'));
    }

    /* =====================================================
     * Email Routing
     * ===================================================== */
    if (!empty($user_email)) {
      $to = $user_email;
    } elseif (!empty($settings['to_email']) && is_email($settings['to_email'])) {
      $to = sanitize_email($settings['to_email']);
    } else {
      $to = DF_Settings::get('smtp_user');
    }

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

    $cc = [];
    $admin_email = DF_Settings::get('smtp_user');
    if ($admin_email && $admin_email !== $to) {
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
     * Store Entry (DB-safe)
     * ===================================================== */
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    DF_FormRepository::save_entry(
      $form_id,
      $data,
      $_SERVER['REMOTE_ADDR'] ?? '',
      $user_agent
    );

    /* =====================================================
     * Success
     * ===================================================== */
    DF_Response::success(
      $settings['success_message']
        ?? __('Thank you! Your submission has been received.', 'dynamic-form')
    );
  }
}
