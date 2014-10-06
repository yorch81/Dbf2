<?php
require_once('config.php');
require_once('Dbf2.class.php');

$dbf2 = new Dbf2(Dbf2::MYSQL, $username, $password, $dbname, $csvPath);

if ($dbf2->hasError()){
	echo $dbf2->getErrorCode();
}
else{
	if (!$dbf2->generateFiles($dbfFile)){
		if ($dbf2->hasError()){
			echo $dbf2->getErrorCode();
		}

		$dbf2->dropTable();
		$dbf2->generateFiles($dbfFile);
	}
}

$dbf2 = null;
?>