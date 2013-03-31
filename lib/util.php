<?php

//Basic formatting
function hexNum($num)
{
	return "0x".strtoupper(dechex($num));
}

function hexNum32($num)
{
	return "0x".str_pad(strtoupper(dechex($num)), 8, "0", STR_PAD_LEFT);
}

//Instruction formatting
function immediate($num)
{
	return applyClass("data highlight", "#".hexNum($num));
}


function immediateRef($num)
{
	$target = fetchAddr($num);
	
	//Addr doesn't exist
	if(!$target || $target["labeltype"] == LABEL_NONE)
		return applyClass("data highlight", hexNum32($num));
	
	return makeLabelTag($target);
}

function makeLabelTag($target)
{
	global $exporting;
	
	if(!$target["label"])
	{
		switch($target["labeltype"])
		{
			case LABEL_NONE: $class=""; break;
			case LABEL_UNK: $class="unk"; break;
			case LABEL_DATA: $class="data"; break;
			case LABEL_CODE: $class="loc"; break;
			case LABEL_FUNC: $class="func"; break;
		}
	
		if($target["data"] == (int)0xE12FFF1E) //bx lr
			$class="nullfunc";
			
		$target["label"] = $class."_".str_pad(strtoupper(dechex($target["addr"])), 8, "0", STR_PAD_LEFT);
	}
	
	switch($target["labeltype"])
	{
		case LABEL_NONE: $class=""; break;
		case LABEL_UNK: $class="labelunk"; break;
		case LABEL_DATA: $class="labeldata"; break;
		case LABEL_CODE: $class="labelcode"; break;
		case LABEL_FUNC: $class="labelfunc"; break;
	}
	
	if($exporting)
		return $target["label"];
	else
		return "<a href=\"${target["label"]}\" class=\"$class label highlight\" onclick=\"gotoAddress(${target["addr"]}); return false;\">${target["label"]}</a>";
}

function immediateShift($num)
{
	return applyClass("data highlight", "#".$num);
}

//Utility bitwise functions.
function signExtend($val, $bits)
{
	$sign = $val >> ($bits-1);

	if(!$sign) return $val;
	
	$mask = (1<<$bits)-1;
	$val ^= $mask;
	return -$val-1;
}

function rotateRight($num, $amt)
{
	return (($num >> $amt) | ($num << (32-$amt))) & 0xFFFFFFFF;
}
function rotateLeft($num, $amt)
{
	return (($num << $amt) | ($num >> (32-$amt))) & 0xFFFFFFFF;
}

//HTML formatting
function applyClass($class, $text, $onclick="", $ondblclick="")
{
	global $exporting;
	if($exporting) return $text;

	$lol = "";
	if($onclick) $lol .= "onclick=\"".htmlspecialchars($onclick)."\" ";
	if($ondblclick) $lol .= "ondblclick=\"".htmlspecialchars($ondblclick)."\" ";
	return "<span class=\"$class\" $lol>$text</span>";
}

// Useful definitions.

define("LABEL_NONE", 0);
define("LABEL_UNK", 2);
define("LABEL_DATA", 4);
define("LABEL_CODE", 7);
define("LABEL_FUNC", 10);

define("TYPE_UNK", 0);
define("TYPE_DATA", 1);
define("TYPE_CODE", 2);

// MySQL utils, and caching shit.

function fetchAddr($num)
{
	global $fetchCache;

	if($fetchCache["addr"] == $num)
		return $fetchCache;
	
	$fetchCache = Fetch(Query("select * from dis_data where addr = $num"));
	return $fetchCache;
}

function saveAddr($data)
{
	global $fetchCache;

	$direct = false;
	
	if($data["addr"] != $fetchCache["addr"])
		$direct = true;
		
	$sets = array();
	foreach($data as $key => $value)
		if($direct || $data[$key] != $fetchCache[$key])	
			$sets[] = $key." = '".JustEscape($value)."'";
	
	if(count($sets) != 0)
		Query("update dis_data set ".implode(",", $sets)." where addr=".$data["addr"]);

	$fetchCache = $data;
}
