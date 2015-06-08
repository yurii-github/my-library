<?php


use yii\widgets\DetailView;
use yii\widgets\ListView;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;


//  'book_guid',
//   'created_date',
//  'updated_date',
//  'book_cover',
//  'favorite',
// 'read',
// 'year',
// 'title',
// 'isbn13',
// 'author',
// 'publisher',
// 'ext',
// 'filename:ntext',

?>
<div class="box box-info">
	<div class="box-header with-border">
		<h3 class="box-title">Book List</h3>
		<div class="box-tools pull-right">
			<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
			<button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
		</div>
	</div><!-- /.box-header -->
	<div class="box-body" style="display: block;">
		<div class="table-responsive">
			<?php
			Pjax::begin();
			echo GridView::widget([
				'dataProvider' => $dataProvider,
				'filterModel' => $filterModel,
				'tableOptions' => [
					'class' => 'table table-striped table-hover table-condensed'
				],
				'columns' => [
					['class' => 'yii\grid\SerialColumn'],
					['attribute' => 'updated_date', 'format' => ['date', 'php:Y-m-d']],
					['attribute' => 'created_date', 'format' => ['date', 'php:Y-m-d']],
					'title',
					'publishers.name',
					/*[
						'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
						'value' => function ($data) {
							return $data->created_date; // $data['name'] for array data, e.g. using SqlDataProvider.
						},
					],*/
					['class' => 'yii\grid\ActionColumn', 'template' => '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;{delete}'],
				]
			]);
			Pjax::end();?>
		</div>
		<div class="box-footer clearfix" style="display: block;">
			<a href="<?php echo Url::to(['create']);?>" class="btn btn-sm btn-info btn-flat pull-left">Add Book</a>
		</div><!-- /.box-footer -->
	</div>
</div>

