<?php 
use yii\helpers\Url;

/* @var $cfg \frontend\components\Configuration */
$cfg =  \Yii::$app->mycfg;
$checked = 'checked="checked"';
$html_valid = '<span style="display: inline-block;" class="status ui-icon ui-icon-circle-check"></span>';

$get_themes = [
	'base',
	'black-tie',
	'blitzer',
	'cupertino',
	'dark-hive',
	'dot-luv',
	'eggplant',
	'excite-bike',
	'flick',
	'hot-sneaks',
	'humanity',
	'le-frog',
	'mint-choc',
	'overcast',
	'pepper-grinder',
	'redmond',
	'smoothness',
	'south-street',
	'start',
	'sunny',
	'swanky-purse',
	'trontastic',
	'ui-darkness',
	'ui-lightness',
	'vader'
];
?>
<style type="text/css">
label.cfg {
	width: 150px;
	display: inline-block;
}
form.configuration-form input[type="text"] {
	width: 550px;
	display: inline-block;
}
form.configuration-form fieldset {
	margin-bottom: 10px;
}
form.configuration-form fieldset legend, form.configuration-form fieldset label { text-transform: capitalize; }
</style>

<div id="tabs" style="width: 800px; margin: auto; text-align: left;">
	<ul>
		<li><a href="#tabs-1"><?php echo \Yii::t('frontend/config', 'settings'); ?></a><span style="display: inline-block;" class="status ui-icon ui-icon-wrench"></span></li>
		<li><a href="#tabs-3"><?php echo \Yii::t('frontend/config', 'syncronization'); ?></a><span style="display: inline-block;" class="status ui-icon ui-icon-refresh"></span></li>
		<li><a href="<?= Url::to(['config/users']);?>"><?php echo \Yii::t('frontend/config', 'users'); ?></a></li>
	</ul>

	<div id="tabs-1">
		<form action="<?php echo Yii::$app->urlManager->createUrl('config/save'); ?>" method="post" class="configuration-form">
			<fieldset>
				<legend>&nbsp;<?php echo \Yii::t('frontend/config', 'system'); ?>&nbsp;</legend>
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'enable email'); ?></label>
				<div style="display: inline-block;" id="system_email">
					<input type="radio" id="system_email1" name="system_email" value="1" <?= ($cfg->system->email == true ? $checked : ''); ?> />
					<label for="system_email1"><?php echo \Yii::t('frontend/config', 'yes'); ?></label>
					<input type="radio" id="system_email2" name="system_email" value="0" <?= ($cfg->system->email == false ? $checked : ''); ?> />
					<label for="system_email2"><?php echo \Yii::t('frontend/config', 'no'); ?></label>
				</div>
				<br /><br />
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'email'); ?></label>
				<input name="system_emailto" id="system_emailto" type="text" title="email address where send email" value="<?= $cfg->system->emailto; ?>" />
				<br /><br />
				<label class="cfg" title="interface language"><?php echo \Yii::t('frontend/config', 'language')?></label>
				<select name="system_language" id="system_language">
					<?php foreach ([['en-US', 'English'], ['uk-UA', 'Ukrainian'] ] as $lang) { ?>
					<option <?= $cfg->system->language == $lang[0] ? 'selected="selected"' : ''; ?> value="<?= $lang[0]; ?>"><?= $lang[1]; ?> - <?= $lang[0]; ?></option>
					<?php } ?>
				</select> (ICU support: <a href="http://site.icu-project.org/">v.<?php echo INTL_ICU_VERSION?></a>)
				<br /><br />
				<label class="cfg" title="library theme"><?php echo \Yii::t('frontend/config', 'theme'); ?></label>
				<select name="system_theme" id="system_theme" >
					<?php foreach ($get_themes as $t) { ?>
					<option <?= $cfg->system->theme == $t ? 'selected="selected"' : ''; ?>><?= $t; ?></option>
					<?php } ?>
				</select>
				<br /><br />
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'time zone'); ?></label>
				<select name="system_timezone" id="system_timezone" title="PHP timezone used to show and store data">
				<?php foreach (DateTimeZone::listIdentifiers() as $dt) : ?>
				<option <?= ($cfg->system->timezone == $dt ? 'selected="selected"' : '');?>><?=$dt ?></option>
				<?php endforeach; ?>
				</select>
			</fieldset>
			<fieldset>
				<legend>&nbsp;<?php echo \Yii::t('frontend/config', 'library'); ?>&nbsp;</legend>
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'directory'); ?></label>
				<input name="library_directory" id="library_directory" type="text" title="Location of your books. Must end with '\' or '/' " value="<?= $cfg->library->directory; ?>" />
				<br /><br />
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'syncronization'); ?></label>
				<div style="display: inline-block;" id="library_sync">
					<input type="radio" id="library_sync1" name="library_sync" value="1" <?= ($cfg->library->sync == true ? $checked : ''); ?> />
					<label for="library_sync1"><?php echo \Yii::t('frontend/config', 'yes'); ?></label>
					<input type="radio" id="library_sync2" name="library_sync" value="0" <?= ($cfg->library->sync == false ? $checked : ''); ?> />
					<label for="library_sync2"><?php echo \Yii::t('frontend/config', 'no'); ?></label>
				</div>
				<br /><br />
				<label class="cfg">Filename Codepage</label>
				<select name="library_codepage" id="library_codepage" title="FileSystem codepage used for filename storage.">
					<?php foreach (array('cp1251'=> 'Windows(Cyrillic)','cp1252'=>'Windows(Latin)','utf-8' => 'Unicode') as $k => $v) { ?>
					<option value="<?php echo $k; ?>" <?= ($cfg->library->codepage == $k ? 'selected="selected"' : ''); ?>><?php echo "$k - $v"; ?></option>
					<?php } ?>
				</select>
			</fieldset>
			<fieldset>
				<legend>&nbsp;<?php echo \Yii::t('frontend/config', 'database'); ?>&nbsp;</legend>
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'DB format'); ?></label>
				<select id="database_format" name="database_format">
				<?php foreach (['sqlite','mysql'] as $f): ?>
					<option <?php echo ($cfg->database->format == $f) ? 'selected="selected"' : ''?>><?php echo $f; ?></option>
				<?php endforeach; ?>
				</select>
				<br/></br/>
				
				<fieldset id="database_format_sqlite">
					<label class="cfg"><?php echo \Yii::t('frontend/config', 'file'); ?></label>
					<input name="database_filename" id="database_filename" type="text" value="<?= $cfg->database->filename; ?>" />
				</fieldset>
				
				<fieldset id="database_format_mysql">
					<label class="cfg"><?php echo \Yii::t('frontend/config', 'dbname'); ?></label>
					<input name="database_dbname" id="database_dbname" type="text" value="<?= $cfg->database->dbname; ?>" />
					<label class="cfg"><?php echo \Yii::t('frontend/config', 'host'); ?></label>
					<input name="database_host" id="database_host" type="text" value="<?= $cfg->database->host; ?>" />
					<label class="cfg"><?php echo \Yii::t('frontend/config', 'login'); ?></label>
					<input name="database_login" id="database_login" type="text" value="<?= $cfg->database->login; ?>" />
					<label class="cfg"><?php echo \Yii::t('frontend/config', 'password'); ?></label>
					<input name="database_password" id="database_password" type="text" value="<?= $cfg->database->password; ?>" />
				</fieldset>
				
				<br /><br />

			</fieldset>
			<fieldset>
				<legend><?php echo \Yii::t('frontend/config', 'book'); ?></legend>
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'name format'); ?></label>
				<input name="book_nameformat" type="text" value="<?= $cfg->book->nameformat; ?>" />
				<br /><br />
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'cover type'); ?></label>
				<select name="book_covertype" id="book_covertype" title="All images will be saved as JPEG to database">
					<option><?= $cfg->book->covertype; ?></option>
				</select>
				<br /><br />
				<label class="cfg"><?php echo \Yii::t('frontend/config', 'cover max width, px'); ?></label>
				<input id="book_covermaxwidth" name="book_covermaxwidth" type="text" value="<?= $cfg->book->covermaxwidth; ?>" />
			</fieldset>
		</form>		
		<div id="result-message"></div>		
	</div>

	<div id="tabs-3">
		<span id="sync-check-files" title="Find mismatched records in database and library directory">check files (safe)</span>
		<span id="sync-import-fs-files" title="Import unmatched filenames to database. Filenames are stored into title and filename columns. Use Check Files to see what will be imported">import fs only (possible duplicates)</span>
		<span id="sync-clear-db-files" title="Removes records from databases that don't have matched real book files.">clear unmatched db files (unsafe!)</span>
		<div id="sync-check-files-result"></div>
	</div>


</div>

<script>
$(document).tooltip();

// CONFIGURATION PARAMS
//
// radio buttons
//
$("#system_email, #library_sync, #system_debug").buttonset();
$('input[name="system_email"], input[name="library_sync"], input[name="system_debug"]').on('click', function(e) {
	saveParameter(this);
});
//
// text inputs
//
$("#system_emailto, #system_language, #system_theme, #system_timezone, #system_sessionpath, #library_directory, #database_format, input[id^='database_'], #book_covermaxwidth, #library_codepage")
.on('focusout', function (e) {	
	saveParameter(this);
});

//
// db swtich
//
$('#database_format').on('change', function(e){
	var format = $(this).val();
	console.log('database_format change ' + $(this).val());
	toggleDbForm(format);
});
toggleDbForm($('#database_format').val());

function toggleDbForm(format)
{
	$('[id^="database_format_"]').hide();
	$('#database_format_'+format).show();
}



$("#sync-check-files, #sync-import-fs-files, #sync-clear-db-files").button();


// status error - 0 | info - 1
function setResultMsg(message, title, result)
{
	if (message == '') {
		return;
	}
	
	var state = (result == 1 ? 'highlight' : 'error');
	var icon = (result == 1 ? 'info' : 'alert');
	var msg = $('#result-message');
	
	msg.html(
		'<div class="ui-state-'+state+' ui-corner-all" style="padding: 10px; margin-top: 20px; margin-bottom: 20px;">' +
		'<p><span class="ui-icon ui-icon-'+icon+'" style="float: left; margin-right: .3em;"></span> ' +
		'<b>'+title+'</b>&nbsp;&nbsp; '+message+'</p>' +
		'</div>');
}


function saveParameter(e)
{
	var action_url = $(e).closest("form").attr('action');
	var field = $(e).attr('name');
	var value = $(e).val();
	
	$.post(action_url, {field: field, value: value}, function (data) {
		setResultMsg(data.msg, data.title, data.result);
		if (data.result && (field == 'system_theme' || field == 'system_language')) {
			location.reload();
		}
	}, 'json');
}



//sync-clear-db-files
$('#sync-clear-db-files').click(function(){
	var res = $('#sync-check-files-result');

	// get number of unmatched records for progress bar
	$.get('<?= Yii::$app->urlManager->createUrl(['config/clear-db-files', 'count'=>'all']);?>', function(data) {
		res.empty();
		var records_to_remove = parseInt(data);
		var records_removed = 0;
		
		if(records_to_remove == 0) {
			res.html('<p>nothing to clear from database</p>');
			return;
		}

		res.append('<br/><br/><span></span><br/><br/><progress/>');
		var bar = $('progress', res);
		var span = $('span', res);
		bar.css('width', res.css('width'));
		var width = parseInt(bar.css('width'));
		bar.attr('max', records_to_remove);
		bar.attr('value', 0);
		span.text(records_removed+'/'+records_to_remove);
		var stepping = Math.ceil(width /records_to_remove);

		var batcher = function(stepping) {
			$.get('<?= Yii::$app->urlManager->createUrl(['config/clear-db-files']);?>?stepping='+stepping, function(data) {
				if (data.length > 0 && bar.val() < bar.attr('max')) {
					bar.attr('value', bar.val()+data.length);
					records_removed += data.length;
					span.text(records_removed+'/'+records_to_remove);
					batcher(stepping);
				} else {
					res.append('<br/><p><b>Database was cleared from unmatched records</b></p>' + '<ul>');
				}
				console.log(data);
			}, 'json');
		};

		batcher(stepping);
	}, 'json');
});


//---------------------
$('#sync-import-fs-files').click(function(){
	// get fs files only filenames
	$.get('<?php echo Yii::$app->urlManager->createUrl(['config/import-files']);?>', function(data) {
		console.table(data);
		var records_total = data.length;
		var records_done = 0;
		var res = $('#sync-check-files-result');
		res.empty();
		if(records_total == 0) {
			res.html('<p>nothing to do</p>');
			return;
		}
		res.append('<br/><br/><progress/><br/><br/><span id="counter"></span><span id="message"></span>');
		var bar = $('progress', res);
		var span_counter = $('span#counter', res);
		var span_message = $('span#message', res);
		bar.css('width', res.css('width'));
		var width = parseInt(bar.css('width'));
		bar.attr('max', records_total);
		bar.attr('value', records_done);
		var stepping = 1; // items on 1 request
		span_counter.text(records_done + '/' + records_total);
			
		var batcher = function(stepping) {
			var post = data.slice(records_done, records_done+stepping);
			if(post.length <= 0) {
				span_message.text(' Action was successful');
				return;
			}			
			$.post('<?php echo Yii::$app->urlManager->createUrl(['config/import-files']);?>',
				{ post: post}, function(response) {
					//console.log(response);
				if (response.result) { //continue adding
					records_done += post.length;
					bar.attr('value', records_done);
					span_counter.text(records_done + '/' + records_total);
					span_message.text('');
					for(var i=0;i<response.data.length;i++){
						span_message.append('<p>'+response.data[i]+'</p>');
					}
					batcher(stepping);
				} else {
					//error or success
					span_message.text('<p>'+response.error+'</p>');
					return;
				}
			}, 'json');
		};
		
		batcher(stepping);
	}, 'json');
});


$('#sync-check-files').click(function(){
	var res = $('#sync-check-files-result');
	
	$.get('<?php echo Yii::$app->urlManager->createUrl('config/check-files');?>', function(data) {
		res.empty();

		if (data.fs == 0 && data.db == 0) {
			res.html('<p>Great news! Your library is synced already. Keep it up.</p>');
			return;
		}
		
		res.append('<br /><p><b>FileSystem only records (files)</b></p>' + '<ul>');
		for(var i = 0; i < data.fs.length; i++) {
			res.append('<li>'+data.fs[i]+'</li>');
		}
		res.append('</ul><br />');
		
		res.append('<p><b>DB only records</b></p>' + '<ul>');
		for(var i = 0; i < data.db.length; i++) {
			res.append('<li>'+data.db[i]+'</li>');
		}
		res.append('</ul><br />');
	},
	'json');
});


  $(function() {
	    $( "#tabs" ).tabs({
	      beforeLoad: function( event, ui ) {
	        ui.jqXHR.error(function() {
	          ui.panel.html("Couldn't load this tab. Please create a bug report");
	        });
	      }
	    });
});
</script>




