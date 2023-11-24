<?php

add_action( 'wp_loaded', 'ts_service_booking_load_after_woocommerce' );

function ts_service_booking_load_after_woocommerce() {
	if ( class_exists( 'WC_Product' ) ) {
		class TFSB_Woo_Product extends WC_Product {

			protected $post_type = 'tf-service-booking';

			public function get_type() {
				return 'tf-service-booking';
			}

			public function __construct( $product = 0 ) {
				$this->supports[] = 'ajax_add_to_cart';

				parent::__construct( $product );
			}
		}
	}

	if ( class_exists( 'WC_Product_Data_Store_CPT' ) ) {
		class TFSB_Data_Store_CPT extends WC_Product_Data_Store_CPT {

			public function read( &$product ) {
				$product->set_defaults();
				$post_object = get_post( $product->get_id() );

				if ( ! $product->get_id() || ! $post_object || 'tf-service-booking' !== $post_object->post_type ) {

					throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
				}

				$product->set_props(
					array(
						'name'              => $post_object->post_title,
						'slug'              => $post_object->post_name,
						'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
						'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
						'status'            => $post_object->post_status,
						'description'       => $post_object->post_content,
						'short_description' => $post_object->post_excerpt,
						'parent_id'         => $post_object->post_parent,
						'menu_order'        => $post_object->menu_order,
						'reviews_allowed'   => 'open' === $post_object->comment_status,
					)
				);

				$this->read_attributes( $product );
				$this->read_downloads( $product );
				$this->read_visibility( $product );
				$this->read_product_data( $product );
				$this->read_extra_data( $product );
				$product->set_object_read( true );
			}
		}
	}

	if ( class_exists( 'WC_Order_Item_Product' ) ) {

		class TFSB_WC_Order_Item_Product extends WC_Order_Item_Product {

			public function set_product_id( $value ) {
				if ( $value > 0 && 'tf-service-booking' !== get_post_type( absint( $value ) ) ) {
					$this->error( 'order_item_product_invalid_product_id', __( 'Invalid product ID', 'woocommerce' ) );
				}
				$this->set_prop( 'product_id', absint( $value ) );
			}
		}
	}
}

function tfsb_woocommerce_data_stores( $stores ) {
	$stores['product-tf-service-booking'] = 'TFSB_Data_Store_CPT';
	return $stores;
}

add_filter( 'woocommerce_data_stores', 'tfsb_woocommerce_data_stores', 11, 1 );

function TFSB_Woo_product_class( $class_name, $product_type, $product_id ) {
	if ( $product_type == 'tf-service-booking' ) {
		$class_name = 'TFSB_Woo_Product';
	}

	return $class_name;
}

add_filter( 'woocommerce_product_class', 'TFSB_Woo_product_class', 25, 3 );

function my_woocommerce_product_get_price( $price, $post ) {
	if ( $post->post_type === 'post' ) {
		$price = get_post_meta( $post->id, 'price', true );
	}
	return $price;
}

add_filter( 'woocommerce_product_get_price', 'my_woocommerce_product_get_price', 20, 2 );
add_filter( 'woocommerce_product_get_price', 'my_woocommerce_product_get_price', 10, 2 );

function TFSB_Woo_product_type( $false, $product_id ) {
	if ( $false === false ) {
		global $post;
		if ( is_object( $post ) && ! empty( $post ) ) {
			if ( $post->post_type == 'tf-service-booking' && $post->ID == $product_id ) {
				return 'tf-service-booking';
			} else {
				$product = get_post( $product_id );
				if ( is_object( $product ) && ! is_wp_error( $product ) ) {
					if ( $product->post_type == 'tf-service-booking' ) {
						return 'tf-service-booking';
					}
				}
			}
		} elseif ( wp_doing_ajax() ) {
			$product_post = get_post( $product_id );
			if ( $product_post->post_type == 'tf-service-booking' ) {
				return 'tf-service-booking';
			}
		} else {
			$product = get_post( $product_id );
			if ( is_object( $product ) && ! is_wp_error( $product ) ) {
				if ( $product->post_type == 'tf-service-booking' ) {
					return 'tf-service-booking';
				}
			}
		}
	}
	return false;
}

add_filter( 'woocommerce_product_type_query', 'TFSB_Woo_product_type', 12, 2 );

function tfsb_woocommerce_checkout_create_order_line_item_object( $item, $cart_item_key, $values, $order ) {

	$product = $values['data'];
	if ( $product->get_type() == 'tf-service-booking' ) {
		return new TFSB_WC_Order_Item_Product();
	}
	return $item;
}

add_filter( 'woocommerce_checkout_create_order_line_item_object', 'tfsb_woocommerce_checkout_create_order_line_item_object', 20, 4 );

function cod_woocommerce_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
	if ( $values['data']->get_type() == 'tf-service-booking' ) {
		$item->update_meta_data( '_tf-service-booking', 'yes' );
		return;
	}
}

add_action( 'woocommerce_checkout_create_order_line_item', 'cod_woocommerce_checkout_create_order_line_item', 20, 4 );

function tfsb_woocommerce_get_order_item_classname( $classname, $item_type, $id ) {
	global $wpdb;
	$is_IA = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = {$id} AND meta_key = '_tf-service-booking'" );

	if ( 'yes' === $is_IA ) {
		$classname = 'TFSB_WC_Order_Item_Product';
	}
	return $classname;
}

add_filter( 'woocommerce_get_order_item_classname', 'tfsb_woocommerce_get_order_item_classname', 20, 3 );
