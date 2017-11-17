<?php

function dmnmlk_admin_subpage_standard_html()
{
	$current_tab_id = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : '1';
	$current_range = ( ! empty( $_GET['range'] ) ) ? esc_attr( $_GET['range'] ) : "last_week";
	$totalValue = dmnmlk_total_value($current_tab_id, $current_range, 1);
    ?>
    <div class="wrap">
		<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
		<h2>Wybierz typ akcji do wyświetlenia na wykresie:</h2>
		<nav class="nav-tab-wrapper">
			<?php
				foreach ( dmnmlk_get_action_types() as $action_type ) 
				{
					echo '<a href="' . admin_url( 'admin.php?page=dma_standard&tab=' . urlencode( $action_type->ID ) ) . '&range=' . urlencode( $current_range ) . '" class="nav-tab ';
					if ( $current_tab_id == $action_type->ID ) {
						echo 'nav-tab-active';
					}
					echo '">' . esc_html( $action_type->full_action_name ) . '</a>';
				}
			?>
		</nav>
		<?php
			foreach ( dmnmlk_get_action_types() as $action_type ) :
				if ( $current_tab_id == $action_type->ID ) :
					$stat = dmnmlk_get_standard_statistic($current_tab_id, $current_range);

					$dataPoints = "[";
					foreach($stat as $dayStatistic)
					{
						$dataPoints .= "{ label: '".$dayStatistic[0]."', y:".$dayStatistic[1]."},";
					}
					$dataPoints .= "]";
		?>
		<script type="text/javascript">
		window.onload = function () {
			var chart = new CanvasJS.Chart("chartContainer", {
				backgroundColor: "#f1f1f1",
				title:{
					text: "<?php echo $action_type->full_action_name ?>"              
				},
				axisX:{
					title:"Okres",
				},
				axisY:{
					title:"Liczba",
				},
				data: [              
				{
					type: "line",
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
    </div>
    <?php 
}