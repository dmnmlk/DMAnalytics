<?php

function dmnmlk_get_statistic_table_name()
{
	global $wpdb;
	return $wpdb->prefix . 'dmnmlk_statistic';
}

function dmnmlk_get_action_type_table_name()
{
	global $wpdb;
	return $wpdb->prefix . 'dmnmlk_action_type';
}

function dmnmlk_get_web_browser_table_name()
{
	global $wpdb;
	return $wpdb->prefix . 'dmnmlk_web_browser';
}

// adding plugin tables
function dmnmlk_create_tables()
{
	dmnmlk_create_web_browser_table();
	dmnmlk_create_action_type_table();
	dmnmlk_create_statistic_table();
}

function dmnmlk_create_statistic_table()
{
	global $wpdb;
	
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$action_type_table_name = dmnmlk_get_action_type_table_name();
	$web_browser_table_name = dmnmlk_get_web_browser_table_name();
	
	$users_table_name = $wpdb->prefix . 'users';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sqlStatistic = "CREATE TABLE $statistic_table_name (
		ID mediumint(10) NOT NULL AUTO_INCREMENT,
		date datetime NOT NULL,
		id_action_type mediumint(10) NOT NULL,
    	id_user bigint(20) unsigned,
		id_web_browser mediumint(3) NOT NULL,		
		product_qty varchar(250),
		product_ids varchar(250),
		order_id varchar(55),
		FOREIGN KEY (id_action_type) 
			REFERENCES $action_type_table_name(ID)
			ON UPDATE CASCADE 
			ON DELETE CASCADE,
		FOREIGN KEY (id_user) 
			REFERENCES $users_table_name(ID)
			ON UPDATE CASCADE 
			ON DELETE CASCADE,
		FOREIGN KEY (id_web_browser) 
			REFERENCES $web_browser_table_name(ID)
			ON UPDATE CASCADE 
			ON DELETE CASCADE,
		PRIMARY KEY (ID)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sqlStatistic );		
}

function dmnmlk_create_action_type_table()
{
	global $wpdb;

	$action_type_table_name = dmnmlk_get_action_type_table_name();

	$charset_collate = $wpdb->get_charset_collate();

	$sqlActionType = "CREATE TABLE $action_type_table_name (
		ID mediumint(10) NOT NULL AUTO_INCREMENT,
		action_name varchar(50) NOT NULL,
		full_action_name varchar(200) NOT NULL,
		PRIMARY KEY  (ID)
	) $charset_collate;";	
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sqlActionType );
}

function dmnmlk_create_web_browser_table()
{
	global $wpdb;

	$web_browser_table_name = dmnmlk_get_web_browser_table_name();

	$charset_collate = $wpdb->get_charset_collate();

	$sqlWebBrowser = "CREATE TABLE $web_browser_table_name (
		ID mediumint(10) NOT NULL AUTO_INCREMENT,
		web_browser_name varchar(50) NOT NULL,
		PRIMARY KEY  (ID)
	) $charset_collate;";	
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sqlWebBrowser );
}


// droping plugin tables
function dmnmlk_drop_tables()
{
	dmnmlk_drop_statistic_table();
	dmnmlk_drop_action_type_table();
	dmnmlk_drop_web_browser_table();
}

function dmnmlk_drop_statistic_table()
{
	global $wpdb;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$sqlc = "DROP TABLE IF EXISTS $statistic_table_name";
	$wpdb->query($sql);
}

function dmnmlk_drop_action_type_table()
{
	global $wpdb;
	$action_type_table_name = dmnmlk_get_action_type_table_name();
	$sql= "DROP TABLE IF EXISTS $action_type_table_name";
	$wpdb->query($sql);
}

function dmnmlk_drop_web_browser_table()
{
	global $wpdb;
	$web_browser_table_name = dmnmlk_get_web_browser_table_name();
	$sql= "DROP TABLE IF EXISTS $web_browser_table_name";
	$wpdb->query($sql);
}


// inserting data inside plugin tables
function dmnmlk_insert_data() 
{
	dmnmlk_insert_action_type_data();
	dmnmlk_insert_web_browser_data();
}

function dmnmlk_insert_action_type_data()
{
	global $wpdb;
	
	$action_type_table_name = dmnmlk_get_action_type_table_name();	
	
	$insert_data = 
	[
		1 => [1, 'add_product_to_cart','Dodanie produktu do koszyka'],
		2 => [2, 'show_product_cart','Wyświetlanie karty produktu'],
		3 => [3, 'delete_product_from_cart','Usunięcie produktu z koszyka'],
		4 => [4, 'add_coupon_in_cart','Dodanie rabatu w koszyku'],
		5 => [5, 'cart','Przejście do koszyka'],
		6 => [6, 'checkout','Przejście do realizacji'],
		7 => [7, 'place_order','Złożenie zamówienia']
	];

	foreach($insert_data as $row)
	{
		$wpdb->insert( 
			$action_type_table_name, 
			array( 
				'ID' => $row[0],
				'action_name' => $row[1],
				'full_action_name' => $row[2],
			) 
		);
	}

	unset($insert_data);
}

function dmnmlk_insert_web_browser_data()
{
	global $wpdb;
	
	$web_browser_table_name = dmnmlk_get_web_browser_table_name();
	
	$insert_data =
	[
		1 => [1, 'iPhone Safari'],
		2 => [2, 'Google Chrome'],
		3 => [3, 'Safari'],
		4 => [4, 'Opera'],
		5 => [5, 'Firefox'],
		6 => [6, 'Internet Explorer'],
		7 => [7, 'Microsoft Edge'],
		8 => [8, 'Inna przeglądarka']
	];
	
	foreach($insert_data as $row)
	{
		$wpdb->insert( 
			$web_browser_table_name, 
			array( 
				'ID' => $row[0],
				'web_browser_name' => $row[1]
			) 
		);
	}
	
	unset($insert_data);
}

