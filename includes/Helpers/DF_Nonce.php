<?php
defined('ABSPATH') || exit;

/**
 * Class DF_Nonce
 *
 * Centralized nonce handling for Dynamic Form plugin.
 * Purpose:
 * - Create nonces
 * - Verify nonces
 * - Avoid hard-coding nonce strings everywhere
 */
class DF_Nonce
{

  /**
   * Nonce actions (constants)
   */
  const ADMIN_ACTION    = 'df_admin_nonce';
  const FRONTEND_ACTION = 'df_frontend_submit';

  /**
   * Create admin nonce
   */
  public static function create_admin(): string
  {
    return wp_create_nonce(self::ADMIN_ACTION);
  }

  /**
   * Verify admin nonce (AJAX / admin forms)
   */
  public static function verify_admin(string $nonce): bool
  {
    return wp_verify_nonce($nonce, self::ADMIN_ACTION) === 1;
  }

  /**
   * Create frontend nonce
   */
  public static function create_frontend(): string
  {
    return wp_create_nonce(self::FRONTEND_ACTION);
  }

  /**
   * Verify frontend nonce
   */
  public static function verify_frontend(string $nonce): bool
  {
    return wp_verify_nonce($nonce, self::FRONTEND_ACTION) === 1;
  }

  /**
   * Output hidden nonce field for frontend forms
   */
  public static function field_frontend(): void
  {
    wp_nonce_field(self::FRONTEND_ACTION, '_df_nonce');
  }

  /**
   * Output hidden nonce field for admin forms
   */
  public static function field_admin(): void
  {
    wp_nonce_field(self::ADMIN_ACTION, '_df_admin_nonce');
  }
}
