<?php

get_header();
?>
	<div class="row">
		<div class="col-md-8">
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</div>
		<div class="col-md-4">
			<?php
				global $product;
				$product = wc_get_product( get_the_ID() );

			if ( ! is_null( $product ) && $product->is_in_stock() ) {
				echo $product->get_price_html();
			} else {
				_e( 'Product not found or out of stock.' );
			}

			?>
			<form action="" method="post" enctype="multipart/form-data">
				<input name="add-to-cart" type="hidden" value="<?php echo $post->ID; ?>" />
				<input name="quantity" type="number" value="1" min="1"  />
				<button type="submit" name="add-to-cart" value="<?php echo get_the_ID(); ?>"><?php _e( 'Add to cart' ); ?></button>
			</form>
		</div>
	</div>

<?php
get_footer();