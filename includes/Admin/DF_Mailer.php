<?php
defined('ABSPATH') || exit;


// require '../vendor/autoload.php';

/**
 * Class DF_Mailer
 *
 * Configures SMTP using WordPress PHPMailer.
 */
class DF_Mailer
{
  static $mail = NULL;
  public static function init(): void
  {
    // self::$mail = new PHPMailer();
    add_action('phpmailer_init', [self::class, 'configure']);
  }

  public static function configure($phpmailer): void
  {
    error_log("=========================================================================================================================");
    error_log('SMTP CONFIGURED');
    error_log("=========================================================================================================================");

    if (!DF_Settings::get('smtp_enabled')) {
      return;
    }
    $host = DF_Settings::get('smtp_host');
    $port = (int) DF_Settings::get('smtp_port');
    $user = DF_Settings::get('smtp_user');
    $pass = DF_Settings::get('smtp_pass');
    $enc  = DF_Settings::get('smtp_encryption');
    error_log($host . ">>>>>>>>>>>>>>>>>" . $port . ">>>>>>>>>>>>>>>>>" . $user . ">>>>>>>>>>>>>>>>>" . $pass . ">>>>>>>>>>>>>>>>>" . $enc);
    if (!$host || !$port) {
      return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->Port       = $port;
    $phpmailer->SMTPAuth   = !empty($user);
    $phpmailer->Username   = $user;
    $phpmailer->Password   = $pass;
    $phpmailer->SMTPSecure = $enc ?: '';

    $phpmailer->From     =  DF_Settings::get('smtp_user');

    if (defined('WP_DEBUG') && WP_DEBUG) {
      $phpmailer->SMTPDebug = 0; // keep 0 unless actively debugging
    }
  }
}
