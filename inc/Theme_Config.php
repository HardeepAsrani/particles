<?php
/**
 * Theme Config Class.
 *
 * @package Codeinwp\Particles
 */

namespace ThemeIsle\Particles;

/**
 * Class Theme_Config
 */
class Theme_Config {

	/**
	 * Get theme configuration for AI pattern generation.
	 * 
	 * @return array<string, mixed> Theme configuration data
	 */
	public static function get_config(): array {
		$settings = wp_get_global_settings();
		
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}
		
		return [
			'colors'     => [
				'palette' => self::get_colors( $settings ),
			],
			'typography' => [
				'fontSizes'    => self::get_font_sizes( $settings ),
				'fontFamilies' => self::get_font_families( $settings ),
			],
			'spacing'    => [
				'spacingSizes' => self::get_spacing( $settings ),
			],
			'layout'     => self::get_layout( $settings ),
		];
	}
	
	/**
	 * Get color palette.
	 * 
	 * @param array<string, mixed> $settings Global settings.
	 * @return array<int, array<string, string>> Color palette
	 */
	private static function get_colors( array $settings ): array {
		$colors = [];
		
		if ( isset( $settings['color'] ) && 
			is_array( $settings['color'] ) &&
			isset( $settings['color']['palette'] ) && 
			is_array( $settings['color']['palette'] ) &&
			isset( $settings['color']['palette']['theme'] ) && 
			is_array( $settings['color']['palette']['theme'] ) 
		) {
			foreach ( $settings['color']['palette']['theme'] as $color ) {
				if ( is_array( $color ) && isset( $color['slug'], $color['color'], $color['name'] ) ) {
					$colors[] = [
						'slug'  => is_string( $color['slug'] ) ? $color['slug'] : '',
						'color' => is_string( $color['color'] ) ? $color['color'] : '',
						'name'  => is_string( $color['name'] ) ? $color['name'] : '',
					];
				}
			}
		}
		
		return $colors;
	}
	
	/**
	 * Get font sizes.
	 * 
	 * @param array<string, mixed> $settings Global settings.
	 * @return array<int, array<string, string>> Font sizes
	 */
	private static function get_font_sizes( array $settings ): array {
		$sizes = [];
		
		if ( isset( $settings['typography'] ) && 
			is_array( $settings['typography'] ) &&
			isset( $settings['typography']['fontSizes'] ) && 
			is_array( $settings['typography']['fontSizes'] ) &&
			isset( $settings['typography']['fontSizes']['theme'] ) && 
			is_array( $settings['typography']['fontSizes']['theme'] ) 
		) {
			foreach ( $settings['typography']['fontSizes']['theme'] as $size ) {
				if ( is_array( $size ) && isset( $size['slug'], $size['size'], $size['name'] ) ) {
					$sizes[] = [
						'slug' => is_string( $size['slug'] ) ? $size['slug'] : '',
						'size' => is_string( $size['size'] ) ? $size['size'] : '',
						'name' => is_string( $size['name'] ) ? $size['name'] : '',
					];
				}
			}
		}
		
		return $sizes;
	}
	
	/**
	 * Get font families.
	 * 
	 * @param array<string, mixed> $settings Global settings.
	 * @return array<int, array<string, string>> Font families
	 */
	private static function get_font_families( array $settings ): array {
		$families = [];
		
		if ( isset( $settings['typography'] ) && 
			is_array( $settings['typography'] ) &&
			isset( $settings['typography']['fontFamilies'] ) && 
			is_array( $settings['typography']['fontFamilies'] ) &&
			isset( $settings['typography']['fontFamilies']['theme'] ) && 
			is_array( $settings['typography']['fontFamilies']['theme'] ) 
		) {
			foreach ( $settings['typography']['fontFamilies']['theme'] as $family ) {
				if ( is_array( $family ) && isset( $family['slug'], $family['fontFamily'], $family['name'] ) ) {
					$families[] = [
						'slug'       => is_string( $family['slug'] ) ? $family['slug'] : '',
						'fontFamily' => is_string( $family['fontFamily'] ) ? $family['fontFamily'] : '',
						'name'       => is_string( $family['name'] ) ? $family['name'] : '',
					];
				}
			}
		}
		
		return $families;
	}
	
	/**
	 * Get spacing sizes.
	 * 
	 * @param array<string, mixed> $settings Global settings.
	 * @return array<int, array<string, string>> Spacing sizes
	 */
	private static function get_spacing( array $settings ): array {
		$sizes = [];
		
		if ( isset( $settings['spacing'] ) && 
			is_array( $settings['spacing'] ) &&
			isset( $settings['spacing']['spacingSizes'] ) && 
			is_array( $settings['spacing']['spacingSizes'] ) &&
			isset( $settings['spacing']['spacingSizes']['theme'] ) && 
			is_array( $settings['spacing']['spacingSizes']['theme'] ) 
		) {
			foreach ( $settings['spacing']['spacingSizes']['theme'] as $space ) {
				if ( is_array( $space ) && isset( $space['slug'], $space['size'], $space['name'] ) ) {
					$sizes[] = [
						'slug' => is_string( $space['slug'] ) ? $space['slug'] : '',
						'size' => is_string( $space['size'] ) ? $space['size'] : '',
						'name' => is_string( $space['name'] ) ? $space['name'] : '',
					];
				}
			}
		}
		
		return $sizes;
	}
	
	/**
	 * Get layout settings
	 * 
	 * @param array<string, mixed> $settings Global settings.
	 * @return array<string, string> Layout settings
	 */
	private static function get_layout( array $settings ): array {
		$content_size = '';
		$wide_size    = '';
		
		if ( isset( $settings['layout'] ) && is_array( $settings['layout'] ) ) {
			if ( isset( $settings['layout']['contentSize'] ) && is_string( $settings['layout']['contentSize'] ) ) {
				$content_size = $settings['layout']['contentSize'];
			}
			if ( isset( $settings['layout']['wideSize'] ) && is_string( $settings['layout']['wideSize'] ) ) {
				$wide_size = $settings['layout']['wideSize'];
			}
		}
		
		return [
			'contentSize' => $content_size,
			'wideSize'    => $wide_size,
		];
	}
}
