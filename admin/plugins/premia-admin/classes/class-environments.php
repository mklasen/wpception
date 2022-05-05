<?php

namespace Premia_Admin;

class Environments {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'acf/save_post', array( $this, 'save_post' ), 20 );
		add_action( 'trashed_post', array( $this, 'remove_env' ), 20 );
		add_filter( 'acf/load_field/key=field_625fa86ec3f06', array( $this, 'add_details' ) );
		add_filter( 'acf/load_field/key=field_625faaf581eec', array( $this, 'add_manage_buttons' ) );
		add_filter( 'acf/load_field/name=container_ids', array( $this, 'set_read_only' ) );
		add_filter( 'acf/load_field/name=port', array( $this, 'set_read_only' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function register_post_types() {
		register_post_type(
			'environment',
			array(
				'label'  => 'Environments',
				'public' => true,
			)
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'premia-admin-scripts', plugin_dir_url( dirname(__FILE__) ) . 'dist/index.js', array( 'wp-api' ), filemtime( plugin_dir_path( dirname(__FILE__) ) . 'dist/index.js' ) );
	}

	public static function get_container_ids( $post_id ) {
		$container_ids = '';
		if ( function_exists( 'get_field' ) ) {
			$container_ids = get_field( 'container_ids', $post_id );
		}
		return array_filter( explode( ',', $container_ids ) );
	}

	public function save_post( $post_id ) {

		$post = get_post( $post_id );

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_autosave( $post ) || $post->post_status === 'auto-draft' || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$ids = $this->create_environment( $post, true );
	}

	public function create_environment( $post, $start = false ) {

		if ( ! function_exists( 'get_field' ) ) {
			return false;
		}

		$container_ids = get_field( 'container_ids', $post->ID );
		$container_ids = array_filter( explode( ',', $container_ids ) );

		// Get a port.
		$port = $this->get_available_port();
		update_field( 'port', $port );

		$requests = array();

		$requests[] = $this->create_ssh( $post );
		$requests[] = $this->create_database( $post );
		$requests[] = $this->create_wordpress( $post );

		foreach ( $requests as $response ) {
			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			switch ( $code ) {
				case 201:
					$container_ids[] = $data->Id;
					break;
				default:
					break;
			}
		}

		update_field( 'container_ids', implode( ',', $container_ids ), $post->ID );

		foreach ( $container_ids as $id ) {
			$this->docker_request( 'POST', '/containers/' . $id . '/start' );
		}

		return $container_ids;
	}

	public function get_available_port() {
		return $this->get_random_port();
	}

	public function get_random_port() {
		$start = 18000;
		$end   = 18999;

		$used_ports = get_option( 'premia_used_ports' );
		if ( ! is_array( $used_ports ) ) {
			$used_ports = array();
		}

		if ( count( $used_ports ) < 999 ) {
			$port = rand( $start, $end );
			if ( in_array( $port, $used_ports ) ) {
				$port = $this->get_random_port();
			}
		} else {
			$port = 'oops';
		}
		return $port;
	}

	public function get_hostname( $post ) {

		$hostname = $post->post_name;

		$local = false;

		if ( function_exists( 'get_field' ) ) {
			$local = get_field( 'local', $post->ID );
		}

		$domain = get_option( 'premia_domain' );

		if ( true === $local ) {
			$hostname = "{$hostname}.local";
		}

		return "{$hostname}.{$domain}";
	}

	public function create_wordpress( $post ) {

		$hostname = $this->get_hostname( $post );
		$path = get_option( 'premia_dir_path' );

		return $this->docker_request(
			'POST',
			'/containers/create?name=' . $post->post_name . '-wp',
			array(
				'Image'        => 'wordpress',
				'Env'          => array(
					"WORDPRESS_DB_HOST={$post->post_name}-db",
					'WORDPRESS_DB_USER=exampleuser',
					'WORDPRESS_DB_PASSWORD=examplepass',
					'WORDPRESS_DB_NAME=exampledb',
				),
				'Labels'       => array(
					'traefik.enable' => 'true',
					"traefik.http.routers.{$post->post_name}.rule" => "Host(`{$hostname}`)",
					"traefik.http.routers.{$post->post_name}.entrypoints" => 'websecure',
					"traefik.http.routers.{$post->post_name}.tls.certresolver" => 'myresolver',
				),
				'ExposedPorts' => array(
					'8080/tcp' => (object) array(),
				),
				'HostConfig'   => array(
					'NetworkMode' => 'swarm',
					'Binds'       => array(
						"{$path}/users/home/{$post->post_name}/config/web/themes:/var/www/html/wp-content/themes",
						"{$path}/users/home/{$post->post_name}/config/web/plugins:/var/www/html/wp-content/plugins",
						"{$path}/users/home/{$post->post_name}/config/wp:/var/www/html",
					),
				),
			)
		);
	}

	public function create_database( $post ) {
		return $this->docker_request(
			'POST',
			'/containers/create?name=' . $post->post_name . '-db',
			array(
				'Image'      => 'mysql:5.7',
				'Env'        => array(
					'MYSQL_ROOT_PASSWORD=somewordpress',
					'MYSQL_DATABASE=exampledb',
					'MYSQL_USER=exampleuser',
					'MYSQL_PASSWORD=examplepass',
				),
				'HostConfig' => array(
					'NetworkMode' => 'swarm',
				),
			)
		);
	}

	public function create_ssh( $post ) {

		if ( ! function_exists( 'get_field' ) ) {
			return false;
		}
		$port = get_field( 'port', $post->ID );
		$path = get_option( 'premia_dir_path' );

		return $this->docker_request(
			'POST',
			'/containers/create?name=' . $post->post_name . '-ssh',
			array(
				'Image'        => 'lscr.io/linuxserver/openssh-server',
				'Env'          => array(
					'PUID=33',
					'PGID=33',
					'TZ=Europe/Amsterdam',
					'SUDO_ACCESS=true',
					'PASSWORD_ACCESS=true',
					"USER_PASSWORD={$post->post_name}",
					"USER_NAME={$post->post_name}",
				),
				'Labels'       => array(
					'traefik.enable'                    => 'true',
					"traefik.tcp.routers.{$post->post_name}-ssh.rule" => 'HostSNI(`*`)',
					"traefik.tcp.routers.{$post->post_name}-ssh.entrypoints" => 'ssh',
					'traefik.tcp.routers.users.service' => 'users-service',
					'traefik.tcp.services.users-service.loadbalancer.server.port' => '2222',
				),
				'HostConfig'   => array(
					'NetworkMode'  => 'swarm',
					'PortBindings' => array(
						'2222/tcp' => array(
							array(
								'HostPort' => $port,
							),
						),
					),
					'Binds'        => array(
						"{$path}/users/defaults/custom-cont-init.d:/config/custom-cont-init.d",
						"{$path}/users/defaults/.profile:/config/.profile",
						"{$path}/users/home/{$post->post_name}/config:/config",
						"{$path}/users/welcome.txt:/etc/motd",
						"{$path}/users/defaults/ssh_config:/config/ssh_host_keys/sshd_config",
					),
				),
				'ExposedPorts' => array(
					"{$port}/tcp" => (object) array(),
				),
			)
		);
	}

	public static function docker_request( $action, $path, $args = array() ) {

		$default_args = array();

		$args = array_merge( $default_args, $args );

		$api_url = get_option( 'premia_api_url' );

		$request = wp_remote_post(
			$api_url . $path,
			array(
				'body'    => wp_json_encode( $args ),
				'method'  => $action,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			)
		);

		return $request;
	}

	public function add_details( $field ) {

		if ( ! function_exists( 'get_field' ) ) {
			echo '<p>Activate required plugins first.</p>';
		}

		$field['disabled'] = true;

		$post_id = get_the_ID();
		$post    = get_post( $post_id );

		$hostname      = $this->get_hostname( $post );
		$container_ids = $this->get_container_ids( $post_id );
		$port          = get_field( 'port', $post_id );

		if ( ! empty( $container_ids ) ) {

			ob_start();

			echo '<table>';
			echo "<tr><td>Host</td><td>{$hostname}</td></tr>";
			echo "<tr><td>Username</td><td>{$post->post_name}</td></tr>";
			echo "<tr><td>Password</td><td>{$post->post_name}</td></tr>";
			echo "<tr><td>Port</td><td>{$port}</td></tr>";
			echo '</table>';

			echo "<p>SSH: <i>ssh {$post->post_name}@{$hostname} -p{$port}</i></p>";

			echo '<p><a class="button button-primary button-large" target="_blank" href="https://' . $hostname . '">Visit environment</a></p>';

			$field['message'] = ob_get_clean();
		} else {
			$field['message'] = '<p><i>Environment has not been created yet.</i>';
		}

		return $field;
	}

	public function add_manage_buttons( $field ) {

		$field['disabled'] = true;

		$post_id       = get_the_ID();
		$container_ids = $this->get_container_ids( $post_id );

		if ( ! function_exists( 'get_field' ) ) {
			return false;
		}

		$port = get_field( 'port', $post_id );

		if ( is_array( $container_ids ) && ! empty( $container_ids ) ) {
			ob_start();
			?>
			<div class="premia-container-control">
				<p>
					<button class="button" target="_blank" data-id="<?php echo $post_id; ?>" data-action="start">Start containers</button>
					<button class="button" target="_blank" data-id="<?php echo $post_id; ?>" data-action="stop">Stop containers</button>
				</p>
			</div>
			<?php
			$field['message'] = ob_get_clean();
		} else {
			$field['message'] = '<p><i>Environment has not been created yet.</i>';
		}

		return $field;
	}

	public function set_read_only( $field ) {
		$field['readonly'] = true;
		return $field;
	}


	public function remove_env( $post_id ) {
		$container_ids = $this->get_container_ids( $post_id );
		if ( is_array( $container_ids ) ) {
			foreach ( $container_ids as $id ) {
				$response = $this->docker_request( 'DELETE', '/containers/' . $id . '?force=true' );

				$code = wp_remote_retrieve_response_code( $response );
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body );
			}
		}

		update_field( 'container_ids', '', $post_id );
	}
}
