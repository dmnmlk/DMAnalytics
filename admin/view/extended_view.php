<?php

function dmnmlk_admin_subpage_extended_html()
{
	$current_range = ( ! empty( $_GET['range'] ) ) ? esc_attr( $_GET['range'] ) : "last_week";
	$current_type = $_GET['type'];
	$arr = dmnmlk_get_extended_data();
	$current_name = $arr[$current_type][0];
	$totalValue = dmnmlk_total_value($current_type, $current_range, 0);
    ?>
    <div class="wrap">
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<?php if (!isset($_GET['type'])): ?>
	<h1><?= esc_html(get_admin_page_title()); ?></h1>
	<ul>
		<?php foreach(dmnmlk_get_extended_data() as $k => $v):
			$raw_url = admin_url( 'admin.php?page=dma_extended&type=' . $k . '&range=' . urlencode( $current_range ));
			$esc_url = esc_url( $raw_url );
			echo '<li><a style ="width: 25%" class="button button-secondary" href="' . $esc_url . '">' . $v[0] . '</a></li>';
		endforeach; ?>
	</ul>
<?php else: ?>
<?php switch ($_GET['type']): ?>
<?php default: ?>
	<h1><?php echo $current_name ?> <a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=dma_extended');?>">Powrót</a></h1>
	
	<?php
		foreach ( dmnmlk_get_date_ranges() as $date_range ) :
			if ( $current_range == $date_range[0] ) :
				$stat = dmnmlk_get_extended_statistic($current_type, $current_range);
				$dataPoints = "[";
				foreach($stat as $dayStatistic)
				{
					$dataPoints .= "{ label: '".$dayStatistic[0]."', y:".$dayStatistic[1]."},";
				}
				$dataPoints .= "]";
				
				//pobiera labele puste albo dla wartości procentowych
				list($axisY, $percentFormatString, $toolTipContent) = dmnmlk_get_percent_label($current_type);
	?>
	<script type="text/javascript">
	window.onload = function () {
		var chart = new CanvasJS.Chart("chartContainer", {
			backgroundColor: "#f1f1f1",
			title:{
				text: "<?php echo $action_type->full_action_name ?>"              
			},
			axisY:{
				title: "<?php echo $axisY ?>",
			},
			axisX:{
				title:"Okres",
			},
			data: [              
			{
				type: "line",
				percentFormatString: "<?php echo $percentFormatString ?>",
				toolTipContent: "<?php echo $toolTipContent ?>",
				dataPoints: <?php echo $dataPoints ?>
			}
			]
		});
		chart.render();
	}	
	</script>

	<?php
			endif;
		endforeach;
	?>
	<h2>Wybierz okres do wyświetlenia:</h2>
	<nav class="nav-tab-wrapper">
		<?php
			foreach ( dmnmlk_get_date_ranges() as $range ) 
			{
				echo '<a href="' . esc_url( remove_query_arg( array( 'start_date', 'end_date' ), add_query_arg( 'range', $range[0] ) ) ) . '" class="nav-tab ';
				if ( $current_range == $range[0] ) {
					echo 'nav-tab-active';
				}
				echo '">' . esc_html( $range[1] ) . '</a>';
			}

			do_action( 'wc_reports_tabs' );
		?>
	</nav>
	<h3><?php echo $totalValue ?></h3>
	<div id="chartContainer" style="margin-left: 5%; height: 80%; width: 90%;"></div>
<?php break; ?>
	

<?php endswitch; ?>
<?php endif; ?>
    </div>
    <?php
}