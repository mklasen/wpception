<?php

namespace Premia_Admin;

class Rest {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	public function register_endpoints() {
		register_rest_route(
			'premia/v1',
			'/container',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'manage_env' ),
			)
		);
	}

	public function manage_env( $request ) {
		$params  = $request->get_json_params();
		$action  = $params['action'];
		$post_id = $params['id'];

		$container_ids = Environments::get_container_ids( $post_id );

		$responses = array();

		if ( is_array( $container_ids ) ) {
			foreach ( $container_ids as $id ) {
				$request     = Environments::docker_request( 'POST', '/containers/' . $id . '/' . $action );
				$responses[] = $request;
			}
		}

		return $responses;
	}
}