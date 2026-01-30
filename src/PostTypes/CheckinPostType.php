<?php
/**
 * Checkin Post Type class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\PostTypes;

use HealthyJointGoals\Interfaces\Registrable;

/**
 * Checkin Post Type class.
 *
 * Handles registration of the Checkin custom post type.
 * Follows Single Responsibility Principle.
 */
class CheckinPostType implements Registrable {
	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'hj_checkin';

	/**
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register the Checkin post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Check-ins', 'Post type general name', 'healthyjoint-goals' ),
			'singular_name'         => _x( 'Check-in', 'Post type singular name', 'healthyjoint-goals' ),
			'menu_name'             => _x( 'Check-ins', 'Admin Menu text', 'healthyjoint-goals' ),
			'name_admin_bar'        => _x( 'Check-in', 'Add New on Toolbar', 'healthyjoint-goals' ),
			'add_new'               => __( 'Add New', 'healthyjoint-goals' ),
			'add_new_item'          => __( 'Add New Check-in', 'healthyjoint-goals' ),
			'new_item'              => __( 'New Check-in', 'healthyjoint-goals' ),
			'edit_item'             => __( 'Edit Check-in', 'healthyjoint-goals' ),
			'view_item'             => __( 'View Check-in', 'healthyjoint-goals' ),
			'all_items'             => __( 'All Check-ins', 'healthyjoint-goals' ),
			'search_items'          => __( 'Search Check-ins', 'healthyjoint-goals' ),
			'parent_item_colon'     => __( 'Parent Check-ins:', 'healthyjoint-goals' ),
			'not_found'             => __( 'No check-ins found.', 'healthyjoint-goals' ),
			'not_found_in_trash'    => __( 'No check-ins found in Trash.', 'healthyjoint-goals' ),
			'featured_image'        => _x( 'Check-in Image', 'Overrides the "Featured Image" phrase', 'healthyjoint-goals' ),
			'set_featured_image'    => _x( 'Set check-in image', 'Overrides the "Set featured image" phrase', 'healthyjoint-goals' ),
			'remove_featured_image' => _x( 'Remove check-in image', 'Overrides the "Remove featured image" phrase', 'healthyjoint-goals' ),
			'use_featured_image'    => _x( 'Use as check-in image', 'Overrides the "Use as featured image" phrase', 'healthyjoint-goals' ),
			'archives'              => _x( 'Check-in archives', 'The post type archive label', 'healthyjoint-goals' ),
			'insert_into_item'      => _x( 'Insert into check-in', 'Overrides the "Insert into post" phrase', 'healthyjoint-goals' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this check-in', 'Overrides the "Uploaded to this post" phrase', 'healthyjoint-goals' ),
			'filter_items_list'     => _x( 'Filter check-ins list', 'Screen reader text for the filter links', 'healthyjoint-goals' ),
			'items_list_navigation' => _x( 'Check-ins list navigation', 'Screen reader text for the pagination', 'healthyjoint-goals' ),
			'items_list'            => _x( 'Check-ins list', 'Screen reader text for the items list', 'healthyjoint-goals' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false, // Will be added to custom menu.
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'checkins' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => true, // Enable parent-child relationship.
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-yes',
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'page-attributes' ),
			'show_in_rest'       => true, // Enable Gutenberg editor and REST API.
			'rest_base'          => 'hj-checkins', // Custom REST API base.
		);

		register_post_type( self::POST_TYPE, $args );
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
	 * Get valid rating types.
	 *
	 * @return array
	 */
	public static function get_rating_types() {
		return array(
			'movement' => __( 'Movement', 'healthyjoint-goals' ),
			'pain'     => __( 'Pain', 'healthyjoint-goals' ),
		);
	}
}
