<?php
/*
 * Template Name: Page template
*/

get_header();
?>

<h1><?php echo the_title( 'Service Booking ', '' ); ?></h1>

<?php

/**
 * Create ajax search form
 */
?>
<form role="search" id="tfsb_form" action="" method="post">
	<label>
		<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label' ); ?></span>
		<input type="search" class="title search-field"
			placeholder="<?php echo esc_attr_x( 'Search post here...', 'placeholder' ); ?>"
			value="<?php echo get_search_query(); ?>" name="s"
			title="<?php echo esc_attr_x( 'Search for:', 'label' ); ?>" />
	</label>
	<input type="submit" name="submit" class="search-submit"
		value="<?php echo esc_attr_x( 'Search', 'submit button' ); ?>" />
		<input type="hidden" name="post_type" value="tf-service-booking" />
</form>

<?php

// get_search_form();

if ( have_posts() ) :

	echo '<div class="load-more-posts">';
	$posts_per_page = -1;

	$query = array(
		'post_status'    => 'publish',
		'posts_per_page' => $posts_per_page,
		'paged'          => 1,
		'post_type'      => 'tf-service-booking',
	);

	$query = new WP_Query( $query );

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

echo '<br/>';
get_footer();