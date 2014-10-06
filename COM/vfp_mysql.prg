*/**
* Genera un archivo CSV para importar a MySQL con LOAD DATA LOCAL INFILE.
* Genera un archivo SQL para crear la Tabla en MySQL y el comando para importar.
*
* @category   Visual FoxPro Program
* @package    vfp_mysql.prg
* @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
* @license    http://www.apache.org/licenses/LICENSE-2.0
* @version    1.0.0, 2014-09-01
* @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
* @return File
*/
SET DATE TO YMD

* Seleccionamos la Tabla
USE ?
nameFields = ''
tableFields = ''
numFields = AFIELDS(arrFields)  

* Seleccionamos los archivos a generar
csvFile = PUTFILE("Archivo:",ALIAS(),"csv")
sqlFile = PUTFILE("Archivo:",ALIAS(),"sql")

FOR nCount = 1 TO numFields
	nameFields = nameFields + arrFields(nCount,1) + ','
	
	tableFields = tableFields + arrFields(nCount,1) + ' VARCHAR(65535) NULL,' + CHR(13)
ENDFOR

nameFields = SUBSTR(nameFields, 1, LEN(nameFields)-1)
tableFields = SUBSTR(tableFields, 1, LEN(tableFields)-2)

* Generar CSV
COPY TO &csvFile FIELDS &nameFields DELIMITED WITH CHARACTER |

* Generar SQL
tableFields = 'CREATE TABLE ' + ALIAS() + '(' + tableFields + ') ENGINE=InnoDB DEFAULT CHARSET=utf8' + CHR(13) + CHR(13) 
tableFields = tableFields + "LOAD DATA LOCAL INFILE '" + STRTRAN(csvFile , '\', '/') + "' INTO TABLE " + ALIAS() + " FIELDS TERMINATED BY '|' ENCLOSED BY '" + '"' + "' LINES TERMINATED BY '\n' (" + nameFields + ");"

IF FILE(sqlFile) 
	gnSqlFile = FOPEN(sqlFile,12) 
ELSE 
	gnSqlFile = FCREATE(sqlFile)
ENDIF 

IF gnSqlFile < 0 
	WAIT 'No se puede Abrir el Archivo' WINDOW NOWAIT 
ELSE 
	=FWRITE(gnSqlFile, tableFields) 
ENDIF 

=FFLUSH(gnSqlFile)
=FCLOSE(gnSqlFile) 

CLOSE DATABASES ALL
CLOSE TABLES ALL
CLOSE ALL


