<?php

// function returns number corresponding to web browser order in plugin table in database
function dmnmlk_detect_web_browser()
{
	global $is_iphone;
	global $is_chrome;
	global $is_safari;
	global $is_opera;
	global $is_gecko; //firefox
	global $is_IE;
	global $is_edge;
	
	if($is_iphone) return 1;
	elseif($is_chrome) return 2;
	elseif($is_safari) return 3;
	elseif($is_opera) return 4;
	elseif($is_gecko) return 5;
	elseif($is_IE) return 6;
	elseif($is_edge) return 7;
	else return 8;
}

// function returns boolean value, checks if action is somehow duplicated
function dmnmlk_is_action_duplicated( $array_to_insert )
{
	global $wpdb;
	
	$time_in_sec_to_block_action = 300; // time in sec for freezing selected actions
	
	$statistic_table_name = dmnmlk_get_statistic_table_name();

	$sql = "SELECT date 
			FROM $statistic_table_name
			WHERE id_action_type = %d
			AND id_user = %d
			AND id_web_browser = %d
			AND product_qty = %s
			AND product_ids = %s
			AND order_id = %s
	";	

	$dbData = $wpdb->get_results( 
					$wpdb->prepare( 
						$sql, 
						$array_to_insert['id_action_type'], 
						$array_to_insert['id_user'], 
						$array_to_insert['id_web_browser'], 
						$array_to_insert['product_qty'], 
						$array_to_insert['product_ids'], 
						$array_to_insert['order_id'] 
					) 
			  );

	if(!empty($dbData))
	{
		foreach($dbData as $value)
		{
			$dateDB = strtotime($value->date);
			$dateInserted = strtotime($array_to_insert['date']);
			$dateDiff = $dateInserted - $dateDB;

			if ($dateDiff <= $time_in_sec_to_block_action)
			{
				return true; // block selected action
			}
		}
	}

	return false; // default don't block selected action
}

// adding record to database when product is added to cart
add_action( 'woocommerce_add_to_cart', 'dmnmlk_add_to_cart_action', 10, 6 );
function dmnmlk_add_to_cart_action($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) 
{
	global $wpdb;
	$array_to_insert = [];
	$action_type = 1;
	$statistic_table_name = dmnmlk_get_statistic_table_name();

	$id_user = NULL;
	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}
	
	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time('mysql'),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => $quantity,
		'product_ids' => $product_id,
		'order_id' => 0		
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))
	{
		$wpdb->insert(
			$statistic_table_name, 
			$array_to_insert
		);
	}
}

// adding record to database when product card is viewed
add_action( 'woocommerce_before_single_product', 'dmnmlk_product_card_action' );
function dmnmlk_product_card_action()
{
	global $product;
	global $wpdb;
	$array_to_insert = [];
	$action_type = 2;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$id_user = NULL;

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}

	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time('mysql'),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => 0,
		'product_ids' => $product->get_id(),
		'order_id' => 0
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))	
	{
		$wpdb->insert(
			$statistic_table_name, 
			$array_to_insert
		);
	}
}

// adding record to database when product is deleted from card
add_action( 'woocommerce_remove_cart_item', 'dmnmlk_delete_product_from_cart_action', 10, 2 );
function dmnmlk_delete_product_from_cart_action( $cart_item_key, $cart )
{
	global $wpdb;
	$array_to_insert = [];
	$action_type = 3;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$id_user = NULL;

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}

	$product_id = $cart->cart_contents[ $cart_item_key ]['product_id']; 

	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time( 'mysql' ),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => 0,
		'product_ids' => $product_id,
		'order_id' => 0
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))	
	{
		$wpdb->insert(
			$statistic_table_name,
			$array_to_insert
		);
	}
}

// adding record to database when coupon is added to cart
add_action( 'woocommerce_applied_coupon', 'dmnmlk_add_coupon_code_action' );
function dmnmlk_add_coupon_code_action()
{
	global $wpdb;
	$array_to_insert = [];
	$action_type = 4;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$id_user = NULL;

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}

	foreach ( WC()->cart->get_cart() as $cart_item ) 
	{
		$product_qtys[] = $cart_item['quantity'];
		$product_ids[] = $cart_item['product_id'];
	}
	$product_qtys = implode(',',$product_qtys);
	$product_ids = implode(',',$product_ids);
	
	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time( 'mysql' ),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => $product_qtys,
		'product_ids' => $product_ids,
		'order_id' => 0
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))
	{
		$wpdb->insert(
			$statistic_table_name,
			$array_to_insert
		);
	}
}

// adding record to database when customer reaches first step in cart
add_action( 'woocommerce_before_cart', 'dmnmlk_cart_action' );
function dmnmlk_cart_action()
{
	global $wpdb;
	$array_to_insert = [];
	$product_qtys = [];
	$product_ids = [];
	$action_type = 5;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$id_user = NULL;

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}

	foreach ( WC()->cart->get_cart() as $cart_item ) 
	{
		$product_qtys[] = $cart_item['quantity'];
		$product_ids[] = $cart_item['product_id'];
	}
	$product_qtys = implode(',',$product_qtys);
	$product_ids = implode(',',$product_ids);

	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time('mysql'),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => $product_qtys,
		'product_ids' => $product_ids,
		'order_id' => 0
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))
	{
		$wpdb->insert(
			$statistic_table_name, 
			$array_to_insert
		);
	}
}

// adding record to database when customer reaches second step in cart
add_action( 'woocommerce_before_checkout_form', 'dmnmlk_checkout_action' );
function dmnmlk_checkout_action()
{
	global $wpdb;
	$array_to_insert = [];
	$product_qtys = [];
	$product_ids = [];
	$action_type = 6;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$id_user = NULL;

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}

	foreach ( WC()->cart->get_cart() as $cart_item ) 
	{
		$product_qtys[] = $cart_item['quantity'];
		$product_ids[] = $cart_item['product_id'];
	}
	$product_qtys = implode(',',$product_qtys);
	$product_ids = implode(',',$product_ids);

	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time('mysql'),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => $product_qtys,
		'product_ids' => $product_ids,
		'order_id' => 0
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))
	{
		$wpdb->insert(
			$statistic_table_name, 
			$array_to_insert
		);
	}
}

// adding record to database when customer reaches third step in cart
add_action( 'woocommerce_thankyou', 'dmnmlk_place_order_action', 10, 1 );
function dmnmlk_place_order_action($order_id)
{
	global $wpdb;
	$array_to_insert = [];
	$action_type = 7;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$id_user = NULL;

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$id_user = $current_user->ID;
	}

	$array_to_insert = 
	[
		'ID' => '',
		'date' => current_time('mysql'),
		'id_action_type' => $action_type,
		'id_user' => $id_user,
		'id_web_browser' => dmnmlk_detect_web_browser(),
		'product_qty' => 0,
		'product_ids' => 0,
		'order_id' => $order_id
	];
	
	if(!dmnmlk_is_action_duplicated($array_to_insert))
	{
		$wpdb->insert(
			$statistic_table_name, 
			$array_to_insert
		);
	}
}

function dmnmlk_get_date_ranges()
{
	return [
		1 => ['last_year','Ostatni rok'],
		2 => ['last_month','Poprzedni miesiąc'],
		3 => ['this_month','Ten miesiąc'],
		4 => ['last_week','Ostatni tydzień']
	];
}

function dmnmlk_get_date_format($range)
{
	if ($range == 'last_year')
	{
		return 'M';
	}
	else
	{
		return 'j M';
	}
}

function dmnmlk_get_date_gap($range)
{
	if ($range == 'last_year')
	{
		return 'month';
	}
	else
	{
		return 'day';
	}
}
