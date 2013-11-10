<?php
/*
Plugin Name: Add Products
Description: Product Functionality with Stripe, including pre-configured payment form that accepts membership and general product tags.
Author: Steven Stevenson
Author URI: http://stevenandleah.com/
*/
/* Add your functions below this line */
add_action( 'init', 'ssc_register_product_posts' );
add_action('admin_menu','ss_register_product_menu_settings');
add_action('admin_init', 'register_product_settings');

function ss_register_product_menu_settings() {
	add_options_page('Product Settings', 'Product Settings', 'manage_options', 'ss_product_options_menu', 'ss_product_options_page');
	include('settings.php');
}

function register_product_settings() {
	register_setting('ss_product_options_group', 'ss_product_options', 'ss_products_options_validate' );
}

function ssc_register_product_posts() {

/* Labels for the product post type. */
	$product_labels = array(
		'name' => __( 'Products'),
		'singular_name' => __( 'Product'),
		'add_new' => __( 'Add New'),
		'add_new_item' => __( 'Add New Product'),
		'edit' => __( 'Edit'),
		'edit_item' => __( 'Edit Product'),
		'new_item' => __( 'New product'),
		'view' => __( 'View Product'),
		'view_item' => __( 'View Product'),
		'search_items' => __( 'Search Products'),
		'not_found' => __( 'No products found'),
		'not_found_in_trash' => __( 'No products found in Trash'),
	);

	/* Arguments for the product post type. */
	$product_args = array(
		'labels' => $product_labels,
		'capability_type' => 'post',
		'public' => true,
		'can_export' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'products', 'with_front' => false ),
		'supports' => array( 'title', 'editor', 'custom-fields', 'thumbnail' )
	);

	/* Register the product post type. */
	register_post_type( 'product', $product_args );

	add_theme_support( 'post-thumbnails', array('product') ); 
}
/* add payment-form shortcode */
include('paymentForm.php');
?>