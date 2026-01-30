<?php
/**
 * Check In Module for Beaver Builder
 *
 * @package HealthyJointGoals
 */

/**
 * Check In Module Class
 */
class HJCheckInModule extends FLBuilderModule {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Check In Form', 'healthyjoint-goals' ),
				'description'     => __( 'A form to check in on goal progress and add ratings', 'healthyjoint-goals' ),
				'category'        => __( 'HealthyJoint', 'healthyjoint-goals' ),
				'group'           => __( 'HealthyJoint Modules', 'healthyjoint-goals' ),
				'dir'             => HEALTHYJOINT_GOALS_PLUGIN_DIR . 'beaver-builder/modules/check-in/',
				'url'             => HEALTHYJOINT_GOALS_PLUGIN_URL . 'beaver-builder/modules/check-in/',
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
				'icon'            => 'calendar-check.svg',
			)
		);

		// Enqueue frontend scripts and styles.
		$this->add_css( 'hj-check-in-style', $this->url . 'css/frontend.css' );
		$this->add_js( 'hj-check-in-script', $this->url . 'js/check-in.js', array( 'jquery' ), '', true );

		// Localize script for AJAX.
		$api_token = \HealthyJointGoals\Core\AuthenticationHelper::get_api_token();
		
		wp_localize_script(
			'hj-check-in-script',
			'hjCheckIn',
			array(
				'ajaxUrl'     => rest_url( 'wp/v2/' ),
				'restUrl'     => rest_url( 'healthyjoint/v1/' ),
				'apiToken'    => $api_token ? $api_token : '',
				'newGoalUrl'  => isset( $this->settings->new_goal_url ) ? $this->settings->new_goal_url : '',
				'editGoalUrl' => isset( $this->settings->edit_goal_url ) ? $this->settings->edit_goal_url : '',
				'strings'     => array(
					// Goal loading messages.
					'noGoalsFound'               => __( 'No goals found', 'healthyjoint-goals' ),
					'unableToLoadGoals'          => __( 'Unable to load goals. Please refresh the page and try again.', 'healthyjoint-goals' ),
					'loadingGoals'               => __( 'Loading goals...', 'healthyjoint-goals' ),
					'noActiveGoalsAvailable'     => __( 'No active goals available', 'healthyjoint-goals' ),

					// Goal states
					'noGoalDescriptionAvailable' => __( 'No goal description available', 'healthyjoint-goals' ),
					'noCurrentActiveGoal'        => __( 'You do not currently have an active Goal', 'healthyjoint-goals' ),

					// Button texts.
					'editGoal'                   => __( 'Edit Goal', 'healthyjoint-goals' ),
					'completeGoal'               => __( 'Complete Goal', 'healthyjoint-goals' ),
					'newGoal'                    => __( 'New Goal', 'healthyjoint-goals' ),
					'cancel'                     => __( 'Cancel', 'healthyjoint-goals' ),
					'save'                       => __( 'Save', 'healthyjoint-goals' ),
					'saving'                     => __( 'Saving...', 'healthyjoint-goals' ),
					'saveCheckIn'                => __( 'Save Check-In', 'healthyjoint-goals' ),

					// Questions and messages.
					'workingTowardsGoal'         => __( 'Do you think you are working towards it?', 'healthyjoint-goals' ),
					'editOrCompleteGoal'         => __( 'You can click below to edit the goal, or mark it as complete (great job!).', 'healthyjoint-goals' ),
					'wouldLikeNewGoal'           => __( 'Would you like to add a new Goal?', 'healthyjoint-goals' ),
					'createFirstGoal'            => __( 'Click the button below to create your first goal and start your wellness journey.', 'healthyjoint-goals' ),

					// Confirmation dialogs.
					'confirmCompleteGoal'        => __( 'Are you sure you want to mark this goal as completed?', 'healthyjoint-goals' ),
					'confirmCancel'              => __( 'Are you sure you want to cancel? All entered data will be lost.', 'healthyjoint-goals' ),
					'editGoalNotImplemented'     => __( 'Edit goal functionality - to be implemented', 'healthyjoint-goals' ),
					'navigateToGoalCreation'     => __( 'Please navigate to the goal creation page to add a new goal.', 'healthyjoint-goals' ),
					'navigateToGoalEditing'      => __( 'Please navigate to the goal editing page to edit this goal.', 'healthyjoint-goals' ),
					'noGoalSelectedForEditing'   => __( 'No goal selected for editing', 'healthyjoint-goals' ),

					// Success messages.
					'goalCompletedSuccessfully'  => __( 'Goal completed successfully! Well done!', 'healthyjoint-goals' ),
					'goalStatusUpdated'          => __( 'Goal status updated successfully!', 'healthyjoint-goals' ),
					'checkInSavedSuccessfully'   => __( 'Check-in saved successfully!', 'healthyjoint-goals' ),

					// Error messages.
					'failedToUpdateGoalStatus'   => __( 'Failed to update goal status', 'healthyjoint-goals' ),
					'noGoalSelected'             => __( 'No goal selected', 'healthyjoint-goals' ),
					'failedToCreateCheckIn'      => __( 'Failed to create check-in', 'healthyjoint-goals' ),
					'errorCreatingCheckIn'       => __( 'An error occurred while creating the check-in', 'healthyjoint-goals' ),

					// Console messages.
					'messageContainerNotFound'   => __( 'Message container not found, creating one', 'healthyjoint-goals' ),
					'wpNonceNotFound'            => __( 'WordPress nonce not found. API requests may fail.', 'healthyjoint-goals' ),

					// Template content.
					'checkInTitle'               => __( 'Check-in', 'healthyjoint-goals' ),
					'reviewWhereYouAre'          => __( 'Let\'s review where you are right now', 'healthyjoint-goals' ),
					'yourGoalIsTo'               => __( 'Your goal is to be able to...', 'healthyjoint-goals' ),
					'loadingGoalDefault'         => __( '"Loading goal..."', 'healthyjoint-goals' ),
					'howAreYourPriorityAreas'    => __( 'How are your priority areas?', 'healthyjoint-goals' ),
					'ratingScaleDescription'     => __( 'Use the scale to rate yourself from 1 being an area needing a lot of improvement, to 5 being something you think is going well.', 'healthyjoint-goals' ),
					'eatingHabits'               => __( 'Eating habits', 'healthyjoint-goals' ),
					'bodyweight'                 => __( 'Bodyweight', 'healthyjoint-goals' ),
					'addNoteDescription'         => __( 'Add a note to say how you\'re doing/feeling/thinking. Or maybe just write what you have been working on. (optional)', 'healthyjoint-goals' ),
					'typeYourNote'               => __( 'Type your note', 'healthyjoint-goals' ),
				),
			)
		);
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		if ( $this->settings && is_object( $this->settings ) ) {
			wp_enqueue_script( 'hj-check-in-script' );
		}
	}
}

// Register the module
FLBuilder::register_module(
	'HJCheckInModule',
	array(
		'general' => array(
			'title'    => __( 'General', 'healthyjoint-goals' ),
			'sections' => array(
				'general'         => array(
					'title'  => __( 'General Settings', 'healthyjoint-goals' ),
					'fields' => array(
						'title'        => array(
							'type'    => 'text',
							'label'   => __( 'Form Title', 'healthyjoint-goals' ),
							'default' => __( 'Check In on Your Progress', 'healthyjoint-goals' ),
						),
						'subtitle'     => array(
							'type'    => 'textarea',
							'label'   => __( 'Form Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'How are you feeling about your progress? Rate your experience and add any notes.', 'healthyjoint-goals' ),
							'rows'    => 3,
						),
						'redirect_url' => array(
							'type'        => 'link',
							'label'       => __( 'Success Redirect URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/dashboard', 'healthyjoint-goals' ),
						),
						'new_goal_url'  => array(
							'type'        => 'link',
							'label'       => __( 'New Goal URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/add-goal', 'healthyjoint-goals' ),
							'help'        => __( 'URL to redirect to when user clicks "New Goal" button (when no active goals exist).', 'healthyjoint-goals' ),
						),
						'edit_goal_url' => array(
							'type'        => 'link',
							'label'       => __( 'Edit Goal URL', 'healthyjoint-goals' ),
							'placeholder' => __( '/edit-goal', 'healthyjoint-goals' ),
							'help'        => __( 'URL to redirect to when user clicks "Edit Goal" button. The goal ID will be appended as a query parameter.', 'healthyjoint-goals' ),
						),
					),
				),
				'form_sections'   => array(
					'title'  => __( 'Form Section Text', 'healthyjoint-goals' ),
					'fields' => array(
						'section1_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Goal Selection Title', 'healthyjoint-goals' ),
							'default' => __( 'Select Your Goal', 'healthyjoint-goals' ),
						),
						'section1_subtitle' => array(
							'type'    => 'text',
							'label'   => __( 'Goal Selection Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'Choose which goal you want to check in on.', 'healthyjoint-goals' ),
						),
						'section2_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Rating Title', 'healthyjoint-goals' ),
							'default' => __( 'How are you feeling?', 'healthyjoint-goals' ),
						),
						'section2_subtitle' => array(
							'type'    => 'text',
							'label'   => __( 'Rating Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'Rate different aspects of your progress.', 'healthyjoint-goals' ),
						),
						'section3_title'    => array(
							'type'    => 'text',
							'label'   => __( 'Notes Title', 'healthyjoint-goals' ),
							'default' => __( 'Additional Notes', 'healthyjoint-goals' ),
						),
						'section3_subtitle' => array(
							'type'    => 'text',
							'label'   => __( 'Notes Subtitle', 'healthyjoint-goals' ),
							'default' => __( 'Share any thoughts, challenges, or victories.', 'healthyjoint-goals' ),
						),
					),
				),
				'rating_fields'   => array(
					'title'  => __( 'Rating Categories', 'healthyjoint-goals' ),
					'fields' => array(
						'rating1_label' => array(
							'type'    => 'text',
							'label'   => __( 'Rating 1 Label', 'healthyjoint-goals' ),
							'default' => __( 'Pain Level', 'healthyjoint-goals' ),
						),
						'rating1_desc'  => array(
							'type'    => 'text',
							'label'   => __( 'Rating 1 Description', 'healthyjoint-goals' ),
							'default' => __( 'How is your pain today?', 'healthyjoint-goals' ),
						),
						'rating2_label' => array(
							'type'    => 'text',
							'label'   => __( 'Rating 2 Label', 'healthyjoint-goals' ),
							'default' => __( 'Energy Level', 'healthyjoint-goals' ),
						),
						'rating2_desc'  => array(
							'type'    => 'text',
							'label'   => __( 'Rating 2 Description', 'healthyjoint-goals' ),
							'default' => __( 'How is your energy today?', 'healthyjoint-goals' ),
						),
						'rating3_label' => array(
							'type'    => 'text',
							'label'   => __( 'Rating 3 Label', 'healthyjoint-goals' ),
							'default' => __( 'Mood', 'healthyjoint-goals' ),
						),
						'rating3_desc'  => array(
							'type'    => 'text',
							'label'   => __( 'Rating 3 Description', 'healthyjoint-goals' ),
							'default' => __( 'How is your mood today?', 'healthyjoint-goals' ),
						),
						'rating4_label' => array(
							'type'    => 'text',
							'label'   => __( 'Rating 4 Label', 'healthyjoint-goals' ),
							'default' => __( 'Progress Satisfaction', 'healthyjoint-goals' ),
						),
						'rating4_desc'  => array(
							'type'    => 'text',
							'label'   => __( 'Rating 4 Description', 'healthyjoint-goals' ),
							'default' => __( 'How satisfied are you with your progress?', 'healthyjoint-goals' ),
						),
					),
				),
				'sidebar_content' => array(
					'title'  => __( 'Sidebar Information', 'healthyjoint-goals' ),
					'fields' => array(
						'info_title1'     => array(
							'type'    => 'text',
							'label'   => __( 'Information Section Title', 'healthyjoint-goals' ),
							'default' => __( 'Why Check In?', 'healthyjoint-goals' ),
						),
						'info_list_item1' => array(
							'type'    => 'text',
							'label'   => __( 'Info List Item 1', 'healthyjoint-goals' ),
							'default' => __( 'Regular check-ins help track your progress over time', 'healthyjoint-goals' ),
						),
						'info_list_item2' => array(
							'type'    => 'text',
							'label'   => __( 'Info List Item 2', 'healthyjoint-goals' ),
							'default' => __( 'Honest ratings help identify patterns and improvements', 'healthyjoint-goals' ),
						),
						'info_list_item3' => array(
							'type'    => 'text',
							'label'   => __( 'Info List Item 3', 'healthyjoint-goals' ),
							'default' => __( 'Your notes provide valuable context for your journey', 'healthyjoint-goals' ),
						),
						'tips_title'      => array(
							'type'    => 'text',
							'label'   => __( 'Tips Section Title', 'healthyjoint-goals' ),
							'default' => __( 'Check-in Tips', 'healthyjoint-goals' ),
						),
						'tip1'            => array(
							'type'    => 'text',
							'label'   => __( 'Tip 1', 'healthyjoint-goals' ),
							'default' => __( 'Be honest about your experience', 'healthyjoint-goals' ),
						),
						'tip2'            => array(
							'type'    => 'text',
							'label'   => __( 'Tip 2', 'healthyjoint-goals' ),
							'default' => __( 'Note any changes since your last check-in', 'healthyjoint-goals' ),
						),
						'tip3'            => array(
							'type'    => 'text',
							'label'   => __( 'Tip 3', 'healthyjoint-goals' ),
							'default' => __( 'Include both challenges and victories', 'healthyjoint-goals' ),
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
