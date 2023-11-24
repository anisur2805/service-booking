<?php
/*
 * Template Name: Page template
*/

get_header();

?>

<h1><?php the_title(); ?></h1>

<div>
<?php
if ( have_posts() ) :

	echo '<div class="load-more-posts">';
	$posts_per_page = get_option( 'posts_per_page' );
	$query_var      = ( isset( $_GET['keyword'] ) ) ? $_GET['keyword'] : '';
	$paged          = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
	$query          = array(
		'post_status'    => 'publish',
		'posts_per_page' => $posts_per_page,
		'paged'          => $paged,
		'post_type'      => 'tf-service-booking',
		's'              => $query_var,
	);

	$query     = new WP_Query( $query );
	$max_pages = $query->max_num_pages;

	echo '<ul>';
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) :
			$query->the_post();
			include TFSB_DIR . '/tfsb-content.php';
		endwhile;

		else :
			_e( 'No posts found' );
		endif;
		echo '</ul> </div>';
endif;


	echo '<div class="tfsb_ajax_pagination">' . paginate_links(
		array(
			'total'        => $query->max_num_pages,
			'current'      => $paged,
			'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
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
	) . '</div>';

	echo '<br/>';
	get_footer();