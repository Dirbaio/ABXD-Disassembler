<?php


$dataInstructions = array(
	"AND",
	"EOR",
	"SUB",
	"RSB",
	"ADD",
	"ADC",
	"SBC",
	"RSC",
	"TST",
	"TEQ",
	"CMP",
	"CMN",
	"ORR",
	"MOV",
	"BIC",
	"MVN"
);

function nop($data)
{
	return "NOP";
}

function dataProcBase($data)
{
	global $dataInstructions;

	$res = $dataInstructions[$data['o']];
	$res .= conditionField($data['c']);
	if($data["o"] < 8 || $data["o"] > 11)
		if($data["s"])
			$res .= "S";
		
	$res .= " ";
	
	if($data["o"] < 8 || $data["o"] > 11)
		$res .= register($data["d"]).", ";

	if($data["o"] != 13 && $data["o"] != 15)
		$res .= register($data["n"]).", ";
	
	return $res;
}

function baseShiftImm($data)
{
	$res = "";
	$res .= register($data["m"]);
	
	if($data["h"] != 0)
	{
		$res .= ", ";
		$res .= shiftType($data["t"]);
		$res .= immediateShift($data["h"]);
	}
	else
	{
		if($data["t"] == 1) //LSR#32
			$res .= ", ".shiftType($data["t"]).immediateShift(32);
		if($data["t"] == 2) //ASR#32
			$res .= ", ".shiftType($data["t"]).immediateShift(32);
		if($data["t"] == 3) //RRX#1
			$res .= ", RRX".immediateShift(1);
	}
	return $res;
	
}
function dataProcShiftImm($data)
{
	$res = dataProcBase($data);
	$res .= baseShiftImm($data);
	return $res;
}

function dataProcShiftReg($data)
{
	$res = dataProcBase($data);
	$res .= register($data["m"]);
	
	$res .= ", ";
	$res .= shiftType($data["t"]);
	$res .= " ";
	$res .= register($data["h"]);

	return $res;	
}

function dataProcImm($data)
{
	$res = dataProcBase($data);
	$res .= immediate(rotateRight($data["i"], $data["h"]*2));
	return $res;
}

function transConst($data)
{
	global $pc, $autoanalysis;
	$addr = fetchAddr($pc+8+$data["o"]);

	if(!$addr)
	{
		$data["p"] = 1;
		$data["u"] = 1;
		$data["b"] = 0;
		$data["w"] = 0;
		$data["l"] = 1;
		$data["n"] = 15;
		return transImm9($data);
	}

/*	if($addr["code"] == 1)
		Query("update dis_data set code=0 where addr=".($pc+8+$data["o"]));*/

	$res = "LDR";
	$res .= conditionField($data["c"]);
	$res .= " ";
	
	$res .= register($data["d"]);
	$res .= ", =";
	
	if($autoanalysis)
		makeImmediateRef($addr["data"], LABEL_UNK);
	
	$res .= immediateRef($addr["data"]);
	return $res;
}

function transBaseOpc($data)
{
	$res = $data["l"]?"LDR":"STR";
	
	$writeback = $data["w"];
	$preindexing = $data["p"];

	$res .= conditionField($data["c"]);
	if($data["b"])
		$res .= "B";
	if(!$preindexing && $writeback)
		$res .= "T";

	return $res;
}

function transBaseOpc2($data)
{
	$c = conditionField($data["c"]);
	if($data["l"] == 0 && $data["t"] == 1) $res = "STR${c}H";
	if($data["l"] == 0 && $data["t"] == 2) $res = "LDR${c}D";
	if($data["l"] == 0 && $data["t"] == 3) $res = "STR${c}D";
	if($data["l"] == 1 && $data["t"] == 1) $res = "LDR${c}H";
	if($data["l"] == 1 && $data["t"] == 2) $res = "LDR${c}SB";
	if($data["l"] == 1 && $data["t"] == 3) $res = "LDR${c}SH";

	return $res;
}

function transBase($opc, $data, $offset)
{
	$res = $opc;
	
	$writeback = $data["w"];
	$preindexing = $data["p"];

	if(!$preindexing)
		$writeback = 1;

	$res .= " ";
	$res .= register($data["d"]);
	$res .= ", ";
	
	$res .= "[";
	$res .= register($data["n"]);
	
	if($preindexing)
		$res .= $offset;
	$res .= "]";
	
	if(!$preindexing)
		$res .= $offset;
	
	if($writeback && $preindexing)
		$res .= "!";
		
	return $res;
}

function transReg9($data)
{
	$sign = $data["u"]?"":"-";
	$opc = transBaseOpc($data);
	return transBase($opc, $data, ", ".$sign.baseShiftImm($data));
}

function transImm9($data)
{
	$sign = $data["u"]?"":"-";

	$offs = $data["o"];
	if($offs == 0)
		$offs = "";
	else
		$offs = ", ".$sign.immediate($offs);

	$opc = transBaseOpc($data);
	return transBase($opc, $data, $offs);
}

function transImm10($data)
{

	$sign = $data["u"]?"":"-";
	$offs = ($data["h"] << 4) | $data["L"];

	if($offs == 0)
		$offs = "";
	else
		$offs = ", ".$sign.immediate($offs);

	$opc = transBaseOpc2($data);
	return transBase($opc, $data, $offs);	
}

function transReg10($data)
{
	$offs = ", ".register($data["m"]);

	$opc = transBaseOpc2($data);
	return transBase($opc, $data, $offs);	
}

function swap($data)
{
	$res = "SWP";
	$res .= conditionField($data["c"]);
	if($data["b"])
		$res .= "B";
	
	$res .= " ";
	$res .= register($data["d"]);
	$res .= ", ";
	$res .= register($data["m"]);
	$res .= ", [";
	$res .= register($data["n"]);
	$res .= "]";
	return $res;
}

function transMultiple($data)
{
	global $analyzeNext;
	
	$c = conditionField($data["c"]);
	
	if($data["n"] == 13 && $data["p"] == 0 && $data["u"] == 1 && $data["l"] == 1 && $data["w"] == 1)
		$res = "POP$c";
	else if($data["n"] == 13 && $data["p"] == 1 && $data["u"] == 0 && $data["l"] == 0 && $data["w"] == 1)
		$res = "PUSH$c";
	else
	{
		if($data["l"])
			$res = "LDM";
		else
			$res = "STM";
	
		$res .= $c;

		if($data["u"]) $res .= "I";
		else $res .= "D";
		if($data["p"]) $res .= "B";
		else $res .= "A";
	
		$res .= " ";
		$res .= register($data["n"]);
		if($data["w"])
			$res .= "!";
		$res .= ",";
	}
	
	$res .= " {";
	$first = true;
	for($i = 0; $i < 16; $i++)
		if($data["r"] & (1<<$i))
		{
			if(!$first)
				$res .= ", ";
			$first = false;
			$res .= register($i);
		}

	$res .= "}";
	if($data["s"])
		$res .= "^";
		
	if($data["l"] && (($data["r"] & (1<<15)) != 0) && $data["c"] == 14)
		$analyzeNext = false;
	
	return $res;
}

function branchExchange($data)
{
	global $analyzeNext;
	
	if($data["c"] == 14 && !$data["l"])
		$analyzeNext = false;

	if($data["l"])
		$res = "BLX";
	else
		$res = "BX";
	$res .= conditionField($data["c"]);
	$res .= " ";
	$res .= register($data["n"]);
		
	return $res;
}

function branch($data)
{
	global $pc, $autoanalysis, $analyzeNext;
	$data["o"] = signExtend($data["o"], 24);
	
	if($data["c"] == 15) //BLX
	{
		$res = "BLX ";
		if($autoanalysis)
			makeImmediateRef($pc+8+$data["o"]*4+$data["l"]*2, LABEL_CODE);

		$res .= immediateRef($pc+8+$data["o"]*4+$data["l"]*2);
		return $res;
	}
	else //B, BL
	{
		$res =  "B";
		if($data["l"])
			$res .= "L";
		else 
			if($data["c"] == 14)
				$analyzeNext = false;
			
		$res .= conditionField($data["c"]);
		$res .= " ";

		if($autoanalysis)
			makeImmediateRef($pc+8+$data["o"]*4, $data["l"]?LABEL_FUNC:LABEL_CODE);

		$res .= immediateRef($pc+8+$data["o"]*4);
		return $res;
	}
}

function swi($data)
{
	$res = "SWI";
	$res .= conditionField($data["c"]);
	$res .= " ";
	$res .= immediate($data["d"]);		
	return $res;
}

function clz($data)
{
	$res = "CLZ";
	$res .= conditionField($data["c"]);
	$res .= " ";
	$res .= register($data["d"]);
	$res .= ", ";
	$res .= register($data["m"]);
	return $res;
}

function multiply($data)
{
}
function multiplyLong($data)
{
}
function multiplyHalf($data)
{
}

function psrImm($data)
{
}
function psrReg($data)
{
}
