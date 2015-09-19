# Dbf2 Importer #

## Description ##
Dbf2 is a tool for import DBF Files for SQL Server and MySQL, with a COM Server in Visual Fox Pro 8.

## Requirements ##
* [PHP 5.4.1 or higher](http://www.php.net/)
* [mysqli extension](http://www.php.net/)
* [sqlsrv extension](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx/)
* [com_dotnet extension](http://php.net/manual/en/class.dotnet.php)
* [Bootstrap 3](http://getbootstrap.com/)
* [JQuery File Tree](https://github.com/daverogers/jQueryFileTree)
* [Visual FoxPro Runtime](http://msdn.microsoft.com/en-us/library/ms950411.aspx)

## Developer Documentation ##
Execute phpdoc -d classes/ 

## Installation ##
Register COM Server with Administrator Permissions regsvr32 dbf2csv.dll
Clone Repository DBF2.
Execute composer.phar for install for download dependencies.
Create in directory classes a script config.php, with the next structure:

~~~

$hostname = 'localhost';
$username = 'myuser';
$password = 'mypassword';
$dbname   = 'mydbname';
$csvPath  =  "C:/PATH_CSV/";
$dbfPath  = "C:/PATH_DBF/";
$appUser  = "application_user";
$appPassword  = "application_password";

~~~

## Basic Example ##
see example.php

## Notes ##
This Tool only works in Windows, and the host only must be localhost.
The DBFs Files are opened in Exclusive Mode.
For SQL Server the Hostname must include INSTANCE, if necessary.
Example: localhost\SQLINSTANCE

Sorry, my english is bad :(.

## References ##
http://es.wikipedia.org/wiki/Component_Object_Model
http://msdn.microsoft.com/es-es/library/cc450432(v=vs.71).aspx
http://php.net/manual/es/class.com.php

## Environment Test ##
- Windows 8 Enterprise x64
- XAMPP 1.8.2
- PHP: 5.4.31
- Microsoft Visual Fox Pro 8.0 SP1

P.D. Let's go play !!!




