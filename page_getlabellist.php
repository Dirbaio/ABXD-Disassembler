<?php

$ajaxPage = true;

$labels = Query("select * from dis_data where labeltype=".LABEL_FUNC." or labeltype=".LABEL_DATA." order by addr");

while($label = Fetch($labels))
{
	echo "<tr><td>", makeLabelTag($label), "</tr></td>";
}

