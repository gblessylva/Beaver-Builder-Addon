<?php
/**
 * Menu Page class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Admin;

use HealthyJointGoals\Interfaces\Registrable;
use HealthyJointGoals\Services\GoalService;
use HealthyJointGoals\Services\CheckinService;
use HealthyJointGoals\PostTypes\GoalPostType;
use HealthyJointGoals\PostTypes\CheckinPostType;

/**
 * Menu Page class.
 *
 * Handles the plugin's admin menu and dashboard.
 * Follows Single Responsibility and Dependency Inversion principles.
 */
class MenuPage implements Registrable {
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
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu pages.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		// Main menu page.
		add_menu_page(
			__( 'HealthyJoint Goals', 'healthyjoint-goals' ),
			__( 'HJ Goals', 'healthyjoint-goals' ),
			'manage_options',
			'healthyjoint-goals',
			array( $this, 'render_dashboard_page' ),
			'dashicons-heart',
			30
		);

		// Dashboard submenu (same as main page).
		add_submenu_page(
			'healthyjoint-goals',
			__( 'Dashboard', 'healthyjoint-goals' ),
			__( 'Dashboard', 'healthyjoint-goals' ),
			'manage_options',
			'healthyjoint-goals',
			array( $this, 'render_dashboard_page' )
		);

		// Goals submenu.
		add_submenu_page(
			'healthyjoint-goals',
			__( 'Goals', 'healthyjoint-goals' ),
			__( 'Goals', 'healthyjoint-goals' ),
			'edit_posts',
			'edit.php?post_type=' . GoalPostType::get_post_type()
		);

		// Check-ins submenu.
		add_submenu_page(
			'healthyjoint-goals',
			__( 'Check-ins', 'healthyjoint-goals' ),
			__( 'Check-ins', 'healthyjoint-goals' ),
			'edit_posts',
			'edit.php?post_type=' . CheckinPostType::get_post_type()
		);

		// Settings submenu.
		add_submenu_page(
			'healthyjoint-goals',
			__( 'Settings', 'healthyjoint-goals' ),
			__( 'Settings', 'healthyjoint-goals' ),
			'manage_options',
			'healthyjoint-goals-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our plugin pages.
		if ( strpos( $hook, 'healthyjoint-goals' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'healthyjoint-goals-admin',
			HEALTHYJOINT_GOALS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			HEALTHYJOINT_GOALS_VERSION
		);

		wp_enqueue_script(
			'healthyjoint-goals-admin',
			HEALTHYJOINT_GOALS_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			HEALTHYJOINT_GOALS_VERSION,
			true
		);

		// Localize script for AJAX.
		wp_localize_script(
			'healthyjoint-goals-admin',
			'healthyJointGoals',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'healthyjoint_goals_nonce' ),
			)
		);
	}

	/**
	 * Render dashboard page.
	 *
	 * @return void
	 */
	public function render_dashboard_page() {
		// Get statistics from services.
		$stats = $this->get_dashboard_statistics();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'HealthyJoint Goals Dashboard', 'healthyjoint-goals' ); ?></h1>
			
			<div class="hj-dashboard-stats">
				<div class="hj-stat-card">
					<h3><?php esc_html_e( 'Total Goals', 'healthyjoint-goals' ); ?></h3>
					<span class="hj-stat-number"><?php echo esc_html( $stats['total_goals'] ); ?></span>
				</div>
				
				<div class="hj-stat-card">
					<h3><?php esc_html_e( 'Active Goals', 'healthyjoint-goals' ); ?></h3>
					<span class="hj-stat-number"><?php echo esc_html( $stats['active_goals'] ); ?></span>
				</div>
				
				<div class="hj-stat-card">
					<h3><?php esc_html_e( 'Completed Goals', 'healthyjoint-goals' ); ?></h3>
					<span class="hj-stat-number"><?php echo esc_html( $stats['completed_goals'] ); ?></span>
				</div>
				
				<div class="hj-stat-card">
					<h3><?php esc_html_e( 'Total Check-ins', 'healthyjoint-goals' ); ?></h3>
					<span class="hj-stat-number"><?php echo esc_html( $stats['total_checkins'] ); ?></span>
				</div>
			</div>

			<div class="hj-dashboard-content">
				<div class="hj-dashboard-section">
					<h2><?php esc_html_e( 'Recent Goals', 'healthyjoint-goals' ); ?></h2>
					<?php $this->render_recent_goals(); ?>
				</div>
				
				<div class="hj-dashboard-section">
					<h2><?php esc_html_e( 'Recent Check-ins', 'healthyjoint-goals' ); ?></h2>
					<?php $this->render_recent_checkins(); ?>
				</div>
			</div>

			<div class="hj-dashboard-actions">
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . GoalPostType::get_post_type() ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Create New Goal', 'healthyjoint-goals' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . CheckinPostType::get_post_type() ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Add Check-in', 'healthyjoint-goals' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'HealthyJoint Goals Settings', 'healthyjoint-goals' ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'healthyjoint_goals_settings' );
				do_settings_sections( 'healthyjoint_goals_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get dashboard statistics.
	 *
	 * @return array
	 */
	private function get_dashboard_statistics() {
		return array(
			'total_goals'     => $this->goal_service->get_total_goals_count(),
			'active_goals'    => $this->goal_service->get_active_goals_count(),
			'completed_goals' => $this->goal_service->get_completed_goals_count(),
			'total_checkins'  => $this->checkin_service->get_total_checkins_count(),
		);
	}

	/**
	 * Render recent goals section.
	 *
	 * @return void
	 */
	private function render_recent_goals() {
		$recent_goals = $this->goal_service->get_recent_goals( 5 );

		if ( empty( $recent_goals ) ) {
			?>
			<p><?php esc_html_e( 'No goals found. Create your first goal to get started!', 'healthyjoint-goals' ); ?></p>
			<?php
			return;
		}

		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Goal', 'healthyjoint-goals' ); ?></th>
					<th><?php esc_html_e( 'Type', 'healthyjoint-goals' ); ?></th>
					<th><?php esc_html_e( 'Status', 'healthyjoint-goals' ); ?></th>
					<th><?php esc_html_e( 'Progress', 'healthyjoint-goals' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_goals as $goal ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( get_edit_post_link( $goal->ID ) ); ?>">
								<?php echo esc_html( $goal->post_title ); ?>
							</a>
						</td>
						<td><?php echo esc_html( get_post_meta( $goal->ID, '_hj_goal_type', true ) ); ?></td>
						<td><?php echo esc_html( get_post_meta( $goal->ID, '_hj_goal_status', true ) ); ?></td>
						<td>
							<?php
							$current = get_post_meta( $goal->ID, '_hj_goal_current_value', true );
							$target  = get_post_meta( $goal->ID, '_hj_goal_target_value', true );
							$unit    = get_post_meta( $goal->ID, '_hj_goal_unit', true );
							echo esc_html( "$current / $target $unit" );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render recent check-ins section.
	 *
	 * @return void
	 */
	private function render_recent_checkins() {
		$recent_checkins = $this->checkin_service->get_recent_checkins( 5 );

		if ( empty( $recent_checkins ) ) {
			?>
			<p><?php esc_html_e( 'No check-ins found. Add your first check-in!', 'healthyjoint-goals' ); ?></p>
			<?php
			return;
		}

		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Check-in', 'healthyjoint-goals' ); ?></th>
					<th><?php esc_html_e( 'Date', 'healthyjoint-goals' ); ?></th>
					<th><?php esc_html_e( 'Progress', 'healthyjoint-goals' ); ?></th>
					<th><?php esc_html_e( 'Mood', 'healthyjoint-goals' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_checkins as $checkin ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( get_edit_post_link( $checkin->ID ) ); ?>">
								<?php echo esc_html( $checkin->post_title ); ?>
							</a>
						</td>
						<td><?php echo esc_html( get_post_meta( $checkin->ID, '_hj_checkin_date', true ) ); ?></td>
						<td><?php echo esc_html( get_post_meta( $checkin->ID, '_hj_checkin_progress_value', true ) ); ?></td>
						<td><?php echo esc_html( get_post_meta( $checkin->ID, '_hj_checkin_mood_rating', true ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
