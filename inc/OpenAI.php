<?php
/**
 * OpenAI class.
 * 
 * @package Codeinwp/Particles
 */

namespace ThemeIsle\Particles;

use ThemeIsle\Particles\Prompts;

/**
 * OpenAI class.
 */
class OpenAI {

	/**
	 * Base URL.
	 * 
	 * @var string
	 */
	private static $base_url = 'https://api.openai.com/v1/';

	/**
	 * Chat Model.
	 * 
	 * @var string
	 */
	private $chat_model = 'gpt-4o-mini';

	/**
	 * API Key.
	 * 
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->api_key = PARTICLES_API_KEY;
	}

	/**
	 * Create Request.
	 * 
	 * @param string               $endpoint Endpoint.
	 * @param array<string, mixed> $params   Parameters.
	 * @param string               $method   Method.
	 * 
	 * @return mixed
	 */
	private function request( $endpoint, $params = [], $method = 'POST' ) {
		if ( ! $this->api_key ) {
			return (object) [
				'error'   => true,
				'message' => 'API key is missing.',
			];
		}

		$body = wp_json_encode( $params );

		if ( false === $body ) {
			return (object) [
				'error'   => true,
				'message' => 'Invalid params.',
			];
		}

		$response = '';

		if ( 'POST' === $method ) {
			$response = wp_remote_post(
				self::$base_url . $endpoint,
				[
					'headers'     => [
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $this->api_key,
					],
					'body'        => $body,
					'method'      => 'POST',
					'data_format' => 'body',
				]
			);
		}

		if ( 'GET' === $method ) {
			$url  = self::$base_url . $endpoint;
			$args = [
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->api_key,
				],
			];

			if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
				$response = vip_safe_wp_remote_get( $url, '', 3, 1, 20, $args );
			} else {
				$response = wp_remote_get( $url, $args ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
			}
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body );

			if ( isset( $body->error ) ) {
				if ( isset( $body->error->message ) ) {
					return new \WP_Error( isset( $body->error->code ) ? $body->error->code : 'unknown_error', $body->error->message );
				}
				return new \WP_Error( 'unknown_error', __( 'An error occurred while processing the request.', 'particles' ) );
			}

			return $body;
		}
	}

	/**
	 * Moderation Thresholds.
	 * 
	 * @var array<string, int>
	 */
	public function get_moderation_thresholds() {
		return [
			'sexual'                 => 80,
			'hate'                   => 70,
			'harassment'             => 70,
			'self-harm'              => 50,
			'sexual/minors'          => 50,
			'hate/threatening'       => 60,
			'violence/graphic'       => 80,
			'self-harm/intent'       => 50,
			'self-harm/instructions' => 50,
			'harassment/threatening' => 60,
			'violence'               => 70,
		];
	}

	/**
	 * Create Moderation Request.
	 * 
	 * @param string $message Message.
	 * 
	 * @return true|object{flagged: bool, categories: array<string, bool>, category_scores: array<string, float>, category_applied_input_types: array<string, string[]>}|\WP_Error Moderation result or error.
	 */
	public function moderate( $message ) {
		$response = $this->request(
			'moderations',
			[
				'input' => $message,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response->results ) ) {
			$result = reset( $response->results );

			if ( isset( $result->flagged ) && $result->flagged ) {
				/**
				 * Moderation result or error.
				 * 
				 * @var object{flagged: bool, categories: array<string, bool>, category_scores: array<string, float>, category_applied_input_types: array<string, string[]>} $result */
				return $result;
			}
		}

		return true;
	}

	/**
	 * Create a Conversation.
	 * 
	 * @param array<string, mixed> $params Parameters.
	 * 
	 * @return string|\WP_Error
	 */
	public function create_conversation( $params = [] ) {
		$response = $this->request(
			'conversations',
			$params
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response->id ) ) {
			return $response->id;
		}

		return new \WP_Error( 'unknown_error', __( 'An error occurred while creating the conversation.', 'particles' ) );
	}

	/**
	 * Create a Response.
	 * 
	 * @param string               $type         Request type.
	 * @param string               $conversation Conversation.
	 * @param array<string, mixed> $params       Additional parameters.
	 * 
	 * @return object|\WP_Error
	 */
	public function create_response( $type, $conversation, $params = [] ) {
		$data = Prompts::get_schema_and_prompt( $type, $params );

		$params = [
			'background'   => true,
			'conversation' => $conversation,
			'model'        => $this->chat_model,
			'temperature'  => 1,
			'top_p'        => 1,
			'input'        => $data['prompt'],
			'text'         => [
				'format' => $data['schema'],
			],
		];

		$response = $this->request( 'responses', $params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response->id ) || ( isset( $response->status ) && 'queued' !== $response->status ) ) {
			return new \WP_Error( 'unknown_error', __( 'An error occurred while creating the run.', 'particles' ) );
		}

		return $response->id;
	}

	/**
	 * Get Response.
	 * 
	 * @param string $response_id Response ID.
	 * 
	 * @return mixed
	 */
	public function get_response( $response_id ) {
		$response = $this->request( 'responses/' . $response_id, [], 'GET' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response->id ) || ( isset( $response->error ) && ( is_object( $response->error ) || $response->error ) ) ) {
			return new \WP_Error( 'unknown_error', __( 'An error occurred while getting the response.', 'particles' ) );
		}

		return $response;
	}
}
