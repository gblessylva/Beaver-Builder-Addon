<?php
/**
 * Checkin Meta Box class.
 *
 * @package HealthyJointGoals
 */

namespace HealthyJointGoals\Admin\MetaBoxes;

use HealthyJointGoals\Interfaces\Registrable;
use HealthyJointGoals\Services\CheckinService;
use HealthyJointGoals\PostTypes\CheckinPostType;
use HealthyJointGoals\PostTypes\GoalPostType;

/**
 * Checkin Meta Box class.
 *
 * Handles the checkin-specific meta boxes in the admin.
 * Follows Single Responsibility and Dependency Inversion principles.
 */
class CheckinMetaBox implements Registrable {
	/**
	 * Checkin service instance.
	 *
	 * @var CheckinService
	 */
	private $checkin_service;

	/**
	 * Constructor.
	 *
	 * @param CheckinService $checkin_service Checkin service dependency.
	 */
	public function __construct( CheckinService $checkin_service ) {
		$this->checkin_service = $checkin_service;
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
			'hj_checkin_details',
			__( 'Check-in Details', 'healthyjoint-goals' ),
			array( $this, 'render_checkin_details_meta_box' ),
			CheckinPostType::get_post_type(),
			'normal',
			'high'
		);
	}

	/**
	 * Render checkin details meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_checkin_details_meta_box( $post ) {
		// Security nonce.
		wp_nonce_field( 'hj_checkin_meta_box', 'hj_checkin_meta_box_nonce' );

		// Get current values.
		$checkin_date = get_post_meta( $post->ID, 'hj_checkin_date', true );
		$ratings      = get_post_meta( $post->ID, 'hj_ratings', true );
		
		// Ensure ratings is an array.
		if ( ! is_array( $ratings ) ) {
			$ratings = array();
		}

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="hj_checkin_date"><?php esc_html_e( 'Check-in Date', 'healthyjoint-goals' ); ?></label>
				</th>
				<td>
					<input type="date" id="hj_checkin_date" name="hj_checkin_date" value="<?php echo esc_attr( $checkin_date ? $checkin_date : gmdate( 'Y-m-d' ) ); ?>" class="regular-text" required />
					<p class="description"><?php esc_html_e( 'Date of this check-in (required).', 'healthyjoint-goals' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Ratings', 'healthyjoint-goals' ); ?></label>
				</th>
				<td>
					<?php foreach ( CheckinPostType::get_rating_types() as $type => $label ) : ?>
						<div class="rating-field">
							<label for="hj_rating_<?php echo esc_attr( $type ); ?>">
								<strong><?php echo esc_html( $label ); ?></strong>
							</label>
							<select id="hj_rating_<?php echo esc_attr( $type ); ?>" name="hj_ratings[<?php echo esc_attr( $type ); ?>]" class="regular-text">
								<option value=""><?php esc_html_e( 'No rating', 'healthyjoint-goals' ); ?></option>
								<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
									<option value="<?php echo esc_attr( $i ); ?>" <?php selected( isset( $ratings[ $type ] ) ? $ratings[ $type ] : '', $i ); ?>>
										<?php echo esc_html( $i ); ?>
									</option>
								<?php endfor; ?>
							</select>
						</div>
						<br>
					<?php endforeach; ?>
					<p class="description"><?php esc_html_e( 'Rate different aspects on a scale of 1-5 (optional).', 'healthyjoint-goals' ); ?></p>
				</td>
			</tr>
		</table>
		
		<div class="hj-checkin-notes">
			<h4><?php esc_html_e( 'Notes', 'healthyjoint-goals' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Use the main content editor above to add notes about this check-in.', 'healthyjoint-goals' ); ?></p>
		</div>
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
		if ( ! isset( $_POST['hj_checkin_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( wp_unslash( $_POST['hj_checkin_meta_box_nonce'] ), 'hj_checkin_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( get_post_type( $post_id ) !== CheckinPostType::get_post_type() ) {
			return;
		}

		// Sanitize and save checkin date.
		$checkin_date = sanitize_text_field( wp_unslash( $_POST['hj_checkin_date'] ?? '' ) );
		update_post_meta( $post_id, 'hj_checkin_date', $checkin_date );

		// Sanitize and save ratings.
		$ratings = array();
		if ( isset( $_POST['hj_ratings'] ) && is_array( $_POST['hj_ratings'] ) ) {
			$valid_rating_types = array_keys( CheckinPostType::get_rating_types() );
			
			foreach ( wp_unslash( $_POST['hj_ratings'] ) as $type => $rating ) {
				$type   = sanitize_text_field( $type );
				$rating = intval( $rating );
				
				// Validate rating type and value.
				if ( in_array( $type, $valid_rating_types, true ) && $rating >= 1 && $rating <= 5 ) {
					$ratings[ $type ] = $rating;
				}
			}
		}
		
		update_post_meta( $post_id, 'hj_ratings', $ratings );
	}
}
