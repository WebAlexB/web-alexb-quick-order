<?php
// added email
function get_admin_email() {
	$email_information = get_field( 'email_information', 'option' );

	return $email_information ? $email_information : '';
}

function get_name_company() {
	$name = get_field( 'name_company', 'option' );

	return $name ? $name : '';
}

// Sending order and status information
/**
 * @param $order_id
 */
function payment_notification_order( $order_id ) {
	send_order_email( $order_id );
}

add_action( 'woocommerce_checkout_order_processed', 'payment_notification_order', 10, 1 );

// sending and creating quick payment
function save_form_data_to_order( $contact_form ) {
	$submission = WPCF7_Submission::get_instance();
	$order      = wc_create_order();
	$order_id   = $order->get_id();
	$order      = wc_get_order( $order_id );
	if ( $submission ) {
		$name = sanitize_text_field( $submission->get_posted_data( 'your-name' ) );
		$tel  = sanitize_text_field( $submission->get_posted_data( 'tel-864' ) );
		$order->set_customer_id( get_current_user_id() );
		$order->set_billing_first_name( $name );
		$order->set_shipping_first_name( $name );
		$order->set_billing_phone( $tel );
		$order->update_meta_data( '_shipping_number', $tel );

		$products = array(
			array(
				'product_id' => 6680,
				'quantity'   => 1,
				'size'       => 'l',
				'color'      => 'black',
			),
			array(
				'product_id' => 6681,
				'quantity'   => 1,
				'size'       => 'm',
				'color'      => 'black',
			),
			array(
				'product_id' => 6682,
				'quantity'   => 1,
				'size'       => 's',
				'color'      => 'black',
			),
		);

		foreach ( $products as $product_data ) {
			$product_id = $product_data['product_id'];
			$quantity   = $product_data['quantity'];
			$size       = $product_data['size'];
			$color      = $product_data['color'];
			$order->update_meta_data( 'pa_size', $size );
			$order->update_meta_data( 'pa_color', $color );
			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {
				$order->add_product( $product, $quantity, array(
					'variation' => array(
						'pa_size'  => $size,
						'pa_color' => $color,
					),
				) );
			}
		}


		$order_comments = 'Швидке замовлення';
		$order->add_order_note( $order_comments );
		$order->set_total( $order->calculate_totals(), 'total' );
		$order->save();

		send_order_email( $order );
	}
}

add_action( 'wpcf7_before_send_mail', 'save_form_data_to_order' );
// sent order email

function send_order_email( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order ) {
		$warehouse_ref       = '';
		$name_company        = get_name_company();
		$order_data          = $order->get_data();
		$order_number        = $order->get_order_number();
		$order_date          = $order->get_date_created()->format( 'Y-m-d H:i:s' );
		$order_language      = $order_data['billing']['language'] ?? 'Не указан';
		$language            = ! empty( $order_language ) ? $order_language : pll_current_language();
		$currency            = $order->get_currency();
		$billing_country     = $order->get_billing_country() ? $order->get_billing_country() : 'Швидке замовлення';
		$billing_last_name   = $order->get_billing_last_name() ? $order->get_billing_last_name() : 'Швидке замовлення';
		$billing_first_name  = $order->get_billing_first_name();
		$billing_phone       = $order->get_billing_phone();
		$billing_email       = $order->get_billing_email() ? $order->get_billing_email() : 'Швидке замовлення';
		$shipping_country    = $order->get_shipping_country() ? $order->get_shipping_country() : 'Швидке замовлення';
		$shipping_last_name  = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : 'Швидке замовлення';
		$shipping_first_name = $order->get_shipping_first_name();
		$payment_result      = $order->get_payment_method_title() ? $order->get_payment_method_title() : 'Швидке замовлення';
		$shipping_method     = $order->get_shipping_method() ? $order->get_shipping_method() : 'Швидке замовлення';
		if ( $shipping_method === "Доставка кур'єром" ) {
			$postcode           = $order->get_billing_postcode();
			$selected_warehouse = $order->get_billing_address_1() . $order->get_billing_address_2();
			$selected_city      = $order->get_billing_city();
		} else {
			$postcode           = $order->get_shipping_postcode() ? $order->get_shipping_postcode() : 'Не указан';
			$selected_warehouse = $order->get_shipping_address_1() ? $order->get_shipping_address_1() : 'Швидке замовлення';
			$selected_city      = $order->get_shipping_city() ? $order->get_shipping_city() : 'Швидке замовлення';
			if ( $shipping_method === 'Новая почта' ) {
				$warehouse_ref = get_novaposhta_warehouse_ref( $selected_city, $selected_warehouse );
			}
		}
		$shipping_phone      = $order->get_meta( '_shipping_number' );
		$do_not_call         = $order->get_meta( '_no_call_back' ) ? $order->get_meta( '_no_call_back' ) : 'Швидке замовлення';
		$shipping_payment    = $order->get_shipping_total() ? $order->get_shipping_total() : 'Швидке замовлення';
		$payment_status      = $order->get_status() ? $order->get_status() : 'Швидке замовлення';
		$ip                  = $order->get_customer_ip_address();
		$customer_user_agent = $order->get_customer_user_agent();
		$device_type         = wp_is_mobile() ? 'mobile' : 'desktop';
		$os_type             = php_uname( 's' );
		$current_url         = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url         = 'https://alba-soboni.ua/uk/bags/urban-backpacks?utm_source=facebook&utm_medium=Facebook_Mobile_Feed&utm_campaign=A_C_ICh&utm_content=LAL_Base_CRM_0_3%25_Purchase_Value_0_3%25_850k&utm_term=Video_06.10_outside&fbclid=IwAR1ZMJKP2Q79NbLdwbFBqu5Ef7UKFp-WeK6OvC1coYVUbVFrtnBllz9_OfU" "Mozilla/5.0 (Linux; Android 12; SM-M315F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/106.0.5249.126 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/390.0.0.27.105;]';
		$query_string        = parse_url( $current_url, PHP_URL_QUERY );
		parse_str( $query_string, $query_params );
		$utm_source         = isset( $query_params['utm_source'] ) ? $query_params['utm_source'] : '';
		$utm_medium         = isset( $query_params['utm_medium'] ) ? $query_params['utm_medium'] : '';
		$utm_campaign       = isset( $query_params['utm_campaign'] ) ? $query_params['utm_campaign'] : '';
		$utm_content        = isset( $query_params['utm_content'] ) ? $query_params['utm_content'] : '';
		$utm_term           = isset( $query_params['utm_term'] ) ? $query_params['utm_term'] : '';
		$referrer           = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : 'Нет информации о точке входа';
		$product_data       = '';
		$price_total        = '';
		$total_order_amount = 0;
		$items              = $order->get_items();
		foreach ( $items as $item_id => $item ) {
			$product_id         = $item->get_product_id();
			$variant_id         = $item->get_variation_id();
			$product_sku        = $item->get_product() ? $item->get_product()->get_sku() : 'Не указан';
			$product_name       = $item->get_name();
			$quantity           = $item->get_quantity();
			$size_term          = $item->get_meta( 'pa_size', true );
			$size               = $size_term ? get_term_by( 'slug', $size_term, 'pa_size' )->name : 'Не указан';
			$color_term         = $item->get_meta( 'pa_color', true );
			$color              = $color_term ? get_term_by( 'slug', $color_term, 'pa_color' )->name : 'Не указан';
			$product_link       = get_permalink( $item->get_product_id() );
			$product_price      = $item->get_total();
			$amount             = $item->get_total();
			$total_order_amount += $product_price;
			$product_data       .= "\n\nНазвание товара: {$product_name}";
			$product_data       .= "\nID Родитель товара: {$product_id}";
			$product_data       .= "\nID Дочерний товара: {$variant_id}";
			$product_data       .= "\nКод товара: {$product_sku}";
			$product_data       .= "\nКоличество: {$quantity}";
			$product_data       .= "\nЦвет товара: {$color}";
			$product_data       .= "\nРазмер товара: {$size}";
			$product_data       .= "\nСсылка на товар: {$product_link}";
			$product_data       .= "\nЦена товара: {$amount}";
			$price_total        .= "\n\nОбщая сумма всех продуктов: {$total_order_amount}";
		}
		$subject = "{$name_company} Информация о заказе - Заказ:{$order_number}, Дата: {$order_date}";
		$message = "Ваш заказ находится в состоянии {$order->get_status()}";
		$message .= "\n\nНомер заказа: {$order_number}";
		$message .= "\nДата заказа: {$order_date}";
		$message .= "\nЯзык: {$language}";
		$message .= "\nВалюта: {$currency}";
		$message .= "\nСтрана плательщика: {$billing_country}";
		$message .= "\nФамилия плательщика: {$billing_last_name}";
		$message .= "\nИмя плательщика: {$billing_first_name}";
		$message .= "\nТелефон плательщика: {$billing_phone}";
		$message .= "\nE-mail плательщика: {$billing_email}";
		$message .= "\nИмя получателя: {$shipping_first_name}";
		$message .= "\nФамилия получателя: {$shipping_last_name}";
		$message .= "\nСтрана получателя: {$shipping_country}";
		$message .= "\nТелефон получателя: {$shipping_phone}";
		$message .= "\nIp: {$ip}";
//		$message         .= "\nТочка входа: {$referrer}";
//		$message         .= "\nТочка входа: {$utm_source} {$utm_medium} {$utm_campaign} {$utm_content} {$utm_term}";
		$message         .= "\nТип устройства: {$customer_user_agent}";
		$message         .= "\nОперациВид: {},онная система: {$os_type}";
		$message         .= "\nОплата за доставку: {$shipping_payment}";
		$message         .= "\nНовая Почта Ref: {$warehouse_ref}";
		$message         .= "\nИнформация о доставке: {$shipping_method}, Город: {$selected_city}, Отделение: {$selected_warehouse}, Индекс: {$postcode}";
		$message         .= "\nМетод оплаты: {$payment_result}";
		$message         .= "\nРезультат оплаты: {$payment_status}";
		$message         .= "\nНе перезванивать: {$do_not_call}";
		$message         .= "\nУстройство (mobile): {$device_type}";
		$message         .= "\nДанные о товарах: {$product_data}";
		$message         .= "\nОбщая сумма всех продуктов: {$total_order_amount}";
		$recipient_email = get_admin_email();
		wp_mail( $recipient_email, $subject, $message );
	}
}

function get_novaposhta_warehouse_ref( $city, $address ) {

	$api_key = '3e7741091e4c54f1735febb40b4ff7e0';
	$url     = "https://api.novaposhta.ua/v2.0/json/";

	// Формируем данные для запроса
	$data = array(
		'apiKey'           => $api_key,
		'modelName'        => 'AddressGeneral',
		'calledMethod'     => 'getWarehouses',
		'methodProperties' => array(
			'CityName'     => $city,
			'FindByString' => $address,
		),
	);

	$response = wp_remote_post( $url, array(
		'body'    => json_encode( $data ),
		'headers' => array( 'Content-Type' => 'application/json' ),
	) );

	if ( is_array( $response ) && ! is_wp_error( $response ) ) {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! empty( $data['success'] ) && $data['success'] === true ) {
			return $data['data'][0]['Ref'];
		} else {
			return ! empty( $data['errors'] ) ? $data['errors'][0] : 'Не удалось получить данные об отделениях.';
		}
	} else {
		return 'Не удалось выполнить запрос к API Новой Почты.';
	}
}
