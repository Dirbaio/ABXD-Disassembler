<?php

$ajaxPage = true;

function printAddr($label)
{
	$addr = $label["addr"];

	echo "<tr id=\"addr_$addr\" onclick=\"highlightAddress($addr, this);\">",
		"<td style=\"width:50px; text-align:right;\">", "RAM", "</td>",
		"<td style=\"width:100px;\">:", applyClass("highlight", hexNum32($addr)), "</td>";
}

$sections = Query("select * from dis_sections where 1");

$addr = (int) $_GET["addr"];

$func = Fetch(Query("select * from {dis_data} where labeltype={0} and addr <= {1} order by addr desc limit 1", LABEL_FUNC, $addr));
if(!$func)
{
	$func =  Fetch(Query("select * from {dis_data} where 1 order by addr asc limit 1"));
	$start = 0x02000000;
}
else
	$start = $func["addr"];

$maxcount = 1000;

if($start < $addr-$maxcount*2)
	$start = $addr-$maxcount*2;
	
$count = $maxcount*4;

$func2 = Fetch(Query("select * from {dis_data} where labeltype={0} and addr > {1} order by addr asc limit 1", LABEL_FUNC, $start));
if($func2)
	$count = $func2["addr"]-$start;

$count /= 4;

if($count > $maxcount)
	$count = $maxcount;

	
$instrs = Query("select * from {dis_data}
	where addr >= {0}
	order by addr asc limit {1u}", $start, $count);

while($data = Fetch($instrs))
{
	if($data["labeltype"])
	{
		$f = "";
		if($data["labeltype"] == LABEL_FUNC)
		{
			printAddr($data);
			echo "<td colspan=\"3\">", "</td><td></td></tr>";
			printAddr($data);
			echo "<td colspan=\"3\">", applyClass("syscomment comment", "=== Function ".$data["label"]." ==="), "</td><td></td></tr>";
			$f = " function";
		}
		printAddr($data);
		echo "<td colspan=\"3\">", makeLabelTag($data), ":</td><td></td></tr>";
	}
		
	$cmt = $data["comment"];
	if($cmt) $cmt = "@$cmt";
	$cmt = wordwrap($cmt, 50);
	printAddr($data);
	echo "<td style=\"width:30px;\"></td><td style=\"width:80px;\">", disassemble($data), "</td><td>", $cmt, "</td></tr>";
}
?>


<tr><td colspan="5">Page rendered in <?php print sprintf("%1.3f",usectime()-$timeStart)?> seconds with <?php print Plural($queries, __("MySQL query"))?> <br /></td></tr>
