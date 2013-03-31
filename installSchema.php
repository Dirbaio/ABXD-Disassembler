<?php

$unsignedInt = "int(11) UNSIGNED NOT NULL DEFAULT '0'";

$tables["dis_sections"] = array
	(
		"fields" => array
		(
			"id" => $AI,
			"name" => "varchar(512)".$notNull,
			"start" => $unsignedInt,
			"end" => $unsignedInt,
		),
		"special" => $keyID
	);

$tables["dis_data"] = array
	(
		"fields" => array
		(
			"addr" => $unsignedInt,
			"section" => $genericInt,
			"data" => $unsignedInt,
			"type" => $smallerInt,
			"comment" => $text,
			"label" => "varchar(64)".$notNull,
			"labeltype" => $smallerInt,
		),
		"special" => "key `addr` (`addr`), key `section` (`section`), key `label` (`label`)"
	);

