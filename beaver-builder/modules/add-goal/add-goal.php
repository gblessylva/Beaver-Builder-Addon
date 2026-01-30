<?php
/**
 * Add Goal Module for Beaver Builder
 *
 * @package HealthyJointGoals
 */

/**
 * Add Goal Module Class
 */
class HJAddGoalModule extends FLBuilderModule {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Add Goal Form', 'healthyjoint-goals' ),
				'description'     => __( 'A form to add new health and wellness goals', 'healthyjoint-goals' ),
				'category'        => __( 'HealthyJoint', 'healthyjoint-goals' ),
				'group'           => __( 'HealthyJoint Modules', 'healthyjoint-goals' ),
				'dir'             => HEALTHYJOINT_GOALS_PLUGIN_DIR . 'beaver-builder/modules/add-goal/',
				'url'             => HEALTHYJOINT_GOALS_PLUGIN_URL . 'beaver-builder/modules/add-goal/',
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
				'icon'            => 'plus-circle.svg',
			)
		);

		// Enqueue frontend scripts and styles
		$this->add_css( 'hj-add-goal-style', $this->url . 'css/frontend.css' );
		$this->add_js( 'hj-add-goal-script', $this->url . 'js/add-goal.js', array( 'jquery' ), '', true );

		// Localize script for AJAX.
		$api_token = \HealthyJointGoals\Core\AuthenticationHelper::get_api_token();
		
		wp_localize_script(
			'hj-add-goal-script',
			'hjAddGoal',
			array(
				'ajaxUrl'     => rest_url( 'wp/v2/' ),
				'restUrl'     => rest_url( 'healthyjoint/v1/' ),
				'apiToken'    => $api_token ? $api_token : '',
				'redirectUrl' => isset( $this->settings->redirect_url ) ? esc_url( $this->settings->redirect_url ) : '/dashboard',
			)
		);
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		if ( $this->settings && is_object( $this->settings ) ) {
			wp_enqueue_script( 'hj-add-goal-script' );
		}
	}
}

// Register the module
FLBuilder::register_module(
	'HJAddGoalModule',
	array(
		'general' => array(
			'title'    => __( 'General', 'healthyjoint-goals' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General Settings', 'healthyjoint-goals' ),
					'fields' => array(
						'title'        => array(
							'type'    => 'text',
							'label'   => __( 'Form Title', 'healthyjoint-goals' ),
							'default' => __( 'Change your goal', 'healthyjoint-goals' ),
						),
						'subtitle'     => array(
							'type'    => 'textarea',
							'label'   => __( 'Form Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'Set a new goal by following the same process as when you started.', 'healthyjoint-goals' ),
							'rows'    => 3,
						),
						'redirect_url' => array(
							'type'        => 'link',
							'label'       => __( 'Success Redirect URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/dashboard', 'healthyjoint-goals' ),
						),
					),
				),
				'form_sections' => array(
					'title'  => __( 'Form Section Text', 'healthyjoint-goals' ),
					'fields' => array(
						'section1_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Priority Areas Title', 'healthyjoint-goals' ),
							'default' => __( 'Do you want to change your priority areas?', 'healthyjoint-goals' ),
						),
						'section1_subtitle' => array(
							'type'    => 'text',
							'label'   => __( 'Priority Areas Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'You can select up to 2 priority areas.', 'healthyjoint-goals' ),
						),
						'section2_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Goal Type Title', 'healthyjoint-goals' ),
							'default' => __( 'I want to...', 'healthyjoint-goals' ),
						),
						'section2_subtitle' => array(
							'type'    => 'text',
							'label'   => __( 'Goal Type Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'Choose if you want to maintain or improve', 'healthyjoint-goals' ),
						),
						'section3_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Goal Description Title', 'healthyjoint-goals' ),
							'default' => __( 'so that I can...', 'healthyjoint-goals' ),
						),
						'section3_subtitle' => array(
							'type'    => 'text',
							'label'   => __( 'Goal Description Subtitle', 'healthyjoint-goals' ),
							'default' => __( '(write or update your goal below)', 'healthyjoint-goals' ),
						),
					),
				),
				'sidebar_content' => array(
					'title'  => __( 'Sidebar Information', 'healthyjoint-goals' ),
					'fields' => array(
						'info_title1'    => array(
							'type'    => 'text',
							'label'   => __( 'Information Section Title', 'healthyjoint-goals' ),
							'default' => __( 'What is this for?', 'healthyjoint-goals' ),
						),
						'info_list_item1' => array(
							'type'    => 'text',
							'label'   => __( 'Info List Item 1', 'healthyjoint-goals' ),
							'default' => __( 'A goal gives you motivation, and a way of tracking your progress', 'healthyjoint-goals' ),
						),
						'info_list_item2' => array(
							'type'    => 'text',
							'label'   => __( 'Info List Item 2', 'healthyjoint-goals' ),
							'default' => __( 'What is the deadline for your goal? Think about what change you can make in that time', 'healthyjoint-goals' ),
						),
						'info_list_item3' => array(
							'type'    => 'text',
							'label'   => __( 'Info List Item 3', 'healthyjoint-goals' ),
							'default' => __( 'You can change your goal later as you keep learning more', 'healthyjoint-goals' ),
						),
						'examples_title' => array(
							'type'    => 'text',
							'label'   => __( 'Examples Section Title', 'healthyjoint-goals' ),
							'default' => __( 'Here are some example goals', 'healthyjoint-goals' ),
						),
						'example1'       => array(
							'type'    => 'text',
							'label'   => __( 'Example Goal 1', 'healthyjoint-goals' ),
							'default' => __( 'play with my grandkids', 'healthyjoint-goals' ),
						),
						'example2'       => array(
							'type'    => 'text',
							'label'   => __( 'Example Goal 2', 'healthyjoint-goals' ),
							'default' => __( 'walk to the store and back with my shopping', 'healthyjoint-goals' ),
						),
						'example3'       => array(
							'type'    => 'text',
							'label'   => __( 'Example Goal 3', 'healthyjoint-goals' ),
							'default' => __( 'put my shoes on easier', 'healthyjoint-goals' ),
						),
						'example4'       => array(
							'type'    => 'text',
							'label'   => __( 'Example Goal 4', 'healthyjoint-goals' ),
							'default' => __( 'have a better social life', 'healthyjoint-goals' ),
						),
						'example5'       => array(
							'type'    => 'text',
							'label'   => __( 'Example Goal 5', 'healthyjoint-goals' ),
							'default' => __( 'do all of the housework without needing any help', 'healthyjoint-goals' ),
						),
						'example6'       => array(
							'type'    => 'text',
							'label'   => __( 'Example Goal 6', 'healthyjoint-goals' ),
							'default' => __( 'spend more time in the garden', 'healthyjoint-goals' ),
						),
					),
				),
			),
		),
		'style'   => array(
			'title'    => __( 'Style', 'healthyjoint-goals' ),
			'sections' => array(
				'colors' => array(
					'title'  => __( 'Colors', 'healthyjoint-goals' ),
					'fields' => array(
						'primary_color'   => array(
							'type'       => 'color',
							'label'      => __( 'Primary Color', 'healthyjoint-goals' ),
							'default'    => '007cba',
							'show_reset' => true,
						),
						'secondary_color' => array(
							'type'       => 'color',
							'label'      => __( 'Secondary Color', 'healthyjoint-goals' ),
							'default'    => 'f0f0f0',
							'show_reset' => true,
						),
					),
				),
			),
		),
	)
);
