<?php

/**
 * Plugin Name: Service Booking
 * Description: Awesome Desc...
 * Plugin URI:  #
 * Version:     1.0
 * Author:      #
 * Author URI:  #
 * Text Domain: tf-service-booking
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/functions.php';
require __DIR__ . '/woocommerce.php';

/**
 * Define Constant
 */
define( 'TFSB_FILE', __FILE__ );
define( 'TFSB_DIR_URL', plugin_dir_url( TFSB_FILE ) );
define( 'TFSB_ASSETS', TFSB_DIR_URL . 'assets' );
define( 'TFSB_DIR', __DIR__ );

/**
 * Load assets for front-end
 */
add_action( 'wp_enqueue_scripts', 'tfsb_assets' );
function tfsb_assets() {
	wp_enqueue_style( 'tfsb-main-style', TFSB_ASSETS . '/css/style.css', null, time(), 'all' );
	wp_enqueue_script( 'tfsb-main-script', TFSB_ASSETS . '/js/main.js', array( 'jquery' ), time(), true );

	wp_localize_script(
		'tfsb-main-script',
		'siteConfig',
		array(
			'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
			'error'              => __( 'Something went wrong' ),
			'nonce'              => wp_create_nonce( 'tfsb-nonce' ),
			'loading'            => __( 'Content loading...', 'tf-service-booking' ),
			'tfsb_archive_nonce' => wp_create_nonce( 'tfsb_archive_nonce' ),
			'paged'              => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
		)
	);
}

/**
 * Register CPT - 'tf-service-booking'
 */
add_action( 'init', 'tfsb_create_posttype' );
function tfsb_create_posttype() {
	$labels = array(
		'name'          => __( 'TF Services' ),
		'singular_name' => __( 'TF Service Booking' ),
	);
	$args   = array(
		'labels'          => $labels,
		'public'          => true,
		'has_archive'     => true,
		'supports'        => array( 'title', 'editor', 'thumbnail' ),
		'capability_type' => 'product',
		'map_meta_cap'    => true,
		'menu_icon'       => 'dashicons-cart',
		'rewrite'         => array(
			'slug'       => 'tf-service-booking',
			'with_front' => false,
		),
	);
	register_post_type( 'tf-service-booking', $args );
}

/**
 * Create shortcode 'tf_service_result'
 * for load all posts
 *
 * @param limit default 5
 */
add_shortcode( 'tf_service_result', 'tfsb_shortcode' );
function tfsb_shortcode( $atts, $content = null ) {

	$atts    = shortcode_atts(
		array(
			'limit'   => 5,
			'orderby' => 'ASC',
		),
		$atts,
		'tf_service_result'
	);
	$limit   = $atts['limit'];
	$orderby = $atts['orderby'];

	ob_start();
	$args = array(
		'post_type'      => 'tf-service-booking',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'title',
		'order'          => $orderby,
	);

	$loop = new WP_Query( $args );

	if ( $loop->have_posts() ) :
		while ( $loop->have_posts() ) :
			$loop->the_post();
			include TFSB_DIR . '/tfsb-content.php';
			?>


			<?php
		endwhile;
	else :
		_e( 'No posts found' );
	endif;

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Create page template
 */
add_filter( 'theme_page_templates', 'tfsb_theme_page_templates', 20, 3 );
function tfsb_theme_page_templates( $templates ) {
	$templates[ plugin_dir_path( __FILE__ ) . 'templates/page-template.php' ] = __( 'TFSB Page Template', 'tf-service-booking' );
	return $templates;
}

add_filter( 'template_include', 'tfsb_change_page_template', 99 );
function tfsb_change_page_template( $template ) {
	if ( is_page( 'page-template' ) ) {
		$meta = get_post_meta( get_the_ID() );

		if ( ! empty( $meta['_wp_page_template'][0] ) && $meta['_wp_page_template'][0] != $template ) {
			$template = $meta['_wp_page_template'][0];
		}
	}

	return $template;
}


/**
 * Page template for All matching posts
 */
add_filter( 'theme_page_templates', 'tfsb_theme_page_archive_templates', 20, 3 );
function tfsb_theme_page_archive_templates( $templates ) {
	$templates[ plugin_dir_path( __FILE__ ) . 'templates/archive-page-template.php' ] = __( 'TFSB Archive Page Template', 'tf-service-booking' );
	return $templates;
}

add_filter( 'template_include', 'tfsb_change_archive_page_template', 99 );
function tfsb_change_archive_page_template( $template ) {
	if ( is_page( 'archive-page-template' ) ) {
		$meta = get_post_meta( get_the_ID() );

		if ( ! empty( $meta['_wp_page_template'][0] ) && $meta['_wp_page_template'][0] != $template ) {
			$template = $meta['_wp_page_template'][0];
		}
	}

	return $template;
}