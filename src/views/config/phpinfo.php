<?php

/**
 * parse phpinfo into array
 *
 * @param boolean $return TRUE return as array, print otherwise
 * @return mixed array or void
 *
 * @see source from http://www.php.net/manual/en/function.phpinfo.php#87463
 */
$phpinfo_array = function($return = false) {
	ob_start();
	phpinfo(INFO_ALL);

	$pi = preg_replace(
			array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
					'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
					"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
					'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
					.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
					'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
					'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
					"# +#", '#<tr>#', '#</tr>#'),
			array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
					'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
					"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
					'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
					'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
					'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
			ob_get_clean());

	$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
	unset($sections[0]);

	$pi = array();
	foreach($sections as $section){
		$n = substr($section, 0, strpos($section, '</h2>'));
		preg_match_all(
		'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
		$section, $askapache, PREG_SET_ORDER);
		foreach($askapache as $m)
			$pi[$n][$m[1]] = (!isset($m[3])||$m[2]==$m[3]) ?
			@$m[2] : array_slice($m, 2); // my fix
	}

	return ($return === false) ? print_r($pi) : $pi;
};


$pi = $phpinfo_array(true);
?>
<style type="text/css">
table.php-info {
border-collapse:separate !important; border-spacing: 0 !important;
}

table.php-info th.module {
	font-size: 20px;
	font-weight: bold;
	text-align: center;
	padding: 20px;
	text-shadow:1px 1px 3px gray;
}
table.php-info td.parameter {  width:250px; padding-left: 5px; white-space: normal; }
table.php-info td.value { word-break:break-all; padding-left: 5px; white-space: normal; } 
</style>

<div class="ui-jqgrid ui-widget ui-widget-content ui-corner-all">
<table class="php-info ui-jqgrid-btable">
	<tbody>
	<?php foreach ($pi as $m_k => $m_v) {?>
	<tr class="ui-widget-content jqgrow ui-row-ltr">
		<th colspan="2" class="module ui-state-default jqgrid-rownum"><?php echo $m_k?></th>
	</tr>
	<?php foreach ($m_v as $p_k => $p_v) { ?>
	<tr class="ui-widget-content jqgrow ui-row-ltr">
		<td class="parameter ui-state-default jqgrid-rownum"><?php echo $p_k; ?></td>
		<td class="value"><?php if (is_array($p_v)) {
			foreach ($p_v as $val) {
	echo $val .' <br />';
}
		} else {
echo $p_v;
}
?>
		</td>
	</tr>
	<?php }?>
<?php }?>
</tbody>
</table>
</div>