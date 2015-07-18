<?php
use yii\helpers\Url;
?>

<script type="text/javascript">
$(document).ready(function(e) {

	$("#auth-link").on("click", function(e) {
		e.preventDefault();
		var link_logout = $('input', $(this).parent()).last().val();
		console.log(link_logout);

		$.post(link_logout, function(e){
			window.location.href = '<?php echo Url::to(['site/index'])?>';
		});
	});
	
});
</script>