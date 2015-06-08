<?php 
/* @var $this \yii\web\view */
/* @var $message yii\swiftmailer\Message */
$imgdir = $this->context->viewPath . "/images/";

////TODO: hangs when file does not exist
$img = function($file) use ($message, $imgdir) {
	if (file_exists($imgdir.$file)) {
		return $message->embed($imgdir.$file);
	} else {
		throw new \yii\base\Exception('file does not exist '.$imgdir.$file);
	}
};

?>
<style type="text/css">
.twitter {
	display: inline-block;
	width: 23px; height: 19px;
	background-image: url("<?php echo $img('twitter.jpg');?>");
}
footer {text-align: center; }
</style>
<div style="width: 600px; background-color: white; margin: auto;">
	<header></header>
	<article><?php echo $content; ?></article>
	<footer><a class="twitter" href="https://twitter.com"></a></footer>
</div>


