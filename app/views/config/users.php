<?php

/*ui-jqgrid-htable
 * 
 * ui-state-default ui-th-column ui-th-ltr ui-sortable-handle
 * ui-widget-content jqgrow ui-row-ltr
 */
?>
<table id="roles-list"></table>

<script type="text/javascript">
var roles_list =  $('#roles-list');
var data = [
	<?php foreach ($data as $name => $r): ?>
	{
		role: '<?=$name?>',
		<?php foreach ($perms as $p): ?>
		"<?=$p->name?>": "<?= (!empty($r[$p->name]) ? 'X':'');?>",
		<?php endforeach; ?>
	},
	<?php endforeach; ?>   
];

roles_list.jqGrid({
	datatype: 'local',
	data: data,
	cmTemplate: { sortable: false, editable: false},
	colNames: [
		'roles',
		<?php foreach ($perms as $p): ?>
		'<?=$p->name?>',
		<?php endforeach; ?>
	],
	colModel: [
		{ name:'role' },
		<?php foreach ($perms as $p): ?>
		{ name: '<?=$p->name?>' } ,
		<?php endforeach; ?>
	]
});
</script>