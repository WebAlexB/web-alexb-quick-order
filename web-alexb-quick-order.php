<?php
/*
Plugin Name: Web Alexb Quick Order
Description: One Click Buy
Version: 1.0
Author: webalexb
*/

/**
 * email
 */
require_once 'configure/email/email.php';

$plugin_path = plugin_dir_url( __FILE__ );

function add_quick_order_button_to_cart() {
	$contact_form = get_field( 'add_shortcode_contact_form', 'option' );
	$ip           = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$api_url      = "";
	$response     = file_get_contents( $api_url );
	$ip_details   = json_decode( $response );

	if ( isset( $ip_details->country ) && $ip_details->country == 'UA' ) { ?>
		<div class="quick_order" data-modal="#quick-order-modal">
			<a href="#" class="quick-order-click">Швидке замовлення</a>
		</div>
		<div id="quick-order-modal">
			<?php
			if ( ! empty( $contact_form ) ) {
				echo do_shortcode( $contact_form );
			} ?>
		</div>
	<?php }
}

add_action( 'woocommerce_after_mini_cart', 'add_quick_order_button_to_cart' );

wp_register_style( 'web-alexb-one-click-style', $plugin_path . '/assets/css/web-alexb.css', array(), null, 'all' );
wp_enqueue_style( 'web-alexb-one-click-style' );
wp_register_script('web-alexb-one-click-script', $plugin_path . '/assets/js/web-alexb.js', array('jquery'), null, true);
wp_enqueue_script('web-alexb-one-click-script');

