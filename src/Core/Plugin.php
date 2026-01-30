<?php
/**
 * Core Plugin class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Core;

use HealthyJointGoals\Interfaces\Registrable;
use HealthyJointGoals\PostTypes\GoalPostType;
use HealthyJointGoals\PostTypes\CheckinPostType;
use HealthyJointGoals\Admin\MenuPage;
use HealthyJointGoals\Admin\MetaBoxes\GoalMetaBox;
use HealthyJointGoals\Admin\MetaBoxes\CheckinMetaBox;
use HealthyJointGoals\Services\GoalService;
use HealthyJointGoals\Services\CheckinService;
use HealthyJointGoals\API\RestEndpoints;
use HealthyJointGoals\Integrations\BeaverBuilderIntegration;

/**
 * Main Plugin class.
 *
 * Implements the Singleton pattern and Dependency Injection Container.
 * Follows Single Responsibility Principle by only handling plugin initialization.
 */
final class Plugin {
	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Services container.
	 *
	 * @var array
	 */
	private $services = array();

	/**
	 * Registered components.
	 *
	 * @var Registrable[]
	 */
	private $components = array();

	/**
	 * Private constructor to prevent multiple instances.
	 */
	private function __construct() {
		// Private constructor for singleton pattern.
	}

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init() {
		$this->register_services();
		$this->register_components();
		$this->register_hooks();
	}

	/**
	 * Register services in the container.
	 * Follows Dependency Inversion Principle.
	 *
	 * @return void
	 */
	private function register_services() {
		$this->services['goal_service']    = new GoalService();
		$this->services['checkin_service'] = new CheckinService();
	}

	/**
	 * Register components.
	 * Each component implements Registrable interface (Interface Segregation).
	 *
	 * @return void
	 */
	private function register_components() {
		// Post Types.
		$this->components[] = new GoalPostType();
		$this->components[] = new CheckinPostType();

		// Admin components.
		$this->components[] = new MenuPage( $this->get_service( 'goal_service' ), $this->get_service( 'checkin_service' ) );
		$this->components[] = new GoalMetaBox( $this->get_service( 'goal_service' ) );
		$this->components[] = new CheckinMetaBox( $this->get_service( 'checkin_service' ) );
		
		// REST API endpoints.
		$this->components[] = new RestEndpoints( $this->get_service( 'goal_service' ), $this->get_service( 'checkin_service' ) );
		
		// Third-party integrations.
		$this->components[] = new BeaverBuilderIntegration();
	}

	/**
	 * Register hooks for all components.
	 *
	 * @return void
	 */
	private function register_hooks() {
		foreach ( $this->components as $component ) {
			if ( $component instanceof Registrable ) {
				$component->register();
			}
		}
	}

	/**
	 * Get service from container.
	 * Simple service locator pattern.
	 *
	 * @param string $service_name Service name.
	 * @return mixed|null
	 */
	public function get_service( $service_name ) {
		return isset( $this->services[ $service_name ] ) ? $this->services[ $service_name ] : null;
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
		// Only register post types directly without hooks during activation.
		$this->register_post_types_for_activation();
		
		// Flush rewrite rules.
		flush_rewrite_rules();

		// Create database tables if needed.
		$this->create_database_tables();
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Register post types during activation.
	 *
	 * @return void
	 */
	private function register_post_types_for_activation() {
		$goal_post_type    = new GoalPostType();
		$checkin_post_type = new CheckinPostType();
		
		// Call post type registration directly instead of register() method.
		$goal_post_type->register_post_type();
		$checkin_post_type->register_post_type();
	}

	/**
	 * Create database tables if needed.
	 *
	 * @return void
	 */
	private function create_database_tables() {
		// Future database table creation logic can be added here.
		// This follows Open/Closed Principle - open for extension.
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 */
	public function __wakeup() {}
}
