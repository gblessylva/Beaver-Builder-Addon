<?php
/**
 * Frontend template for Progress Module
 *
 * @package HealthyJoint_Goals
 */

// Require the helper classes.;

// use HealthyJointGoals\Core\AuthenticationHelper;
use HealthyJointGoals\Core\BeaverBuilderHelper;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// AuthenticationHelper::require_login();

// Get the goal ID using utility helper.
$goal_id = BeaverBuilderHelper::get_goal_id_with_fallback( $settings );

?>
<div class="hj-progress-module <?php echo esc_attr( $id ); ?>" 
	data-goal-id="<?php echo esc_attr( $goal_id ); ?>"
	data-checkin-url="<?php echo esc_attr( $settings->checkin_url ); ?>"
	data-edit-goal-url="<?php echo esc_attr( $settings->edit_goal_url ); ?>"
	data-complete-goal-url="<?php echo esc_attr( $settings->complete_goal_url ); ?>">
	
	<div class="hj-form-container">
		<div class="hj-main-content">
			
			<!-- Loading State -->
			<div class="hj-progress-loading" style="display: none;">
				<div class="hj-loading-spinner"></div>
				<p><?php esc_html_e( 'Loading your progress...', 'healthyjoint-goals' ); ?></p>
			</div>
			
			<!-- Error State -->
			<div class="hj-progress-error" style="display: none;">
				<p class="hj-error-message"><?php esc_html_e( 'Unable to load progress data. Please try again.', 'healthyjoint-goals' ); ?></p>
			</div>
			
			<!-- Top Section - Goal Display -->
			<div class="hj-top-section">
				<div class="hj-left-content">
					<div class="hj-goal-header-content">
					<h2 class="hj-main-title"><?php esc_html_e( 'Progress', 'healthyjoint-goals' ); ?></h2>
					<p class="hj-main-subtitle"><?php esc_html_e( 'Track your journey and see how far you\'ve come', 'healthyjoint-goals' ); ?></p>
					</div>


					<a href="/new-assessment" class="hj-btn hj-btn-add-goal"><?php esc_html_e( 'Add Another Check-in', 'healthyjoint-goals' ); ?>
                    <span class="hj-btn-arrow">›</span>
                </a>
				
				</div>
				
				<div class="hj-goal-display-card">
					<h2 class="hj-goal-card-title"><?php esc_html_e( 'Your goal is to be able to...', 'healthyjoint-goals' ); ?></h2>
					<div class="hj-goal-content">
						<p class="hj-goal-text"><?php esc_html_e( '"Loading goal..."', 'healthyjoint-goals' ); ?></p>
					</div>
					<div class="hj-goal-underline"></div>
					
					<div class="hj-goal-question">
						<p class="hj-question-text"><?php esc_html_e( 'Do you think you are working towards it?', 'healthyjoint-goals' ); ?></p>
						<p class="hj-question-subtitle"><?php esc_html_e( 'You can click below to edit the goal, or mark it as complete (great job!).', 'healthyjoint-goals' ); ?></p>
					</div>
					
					<div class="hj-goal-actions">
						<a href="#" class="hj-btn hj-btn-edit-goal"><?php esc_html_e( 'Edit Goal', 'healthyjoint-goals' ); ?></a>
						<a href="#" class="hj-btn hj-btn-complete-goal"><?php esc_html_e( 'Complete Goal', 'healthyjoint-goals' ); ?></a>
					</div>
				</div>
			</div>

			<!-- Bottom Section - Progress Timeline -->
			<div class="hj-bottom-section">
				<div class="hj-timeline">
					<h2 class="hj-timeline-title"><?php esc_html_e( 'Your check-in history', 'healthyjoint-goals' ); ?></h2>
					<p class="hj-timeline-subtitle"><?php esc_html_e( 'See your progress over time and track improvements in your priority areas.', 'healthyjoint-goals' ); ?></p>
					
					<!-- Filter Section -->
					<div class="hj-progress-filters" style="display: none;">
						<p class="hj-filter-title"><?php esc_html_e( 'Show Check-ins which include:', 'healthyjoint-goals' ); ?></p>
						<div class="hj-filter-buttons">
							<!-- Filter buttons will be populated by JavaScript -->
						</div>
					</div>
					
					<!-- Check-ins Timeline -->
					<div class="hj-checkins-timeline">
						<!-- Timeline items will be populated by JavaScript -->
					</div>
				</div>

			</div>
			
		</div>
	</div>
</div>
