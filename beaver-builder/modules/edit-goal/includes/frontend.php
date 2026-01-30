<?php
/**
 * Frontend template for Edit Goal Module
 *
 * @package HealthyJoint_Goals
 */

// Require the helper classes.

use HealthyJointGoals\Core\AuthenticationHelper;
use HealthyJointGoals\Core\BeaverBuilderHelper;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// AuthenticationHelper::require_login();

// Get the goal ID using utility helper.
$goal_id = BeaverBuilderHelper::get_goal_id_with_fallback( $settings );

?>
<div class="hj-edit-goal-form <?php echo esc_attr( $id ); ?>" data-redirect-url="<?php echo esc_attr( $settings->redirect_url ); ?>" data-goal-id="<?php echo esc_attr( $goal_id ); ?>">
	<div class="hj-form-container">
		<div class="hj-form-header">
			<div class="hj-header-content">
				<h2 class="hj-form-title"><?php echo esc_html( $settings->title ); ?></h2>
				<p class="hj-form-subtitle"><?php echo esc_html( $settings->subtitle ); ?></p>
			</div>
			<div class="hj-header-decoration">
				<div class="hj-green-circle"></div>
			</div>
		</div>

		<form id="hj-goal-form" class="hj-goal-form">
			<input type="hidden" id="hjGoalId" name="goal_id" value="<?php echo esc_attr( $goal_id ); ?>">
			
			<div class="hj-main-content">
				<div class="hj-form-section">
					<div class="hj-section-header">
						<div class="hj-step-number">1</div>
						<div class="hj-section-content">
							<h3 class="hj-section-title"><?php echo esc_html( $settings->section1_title ); ?></h3>
							<p class="hj-section-subtitle"><?php echo esc_html( $settings->section1_subtitle ); ?></p>
						</div>
					</div>
					
					<div class="hj-priority-options">
						<label class="hj-priority-option">
							<input type="checkbox" name="focus_areas[]" value="movement" class="hj-priority-checkbox">
							<span class="hj-priority-label">Movement</span>
						</label>
						<label class="hj-priority-option">
							<input type="checkbox" name="focus_areas[]" value="pain" class="hj-priority-checkbox">
							<span class="hj-priority-label">Pain</span>
						</label>
						<label class="hj-priority-option">
							<input type="checkbox" name="focus_areas[]" value="eating_habits" class="hj-priority-checkbox">
							<span class="hj-priority-label">Eating habits</span>
						</label>
						<label class="hj-priority-option">
							<input type="checkbox" name="focus_areas[]" value="bodyweight" class="hj-priority-checkbox">
							<span class="hj-priority-label">Bodyweight</span>
						</label>
						<label class="hj-priority-option">
							<input type="checkbox" name="focus_areas[]" value="mood" class="hj-priority-checkbox">
							<span class="hj-priority-label">Mood</span>
						</label>
					</div>
				</div>

				<div class="hj-form-section">
					<div class="hj-section-header">
						<div class="hj-step-number">2</div>
						<div class="hj-section-content">
							<h3 class="hj-section-title"><?php echo esc_html( $settings->section2_title ); ?></h3>
							<p class="hj-section-subtitle"><?php echo esc_html( $settings->section2_subtitle ); ?></p>
						</div>
					</div>
					
					<div class="hj-goal-type-container">
						<!-- Dynamic goal type options will be inserted here by JavaScript -->
					</div>
				</div>

				<div class="hj-form-section">
					<div class="hj-section-header">
						<div class="hj-section-content">
							<h3 class="hj-section-title"><?php echo esc_html( $settings->section3_title ); ?></h3>
							<p class="hj-section-subtitle"><?php echo esc_html( $settings->section3_subtitle ); ?></p>
						</div>
					</div>
					
					<textarea 
						id="hj-goal-description" 
						name="goal_description" 
						class="hj-goal-textarea" 
						rows="4"
						placeholder="Do this"
						required
					></textarea>

					<div class="hj-form-message" style="display: none;">
						<div class="hj-message-content"></div>
					</div>

					<div class="hj-form-actions">
						<button type="button" class="hj-btn hj-btn-cancel">
							Cancel
						</button>
						<button type="submit" class="hj-btn hj-btn-save">
							<span class="hj-btn-text">Save</span>
							<span class="hj-btn-spinner" style="display: none;">
								<svg class="hj-spinner" viewBox="0 0 24 24">
									<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"></circle>
									<path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor"></path>
								</svg>
							</span>
						</button>
					</div>
				</div>
			</div>

			<div class="hj-side-bar">
				<div class="hj-info-sidebar">
					<div class="hj-info-section">
						<div class="hj-info-icon hj-info-purple">?</div>
						<div>
							<h4 class="hj-info-title"><?php echo esc_html( $settings->info_title1 ); ?></h4>
							<ul class="hj-info-list">
								<li><?php echo esc_html( $settings->info_list_item1 ); ?></li>
								<li><?php echo esc_html( $settings->info_list_item2 ); ?></li>
								<li><?php echo esc_html( $settings->info_list_item3 ); ?></li>
							</ul>
						</div>
					</div>

					<div class="hj-info-section">
						<div class="hj-info-icon hj-info-dark">💡</div>
						<div>
							<h4 class="hj-info-title"><?php echo esc_html( $settings->examples_title ); ?></h4>
							<div class="hj-examples">
								<?php if ( ! empty( $settings->example1 ) ) : ?>
									<p>"<?php echo esc_html( $settings->example1 ); ?>"</p>
								<?php endif; ?>
								<?php if ( ! empty( $settings->example2 ) ) : ?>
									<p>"<?php echo esc_html( $settings->example2 ); ?>"</p>
								<?php endif; ?>
								<?php if ( ! empty( $settings->example3 ) ) : ?>
									<p>"<?php echo esc_html( $settings->example3 ); ?>"</p>
								<?php endif; ?>
								<?php if ( ! empty( $settings->example4 ) ) : ?>
									<p>"<?php echo esc_html( $settings->example4 ); ?>"</p>
								<?php endif; ?>
								<?php if ( ! empty( $settings->example5 ) ) : ?>
									<p>"<?php echo esc_html( $settings->example5 ); ?>"</p>
								<?php endif; ?>
								<?php if ( ! empty( $settings->example6 ) ) : ?>
									<p>"<?php echo esc_html( $settings->example6 ); ?>"</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
