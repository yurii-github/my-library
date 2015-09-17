USERS : TODO
<br><br><br>
PERMISSIONS
<table id="roles-list"></table>

<script type="text/javascript">
// http://stackoverflow.com/a/12294020
var roles_list =  $('#roles-list');
var data = [
	<?php foreach ($roles as $name => $r): ?>
	{
		"role": "<?php echo $name?>",
		<?php foreach ($perms as $p) {
			$set = !empty($r[$p->name]) ? 'X':'';
			echo "\"{$p->name}\":\"$set\",";
		} ?>
	},
	<?php endforeach; ?>   
];

roles_list.jqGrid({
	datatype: 'local',
	data: data,
	cmTemplate: { sortable: false, editable: false, align: 'center'},
	colNames: [
		'<b>Roles</b>',
		<?php foreach ($perms as $p) { echo "\"{$p->name}\","; } ?>
	],
	colModel: [
		{ name:'role' },
		<?php foreach ($perms as $p) { echo "{ name: '{$p->name}' },"; } ?>
	]
});

roles_list.jqGrid('setGroupHeaders', {
	useColSpanStyle: true, 
	groupHeaders:[ //TODO: if no permissions
		{ startColumnName: '<?php echo array_keys($perms)[0]; ?>', numberOfColumns: <?php echo count($perms); ?>, titleText: '<center><b>Permissions</b></center>'},
	]
});
</script>

