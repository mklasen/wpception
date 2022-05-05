<?php

namespace Premia_Admin;

class ACF_Fields {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register_fields() {
		if ( function_exists( 'acf_add_local_field_group' ) ) :

			acf_add_local_field_group(
				array(
					'key'                   => 'group_625c2507c67d8',
					'title'                 => 'Environment details',
					'fields'                => array(
						// array(
						// 	'key'               => 'field_625fa6db5b326',
						// 	'label'             => 'Local',
						// 	'name'              => 'local',
						// 	'type'              => 'true_false',
						// 	'instructions'      => '',
						// 	'required'          => 0,
						// 	'conditional_logic' => 0,
						// 	'wrapper'           => array(
						// 		'width' => '',
						// 		'class' => '',
						// 		'id'    => '',
						// 	),
						// 	'message'           => '',
						// 	'default_value'     => 0,
						// 	'ui'                => 1,
						// 	'ui_on_text'        => '',
						// 	'ui_off_text'       => '',
						// ),
						array(
							'key'               => 'field_625faaf581eec',
							'label'             => 'Manage container',
							'name'              => '',
							'type'              => 'message',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'message'           => '<p><i>Environment has not been created yet.</i>',
							'new_lines'         => '',
							'esc_html'          => 0,
							'disabled'          => true,
						),
						array(
							'key'               => 'field_625fa86ec3f06',
							'label'             => 'Access details',
							'name'              => '',
							'type'              => 'message',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'message'           => '<p><i>Environment has not been created yet.</i>',
							'new_lines'         => '',
							'esc_html'          => 0,
							'disabled'          => true,
						),
						array(
							'key'               => 'field_625c250aeb105',
							'label'             => 'Container ids',
							'name'              => 'container_ids',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
							'readonly'          => true,
						),
						array(
							'key'               => 'field_625d651426dda',
							'label'             => 'Port',
							'name'              => 'port',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
							'readonly'          => true,
						),
					),
					'location'              => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => 'environment',
							),
						),
					),
					'menu_order'            => 0,
					'position'              => 'normal',
					'style'                 => 'default',
					'label_placement'       => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen'        => '',
					'active'                => true,
					'description'           => '',
					'show_in_rest'          => 0,
				)
			);

			endif;
	}
}