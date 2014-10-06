*/**
* Genera un archivo CSV para importar a SQL Server con BULK INSERT.
* Genera un archivo SQL para crear la Tabla en SQL Server y el comando para importar.
*
* @category   Visual FoxPro Program
* @package    vfp_mssql.prg
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
	
	tableFields = tableFields + arrFields(nCount,1) + ' VARCHAR(MAX) NULL,' + CHR(13)
ENDFOR

nameFields = SUBSTR(nameFields, 1, LEN(nameFields)-1)
tableFields = SUBSTR(tableFields, 1, LEN(tableFields)-2)

* Generar CSV
COPY TO &csvFile FIELDS &nameFields DELIMITED WITH CHARACTER |

* Generar SQL
tableFields = 'CREATE TABLE [dbo].' + ALIAS() + '(' + tableFields + ') ON [PRIMARY]' + CHR(13) + "BULK INSERT dbo." + ALIAS() + " FROM '" + csvFile + "' WITH (FIELDTERMINATOR = '|')"

IF FILE(sqlFile) 
	gnSqlFile = FOPEN(sqlFile,12) 
ELSE 
	gnSqlFile = FCREATE(sqlFile)
ENDIF 

IF gnSqlFile < 0 
	WAIT 'No se puede Abrir el Archivo' WINDOW NOWAIT 
ELSE 
	=FWRITE(gnSqlFile, tableFields) 
	
	* Crear Updates Extras para Cadenas ""
	FOR nCount = 1 TO numFields
		nameFields = arrFields(nCount,1)
		IF arrFields(nCount,2) = 'C'
			updCommand = CHR(13) + 'UPDATE [dbo].' + ALIAS() + ' SET ' + nameFields + '=REPLACE(' + nameFields + ',' + "'" + '"' + "'" + ',' + "'')"
			=FWRITE(gnSqlFile, updCommand) 
		ENDIF
	ENDFOR
ENDIF 

=FFLUSH(gnSqlFile)
=FCLOSE(gnSqlFile) 

CLOSE DATABASES ALL
CLOSE TABLES ALL
CLOSE ALL


