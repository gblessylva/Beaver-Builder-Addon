<?php
/**
 * Beaver Builder Integration
 *
 * @package HealthyJoint_Goals
 */

namespace HealthyJointGoals\Integrations;

use HealthyJointGoals\Interfaces\Registrable;

/**
 * Beaver Builder integration class.
 */
class BeaverBuilderIntegration implements Registrable {

	/**
	 * Register the integration.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'load_modules' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Load Beaver Builder modules.
	 *
	 * @return void
	 */
	public function load_modules() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return;
		}

		// Define modules directory.
		$modules_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'beaver-builder/modules/';
		
		// Load Add Goal module.
		if ( file_exists( $modules_dir . 'add-goal/add-goal.php' ) ) {
			require_once $modules_dir . 'add-goal/add-goal.php';
		}

		// Load Check In module.
		if ( file_exists( $modules_dir . 'check-in/check-in.php' ) ) {
			require_once $modules_dir . 'check-in/check-in.php';
		}

		// Load Edit Goal module.
		if ( file_exists( $modules_dir . 'edit-goal/edit-goal.php' ) ) {
			require_once $modules_dir . 'edit-goal/edit-goal.php';
		}

		// Load Progress module.
		if ( file_exists( $modules_dir . 'progress/progress.php' ) ) {
			require_once $modules_dir . 'progress/progress.php';
		}

		// Load Add to Favourite module.
		if ( file_exists( $modules_dir . 'add-to-favourite/add-to-favourite.php' ) ) {
			require_once $modules_dir . 'add-to-favourite/add-to-favourite.php';
		}

		// Load Dashboard module.
		if ( file_exists( $modules_dir . 'dashboard/dashboard.php' ) ) {
			require_once $modules_dir . 'dashboard/dashboard.php';
		}
	}

	/**
	 * Enqueue scripts and styles for Beaver Builder modules.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! class_exists( 'FLBuilderModel' ) ) {
			return;
		}

		// Only enqueue on pages with Beaver Builder content or in builder mode.
		if ( ! \FLBuilderModel::is_builder_enabled() && ! $this->has_bb_content() ) {
			return;
		}

		// Enqueue REST API nonce for AJAX requests.
		wp_localize_script(
			'jquery',
			'hjGoalAjax',
			array(
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'restUrl' => rest_url( 'healthyjoint/v1/' ),
			)
		);
	}

	/**
	 * Check if current page has Beaver Builder content.
	 *
	 * @return bool
	 */
	private function has_bb_content() {
		global $post;

		if ( ! $post ) {
			return false;
		}

		// Check if the post has Beaver Builder data.
		$bb_enabled = get_post_meta( $post->ID, '_fl_builder_enabled', true );
		$bb_data    = get_post_meta( $post->ID, '_fl_builder_data', true );

		return ! empty( $bb_enabled ) || ! empty( $bb_data );
	}
}