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
    add_action('wp_ajax_df_delete_entry', [self::class, 'delete_entry']);
    add_action('wp_ajax_df_test_email', [self::class, 'test_email']);
  }

  /* =====================================================
   * Security
   * ===================================================== */
  private static function verify(): void
  {

    // error_log('Nonce received: ' . ($_POST['_ajax_nonce'] ?? 'MISSING'));
    // error_log('Expected nonce:>>>>>>>>>>>>>>>>>>>>>>>' . DF_Nonce::create_admin());

    if (!DF_Nonce::verify_admin($_POST['_ajax_nonce'] ?? '')) {
      // error_log(DF_Nonce::create_admin() . ">>>>>>>>>>>>>>>>>>>>>>>>FAILED<<<<<<<<<<<<<<<<<<<<<<<<<<<<" . $_POST['_ajax_nonce']);
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
   * Delete an Entry
   * ===================================================== */
  public static function delete_entry(): void
  {
    //error_log("I am here<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    /* ---------- Security ---------- */
    self::verify();

    if (!current_user_can('manage_options')) {
      DF_Response::error(
        __('You do not have permission to delete entries.', 'dynamic-form'),
        403
      );
    }

    /* ---------- Validate Input ---------- */
    $entry_id = absint($_POST['entry_id'] ?? 0);

    if (!$entry_id) {
      DF_Response::error(
        __('Invalid entry ID.', 'dynamic-form'),
        400
      );
    }

    /* ---------- Fetch Entry (for validation + logging) ---------- */
    $entry = DF_FormRepository::get_entry($entry_id);

    if (!$entry) {
      DF_Response::error(
        __('Entry not found.', 'dynamic-form'),
        404
      );
    }

    /* ---------- Delete ---------- */
    $deleted = DF_FormRepository::delete_entry($entry_id);

    if (!$deleted) {
      DF_Response::error(
        __('Failed to delete entry.', 'dynamic-form'),
        500
      );
    }

    /* ---------- Log ---------- */
    error_log(sprintf(
      'DF: Entry #%d (Form #%d) deleted by user #%d',
      $entry_id,
      (int) $entry['form_id'],
      get_current_user_id()
    ));

    /* ---------- Success ---------- */
    DF_Response::success(
      __('Entry deleted successfully.', 'dynamic-form')
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



  public static function send_mail(
    string $to,
    string $subject,
    string $body,
    array $cc = []
  ): bool {
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = DF_Settings::get('smtp_host');
      $mail->SMTPAuth   = true;
      $mail->Username   = DF_Settings::get('smtp_user');
      $mail->Password   = DF_Settings::get('smtp_pass');
      $mail->SMTPSecure = DF_Settings::get('smtp_encryption') ?: 'tls';
      $mail->Port       = (int) DF_Settings::get('smtp_port', 587);

      $mail->setFrom(
        DF_Settings::get('from_email', $mail->Username),
        'Dynamic Form'
      );

      $mail->addAddress($to);

      foreach ($cc as $email) {
        if (is_email($email)) {
          $mail->addCC($email);
        }
      }

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = nl2br($body);

      return $mail->send();
    } catch (\Exception $e) {
      error_log('DF Mail Error: ' . $e->getMessage());
      return false;
    }
  }

  /* =====================================================
   * Test Email
   * ===================================================== */
  public static function test_email(): void
  {
    // error_log('=== test_email() STARTED ===');
    self::verify();

    // error_log('=== PASSED VERIFICATION ===');

    // SAFE option access
    $to = DF_Settings::get('admin_email');

    $cc = DF_Settings::get('cc_email');

    $cc_emails = [];

    if (!empty($cc)) {
      $cc_emails = array_values(
        array_filter(
          array_map(
            'sanitize_email',
            array_map('trim', explode(';', $cc))
          ),
          'is_email'
        )
      );
    }

    // error_log('=== TO EMAIL: ' . $to . ' ===');

    if (!$to || !is_email($to)) {
      // error_log('=== INVALID EMAIL ===');
      DF_Response::error('No valid admin email configured');
      return;
    }

    $body = "SMTP test successful!\n\n"
      . "Server: " . DF_Settings::get('smtp_host') . "\n"
      . "Port: " . DF_Settings::get('smtp_port');

    $sent = self::send_mail(
      $to,
      'Dynamic Form - Test Email',
      $body,
      $cc_emails
    );
    if (!$sent) {
      DF_Response::error('Failed to send test email');
    }

    DF_Response::success('✅ Test email sent successfully!');
  }
}
