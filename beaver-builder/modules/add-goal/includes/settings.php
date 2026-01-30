<?php
/**
 * Module Settings for Add Goal Module
 */

FLBuilder::register_settings_form(
	'hj_add_goal_form',
	array(
		'title' => __( 'Goal Form Settings', 'healthyjoint-goals' ),
		'tabs'  => array(
			'general' => array(
				'title'    => __( 'General', 'healthyjoint-goals' ),
				'sections' => array(
					'general' => array(
						'title'  => '',
						'fields' => array(
							'title' => array(
								'type'        => 'text',
								'label'       => __( 'Form Title', 'healthyjoint-goals' ),
								'default'     => __( 'Change your goal', 'healthyjoint-goals' ),
								'preview'     => array(
									'type'     => 'text',
									'selector' => '.hj-form-title',
								),
							),
							'subtitle' => array(
								'type'    => 'textarea',
								'label'   => __( 'Form Subtitle', 'healthyjoint-goals' ),
								'default' => __( 'Set a new goal by following the same process as when you started.', 'healthyjoint-goals' ),
								'rows'    => 3,
								'preview' => array(
									'type'     => 'text',
									'selector' => '.hj-form-subtitle',
								),
							),
							'redirect_url' => array(
								'type'        => 'link',
								'label'       => __( 'Success Redirect URL', 'healthyjoint-goals' ),
								'placeholder' => __( '/dashboard', 'healthyjoint-goals' ),
								'preview'     => array(
									'type' => 'none',
								),
							),
						),
					),
				),
			),
			'style'   => array(
				'title'    => __( 'Style', 'healthyjoint-goals' ),
				'sections' => array(
					'colors' => array(
						'title'  => __( 'Colors', 'healthyjoint-goals' ),
						'fields' => array(
							'primary_color' => array(
								'type'       => 'color',
								'label'      => __( 'Primary Color', 'healthyjoint-goals' ),
								'default'    => '007cba',
								'show_reset' => true,
								'preview'    => array(
									'type'     => 'css',
									'selector' => '.hj-add-goal-form .hj-btn-primary',
									'property' => 'background-color',
								),
							),
							'secondary_color' => array(
								'type'       => 'color',
								'label'      => __( 'Secondary Color', 'healthyjoint-goals' ),
								'default'    => 'f0f0f0',
								'show_reset' => true,
								'preview'    => array(
									'type'     => 'css',
									'selector' => '.hj-add-goal-form .hj-btn-secondary',
									'property' => 'background-color',
								),
							),
						),
					),
				),
			),
		),
	)
);