<?php
require_once('../../MyDb/MyDb.class.php');

/**
 * Dbf2 
 *
 * Dbf2 Imports CSV files to SQL Server or MySQL with internal commands (BULK INSERT or LOAD DATA)
 *
 * Copyright 2014 Jorge Alberto Ponce Turrubiates
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   Dbf2
 * @package    Dbf2
 * @copyright  Copyright 2014 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2014-09-05
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class Dbf2
{
	/**
     * Connection Types
     *
     * @const MSSQLSERVER For MS SQL Server
     * @const MYSQL  For Oracle MySQL or MariaDB
     * @access private
     */
	const MSSQLSERVER = 'SQLServer';
	const MYSQL = 'MySQL';

	/**
     * Object COM
     *
     * @var object $_com Handler of COM Server in VFP
     * @access private
     */
	protected $_com = null;

	/**
     * Error Handler
     *
     * @var string $_errorCode Error Handler
     * @access private
     */
	protected $_errorCode = null;

	/**
     * CSV Path
     *
     * @var string $_csvPath Valid path
     * @access private
     */
	protected $_csvPath = '';

	/**
     * DBF Alias
     *
     * @var string $_tableAlias DBF Alias
     * @access private
     */
	protected $_tableAlias = '';

	/**
     * Connection of DB
     *
     * @var object $_conn Connection of DB
     * @access private
     */
	protected $_conn = null;

	/**
     * MySQL selected DB
     *
     * @var string $_mysqlSchema MySQL selected DB
     * @access private
     */
	protected $_mysqlSchema = '';

	/**
	 * Constructor of class 
	 *
	 * @param string $provider A valid provider defaults SQL Server
	 * @param string $username A valid user in RDBMS
	 * @param string $password A valid password in RDBMS
	 * @param string $dbname A valid database in RDBMS
	 * @param string $csvPath A valid path for create files
	 * @return instance | null
	 */
	public function __construct($provider = self::MSSQLSERVER, $username, $password, $dbname, $csvPath)
	{
		// If checkEnvironment()
		if ($this->checkEnvironment()){
			$this->_com = new COM("dbf2csv.Tools");

			if (is_null($this->_com)){
				$this->_errorCode = 'Could not create object COM dbf2csv.Tools';
			}
			else{
				// Set DBType
				$this->_com->DBType = $provider;

				// Load path of CSV Files
				if (file_exists($csvPath)){
					$this->_csvPath = $csvPath;
					$this->_com->csvPath = $this->_csvPath;

					// Connect to localhost
					$this->_conn = MyDb::getConnection($provider . 'Db', 'localhost', $username, $password, $dbname);

					// If connection is MySQL save schema
					if ($this->_conn->getProvider() == 'MySQLDb')
						$this->_mysqlSchema = $dbname;

					if (!$this->_conn->isConnected()){
						$this->_errorCode = "Not Connected to $provider, please check log";
					}
				}
				else
					$this->_errorCode = "The CSV directory $csvPath not exists";
			}
		}
	}

	/**
	 * Create CSV and scripts files in path $csvPath
	 *
	 * @param string $dbfFile A valid table DBF
	 * @return 0 Sucessful 1 DBF not Exists 2 Table Already Exists 3 Server COM Error
	 */
	public function generateFiles($dbfFile)
	{
		$retvalue = 0;

		$this->_errorCode = null;

		if (!$this->hasError()){
			if (file_exists($dbfFile)){
				$this->_tableAlias = '';
				$this->_tableAlias = $this->getAlias($dbfFile);

				if ($this->existsTable()){
					$retvalue = 2;
					$this->_errorCode = "The table " . $this->getAlias($dbfFile) ." already exists";
				}
				else{
					$comMsj = $this->_com->generateCSV($dbfFile);

					// No Error
					if (strlen($comMsj) == 0){
						$this->executeScripts();
						$retvalue = 0;
					}
					else{
						$retvalue = 3;
						$this->_errorCode = $comMsj;
					}
				}
			}
			else{
				$this->_errorCode = "The DBF file $dbfFile not exists";
				$retValue = 1;
			}	
		}

		return $retvalue;
	}

	/**
	 * Return true if error exists
	 *
	 * @return true | false
	 */
	public function hasError()
	{
		return !is_null($this->_errorCode);
	}

	/**
	 * Return last error message
	 *
	 * @return string
	 */
	public function getErrorCode()
	{
		return $this->_errorCode;
	}

	/**
	 * Drop Table if exists
	 *
	 * @param string $dbfFile A valid table DBF
	 * @return void
	 */
	public function dropTable($dbfFile)
	{
		if (!$this->hasError()){
			if (file_exists($dbfFile)){
				$this->_tableAlias = '';
				$this->_tableAlias = $this->getAlias($dbfFile);
				$this->_conn->executeCommand("DROP TABLE " . $this->_tableAlias);
			}
		}
	}

	/**
	 * Load and execute the scripts generated
	 *
	 * @return void
	 */
	private function executeScripts()
	{
		if (!$this->hasError()){
			// CREATE TABLE
			$commandFile = $this->_csvPath . $this->_tableAlias . '.tab';
			if (file_exists($commandFile)){
				$command = file_get_contents($commandFile);
				$this->executeCommand($command);
			}
			
			// INSERT CSV
			$commandFile = $this->_csvPath . $this->_tableAlias . '.ins';
			if (file_exists($commandFile)){
				$command = file_get_contents($commandFile);
				$this->executeCommand($command);
			}

			// EXTRA QUERIES only SQL Server
			if ($this->_conn->getProvider() == 'SQLServerDb'){
				$commandFile = $this->_csvPath . $this->_tableAlias . '.ext';
				if (file_exists($commandFile)){
					$command = file_get_contents($commandFile);
					$this->executeCommand($command);
				}
			}
		}
	}

	/**
	 * Execute script in DB
	 *
	 * @return void
	 */
	private function executeCommand($commandSQL)
	{
		if (!$this->hasError()){
			$this->_conn->executeCommand($commandSQL);
		}
	}

	/**
	 * Check id table exists in DB
	 *
	 * @return true | false
	 */
	private function existsTable()
	{
		// If provider is SQL Server
		if ($this->_conn->getProvider() == 'SQLServerDb')
			$query = "SELECT TABLAS.name AS TABLE_NAME
						FROM SYS.OBJECTS TABLAS
							,SYS.SCHEMAS ESQUEMAS
						WHERE TABLAS.TYPE = 'U' 
						AND ESQUEMAS.SCHEMA_ID = TABLAS.SCHEMA_ID
						AND ESQUEMAS.name = 'dbo'
						AND TABLAS.name = '" . $this->_tableAlias . "'"; 
		else
			$query = "SELECT TABLE_NAME
						FROM INFORMATION_SCHEMA.TABLES
						WHERE TABLE_SCHEMA = '" . $this->_mysqlSchema . 
						"' AND TABLE_NAME = '" . $this->_tableAlias . "'"; 

		$resultSet = $this->_conn->executeCommand($query);

		// If returns something
		if ($resultSet != false){
			if (count($resultSet) == 0)
				return false;
			else
				return true;
		}
		else
			return false;
	}
	
	/**
	 * Return Alias of DBF table
	 * Example: C:\DBF\TABLE.DBF returns TABLE
	 *
	 * @return string
	 */
	private function getAlias($dbfFile)
	{
		$arrPath = explode('\\', $dbfFile);
		$dbf = $arrPath[count($arrPath)-1];
		$arrDbf = explode('.', $dbf);

		return $arrDbf[0];
	}

	/**
	 * Check Environment Operating System must be Windows and
	 * the extension com_dotnet must be loaded
	 *
	 * @return true | false
	 */
	private function checkEnvironment()
	{
		$this->_errorCode = null;

		// If Operating System is Windows
		if (strpos(PHP_OS, 'WIN') !== false){
			// Check if extension com_dotnet is loaded
			if (!extension_loaded('com_dotnet')) {
				// If not loaded then try load the extension php_com_dotnet.dll
				if (!dl('php_com_dotnet.dll')) 
					$this->_errorCode = 'Could not load the extension php_com_dotnet.dll';
			}
		}
		else{
		    $this->_errorCode = 'The Operating System must be MS Windows';
		}

		return is_null($this->_errorCode);
	}
}

?>