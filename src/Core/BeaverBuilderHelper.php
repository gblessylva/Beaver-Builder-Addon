<?php
/**
 * Beaver Builder Utility Helper for HealthyJoint Goals Plugin
 *
 * @package HealthyJoint_Goals
 */

namespace HealthyJointGoals\Core;

/**
 * Class BeaverBuilderHelper
 *
 * Provides utility methods for Beaver Builder modules.
 */
class BeaverBuilderHelper {

	/**
	 * Get goals for dropdown options
	 *
	 * @return array Array of goal options for select dropdown
	 */
	public static function get_goals_for_dropdown() {
		$options = array(
			'' => __( '-- Select a Goal for Preview --', 'healthyjoint-goals' ),
		);

		// Only load goals if user has admin capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $options;
		}

		// Query goals.
		$goals = get_posts(
			array(
				'post_type'      => 'hj_goal',
				'post_status'    => array( 'publish', 'private', 'draft' ),
				'posts_per_page' => 100, // Limit to prevent performance issues.
				'orderby'        => 'date',
				'order'          => 'DESC',

			)
		);

		if ( ! empty( $goals ) ) {
			foreach ( $goals as $goal ) {
				$options[ $goal->ID ] = $goal->ID;
			}
		} else {
			$options['no_goals'] = __( 'No goals found', 'healthyjoint-goals' );
		}

		return $options;
	}

	/**
	 * Get check-ins for dropdown options
	 *
	 * @return array Array of check-in options for select dropdown
	 */
	public static function get_checkins_for_dropdown() {
		$options = array(
			'' => __( '-- Select a Check-in for Preview --', 'healthyjoint-goals' ),
		);

		// Only load check-ins if user has admin capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $options;
		}

		// Query check-ins.
		$checkins = get_posts(
			array(
				'post_type'      => 'hj_checkin',
				'post_status'    => array( 'publish', 'private', 'draft' ),
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $checkins ) ) {
			foreach ( $checkins as $checkin ) {
				$goal_id     = get_post_meta( $checkin->ID, 'goal_id', true );
				$author_name = get_the_author_meta( 'display_name', $checkin->post_author );
				$date        = get_the_date( 'Y-m-d', $checkin );
				
				$option_label = sprintf(
					'ID: %d - %s (Goal: %s) - %s',
					$checkin->ID,
					$date,
					$goal_id ? $goal_id : 'N/A',
					esc_html( $author_name )
				);

				$options[ $checkin->ID ] = $option_label;
			}
		} else {
			$options['no_checkins'] = __( 'No check-ins found', 'healthyjoint-goals' );
		}

		return $options;
	}

	/**
	 * Get goal ID with fallback logic for modules
	 *
	 * @param object $settings Module settings object.
	 * @param string $test_field_name Name of the test field in settings.
	 * @param string $url_param_name Name of the URL parameter.
	 * @return int Goal ID or 0 if none found.
	 */
	public static function get_goal_id_with_fallback( $settings, $test_field_name = 'test_goal_id', $url_param_name = 'goal_id' ) {
		$goal_id = 0;
		
		// Check for test goal ID if admin is previewing.
		if ( ! empty( $settings->{$test_field_name} ) && current_user_can( 'manage_options' ) ) {
			$goal_id = intval( $settings->{$test_field_name} );
		} elseif ( isset( $_GET[ $url_param_name ] ) ) {
			// Use URL parameter for normal operation.
			$goal_id = intval( sanitize_text_field( wp_unslash( $_GET[ $url_param_name ] ) ) );
		}
		
		return $goal_id;
	}
}
