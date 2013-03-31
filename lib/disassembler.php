<?php

$instructionMasks = array(
//    3         2         1         0
//   10987654321098765432109876543210
	'11100001101000000000000000000000' => "nop",
	'cccc0001001011111111111100l1nnnn' => "branchExchange",
	'cccc101loooooooooooooooooooooooo' => "branch",
	'cccc000oooosnnnnddddhhhhhtt0mmmm' => "dataProcShiftImm",
	'cccc000oooosnnnnddddhhhh0tt1mmmm' => "dataProcShiftReg",
	'cccc001oooosnnnnddddhhhhiiiiiiii' => "dataProcImm",
	'cccc010110011111ddddoooooooooooo' => "transConst",
	'cccc010pubwlnnnnddddoooooooooooo' => "transImm9",
	'cccc011pubwlnnnnddddhhhhhtt0mmmm' => "transReg9",
	'cccc1111dddddddddddddddddddddddd' => "swi",
	'cccc000101101111dddd11110001mmmm' => "clz",
//    3         2         1         0
//   10987654321098765432109876543210
	'cccc00010b00nnnndddd00001001mmmm' => "swap",
	'cccc000pu0wlnnnndddd00001tt1mmmm' => "transReg10",
	'cccc000pu1wlnnnnddddhhhh1tt1LLLL' => "transImm10",
	'cccc100puswlnnnnrrrrrrrrrrrrrrrr' => "transMultiple",
	'cccc000000asddddnnnnssss1001mmmm' => "multiply",
	'cccc00001uashhhhllllssss1001mmmm' => "multiplyLong",
	'cccc00010oo0hhhhllllssss1yx0mmmm' => "multiplyHalf",
);

$conditionFlags = array(
	"EQ",
	"NE",
	"CS",
	"CC",
	"MI",
	"PL",
	"VS",
	"VC",
	"HI",
	"LS",
	"GE",
	"LT",
	"GT",
	"LE",
	"",
	"NV"
);


function conditionField($cond)
{
	global $conditionFlags;
	
	return $conditionFlags[$cond];
}

function register($num)
{
	return applyClass("register highlight r$num", registerName($num));
}

function registerName($num)
{
	if($num == 15)
		return "PC";
	if($num == 14)
		return "LR";
	if($num == 13)
		return "SP";
		
	return "R".$num;
}

function shiftType($num)
{
	if($num == 0) return "LSL";
	if($num == 1) return "LSR";
	if($num == 2) return "ASR";
	if($num == 3) return "ROR";
	return "???";
}


function wordMatchesMask($word, $mask)
{
	$result = array();
	$powers = array();
	$result["instr"] = $word;
	for($i = 0; $i < 32; $i++)
	{
		$bit = $word & 1;
		$word = (int)($word/2);
		
		$char = $mask[31-$i];
		if($char == '0' && $bit != 0) return false;
		if($char == '1' && $bit != 1) return false;
		
		if($char != '0' && $char != '1')
		{
			if(!isset($result[$char]))
			{
				$result[$char] = 0;
				$power[$char] = 1;
			}
			
			if($bit == 1)
				$result[$char] += $power[$char];
			
			$power[$char] *= 2;
		}
	}
	
	return $result;
}

function disassemble($word)
{
	global $instructionMasks, $analysisQueue, $autoanalysis, $analyzeNext, $pc;
	foreach($instructionMasks as $mask => $func)
	{
		$match = wordMatchesMask($word, $mask);
		if($match)
		{
			$analyzeNext = true;
			$res = $func($match);
			
			if($analyzeNext && $autoanalysis)
				makeCode($pc+4);

			return $res;
		}
	}
	return "???";
}

function disassembleDb($data)
{
	global $exporting, $pc;
	$pc = $data["addr"];
	$res = "???";
	
	if($data["type"] == TYPE_CODE)
		$res = disassemble($data["data"]);

	if($res == "???")
		$res = ".word ".applyClass("data highlight", hexNum($data["data"]));
		
	if($exporting) return $res;
	
	$instr = explode(" ", $res, 2);
	$args = $instr[1];
	$instr = $instr[0];
	
	return applyClass("instruction highlight", $instr)."</td><td>".$args;
}

/*
$pc = 0;

$instrs = file_get_contents("test.bin");
//$instrs = array(0xe2402001, 0xe0612002 , 0xe4d13001 , 0xe3530000 , 0xe7c13002 , 0x1afffffb , 0xe12fff1e);

for($i = 0; $i < strlen($instrs); $i+=4)
{
	$instr = 0;
	$instr |= ord($instrs[$i]) << 0;
	$instr |= ord($instrs[$i+1]) << 8;
	$instr |= ord($instrs[$i+2]) << 16;
	$instr |= ord($instrs[$i+3]) << 24;
	echo decbin($instr), " ";
	echo disassemble($instr), "\n";
	$pc += 4;
}

*/
