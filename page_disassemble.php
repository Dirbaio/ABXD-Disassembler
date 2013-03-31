<?php

$ajaxPage = true;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
	<title>Disassembler</title>
	<?php include("header.php"); ?>
</head>

<body style="margin:0;padding:0;width:100%; height:100%; font-size: <?php print $loguser['fontsize']; ?>%;">

	<div id="labellist_container" class="discontainer" style="left: 0; width: 200px;">
		<table id="labellist" class="listing">
		</table>
	</div>

	<div id="disassembly_container" class="discontainer" style="left: 210px; width: 600px;">
		<table id="disassembly" class="disassembly listing">
		</table>
	</div>
	<div id="info_container" class="discontainer" style="left: 820px; width: 300px;">
		<textarea>lol</textarea>
		<input type="text" />
	</div>

	<div id="loading_container" class="discontainer dimbackground" style="left: 0px; width: 100%;">
		<img style="position:absolute; left:45%; top:40%" src="./plugins/disassembler/ajax-loader.gif">
	</div>
	<script type="text/javascript">
		gotoAddress(0);
		loadLabelList();
		endLoad();
	</script>

</body>
</html>
