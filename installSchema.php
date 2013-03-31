<?php

$dataInt = "int(11) NOT NULL DEFAULT '0'";

$tables["dis_sections"] = array
	(
		"fields" => array
		(
			"id" => $AI,
			"name" => "varchar(512)".$notNull,
			"start" => $dataInt,
			"end" => $dataInt,
		),
		"special" => $keyID
	);

$tables["dis_data"] = array
	(
		"fields" => array
		(
			"addr" => $dataInt,
			"section" => $genericInt,
			"data" => $dataInt,
			"type" => $smallerInt,
			"comment" => $text,
			"label" => "varchar(64)".$notNull,
			"labeltype" => $smallerInt,
		),
		"special" => "key `addr` (`addr`), key `section` (`section`), key `label` (`label`)"
	);

