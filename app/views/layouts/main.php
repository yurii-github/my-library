<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\MenuWidget;
use app\components\widgets\LoginWidget;
use app\models\forms\Login;
/* @var $this \yii\web\View */
/* @var $content string */
$this->title = "MyLibrary ~ {$this->title}";


app\assets\App::register($this);
app\assets\Jquery::register($this);
app\assets\JqueryUI::register($this);
app\assets\JqueryRaty::register($this);
app\assets\JqueryFancybox::register($this);
app\assets\JqueryGrid::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language ?>">
<head>
<title><?php echo Html::encode($this->title) ?></title>
<link type="image/x-icon" rel="shortcut icon" href="<?php echo Yii::getAlias('@web');?>/assets/app/logo.ico" />
<?php echo Html::csrfMetaTags(); ?>
<?php $this->head(); ?>
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
		  /* TODO: show when finished with AUTH
			Yii::$app->user->isGuest ?
				['link' => ['/site/login'], 'title' => \Yii::t('frontend/site', 'Login'), 'style' => 'color: white; background: green', 'id' => 'auth-link'] :
				['link' => ['/site/logout'], 'title' => 'logout ('.Yii::$app->user->identity->username.')', 'style' => 'color: white; background: #BDB437', 'id' => 'auth-link']
			*/
		]]);
	?>
	</form>
<?php
if(\Yii::$app->user->isGuest) { // append login form
	echo $this->renderFile(__DIR__.'/_login.php');
} else {
	echo $this->renderFile(__DIR__.'/_logout.php');
}
?>
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