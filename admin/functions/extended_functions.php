<?php

function dmnmlk_check_if_procent_from_type($type)
{
	switch ($type)
	{
		case 1:
			return false;
		case 2:
			return true;
		case 3:
			return true;
		case 4:
			return false;
		case 5:
			return false;
		case 6:
			return false;
		case 7:
			return false;
		case 8:
			return true;
	}
}

function dmnmlk_get_percent_label($type)
{
	$czyProcent = dmnmlk_check_if_procent_from_type($type);
	
	if($czyProcent)
	{
		return ["Procent", "#0.##", "{y} %"];
	}
	else 
	{
		return ["Liczba", "#0.##", "{y}"];
	}
}

function dmnmlk_get_extended_statistic($type, $range)
{
	global $wpdb;
	$result = [];
	
	$type = (!empty($type) ? $type : '1');
	$range = (!empty($range) ? $range : 'last_week');
	
	switch($type)
	{	
		case 1 :
			return dmnmlk_get_total_views($range);
			break;
		case 2 :
			return dmnmlk_get_conversion_rate($range);
			break;
		case 3 :
			return dmnmlk_get_rejection_rate($range);
			break;
	}
	
}

function dmnmlk_get_total_views($range)
{
	global $wpdb;
	$statistic_table_name = dmnmlk_get_statistic_table_name();
	$gap = dmnmlk_get_date_gap($range);
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
	$gap = dmnmlk_get_date_gap($range);
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