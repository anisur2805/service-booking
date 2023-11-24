<li class="tfsb_single_post" data-id="<?php echo get_the_ID(); ?>">
	<div class="tfsb-result-content">
		<h3 class="tfsb-listing-title">
			<a href="<?php the_permalink( get_the_ID() ); ?>"><?php the_title(); ?></a>
		</h3>
		<div class="tfsb-post-excerpt"><?php echo get_the_excerpt(); ?></div>
		<a href="<?php the_permalink( get_the_ID() ); ?>"><?php _e( 'Read Moe' ); ?></a>
	</div>
</li>