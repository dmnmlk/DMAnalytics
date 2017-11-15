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

function dmnmlk_get_action_types()
{
	global $wpdb;
	
	$action_type_table_name = dmnmlk_get_action_type_table_name();
	
	$sql = "SELECT * 
			FROM $action_type_table_name
	";	

	$dbData = $wpdb->get_results($sql);
	return (array) $dbData;
}

function dmnmlk_get_standard_statistic($tab_id, $range)
{
	global $wpdb;
	$result = [];
	
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	
	$tab_id = (!empty($tab_id) ? $tab_id : '1');
	$range = (!empty($range) ? $range : 'last_week');

	list($startDate, $endDate, $groupBy) = dmnmlk_get_sql_from_range($range);

	$sql = "SELECT count(*) as liczba, date as data
			FROM $statistic_table_name
			WHERE id_action_type = $tab_id
			AND UNIX_TIMESTAMP(date) >= $startDate
			AND UNIX_TIMESTAMP(date) <= $endDate
			GROUP BY $groupBy
	";

	$dbData = $wpdb->get_results($sql);

	

	foreach ($dbData as $dayStatistic)
	{
		$dateObj = new DateTime($dayStatistic->data);
		$dateFormat = dmnmlk_get_date_format($range);
		$data = date_format($dateObj, $dateFormat);

		$result[] = [
			$data,
			$dayStatistic->liczba
		];
	}
	var_dump($result);
	$result = dmnmlk_add_zeros_to_result($result, $range);

	return (array) $result;
}

function dmnmlk_get_sql_from_range($range)
{
	switch($range)
	{
		case 'last_year' :
			$start_date = strtotime( date( 'Y-01-01', current_time( 'timestamp' ) ) );
			$end_date = strtotime( 'today 22:00', current_time( 'timestamp' ) );
			$group_by_query = 'YEAR(date), MONTH(date)';
			return [$start_date, $end_date, $group_by_query];
		case 'last_month' :
			$first_day_current_month = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
			$start_date = strtotime( date( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) );
			$end_date = strtotime( date( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ) );
			$group_by_query = 'YEAR(date), MONTH(date), DAY(date)';
			return [$start_date, $end_date, $group_by_query];
		case 'this_month' :
			$start_date = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
			$end_date = strtotime( 'today 22:00', current_time( 'timestamp' ) );
			$group_by_query = 'YEAR(date), MONTH(date), DAY(date)';
			return [$start_date, $end_date, $group_by_query];
		case 'last_week' :
		default:
			$start_date = strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) );
			$end_date = strtotime( 'today 22:00', current_time( 'timestamp' ) );
			$group_by_query = 'YEAR(date), MONTH(date), DAY(date)';
			return [$start_date, $end_date, $group_by_query];
	}
}

function dmnmlk_add_zeros_to_result($stat, $range)
{
	$result = [];
	$gap = dmnmlk_get_date_gap($range);
	$dateFormat = dmnmlk_get_date_format($range);
	list($startDate, $endDate) = dmnmlk_get_sql_from_range($range);

	$start = date("Y-m-d", $startDate);
	$end = date("Y-m-d", $endDate);

	$startDate = new \DateTime($start);
	$endDate = new \DateTime($end);

	for($i = $startDate; $i <= $endDate; $i->modify('+1 '.$gap))
	{
		$label = date($dateFormat, $i->getTimestamp());
		$result[$label] = [ $label, 0 ];
	}

	foreach($stat as $value)
	{
		if(array_key_exists($value[0], $result))
		{
			$result[$value[0]][1] = $value[1];
		}
	}
	return $result;
}

function dmnmlk_get_extended_statistic($type, $range)
{
	global $wpdb;
	$result = [];
	
	$type = (!empty($type) ? $type : '1');
	$range = (!empty($range) ? $range : 'last_week');
	
	$viewArray = dmnmlk_get_total_views_per_range($range);
	
	switch($type)
	{	
		case 1 :
			return dmnmlk_get_total_views_per_range($range);
			break;
	}
	
}

function dmnmlk_get_total_views_per_range($range)
{
	global $wpdb;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$gap = dmnmlk_get_date_gap($range);
	$dateFormat = dmnmlk_get_date_format($range);
	$time_in_sec = 300;
	
	list($startDate, $endDate, $groupBy) = dmnmlk_get_sql_from_range($range);


	$sql = "SELECT count(date) as liczba, date as data
			FROM $statistic_table_name
			WHERE UNIX_TIMESTAMP(date) >= $startDate
			AND UNIX_TIMESTAMP(date) <= $endDate
			GROUP BY $groupBy
	";

	$dbData = $wpdb->get_results($sql);


	foreach ($dbData as $dayStatistic)
	{	
		$dateObj = new DateTime($dayStatistic->data);
		$dateFormat = dmnmlk_get_date_format($range);
		$data = date_format($dateObj, $dateFormat);

		$result[] = [
			$data,
			$dayStatistic->liczba
		];
	}

	$result = dmnmlk_add_zeros_to_result($result, $range);
	
	return $result;
}