<?php

/**
 * @var array $items
 */

use yii\helpers\Url;

$checked = function($url) {
    return \Yii::$app->getRequest()->getUrl() == Url::to($url) ? 'checked="checked"' : '';
};

?>
<div id="mylibrary-menu" class="no-selection">
	<?php for ($i = 0; $i < count($items); $i++): ?>
  <?php $id = "menu_$i"; ?>
	<input id="<?= $id;?>" type="radio" value="<?= Url::to($items[$i]['link']); ?>" <?= $checked($items[$i]['link']); ?> />
	<label for="<?= $id;?>"><?= $items[$i]['title']; ?></label>
	<?php endfor; ?>
</div>
