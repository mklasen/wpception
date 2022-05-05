<?php

namespace Premia_Admin;

class Settings {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 9 );
		add_action( 'tgmpa_register', array( $this, 'require_plugins' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		add_settings_section(
			'premia_settings_general',
			'General settings',
			array( $this, 'general_settings_intro' ),
			'premia-settings'
		);

		$this->add_settings_field( 'premia_api_url', 'API URL', 'premia_settings_general' );
		$this->add_settings_field( 'premia_domain', 'Domain', 'premia_settings_general' );
		$this->add_settings_field( 'premia_dir_path', 'Directory Path', 'premia_settings_general' );
	}

	public function add_settings_field( $name, $label, $section ) {
		register_setting(
			'premia_settings',
			$name,
			'sanitize_text_field',
		);

		add_settings_field(
			$name,
			$label,
			array( $this, 'render_field' ),
			'premia-settings',
			$section,
			array(
				'name'        => $name,
				'option_name' => $name,
				'class'       => 'premia-field',
			)
		);
	}

	public function general_settings_intro() {
		echo wpautop( 'Enter the API and domain configuration.' );
	}

	public function render_field( $field ) {
		$text = get_option( $field['option_name'] );

		printf(
			'<input type="text" id="' . $field['option_name'] . '" name="' . $field['name'] . '" value="%s" />',
			esc_attr( $text )
		);
	}

	public function add_admin_menu() {
		add_menu_page( 'Premia', 'Premia', 'manage_options', 'premia-settings', array( $this, 'admin_menu_content' ), 'dashicons-admin-multisite', 79 );
	}

	public function admin_menu_content() {
		echo '<div class="wrap">
			<h1>Premia Settings</h1>
			<form method="post" action="options.php">';

		settings_fields( 'premia_settings' );
		do_settings_sections( 'premia-settings' );
		submit_button();

		echo '</form></div>';
	}

	public function require_plugins() {
		$plugins = array(
			array(
				'name' => 'Advanced Custom Fields',
				'slug' => 'advanced-custom-fields',
			),
		);

		$config = array(
			'id'           => 'tgmpa-premia',
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'parent_slug'  => 'themes.php',           
			'capability'   => 'edit_theme_options',   
			'has_notices'  => true,                   
			'dismissable'  => true,                   
			'dismiss_msg'  => '',                     
			'is_automatic' => false,                  
			'message'      => '',                     
		);

		tgmpa( $plugins, $config );
	}
}