<?php
use modules\apc\models\APCu;
use yii\web\UrlManager;
use yii\helpers\Url;
use modules\apc\Module;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use modules\apc\models\CachedVariable;
use yii\widgets\Pjax;
use yii\grid\DataColumn;

//$this->registerAssetBundle('yii\jui\JuiAsset');
/* @var $this yii\web\View */

/* @var $m modules\apc\Module */
/*
if (@modules\apc\Module::getInstance()->jui) {
	
}*/
?>

<script>
$(function() {
	$( "#tabs" ).tabs();
});
</script>

	<style>
	.apcu-table { float: left; }
	.apcu-table td, .apcu-table th { border: 1px solid; padding: 5px }
	.apcu-table th { font-weight: bold; text-align: center; }
</style>	
<div id="tabs" style="width: 800px; margin: auto; text-align: left;">
	<ul>
		<li><a href="#tabs-1">General Info</a></li>
		<li><a href="#tabs-2">Proin dolor</a></li>
		<li><a href="#tabs-3">Aenean lacinia</a></li>
	</ul>
	<div id="tabs-1">


	<?php 
	//$dp = new ActiveDataProvider();
	//apcu_clear_cache();
	
	$dp = new ArrayDataProvider([
		'allModels' => apcu_cache_info()['cache_list'],
		'pagination' => ['defaultPageSize' => 8]
		]);
	
	/*Pjax::begin([
		
		'id' => 'apcu-variables-list',
		'options' => ['class' => 'ui-jqgrid ui-widget ui-widget-content ui-corner-all',], //div
		'enablePushState' => false,
		//'timeout' => false,
		'clientOptions' => ['method' => 'POST']]);
	echo GridView::widget([
		
		'options' => [ 'class' => 'ui-jqgrid-view',], // div
		'tableOptions' => ['class' => 'ui-jqgrid-htable'], // ['tr' => ['class'=>]],// [ 'class' => 'datagrid',], //table
		//'rowOptions' => ['class'=> 'ui-state-default ui-corner-top'],
		'headerRowOptions' => ['class'=> 'ui-jqgrid-labels ui-sortable'],//'ui-state-default ui-corner-top'],
		'dataProvider' => $dp,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'class' => DataColumn::className(),
				'attribute' => 'info',
				'label' => 'name'
			],
			[
				'class' => DataColumn::className(),
				'attribute' => 'creation_time',
				'format' => ['date', 'php:Y-m-d H:i:s'],
				'label' => 'created'
			],
			[
			'class' => DataColumn::className(),
			'attribute' => 'mem_size',
				'format' => 'raw',
				'label' => 'size',
				'value' => function ($m) { return APCu::bsize($m['mem_size']); } // { return json_encode($m);}
			],
		]
	]);
	Pjax::end();*/
	?>
	


	
	<table class="apcu-table">
		<tr class="ui-state-default ui-corner-top"><th colspan="2">Info</th></tr>
		<tr><td><?= $apcu->getAttributeLabel('apcu_version'); ?></td><td><?= $apcu->apcu_version; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('php_version'); ?></td><td><?= $apcu->php_version; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('memory_type'); ?></td><td><?= $apcu->memory_type; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('memory_total_size'); ?></td><td><?=  round($apcu->memory_total_size/1048576,2); ?> Mb (<?= $apcu->memory_segments ?>x<?= APCu::bsize($apcu->memory_segment_size)?>)</td></tr>
		<tr><td><?= $apcu->getAttributeLabel('start_time'); ?></td><td><?= $apcu->start_time; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('uptime'); ?></td><td><?= $apcu->uptime; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('file_upload_progress'); ?></td><td><?= $apcu->file_upload_progress; ?></td></tr>
		<tr class="ui-state-default ui-corner-top"><th colspan="2">Cache</th></tr>
		<tr><td><?= $apcu->getAttributeLabel('entries'); ?></td><td><?= $apcu->entries; ?> (<?= APCu::bsize($apcu->variables_size); ?>)</td></tr>
		<tr><td><?= $apcu->getAttributeLabel('hits'); ?></td><td><?= $apcu->hits; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('misses'); ?></td><td><?= $apcu->misses; ?></td></tr>
		<tr><td><?= $apcu->getAttributeLabel('inserts'); ?></td><td><?= $apcu->inserts; ?></td></tr>
		<tr class="ui-state-default ui-corner-top"><th colspan="2">Runtime Settings</th></tr>
		<?php foreach (ini_get_all('apcu') as $k => $v) : ?>
		<tr><td><?= $k ?></td><td><?= $v['local_value']; ?></td></tr>
		<?php endforeach; ?>
	</table>
	<table class="apcu-table">
	<tr class="ui-state-default ui-corner-top"><th style="width:400px">Memory Usage</th></tr>
	<tr><td><div id="chart_memory-usage"></div></td></tr>
	<tr class="ui-state-default ui-corner-top"><th>Hits &amp; Misses</th></tr>
	<tr><td><div id="chart_hitmiss"></div></td></tr>
	</table>
	<div style="clear: left;"></div>

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load("visualization", "1", {packages:['corechart','table']});

$(document).ready(function(){

	function drawMemoryUsage(data)
	{
		var chart = new google.visualization.PieChart(document.getElementById('chart_memory-usage'));
		var tb = google.visualization.arrayToDataTable(data);
		var options =  { title: null, chartArea: { top:5, left:0, width: '100%'},
			width: '100%', is3D: false, sliceVisibilityThreshold: 0, colors: ['green', 'red',],
			legend: { position: 'bottom', alignment: 'center' } };
		
		chart.draw(tb, options);
	}


	function drawHitAndMiss(data)
	{
		var chart = new google.visualization.BarChart(document.getElementById('chart_hitmiss'));
		var tb = google.visualization.arrayToDataTable(data);
		var options =  { legend: { position: 'none' }, width: '100%'};
		
		chart.draw(tb, options);
	}


	function drawTable(data) {
		var tb = google.visualization.arrayToDataTable(data);
		var table = new google.visualization.Table(document.getElementById('apcu-cached-variables'));
		table.draw(data, {showRowNumber: true});
/*
		  google.visualization.events.addListener(table, 'select', function() {
		    var row = table.getSelection()[0].row;
		    alert('You selected ' + data.getValue(row, 0));
			  });*/
	}



	
	drawChart();

	
	function drawChart() {
		$.get('<?= Url::to(['default/charts']);?>', function(resp) {
			console.log(resp.usage);
			drawMemoryUsage(resp.usage);
			drawHitAndMiss(resp.hitmiss);
			drawTable(resp.variables);
		},'json');
	}

	




	











	
});
</script>



	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	<?php 
	

	//grid
	?>
	</div>
	<div id="tabs-2">
<div id="apcu-cached-variables"></div>
	</div>
	<div id="tabs-3">
		<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti.
			Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat,
			eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent
			taciti sociosqu ad litora torquent per conubia nostra, per inceptos
			himenaeos. Fusce sodales. Quisque eu urna vel enim commodo
			pellentesque. Praesent eu risus hendrerit ligula tempus pretium.
			Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
		<p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper
			at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo
			vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti.
			Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros,
			id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero
			sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat
			porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu
			tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit.
			Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
	</div>
</div>

<?php 
//$cache = apcu_cache_info();

//$mem = apcu_sma_info();
//var_dump($cache, $mem);

/*


*/
//apc_cache_info()

?>