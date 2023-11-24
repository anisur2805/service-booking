<?php

add_action( 'wp_ajax_tfsb_search_form_action', 'tfsb_search_form_action' );
add_action( 'wp_ajax_nopriv_tfsb_search_form_action', 'tfsb_search_form_action' );
function tfsb_search_form_action() {
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tfsb-nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Nonce verification failed' ),
			)
		);
	} else {
		$title          = isset( $_POST['title'] ) ? esc_attr( $_POST['title'] ) : '';
		$posts_per_page = -1;

		$query = array(
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'post_type'      => 'tf-service-booking',
			's'              => $title,
		);

		$query       = new WP_Query( $query );
		$found_posts = $query->found_posts;

		ob_start();
		if ( $query->have_posts() ) :
			$counter = 0;
			while ( $query->have_posts() && $counter < 5 ) :
				$query->the_post();
				include TFSB_DIR . '/tfsb-content.php';
				++$counter;
			endwhile;
		else :
			_e( 'No posts found' );
		endif;

		wp_reset_postdata();

		$output = ob_get_clean();
		wp_send_json_success(
			array(
				'html'        => $output,
				'posts_found' => $found_posts,
			)
		);

	}
}

// Perform ajax pagination in archive page
add_action( 'wp_ajax_nopriv_archive_pagination', 'tfsb_archive_pagination' );
add_action( 'wp_ajax_archive_pagination', 'tfsb_archive_pagination' );
function tfsb_archive_pagination() {
	check_ajax_referer( 'tfsb_archive_nonce', 'security' );
	$keyword = ( isset( $_GET['keyword'] ) ) ? $_GET['keyword'] : 1;
	$paged   = isset( $_POST['paged'] ) ? $_POST['paged'] : 1;
	$args    = array(
		'post_type'      => 'tf-service-booking',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'paged'          => $paged,
		's'              => $keyword,
	);

	$loop = new WP_Query( $args );

	$payload['html'] = '';

	$payload['pagination'] = paginate_links(
		array(
			'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
			'total'        => $loop->max_num_pages,
			'current'      => max( 1, get_query_var( 'paged' ) ),
			'format'       => '?paged=%#%',
			'show_all'     => false,
			'type'         => 'plain',
			'prev_next'    => true,
			'prev_text'    => is_rtl() ? '&rarr;' : '&larr;',
			'next_text'    => is_rtl() ? '&larr;' : '&rarr;',
			'type'         => 'list',
			'end_size'     => 3,
			'mid_size'     => 3,
			'add_args'     => false,
			'add_fragment' => '',
		)
	);
	ob_start();

	while ( $loop->have_posts() ) :
		$loop->the_post();
		include TFSB_DIR . '/tfsb-content.php';
	endwhile;

	$payload['html'] .= ob_get_clean();
	echo json_encode( $payload );

	wp_die();
}

// Add the custom field meta box
add_action( 'add_meta_boxes', 'tfsb_custom_meta_box' );
function tfsb_custom_meta_box() {
	add_meta_box(
		'tfsb_price',
		'Price',
		'tfsb_callback',
		'tf-service-booking'
	);
}

function tfsb_callback( $post ) {
	$value = get_post_meta( $post->ID, '_price', true );
	echo '<label for="_price">Price</label><br/>';
	echo '<input type="text" id="_price" name="_price" value="' . esc_attr( $value ) . '">';
}

add_action( 'save_post', 'tfsb_save_custom_meta_box' );
function tfsb_save_custom_meta_box( $post_id ) {
	if ( isset( $_POST['_price'] ) ) {
		update_post_meta( $post_id, '_price', sanitize_text_field( $_POST['_price'] ) );
	}
}

// set single template for CPT
add_filter( 'single_template', 'tfsb_set_single_template' );
function tfsb_set_single_template( $file ) {
	global $post;
	if ( 'tf-service-booking' == $post->post_type ) {
		$file_path = plugin_dir_path( __FILE__ ) . 'single-tf-service-booking.php';
		$file      = $file_path;
	}
	return $file;
}
