<?php
/**
 * Plugin Name: HealthyJoint Goals
 * Description: A goal tracking plugin for health and wellness management
 * Version: 1.0.0
 * Author: AppPresser
 * Text Domain: healthyjoint-goals
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 *
 * @package HealthyJointGoals
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'HEALTHYJOINT_GOALS_VERSION', '1.0.0' );
define( 'HEALTHYJOINT_GOALS_PLUGIN_FILE', __FILE__ );
define( 'HEALTHYJOINT_GOALS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HEALTHYJOINT_GOALS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader.
spl_autoload_register(
	function ( $class_name ) {
		$prefix   = 'HealthyJointGoals\\';
		$base_dir = HEALTHYJOINT_GOALS_PLUGIN_DIR . 'src/';

		// Check if the class uses the namespace prefix.
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class_name, $len ) !== 0 ) {
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class_name, $len );

		// Replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php.
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

// Initialize the plugin.
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'HealthyJointGoals\\Core\\Plugin' ) ) {
			HealthyJointGoals\Core\Plugin::get_instance()->init();
		}
	}
);

// Activation hook.
register_activation_hook(
	__FILE__,
	function () {
		// Simple activation - just register post types and flush rules.
		if ( ! function_exists( 'register_post_type' ) ) {
			return;
		}
		
		// Register Goal post type.
		register_post_type(
			'hj_goal',
			array(
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'rewrite'            => array( 'slug' => 'goals' ),
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
			)
		);
		
		// Register Checkin post type.
		register_post_type(
			'hj_checkin',
			array(
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'rewrite'            => array( 'slug' => 'checkins' ),
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
			)
		);
		
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
);

// Deactivation hook.
register_deactivation_hook(
	__FILE__,
	function () {
		// Flush rewrite rules on deactivation.
		flush_rewrite_rules();
	}
);
