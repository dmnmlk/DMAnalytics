<?php

function dmnmlk_get_extended_data()
{
	return [
		1 => ['Liczba odsłon'],
		2 => ['Współczynnik konwersji e-commerce'],
		3 => ['Całkowity współczynnik odrzuceń'],
		4 => ['Skuteczność produktu'],
		5 => ['Skuteczność sprzedaży'],
		6 => ['Łączna kwota udzielonych rabatów'],
		7 => ['Użycie przeglądarek']
	];
}

function dmnmlk_get_extended_labels_sum($stat)
{
	$result = 0;
	foreach($stat as $dayStatistic)
	{
		$result += $dayStatistic[1];
	}
	return $result;	
}

function dmnmlk_get_extended_labels_avg($stat)
{
	$licznik = 0;
	foreach($stat as $dayStatistic)
	{
		$licznik++;
		$result += $dayStatistic[1];
	}
	return number_format($result/$licznik, 2);	
}

function dmnmlk_get_extended_labels($type, $range, $product_id)
{
	$stat = dmnmlk_get_extended_statistic($type, $range, $product_id);
	
	$avgValue = dmnmlk_get_extended_labels_avg($stat);
	$sumValue = dmnmlk_get_extended_labels_sum($stat);

	switch($type)
	{
		case 1:
			return [
				"Łączna liczba odsłon w wybranym okresie: ".$sumValue,
				"Liczba",
				"",
				"{y}",
				"line",
				""
			];
		case 2:
			return [
				"Średnia wartość procentowa współczynnika konwersji w wybranym okresie: ".$avgValue."%",
				"Procent",
				"#0.##", 
				"{y} %",
				"line",
				""
			];
		case 3:
			return [
				"Średnia wartość procentowa współczynnika odrzuceń w wybranym okresie: ".$avgValue."%",
				"Procent",
				"#0.##", 
				"{y} %",
				"line",
				""
			];
		case 4:
			return [
				"Łączna sprzedaż produktu w wybranym okresie: ".$sumValue,
				"Liczba",
				"", 
				"{y}",
				"line",
				""
			];
		case 5:
			return [
				"Łączna wartość zamówień w wybranym okresie: ".$sumValue." PLN",
				"Kwota",
				"", 
				"{y} PLN",
				"line",
				""
			];
		case 6:
			return [
				"Łączna wartość rabatów w wybranym okresie: ".$sumValue." PLN",
				"Kwota",
				"", 
				"{y} PLN",
				"line",
				""
			];
		case 7:
			return [
				"",
				"",
				"", 
				"{y} %",
				"pie",
				"{label} {y} %"
			];
	}
}

function dmnmlk_get_extended_statistic($type, $range, $product_id = NULL)
{
	global $wpdb;
	$result = [];
	
	$type = (!empty($type) ? $type : '1');
	$range = (!empty($range) ? $range : 'last_week');
	
	switch($type)
	{	
		case 1 :
			return dmnmlk_get_total_views($range);
		case 2 :
			return dmnmlk_get_conversion_rate($range);
		case 3 :
			return dmnmlk_get_rejection_rate($range);
		case 4 :
			return dmnmlk_get_products_stat($range, $product_id);
		case 5 :
			return dmnmlk_get_transaction_and_discount_stat($range, 'transaction');
		case 6 :
			return dmnmlk_get_transaction_and_discount_stat($range, 'discount');
		case 7 :
			return dmnmlk_get_web_browser_stat($range);
	}
	
}

function dmnmlk_get_total_views($range)
{
	global $wpdb;
	$statistic_table_name = dmnmlk_get_statistic_table_name();

	$dateFormat = dmnmlk_get_date_format($range);
	
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

function dmnmlk_get_conversion_rate($range)
{
	$result = [];
	
	$odslony = dmnmlk_get_total_views($range);

	//info o zamowieniach w podanym okresie
	$transakcje = dmnmlk_get_standard_statistic(7, $range);

	foreach($odslony as $odslona)
	{
		foreach($transakcje as $transakcja)
		{
			if($odslona[0] == $transakcja[0])
			{
				if ((float)$odslona[1] > 0)
				{
					$percent = number_format(((float)$transakcja[1])/((float)$odslona[1])*100, 2);

					$result[$odslona[0]] = [
						$odslona[0],
						$percent
					];
				}
				else
				{
					$result[$odslona[0]] = [
						$odslona[0],
						0
					];					
				}
			}			
		}

	}	
	return $result;
}

function dmnmlk_get_rejection_rate($range)
{
	$result = [];
	
	$odslony = dmnmlk_get_total_views($range);

	$odrzucenia = dmnmlk_get_rejection_array($range);

	foreach($odslony as $odslona)
	{
		foreach($odrzucenia as $odrzucenie)
		{
			if($odslona[0] == $odrzucenie[0])
			{
				if ((float)$odslona[1] > 0)
				{
					$percent = number_format(((float)$odrzucenie[1])/((float)$odslona[1])*100, 2);

					$result[$odslona[0]] = [
						$odslona[0],
						$percent
					];
				}
				else
				{
					$result[$odslona[0]] = [
						$odslona[0],
						0
					];					
				}
			}			
		}

	}	
	return $result;
}

function dmnmlk_get_rejection_array($range)
{
	global $wpdb;
	$statistic_table_name = dmnmlk_get_statistic_table_name();

	$dateFormat = dmnmlk_get_date_format($range);
	
	list($startDate, $endDate, $groupBy) = dmnmlk_get_sql_from_range($range);

	$sql = "SELECT count(date) as liczba, date as data
			FROM $statistic_table_name
			WHERE UNIX_TIMESTAMP(date) >= $startDate
			AND UNIX_TIMESTAMP(date) <= $endDate
			AND id_action_type NOT IN (6,7)
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

function dmnmlk_get_products()
{
	global $wpdb;
	$result = [];
	$posts_table_name = dmnmlk_get_posts_table_name();
	
	$sql = "SELECT ID, post_title
			FROM $posts_table_name
			WHERE post_type = 'product'
			ORDER BY ID asc
	";

	$products = $wpdb->get_results($sql);

	foreach ($products as $product)
	{	
		$result[$product->ID] = $product->post_title;
	}
	
	return $result;	
}

function dmnmlk_get_first_product_id()
{
	global $wpdb;
	$posts_table_name = dmnmlk_get_posts_table_name();
	
	$sql = "SELECT ID
			FROM $posts_table_name
			WHERE post_type = 'product'
			ORDER BY ID asc
			LIMIT 1
	";

	$product = $wpdb->get_results($sql);

	return $product[0]->ID;	
}

function dmnmlk_get_products_stat($range, $product_id)
{
	global $wpdb;
	$result = [];
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$wc_order_items_table_name = dmnmlk_get_wc_order_items_table_name();
	$wc_order_itemmeta_table_name = dmnmlk_get_wc_order_itemmeta_table_name();
	$dateFormat = dmnmlk_get_date_format($range);
	
	list($startDate, $endDate, $groupBy) = dmnmlk_get_sql_from_range($range);
	
	$sql = "SELECT count(im.meta_value) as liczba, s.date as data
			FROM $statistic_table_name s
			JOIN $wc_order_items_table_name i
			ON s.order_id = i.order_id
			JOIN $wc_order_itemmeta_table_name im
			ON i.order_item_id = im.order_item_id
			WHERE UNIX_TIMESTAMP(s.date) >= $startDate
			AND UNIX_TIMESTAMP(s.date) <= $endDate
			AND s.id_action_type = 7
            AND i.order_item_type = 'line_item'
            AND im.meta_key = '_product_id'
            AND im.meta_value = $product_id
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

function dmnmlk_get_transaction_and_discount_stat($range, $type)
{
	global $wpdb;
	$result = [];
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$posts_table_name = dmnmlk_get_posts_table_name();
	$postmeta_table_name = dmnmlk_get_postmeta_table_name();
	$dateFormat = dmnmlk_get_date_format($range);
	
	$metaKey = ($type == 'transaction') ? '_order_total' : '_cart_discount';

	list($startDate, $endDate, $groupBy) = dmnmlk_get_sql_from_range($range);
	
	$sql = "SELECT SUM(pm.meta_value) as suma, s.date as data
			FROM $statistic_table_name s
			JOIN $posts_table_name p
			ON s.order_id = p.ID
			JOIN $postmeta_table_name pm
			ON p.ID = pm.post_id
			WHERE UNIX_TIMESTAMP(s.date) >= $startDate
			AND UNIX_TIMESTAMP(s.date) <= $endDate
			AND s.id_action_type = 7
            AND pm.meta_key = '$metaKey'
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
			$dayStatistic->suma
		];
	}

	$result = dmnmlk_add_zeros_to_result($result, $range);
	
	return $result;	
}

function dmnmlk_get_web_browser_stat($range)
{
	global $wpdb;
	$result = [];
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$web_browser_table_name = dmnmlk_get_web_browser_table_name();
	
	list($startDate, $endDate, $groupBy) = dmnmlk_get_sql_from_range($range);

	$sql = "SELECT count(*) as liczba, wb.web_browser_name as nazwa
			FROM $statistic_table_name s
			JOIN $web_browser_table_name wb
			ON s.id_web_browser = wb.ID
			WHERE UNIX_TIMESTAMP(date) >= $startDate
			AND UNIX_TIMESTAMP(date) <= $endDate
			GROUP BY wb.web_browser_name
	";

	$dbData = $wpdb->get_results($sql);

	$count = dmnmlk_get_web_browsers_count($startDate, $endDate);

	foreach ($dbData as $web_browser)
	{	
		$result[] = [
			$web_browser->nazwa,
			number_format(($web_browser->liczba/$count)*100, 2)
		];
	}
	
	return $result;	
}

function dmnmlk_get_web_browsers_count($startDate, $endDate)
{
	global $wpdb;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	
	$sql = "SELECT count(*) as liczba
			FROM $statistic_table_name s
			WHERE UNIX_TIMESTAMP(date) >= $startDate
			AND UNIX_TIMESTAMP(date) <= $endDate
	";

	$dbData = $wpdb->get_results($sql);

	return $dbData[0]->liczba;
}