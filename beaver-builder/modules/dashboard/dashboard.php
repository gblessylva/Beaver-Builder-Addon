<?php
/**
 * Dashboard Module for Beaver Builder
 *
 * @package HealthyJoint_Goals
 */

/**
 * Dashboard Module Class
 */
class HJDashboardModule extends FLBuilderModule {

	/**
	 * Constructor method for the class.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Dashboard', 'healthyjoint-goals' ),
				'description'     => __( 'Interactive dashboard for quick goal check-ins', 'healthyjoint-goals' ),
				'category'        => __( 'HealthyJoint Goals', 'healthyjoint-goals' ),
				'dir'             => plugin_dir_path( __FILE__ ),
				'url'             => plugin_dir_url( __FILE__ ),
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
			)
		);

		// Add custom script enqueue hook.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dashboard_scripts' ) );
	}

	/**
	 * Enqueue dashboard-specific scripts and localize data.
	 */
	public function enqueue_dashboard_scripts() {
		// Register the script handle for localization
		// Note: Beaver Builder will auto-enqueue the actual JS file
		wp_register_script(
			'hj-dashboard-script',
			$this->url . 'js/dashboard.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		// Get API token for current user
		$api_token = \HealthyJointGoals\Core\AuthenticationHelper::get_api_token();

		// Localize script with AJAX data
		wp_localize_script(
			'hj-dashboard-script',
			'hjGoalAjax',
			array(
				'restUrl'  => rest_url( 'healthyjoint/v1/' ),
				'apiToken' => $api_token ? $api_token : '',
			)
		);

		// Enqueue the script
		wp_enqueue_script( 'hj-dashboard-script' );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'HJDashboardModule',
	array(
		'general' => array(
			'title'    => __( 'General', 'healthyjoint-goals' ),
			'sections' => array(
				'content' => array(
					'title'  => __( 'Content', 'healthyjoint-goals' ),
					'fields' => array(
						'greeting_text'      => array(
							'type'    => 'text',
							'label'   => __( 'Greeting Text', 'healthyjoint-goals' ),
							'default' => __( 'Hello', 'healthyjoint-goals' ),
						),
						'user_name'          => array(
							'type'    => 'text',
							'label'   => __( 'User Name', 'healthyjoint-goals' ),
							'default' => __( 'User', 'healthyjoint-goals' ),
							'help'    => __( 'Leave blank to use current user\'s display name', 'healthyjoint-goals' ),
						),
						'goal_card_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Goal Card Title', 'healthyjoint-goals' ),
							'default' => __( 'Your goal is to be able to', 'healthyjoint-goals' ),
						),
						'goal_placeholder'   => array(
							'type'    => 'text',
							'label'   => __( 'Goal Placeholder Text', 'healthyjoint-goals' ),
							'default' => __( 'Loading goal...', 'healthyjoint-goals' ),
						),
						'rating_question'    => array(
							'type'    => 'text',
							'label'   => __( 'Rating Question', 'healthyjoint-goals' ),
							'default' => __( 'How would you rate your bodyweight at the moment?', 'healthyjoint-goals' ),
						),
						'rating_description' => array(
							'type'    => 'textarea',
							'label'   => __( 'Rating Description', 'healthyjoint-goals' ),
							'default' => __( 'Use the scale to rate yourself from 1 being an area needing a lot of improvement, to 5 being something you think is going well', 'healthyjoint-goals' ),
							'rows'    => 3,
						),
					),
				),
				'buttons' => array(
					'title'  => __( 'Buttons', 'healthyjoint-goals' ),
					'fields' => array(
						'edit_goal_text'     => array(
							'type'    => 'text',
							'label'   => __( 'Edit Goal Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Edit Goal', 'healthyjoint-goals' ),
						),
						'complete_goal_text' => array(
							'type'    => 'text',
							'label'   => __( 'Complete Goal Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Complete Goal', 'healthyjoint-goals' ),
						),
						'submit_text'        => array(
							'type'    => 'text',
							'label'   => __( 'Submit Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Submit', 'healthyjoint-goals' ),
						),
						'full_checkin_text'  => array(
							'type'    => 'text',
							'label'   => __( 'Full Check-in Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Do a full check-in', 'healthyjoint-goals' ),
						),
						'see_previous_text'  => array(
							'type'    => 'text',
							'label'   => __( 'See Previous Check-ins Text', 'healthyjoint-goals' ),
							'default' => __( 'See all previous check-ins', 'healthyjoint-goals' ),
						),
					),
				),
				'courses' => array(
					'title'  => __( 'Courses Section', 'healthyjoint-goals' ),
					'fields' => array(
						'courses_section_title' => array(
							'type'    => 'text',
							'label'   => __( 'Courses Section Title', 'healthyjoint-goals' ),
							'default' => __( 'Learn a bit about osteoarthritis', 'healthyjoint-goals' ),
						),
						'course_button_text'    => array(
							'type'    => 'text',
							'label'   => __( 'Course Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Learn about', 'healthyjoint-goals' ),
						),
						'default_course_image'  => array(
							'type'  => 'photo',
							'label' => __( 'Default Course Image', 'healthyjoint-goals' ),
							'help'  => __( 'Fallback image for courses without featured images', 'healthyjoint-goals' ),
						),
					),
				),
				'urls'    => array(
					'title'  => __( 'Page URLs', 'healthyjoint-goals' ),
					'fields' => array(
						'edit_goal_url'         => array(
							'type'        => 'link',
							'label'       => __( 'Edit Goal URL', 'healthyjoint-goals' ),
							'help'        => __( 'URL to the edit goal page', 'healthyjoint-goals' ),
							'show_target' => false,
						),
						'complete_goal_url'     => array(
							'type'        => 'link',
							'label'       => __( 'Complete Goal URL', 'healthyjoint-goals' ),
							'help'        => __( 'URL to the complete goal page', 'healthyjoint-goals' ),
							'show_target' => false,
						),
						'full_checkin_url'      => array(
							'type'        => 'link',
							'label'       => __( 'Full Check-in URL', 'healthyjoint-goals' ),
							'help'        => __( 'URL to the full check-in page', 'healthyjoint-goals' ),
							'show_target' => false,
						),
						'previous_checkins_url' => array(
							'type'        => 'link',
							'label'       => __( 'Previous Check-ins URL', 'healthyjoint-goals' ),
							'help'        => __( 'URL to view all previous check-ins', 'healthyjoint-goals' ),
							'show_target' => false,
						),
						'success_redirect_url'  => array(
							'type'        => 'link',
							'label'       => __( 'Success Redirect URL', 'healthyjoint-goals' ),
							'help'        => __( 'URL to redirect after successful check-in submission', 'healthyjoint-goals' ),
							'show_target' => false,
						),
					),
				),
			),
		),
		'style'   => array(
			'title'    => __( 'Style', 'healthyjoint-goals' ),
			'sections' => array(
				'layout'     => array(
					'title'  => __( 'Layout', 'healthyjoint-goals' ),
					'fields' => array(
						'container_padding' => array(
							'type'    => 'dimension',
							'label'   => __( 'Container Padding', 'healthyjoint-goals' ),
							'slider'  => true,
							'units'   => array( 'px', 'em', 'rem', '%' ),
							'default' => array(
								'top'    => '40',
								'right'  => '40',
								'bottom' => '40',
								'left'   => '40',
							),
						),
						'section_spacing'   => array(
							'type'    => 'unit',
							'label'   => __( 'Section Spacing', 'healthyjoint-goals' ),
							'default' => '40',
							'units'   => array( 'px' ),
							'slider'  => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 5,
							),
						),
					),
				),
				'colors'     => array(
					'title'  => __( 'Colors', 'healthyjoint-goals' ),
					'fields' => array(
						'background_color'   => array(
							'type'       => 'color',
							'label'      => __( 'Background Color', 'healthyjoint-goals' ),
							'default'    => 'f8f7f5',
							'show_reset' => true,
							'show_alpha' => true,
						),
						'goal_card_bg_color' => array(
							'type'       => 'color',
							'label'      => __( 'Goal Card Background', 'healthyjoint-goals' ),
							'default'    => 'c8e6f5',
							'show_reset' => true,
							'show_alpha' => true,
						),
						'circle_color'       => array(
							'type'       => 'color',
							'label'      => __( 'Circle Color', 'healthyjoint-goals' ),
							'default'    => '4a5568',
							'show_reset' => true,
							'show_alpha' => true,
						),
					),
				),
				'typography' => array(
					'title'  => __( 'Typography', 'healthyjoint-goals' ),
					'fields' => array(
						'greeting_typography'  => array(
							'type'    => 'typography',
							'label'   => __( 'Greeting Typography', 'healthyjoint-goals' ),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-dashboard-greeting',
							),
						),
						'goal_card_typography' => array(
							'type'    => 'typography',
							'label'   => __( 'Goal Card Typography', 'healthyjoint-goals' ),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-goal-card',
							),
						),
					),
				),
			),
		),
	)
);
