<?php /* @var $exception Exception */ ?>

ERROR!!!!<br />
<br/>
code: <?= $exception->getCode()?>
<br/>
message: <?= $exception->getMessage();?>
<br/>
file: <? echo $exception->getFile() . ' : ' . $exception->getLine();
