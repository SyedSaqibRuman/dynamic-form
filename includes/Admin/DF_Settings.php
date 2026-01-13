<?php
defined('ABSPATH') || exit;

/**
 * Class DF_Settings
 *
 * Handles plugin settings:
 * - Email settings
 * - Turnstile settings
 */
class DF_Settings
{
  const OPTION_KEY = 'df_settings';

  public static function init(): void
  {
    add_action('admin_menu', [self::class, 'register_menu']);
    add_action('admin_init', [self::class, 'register_settings']);
    add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
  }

  public static function register_menu(): void
  {
    add_submenu_page(
      'df-forms',
      __('Settings', 'dynamic-form'),
      __('Settings', 'dynamic-form'),
      'manage_options',
      'df-settings',
      [self::class, 'render_page']
    );
  }

  public static function register_settings(): void
  {
    register_setting(
      'df_settings_group',
      self::OPTION_KEY,
      [self::class, 'sanitize']
    );
  }

  public static function sanitize(array $input): array
  {
    $sanitized = [
      /* ---------- Email ---------- */
      'admin_email_enabled' => !empty($input['admin_email_enabled']),
      'user_email_enabled'  => !empty($input['user_email_enabled']),
      'admin_email'         => sanitize_email($input['admin_email'] ?? ''),
      'from_email'          => sanitize_email($input['from_email'] ?? ''),
      'admin_subject'       => sanitize_text_field($input['admin_subject'] ?? ''),
      'user_subject'        => sanitize_text_field($input['user_subject'] ?? ''),

      /* ---------- SMTP ---------- */
      'smtp_enabled'    => !empty($input['smtp_enabled']),
      'smtp_host'       => sanitize_text_field($input['smtp_host'] ?? ''),
      'smtp_port'       => absint($input['smtp_port'] ?? 0),
      'smtp_user'       => sanitize_text_field($input['smtp_user'] ?? ''),
      'smtp_pass'       => sanitize_text_field($input['smtp_pass'] ?? ''),
      'smtp_encryption' => in_array(
        $input['smtp_encryption'] ?? '',
        ['ssl', 'tls', ''],
        true
      ) ? $input['smtp_encryption'] : '',

      /* ---------- Turnstile ---------- */
      'turnstile_enabled'   => !empty($input['turnstile_enabled']),
      'turnstile_site_key'  => sanitize_text_field($input['turnstile_site_key'] ?? ''),
      'turnstile_secret'    => sanitize_text_field($input['turnstile_secret'] ?? ''),
      'turnstile_mode'      => in_array(
        $input['turnstile_mode'] ?? 'managed',
        ['managed', 'invisible'],
        true
      ) ? $input['turnstile_mode'] : 'managed',

      /* ---------- Redirect ---------- */
      'redirect_url' => esc_url_raw($input['redirect_url'] ?? ''),
    ];

    $existing = get_option(self::OPTION_KEY, []);

    // SMTP password
    if (empty($input['smtp_pass']) && array_key_exists('smtp_pass', $existing)) {
      $sanitized['smtp_pass'] = $existing['smtp_pass'];
    }

    // Turnstile secret
    if (empty($input['turnstile_secret']) && array_key_exists('turnstile_secret', $existing)) {
      $sanitized['turnstile_secret'] = $existing['turnstile_secret'];
    }


    return $sanitized;
  }

  public static function enqueue_assets(string $hook): void
  {
    if ($hook !== 'dynamic-form_page_df-settings') {
      return;
    }

    wp_enqueue_style(
      'df-settings',
      DF_URL . 'includes/Assets/css/settings.css',
      [],
      DF_VERSION
    );

    wp_enqueue_script(
      'df-settings',
      DF_URL . 'includes/Assets/js/settings.js',
      ['jquery'],
      DF_VERSION,
      true
    );

    wp_localize_script(
      'df-settings',
      'dfSettings',
      [
        'nonce' => DF_Nonce::create_admin(),
      ]
    );
  }

  public static function render_page(): void
  {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have permission to access this page.'));
    }

    $settings = get_option(self::OPTION_KEY, []);
    $admin_email_fallback = $settings['admin_email'] ?? DF_Settings::get('smtp_user');
    $from_email_fallback = $settings['from_email'] ?? $admin_email_fallback;
?>
    <div class="wrap">
      <h1><?php esc_html_e('Dynamic Form – Settings', 'dynamic-form'); ?></h1>

      <form method="post" action="options.php">
        <?php settings_fields('df_settings_group'); ?>

        <!-- ================= EMAIL SETTINGS ================= -->
        <h2><?php esc_html_e('Email Settings', 'dynamic-form'); ?></h2>

        <table class="form-table">
          <tr>
            <th scope="row"><?php esc_html_e('Enable Admin Email', 'dynamic-form'); ?></th>
            <td>
              <input type="checkbox"
                id="admin_email_enabled"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_email_enabled]"
                <?php checked(!empty($settings['admin_email_enabled'])); ?>>
              <p class="description"><?php esc_html_e('Send form submissions to admin email.', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('Enable User Email', 'dynamic-form'); ?></th>
            <td>
              <input type="checkbox"
                id="user_email_enabled"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[user_email_enabled]"
                <?php checked(!empty($settings['user_email_enabled'])); ?>>
              <p class="description"><?php esc_html_e('Send confirmation email to form submitter.', 'dynamic-form'); ?></p>
            </td>
          </tr>



          <tr>
            <th scope="row"><?php esc_html_e('From Email', 'dynamic-form'); ?></th>
            <td>
              <input type="email"
                class="regular-text ltr"
                required
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[from_email]"
                value="<?php echo esc_attr($from_email_fallback); ?>"
                placeholder="<?php esc_attr__('noreply@example.com', 'dynamic-form'); ?>">
              <p class="description"><?php esc_html_e('Sender email address for outgoing emails.', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('To Email', 'dynamic-form'); ?></th>
            <td>
              <input type="email"
                class="regular-text ltr"
                required
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_email]"
                value="<?php echo esc_attr($admin_email_fallback); ?>"
                placeholder="<?php esc_html_e('admin@example.com', 'dynamic-form'); ?>">
              <p class="description"><?php esc_html_e('Email address to receive form submissions.', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('Admin Email Subject', 'dynamic-form'); ?></th>
            <td>
              <input type="text"
                class="regular-text"
                required
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_subject]"
                value="<?php echo esc_attr($settings['admin_subject'] ?? 'New Form Submission'); ?>"
                placeholder="<?php esc_html_e('New Form Submission', 'dynamic-form'); ?>">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('User Email Subject', 'dynamic-form'); ?></th>
            <td>
              <input type="text"
                class="regular-text"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[user_subject]"
                value="<?php echo esc_attr($settings['user_subject'] ?? 'Thank you for contacting us'); ?>"
                placeholder="<?php esc_html_e('Thank you for contacting us', 'dynamic-form'); ?>">
            </td>
          </tr>
        </table>

        <!-- ================= SMTP SETTINGS ================= -->
        <h2><?php esc_html_e('SMTP Settings', 'dynamic-form'); ?></h2>
        <p class="description"><?php esc_html_e('Configure SMTP for reliable email delivery.', 'dynamic-form'); ?></p>

        <table class="form-table">
          <tr>
            <th scope="row"><?php esc_html_e('Enable SMTP', 'dynamic-form'); ?></th>
            <td>
              <input type="checkbox"
                id="smtp_enabled"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[smtp_enabled]"
                <?php checked(!empty($settings['smtp_enabled'])); ?>>
              <p class="description"><?php esc_html_e('Use custom SMTP server instead of WordPress default.', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('SMTP Host', 'dynamic-form'); ?></th>
            <td>
              <input type="text"
                class="regular-text ltr"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[smtp_host]"
                value="<?php echo esc_attr($settings['smtp_host'] ?? ''); ?>"
                placeholder="SMPT Host">
              <p class="description"><?php esc_html_e('SMTP server hostname.', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('SMTP Port', 'dynamic-form'); ?></th>
            <td>
              <input type="number"
                min="1"
                max="65535"
                class="small-text"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[smtp_port]"
                value="<?php echo esc_attr($settings['smtp_port'] ?? 587); ?>">
              <p class="description"><?php esc_html_e('Common ports: 25, 465 (SSL), 587 (TLS)', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('Encryption', 'dynamic-form'); ?></th>
            <td>
              <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[smtp_encryption]">
                <option value="" <?php selected($settings['smtp_encryption'] ?? '', ''); ?>>None</option>
                <option value="tls" <?php selected($settings['smtp_encryption'] ?? '', 'tls'); ?>>TLS</option>
                <option value="ssl" <?php selected($settings['smtp_encryption'] ?? '', 'ssl'); ?>>SSL</option>
              </select>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('SMTP Username', 'dynamic-form'); ?></th>
            <td>
              <input type="text"
                class="regular-text ltr"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[smtp_user]"
                value="<?php echo esc_attr($settings['smtp_user'] ?? ''); ?>"
                placeholder="your-email@gmail.com">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('SMTP Password', 'dynamic-form'); ?></th>
            <td>
              <input type="password"
                class="regular-text ltr"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[smtp_pass]"
                placeholder="<?php esc_html_e('Leave empty to keep current password', 'dynamic-form'); ?>">
              <p class="description"><?php esc_html_e('App password recommended for Gmail.', 'dynamic-form'); ?></p>
            </td>
          </tr>
        </table>

        <!-- ================= TURNSTILE SETTINGS ================= -->
        <h2><?php esc_html_e('Turnstile Settings', 'dynamic-form'); ?></h2>

        <table class="form-table">
          <tr>
            <th scope="row"><?php esc_html_e('Enable Turnstile', 'dynamic-form'); ?></th>
            <td>
              <input type="checkbox"
                id="turnstile_enabled"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[turnstile_enabled]"
                <?php checked(!empty($settings['turnstile_enabled'])); ?>>
              <p class="description"><?php esc_html_e('Protect forms from spam bots.', 'dynamic-form'); ?></p>
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('Site Key', 'dynamic-form'); ?></th>
            <td>
              <input type="text"
                class="regular-text code ltr"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[turnstile_site_key]"
                value="<?php echo esc_attr($settings['turnstile_site_key'] ?? ''); ?>"
                placeholder="0x4AAAAAA...">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('Secret Key', 'dynamic-form'); ?></th>
            <td>
              <input type="password"
                class="regular-text code ltr"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[turnstile_secret]"
                placeholder="<?php esc_html_e('Leave empty to keep current key', 'dynamic-form'); ?>">
            </td>
          </tr>

          <tr>
            <th scope="row"><?php esc_html_e('Mode', 'dynamic-form'); ?></th>
            <td>
              <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[turnstile_mode]">
                <option value="managed" <?php selected($settings['turnstile_mode'] ?? 'managed', 'managed'); ?>>Managed</option>
                <option value="invisible" <?php selected($settings['turnstile_mode'] ?? 'managed', 'invisible'); ?>>Invisible</option>
              </select>
            </td>

            <!-- ================= REDIRECTION URL SETTINGS ================= -->
          <tr>
            <th scope="row">
              <?php esc_html_e('Form Redirect URL', 'dynamic-form'); ?>
            </th>
            <td>
              <input
                type="url"
                name="<?php echo esc_attr(self::OPTION_KEY); ?>[redirect_url]"
                value="<?php echo esc_url_raw($settings['redirect_url'] ?? ''); ?>"
                placeholder="<?php echo esc_attr__('https://example.com/thank-you', 'dynamic-form'); ?>"
                class="regular-text">
              <p class="description">
                <?php esc_html_e('Redirect users to this URL after successful submission.', 'dynamic-form'); ?>
              </p>
            </td>
          </tr>

        </table>

        <?php submit_button(); ?>

        <button type="button" class="button" id="df-test-email">
          <?php esc_html_e('Send Test Email', 'dynamic-form'); ?>
        </button>

        <p id="df-test-email-result" style="margin-top: 1em;"></p>
      </form>
    </div>
<?php
  }

  public static function get(string $key, $default = ''): mixed
  {
    $settings = get_option(self::OPTION_KEY, []);
    return $settings[$key] ?? $default;
  }
}
