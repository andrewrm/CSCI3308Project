<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access!' );

class CF_Factory {
	
	// Gets a reference to the configuration object
	public static function &getConfig() {
		return CF_Config::getInstance();
	}
	
	// Gets a reference to the current user object
	public static function &getUser() {
		return CF_User::getInstance();
	}
	
	// Gets a reference to the database object
	public static function &getDBO() {
		return CF_Database::getInstance();
	}
	
}

?>