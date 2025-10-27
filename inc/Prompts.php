<?php
/**
 * Prompts Class.
 *
 * @package Codeinwp\Particles
 */

namespace ThemeIsle\Particles;

use ThemeIsle\Particles\Theme_Config;

/**
 * Class Prompts
 */
class Prompts {
	/**
	 * Get JSON schema and prompt based on request type.
	 * 
	 * @param string               $type Request type: planning, generation, refinement.
	 * @param array<string, mixed> $params Additional parameters for prompt generation.
	 * 
	 * @return array{schema: array, prompt: string} JSON schema and prompt
	 * 
	 * @throws \InvalidArgumentException If the request type is invalid.
	 */
	public static function get_schema_and_prompt( $type, $params = [] ): array {
		switch ( $type ) {
			case 'planning':
				return [
					'schema' => self::get_planning_schema(),
					'prompt' => self::get_planning_prompt( $params['user_input'] ),
				];
			case 'generation':
				return [
					'schema' => self::get_generation_schema(),
					'prompt' => self::get_generation_prompt( $params['outline'] ),
				];
			case 'refinement':
				return [
					'schema' => self::get_refinement_schema(),
					'prompt' => self::get_refinement_prompt( $params['current_pattern'], $params['user_request'] ),
				];
			default:
				throw new \InvalidArgumentException( 'Invalid request type: ' . esc_attr( $type ) );
		}
	}
	
	/**
	 * Get JSON schema for planning response.
	 * 
	 * @return array OpenAI JSON schema configuration
	 */
	public static function get_planning_schema() {
		return [
			'type'   => 'json_schema',
			'name'   => 'planning_response',
			'strict' => true,
			'schema' => [
				'type'                 => 'object',
				'properties'           => [
					'outline' => [
						'type'                 => 'object',
						'properties'           => [
							'page_type'        => [
								'type'        => 'string',
								'description' => 'Type of page being created (e.g., landing_page, about_page)',
							],
							'subject'          => [
								'type'        => 'string',
								'description' => 'Brief description of the subject matter',
							],
							'sections'         => [
								'type'  => 'array',
								'items' => [
									'type'                 => 'object',
									'properties'           => [
										'id'          => [
											'type'        => 'string',
											'description' => 'Unique identifier for the section',
										],
										'name'        => [
											'type'        => 'string',
											'description' => 'Display name of the section',
										],
										'description' => [
											'type'        => 'string',
											'description' => 'What this section does',
										],
										'components'  => [
											'type'        => 'array',
											'items'       => [
												'type' => 'string',
											],
											'description' => 'List of block types needed',
										],
									],
									'required'             => [ 'id', 'name', 'description', 'components' ],
									'additionalProperties' => false,
								],
							],
							'estimated_blocks' => [
								'type'        => 'integer',
								'description' => 'Estimated number of blocks',
							],
						],
						'required'             => [ 'page_type', 'subject', 'sections', 'estimated_blocks' ],
						'additionalProperties' => false,
					],
					'message' => [
						'type'        => 'string',
						'description' => 'Friendly message to show the user in chat',
					],
				],
				'required'             => [ 'outline', 'message' ],
				'additionalProperties' => false,
			],
		];
	}
	
	/**
	 * Get JSON schema for generation response
	 * 
	 * @return array OpenAI JSON schema configuration
	 */
	public static function get_generation_schema() {
		return [
			'type'   => 'json_schema',
			'name'   => 'generation_response',
			'strict' => true,
			'schema' => [
				'type'                 => 'object',
				'properties'           => [
					'patterns' => [
						'type'        => 'array',
						'items'       => [
							'type' => 'string',
						],
						'description' => 'Array of WordPress block pattern markup strings',
					],
					'message'  => [
						'type'        => 'string',
						'description' => 'Friendly message to show the user in chat',
					],
				],
				'required'             => [ 'patterns', 'message' ],
				'additionalProperties' => false,
			],
		];
	}
	
	/**
	 * Get JSON schema for refinement response
	 * 
	 * @return array OpenAI JSON schema configuration
	 */
	public static function get_refinement_schema() {
		return [
			'type'   => 'json_schema',
			'name'   => 'refinement_response',
			'strict' => true,
			'schema' => [
				'type'                 => 'object',
				'properties'           => [
					'patterns' => [
						'type'        => 'array',
						'items'       => [
							'type' => 'string',
						],
						'description' => 'Array of updated WordPress block pattern markup strings',
					],
					'message'  => [
						'type'        => 'string',
						'description' => 'Friendly message explaining what changes were made',
					],
				],
				'required'             => [ 'patterns', 'message' ],
				'additionalProperties' => false,
			],
		];
	}

	/**
	 * Get planning prompt for initial user request.
	 * 
	 * @param string $user_input User's request.
	 * @return string Complete prompt for AI
	 */
	public static function get_planning_prompt( $user_input ) {
		return "You are a WordPress design assistant helping users create Block Patterns.

TASK: Analyze the user's request and create a structured outline.

USER REQUEST: {$user_input}

REQUIREMENTS:
- Identify the type of content (landing page, about page, portfolio, etc.)
- Break down into logical sections
- Suggest appropriate WordPress core blocks for each section
- Return structured JSON outline

CONSTRAINTS:
- Use only core WordPress blocks
- Consider typical web design best practices
- Keep sections focused and purposeful

OUTPUT FORMAT (JSON):
{
  \"outline\": {
    \"page_type\": \"landing_page\",
    \"subject\": \"Brief description\",
    \"sections\": [
      {
        \"id\": \"unique_id\",
        \"name\": \"Section Name\",
        \"description\": \"What this section does\",
        \"components\": [\"block_types\", \"needed\"]
      }
    ],
    \"estimated_blocks\": 25
  },
  \"message\": \"I've created a structure for your landing page with 5 sections.\"
}

The \"message\" field should be a friendly response to show the user in the chat interface.

Return only the JSON, no additional text.";
	}

	/**
	 * Get generation prompt for creating block pattern
	 * 
	 * @param string $outline Outline from planning phase.
	 * @return string Complete prompt for AI
	 */
	public static function get_generation_prompt( string $outline ): string {
		$outline_json = $outline;
		$theme_json   = wp_json_encode( Theme_Config::get_config() );
		
		return "You are a WordPress Block Pattern generator.

TASK: Generate complete WordPress block markup based on the outline.

OUTLINE:
{$outline_json}

THEME CONFIGURATION:
{$theme_json}

REQUIREMENTS:
- Generate valid WordPress block markup (HTML comments format)
- Use ONLY core WordPress blocks
- Apply styling from theme.json (colors, typography, spacing)
- Write actual content based on subject matter (placeholder content acceptable)
- Add \"context\" attribute to all top-level blocks with medium-detail explanation

CONTEXT ATTRIBUTE FORMAT:
- Free-form text describing purpose + key design decisions + content intent
- Example: \"Hero section introducing [subject] with [design choice], emphasizing [key message]\"
- Avoid sensitive information

STYLING CONSTRAINTS:
- All colors must be from theme.json palette (use slug like \"accent-1\")
- All font sizes must use theme.json scale (use slug like \"x-large\")
- All spacing must use theme.json spacing scale (use slug like \"50\")
- Use var:preset|color|slug for colors
- Use var:preset|font-size|slug for font sizes
- Use var:preset|spacing|slug for spacing
- Ensure proper block nesting and valid markup

OUTPUT FORMAT (JSON):
{
  \"patterns\": [
    \"<!-- wp:group {...} -->\\n<div>...</div>\\n<!-- /wp:group -->\",
    \"<!-- wp:group {...} -->\\n<div>...</div>\\n<!-- /wp:group -->\",
    \"<!-- wp:group {...} -->\\n<div>...</div>\\n<!-- /wp:group -->\"
  ],
  \"message\": \"I've created your Audi F1 landing page with 5 sections. Check the preview!\"
}

The \"message\" field should be a friendly response to show the user in the chat interface.

Return the response as JSON with the complete block markup in the \"pattern\" field.";
	}

	/**
	 * Get refinement prompt for modifying existing pattern.
	 * 
	 * @param string $current_pattern Current pattern markup.
	 * @param string $user_request User's modification request.
	 * @return string Complete prompt for AI
	 */
	public static function get_refinement_prompt( string $current_pattern, string $user_request ): string {
		$theme_json = wp_json_encode( Theme_Config::get_config() );

		return "You are modifying an existing WordPress Block Pattern.

TASK: Apply the requested changes to the pattern.

USER REQUEST: {$user_request}

CURRENT PATTERN:
{$current_pattern}

THEME CONFIGURATION:
{$theme_json}

REQUIREMENTS:
- Apply all requested modifications
- Return complete updated pattern (not just changed sections)
- Update context attributes for significantly modified sections
- Maintain consistency with theme.json constraints
- Use context attributes to quickly identify which sections to modify

HANDLING EDGE CASES:
- If request is ambiguous, use conversation history to infer intent
- If request is impossible with core blocks, do best approximation and explain in the message
- If theme doesn't support requested styling, suggest closest alternative

STYLING CONSTRAINTS:
- All colors must be from theme.json palette (use slug)
- All font sizes must use theme.json scale (use slug)
- All spacing must use theme.json spacing scale (use slug)
- Use var:preset format for all design tokens

OUTPUT FORMAT (JSON):
{
  \"patterns\": [
    \"<!-- wp:group {...} -->\\n<div>...</div>\\n<!-- /wp:group -->\",
    \"<!-- wp:group {...} -->\\n<div>...</div>\\n<!-- /wp:group -->\"
  ],
  \"message\": \"I've updated the CTA button alignment and moved the team section.\"
}

The \"patterns\" field should be an array containing each section's updated block markup.
The \"message\" field should be a friendly response explaining what changes were made.

Return the response as JSON with the complete updated pattern markup in the \"patterns\" array.";
	}
}
