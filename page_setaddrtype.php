<?php

$ajaxPage = true;

$addr = (int) $_GET["addr"];
$type = (int) $_GET["type"];

switch($_GET["type"])
{
	case 0: makeUnknown($addr); break;
	case 1: makeData($addr); break;
	case 2: makeCode($addr); break;
	case 3: makeFunction($addr); break;
}

analyze($addr);

