<?php
use yii\helpers\Html;
use app\components\widgets\MenuWidget;

/* @var $this \yii\web\View */
/* @var $content string */
$this->title = "MyLibrary ~ {$this->title}";
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language ?>">
<head>
<title><?php echo Html::encode($this->title) ?></title>
<link type="image/x-icon" rel="shortcut icon" href="<?php echo Yii::getAlias('@web/assets/app/logo.ico');?>" />
  <?php echo Html::csrfMetaTags(); ?>
  <?php $this->head(); ?>

  <link type="text/css" href="<?= Yii::getAlias('@web/3rd/yui-reset-3.5.0.css');?>" rel="stylesheet" />
  <link type="text/css" href="<?= Yii::getAlias('@web/3rd/jquery-ui-themes-1.12.1/themes/'.\Yii::$app->mycfg->system->theme.'/jquery-ui.css');?>" rel="stylesheet">
  <link type="text/css" href="<?= Yii::getAlias('@web/3rd/jquery-ui-themes-1.12.1/themes/'.\Yii::$app->mycfg->system->theme.'/theme.css');?>" rel="stylesheet">
  <link type="text/css" href="<?= Yii::getAlias('@web/3rd/fancybox-2.1.5/source/jquery.fancybox.css');?>" rel="stylesheet">
  <link type="text/css" href="<?= Yii::getAlias('@web/3rd/raty-2.8.0/lib/jquery.raty.css');?>" rel="stylesheet" />
  <link type="text/css" href="<?= Yii::getAlias('@web/3rd/jqgrid-4.6.0/ui.jqgrid.css');?>" rel="stylesheet" />
  <link type="text/css" href="<?= Yii::getAlias('@web/assets/app/css/style.css');?>" rel="stylesheet">

  <script src="<?= Yii::getAlias("@web/3rd/jquery-2.2.4/jquery.min.js");?>"></script>
  <script src="<?= Yii::getAlias("@web/3rd/jquery-ui-1.12.1/jquery-ui.min.js");?>"></script>
  <script src="<?= Yii::getAlias("@web/3rd/fancybox-2.1.5/source/jquery.fancybox.pack.js");?>"></script>
  <script src="<?= Yii::getAlias("@web/3rd/raty-2.8.0/lib/jquery.raty.js");?>"></script>
  <script src="<?= Yii::getAlias("@web/3rd/jqgrid-4.6.0/jquery.jqGrid.min.js");?>"></script>
  <script src="<?= Yii::getAlias("@web/3rd/jqgrid-4.6.0/i18n/grid.locale-".(['uk-UA' => 'ua'][\Yii::$app->language] ?? 'en').".js");?>"></script>
  <script src="<?= Yii::getAlias('@web/3rd/js.cookie.js');?>"></script>

</head>
<body>
<?php $this->beginBody(); ?>
<header style="margin: auto !important; text-align: center; position: relative;">
	<form style="margin: 30px;">
	<?php echo MenuWidget::widget([
		'items' => [//link, title, style, id
			['link' => ['//site/index'], 'title' => \Yii::t('frontend/site', 'Library')],
			['link' => ['//config/index'], 'title' => \Yii::t('frontend/site', 'Configuration')],
			['link' => ['//config/vacuum'], 'title' => \Yii::t('frontend/site', 'Compact'), 'id' => 'vacuum-link'],
			['link' => ['//site/about'], 'title' => \Yii::t('frontend/site', 'About {version}', ['version' => 'v.'.\Yii::$app->mycfg->version])],
		]]);
	?>
	</form>
</header>
<?php echo $content; ?>
<footer></footer>
<script>
	$("#mylibrary-menu").buttonset();
	$("#mylibrary-menu input").click(function() {
		window.location.href = $(this).val();
	});


	//
	//
	$("#vacuum-link").on("click", function(e) {
		e.preventDefault();
		var $menuItem = $(this);
		var link = $menuItem.prev().val();
		var menuText = $menuItem.children('span').text();//backup menu text

		// dummy no multi click
		if ($menuItem.prop('disabled')) {
			return false;
		}
		$menuItem.prop('disabled', true);
		$('span',this).text('<?php echo \Yii::t('frontend/site', 'doing...')?>');
		console.log('compacting...');
		var xhr = $.post(link, function(data) {
			alert(data);
		});
		xhr.always(function(){
			$menuItem.children('span').text(menuText);
			$menuItem.removeClass('ui-state-active');
			$menuItem.prop('disabled', false);
		});
	});
</script>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();
