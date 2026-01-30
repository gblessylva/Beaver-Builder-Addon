<?php
/**
 * Checkin Service class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Services;

use HealthyJointGoals\PostTypes\CheckinPostType;

/**
 * Checkin Service class.
 *
 * Handles business logic for check-ins.
 * Follows Single Responsibility Principle.
 */
class CheckinService {
	/**
	 * Update checkin meta data.
	 *
	 * @param int   $post_id      Post ID.
	 * @param array $checkin_data Checkin data array.
	 * @return bool Success status.
	 */
	public function update_checkin_meta( $post_id, $checkin_data ) {
		$fields = array(
			'goal_id'        => '_hj_checkin_goal_id',
			'progress_value' => '_hj_checkin_progress_value',
			'checkin_date'   => '_hj_checkin_date',
			'mood_rating'    => '_hj_checkin_mood_rating',
			'energy_level'   => '_hj_checkin_energy_level',
			'notes'          => '_hj_checkin_notes',
		);

		foreach ( $fields as $key => $meta_key ) {
			if ( isset( $checkin_data[ $key ] ) ) {
				update_post_meta( $post_id, $meta_key, $checkin_data[ $key ] );
			}
		}

		// Update related goal if goal_id is provided.
		if ( ! empty( $checkin_data['goal_id'] ) && ! empty( $checkin_data['progress_value'] ) ) {
			$this->update_related_goal( $checkin_data['goal_id'], $checkin_data['progress_value'] );
		}

		return true;
	}

	/**
	 * Update related goal with checkin progress.
	 *
	 * @param int   $goal_id Goal post ID.
	 * @param float $progress_value Progress value.
	 * @return bool
	 */
	public function update_related_goal( $goal_id, $progress_value ) {
		// Get goal service from plugin container.
		$plugin       = \HealthyJointGoals\Core\Plugin::get_instance();
		$goal_service = $plugin->get_service( 'goal_service' );

		if ( $goal_service ) {
			return $goal_service->update_goal_from_checkin( $goal_id, $progress_value, false );
		}

		return false;
	}

	/**
	 * Get total checkins count.
	 *
	 * @return int
	 */
	public function get_total_checkins_count() {
		$checkins = get_posts(
			array(
				'post_type'      => CheckinPostType::get_post_type(),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		return count( $checkins );
	}

	/**
	 * Get recent checkins.
	 *
	 * @param int $limit Number of checkins to retrieve.
	 * @return array
	 */
	public function get_recent_checkins( $limit = 5 ) {
		return get_posts(
			array(
				'post_type'      => CheckinPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	/**
	 * Get checkins for a specific goal.
	 *
	 * @param int $goal_id Goal post ID.
	 * @param int $limit   Number of checkins to retrieve.
	 * @return array
	 */
	public function get_checkins_for_goal( $goal_id, $limit = -1 ) {
		return get_posts(
			array(
				'post_type'      => CheckinPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_hj_checkin_goal_id',
						'value'   => $goal_id,
						'compare' => '=',
					),
				),
			)
		);
	}

	/**
	 * Get checkin statistics for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d format).
	 * @param string $end_date   End date (Y-m-d format).
	 * @return array
	 */
	public function get_checkin_statistics( $start_date, $end_date ) {
		$checkins = get_posts(
			array(
				'post_type'      => CheckinPostType::get_post_type(),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_hj_checkin_date',
						'value'   => array( $start_date, $end_date ),
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					),
				),
			)
		);

		$total_checkins = count( $checkins );
		$mood_sum       = 0;
		$energy_sum     = 0;
		$mood_count     = 0;
		$energy_count   = 0;

		foreach ( $checkins as $checkin ) {
			$mood   = get_post_meta( $checkin->ID, '_hj_checkin_mood_rating', true );
			$energy = get_post_meta( $checkin->ID, '_hj_checkin_energy_level', true );

			if ( ! empty( $mood ) ) {
				$mood_sum += intval( $mood );
				++$mood_count;
			}

			if ( ! empty( $energy ) ) {
				$energy_sum += intval( $energy );
				++$energy_count;
			}
		}

		return array(
			'total_checkins' => $total_checkins,
			'average_mood'   => $mood_count > 0 ? round( $mood_sum / $mood_count, 2 ) : 0,
			'average_energy' => $energy_count > 0 ? round( $energy_sum / $energy_count, 2 ) : 0,
			'mood_count'     => $mood_count,
			'energy_count'   => $energy_count,
		);
	}

	/**
	 * Get checkin data for goal progress tracking.
	 *
	 * @param int $goal_id Goal post ID.
	 * @return array
	 */
	public function get_goal_progress_data( $goal_id ) {
		$checkins      = $this->get_checkins_for_goal( $goal_id );
		$progress_data = array();

		foreach ( $checkins as $checkin ) {
			$date     = get_post_meta( $checkin->ID, '_hj_checkin_date', true );
			$progress = get_post_meta( $checkin->ID, '_hj_checkin_progress_value', true );

			if ( $date && $progress !== '' ) {
				$progress_data[] = array(
					'date'       => $date,
					'progress'   => floatval( $progress ),
					'checkin_id' => $checkin->ID,
				);
			}
		}

		// Sort by date ascending.
		usort(
			$progress_data,
			function ( $a, $b ) {
				return strcmp( $a['date'], $b['date'] );
			}
		);

		return $progress_data;
	}

	/**
	 * Delete checkin and update related goal.
	 *
	 * @param int $post_id Checkin post ID.
	 * @return bool
	 */
	public function delete_checkin_and_update_goal( $post_id ) {
		$goal_id = get_post_meta( $post_id, '_hj_checkin_goal_id', true );

		// Delete the checkin.
		$deleted = wp_delete_post( $post_id, true );

		// Recalculate goal progress if there was a related goal.
		if ( $deleted && ! empty( $goal_id ) ) {
			$this->recalculate_goal_progress( $goal_id );
		}

		return (bool) $deleted;
	}

	/**
	 * Get goal statistics.
	 *
	 * @param int $goal_id Goal post ID.
	 * @return array
	 */
	public function get_goal_statistics( $goal_id ) {
		$checkins = $this->get_checkins_for_goal( $goal_id );

		$total_checkins = count( $checkins );
		$ratings_stats  = array();

		if ( $total_checkins > 0 ) {
			$rating_types = array_keys( CheckinPostType::get_rating_types() );

			foreach ( $rating_types as $type ) {
				$ratings = array();

				foreach ( $checkins as $checkin ) {
					$checkin_ratings = get_post_meta( $checkin->ID, 'hj_ratings', true );
					if ( is_array( $checkin_ratings ) && isset( $checkin_ratings[ $type ] ) ) {
						$ratings[] = intval( $checkin_ratings[ $type ] );
					}
				}

				if ( ! empty( $ratings ) ) {
					$ratings_stats[ $type ] = array(
						'average' => round( array_sum( $ratings ) / count( $ratings ), 2 ),
						'count'   => count( $ratings ),
						'latest'  => end( $ratings ),
					);
				}
			}
		}

		return array(
			'total_checkins' => $total_checkins,
			'ratings_stats'  => $ratings_stats,
			'latest_checkin' => ! empty( $checkins ) ? $checkins[0]->post_date : null,
		);
	}

	/**
	 * Recalculate goal progress based on all checkins.
	 *
	 * @param int $goal_id Goal post ID.
	 * @return bool
	 */
	private function recalculate_goal_progress( $goal_id ) {
		$checkins = $this->get_checkins_for_goal( $goal_id );

		if ( empty( $checkins ) ) {
			update_post_meta( $goal_id, '_hj_goal_current_value', 0 );
		} else {
			// Get the latest checkin progress value.
			$latest_checkin  = $checkins[0]; // Already ordered by date DESC.
			$latest_progress = get_post_meta( $latest_checkin->ID, '_hj_checkin_progress_value', true );

			update_post_meta( $goal_id, '_hj_goal_current_value', floatval( $latest_progress ) );
		}

		// Update goal progress percentage.
		$plugin       = \HealthyJointGoals\Core\Plugin::get_instance();
		$goal_service = $plugin->get_service( 'goal_service' );

		if ( $goal_service ) {
			$goal_service->update_goal_progress( $goal_id );
		}

		return true;
	}
}
