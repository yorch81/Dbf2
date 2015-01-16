# Dbf2 CSV Importer #

## Description ##
Dbf2 es una herramienta para generar y cargar archivos CSV en SQL Server o MySQL
utlizando un servidor COM desarrollado en Visual FoxPro 8 para generar CSVs 
y scripts de carga.

## Requirements ##
* [PHP 5.4.1 or higher](http://www.php.net/)
* [mysqli extension](http://www.php.net/)
* [sqlsrv extension](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx/)
* [com_dotnet extension](http://www.php.net/)
* [Bootstrap 3](http://getbootstrap.com/)
* [JQuery File Tree](https://github.com/daverogers/jQueryFileTree)
* [Visual FoxPro Runtime](http://msdn.microsoft.com/en-us/library/ms950411.aspx)

## Developer Documentation ##
El código está documentado tanto de PHP como el Server COM escrito en VFP8.

## Installation ##
Copiar el directorio o clonar el repositorio. El servidor COM se distribuye también
y solo se debe registrar de la siguiente manera: regsvr32 dbf2csv.dll

## Basic Example ##
ver example.php

## Notes ##
Está aplicacion solo funciona en MS Windows.
Debe tener instalado Visual FoxPro o el runtime.
El Servidor de BD donde se importarán los datos, solo puede ser LOCALHOST.
Esta aplicación fué desarrollada en base a la problemática de importar tablas de FoxPro
a SQL Server, ya que con Servidores Vinculados la carga es muy lenta, importar una tabla
de 1 millón de registros tarda alrededor de 1 hora, con CSV tarda alrededor de 2 minutos.

## References ##
http://es.wikipedia.org/wiki/Component_Object_Model
http://msdn.microsoft.com/es-es/library/cc450432(v=vs.71).aspx
http://php.net/manual/es/class.com.php




