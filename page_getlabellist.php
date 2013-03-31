<?php

$ajaxPage = true;

$labels = Query("select * from {dis_data} where labeltype={0} or labeltype = {1} order by addr", LABEL_FUNC, LABEL_DATA);

while($label = Fetch($labels))
{
	echo "<tr><td>", makeLabelTag($label), "</tr></td>";
}

