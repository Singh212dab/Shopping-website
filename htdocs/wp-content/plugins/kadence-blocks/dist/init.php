<?php
/**
 * Enqueue admin CSS/JS and edit width functions
 *
 * @since   1.0.0
 * @package Kadence Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue block assets for backend editor.
 *
 * @since 1.0.0
 */
function kadence_gutenberg_editor_assets() {
	// If in the frontend, bail out.
	if ( ! is_admin() ) {
		return;
	}
	$asset_meta = kadence_blocks_get_asset_file( 'dist/build/blocks' );
	// Scripts.
	wp_register_script( 'kadence-blocks-js', KADENCE_BLOCKS_URL . 'dist/build/blocks.js', array_merge( $asset_meta['dependencies'], array( 'wp-api' ) ), $asset_meta['version'], true );
	// Styles.
	wp_register_style( 'kadence-blocks-editor-css', KADENCE_BLOCKS_URL . 'dist/build/blocks.css', array( 'wp-edit-blocks' ), $asset_meta['version'] );
	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'kadence-blocks-js', 'kadence-blocks' );
	}
}
add_action( 'init', 'kadence_gutenberg_editor_assets', 12 );
/**
 * Enqueue block settings for backend editor.
 */
function kadence_blocks_gutenberg_editor_assets_variables() {
	$editor_widths  = get_option( 'kt_blocks_editor_width', array() );
	$sidebar_size   = 750;
	$nosidebar_size = 1140;
	$jssize         = 2000;
	if ( ! isset( $editor_widths['enable_editor_width'] ) || 'true' === $editor_widths['enable_editor_width'] ) {
		$add_size = 30;
		$post_type = get_post_type();
		if ( isset( $editor_widths['page_default'] ) && ! empty( $editor_widths['page_default'] ) && isset( $editor_widths['post_default'] ) && ! empty( $editor_widths['post_default'] ) ) {
			if ( isset( $post_type ) && 'page' === $post_type ) {
				$defualt_size_type = $editor_widths['page_default'];
			} else {
				$defualt_size_type = $editor_widths['post_default'];
			}
		} else {
			$defualt_size_type = 'sidebar';
		}
		if ( isset( $editor_widths['sidebar'] ) && ! empty( $editor_widths['sidebar'] ) ) {
			$sidebar_size = $editor_widths['sidebar'] + $add_size;
		} else {
			$sidebar_size = 750;
		}
		if ( isset( $editor_widths['nosidebar'] ) && ! empty( $editor_widths['nosidebar'] ) ) {
			$nosidebar_size = $editor_widths['nosidebar'] + $add_size;
		} else {
			$nosidebar_size = 1140 + $add_size;
		}
		if ( 'sidebar' == $defualt_size_type ) {
			$default_size = $sidebar_size;
		} elseif ( 'fullwidth' == $defualt_size_type ) {
			$default_size = 'none';
		} else {
			$default_size = $nosidebar_size;
		}
		if ( 'none' === $default_size ) {
			$jssize = 2000;
		} else {
			$jssize = $default_size;
		}
	}
	if ( current_user_can( apply_filters( 'kadence_blocks_admin_role', 'manage_options' ) ) ) {
		$userrole = 'admin';
	} else if ( current_user_can( apply_filters( 'kadence_blocks_editor_role', 'delete_others_pages' ) ) ) {
		$userrole = 'editor';
	} else if ( current_user_can( apply_filters( 'kadence_blocks_author_role', 'publish_posts' ) ) ) {
		$userrole = 'author';
	} else if ( current_user_can( apply_filters( 'kadence_blocks_contributor_role', 'edit_posts' ) ) ) {
		$userrole = 'contributor';
	} else {
		$userrole = 'none';
	}
	if ( isset( $editor_widths['enable_editor_width'] ) && 'false' === $editor_widths['enable_editor_width'] ) {
		$enable_editor_width = false;
	} else {
		$enable_editor_width = true;
	}
	$pro_data = false;
	if ( class_exists( 'Kadence_Blocks_Pro' ) ) {
		$pro_data = kadence_blocks_get_pro_license_data();
	}
	$gfonts_path      = KADENCE_BLOCKS_PATH . 'dist/gfonts-array.php';
	$gfont_names_path = KADENCE_BLOCKS_PATH . 'dist/gfonts-names-array.php';
	$icon_names_path  = KADENCE_BLOCKS_PATH . 'dist/icon-names-array.php';
	$icon_ico_path    = KADENCE_BLOCKS_PATH . 'dist/icon-ico-array.php';
	$icons_path       = KADENCE_BLOCKS_PATH . 'dist/icons-array.php';
	wp_localize_script(
		'kadence-blocks-js',
		'kadence_blocks_params',
		array(
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'     => wp_create_nonce( 'kadence-blocks-ajax-verification' ),
			'sidebar_size'   => $sidebar_size,
			'nosidebar_size' => $nosidebar_size,
			'default_size'   => $jssize,
			'config'         => get_option( 'kt_blocks_config_blocks' ),
			'configuration'  => get_option( 'kadence_blocks_config_blocks' ),
			'settings'       => get_option( 'kadence_blocks_settings_blocks' ),
			'userrole'       => $userrole,
			'proData'        => $pro_data,
			'pro'            => ( class_exists( 'Kadence_Blocks_Pro' ) ? 'true' : 'false' ),
			'library_sections' => Kadence_Blocks_Prebuilt_Library::get_instance()->get_section_prebuilt_data( $pro_data ),
			'colors'         => get_option( 'kadence_blocks_colors' ),
			'global'         => get_option( 'kadence_blocks_global' ),
			'gutenberg'      => ( function_exists( 'gutenberg_menu' ) ? 'true' : 'false' ),
			'privacy_link'   => get_privacy_policy_url(),
			'privacy_title'  => ( get_option( 'wp_page_for_privacy_policy' ) ? get_the_title( get_option( 'wp_page_for_privacy_policy' ) ) : '' ),
			'editor_width'   => apply_filters( 'kadence_blocks_editor_width', $enable_editor_width ),
			'isKadenceT'     => class_exists( 'Kadence\Theme' ),
			'headingWeights' => class_exists( 'Kadence\Theme' ) ? kadence_blocks_get_headings_weights() : null,
			'buttonWeights'  => class_exists( 'Kadence\Theme' ) ? kadence_blocks_get_button_weights() : null,
			'postTypes'      => kadence_blocks_get_post_types(),
			'taxonomies'     => kadence_blocks_get_taxonomies(),
			'g_fonts'        => file_exists( $gfonts_path ) ? include $gfonts_path : array(),
			'g_font_names'   => file_exists( $gfont_names_path ) ? include $gfont_names_path : array(),
			'c_fonts'        => apply_filters( 'kadence_blocks_custom_fonts', array() ),
			'fluentCRM'      => defined( 'FLUENTCRM' ),
			'cloud_enabled'  => apply_filters( 'kadence_blocks_cloud_enabled', true ),
			'cloud_settings' => get_option( 'kadence_blocks_cloud' ),
			'prebuilt_libraries' => apply_filters( 'kadence_blocks_custom_prebuilt_libraries', array() ),
			'showDesignLibrary' => apply_filters( 'kadence_blocks_design_library_enabled', true ),
			'postQueryEndpoint'  => '/kbp/v1/post-query',
			'icon_names'     => file_exists( $icon_names_path ) ? include $icon_names_path : array(),
			'icons_ico'      => file_exists( $icon_ico_path ) ? include $icon_ico_path : array(),
			'icons_fa'       => file_exists( $icons_path ) ? include $icons_path : array(),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'kadence_blocks_gutenberg_editor_assets_variables' );

/**
 * Get the asset file produced by wp scripts.
 *
 * @param string $filepath the file path.
 * @return array
 */
function kadence_blocks_get_asset_file( $filepath ) {
	$asset_path = KADENCE_BLOCKS_PATH . $filepath . '.asset.php';

	return file_exists( $asset_path )
		? include $asset_path
		: array(
			'dependencies' => array( 'lodash', 'react', 'react-dom', 'wp-block-editor', 'wp-blocks', 'wp-data', 'wp-element', 'wp-i18n', 'wp-polyfill', 'wp-primitives', 'wp-api' ),
			'version'      => KADENCE_BLOCKS_VERSION,
		);
}

/**
 * Setup the post type taxonomies for post blocks.
 *
 * @return array
 */
function kadence_blocks_get_taxonomies() {
	$post_types = kadence_blocks_get_post_types();
	$output = array();
	foreach ( $post_types as $key => $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type['value'], 'objects' );
		$taxs = array();
		foreach ( $taxonomies as $term_slug => $term ) {
			if ( ! $term->public || ! $term->show_ui ) {
				continue;
			}
			$taxs[ $term_slug ] = $term;
			$terms = get_terms( $term_slug );
			$term_items = array();
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term_key => $term_item ) {
					$term_items[] = array(
						'value' => $term_item->term_id,
						'label' => $term_item->name,
					);
				}
				$output[ $post_type['value'] ]['terms'][ $term_slug ] = $term_items;
			}
		}
		$output[ $post_type['value'] ]['taxonomy'] = $taxs;
	}
	return apply_filters( 'kadence_blocks_taxonomies', $output );
}

/**
 * Setup the post type options for post blocks.
 *
 * @return array
 */
function kadence_blocks_get_post_types() {
	$args = array(
		'public'       => true,
		'show_in_rest' => true,
	);
	$post_types = get_post_types( $args, 'objects' );
	$output = array();
	foreach ( $post_types as $post_type ) {
		// if ( 'product' == $post_type->name || 'attachment' == $post_type->name ) {
		// 	continue;
		// }
		if ( 'attachment' == $post_type->name ) {
			continue;
		}
		$output[] = array(
			'value' => $post_type->name,
			'label' => $post_type->label,
		);
	}
	return apply_filters( 'kadence_blocks_post_types', $output );
}
/**
 * Get an array font weight options.
 */
function kadence_blocks_get_headings_weights() {
	$weights = array();
	if ( function_exists( 'Kadence\kadence' ) ) {
		$heading_font = \Kadence\kadence()->option( 'heading_font' );
		if ( is_array( $heading_font ) ) {
			if ( isset( $heading_font['family'] ) && ! empty( $heading_font['family'] ) && 'inherit' === $heading_font['family'] ) {
				// Inherit from Body.
				$heading_font = \Kadence\kadence()->option( 'base_font' );
			}
			if ( isset( $heading_font['google'] ) && $heading_font['google'] ) {
				// Google font.
				if ( isset( $heading_font['variant'] ) && is_array( $heading_font['variant'] ) ) {
					$new_weight_variant = array();
					foreach ( array( 'h1_font', 'h2_font', 'h3_font', 'h4_font', 'h5_font', 'h6_font' ) as $option ) {
						$variant = \Kadence\kadence()->sub_option( $option, 'weight' );
						if ( ! in_array( $variant, $new_weight_variant, true ) ) {
							array_push( $new_weight_variant, $variant );
						}
					}
					$weights[] = array( 'value' => '', 'label' => __( 'Inherit', 'kadence-blocks' ) );
					foreach ( $heading_font['variant'] as $key => $value ) {
						if ( $value === '100' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '100', 'label' => __( 'Thin 100', 'kadence-blocks' ) );
						} elseif ( $value === '200' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '200', 'label' => __( 'Extra-Light 200', 'kadence-blocks' ) );
						} elseif ( $value === '300' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '300', 'label' => __( 'Light 300', 'kadence-blocks' ) );
						} elseif ( $value === '400' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '400', 'label' => __( 'Regular', 'kadence-blocks' ) );
						} elseif ( $value === 'regular' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '400', 'label' => __( 'Regular', 'kadence-blocks' ) );
						} elseif ( $value === '500' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '500', 'label' => __( 'Medium 500', 'kadence-blocks' ) );
						} elseif ( $value === '600' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '600', 'label' => __( 'Semi-Bold 600', 'kadence-blocks' ) );
						} elseif ( $value === '700' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '700', 'label' => __( 'Bold 700', 'kadence-blocks' ) );
						} elseif ( $value === '800' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '800', 'label' => __( 'Extra-Bold 800', 'kadence-blocks' ) );
						} elseif ( $value === '900' && in_array( $value, $new_weight_variant ) ) {
							$weights[] = array( 'value' => '900', 'label' => __( 'Ultra-Bold 900', 'kadence-blocks' ) );
						}
					}
				}
			} elseif ( isset( $heading_font['family'] ) && ! empty( $heading_font['family'] ) && substr( $heading_font['family'], 0, strlen( '-apple-system' ) ) === '-apple-system' ) {
				// System Font.
				$weights = array(
					array( 'value' => '', 'label' => __( 'Inherit', 'kadence-blocks' ) ),
					array( 'value' => '100', 'label' => __( 'Thin 100', 'kadence-blocks' ) ),
					array( 'value' => '200', 'label' => __( 'Extra-Light 200', 'kadence-blocks' ) ),
					array( 'value' => '300', 'label' => __( 'Light 300', 'kadence-blocks' ) ),
					array( 'value' => '400', 'label' => __( 'Regular', 'kadence-blocks' ) ),
					array( 'value' => '500', 'label' => __( 'Medium 500', 'kadence-blocks' ) ),
					array( 'value' => '600', 'label' => __( 'Semi-Bold 600', 'kadence-blocks' ) ),
					array( 'value' => '700', 'label' => __( 'Bold 700', 'kadence-blocks' ) ),
					array( 'value' => '800', 'label' => __( 'Extra-Bold 800', 'kadence-blocks' ) ),
					array( 'value' => '900', 'label' => __( 'Ultra-Bold 900', 'kadence-blocks' ) ),
				);
			}
		}
	}
	return apply_filters( 'kadence_blocks_heading_weight_options', $weights );
}
/**
 * Get an array font weight options.
 */
function kadence_blocks_get_button_weights() {
	$weights = array();
	if ( function_exists( 'Kadence\kadence' ) ) {
		$button_font = \Kadence\kadence()->option( 'buttons_typography' );
		if ( is_array( $button_font ) ) {
			if ( isset( $button_font['family'] ) && ! empty( $button_font['family'] ) && 'inherit' === $button_font['family'] ) {
				// Inherit from Body.
				$button_font = \Kadence\kadence()->option( 'base_font' );
			}
			if ( isset( $button_font['google'] ) && $button_font['google'] ) {
				$heading_font = \Kadence\kadence()->option( 'heading_font' );
				if ( isset( $heading_font['family'] ) && ! empty( $heading_font['family'] ) && $button_font['family'] === $heading_font['family'] ) {
					// Google font.
					if ( isset( $heading_font['variant'] ) && is_array( $heading_font['variant'] ) ) {
						if ( isset( $button_font['weight'] ) && ! empty( $button_font['weight'] ) ) {
							$new_weight_variant = array( $button_font['weight'] );
						} else {
							$new_weight_variant = array();
						}
						foreach ( array( 'h1_font', 'h2_font', 'h3_font', 'h4_font', 'h5_font', 'h6_font' ) as $option ) {
							$variant = \Kadence\kadence()->sub_option( $option, 'weight' );
							if ( ! in_array( $variant, $new_weight_variant, true ) ) {
								array_push( $new_weight_variant, $variant );
							}
						}
						$weights[] = array( 'value' => '', 'label' => __( 'Inherit', 'kadence-blocks' ) );
						foreach ( $heading_font['variant'] as $key => $value ) {
							if ( $value === '100' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '100', 'label' => __( 'Thin 100', 'kadence-blocks' ) );
							} elseif ( $value === '200' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '200', 'label' => __( 'Extra-Light 200', 'kadence-blocks' ) );
							} elseif ( $value === '300' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '300', 'label' => __( 'Light 300', 'kadence-blocks' ) );
							} elseif ( $value === '400' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '400', 'label' => __( 'Regular', 'kadence-blocks' ) );
							} elseif ( $value === 'regular' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '400', 'label' => __( 'Regular', 'kadence-blocks' ) );
							} elseif ( $value === '500' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '500', 'label' => __( 'Medium 500', 'kadence-blocks' ) );
							} elseif ( $value === '600' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '600', 'label' => __( 'Semi-Bold 600', 'kadence-blocks' ) );
							} elseif ( $value === '700' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '700', 'label' => __( 'Bold 700', 'kadence-blocks' ) );
							} elseif ( $value === '800' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '800', 'label' => __( 'Extra-Bold 800', 'kadence-blocks' ) );
							} elseif ( $value === '900' && in_array( $value, $new_weight_variant ) ) {
								$weights[] = array( 'value' => '900', 'label' => __( 'Ultra-Bold 900', 'kadence-blocks' ) );
							}
						}
					}
				}
			} elseif ( isset( $button_font['family'] ) && ! empty( $button_font['family'] ) && substr( $button_font['family'], 0, strlen( '-apple-system' ) ) === '-apple-system' ) {
				// System Font.
				$weights = array(
					array( 'value' => '', 'label' => __( 'Inherit', 'kadence-blocks' ) ),
					array( 'value' => '100', 'label' => __( 'Thin 100', 'kadence-blocks' ) ),
					array( 'value' => '200', 'label' => __( 'Extra-Light 200', 'kadence-blocks' ) ),
					array( 'value' => '300', 'label' => __( 'Light 300', 'kadence-blocks' ) ),
					array( 'value' => '400', 'label' => __( 'Regular', 'kadence-blocks' ) ),
					array( 'value' => '500', 'label' => __( 'Medium 500', 'kadence-blocks' ) ),
					array( 'value' => '600', 'label' => __( 'Semi-Bold 600', 'kadence-blocks' ) ),
					array( 'value' => '700', 'label' => __( 'Bold 700', 'kadence-blocks' ) ),
					array( 'value' => '800', 'label' => __( 'Extra-Bold 800', 'kadence-blocks' ) ),
					array( 'value' => '900', 'label' => __( 'Ultra-Bold 900', 'kadence-blocks' ) ),
				);
			}
		}
		//print_r( $button_font );
	}
	return apply_filters( 'kadence_blocks_button_weight_options', $weights );
}
/**
 * Add a filter that matches for custom font filter.
 */
function kadence_blocks_convert_custom_fonts() {
	if ( ! is_admin() ) {
		return;
	}
	$convert_fonts = apply_filters( 'kadence_blocks_add_custom_fonts', array() );
	if ( ! empty( $convert_fonts ) && is_array( $convert_fonts ) ) {
		add_filter(
			'kadence_blocks_custom_fonts',
			function( $custom_fonts ) use( $convert_fonts ) {
				foreach ( $convert_fonts as $font_name => $args ) {
					$weights_arg = array();
					if ( is_array( $args ) && isset( $args['weights'] ) && is_array( $args['weights'] ) ) {
						$weights_arg = $args['weights'];
					}
					$font_slug = ( is_array( $args ) && isset( $args['fallback'] ) && ! empty( $args['fallback'] ) ? '"' . $font_name . '", ' . $args['fallback'] : $font_name );
					$custom_fonts[ $font_slug  ] = array(
						'name'    => $font_slug,
						'weights' => $weights_arg,
						'styles'  => array(),
					);
				}
				return $custom_fonts;
			},
			10
		);
	}
}
add_filter( 'init', 'kadence_blocks_convert_custom_fonts', 11 );
/**
 * Get an array of visibility options.
 */
function kadence_blocks_get_user_visibility_options() {
	$specific_roles = array();
	if ( function_exists( 'get_editable_roles' ) ) {
		foreach ( get_editable_roles() as $role_slug => $role_info ) {
			$specific_roles[] = array(
				'value' => $role_slug,
				'label' => $role_info['name'],
			);
		}
	}
	return apply_filters( 'kadence_blocks_user_visibility_options', $specific_roles );
}
/**
 * Enqueue Gutenberg block assets for backend editor.
 */
function kadence_blocks_early_editor_assets() {
	if ( ! is_admin() ) {
		return;
	}
	global $pagenow;
	if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
		$current_screen = get_current_screen();
		if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			wp_localize_script(
				'kadence-blocks-js',
				'kadence_blocks_user_params',
				array(
					'userVisibility'  => kadence_blocks_get_user_visibility_options(),
				)
			);
		}
	}
}
add_action( 'current_screen', 'kadence_blocks_early_editor_assets' );
/**
 * Register Meta for blocks width
 */
function kt_blocks_init_post_meta() {

	register_post_meta(
		'',
		'kt_blocks_editor_width',
		array(
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		)
	);
}
add_action( 'init', 'kt_blocks_init_post_meta' );

/**
 * Add inline css editor width
 */
function kadence_blocks_admin_theme_content_width() {
	global $content_width;
	if ( isset( $content_width ) ) {
		echo '<style id="kt-block-content-width">';
		echo '.wp-block-kadence-rowlayout > .innerblocks-wrap.kb-theme-content-width {
			max-width:' . esc_attr( $content_width ) . 'px;
		}';
		echo '</style>';
	}
}
add_action( 'admin_head-post.php', 'kadence_blocks_admin_theme_content_width', 100 );
add_action( 'admin_head-post-new.php', 'kadence_blocks_admin_theme_content_width', 100 );
/**
 * Add inline css editor width
 */
function kadence_blocks_admin_editor_width() {
	$editor_widths = get_option( 'kt_blocks_editor_width', array() );
	if ( ( ! isset( $editor_widths['enable_editor_width'] ) || 'true' === $editor_widths['enable_editor_width'] ) && apply_filters( 'kadence_blocks_editor_width', true ) ) {
		if ( isset( $editor_widths['limited_margins'] ) && 'true' === $editor_widths['limited_margins'] ) {
			$add_size = 10;
		} else {
			$add_size = 30;
		}
		$post_type = get_post_type();
		if ( isset( $editor_widths['page_default'] ) && ! empty( $editor_widths['page_default'] ) && isset( $editor_widths['post_default'] ) && ! empty( $editor_widths['post_default'] ) ) {
			if ( isset( $post_type ) && 'page' === $post_type ) {
				$defualt_size_type = $editor_widths['page_default'];
			} else {
				$defualt_size_type = $editor_widths['post_default'];
			}
		} else {
			$defualt_size_type = 'sidebar';
		}
		if ( isset( $editor_widths['sidebar'] ) && ! empty( $editor_widths['sidebar'] ) ) {
			$sidebar_size = $editor_widths['sidebar'] + $add_size;
		} else {
			$sidebar_size = 750;
		}
		if ( isset( $editor_widths['nosidebar'] ) && ! empty( $editor_widths['nosidebar'] ) ) {
			$nosidebar_size = $editor_widths['nosidebar'] + $add_size;
		} else {
			$nosidebar_size = 1140 + $add_size;
		}
		if ( 'sidebar' == $defualt_size_type ) {
			$default_size = $sidebar_size;
		} elseif ( 'fullwidth' == $defualt_size_type ) {
			$default_size = 'none';
		} else {
			$default_size = $nosidebar_size;
		}
		if ( 'none' === $default_size ) {
			$jssize = 2000;
		} else {
			$jssize = $default_size;
		}
		echo '<style id="kt-block-editor-width">';
		echo 'body.gutenberg-editor-page.kt-editor-width-default .editor-post-title__block,
		body.gutenberg-editor-page.kt-editor-width-default .editor-default-block-appender,
		body.gutenberg-editor-page.kt-editor-width-default .block-editor-block-list__block,
		body.block-editor-page.kt-editor-width-default .wp-block {
			max-width: ' . esc_attr( $default_size ) . ( is_numeric( $default_size ) ? 'px' : '' ) . ';
		}';
		echo 'body.gutenberg-editor-page.kt-editor-width-sidebar .editor-post-title__block,
		body.gutenberg-editor-page.kt-editor-width-sidebar .editor-default-block-appender,
		body.gutenberg-editor-page.kt-editor-width-sidebar .block-editor-block-list__block,
		body.block-editor-page.kt-editor-width-sidebar .wp-block {
			max-width: ' . esc_attr( $sidebar_size ) . 'px;
		}';
		echo 'body.gutenberg-editor-page.kt-editor-width-nosidebar .editor-post-title__block,
		body.gutenberg-editor-page.kt-editor-width-nosidebar .editor-default-block-appender,
		body.gutenberg-editor-page.kt-editor-width-nosidebar .block-editor-block-list__block,
		body.block-editor-page.kt-editor-width-nosidebar .wp-block {
			max-width: ' . esc_attr( $nosidebar_size ) . 'px;
		}';
		echo 'body.gutenberg-editor-page.kt-editor-width-fullwidth .editor-post-title__block,
		body.gutenberg-editor-page.kt-editor-width-fullwidth .editor-default-block-appender,
		body.gutenberg-editor-page.kt-editor-width-fullwidth .block-editor-block-list__block,
		body.block-editor-page.kt-editor-width-fullwidth .wp-block {
			max-width: none;
		}';
		echo 'body.gutenberg-editor-page .block-editor-block-list__layout .block-editor-block-list__block[data-align=wide],
		body.block-editor-page .block-editor-block-list__layout .wp-block[data-align=wide] {
			width: auto;
			max-width: ' . esc_attr( $nosidebar_size + 200 ) . 'px;
		}';

		echo 'body.gutenberg-editor-page .block-editor-block-list__layout .block-editor-block-list__block[data-align=full],
		body.block-editor-page .block-editor-block-list__layout .wp-block[data-align=full] {
			max-width: none;
		}';
		echo '</style>';
		echo '<script> var kt_blocks_sidebar_size = ' . esc_attr( $sidebar_size ) . '; var kt_blocks_nosidebar_size = ' . esc_attr( $nosidebar_size ) . '; var kt_blocks_default_size = ' . esc_attr( $jssize ) . ';</script>';
	}
}
add_action( 'admin_head-post.php', 'kadence_blocks_admin_editor_width', 100 );
add_action( 'admin_head-post-new.php', 'kadence_blocks_admin_editor_width', 100 );

/**
 * Add class to match editor width.
 *
 * @param string $classes string of body classes.
 */
function kadence_blocks_admin_body_class( $classes ) {
	$screen = get_current_screen();
	if ( 'post' == $screen->base ) {
		global $post;
		$editorwidth = get_post_meta( $post->ID, 'kt_blocks_editor_width', true );
		if ( isset( $editorwidth ) && ! empty( $editorwidth ) && 'default' !== $editorwidth ) {
			$classes .= ' kt-editor-width-' . esc_attr( $editorwidth );
		} else {
			$classes .= ' kt-editor-width-default';
		}
	}
	return $classes;
}
add_filter( 'admin_body_class', 'kadence_blocks_admin_body_class' );

/**
 * Add block category for Kadence Blocks.
 *
 * @param array  $categories the array of block categories.
 * @param object $post the post object.
 */
function kadence_blocks_block_category( $categories, $post ) {
	return array_merge(
		array(
			array(
				'slug'  => 'kadence-blocks',
				'title' => __( 'Kadence Blocks', 'kadence-blocks' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories', 'kadence_blocks_block_category', 10, 2 );

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function kadence_blocks_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	$cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path, $default_path, KADENCE_BLOCKS_VERSION ) ) );
	$template  = (string) wp_cache_get( $cache_key, 'kadence-blocks' );

	if ( ! $template ) {
		$template = kadence_blocks_locate_template( $template_name, $template_path, $default_path );
		wp_cache_set( $cache_key, $template, 'kadence-blocks' );
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$filter_template = apply_filters( 'kadence_blocks_get_template', $template, $template_name, $args, $template_path, $default_path );

	if ( $filter_template !== $template ) {
		if ( ! file_exists( $filter_template ) ) {
			return;
		}
		$template = $filter_template;
	}

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);

	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			unset( $args['action_args'] );
		}
		extract( $args ); // @codingStandardsIgnoreLine
	}

	do_action( 'kadence_blocks_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	include $action_args['located'];

	do_action( 'kadence_blocks_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}
/**
 * Like kadence_blocks_get_template, but returns the HTML instead of outputting.
 *
 * @see kadence_blocks_get_template
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function kadence_blocks_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	kadence_blocks_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}
/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function kadence_blocks_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = apply_filters( 'kadence_blocks_template_path', 'kadenceblocks/' );
	}

	if ( ! $default_path ) {
		$default_path = KADENCE_BLOCKS_PATH . 'dist/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'kadence_blocks_locate_template', $template, $template_name, $template_path );
}
/**
 * Print an SVG Icon
 *
 * @param string $icon the icon name.
 * @param string $icon_title the icon title for screen readers.
 * @param bool   $base if the baseline class should be added.
 */
function kadence_blocks_print_icon( $icon = 'arrow-right-alt', $icon_title = '', $base = true ) {
	echo kadence_blocks_get_icon( $icon, $icon_title, $base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
/**
 * Get an SVG Icon
 *
 * @param string $icon the icon name.
 * @param string $icon_title the icon title for screen readers.
 * @param bool   $base if the baseline class should be added.
 */
function kadence_blocks_get_icon( $icon = 'arrow-right-alt', $icon_title = '', $base = true, $aria = false ) {
	$display_title = apply_filters( 'kadence_svg_icons_have_title', true );
	$output = '<span class="kadence-svg-iconset' . ( $base ? ' svg-baseline' : '' ) . '">';
	switch ( $icon ) {
		case 'arrow-right-alt':
			$output .= '<svg' . ( ! $aria ? ' aria-hidden="true"' : '' ) . ' class="kadence-svg-icon kadence-arrow-right-alt-svg" fill="currentColor" version="1.1" xmlns="http://www.w3.org/2000/svg" width="27" height="28" viewBox="0 0 27 28">';
			if ( $display_title ) {
				$output .= '<title>' . ( ! empty( $icon_title ) ? $icon_title : esc_html__( 'Continue', 'kadence' ) ) . '</title>';
			}
			$output .= '<path d="M27 13.953c0 0.141-0.063 0.281-0.156 0.375l-6 5.531c-0.156 0.141-0.359 0.172-0.547 0.094-0.172-0.078-0.297-0.25-0.297-0.453v-3.5h-19.5c-0.281 0-0.5-0.219-0.5-0.5v-3c0-0.281 0.219-0.5 0.5-0.5h19.5v-3.5c0-0.203 0.109-0.375 0.297-0.453s0.391-0.047 0.547 0.078l6 5.469c0.094 0.094 0.156 0.219 0.156 0.359v0z"></path>
			</svg>';
			break;
		default:
			$output .= '';
			break;
	}
	$output .= '</span>';

	$output = apply_filters( 'kadence_svg_icon', $output, $icon, $icon_title, $base );

	return $output;
}
/**
 * Convert the post ID in Kadence Form's block.
 *
 * @see https://onthegosystems.myjetbrains.com/youtrack/issue/wpmlcore-7207
 *
 * @param array $block the filtered block.
 */
function wpmlcore_7207_fix_kadence_form_block( array $block ) {
	if ( 'kadence/form' === $block['blockName'] && class_exists( 'WPML\PB\Gutenberg\ConvertIdsInBlock\Composite' ) && class_exists( 'WPML\PB\Gutenberg\ConvertIdsInBlock\TagAttributes' ) && class_exists( 'WPML\PB\Gutenberg\ConvertIdsInBlock\BlockAttributes' ) ) {
		$slug      = get_post_type();
		$converter = new WPML\PB\Gutenberg\ConvertIdsInBlock\Composite(
			array(
				new WPML\PB\Gutenberg\ConvertIdsInBlock\TagAttributes(
					array(
						array(
							'xpath' => '//*[@name="_kb_form_post_id"]/@value',
							'slug' => $slug,
							'type' => 'post',
						),
					)
				),
				new WPML\PB\Gutenberg\ConvertIdsInBlock\BlockAttributes(
					array(
						array(
							'name' => 'postID',
							'slug' => $slug,
							'type' => 'post',
						),
					)
				),
			)
		);
		return $converter->convert( $block );
	}
	return $block;
}
add_filter( 'render_block_data', 'wpmlcore_7207_fix_kadence_form_block' );

/**
 * Setup the post select API endpoint.
 *
 * @return void
 */
function kadence_blocks_register_api_endpoints() {
	$posts_controller = new Kadence_Blocks_Post_Rest_Controller();
	$posts_controller->register_routes();
	$mailerlite_controller = new Kadence_MailerLite_REST_Controller();
	$mailerlite_controller->register_routes();
	$fluentcrm_controller = new Kadence_FluentCRM_REST_Controller();
	$fluentcrm_controller->register_routes();
}
add_action( 'rest_api_init', 'kadence_blocks_register_api_endpoints' );

/**
 * Get the license information.
 *
 * @return array
 */
function kadence_blocks_get_pro_license_data() {
	$data = false;
	$current_theme = wp_get_theme();
	$current_theme_name = $current_theme->get( 'Name' );
	$current_theme_template = $current_theme->get( 'Template' );
	// Check for a classic theme license.
	if ( 'Pinnacle Premium' == $current_theme_name || 'pinnacle_premium' == $current_theme_template || 'Ascend - Premium' == $current_theme_name || 'ascend_premium' == $current_theme_template || 'Virtue - Premium' == $current_theme_name || 'virtue_premium' == $current_theme_template ) {
		$pro_data = get_option( 'kt_api_manager' );
		if ( $pro_data ) {
			$data['ithemes']  = '';
			$data['username'] = '';
			if ( 'Pinnacle Premium' == $current_theme_name || 'pinnacle_premium' == $current_theme_template ) {
				$data['product_id'] = 'pinnacle_premium';
			} elseif ( 'Ascend - Premium' == $current_theme_name || 'ascend_premium' == $current_theme_template ) {
				$data['product_id'] = 'ascend_premium';
			} elseif ( 'Virtue - Premium' == $current_theme_name || 'virtue_premium' == $current_theme_template ) {
				$data['product_id'] = 'virtue_premium';
			}
			$data['api_key'] = $pro_data['kt_api_key'];
			$data['api_email'] = $pro_data['activation_email'];
		}
	} else {
		if ( is_multisite() && ! apply_filters( 'kadence_activation_individual_multisites', false ) ) {
			$data = get_site_option( 'kt_api_manager_kadence_gutenberg_pro_data' );
		} else {
			$data = get_option( 'kt_api_manager_kadence_gutenberg_pro_data' );
		}
	}
	return $data;
}
