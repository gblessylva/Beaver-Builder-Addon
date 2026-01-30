<?php
/**
 * Progress Module for Beaver Builder
 *
 * @package HealthyJointGoals
 */

use HealthyJointGoals\Core\BeaverBuilderHelper;

/**
 * Progress Module Class
 */
class HJProgressModule extends FLBuilderModule {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Progress Tracker', 'healthyjoint-goals' ),
				'description'     => __( 'Display progress timeline with check-ins and goal information', 'healthyjoint-goals' ),
				'category'        => __( 'HealthyJoint', 'healthyjoint-goals' ),
				'group'           => __( 'HealthyJoint Modules', 'healthyjoint-goals' ),
				'dir'             => HEALTHYJOINT_GOALS_PLUGIN_DIR . 'beaver-builder/modules/progress/',
				'url'             => HEALTHYJOINT_GOALS_PLUGIN_URL . 'beaver-builder/modules/progress/',
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
				'icon'            => 'chart-line.svg',
			)
		);

		// Enqueue frontend scripts and styles.
		$this->add_css( 'hj-progress-style', $this->url . 'css/frontend.css' );
		$this->add_js( 'hj-progress-script', $this->url . 'js/progress.js', array( 'jquery' ), '', true );

		// Localize script for AJAX.
		$goal_id = BeaverBuilderHelper::get_goal_id_with_fallback( $this->settings );
		$api_token = \HealthyJointGoals\Core\AuthenticationHelper::get_api_token();
		
		wp_localize_script(
			'hj-progress-script',
			'hjProgress',
			array(
				'ajaxUrl'           => rest_url( 'wp/v2/' ),
				'restUrl'           => rest_url( 'healthyjoint/v1/' ),
				'apiToken'          => $api_token ? $api_token : '',
				'goalId'            => $goal_id,
				'checkinUrl'        => isset( $this->settings->checkin_url ) ? esc_url( $this->settings->checkin_url ) : '',
				'editGoalUrl'       => isset( $this->settings->edit_goal_url ) ? esc_url( $this->settings->edit_goal_url ) : '',
				'completeGoalUrl'   => isset( $this->settings->complete_goal_url ) ? esc_url( $this->settings->complete_goal_url ) : '',
			)
		);
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		if ( $this->settings && is_object( $this->settings ) ) {
			wp_enqueue_script( 'hj-progress-script' );
		}
	}
}

// Register the module.
FLBuilder::register_module(
	'HJProgressModule',
	array(
		'general' => array(
			'title'    => __( 'General', 'healthyjoint-goals' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General Settings', 'healthyjoint-goals' ),
					'fields' => array(
						'title'            => array(
							'type'    => 'text',
							'label'   => __( 'Progress Title', 'healthyjoint-goals' ),
							'default' => __( 'Your progress', 'healthyjoint-goals' ),
						),
						'goal_title'       => array(
							'type'    => 'text',
							'label'   => __( 'Goal Section Title', 'healthyjoint-goals' ),
							'default' => __( 'Your goal is to be able to...', 'healthyjoint-goals' ),
						),
						'test_goal_id'     => array(
							'type'    => 'select',
							'label'   => __( 'Test Goal ID (Admin Preview)', 'healthyjoint-goals' ),
							'help'    => __( 'Select a goal to preview how the progress will look with actual data. Leave empty for production use.', 'healthyjoint-goals' ),
							'options' => BeaverBuilderHelper::get_goals_for_dropdown(),
						),
						'checkin_url'      => array(
							'type'        => 'link',
							'label'       => __( 'Check-in URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/check-in', 'healthyjoint-goals' ),
							'help'        => __( 'URL for the "Add another check-in" button', 'healthyjoint-goals' ),
						),
						'edit_goal_url'    => array(
							'type'        => 'link',
							'label'       => __( 'Edit Goal URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/edit-goal', 'healthyjoint-goals' ),
							'help'        => __( 'URL for the "Edit Goal" button', 'healthyjoint-goals' ),
						),
						'complete_goal_url' => array(
							'type'        => 'link',
							'label'       => __( 'Complete Goal URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/complete-goal', 'healthyjoint-goals' ),
							'help'        => __( 'URL for the "Complete Goal" button', 'healthyjoint-goals' ),
						),
					),
				),
				'content' => array(
					'title'  => __( 'Content Settings', 'healthyjoint-goals' ),
					'fields' => array(
						'filter_title'      => array(
							'type'    => 'text',
							'label'   => __( 'Filter Title', 'healthyjoint-goals' ),
							'default' => __( 'Show check-ins which include', 'healthyjoint-goals' ),
						),
						'goal_question'     => array(
							'type'    => 'text',
							'label'   => __( 'Goal Progress Question', 'healthyjoint-goals' ),
							'default' => __( 'Do you think you are working towards it?', 'healthyjoint-goals' ),
						),
						'goal_description'  => array(
							'type'    => 'textarea',
							'label'   => __( 'Goal Action Description', 'healthyjoint-goals' ),
							'default' => __( 'You can click below to edit the goal, or mark it as complete (great job!).', 'healthyjoint-goals' ),
							'rows'    => 3,
						),
						'edit_button_text'  => array(
							'type'    => 'text',
							'label'   => __( 'Edit Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Edit Goal', 'healthyjoint-goals' ),
						),
						'complete_button_text' => array(
							'type'    => 'text',
							'label'   => __( 'Complete Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Complete Goal', 'healthyjoint-goals' ),
						),
						'checkin_button_text' => array(
							'type'    => 'text',
							'label'   => __( 'Check-in Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Add another check-in', 'healthyjoint-goals' ),
						),
					),
				),
			),
		),
		'style' => array(
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
						'progress_color'  => array(
							'type'       => 'color',
							'label'      => __( 'Progress Bar Color', 'healthyjoint-goals' ),
							'default'    => '4CAF50',
							'show_reset' => true,
						),
					),
				),
			),
		),
	)
);
