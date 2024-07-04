<?php
/*
    Author: kontakt@leondierkes.de
    Description: Handles database connections
*/

class DatabaseComponent {
	private $databaseAccessCredentials = array();
	
	private $sqlDatabase = null;
	
	// Returns the database object
	public function getDatabase() {
		return $this->sqlDatabase;
	}

	// Initiates database connection
	public function initConnection() {
		$__sqlDatabase = null;

        // Parse the config file for the database, if an error occurs, then return an error message
        $configParseResult = $this->parseConfig();
        if($configParseResult === false)
            return $configParseResult;
		
		try {
            $pdoConnectString = $this->databaseAccessCredentials['database']['database_driver'];

            if(trim($this->databaseAccessCredentials['database']['database_name']) !== "") {
                if($this->databaseAccessCredentials['database']['database_driver'] === "sqlite") {
                    $pdoConnectString .= ":".$this->databaseAccessCredentials['database']['database_name'];
                }else{
                    $pdoConnectString .= ":dbname=".$this->databaseAccessCredentials['database']['database_name'];
                }
            }

            if(trim($this->databaseAccessCredentials['database']['database_hostname']) !== "")
                $pdoConnectString .= ";host=" . $this->databaseAccessCredentials['database']['database_hostname'].";charset=utf8";

            if(trim($this->databaseAccessCredentials['database']['database_charset']) !== "")
                $pdoConnectString .= ":dbname=" . $this->databaseAccessCredentials['database']['database_name'];

            // Establish the connection to the database with the provided parameters by the config file
			$__sqlDatabase = new PDO($pdoConnectString, $this->databaseAccessCredentials['database_user']['username'], $this->databaseAccessCredentials['database_user']['password']);
			$__sqlDatabase->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			
			$this->sqlDatabase = $__sqlDatabase;
			
			return true;
		}catch(Exception $e) {
			return $e->getMessage();
		}
	}

    private function parseConfig() {
        try {
            $configData = file_get_contents(pathinfo(__FILE__)['dirname'] ."/../config.json");

            // Check if the JSON in the config is valid
            if(!json_validate($configData))
                throw new Exception("Invalid JSON found in config.json");

            $configData = json_decode($configData, true);

            $strucuteValid = $this->isConfigStructureValid($configData);
            if(!$strucuteValid)
                throw new Exception($strucuteValid);

            $this->databaseAccessCredentials = $configData;

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    private function isConfigStructureValid(array $parsedConfigJSON) {
        // Check if structure is OK

        if(!isset($parsedConfigJSON['database']))
            return "Missing 'database' attribute in config!";

        if(!isset($parsedConfigJSON['database_user']))
            return "Missing 'database_user' attribute in config!";

        // Check if attributes are OK

        if(!isset($parsedConfigJSON['database']['database_driver']))
            return "Missing 'database_driver' attribute in config!";

        if(!isset($parsedConfigJSON['database']['database_name']))
            return "Missing 'database_name' attribute in config!";

        if(!isset($parsedConfigJSON['database']['database_hostname']))
            return "Missing 'database_hostname' attribute in config!";

        if(!isset($parsedConfigJSON['database']['database_charset']))
            return "Missing 'database_charset' attribute in config!";

        if(!isset($parsedConfigJSON['database_user']['database_username']))
            return "Missing 'database_username' attribute in config!";

        if(!isset($parsedConfigJSON['database_user']['database_password']))
            return "Missing 'database_password' attribute in config!";

        return true;
    }
}
?>