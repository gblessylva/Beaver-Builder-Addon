<?php
/**
 * Authentication Helper for HealthyJoint Goals Plugin
 *
 * @package HealthyJoint_Goals
 */

namespace HealthyJointGoals\Core;

/**
 * Class AuthenticationHelper
 *
 * Provides authentication utilities for the HealthyJoint Goals plugin.
 */
class AuthenticationHelper {

	/**
	 * Check if user is logged in and redirect to home if not.
	 *
	 * This method checks if the user is logged in. If not, it redirects
	 * to the home page unless the user is already on the front page.
	 *
	 * @return void
	 */
	public static function require_login() {
		if ( ! is_user_logged_in() ) {
			// Redirect to home page.
			// If user is already on home page do not redirect.
			if ( ! is_front_page() ) {
				wp_safe_redirect( home_url() );
				exit;
			}
		}
	}

	/**
	 * Check if user is logged in and return boolean.
	 *
	 * @return bool True if user is logged in, false otherwise.
	 */
	public static function is_logged_in() {
		return is_user_logged_in();
	}

	/**
	 * Get current user ID if logged in.
	 *
	 * @return int User ID if logged in, 0 otherwise.
	 */
	public static function get_current_user_id() {
		return is_user_logged_in() ? get_current_user_id() : 0;
	}

	/**
	 * Check if current user has specific capability.
	 *
	 * @param string $capability The capability to check.
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function user_can( $capability ) {
		return is_user_logged_in() && current_user_can( $capability );
	}

	/**
	 * Get or generate API token for a user.
	 *
	 * @param int $user_id User ID. If not provided, uses current user.
	 * @return string|false API token string, or false if user not found.
	 */
	public static function get_api_token( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		// Try to get existing token.
		$token = get_user_meta( $user_id, 'hj_api_token', true );

		// If no token exists, generate one.
		if ( empty( $token ) ) {
			$token = self::generate_api_token( $user_id );
		}

		return $token;
	}

	/**
	 * Generate a new API token for a user.
	 *
	 * @param int $user_id User ID.
	 * @return string|false Generated token, or false on failure.
	 */
	public static function generate_api_token( $user_id ) {
		if ( ! $user_id ) {
			return false;
		}

		// Generate a secure random token.
		$token = wp_generate_password( 40, false );

		// Store the token in user meta.
		$updated = update_user_meta( $user_id, 'hj_api_token', $token );

		return $updated ? $token : false;
	}

	/**
	 * Regenerate API token for a user (invalidates old token).
	 *
	 * @param int $user_id User ID. If not provided, uses current user.
	 * @return string|false New token, or false on failure.
	 */
	public static function regenerate_api_token( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		// Delete old token.
		delete_user_meta( $user_id, 'hj_api_token' );

		// Generate new token.
		return self::generate_api_token( $user_id );
	}

	/**
	 * Validate an API token and return the associated user ID.
	 *
	 * @param string $token API token to validate.
	 * @return int|false User ID if valid, false otherwise.
	 */
	public static function validate_api_token( $token ) {
		if ( empty( $token ) ) {
			return false;
		}

		$users = get_users(
			array(
				'meta_key'   => 'hj_api_token',
				'meta_value' => $token,
				'number'     => 1,
				'fields'     => 'ID',
			)
		);

		return ! empty( $users ) ? (int) $users[0] : false;
	}
}
