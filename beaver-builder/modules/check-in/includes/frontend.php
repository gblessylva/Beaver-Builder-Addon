<?php
/**
 * Frontend template for Check In Module
 *
 * @package HealthyJoint_Goals
 */

// Require the authentication helper.

// use HealthyJointGoals\Core\AuthenticationHelper;

// AuthenticationHelper::require_login();
?>
<div class="hj-check-in-form <?php echo esc_attr( $id ); ?>" data-redirect-url="<?php echo esc_attr( $settings->redirect_url ); ?>" data-new-goal-url="<?php echo esc_attr( $settings->new_goal_url ); ?>">
	<div class="hj-form-container">
		<form id="hj-check-in-form" class="hj-check-in-form-inner">
			<div class="hj-main-content">
				<!-- Top Section - Goal Display -->
				<div class="hj-top-section">
					<div class="hj-left-content">
						<h2 class="hj-main-title"><?php esc_html_e( 'Check-in', 'healthyjoint-goals' ); ?></h2>
						<p class="hj-main-subtitle"><?php esc_html_e( 'Let\'s review where you are right now', 'healthyjoint-goals' ); ?></p>
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
							<a href="/edit-goal" type="button" class="hj-btn hj-btn-edit-goal"><?php esc_html_e( 'Edit Goal', 'healthyjoint-goals' ); ?></a>
							<button type="button" class="hj-btn hj-btn-complete-goal"><?php esc_html_e( 'Complete Goal', 'healthyjoint-goals' ); ?></button>
						</div>
					</div>
				</div>

				<!-- Bottom Section - Priority Areas Rating -->
				<div class="hj-bottom-section">
					<div class="hj-left-ratings">
						<h2 class="hj-ratings-title"><?php esc_html_e( 'How are your priority areas?', 'healthyjoint-goals' ); ?></h2>
						<p class="hj-ratings-subtitle"><?php esc_html_e( 'Use the scale to rate yourself from 1 being an area needing a lot of improvement, to 5 being something you think is going well.', 'healthyjoint-goals' ); ?></p>
						
						<div class="hj-priority-ratings">
							<div class="hj-priority-rating-item">
								<h3 class="hj-priority-name"><?php esc_html_e( 'Eating habits', 'healthyjoint-goals' ); ?></h3>
								<div class="hj-slider-container">
									<div class="hj-slider-track">
										<div class="hj-slider-fill"></div>
										<div class="hj-slider-thumb" data-value="3"></div>
									</div>
									<div class="hj-slider-labels">
										<span>1</span>
										<span>2</span>
										<span>3</span>
										<span>4</span>
										<span>5</span>
									</div>
								</div>
								<input type="hidden" name="eating_habits_rating" value="3" class="hj-rating-value">
							</div>
							
							<div class="hj-priority-rating-item">
								<h3 class="hj-priority-name"><?php esc_html_e( 'Bodyweight', 'healthyjoint-goals' ); ?></h3>
								<div class="hj-slider-container">
									<div class="hj-slider-track">
										<div class="hj-slider-fill"></div>
										<div class="hj-slider-thumb" data-value="3"></div>
									</div>
									<div class="hj-slider-labels">
										<span>1</span>
										<span>2</span>
										<span>3</span>
										<span>4</span>
										<span>5</span>
									</div>
								</div>
								<input type="hidden" name="bodyweight_rating" value="3" class="hj-rating-value">
							</div>
						</div>
					</div>
					
					<div class="hj-right-notes">
						<div class="hj-notes-section">
							<p class="hj-notes-label"><?php esc_html_e( 'Add a note to say how you\'re doing/feeling/thinking. Or maybe just write what you have been working on. (optional)', 'healthyjoint-goals' ); ?></p>
							<textarea 
								id="hj-check-in-notes" 
								name="check_in_notes" 
								class="hj-notes-textarea" 
								rows="8"
								placeholder="<?php esc_attr_e( 'Type your note', 'healthyjoint-goals' ); ?>"
							></textarea>
						</div>
					</div>
				</div>

				<!-- Form Actions -->
				<div class="hj-form-actions-bottom">
					<div class="hj-form-message" style="display: none;">
						<div class="hj-message-content"></div>
					</div>
					
					<div class="hj-form-buttons">
						<button type="button" class="hj-btn hj-btn-cancel"><?php esc_html_e( 'Cancel', 'healthyjoint-goals' ); ?></button>
						<button type="submit" class="hj-btn hj-btn-save">
							<span class="hj-btn-text"><?php esc_html_e( 'Save', 'healthyjoint-goals' ); ?></span>
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
			
			<!-- Hidden goal selection for backend -->
			<select id="hj-goal-select" name="goal_id" style="display: none;" required>
				<option value=""><?php esc_html_e( 'Loading goals...', 'healthyjoint-goals' ); ?></option>
			</select>
		</form>
	</div>
</div>
