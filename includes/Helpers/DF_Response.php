<?php
defined('ABSPATH') || exit;

/**
 * Class DF_Response
 *
 * Centralized response helper for Dynamic Form plugin.
 * Responsibility:
 * - Send standardized success responses
 * - Send standardized error responses
 * - Keep AJAX handlers clean & consistent
 */
class DF_Response
{

  /**
   * Send success response
   *
   * @param string $message
   * @param array  $data
   * @param int    $status
   */
  public static function success(
    string $message = '',
    array $data = [],
    int $status = 200
  ): void {

    wp_send_json_success(
      array_merge(
        ['message' => $message],
        $data
      ),
      $status
    );
  }

  /**
   * Send error response
   *
   * @param string $message
   * @param int    $status
   * @param array  $data
   */
  public static function error(
    string $message,
    int $status = 400,
    array $data = []
  ): void {

    wp_send_json_error(
      array_merge(
        ['message' => $message],
        $data
      ),
      $status
    );
  }

  /**
   * Unauthorized response shortcut
   */
  public static function unauthorized(): void
  {
    self::error(
      __('Unauthorized request.', 'dynamic-form'),
      403
    );
  }

  /**
   * Security failure response shortcut
   */
  public static function security_failed(): void
  {
    self::error(
      __('Security check failed.', 'dynamic-form'),
      403
    );
  }

  /**
   * Validation error shortcut
   */
  public static function validation_error(string $message): void
  {
    self::error($message, 422);
  }
}
