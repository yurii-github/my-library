<?php use yii\helpers\Url; ?>
<div id="mylibrary-menu" style="-webkit-user-select: none; /* Chrome/Safari */        
-moz-user-select: none; /* Firefox */
-ms-user-select: none; /* IE10+ */ user-select: none;">
	<?php for ($i = 0; $i < count($items); $i++): ?>
	<input id="menu_<?=$i;?>" type="radio" 
		value="<?php echo Url::to($items[$i]['link']);?>" <?php echo (\Yii::$app->getRequest()->getUrl() == Url::to($items[$i]['link']) ? 'checked="checked"' : ''); ?> />
	<label <?= (!empty($items[$i]['style']) ? 'style="'.$items[$i]['style'].'"' : ''); ?> for="menu_<?=$i;?>"
		<?= (!empty($items[$i]['id']) ? 'id="'.$items[$i]['id'].'"' : ''); ?>
	><?= $items[$i]['title']; ?></label>
	<?php endfor; ?>
</div>
