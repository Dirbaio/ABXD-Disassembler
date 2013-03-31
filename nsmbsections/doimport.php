<?php


include("../../../lib/database.php");
include("../../../lib/mysql.php");
include("../init.php");


function importSection($name, $start, $end, $filename)
{
	echo $name, "...\n";
	Query("insert into {dis_sections} (name, start, end) values ({0}, {1l}, {2l})", $name, $start, $end);
	$sec = InsertId();
	
	$pc = $start;

	$instrs = file_get_contents($filename);
	//$instrs = array(0xe2402001, 0xe0612002 , 0xe4d13001 , 0xe3530000 , 0xe7c13002 , 0x1afffffb , 0xe12fff1e);

	$thelen = strlen($instrs);
	
	for($i = 0; $i < $thelen; $i+=4)
	{
		$instr = 0;
		$instr |= ord($instrs[$i]) << 0;
		$instr |= ord($instrs[$i+1]) << 8;
		$instr |= ord($instrs[$i+2]) << 16;
		$instr |= ord($instrs[$i+3]) << 24;
		Query("insert into {dis_data} (addr, data, section) values ({0l}, {1l}, {2})", $pc, $instr, $sec);
		$pc += 4;
	}
	
	if($pc != $end)
		echo "WARNING: File size mismatch. ", hexNum32($pc), " ", hexNum32($end), "\n";

	return $sec;
}

	
function importBssSection($name, $start, $end)
{
	echo $name, "...\n";
	Query("insert into {dis_sections} (name, start, end) values ({0}, {1l}, {2l})", $name, $start, $end);
	$sec = InsertId();
	
	$pc = $start;

	while($pc < $end)
	{
		$instr = 0;
		Query("insert into {dis_data} (addr, data, section) values ({0l}, {1l}, {2})", $pc, $instr, $sec);
		$pc += 4;
	}
	
	if($pc != $end)
		echo "WARNING: BSS size mismatch, wtf. ", hexNum32($pc), " ", hexNum32($end), "\n";
}


function importOverlay($id, $start, $end, $bssend, $staticinitbegin, $staticinitend, $filename)
{
	$sec = importSection("OV_$id", $start, $end, $filename);
	importBssSection("OVB_$id", $end, $bssend, $filename);
	
	
}

query("truncate table {dis_data}");
query("truncate table {dis_sections}");
include("import.php");

