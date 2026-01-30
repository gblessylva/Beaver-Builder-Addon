<?php
/**
 * Goal Meta Box class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Admin\MetaBoxes;

use HealthyJointGoals\Interfaces\Registrable;
use HealthyJointGoals\Services\GoalService;
use HealthyJointGoals\PostTypes\GoalPostType;

/**
 * Goal Meta Box class.
 *
 * Handles the goal-specific meta boxes in the admin.
 * Follows Single Responsibility and Dependency Inversion principles.
 */
class GoalMetaBox implements Registrable {
	/**
	 * Goal service instance.
	 *
	 * @var GoalService
	 */
	private $goal_service;

	/**
	 * Constructor.
	 *
	 * @param GoalService $goal_service Goal service dependency.
	 */
	public function __construct( GoalService $goal_service ) {
		$this->goal_service = $goal_service;
	}

	/**
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
	}

	/**
	 * Add meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'hj_goal_details',
			__( 'Goal Details', 'healthyjoint-goals' ),
			array( $this, 'render_goal_details_meta_box' ),
			GoalPostType::get_post_type(),
			'normal',
			'high'
		);
	}

	/**
	 * Render goal details meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_goal_details_meta_box( $post ) {
		// Security nonce.
		wp_nonce_field( 'hj_goal_meta_box', 'hj_goal_meta_box_nonce' );

		// Get current values.
		$purpose      = get_post_meta( $post->ID, 'hj_purpose', true );
		$focus_areas  = get_post_meta( $post->ID, 'hj_focus_areas', true );
		
		// Ensure focus_areas is an array.
		if ( ! is_array( $focus_areas ) ) {
			$focus_areas = array();
		}

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="hj_purpose"><?php esc_html_e( 'Purpose', 'healthyjoint-goals' ); ?></label>
				</th>
				<td>
					<select id="hj_purpose" name="hj_purpose" class="regular-text" required>
						<option value=""><?php esc_html_e( 'Select Purpose', 'healthyjoint-goals' ); ?></option>
						<?php foreach ( GoalPostType::get_purposes() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $purpose, $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'What do you want to achieve with this goal?', 'healthyjoint-goals' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Focus Areas', 'healthyjoint-goals' ); ?></label>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e( 'Focus Areas', 'healthyjoint-goals' ); ?></legend>
						<?php foreach ( GoalPostType::get_focus_areas() as $key => $label ) : ?>
							<label>
								<input 
									type="checkbox" 
									name="hj_focus_areas[]" 
									value="<?php echo esc_attr( $key ); ?>" 
									<?php checked( in_array( $key, $focus_areas, true ) ); ?>
									class="hj-focus-area-checkbox"
								/>
								<?php echo esc_html( $label ); ?>
							</label><br>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'Select up to 2 focus areas (required).', 'healthyjoint-goals' ); ?></p>
					</fieldset>
				</td>
			</tr>
		</table>
		
		<script>
		jQuery(document).ready(function($) {
			var maxSelections = 2;
			$('.hj-focus-area-checkbox').change(function() {
				var checkedCount = $('.hj-focus-area-checkbox:checked').length;
				if (checkedCount > maxSelections) {
					$(this).prop('checked', false);
					alert('<?php esc_js( __( 'You can select a maximum of 2 focus areas.', 'healthyjoint-goals' ) ); ?>');
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta_box_data( $post_id ) {
		// Security checks.
		if ( ! isset( $_POST['hj_goal_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['hj_goal_meta_box_nonce'], 'hj_goal_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( get_post_type( $post_id ) !== GoalPostType::get_post_type() ) {
			return;
		}

		// Sanitize and validate purpose.
		$purpose = sanitize_text_field( wp_unslash( $_POST['hj_purpose'] ?? '' ) );
		$valid_purposes = array_keys( GoalPostType::get_purposes() );
		
		if ( ! in_array( $purpose, $valid_purposes, true ) ) {
			$purpose = '';
		}

		// Sanitize and validate focus areas.
		$focus_areas = array();
		if ( isset( $_POST['hj_focus_areas'] ) && is_array( $_POST['hj_focus_areas'] ) ) {
			$valid_focus_areas = array_keys( GoalPostType::get_focus_areas() );
			
			foreach ( wp_unslash( $_POST['hj_focus_areas'] ) as $area ) {
				$area = sanitize_text_field( $area );
				if ( in_array( $area, $valid_focus_areas, true ) ) {
					$focus_areas[] = $area;
				}
			}
			
			// Enforce maximum of 2 selections.
			$focus_areas = array_slice( $focus_areas, 0, 2 );
		}

		// Save meta fields.
		update_post_meta( $post_id, 'hj_purpose', $purpose );
		update_post_meta( $post_id, 'hj_focus_areas', $focus_areas );
	}
}
