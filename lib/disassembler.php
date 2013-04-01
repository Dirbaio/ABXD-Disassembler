<?php


function wordMatchesMask($word, $mask)
{
	$word = (int)$word;
	$result = array();
	$powers = array();
	$result["instr"] = $word;
	for($i = 0; $i < 32; $i++)
	{
		$bit = $word & 1;
		$word = $word>>1;
		
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

function disassembleInstruction($word)
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

function disassemble($data)
{
	global $exporting, $pc;
	$pc = $data["addr"];
	$res = "???";

//	$data["data"] = 0xE1A00000;
	if($data["type"] == TYPE_CODE)
		$res = disassembleInstruction($data["data"]);

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
