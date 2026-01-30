<?php
/**
 * Frontend template for Add to Favourite Module
 *
 * @package HealthyJoint_Goals
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prepare button attributes
$button_id = ! empty( $settings->button_id ) ? 'id="' . esc_attr( $settings->button_id ) . '"' : '';
$button_class = 'hj-favourite-btn';
if ( ! empty( $settings->button_class ) ) {
	$button_class .= ' ' . esc_attr( $settings->button_class );
}

// Parse data attributes
$data_attributes = '';
if ( ! empty( $settings->data_attributes ) ) {
	$lines = explode( "\n", $settings->data_attributes );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( ! empty( $line ) && strpos( $line, '=' ) !== false ) {
			$parts = explode( '=', $line, 2 );
			$key = trim( $parts[0] );
			$value = trim( $parts[1], '"\'');
			if ( ! empty( $key ) && ! empty( $value ) ) {
				$data_attributes .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}
	}
}

// Prepare link attributes
$link_url = '';
$link_target = '';
$onclick = '';

if ( $settings->link_type === 'url' && ! empty( $settings->link_url ) ) {
	$link_url = 'href="' . esc_url( $settings->link_url ) . '"';
	$link_target = ! empty( $settings->link_target ) ? 'target="' . esc_attr( $settings->link_target ) . '"' : '';
	if ( ! empty( $settings->link_url_nofollow ) && $settings->link_url_nofollow === 'yes' ) {
		$link_target .= ' rel="nofollow"';
	}
} elseif ( $settings->link_type === 'javascript' && ! empty( $settings->javascript_action ) ) {
	$link_url = 'href="javascript:void(0);"';
	$onclick = 'onclick="' . esc_attr( $settings->javascript_action ) . '"';
}

// Prepare icon
$icon_html = '';
if ( ! empty( $settings->button_icon ) ) {
	$icon_html = '<i class="' . esc_attr( $settings->button_icon ) . ' hj-btn-icon"></i>';
}

// Button text
$button_text = ! empty( $settings->button_text ) ? '<span class="hj-btn-text">' . esc_html( $settings->button_text ) . '</span>' : '';

// Arrange icon and text based on position
$button_content = '';
if ( $settings->icon_position === 'before' ) {
	$button_content = $icon_html . $button_text;
} else {
	$button_content = $button_text . $icon_html;
}
?>

<div class="hj-favourite-module <?php echo esc_attr( $id ); ?>">
	<?php if ( $settings->link_type === 'url' || $settings->link_type === 'javascript' ) : ?>
		<a 
			<?php echo $button_id; ?>
			class="<?php echo esc_attr( $button_class ); ?>"
			<?php echo $link_url; ?>
			<?php echo $link_target; ?>
			<?php echo $onclick; ?>
			<?php echo $data_attributes; ?>
		>
			<?php echo $button_content; ?>
		</a>
	<?php else : ?>
		<button 
			<?php echo $button_id; ?>
			class="<?php echo esc_attr( $button_class ); ?>"
			type="button"
			<?php echo $data_attributes; ?>
		>
			<?php echo $button_content; ?>
		</button>
	<?php endif; ?>
</div>