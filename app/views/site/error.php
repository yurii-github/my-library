<?php /* @var $exception Exception */ ?>

ERROR!!!!<br />
<br/>
code: <?= $exception->getCode()?>
<br/>
message: <?= $exception->getMessage();?>
<br/>
file: <?=$exception->getFile()?> : <?=$exception->getLine()?>
