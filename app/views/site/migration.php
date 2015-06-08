<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div style="font-size: larger; margin: auto; width: 500px">
<b>If migration was successful, go to <?php echo Html::a('Library',['site/index']); ?></b>
<br/><br/>

<p>
You can always run it manually by <i>yii2.bat migrate/up</i> to create schema for new database or update to latest version
</p>
<hr/>

<?php echo $content; ?>
</div>