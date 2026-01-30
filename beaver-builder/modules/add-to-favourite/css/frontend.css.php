<?php
/**
 * CSS template for Add to Favourite Module
 * This file generates dynamic CSS based on module settings
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
.fl-node-<?php echo $id; ?> .hj-favourite-btn {
	<?php if ( ! empty( $settings->background_color ) ) : ?>
		background-color: #<?php echo $settings->background_color; ?>;
	<?php endif; ?>
	
	<?php if ( ! empty( $settings->text_color ) ) : ?>
		color: #<?php echo $settings->text_color; ?>;
	<?php endif; ?>
	
	<?php if ( ! empty( $settings->transition_duration ) ) : ?>
		transition-duration: <?php echo $settings->transition_duration; ?>s;
	<?php endif; ?>
	
	<?php
	// Output border styles
	FLBuilderCSS::border_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'border',
		'selector'     => ".fl-node-$id .hj-favourite-btn",
	) );
	
	// Output border radius
	FLBuilderCSS::dimension_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'border_radius',
		'selector'     => ".fl-node-$id .hj-favourite-btn",
		'property'     => 'border-radius',
		'unit'         => 'px',
	) );
	
	// Output padding
	FLBuilderCSS::dimension_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'padding',
		'selector'     => ".fl-node-$id .hj-favourite-btn",
		'property'     => 'padding',
		'unit'         => 'px',
	) );
	
	// Output margin
	FLBuilderCSS::dimension_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'margin',
		'selector'     => ".fl-node-$id .hj-favourite-btn",
		'property'     => 'margin',
		'unit'         => 'px',
	) );
	
	// Output typography
	FLBuilderCSS::typography_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'typography',
		'selector'     => ".fl-node-$id .hj-favourite-btn",
	) );
	
	// Output box shadow
	FLBuilderCSS::shadow_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'box_shadow',
		'selector'     => ".fl-node-$id .hj-favourite-btn",
	) );
	?>
}

<?php if ( ! empty( $settings->background_hover_color ) ) : ?>
.fl-node-<?php echo $id; ?> .hj-favourite-btn:hover {
	background-color: #<?php echo $settings->background_hover_color; ?> !important;
}
<?php endif; ?>

<?php if ( ! empty( $settings->text_hover_color ) ) : ?>
.fl-node-<?php echo $id; ?> .hj-favourite-btn:hover {
	color: #<?php echo $settings->text_hover_color; ?> !important;
}
<?php endif; ?>

<?php
// Output hover box shadow
if ( ! empty( $settings->box_shadow_hover ) ) {
	FLBuilderCSS::shadow_field_rule( array(
		'settings'     => $settings,
		'setting_name' => 'box_shadow_hover',
		'selector'     => ".fl-node-$id .hj-favourite-btn:hover",
	) );
}
?>