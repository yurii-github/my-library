<?php
use yii\helpers\Html;
use \yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Books */
/* @var $form yii\widgets\ActiveForm */

$cfg = [
	'id' => 'book-form',
	'layout' => 'horizontal', // 'class' => 'form-horizontal'
	'fieldConfig' => [
		//'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
		'horizontalCssClasses' => [
			'label' =>  'col-sm-2'
		]
	],
	'options' => [
		'role' => 'form', //for readers
		'enctype' => 'multipart/form-data' // for uploads
	]
];
$btn_title = $model->isNewRecord ? 'create' : 'save';
$btn_class = $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary';
?>
<div class="row">
<?php $form = ActiveForm::begin($cfg); ?>
	<div class="col-sm-3">
		<img class="img-responsive img-thumbnail" src="<?php echo Url::to(['site/cover', 'id'=> $model->book_guid]);?>" />
	</div>
	<div class="col-sm-6">
		<?php echo $form->field($model, 'book_cover')->fileInput([ 'previewFileType' => 'image']); ?>
		<?php echo $form->field($model, 'title'); ?>
		<?php echo $form->field($model, 'read')->dropDownList(['no','yes']); ?>
		<?php echo $form->field($model, 'favorite')->dropDownList([0,1,2,3,4,5]); ?>
		<?php echo $form->field($model, 'year')->textInput(['maxlength' => 4]); ?>
		<?php echo $form->field($model, 'isbn13'); ?>
		<?php echo $form->field($model, 'author'); ?>
		<?php //echo $form->field($model, 'publisher'); ?>
		<?php echo $form->field($model, 'ext'); ?>
		<?php echo Html::submitInput($btn_title,  ['class' => $btn_class.' btn-block']); ?>
	</div>
<?php ActiveForm::end(); ?>
</div>