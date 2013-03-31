<?php

function makeImmediateRef($num, $type)
{
	$target = fetchAddr($num);
	
	//Addr doesn't exist
	if(!$target) return;
	
	if($target["labeltype"] < $type)
	{
		Query("update dis_data set labeltype='".JustEscape($type)."' where addr=$num");
	
		if(($type == LABEL_CODE || $type == LABEL_FUNC) && !$target["code"])
			makeCode($num);
	}
}

function makeCode($addr)
{
	$target = fetchAddr($addr);
	
	if(!$target) return;
	if($target["type"] != TYPE_UNK) return;
	$target["type"] = TYPE_CODE;

	saveAddr($target);
	
	addToAnalysisQueue($addr);
}

function makeData($addr)
{
	$target = fetchAddr($addr);
	if(!$target) return;
	if($target["type"] != TYPE_UNK) return;

	$target["type"] = TYPE_DATA;
	saveAddr($target);
}

function makeUnknown($addr)
{
	$target = fetchAddr($addr);
	if(!$target) return;

	$target["type"] = TYPE_UNK;
	$target["labeltype"] = LABEL_NONE;
	$target["label"] = "";
	saveAddr($target);
}

function makeFunction($addr, $name="")
{
	$target = fetchAddr($addr);
	
	if(!$target) return;
	if($target["type"] == TYPE_DATA) return;
	
	if($name)
		$target["label"] = $name;
	$target["type"] = TYPE_CODE;
	$target["labeltype"] = LABEL_FUNC;
	
	saveAddr($target);
	addToAnalysisQueue($addr);
}

function addToAnalysisQueue($addr)
{
	global $analysisQueue, $autoanalysis;
	if($autoanalysis)
		array_push($analysisQueue, $addr);
}
function analyze($addr)
{
	global $analysisQueue, $autoanalysis;

	$autoanalysis = true;
	$analysisQueue = array();

	array_push($analysisQueue, $addr);
	
	while(count($analysisQueue) != 0)
	{
		$addr = array_pop($analysisQueue);
		disassembleDb(fetchAddr($addr));
	}

	$autoanalysis = false;
	unset($GLOBALS["analysisQueue"]);
}

