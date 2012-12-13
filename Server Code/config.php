<?php 

// CF_ROOTPAGE is the local files on your server
// eg. (in Linux) /home/content/html/folder1/folder2/folder3/
// eg. (in Windows) C:\web\html\

// CF_WEBROOTPAGE is the domain address plus the folders it falls under
// eg. http://www.example.com/folder1/folder2/folder3/


// Set this file as the root of the web application
// Note: This constant determines whether this file (config.php) is included 
//       in other files. If it isn't, then access is denied. This is to 
//       keep users from directly accessing internal files and also keeps the 
//       entire CocktailFinder system integrated.
// IMPORTANT: Hence, every file in the web application should check for this 
//       constant and deny access if it isn't defined.
define( '_CF_EXEC', 1 );

/*** Global Configuration ***/

// Site Status
define( 'CF_ST_OFFLINE', 0 );
define( 'CF_ST_DEBUG', 1 );
define( 'CF_ST_LIVE', 2 );
/*
	Status Codes:
	
	CF_ST_OFFLINE - Under Maintenance (site is turned off and maintenance message is shown)
	CF_ST_DEBUG - Debug Mode (all errors and technical details are shown)
	CF_ST_LIVE - Live (user-friendly errors are shown, code fails gracefully)
*/

define( 'CF_STATUS', CF_ST_DEBUG );

// Global Generic Error Message
define( 'CF_GLOBAL_ERR', 'An error occurred while initializing Cocktail Finder.' );


final class CF_Config {
	
	// Holds the loaded-in options
	private $options = array();
	
	public function __construct() {
		// Load the basic CF class
		require realpath( dirname( __FILE__ ) . '/classes/class.cf.php' );
		
		// Load the configuration file
		if( !file_exists( dirname( __FILE__ ) . '/settings.php' ) ) {
			throw new Exception( 'Could not locate configuration file!' );
		} else {
			require realpath( dirname( __FILE__ ) . '/settings.php' );
			
			if( !class_exists( 'CF_Settings', false ) ) {
				throw new Exception( 'Could not load configuration file!' );
			} else {
				// Load the options into our array
				$this->options = array_merge_recursive( $this->options, get_class_vars( 'CF_Settings' ) );
			}
		}
		
		// Register the autoload function
		spl_autoload_register( __CLASS__ . '::autoload', true );
		
		// Register Custom Error and Exception Handlers
		set_error_handler( __CLASS__ . '::error' );
		set_exception_handler( __CLASS__ . '::exception' );
	}
	
	// Gets the instance of this config class
	public static function &getInstance() {
		static $instance;
		
		if ( !is_object( $instance ) ) {
			$class = __CLASS__;
			$instance = new $class();
			unset( $class );
		}
		
		return $instance;
	}
	
	public function add( $param_name, $param_value ) {
		if ( !array_key_exists( $param_name, $this->options ) ) {
			$this->options[$param_name] = $param_value;
			return true;
		}
		
		return false;
	}
	
	public function get( $param_name ) {
		// Check to see if the parameter exists in our set of options
		if ( array_key_exists( $param_name, $this->options ) ) {
			return $this->options[$param_name];
		} elseif( defined( strtoupper( $param_name ) ) ) { // If not, fall back to constants
			return constant( strtoupper( $param_name ) );
		} else { // If all else fails, bail out
			return false;
		}
	}
	
	// This function allows for localization variables to be resolved (gets the text associated with a variable name)
	public function _( $variable ) {}
	
	// Define the custom error handler
	public static function error( $err_type, $err_str, $err_file, $err_line ) {
		if( class_exists( 'CF_Error', false ) ) {
			CF_Error::handleError( $err_type, $err_str, $err_file, $err_line );
		} else {
			if( ($err_type == E_WARNING || $err_type == E_NOTICE) && CF_STATUS != CF_ST_LIVE ) {
			// Should log errors if the site is live and errors are not being displayed
				if( strpos( $err_str, 'ldap_bind' ) === false ) {
					echo (CF_STATUS == CF_ST_LIVE) ? CF_GLOBAL_ERR : 'Type: ' . $err_type . '<br />' . $err_str . '<br />' . 'File: ' . $err_file . '<br />' . 'Line: ' . $err_line . '<br />';
				}
			} else {
				if( CF::isLive() )
					die( 'An unexpected error occurred. Please try again or contact the webmaster.' );
				else
					die( (CF_STATUS == CF_ST_LIVE) ? CF_GLOBAL_ERR : 'Type: ' . $err_type . '<br />' . $err_str . '<br />' . 'File: ' . $err_file . '<br />' . 'Line: ' . $err_line . '<br />' );
			}
		}
	}
	
	// Define the custom exception handler
	public static function exception( $exception ) {
		die( $exception->getMessage() );
	}
	
	// Define the autoloader function
	// Note: This function makes it so that code for classes does not have to be explicitly included/imported.
	public static function autoload( $class_name ) {
		// Process the class name
		if ( $class_name !== 'CF' ) {
			$class_name = str_replace( 'cf_', '', strtolower( $class_name ) );
		}
		
		// Traverse through all relevant directories looking for the file until it's found
		if ( file_exists( CF_DIR_CLASSES . 'class.' . strtolower( $class_name ) . '.php' ) ) {
			require CF_DIR_CLASSES . 'class.' . strtolower( $class_name ) . '.php';
		} elseif ( file_exists( CF_DIR_ABSTRACTS . 'abstract.' . strtolower( $class_name ) . '.php' ) ) {
			require CF_DIR_ABSTRACTS . 'abstract.' . strtolower( $class_name ) . '.php';
		}/* elseif ( file_exists( CF_DIR_INTERFACES . 'interface.' . str_replace( '_interface', '', strtolower( $class_name ) ) . '.php' ) ) {
			require_once CF_DIR_INTERFACES . 'interface.' . str_replace( '_interface', '', strtolower( $class_name ) ) . '.php';
		} elseif ( file_exists( CF_DIR_HANDLERS . 'handler.' . str_replace( '_handler', '', strtolower( $class_name ) ) . '.php' ) ) {
			require_once CF_DIR_HANDLERS . 'handler.' . str_replace( '_handler', '', strtolower( $class_name ) ) . '.php';
		}*/ else { // If reached here, then the file doesn't exist
			// Should throw exception, but since we're using the singleton design pattern, the exception can't be caught
			die( 'Could not autoload the required file!' );
			//throw new Exception( 'Could not find the required file!' );
		}
	}
	/*
	public function set( $param_name, $value ) {
		// This should only be enabled when configuration options can change during runtime.
	}
	*/
	private function _initializeSecurity() {}
	
}

// Initialize the configuration object
$config =& CF_Config::getInstance();

// Paths
define( 'CF_ROOTPAGE', $config->get( 'dir_root' ) );
define( 'CF_DIR_GLOBALS', CF_ROOTPAGE . 'globals/' );
define( 'CF_DIR_CLASSES', CF_ROOTPAGE . 'classes/' );
define( 'CF_DIR_ABSTRACTS', CF_ROOTPAGE . 'classes/abstracts/' );
define( 'CF_DIR_INTERFACES', CF_ROOTPAGE . 'classes/interfaces/' );
define( 'CF_DIR_HANDLERS', CF_ROOTPAGE . 'classes/handlers/' );
define( 'CF_DIR_IMAGES', CF_ROOTPAGE . 'img/' );
define( 'CF_DIR_THUMBNAILS', CF_DIR_IMAGES . 'cocktails/' );
define( 'CF_DIR_PLUGINS', CF_ROOTPAGE . 'plugins/' );
define( 'CF_DIR_SCRIPT', CF_ROOTPAGE . 'script/' );

// Web Paths
define( 'CF_WEBROOTPAGE', $config->get( 'web_root' ) );
define( 'CF_WEBDIR_GLOBALS', CF_WEBROOTPAGE . 'globals/' );
define( 'CF_WEBDIR_CLASSES', CF_WEBROOTPAGE . 'classes/' );
define( 'CF_WEBDIR_HOME', CF_WEBROOTPAGE );
define( 'CF_WEBDIR_IMAGES', CF_WEBROOTPAGE . 'img/' );
define( 'CF_WEBDIR_ICONS', CF_WEBDIR_IMAGES . 'icons/' );
define( 'CF_WEBDIR_SCRIPT', CF_WEBROOTPAGE . 'script/' );
define( 'CF_WEBDIR_PLUGINS', CF_WEBROOTPAGE . 'plugins/' );
define( 'CF_WEBDIR_SYLLABUS_ARCHIVE', $config->get( 'web_syllarch' ) );

// Database Connection Parameters
define( 'CF_DB_CONNECT_HOST', $config->get( 'mysql_host' ) );
define( 'CF_DB_CONNECT_USER', $config->get( 'mysql_user' ) );
define( 'CF_DB_CONNECT_PASS', $config->get( 'mysql_pass' ) );
define( 'CF_DB_CONNECT_DBNAME', $config->get( 'mysql_db' ) );


// Database Tables
define( 'CF_ERROR_LOG_TABLE', CF_DB_CONNECT_DBNAME . '.error_log' );
define( 'CF_COCKTAILS_TABLE', CF_DB_CONNECT_DBNAME . '.cocktails' );
define( 'CF_CATEGORIES_TABLE', CF_DB_CONNECT_DBNAME . '.categories' );
define( 'CF_REVIEWS_TABLE', CF_DB_CONNECT_DBNAME . '.reviews' );
define( 'CF_INGREDIENTS_TABLE', CF_DB_CONNECT_DBNAME . '.ingredients' );
define( 'CF_DIRECTIONS_TABLE', CF_DB_CONNECT_DBNAME . '.directions' );
define( 'CF_MEASURES_TABLE', CF_DB_CONNECT_DBNAME . '.measures' );
define( 'CF_MESSAGES_TABLE', CF_DB_CONNECT_DBNAME . '.messages' );
define( 'CF_USERS_TABLE', CF_DB_CONNECT_DBNAME . '.users' );
define( 'CF_BARS_TABLE', CF_DB_CONNECT_DBNAME . '.bars' );


// Location Parameters
define( 'CF_TIMEZONE', $config->get( 'timezone' ) );


// Session Parameters
define( 'CF_SESSION_NAME', 'cf_id' );
define( 'CF_SESSION_KEYLENGTH_SESSID', 9 );
define( 'CF_SESSION_KEYLENGTH_LOGINID', 12 );

// Authentication Parameters

// Note: When using LDAP authentication, set this to 5 to keep 
//       it in tandem with OIT's account lockout limit.
define( 'CF_AUTH_MAXATTEMPTS', 5 );


// Authentication Responses
define( 'CF_AUTH_ERR_LDAP', -2 );
define( 'CF_AUTH_ERR_SQL', -1 );
define( 'CF_AUTH_SUCCESS', 0 );
define( 'CF_AUTH_INVALID', 1 );
define( 'CF_AUTH_DENIED', 2 );
define( 'CF_AUTH_LOCKED', 3 );
define( 'CF_AUTH_NEWUSER', 4 );


// Login Responses
define( 'CF_LOGIN_ERR_SQL', -1 );
define( 'CF_LOGIN_SUCCESS', 0 );
define( 'CF_LOGIN_PARTSUCCESS', 1 );


// System Messages
define( 'CF_MSG_ERROR', -2 );
define( 'CF_MSG_WARNING', -1 );
define( 'CF_MSG_ANNOUNCEMENT', 0 );
define( 'CF_MSG_SUCCESS', 1 );

// User Module Responses
define( 'CF_USER_ERR_SQL', 1 );
define( 'CF_USER_ERR_INVALID', 2 );


// Set the default timezone to GMT-7 (Mountain Time Zone)
date_default_timezone_set( CF_TIMEZONE );



// If site is under maintenance, redirect to main page
if ( CF_STATUS == CF_ST_OFFLINE ) {
	if ( $_SERVER['REQUEST_URI'] != parse_url( CF_WEBROOTPAGE, PHP_URL_PATH ) ) {
		CF::redirect( CF_WEBROOTPAGE );
	}
}

// Start the session (using a custom session name)
session_name( CF_SESSION_NAME );
session_start();

// Regenerate the session ID (hinders session hijacking)
session_regenerate_id();

// Generate a session ID (if it hasn't already been done)
if ( !isset( $_SESSION['sessionID'] ) ) {
	$_SESSION['sessionID'] = CF::generateKey( CF_SESSION_KEYLENGTH_SESSID );
}


// Initialize global objects
$system =& CF::getInstance();
try {
	$dbo =& CF_Factory::getDBO();
} catch( Exception $e ) {
	die( 'Could not connect to the database!' );
}

/*** Perform security checks ***/

// Initialize more global objects (specifically, the user object)
// Note: This is initialized here because session expiry causes "loginid" to be 
//       invalid, so it has to be accounted for before the user object is created.
/*
if ( isset( $_SESSION['login'] ) ) {
	try {
		$user =& CF_Factory::getUser();
		
		// If the session expired, log the user out and ask for relogin
		// Note: Only do this if the user isn't trying to log out. There's
		//       no point telling the user the session expired when s/he
		//       wants to log out anyways. Also, skip this for AJAX scripts.
		// Update the "last accessed" timestamp for user
		$user->touch();
	} catch( Exception $e ) {
		die( 'An error occurred while retrieving the user information. ' . $e->getMessage() );
	}
}
*/