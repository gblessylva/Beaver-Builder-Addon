<?php
/**
 * Registrable interface.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Interfaces;

/**
 * Interface Registrable
 *
 * Defines the contract for classes that need to register hooks with WordPress.
 * This follows the Interface Segregation Principle by defining a single responsibility.
 */
interface Registrable {
	/**
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register();
}
