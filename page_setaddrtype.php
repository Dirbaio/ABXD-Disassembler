<?php

$ajaxPage = true;

$addr = intval($_GET["addr"], 0);
$type = (int) $_GET["type"];

echo hexNum32($addr);
switch($_GET["type"])
{
	case 0: makeUnknown($addr); break;
	case 1: makeData($addr); break;
	case 2: makeCode($addr); break;
	case 3: makeFunction($addr); break;
}

analyze($addr);

