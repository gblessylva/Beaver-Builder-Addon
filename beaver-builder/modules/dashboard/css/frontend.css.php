<?php
/**
 * CSS template for Dashboard Module
 *
 * @package HealthyJoint_Goals
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
.fl-node-<?php echo esc_attr( $id ); ?> .hj-dashboard-module {
	<?php if ( ! empty( $settings->background_color ) ) : ?>
		background-color: #<?php echo esc_attr( $settings->background_color ); ?>;
	<?php endif; ?>
}

.fl-node-<?php echo esc_attr( $id ); ?> .hj-goal-card {
	<?php if ( ! empty( $settings->goal_card_bg_color ) ) : ?>
		background-color: #<?php echo esc_attr( $settings->goal_card_bg_color ); ?>;
	<?php endif; ?>
}

.fl-node-<?php echo esc_attr( $id ); ?> .hj-goal-circle {
	<?php if ( ! empty( $settings->circle_color ) ) : ?>
		background-color: #<?php echo esc_attr( $settings->circle_color ); ?>;
	<?php endif; ?>
}

.fl-node-<?php echo esc_attr( $id ); ?> .hj-dashboard-container {
	<?php
	// Output container padding
	FLBuilderCSS::dimension_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'container_padding',
		'selector'     => ".fl-node-$id .hj-dashboard-container",
		'property'     => 'padding',
		'unit'         => 'px',
	) );
	?>
}

<?php if ( ! empty( $settings->section_spacing ) ) : ?>
.fl-node-<?php echo esc_attr( $id ); ?> .hj-dashboard-greeting {
	margin-bottom: <?php echo esc_attr( $settings->section_spacing ); ?>px;
}

.fl-node-<?php echo esc_attr( $id ); ?> .hj-dashboard-content {
	gap: <?php echo esc_attr( $settings->section_spacing ); ?>px;
}
<?php endif; ?>

<?php
// Output greeting typography
FLBuilderCSS::typography_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'greeting_typography',
	'selector'     => ".fl-node-$id .hj-greeting-text",
) );

// Output goal card typography
FLBuilderCSS::typography_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'goal_card_typography',
	'selector'     => ".fl-node-$id .hj-goal-card",
) );
?>