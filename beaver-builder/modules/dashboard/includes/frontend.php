<?php
/**
 * Frontend template for Dashboard Module
 *
 * @package HealthyJoint_Goals
 */

// Require the helper classes.
// use HealthyJointGoals\Core\AuthenticationHelper;
use HealthyJointGoals\Core\BeaverBuilderHelper;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// AuthenticationHelper::require_login();

// Get user name
$user_name = wp_get_current_user()->first_name ? wp_get_current_user()->first_name : wp_get_current_user()->display_name;
$greeting  = ! empty( $settings->greeting_text ) ? $settings->greeting_text : 'Hello';

?>
<div class="hj-dashboard-module <?php echo esc_attr( $id ); ?>"
	data-edit-goal-url="/edit-goal"
	data-complete-goal-url="<?php echo esc_attr( $settings->complete_goal_url ); ?>"
	data-full-checkin-url="/new-assessment"
	data-previous-checkins-url="/progress"
	data-success-redirect-url="<?php echo esc_attr( $settings->success_redirect_url ); ?>">
	
	<div class="hj-dashboard-container">
		<!-- Greeting Section -->
		<div class="hj-dashboard-greeting">
			<h1 class="hj-greeting-text"><?php echo esc_html( $greeting . ' '  ); ?></h1>
		</div>
		
		<!-- Main Dashboard Content -->
		<div class="hj-dashboard-content">
			
			<!-- Goal Card Section -->
			<div class="hj-goal-section">
				<!-- Decorative Circle -->
				<div class="hj-goal-circle"></div>
				<div class="hj-goal-card">
					<div class="hj-goal-card-inner">
						<h2 class="hj-goal-card-title"><?php echo esc_html( $settings->goal_card_title ); ?></h2>
						<div class="hj-goal-text-container">
							<p class="hj-goal-text"><?php echo esc_html( '"' . $settings->goal_placeholder . '"' ); ?></p>
						</div>
						
						<div class="hj-goal-actions">
							<button type="button" class="hj-btn hj-btn-dark hj-btn-edit-goal">
								<?php echo esc_html( $settings->edit_goal_text ); ?>
							</button>
							<button type="button" class="hj-btn hj-btn-outline hj-btn-complete-goal">
								<?php echo esc_html( $settings->complete_goal_text ); ?>
							</button>
						</div>
					</div>
					
				</div>
			</div>
			
			<!-- Rating Section -->
			<div class="hj-rating-section">
				<div class="hj-rating-content">
					<h3 class="hj-rating-question"><?php echo esc_html( $settings->rating_question ); ?></h3>
					<p class="hj-rating-description"><?php echo esc_html( $settings->rating_description ); ?></p>
					
					<!-- Rating Slider -->
					<div class="hj-rating-slider-container">
						<div class="hj-rating-slider">
							<div class="hj-slider-track">
								<div class="hj-slider-fill"></div>
								<div class="hj-slider-thumb" data-value="3">
									<span class="hj-thumb-value">3</span>
								</div>
							</div>
							<div class="hj-slider-labels">
								<span class="hj-slider-label">1</span>
								<span class="hj-slider-label">2</span>
								<span class="hj-slider-label">3</span>
								<span class="hj-slider-label">4</span>
								<span class="hj-slider-label">5</span>
							</div>
						</div>
						<input type="hidden" name="rating_value" value="3" class="hj-rating-input">
					</div>
					
					<!-- Action Buttons -->
					<div class="hj-rating-actions">
						<div class="hj-primary-actions">
							<button type="button" class="hj-btn hj-btn-dark hj-btn-submit">
								<?php echo esc_html( $settings->submit_text ); ?>
							</button>
							<button type="button" class="hj-btn hj-btn-outline hj-btn-full-checkin">
								<?php echo esc_html( $settings->full_checkin_text ); ?>
							</button>
						</div>
						
						<div class="hj-secondary-action">
							<a href="/progress" class="hj-link-previous">
								<span class="hj-link-icon">↻</span>
								<?php echo esc_html( $settings->see_previous_text ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- LearnDash Courses Section -->
		<div class="hj-courses-section">
			<h2 class="hj-courses-title"><?php echo esc_html( $settings->courses_section_title ); ?></h2>
			<div class="hj-courses-grid">
			<?php
			// Get LearnDash courses.
			$courses_args = array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => 'publish',
				'posts_per_page' => 6,
				'meta_query'     => array(
					array(
						'key'     => '_learndash_course_featured',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			);

				$courses = get_posts( $courses_args );

			if ( empty( $courses ) ) {
				// Fallback: get any published courses
				$courses_args['meta_query'] = array();
				$courses                    = get_posts( $courses_args );
			}

			foreach ( $courses as $course ) {
				$course_id      = $course->ID;
				$course_title   = get_the_title( $course_id );
				$course_excerpt = get_the_excerpt( $course_id );
				$course_url     = get_permalink( $course_id );
				$course_image   = get_the_post_thumbnail_url( $course_id, 'medium' );

				// Get course progress if user is enrolled
				$user_id         = get_current_user_id();
				$course_progress = 0;
				$course_status   = 'not-started';

				if ( $user_id && function_exists( 'learndash_user_get_course_progress' ) ) {
					// Check if user is enrolled in the course
					$is_enrolled = sfwd_lms_has_access( $course_id, $user_id );

					if ( $is_enrolled ) {
						$progress_data = learndash_user_get_course_progress( $user_id, $course_id );

						if ( ! empty( $progress_data ) && is_array( $progress_data ) ) {
							// Calculate percentage from completed vs total steps
							$completed = isset( $progress_data['completed'] ) ? absint( $progress_data['completed'] ) : 0;
							$total     = isset( $progress_data['total'] ) ? absint( $progress_data['total'] ) : 0;

							if ( $total > 0 ) {
								$course_progress = round( ( $completed / $total ) * 100 );
							}

							// Determine course status
							if ( $course_progress >= 100 ) {
								$course_status = 'completed';
							} elseif ( $course_progress > 0 ) {
								$course_status = 'in-progress';
							}
						}
					}
				}

				// Default image if none set
				if ( ! $course_image ) {
					$course_image = $settings->default_course_image ? $settings->default_course_image : '';
				}
				?>
			
				<div class="hj-course-card-outer">
					<div class="hj-course-card">
						<div class="hj-course-card-inner">
							<div class="hj-course-content">
								<?php
								// var_dump($course_status);
								if ( 'completed' === $course_status ) {
									?>
									<div class="hj-course-status">
										<span class="hj-status-icon">✓</span>
										<span class="hj-status-text"><?php esc_html_e( 'Completed', 'healthyjoint-goals' ); ?></span>
									</div>
								<?php } elseif ( 'in-progress' === $course_status ) { ?>
								<div class="hj-course-progress">
									<div class="hj-progress-bar">
										<div class="hj-progress-fill" style="width: <?php echo esc_attr( $course_progress ); ?>%"></div>
									</div>
									<div class="hj-course-status-inprogress">
										<span class="hj-status-icon-inprogress">
											<i class="fal fa-ellipsis-h"></i>
										</span>
										<span class="hj-progress-text"><?php echo esc_html( $course_progress . '% viewed' ); ?></span>
									</div>
								</div>
								<?php } ?>
								
								<h3 class="hj-course-title"><?php echo esc_html( $course_title ); ?></h3>
								<p class="hj-course-description"><?php echo esc_html( $course_excerpt ); ?></p>
								
								<div class="hj-course-action">
									<a href="<?php echo esc_url( $course_url ); ?>" class="hj-course-btn">
									<?php echo esc_html( 'Learn about ' . $course_title ); ?>
										<span class="hj-btn-arrow">→</span>
									</a>
								</div>
							</div>
						</div>
						<?php if ( $course_image ) : ?>
							<div class="hj-course-image">
								<img src="<?php echo esc_url( $course_image ); ?>" alt="<?php echo esc_attr( $course_title ); ?>">
							</div>
							<?php endif; ?>
					</div>
					<div class="hj-course-card-outline">
					</div>
				</div>
				<?php } ?>
				
				<?php if ( empty( $courses ) ) : ?>
				<div class="hj-courses-empty">
					<h3><?php esc_html_e( 'No courses available', 'healthyjoint-goals' ); ?></h3>
					<p><?php esc_html_e( 'Check back later for new learning content about osteoarthritis management.', 'healthyjoint-goals' ); ?></p>
				</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Loading State -->
		<div class="hj-dashboard-loading" style="display: none;">
			<div class="hj-loading-spinner"></div>
			<p><?php esc_html_e( 'Loading your dashboard...', 'healthyjoint-goals' ); ?></p>
		</div>
		
		<!-- Success Message -->
		<div class="hj-dashboard-message" style="display: none;">
			<div class="hj-message-content"></div>
		</div>
	</div>
</div>
