<?php
require "../vendor/autoload.php";

require_once('config.php');
require_once('Dbf2.class.php');

$dbf2 = new Dbf2(Dbf2::MSSQLSERVER, $hostname, $username, $password, $dbname, $csvPath);
//$dbf2 = new Dbf2(Dbf2::MYSQL, $username, $password, $dbname, $csvPath);

if ($dbf2->hasError()){
    die("Unable load Application DBF2");
}
else{
	$dbfFile = "C:TEMP/DBF/COLONIAS.DBF";

	if (!$dbf2->generateFiles($dbfFile)){
		if ($dbf2->hasError()){
			echo $dbf2->getErrorCode();
		}
	}
}

$dbf2 = null;
?>