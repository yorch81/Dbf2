*********************************************************
* Servidor COM que maneja la generación de los archivos CSV para su importación.
*
* @package    Dbf2Csv
* @copyright  Copyright 2014 JAPT
* @license    http://www.apache.org/licenses/LICENSE-2.0
* @version    1.0.0, 2014-09-01
* @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
* @return CSV Files
*********************************************************
DEFINE CLASS Tools AS CUSTOM OLEPUBLIC
	*********************************************************
    * Ruta donde se guardan los archivos CSV
    *
    * @var string csvPath Ruta válida
    * @access private
    *********************************************************
	csvPath = SPACE(0)
	
	*********************************************************
    * Tipo de BD (SQLServer o MySQL)
	*
    * @var string DBType Tipo de BD para generar el Script
    * @access private
    *********************************************************
	DBType = 'SQLServer'
	
    *********************************************************
	* Genera un archivo CSV con la tabla seleccionada y su comando INSERT
	*
	* @param string usedTable Ruta Completa de la Tabla
	* @return Archivo CSV Generado
	*********************************************************
    PROCEDURE generateCSV(usedTable AS String)
     	*SET ALTERNATE TO 'dbf2csv.log' ADDITIVE  
        SET DATE TO YMD  
       
       	*********************************************************
	    * Cadena de retorno
	    *
	    * @var string retValue Regresa el Error si hubo
	    * @access private
	    *********************************************************
        retValue = ''   
        
        * Si no ha definido la ruta de los archivos 
        IF LEN(THIS.csvPath) = 0 THEN
        	retValue = "La Ruta para generar los archivos no ha sido definida"
        ELSE
        	TRY
        		* Abrir la Tabla
				USE &usedTable 
				
				* Archivo CSV a crear
				fileCsv = THIS.csvPath + ALIAS() + '.csv'
				IF FILE(fileCsv) 
					DELETE FILE &fileCsv
				ENDIF 
				
				* Archivo SQL CREATE TABLE
				fileTab = THIS.csvPath + ALIAS() + '.tab'
				IF FILE(fileTab) 
					DELETE FILE &fileTab 
				ENDIF 
				
				* Archivo SQL BULK INSERT o  LOAD DATA
				fileIns = THIS.csvPath + ALIAS() + '.ins'
				IF FILE(fileIns) 
					DELETE FILE &fileIns
				ENDIF 
				
				* Archivo SQL comandos extra para quitar ""
				fileExt = THIS.csvPath + ALIAS() + '.ext'
				IF FILE(fileExt) 
					DELETE FILE &fileExt
				ENDIF 
				
				nameFields = ''
				tableFields = ''
				textFile = ''
				
				* Recorremos campos de DBF
				numFields = AFIELDS(arrFields) 				
				FOR nCount = 1 TO numFields
					IF THIS.DBType = 'SQLServer' THEN
						tableFields = tableFields + arrFields(nCount,1) + ' VARCHAR(MAX) NULL,' + CHR(13)
					ELSE
						tableFields = tableFields + arrFields(nCount,1) + ' VARCHAR(65535) NULL,' + CHR(13)
					ENDIF
						
					nameFields = nameFields + arrFields(nCount,1) + ','
				ENDFOR

				* Eliminamos la ultima ,
				nameFields = SUBSTR(nameFields, 1, LEN(nameFields)-1)
				tableFields = SUBSTR(tableFields, 1, LEN(tableFields)-2)
				
				* Generar CSV con comando COPY TO
				COPY TO &fileCsv FIELDS &nameFields DELIMITED WITH CHARACTER |
				
				* Generar SQL CREATE TABLE
				IF THIS.DBType = 'SQLServer' THEN
					textFile = 'CREATE TABLE [dbo].' + ALIAS() + '(' + tableFields + ') ON [PRIMARY]'
				ELSE
					textFile = 'CREATE TABLE ' + ALIAS() + '(' + tableFields + ') ENGINE=InnoDB DEFAULT CHARSET=utf8' 
				ENDIF

				* Escribir archivo .TAB
				IF FILE(fileTab) 
					gnTabFile = FOPEN(fileTab,12) 
				ELSE 
					gnTabFile = FCREATE(fileTab)
				ENDIF 

				IF gnTabFile < 0 
					retValue = "No se puede abrir el archivo" + fileTab
				ELSE 
					=FWRITE(gnTabFile, textFile) 
				ENDIF 

				=FFLUSH(gnTabFile)
				=FCLOSE(gnTabFile) 

				* Generar INSERT 
				IF THIS.DBType = 'SQLServer' THEN
					textFile = "BULK INSERT dbo." + ALIAS() + " FROM '" + fileCsv + "' WITH (FIELDTERMINATOR = '|')"
				ELSE
					textFile = "LOAD DATA LOCAL INFILE '" + STRTRAN(fileCsv, '\', '/') + "' INTO TABLE " + ALIAS() + " FIELDS TERMINATED BY '|' ENCLOSED BY '" + '"' + "' LINES TERMINATED BY '\n' (" + nameFields + ");"
				ENDIF
				
				* Escribir archivo .INS
				IF FILE(fileIns) 
					gnInsFile = FOPEN(fileIns,12) 
				ELSE 
					gnInsFile = FCREATE(fileIns)
				ENDIF 

				IF gnInsFile < 0 
					retValue = "No se puede abrir el archivo" + fileIns
				ELSE 
					=FWRITE(gnInsFile, textFile) 
				ENDIF 

				=FFLUSH(gnInsFile)
				=FCLOSE(gnInsFile)  
				
				* Generar EXTRA QUERIES (Solo SQL Server)
				IF THIS.DBType = 'SQLServer' THEN
					* Escribir archivo .INS
					IF FILE(fileExt) 
						gnExtFile = FOPEN(fileExt,12) 
					ELSE 
						gnExtFile = FCREATE(fileExt)
					ENDIF 
					
					IF gnExtFile < 0 
						retValue = "No se puede abrir el archivo" + fileExt
					ELSE 
						FOR nCount = 1 TO numFields
							nameFields = arrFields(nCount,1)
							IF arrFields(nCount,2) = 'C'
								updCommand = 'UPDATE [dbo].' + ALIAS() + ' SET ' + nameFields + '=REPLACE(' + nameFields + ',' + "'" + '"' + "'" + ',' + "'');"
								=FWRITE(gnExtFile, updCommand) 
							ENDIF
						ENDFOR
					ENDIF 

					=FFLUSH(gnExtFile)
					=FCLOSE(gnExtFile)
				ENDIF				
				
				* Cerramos la tabla
				CLOSE DATABASES ALL
				CLOSE TABLES ALL
			CATCH TO oErr
				retValue = oErr.Message
			FINALLY
			ENDTRY
        ENDIF
        
        RETURN retValue
    ENDPROC
ENDDEFINE