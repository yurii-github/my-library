<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Books */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="books-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->book_guid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->book_guid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <img src="<?php echo Url::toRoute(['site/cover', 'id' => $model->book_guid]);?>" />
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'book_guid',
            'created_date',
            'updated_date',
           // 'book_cover',
            'favorite',
            'read',
            'year',
            'title',
            'isbn13',
            'author',
            'publisher',
            'ext',
            'filename:ntext',
        ],
    ]) ?>

</div>
