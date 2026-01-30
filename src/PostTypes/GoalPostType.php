<?php
/**
 * Goal Post Type class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\PostTypes;

use HealthyJointGoals\Interfaces\Registrable;

/**
 * Goal Post Type class.
 *
 * Handles registration of the Goal custom post type.
 * Follows Single Responsibility Principle.
 */
class GoalPostType implements Registrable {
	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'hj_goal';

	/**
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_post_statuses' ) );
	}

	/**
	 * Register the Goal post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Goals', 'Post type general name', 'healthyjoint-goals' ),
			'singular_name'         => _x( 'Goal', 'Post type singular name', 'healthyjoint-goals' ),
			'menu_name'             => _x( 'Goals', 'Admin Menu text', 'healthyjoint-goals' ),
			'name_admin_bar'        => _x( 'Goal', 'Add New on Toolbar', 'healthyjoint-goals' ),
			'add_new'               => __( 'Add New', 'healthyjoint-goals' ),
			'add_new_item'          => __( 'Add New Goal', 'healthyjoint-goals' ),
			'new_item'              => __( 'New Goal', 'healthyjoint-goals' ),
			'edit_item'             => __( 'Edit Goal', 'healthyjoint-goals' ),
			'view_item'             => __( 'View Goal', 'healthyjoint-goals' ),
			'all_items'             => __( 'All Goals', 'healthyjoint-goals' ),
			'search_items'          => __( 'Search Goals', 'healthyjoint-goals' ),
			'parent_item_colon'     => __( 'Parent Goals:', 'healthyjoint-goals' ),
			'not_found'             => __( 'No goals found.', 'healthyjoint-goals' ),
			'not_found_in_trash'    => __( 'No goals found in Trash.', 'healthyjoint-goals' ),
			'featured_image'        => _x( 'Goal Cover Image', 'Overrides the "Featured Image" phrase', 'healthyjoint-goals' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'healthyjoint-goals' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'healthyjoint-goals' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'healthyjoint-goals' ),
			'archives'              => _x( 'Goal archives', 'The post type archive label', 'healthyjoint-goals' ),
			'insert_into_item'      => _x( 'Insert into goal', 'Overrides the "Insert into post" phrase', 'healthyjoint-goals' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this goal', 'Overrides the "Uploaded to this post" phrase', 'healthyjoint-goals' ),
			'filter_items_list'     => _x( 'Filter goals list', 'Screen reader text for the filter links', 'healthyjoint-goals' ),
			'items_list_navigation' => _x( 'Goals list navigation', 'Screen reader text for the pagination', 'healthyjoint-goals' ),
			'items_list'            => _x( 'Goals list', 'Screen reader text for the items list', 'healthyjoint-goals' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false, // Will be added to custom menu.
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'goals' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-flag',
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
			'show_in_rest'       => true, // Enable Gutenberg editor and REST API.
			'rest_base'          => 'hj-goals', // Custom REST API base.
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register custom post statuses for goals.
	 *
	 * @return void
	 */
	public function register_post_statuses() {
		register_post_status(
			'inprogress',
			array(
				'label'                     => _x( 'In Progress', 'Goal status', 'healthyjoint-goals' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'post_type'                 => array( self::POST_TYPE ),
				/* translators: %s: number of goals */
				'label_count'               => _n_noop(
					'In Progress <span class="count">(%s)</span>',
					'In Progress <span class="count">(%s)</span>',
					'healthyjoint-goals'
				),
			)
		);

		register_post_status(
			'completed',
			array(
				'label'                     => _x( 'Completed', 'Goal status', 'healthyjoint-goals' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'post_type'                 => array( self::POST_TYPE ),
				/* translators: %s: number of goals */
				'label_count'               => _n_noop(
					'Completed <span class="count">(%s)</span>',
					'Completed <span class="count">(%s)</span>',
					'healthyjoint-goals'
				),
			)
		);
	}

	/**
	 * Get the post type slug.
	 *
	 * @return string
	 */
	public static function get_post_type() {
		return self::POST_TYPE;
	}

	/**
	 * Get valid focus areas.
	 *
	 * @return array
	 */
	public static function get_focus_areas() {
		return array(
			'movement'      => __( 'Movement', 'healthyjoint-goals' ),
			'pain'          => __( 'Pain', 'healthyjoint-goals' ),
			'eating_habits' => __( 'Eating Habits', 'healthyjoint-goals' ),
			'mood'          => __( 'Mood', 'healthyjoint-goals' ),
			'body_weight'   => __( 'Body Weight', 'healthyjoint-goals' ),
		);
	}

	/**
	 * Get valid purposes.
	 *
	 * @return array
	 */
	public static function get_purposes() {
		return array(
			'improve'  => __( 'Improve', 'healthyjoint-goals' ),
			'maintain' => __( 'Maintain', 'healthyjoint-goals' ),
		);
	}
}
