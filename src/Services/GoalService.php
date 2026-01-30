<?php
/**
 * Goal Service class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Services;

use HealthyJointGoals\PostTypes\GoalPostType;

/**
 * Goal Service class.
 *
 * Handles business logic for goals.
 * Follows Single Responsibility Principle.
 */
class GoalService {
	/**
	 * Update goal meta data.
	 *
	 * @param int   $post_id   Post ID.
	 * @param array $goal_data Goal data array.
	 * @return bool Success status.
	 */
	public function update_goal_meta( $post_id, $goal_data ) {
		$fields = array(
			'goal_type'     => '_hj_goal_type',
			'target_value'  => '_hj_goal_target_value',
			'current_value' => '_hj_goal_current_value',
			'unit'          => '_hj_goal_unit',
			'start_date'    => '_hj_goal_start_date',
			'end_date'      => '_hj_goal_end_date',
			'status'        => '_hj_goal_status',
			'priority'      => '_hj_goal_priority',
		);

		foreach ( $fields as $key => $meta_key ) {
			if ( isset( $goal_data[ $key ] ) ) {
				update_post_meta( $post_id, $meta_key, $goal_data[ $key ] );
			}
		}

		// Calculate and update progress percentage.
		$this->update_goal_progress( $post_id );

		return true;
	}

	/**
	 * Calculate and update goal progress.
	 *
	 * @param int $post_id Post ID.
	 * @return float Progress percentage.
	 */
	public function update_goal_progress( $post_id ) {
		$current_value = get_post_meta( $post_id, '_hj_goal_current_value', true );
		$target_value  = get_post_meta( $post_id, '_hj_goal_target_value', true );

		if ( empty( $target_value ) || $target_value <= 0 ) {
			$progress = 0;
		} else {
			$progress = ( $current_value / $target_value ) * 100;
			$progress = min( 100, max( 0, $progress ) ); // Clamp between 0 and 100.
		}

		update_post_meta( $post_id, '_hj_goal_progress_percentage', $progress );

		return $progress;
	}

	/**
	 * Get total goals count.
	 *
	 * @return int
	 */
	public function get_total_goals_count() {
		$goals = get_posts(
			array(
				'post_type'      => GoalPostType::get_post_type(),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		return count( $goals );
	}

	/**
	 * Get active goals count.
	 *
	 * @return int
	 */
	public function get_active_goals_count() {
		$goals = get_posts(
			array(
				'post_type'      => GoalPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_hj_goal_status',
						'value'   => 'active',
						'compare' => '=',
					),
				),
			)
		);

		return count( $goals );
	}

	/**
	 * Get completed goals count.
	 *
	 * @return int
	 */
	public function get_completed_goals_count() {
		$goals = get_posts(
			array(
				'post_type'      => GoalPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_hj_goal_status',
						'value'   => 'completed',
						'compare' => '=',
					),
				),
			)
		);

		return count( $goals );
	}

	/**
	 * Get recent goals.
	 *
	 * @param int $limit Number of goals to retrieve.
	 * @return array
	 */
	public function get_recent_goals( $limit = 5 ) {
		return get_posts(
			array(
				'post_type'      => GoalPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	/**
	 * Get goals by status.
	 *
	 * @param string $status Goal status.
	 * @return array
	 */
	public function get_goals_by_status( $status ) {
		return get_posts(
			array(
				'post_type'      => GoalPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_hj_goal_status',
						'value'   => $status,
						'compare' => '=',
					),
				),
			)
		);
	}

	/**
	 * Get goal progress data.
	 *
	 * @param int $post_id Goal post ID.
	 * @return array
	 */
	public function get_goal_progress_data( $post_id ) {
		$current_value = get_post_meta( $post_id, '_hj_goal_current_value', true );
		$target_value  = get_post_meta( $post_id, '_hj_goal_target_value', true );
		$unit          = get_post_meta( $post_id, '_hj_goal_unit', true );
		$progress      = get_post_meta( $post_id, '_hj_goal_progress_percentage', true );

		return array(
			'current_value'       => floatval( $current_value ),
			'target_value'        => floatval( $target_value ),
			'unit'                => $unit,
			'progress_percentage' => floatval( $progress ),
		);
	}

	/**
	 * Update goal current value from checkin.
	 *
	 * @param int   $goal_id Goal post ID.
	 * @param float $value   New value to add/update.
	 * @param bool  $is_cumulative Whether to add to existing value or replace.
	 * @return bool
	 */
	public function update_goal_from_checkin( $goal_id, $value, $is_cumulative = true ) {
		if ( $is_cumulative ) {
			$current_value = get_post_meta( $goal_id, '_hj_goal_current_value', true );
			$new_value     = floatval( $current_value ) + floatval( $value );
		} else {
			$new_value = floatval( $value );
		}

		update_post_meta( $goal_id, '_hj_goal_current_value', $new_value );
		$this->update_goal_progress( $goal_id );

		return true;
	}

	/**
	 * Check if goal is completed based on target.
	 *
	 * @param int $post_id Goal post ID.
	 * @return bool
	 */
	public function is_goal_completed( $post_id ) {
		$current_value = get_post_meta( $post_id, '_hj_goal_current_value', true );
		$target_value  = get_post_meta( $post_id, '_hj_goal_target_value', true );

		return floatval( $current_value ) >= floatval( $target_value );
	}

	/**
	 * Auto-complete goal if target is reached.
	 *
	 * @param int $post_id Goal post ID.
	 * @return bool Whether goal was auto-completed.
	 */
	public function auto_complete_goal( $post_id ) {
		if ( $this->is_goal_completed( $post_id ) ) {
			$current_status = get_post_meta( $post_id, '_hj_goal_status', true );

			if ( 'completed' !== $current_status ) {
				update_post_meta( $post_id, '_hj_goal_status', 'completed' );
				update_post_meta( $post_id, '_hj_goal_completed_date', current_time( 'mysql' ) );
				return true;
			}
		}

		return false;
	}

	/**
	 * Manually complete a goal.
	 *
	 * @param int $post_id Goal post ID.
	 * @return bool Success status.
	 */
	public function complete_goal( $post_id ) {
		$current_status = get_post_meta( $post_id, '_hj_goal_status', true );

		if ( 'completed' === $current_status ) {
			return false; // Already completed.
		}

		update_post_meta( $post_id, '_hj_goal_status', 'completed' );
		update_post_meta( $post_id, '_hj_goal_completed_date', current_time( 'mysql' ) );

		// Optionally update progress to 100% when manually completed.
		update_post_meta( $post_id, '_hj_goal_progress_percentage', 100 );

		return true;
	}

	/**
	 * Get in-progress goals.
	 *
	 * @param int $user_id User ID (0 for current user).
	 * @return array
	 */
	public function get_inprogress_goals( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return get_posts(
			array(
				'post_type'      => GoalPostType::get_post_type(),
				'post_status'    => 'publish',
				'author'         => $user_id,
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_hj_goal_status',
						'value'   => 'inprogress',
						'compare' => '=',
					),
					array(
						'key'     => '_hj_goal_status',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);
	}
}
