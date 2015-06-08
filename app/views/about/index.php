<div style="font-size: 12px; margin: auto; width: 300px; text-align: left; font-size: 14px;">
	<h1 style="text-align: center; font-size: 20px; margin-bottom: 20px">About</h1>
	<p>
		<b><a href="https://github.com/yurii-github/php-mylibrary">MyLibrary</a></b>
		 is a software to manage your book library.
	</p>
	<h1 style="text-align: center; font-size: 17px; margin: 20px 0">Used Sources &amp; Projects</h1>
	<?php
	if (!empty($projects)) {
		echo '<ul>';
		foreach ($projects as $t => $l) {
			echo "<li><a href=\"{$l}\">{$t}</a></li>";
		}
		echo '</ul>';
	}
	?>
</div>