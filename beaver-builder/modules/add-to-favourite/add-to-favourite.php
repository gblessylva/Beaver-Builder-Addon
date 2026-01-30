<?php
/**
 * Add to Favourite Button Module for Beaver Builder
 *
 * @package HealthyJoint_Goals
 */

/**
 * Add to Favourite Button Module Class
 */
class HJAddToFavouriteModule extends FLBuilderModule {

	/**
	 * Constructor method for the class.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'Add to Favourite', 'healthyjoint-goals' ),
				'description'     => __( 'A customizable button for adding items to favourites', 'healthyjoint-goals' ),
				'category'        => __( 'HealthyJoint Goals', 'healthyjoint-goals' ),
				'dir'             => plugin_dir_path( __FILE__ ),
				'url'             => plugin_dir_url( __FILE__ ),
				'editor_export'   => true,
				'enabled'         => true,
				'partial_refresh' => true,
			)
		);
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'HJAddToFavouriteModule',
	array(
		'general'  => array(
			'title'    => __( 'General', 'healthyjoint-goals' ),
			'sections' => array(
				'content' => array(
					'title'  => __( 'Content', 'healthyjoint-goals' ),
					'fields' => array(
						'button_text'   => array(
							'type'    => 'text',
							'label'   => __( 'Button Text', 'healthyjoint-goals' ),
							'default' => __( 'Add to Favourite', 'healthyjoint-goals' ),
							'preview' => array(
								'type'     => 'text',
								'selector' => '.hj-favourite-btn .hj-btn-text',
							),
						),
						'button_icon'   => array(
							'type'        => 'icon',
							'label'       => __( 'Button Icon', 'healthyjoint-goals' ),
							'show_remove' => true,
							'default'     => 'fas fa-heart',
						),
						'icon_position' => array(
							'type'    => 'select',
							'label'   => __( 'Icon Position', 'healthyjoint-goals' ),
							'default' => 'before',
							'options' => array(
								'before' => __( 'Before Text', 'healthyjoint-goals' ),
								'after'  => __( 'After Text', 'healthyjoint-goals' ),
							),
						),
					),
				),
				'link'    => array(
					'title'  => __( 'Link', 'healthyjoint-goals' ),
					'fields' => array(
						'link_type'         => array(
							'type'    => 'select',
							'label'   => __( 'Link Type', 'healthyjoint-goals' ),
							'default' => 'url',
							'options' => array(
								'url'        => __( 'URL', 'healthyjoint-goals' ),
								'javascript' => __( 'JavaScript Action', 'healthyjoint-goals' ),
							),
							'toggle'  => array(
								'url'        => array(
									'fields' => array( 'link_url', 'link_target' ),
								),
								'javascript' => array(
									'fields' => array( 'javascript_action' ),
								),
							),
						),
						'link_url'          => array(
							'type'          => 'link',
							'label'         => __( 'Link URL', 'healthyjoint-goals' ),
							'show_target'   => true,
							'show_nofollow' => true,
						),
						'link_target'       => array(
							'type'    => 'select',
							'label'   => __( 'Link Target', 'healthyjoint-goals' ),
							'default' => '_self',
							'options' => array(
								'_self'  => __( 'Same Window', 'healthyjoint-goals' ),
								'_blank' => __( 'New Window', 'healthyjoint-goals' ),
							),
						),
						'javascript_action' => array(
							'type'        => 'textarea',
							'label'       => __( 'JavaScript Action', 'healthyjoint-goals' ),
							'rows'        => 4,
							'description' => __( 'JavaScript code to execute when button is clicked. Do not include script tags.', 'healthyjoint-goals' ),
							'placeholder' => 'alert("Added to favourites!");',
						),
					),
				),
			),
		),
		'style'    => array(
			'title'    => __( 'Style', 'healthyjoint-goals' ),
			'sections' => array(
				'colors'     => array(
					'title'  => __( 'Colors', 'healthyjoint-goals' ),
					'fields' => array(
						'background_color'       => array(
							'type'       => 'color',
							'label'      => __( 'Background Color', 'healthyjoint-goals' ),
							'default'    => 'e74c3c',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
								'property' => 'background-color',
							),
						),
						'background_hover_color' => array(
							'type'       => 'color',
							'label'      => __( 'Background Hover Color', 'healthyjoint-goals' ),
							'default'    => 'c0392b',
							'show_reset' => true,
							'show_alpha' => true,
						),
						'text_color'             => array(
							'type'       => 'color',
							'label'      => __( 'Text Color', 'healthyjoint-goals' ),
							'default'    => 'ffffff',
							'show_reset' => true,
							'show_alpha' => true,
							'preview'    => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
								'property' => 'color',
							),
						),
						'text_hover_color'       => array(
							'type'       => 'color',
							'label'      => __( 'Text Hover Color', 'healthyjoint-goals' ),
							'default'    => 'ffffff',
							'show_reset' => true,
							'show_alpha' => true,
						),
					),
				),
				'spacing'    => array(
					'title'  => __( 'Spacing', 'healthyjoint-goals' ),
					'fields' => array(
						'padding' => array(
							'type'    => 'dimension',
							'label'   => __( 'Padding', 'healthyjoint-goals' ),
							'slider'  => true,
							'units'   => array( 'px', 'em', 'rem', '%' ),
							'default' => array(
								'top'    => '12',
								'right'  => '24',
								'bottom' => '12',
								'left'   => '24',
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
								'property' => 'padding',
							),
						),
						'margin'  => array(
							'type'    => 'dimension',
							'label'   => __( 'Margin', 'healthyjoint-goals' ),
							'slider'  => true,
							'units'   => array( 'px', 'em', 'rem', '%' ),
							'default' => array(
								'top'    => '0',
								'right'  => '0',
								'bottom' => '0',
								'left'   => '0',
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
								'property' => 'margin',
							),
						),
					),
				),
				'border'     => array(
					'title'  => __( 'Border', 'healthyjoint-goals' ),
					'fields' => array(
						'border'        => array(
							'type'    => 'border',
							'label'   => __( 'Border', 'healthyjoint-goals' ),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
							),
						),
						'border_radius' => array(
							'type'    => 'dimension',
							'label'   => __( 'Border Radius', 'healthyjoint-goals' ),
							'slider'  => true,
							'units'   => array( 'px', 'em', 'rem', '%' ),
							'default' => array(
								'top'    => '6',
								'right'  => '6',
								'bottom' => '6',
								'left'   => '6',
							),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
								'property' => 'border-radius',
							),
						),
					),
				),
				'typography' => array(
					'title'  => __( 'Typography', 'healthyjoint-goals' ),
					'fields' => array(
						'typography' => array(
							'type'    => 'typography',
							'label'   => __( 'Typography', 'healthyjoint-goals' ),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
							),
						),
					),
				),
				'effects'    => array(
					'title'  => __( 'Effects', 'healthyjoint-goals' ),
					'fields' => array(
						'transition_duration' => array(
							'type'    => 'unit',
							'label'   => __( 'Transition Duration', 'healthyjoint-goals' ),
							'default' => '0.3',
							'units'   => array( 's' ),
							'slider'  => array(
								'min'  => 0,
								'max'  => 2,
								'step' => 0.1,
							),
						),
						'box_shadow'          => array(
							'type'    => 'shadow',
							'label'   => __( 'Box Shadow', 'healthyjoint-goals' ),
							'preview' => array(
								'type'     => 'css',
								'selector' => '.hj-favourite-btn',
								'property' => 'box-shadow',
							),
						),
						'box_shadow_hover'    => array(
							'type'  => 'shadow',
							'label' => __( 'Box Shadow Hover', 'healthyjoint-goals' ),
						),
					),
				),
			),
		),
		'advanced' => array(
			'title'    => __( 'Advanced', 'healthyjoint-goals' ),
			'sections' => array(
				'attributes' => array(
					'title'  => __( 'HTML Attributes', 'healthyjoint-goals' ),
					'fields' => array(
						'button_id'       => array(
							'type'  => 'text',
							'label' => __( 'Button ID', 'healthyjoint-goals' ),
							'help'  => __( 'A unique ID for the button element.', 'healthyjoint-goals' ),
						),
						'button_class'    => array(
							'type'  => 'text',
							'label' => __( 'Button Class', 'healthyjoint-goals' ),
							'help'  => __( 'Additional CSS classes for the button.', 'healthyjoint-goals' ),
						),
						'data_attributes' => array(
							'type'        => 'textarea',
							'label'       => __( 'Data Attributes', 'healthyjoint-goals' ),
							'rows'        => 4,
							'help'        => __( 'Additional data attributes in key="value" format, one per line.', 'healthyjoint-goals' ),
							'placeholder' => 'data-item-id="123"' . "\n" . 'data-category="products"',
						),
					),
				),
			),
		),
	)
);
