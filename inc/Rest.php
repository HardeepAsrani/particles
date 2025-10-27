<?php
/**
 * Rest Class.
 *
 * @package Codeinwp\Particles
 */

namespace ThemeIsle\Particles;

use ThemeIsle\Particles\OpenAI;

/**
 * Class Rest
 */
class Rest {
	/**
	 * API namespace.
	 *
	 * @var string
	 */
	private static $namespace = 'particles';

	/**
	 * API version.
	 *
	 * @var string
	 */
	private static $version = 'v1';

	/**
	 * Rest constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Get endpoint.
	 * 
	 * @return string
	 */
	public static function get_endpoint() {
		return self::$namespace . '/' . self::$version;
	}
	/**
	 * Register REST API route
	 *
	 * @return void
	 */
	public function register_routes() {
		$namespace = self::get_endpoint();

		$routes = [
			'chat' => [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'args'                => [
						'request_id' => [
							'required' => true,
							'type'     => 'string',
						],
					],
					'callback'            => [ $this, 'get_request' ],
					'permission_callback' => function ( $request ) {
						$nonce = $request->get_header( 'x_wp_nonce' );
						return wp_verify_nonce( $nonce, 'wp_rest' );
					},
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'args'                => [
                        'type'         => [
                            'required' => true,
                            'type'     => 'string',
                        ],
						'message'      => [
							'required' => true,
							'type'     => 'string',
						],
						'conversation' => [
							'required' => false,
							'type'     => 'string',
						],
					],
					'callback'            => [ $this, 'send_request' ],
					'permission_callback' => function ( $request ) {
						$nonce = $request->get_header( 'x_wp_nonce' );
						return wp_verify_nonce( $nonce, 'wp_rest' );
					},
				],
			],
		];

		foreach ( $routes as $route => $args ) {
			foreach ( $args as $key => $arg ) {
				if ( ! isset( $args[ $key ]['permission_callback'] ) ) {
					$args[ $key ]['permission_callback'] = function () {
						return current_user_can( 'manage_options' );
					};
				}
			}

			register_rest_route( $namespace, '/' . $route, $args );
		}
	}

	/**
	 * Get request.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_request( $request ) {
		$request_id = $request->get_param( 'request_id' );
		$openai     = new OpenAI();
		$response   = $openai->get_response( $request_id );

		if ( is_wp_error( $response ) ) {
			return rest_ensure_response( [ 'error' => $this->get_error_message( $response ) ] );
		}

		if ( 'completed' !== $response->status ) {
			return rest_ensure_response( [ 'status' => $response->status ] );
		}

		$status = $response->status;

		$message = array_filter(
			$response->output,
			function ( $message ) {
				return (
					isset( $message->type, $message->status, $message->role ) &&
					'message' === $message->type &&
					'completed' === $message->status &&
					'assistant' === $message->role
				);
			}
		);

		if ( empty( $message ) ) {
			return rest_ensure_response( [ 'error' => __( 'No messages found.', 'particles' ) ] );
		}

		$message = reset( $message )->content[0]->text;
		$message = json_decode( $message, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return rest_ensure_response( [ 'error' => __( 'No messages found.', 'particles' ) ] );
		}


		return rest_ensure_response(
			[
				'status'  => $status,
				'message' => $message,
			]
		);
	}

	/**
	 * Send request.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function send_request( $request ) {
        $type    = $request->get_param( 'type' );
		$message = $request->get_param( 'message' );
		$openai  = new OpenAI();

		if ( $request->get_param( 'conversation' ) ) {
			$conversation = $request->get_param( 'conversation' );
		} else {
			$conversation = $openai->create_conversation();
		}

		if ( is_wp_error( $conversation ) ) {
			return rest_ensure_response( [ 'error' => $this->get_error_message( $conversation ) ] );
		}

		$params = [];

		if ( 'planning' === $type ) {
			$params['user_input'] = $message;
		}

		if ( 'generation' === $type ) {
			$params['outline'] = $message;
		}

		$query_run = $openai->create_response(
			$type,
			$conversation,
			$params
		);

		if ( is_wp_error( $query_run ) ) {
			if ( strpos( $this->get_error_message( $query_run ), 'Conversation with id' ) !== false ) {
				$thread_id = $openai->create_conversation();

				if ( is_wp_error( $thread_id ) ) {
					return rest_ensure_response( [ 'error' => $this->get_error_message( $thread_id ) ] );
				}

				$query_run = $openai->create_response(
					'planning',
					$conversation,
					[
						'user_input' => $message,
					]
				);

				if ( is_wp_error( $query_run ) ) {
					return rest_ensure_response( [ 'error' => $this->get_error_message( $query_run ) ] );
				}
			}
		}


		return rest_ensure_response(
			[
				'conversation' => $conversation,
				'response'     => $query_run,
			]
		);
	}

	/**
	 * Get Error Message.
	 * 
	 * @param \WP_Error $error Error.
	 * 
	 * @return string
	 */
	public function get_error_message( $error ) {
		$errors = [
			'invalid_api_key' => __( 'Incorrect API key provided.', 'particles' ),
			'missing_scope'   => __( 'You have insufficient permissions for this operation.', 'particles' ),
		];
		if ( isset( $errors[ $error->get_error_code() ] ) ) {
			return $errors[ $error->get_error_code() ];
		}

		return $error->get_error_message();
	}
}
