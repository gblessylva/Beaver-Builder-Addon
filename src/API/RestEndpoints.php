<?php
/**
 * REST API Endpoints class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\API;

use HealthyJointGoals\Interfaces\Registrable;
use HealthyJointGoals\PostTypes\GoalPostType;
use HealthyJointGoals\PostTypes\CheckinPostType;
use HealthyJointGoals\Services\GoalService;
use HealthyJointGoals\Services\CheckinService;

/**
 * REST API Endpoints class.
 *
 * Handles custom REST API endpoints for the plugin.
 * Follows Single Responsibility Principle.
 */
class RestEndpoints implements Registrable {
	/**
	 * Goal service instance.
	 *
	 * @var GoalService
	 */
	private $goal_service;

	/**
	 * Checkin service instance.
	 *
	 * @var CheckinService
	 */
	private $checkin_service;

	/**
	 * Constructor.
	 *
	 * @param GoalService    $goal_service    Goal service dependency.
	 * @param CheckinService $checkin_service Checkin service dependency.
	 */
	public function __construct( GoalService $goal_service, CheckinService $checkin_service ) {
		$this->goal_service    = $goal_service;
		$this->checkin_service = $checkin_service;
	}

	/**
	 * Authenticate request using custom API key header.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return int|false User ID if authenticated, false otherwise.
	 */
	private function authenticate_request( \WP_REST_Request $request ) {
		$token = $request->get_header( 'x-hj-api-key' );

		if ( ! $token ) {
			return [
				false,
				'message' => 'Missing API token in X-HJ-API-KEY header',
			];
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

	/**
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
		add_filter( 'rest_pre_serve_request', array( $this, 'add_cors_headers' ), 15, 4 );
	}

	/**
	 * Add CORS headers to allow custom X-HJ-API-KEY header.
	 *
	 * @param bool             $served  Whether the request has already been served.
	 * @param \WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param \WP_REST_Request  $request Request used to generate the response.
	 * @param \WP_REST_Server   $server  Server instance.
	 * @return bool
	 */
	public function add_cors_headers( $served, $result, $request, $server ) {
		// Only add headers for our endpoints
		$route = $request->get_route();
		if ( strpos( $route, '/healthyjoint/v1' ) === false ) {
			return $served;
		}

		// Add X-HJ-API-KEY to allowed headers
		header( 'Access-Control-Allow-Headers: Authorization, X-WP-Nonce, Content-Disposition, Content-MD5, Content-Type, X-HJ-API-KEY', false );
		
		return $served;
	}

	/**
	 * Register custom REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		// Create goal endpoint.
		register_rest_route(
			'healthyjoint/v1',
			'/goals',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_goal' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'title'        => array(
						'required' => true,
						'type'     => 'string',
					),
					'content'      => array(
						'required' => false,
						'type'     => 'string',
					),
					'purpose'      => array(
						'required' => false,
						'type'     => 'string',
					),
					'focus_areas'  => array(
						'required' => false,
						'type'     => 'array',
					),
					'goal_types'   => array(
						'required' => false,
						'type'     => 'object',
					),
					'status'       => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);

		// Get goals endpoint.
		register_rest_route(
			'healthyjoint/v1',
			'/goals',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_goals' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
			)
		);

		// Get single goal endpoint.
		register_rest_route(
			'healthyjoint/v1',
			'/goals/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_goal' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		// Update goal endpoint.
		register_rest_route(
			'healthyjoint/v1',
			'/goals/(?P<id>\d+)',
			array(
				'methods'             => array( 'PUT', 'POST' ),
				'callback'            => array( $this, 'update_goal' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'id'               => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'status'           => array(
						'required' => false,
						'type'     => 'string',
						'enum'     => array( 'inprogress', 'completed' ),
					),
					'goal_description' => array(
						'required' => false,
						'type'     => 'string',
					),
					'focus_areas'      => array(
						'required' => false,
						'type'     => 'array',
					),
					'goal_type'        => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);

		// Create checkin endpoint.
		register_rest_route(
			'healthyjoint/v1',
			'/checkins',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_checkin' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'goal_id'      => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
					),
					'checkin_date' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $param );
						},
					),
					'notes'        => array(
						'required' => false,
						'type'     => 'string',
					),
					'ratings'      => array(
						'required' => false,
						'type'     => 'object',
					),
				),
			)
		);

		// Goals endpoints.
		register_rest_route(
			'healthyjoint/v1',
			'/goals/(?P<id>\d+)/checkins',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_goal_checkins' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		// Create checkin for goal.
		register_rest_route(
			'healthyjoint/v1',
			'/goals/(?P<id>\d+)/checkins',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_goal_checkin' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'id'           => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'checkin_date' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $param );
						},
					),
					'ratings'      => array(
						'required' => false,
						'type'     => 'object',
					),
					'content'      => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);

		// Goal statistics endpoint.
		register_rest_route(
			'healthyjoint/v1',
			'/goals/(?P<id>\d+)/statistics',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_goal_statistics' ),
				'permission_callback' => function ( \WP_REST_Request $request ) {
					return (bool) $this->authenticate_request( $request );
				},
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);
	}

	/**
	 * Register custom REST API fields for existing post types.
	 *
	 * @return void
	 */
	public function register_rest_fields() {
		// Add custom fields to goals.
		register_rest_field(
			GoalPostType::get_post_type(),
			'hj_purpose',
			array(
				'get_callback'    => array( $this, 'get_goal_purpose' ),
				'update_callback' => array( $this, 'update_goal_purpose' ),
				'schema'          => array(
					'description' => 'Goal purpose (improve or maintain)',
					'type'        => 'string',
					'enum'        => array_keys( GoalPostType::get_purposes() ),
				),
			)
		);

		register_rest_field(
			GoalPostType::get_post_type(),
			'hj_focus_areas',
			array(
				'get_callback'    => array( $this, 'get_goal_focus_areas' ),
				'update_callback' => array( $this, 'update_goal_focus_areas' ),
				'schema'          => array(
					'description' => 'Goal focus areas (max 2)',
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
						'enum' => array_keys( GoalPostType::get_focus_areas() ),
					),
					'maxItems'    => 2,
				),
			)
		);

		register_rest_field(
			GoalPostType::get_post_type(),
			'hj_goal_status',
			array(
				'get_callback'    => array( $this, 'get_goal_status' ),
				'update_callback' => array( $this, 'update_goal_status' ),
				'schema'          => array(
					'description' => 'Goal status (inprogress or completed)',
					'type'        => 'string',
					'enum'        => array( 'inprogress', 'completed' ),
				),
			)
		);

		// Add custom fields to checkins.
		register_rest_field(
			CheckinPostType::get_post_type(),
			'hj_checkin_date',
			array(
				'get_callback'    => array( $this, 'get_checkin_date' ),
				'update_callback' => array( $this, 'update_checkin_date' ),
				'schema'          => array(
					'description' => 'Check-in date (Y-m-d format)',
					'type'        => 'string',
					'format'      => 'date',
				),
			)
		);

		register_rest_field(
			CheckinPostType::get_post_type(),
			'hj_ratings',
			array(
				'get_callback'    => array( $this, 'get_checkin_ratings' ),
				'update_callback' => array( $this, 'update_checkin_ratings' ),
				'schema'          => array(
					'description' => 'Check-in ratings (movement, pain)',
					'type'        => 'object',
					'properties'  => array(
						'movement' => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => 5,
						),
						'pain'     => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => 5,
						),
					),
				),
			)
		);
	}

	/**
	 * Get user's goals.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_goals( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$args = array(
			'post_type'      => GoalPostType::get_post_type(),
			'post_status'    => 'publish',
			'author'         => $user_id,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'hj_goal_status',
					'value'   => 'completed',
					'compare' => '!=',
				),
				array(
					'key'     => 'hj_goal_status',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		$goals = get_posts( $args );
		$data  = array();

		foreach ( $goals as $goal ) {
			$goal_status = get_post_meta( $goal->ID, 'hj_goal_status', true );
			$data[]      = array(
				'id'      => $goal->ID,
				'title'   => array( 'rendered' => $goal->post_title ),
				'content' => array( 'rendered' => $goal->post_content ),
				'date'    => $goal->post_date,
				'status'  => $goal_status ? $goal_status : 'inprogress',
			);
		}

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Get a single goal by ID.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_goal( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$goal_id = intval( $request['id'] );
		
		// Get the goal post.
		$goal = get_post( $goal_id );
		
		if ( ! $goal || GoalPostType::get_post_type() !== $goal->post_type ) {
			return new \WP_Error( 'goal_not_found', __( 'Goal not found.', 'healthyjoint-goals' ), array( 'status' => 404 ) );
		}
		
		// Check if current user owns this goal.
		if ( $user_id !== intval( $goal->post_author ) ) {
			return new \WP_Error( 'insufficient_permission', __( 'You do not have permission to access this goal.', 'healthyjoint-goals' ), array( 'status' => 403 ) );
		}
		
		// Get meta fields.
		$goal_meta = get_post_meta( $goal_id );
		$goal_description = get_post_meta( $goal_id, 'hj_goal_description', true );
		$focus_areas = get_post_meta( $goal_id, 'hj_focus_areas', true );
		$goal_type = get_post_meta( $goal_id, 'hj_goal_type', true );
		$goal_status = get_post_meta( $goal_id, 'hj_goal_status', true );
		
		// Prepare response data.
		$data = array(
			'id'               => $goal->ID,
			'title'            => array( 'rendered' => $goal->post_title ),
			'content'          => array( 'rendered' => $goal->post_content ),
			'date'             => $goal->post_date,
			'status'           => $goal_status ? $goal_status : 'inprogress',
			'goal_description' => $goal_description ? $goal_description : '',
			'focus_areas'      => $focus_areas ? (array) $focus_areas : array(),
			'goal_type'        => $goal_type ? $goal_type : '',
		);
		
		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Create a new checkin.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_checkin( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$goal_id = intval( $request['goal_id'] );
		
		// Verify goal exists and user has permission.
		$goal = get_post( $goal_id );
		if ( ! $goal || GoalPostType::get_post_type() !== $goal->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Goal not found' ), 404 );
		}

		if ( intval( $goal->post_author ) !== $user_id ) {
			return new \WP_REST_Response( array( 'message' => 'Permission denied' ), 403 );
		}

		// Create checkin post.
		$checkin_data = array(
			'post_type'    => CheckinPostType::get_post_type(),
			'post_title'   => sprintf( 'Check-in for %s - %s', $goal->post_title, $request['checkin_date'] ),
			'post_content' => sanitize_textarea_field( $request['notes'] ?? '' ),
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_parent'  => $goal_id,
		);

		$checkin_id = wp_insert_post( $checkin_data );
		
		if ( is_wp_error( $checkin_id ) ) {
			return new \WP_REST_Response( 
				array( 
					'success' => false, 
					'message' => 'Failed to create checkin',
				), 
				500 
			);
		}

		// Save meta data.
		update_post_meta( $checkin_id, 'hj_checkin_date', sanitize_text_field( $request['checkin_date'] ) );
		
		if ( ! empty( $request['ratings'] ) && is_array( $request['ratings'] ) ) {
			$ratings = array();
			
			foreach ( $request['ratings'] as $type => $rating ) {
				$type   = sanitize_text_field( $type );
				$rating = intval( $rating );
				
				if ( $rating >= 1 && $rating <= 5 ) {
					$ratings[ $type ] = $rating;
				}
			}
			
			update_post_meta( $checkin_id, 'hj_ratings', $ratings );
		}

		return new \WP_REST_Response( 
			array( 
				'success' => true, 
				'id'      => $checkin_id, 
				'message' => 'Check-in created successfully',
			), 
			201 
		);
	}

	/**
	 * Create a new goal.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_goal( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$title = sanitize_text_field( $request['title'] );
		$content = sanitize_textarea_field( $request['content'] );
		$purpose = sanitize_text_field( $request['purpose'] );
		$focus_areas = $request['focus_areas'];
		$goal_types = $request['goal_types'];
		$status = sanitize_text_field( $request['status'] );

		// Create the post
		$post_data = array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'hj_goal',
			'post_author'  => $user_id,
		);

		$goal_id = wp_insert_post( $post_data );

		if ( is_wp_error( $goal_id ) ) {
			return new \WP_REST_Response( 
				array( 
					'success' => false, 
					'message' => 'Failed to create goal' 
				), 
				500 
			);
		}

		// Add meta fields
		if ( $purpose ) {
			update_post_meta( $goal_id, 'hj_purpose', $purpose );
		}

		// Save goal status as meta field instead of post status
		if ( $status && in_array( $status, array( 'inprogress', 'completed' ), true ) ) {
			update_post_meta( $goal_id, 'hj_goal_status', $status );
		}

		if ( is_array( $focus_areas ) ) {
			$valid_areas = array_keys( GoalPostType::get_focus_areas() );
			$filtered_areas = array_intersect( $focus_areas, $valid_areas );
			update_post_meta( $goal_id, 'hj_focus_areas', $filtered_areas );
		}

		// Store goal types for each area if provided
		if ( is_array( $goal_types ) ) {
			update_post_meta( $goal_id, 'hj_goal_types', $goal_types );
		}

		return new \WP_REST_Response( 
			array( 
				'success' => true, 
				'id' => $goal_id,
				'message' => 'Goal created successfully' 
			), 
			201 
		);
	}

	/**
	 * Get checkins for a specific goal.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_goal_checkins( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$goal_id = intval( $request['id'] );
		
		// Verify goal exists and user has permission.
		$goal = get_post( $goal_id );
		if ( ! $goal || GoalPostType::get_post_type() !== $goal->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Goal not found' ), 404 );
		}
		
		if ( intval( $goal->post_author ) !== $user_id ) {
			return new \WP_REST_Response( array( 'message' => 'Permission deniedss' ), 403 );
		}

		$checkins = get_posts(
			array(
				'post_type'      => CheckinPostType::get_post_type(),
				'post_parent'    => $goal_id,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$data = array();
		foreach ( $checkins as $checkin ) {
			$data[] = array(
				'id'           => $checkin->ID,
				'title'        => $checkin->post_title,
				'content'      => $checkin->post_content,
				'date'         => $checkin->post_date,
				'checkin_date' => get_post_meta( $checkin->ID, 'hj_checkin_date', true ),
				'ratings'      => get_post_meta( $checkin->ID, 'hj_ratings', true ),
			);
		}

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Create a new checkin for a goal.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_goal_checkin( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$goal_id = intval( $request['id'] );
		
		// Verify goal exists and user has permission.
		$goal = get_post( $goal_id );
		if ( ! $goal || GoalPostType::get_post_type() !== $goal->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Goal not found' ), 404 );
		}
		
		if ( intval( $goal->post_author ) !== $user_id ) {
			return new \WP_REST_Response( array( 'message' => 'Permission denied' ), 403 );
		}

		// Create checkin post.
		$checkin_data = array(
			'post_type'    => CheckinPostType::get_post_type(),
			'post_title'   => sprintf( 'Check-in for %s - %s', $goal->post_title, $request['checkin_date'] ),
			'post_content' => sanitize_textarea_field( $request['content'] ?? '' ),
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_parent'  => $goal_id,
		);

		$checkin_id = wp_insert_post( $checkin_data );
		
		if ( is_wp_error( $checkin_id ) ) {
			return new \WP_REST_Response( array( 'message' => 'Failed to create checkin' ), 500 );
		}

		// Save meta data.
		update_post_meta( $checkin_id, 'hj_checkin_date', sanitize_text_field( $request['checkin_date'] ) );
		
		if ( ! empty( $request['ratings'] ) && is_array( $request['ratings'] ) ) {
			$valid_rating_types = array_keys( CheckinPostType::get_rating_types() );
			$ratings = array();
			
			foreach ( $request['ratings'] as $type => $rating ) {
				$type   = sanitize_text_field( $type );
				$rating = intval( $rating );
				
				if ( in_array( $type, $valid_rating_types, true ) && $rating >= 1 && $rating <= 5 ) {
					$ratings[ $type ] = $rating;
				}
			}
			
			update_post_meta( $checkin_id, 'hj_ratings', $ratings );
		}

		return new \WP_REST_Response( array( 'id' => $checkin_id, 'message' => 'Checkin created successfully' ), 201 );
	}

	/**
	 * Get goal statistics.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_goal_statistics( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$goal_id = intval( $request['id'] );
		
		// Verify goal exists.
		$goal = get_post( $goal_id );
		if ( ! $goal || GoalPostType::get_post_type() !== $goal->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Goal not found' ), 404 );
		}
		
		if ( intval( $goal->post_author ) !== $user_id ) {
			return new \WP_REST_Response( array( 'message' => 'Permission denied' ), 403 );
		}

		$stats = $this->checkin_service->get_goal_statistics( $goal_id );
		
		return new \WP_REST_Response( $stats, 200 );
	}

	/**
	 * Check read permission.
	 *
	 * @return bool
	 */
	public function check_read_permission() {
		return current_user_can( 'read' );
	}

	/**
	 * Check create permission.
	 *
	 * @return bool
	 */
	public function check_create_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Get goal purpose callback.
	 *
	 * @param array $object Post object array.
	 * @return string
	 */
	public function get_goal_purpose( $object ) {
		return get_post_meta( $object['id'], 'hj_purpose', true );
	}

	/**
	 * Update goal purpose callback.
	 *
	 * @param string $value New value.
	 * @param object $object Post object.
	 * @return bool
	 */
	public function update_goal_purpose( $value, $object ) {
		$valid_purposes = array_keys( GoalPostType::get_purposes() );
		if ( in_array( $value, $valid_purposes, true ) ) {
			return update_post_meta( $object->ID, 'hj_purpose', $value );
		}
		return false;
	}

	/**
	 * Get goal focus areas callback.
	 *
	 * @param array $object Post object array.
	 * @return array
	 */
	public function get_goal_focus_areas( $object ) {
		$areas = get_post_meta( $object['id'], 'hj_focus_areas', true );
		return is_array( $areas ) ? $areas : array();
	}

	/**
	 * Update goal focus areas callback.
	 *
	 * @param array  $value New value.
	 * @param object $object Post object.
	 * @return bool
	 */
	public function update_goal_focus_areas( $value, $object ) {
		if ( ! is_array( $value ) || count( $value ) > 2 ) {
			return false;
		}

		$valid_areas = array_keys( GoalPostType::get_focus_areas() );
		foreach ( $value as $area ) {
			if ( ! in_array( $area, $valid_areas, true ) ) {
				return false;
			}
		}

		return update_post_meta( $object->ID, 'hj_focus_areas', $value );
	}

	/**
	 * Get goal status callback.
	 *
	 * @param array $object Post object array.
	 * @return string
	 */
	public function get_goal_status( $object ) {
		$status = get_post_meta( $object['id'], 'hj_goal_status', true );
		return $status ? $status : 'inprogress'; // Default to inprogress
	}

	/**
	 * Update goal status callback.
	 *
	 * @param string $value New value.
	 * @param object $object Post object.
	 * @return bool
	 */
	public function update_goal_status( $value, $object ) {
		if ( ! in_array( $value, array( 'inprogress', 'completed' ), true ) ) {
			return false;
		}

		return update_post_meta( $object->ID, 'hj_goal_status', $value );
	}

	/**
	 * Get checkin date callback.
	 *
	 * @param array $object Post object array.
	 * @return string
	 */
	public function get_checkin_date( $object ) {
		return get_post_meta( $object['id'], 'hj_checkin_date', true );
	}

	/**
	 * Update checkin date callback.
	 *
	 * @param string $value New value.
	 * @param object $object Post object.
	 * @return bool
	 */
	public function update_checkin_date( $value, $object ) {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return update_post_meta( $object->ID, 'hj_checkin_date', $value );
		}
		return false;
	}

	/**
	 * Get checkin ratings callback.
	 *
	 * @param array $object Post object array.
	 * @return array
	 */
	public function get_checkin_ratings( $object ) {
		$ratings = get_post_meta( $object['id'], 'hj_ratings', true );
		return is_array( $ratings ) ? $ratings : array();
	}

	/**
	 * Update checkin ratings callback.
	 *
	 * @param array  $value New value.
	 * @param object $object Post object.
	 * @return bool
	 */
	public function update_checkin_ratings( $value, $object ) {
		if ( ! is_array( $value ) ) {
			return false;
		}

		$valid_types = array_keys( CheckinPostType::get_rating_types() );
		$ratings = array();

		foreach ( $value as $type => $rating ) {
			if ( in_array( $type, $valid_types, true ) && is_int( $rating ) && $rating >= 1 && $rating <= 5 ) {
				$ratings[ $type ] = $rating;
			}
		}

		return update_post_meta( $object->ID, 'hj_ratings', $ratings );
	}

	/**
	 * Update a goal.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_goal( $request ) {
		$user_id = $this->authenticate_request( $request );
		
		if ( ! $user_id ) {
			return new \WP_Error(
				'unauthorized',
				'Invalid or missing API token',
				array( 'status' => 401 )
			);
		}
		
		$goal_id = intval( $request['id'] );

		// Verify goal exists and user has permission.
		$goal = get_post( $goal_id );
		if ( ! $goal || GoalPostType::get_post_type() !== $goal->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Goal not found' ), 404 );
		}

		if ( $user_id !== (int) $goal->post_author ) {
			return new \WP_REST_Response( array( 'message' => 'Permission denied' ), 403 );
		}

		// Update goal status if provided.
		if ( isset( $request['status'] ) ) {
			$status = sanitize_text_field( $request['status'] );
			if ( in_array( $status, array( 'inprogress', 'completed' ), true ) ) {
				update_post_meta( $goal_id, 'hj_goal_status', $status );
				
				// If marking as completed, use the service method to handle it properly.
				if ( 'completed' === $status ) {
					$this->goal_service->complete_goal( $goal_id );
				}
			}
		}

		// Update goal description if provided.
		if ( isset( $request['goal_description'] ) ) {
			$goal_description = sanitize_textarea_field( $request['goal_description'] );
			update_post_meta( $goal_id, 'hj_goal_description', $goal_description );
		}

		// Update focus areas if provided.
		if ( isset( $request['focus_areas'] ) && is_array( $request['focus_areas'] ) ) {
			$focus_areas = array_map( 'sanitize_text_field', $request['focus_areas'] );
			update_post_meta( $goal_id, 'hj_focus_areas', $focus_areas );
		}

		// Update goal type if provided.
		if ( isset( $request['goal_type'] ) ) {
			$goal_type = sanitize_text_field( $request['goal_type'] );
			update_post_meta( $goal_id, 'hj_goal_type', $goal_type );
		}

		// Return updated goal data.
		$goal_status      = get_post_meta( $goal_id, 'hj_goal_status', true );
		$goal_description = get_post_meta( $goal_id, 'hj_goal_description', true );
		$focus_areas      = get_post_meta( $goal_id, 'hj_focus_areas', true );
		$goal_type        = get_post_meta( $goal_id, 'hj_goal_type', true );

		$response_data = array(
			'id'               => $goal->ID,
			'title'            => array( 'rendered' => $goal->post_title ),
			'content'          => array( 'rendered' => $goal->post_content ),
			'date'             => $goal->post_date,
			'status'           => $goal_status ? $goal_status : 'inprogress',
			'goal_description' => $goal_description ? $goal_description : '',
			'focus_areas'      => $focus_areas ? (array) $focus_areas : array(),
			'goal_type'        => $goal_type ? $goal_type : '',
		);

		return new \WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Check update permission callback.
	 *
	 * @return bool
	 */
	public function check_update_permission() {
		return current_user_can( 'edit_posts' );
	}
}
