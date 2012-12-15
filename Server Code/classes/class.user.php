<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );

// This object throws exceptions with the following error codes:
// CF_USER_ERR_SQL = Error Loading User Information
// CF_USER_ERR_INVALID = Invalid User
class CF_User {
	
	// All of these properties are "protected" to allow for child class derivations in the future
	protected $id;
	protected $username;
	protected $firstname;
	protected $lastname;
	protected $zip_code;
	protected $phone;
	
	// Initializes a new user object with the specified user's information
	public function __construct( $criteria ) {
		$dbo =& CF_Factory::getDBO();
		
		// Build the query depending on the specified argument type
		$query	=	'
			SELECT		*
			FROM		' . CF_USERS_TABLE . '
			WHERE		username = "' . $dbo->sqlsafe( $criteria ) . '"
			OR			id = "' . $dbo->sqlsafe( $criteria ) . '"
			'
		;
		$userquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $userquery ) ) {
			$dbo->submitErrorLog( $userquery, 'CF_User::__construct()' );
			throw new Exception( 'Could not load the user information!', CF_USER_ERR_SQL );
		}
		if ( $dbo->num_rows( $userquery ) != 1 ) {
			throw new Exception( 'The specified user does not exist!', CF_USER_ERR_INVALID );
		}
		
		$row = $dbo->getResultObject( $userquery )->fetch_object();
		$this->id				=	$row->id;
		$this->username			=	$row->username;
		$this->firstname		=	$row->firstname;
		$this->lastname			=	$row->lastname;
		$this->zip_code			=	$row->zip_code;
		$this->phone			=	$row->phone;
	}
	
	// Gets (creates if non-existent) a reference to a new user object that holds the logged-in user's information
	public static function &getInstance() {
		// Make sure the user is logged in
		if ( !isset( $_SESSION['login'] ) ) {
			throw new Exception( 'The user is not logged in.' );
		}
		
		static $instance;
		
		if ( !is_object( $instance ) ) {
			$class = __CLASS__;
			$instance = new $class( $_SESSION['uid'] );
			unset( $class );
			// Make sure the user is logged in
			if ( !$instance->isOnline() ) {
				throw new Exception( 'The user is not logged in.' );
			}
		}
		
		return $instance;
	}
	
	// Authenticates a user
	// Note: The function will lock out an account after 5 unsuccessful attempts.
	//       Since the MyComm system now uses LDAP (which is tied to OIT), locking out with MyComm
	//       will also lock out the AD account, unless OIT's lockout limit is greater than MyComm's.
	/* 
	   Status Codes:
	 
		CF_AUTH_ERR_LDAP - LDAP Error
		CF_AUTH_ERR_SQL - SQL Error
		CF_AUTH_SUCCESS - Success
		CF_AUTH_INVALID - Invalid credentials (username/password)
		CF_AUTH_DENIED - Access denied (user not locally registered)
		CF_AUTH_LOCKED - Account Locked
		 
	*/
	public static function authenticate( $username, $password, $firstname = null, $lastname = null, $zipcode = null, $phone = null ) {
		$config =& CF_Factory::getConfig();
		$dbo =& CF_Factory::getDBO();
		
		// Authenticate users
		$query	=	'
			SELECT		id
			FROM		' . CF_USERS_TABLE . '
			WHERE		username = :username'
		;
		$dbo->createQuery( $query );
		$dbo->bind( ':username', $username );
		$existencequery = $dbo->runQuery();
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $existencequery ) ) {
			$dbo->submitErrorLog( $existencequery, 'CF_User::authenticate()' );
			return CF_AUTH_ERR_SQL;
		} elseif ( $dbo->num_rows( $existencequery ) == 1 ) { // If the username exists, authenticate existing user
			$query	=	'
				SELECT		id
				FROM		' . CF_USERS_TABLE . '
				WHERE		username = :username
				AND			password = SHA1( :password )'
			;
			$dbo->createQuery( $query );
			$dbo->bind( ':username', $username );
			$dbo->bind( ':password', $password );
			$authenticatequery = $dbo->runQuery();
			
			if ( $dbo->num_rows( $authenticatequery ) == 1 ) {
				return CF_AUTH_SUCCESS;
			} else {
				return CF_AUTH_INVALID;
			}
		} else { // If not, create new user
			
			if ( !is_null( $firstname ) && !is_null( $lastname ) ) {
				self::add( array(
					'username' => $username,
					'password' => $password,
					'firstname' => $firstname,
					'lastname' => $lastname,
					'zip_code' => $zipcode,
					'phone' => $phone
				) );
				return CF_AUTH_SUCCESS;
			} else {
				return CF_AUTH_NEWUSER;
			}
			
		}
		
	}
	
	// Logs the current user in
	/*
	   Returns: array( status code, redirect location )
	   
	   Status Codes:
	   
		CF_LOGIN_ERR_SQL - SQL Error
		CF_LOGIN_SUCCESS - Success
		CF_LOGIN_PARTSUCCESS - Success, with warning (previous location retrieval problem)
	*/
	public function login() {
		
		// Make sure the user is not already logged in
		if ( !$this->isOnline() ) {
		
			$dbo =& CF_Factory::getDBO();
			
			// Keep track of the return value
			$ret_val = CF_LOGIN_SUCCESS;
			
			// Generate the login id and update session variables
			$_SESSION['login'] = CF::generateKey( CF_SESSION_KEYLENGTH_LOGINID );
			$_SESSION['uid'] = $this->id;
			
			// Insert the user into the session table
			$query	=	'
				INSERT INTO		' . CF_SESSION_TABLE . '
				( sessionID, user_id, lastAccessed, loginID )
				VALUES
				( :sid, :uid, :timestamp, :lid )'
			;
			$dbo->createQuery( $query );
			$dbo->bind( ':sid', $_SESSION['sessionID'] );
			$dbo->bind( ':uid', $this->id );
			$dbo->bind( ':timestamp', time() );
			$dbo->bind( ':lid', $_SESSION['login'] );
			$response = $dbo->runQuery();
			
			if ( $dbo->hasError() ) {
				return CF_LOGIN_ERR_SQL;
			}
			
			// Logged in. Return the redirect location
			return CF_LOGIN_SUCCESS;
		} else { // If the user is already logged in, handle it appropriately
			if ( !isset( $_SESSION['login'] ) ) {
				$_SESSION['login'] = $this->getLoginID();
				$_SESSION['uid'] = $this->id;
			}
			return CF_LOGIN_SUCCESS;
		}
	}
	
	// Logs the current user out
	public function logout() {
		// Check to make sure the session variable is registered
		if ( $this->isOnline() ) { // Session variable is registered, the user is ready to logout
			$dbo =& CF_Factory::getDBO();
			
			// Delete the session table entry
			$query	=	'
				DELETE FROM		' . CF_SESSION_TABLE . '
				WHERE			user_id = "' . $dbo->sqlsafe( $this->id ) . '"'
			;
			$dbo->query( $query );
			
			if ( $dbo->hasError() ) {
				$dbo->submitErrorLog( null, 'CF_User::logout() - Session deletion' );
				return false;
			}
			
			
			// Unset all of the session variables
			$_SESSION = array();
			
			// Delete the session cookie (technically setting its expiration date to some time in the past)
			if ( isset( $_COOKIE[session_name()] ) ) {
				$params = session_get_cookie_params();
				setcookie( session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly'] );
				unset( $params );
			}
			// Destroy the session
			session_destroy();
			
			return true;
		}
		
		return false;
	}
	
	// Returns the last accessed timestamp of the current user
	public function lastAccessed() {
		$dbo =& CF_Factory::getDBO();
		
		$query		=	'
			SELECT		lastAccessed
			FROM		' . CF_SESSION_TABLE . '
			WHERE		user_id = "' . $dbo->sqlsafe( $this->id )  . '"'
		;
		$timestampquery = $dbo->query( $query );

		if ( $dbo->hasError( $timestampquery ) ) {
			$dbo->submitErrorLog( $timestampquery, 'CF_User::lastAccessed()' );
			return false;
		}
		
		$timestamp = $dbo->field( 0, 'lastAccessed', $timestampquery );
		$dbo->free( $timestampquery );
		return $timestamp;
	}
	
	// Updates the "last accessed" timestamp in the user's session
	public function touch() {
		$dbo =& CF_Factory::getDBO();
		
		// Make sure the user is online
		if ( $this->isOnline() ) {
			$query		=	'
				UPDATE		' . CF_SESSION_TABLE . '
				SET			lastAccessed = :timestamp
				WHERE		user_id = :uid;
			';
			$dbo->createQuery( $query );
			$dbo->bind( ':timestamp', time() );
			$dbo->bind( ':uid', $this->id );
			$touchquery = $dbo->runQuery();

			if ( $dbo->hasError( $touchquery ) ) {
				$dbo->submitErrorLog( $touchquery, 'CF_User::touch()' );
				return false;
			}
			
			// Return success
			return true;
		} else {
			// Return failure
			return false;
		}
	}
	
	// Checks to see whether the current user is logged in (online)
	public function isOnline() {
		$dbo =& CF_Factory::getDBO();
		
		$query		=	'
			SELECT		sessionID
			FROM		' . CF_SESSION_TABLE . '
			WHERE		user_id = "' . $dbo->sqlsafe( $this->id )  . '"'
		;
		$onlinequery = $dbo->query( $query );

		if ( $dbo->hasError( $onlinequery ) ) {
			$dbo->submitErrorLog( $onlinequery, 'CF_User::isOnline()' );
			return false;
		}
		
		$isOnline = ($dbo->num_rows( $onlinequery ) == 1);
		$dbo->free( $onlinequery );
		return $isOnline;
	}
	
	// Gets the login ID of the user (if logged in)
	public function getLoginID() {
		$dbo =& CF_Factory::getDBO();
		
		$query		=	'
			SELECT		sessionID
			FROM		' . CF_SESSION_TABLE . '
			WHERE		user_id = "' . $dbo->sqlsafe( $this->id )  . '"'
		;
		$onlinequery = $dbo->query( $query );

		if ( $dbo->hasError( $onlinequery ) ) {
			$dbo->submitErrorLog( $onlinequery, 'CF_User::isOnline()' );
			return false;
		}
		
		$isOnline = ($dbo->num_rows( $onlinequery ) == 1) ? $dbo->field( 0, 'sessionID', $onlinequery ) : false;
		$dbo->free( $onlinequery );
		return $isOnline;
	}
	
	// Adds a new user
	public static function add( array $parameters ) {
		$config =& CF_Factory::getConfig();
		$dbo =& CF_Factory::getDBO();
		
		if ( !isset( $parameters['username'] ) || empty( $parameters['username'] ) || 
			!isset( $parameters['password'] ) || empty( $parameters['password'] ) ||
			!isset( $parameters['firstname'] ) || empty( $parameters['firstname'] ) ||
			!isset( $parameters['lastname'] ) || empty( $parameters['lastname'] )
		) {
			throw new Exception( 'Required information not provided!', 1 );
		} else {
			if ( !self::_validEmail( $parameters['username'] ) ) {
				throw new Exception( 'Invalid username', 2 );
			} elseif ( preg_match( '/[^A-Za-z\-\']/', $parameters['firstname'] ) > 0 ) {
				throw new Exception( 'Invalid first name', 3 );
			} elseif ( preg_match( '/[^A-Za-z\-\']/', $parameters['lastname'] ) > 0 ) {
				throw new Exception( 'Invalid last name', 4 );
			}
			
			if ( !isset( $parameters['zip_code'] ) || empty( $parameters['zip_code'] ) ) {
				$parameters['zip_code'] = '';
			} elseif ( preg_match( '/\d{5}/', $parameters['zip_code'] ) != 1 ) {
				throw new Exception( 'Invalid zip code', 5 );
			}
			
			if ( !isset( $parameters['phone'] ) || empty( $parameters['phone'] ) ) {
				$parameters['phone'] = '';
			} elseif ( preg_match( '/\d{10,11}/', $parameters['phone'] ) != 1 ) {
				throw new Exception( 'Invalid phone number', 6 );
			}
		}
		
		$query		=	'
			INSERT INTO 	' . CF_USERS_TABLE . '
			( username, password, firstname, lastname, zip_code, phone )
			VALUES(
			  "' . $dbo->sqlsafe( $parameters['username'] ) . '",
			  SHA1( "' . $dbo->sqlsafe( $parameters['password'] ) . '" ),
			  "' . $dbo->sqlsafe( $parameters['firstname'] ) . '",
			  "' . $dbo->sqlsafe( $parameters['lastname'] ) . '",
			  "' . $dbo->sqlsafe( $parameters['zip_code'] ) . '",
			  "' . $dbo->sqlsafe( $parameters['phone'] ) . '"
			)
		';
		$insertquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $insertquery ) ) {
			$dbo->submitErrorLog( $insertquery, 'CF_User::add()' );
			throw new Exception( 'An error occurred while adding a new user.', -1 );
		}
	}
	
	// Deletes a user
	public static function delete( $id ) {
		$dbo =& CF_Factory::getDBO();
		
		if ( empty( $id ) || !is_numeric( $id ) )
			throw new Exception( 'Invalid UID', 0 );
		
		$query	=	'
			DELETE FROM	' . CF_USERS_TABLE . '
			WHERE		id = "' . $dbo->sqlsafe( $id ) . '"
			LIMIT		1'
		;
		$deletequery = $dbo->query( $query );
		
		if ( $dbo->hasError( $deletequery ) ) {
			$dbo->submitErrorLog( $deletequery, 'CF_User::delete()' );
			throw new Exception( 'Error while deleting', -1 );
		}
		
		if ( $dbo->affected_rows == 1 ) {
			return true;
		} else {
			throw new Exception( 'Non-existent user', 1 );
		}
	}
	
	
	// Gets the permission level of this user
	public function getPermission() {
		return $this->permission;
	}
	public function getAlias() {
		return $this->username;
	}
	
	
	// Commits the current state of this object to the user data (only commits mutable properties)
	public function commit() {
		$config =& CF_Factory::getConfig();
		$dbo =& CF_Factory::getDBO();
		
		$permissions = $config->get( 'permissions' );
		
		if ( !isset( $this->username ) || empty( $this->username ) || 
			!isset( $this->firstname ) || empty( $this->firstname ) ||
			!isset( $this->lastname ) || empty( $this->lastname ) || 
			!isset( $this->permission ) || empty( $this->permission )
		) {
			throw new Exception( 'Required information not provided!', 1 );
		} else {
			if ( preg_match( '/[^A-Za-z\-\']/', $this->firstname ) > 0 ) {
				throw new Exception( 'Invalid first name', 3 );
			} elseif ( preg_match( '/[^A-Za-z\-\']/', $this->lastname ) > 0 ) {
				throw new Exception( 'Invalid last name', 4 );
			} elseif ( !is_numeric( $this->permission ) || !isset( $permissions[$this->permission] ) ) {
				throw new Exception( 'Invalid permission value', 5 );
			}
		}
		
		$query		=	'
			UPDATE		' . CF_USERS_TABLE . '
			SET			firstname = :firstname,
						lastname = :lastname,
						permission = :permission
			WHERE		id = :userid'
		;
		$dbo->createQuery( $query );
		$dbo->bind( ':firstname', $this->firstname );
		$dbo->bind( ':lastname', $this->lastname );
		$dbo->bind( ':permission', $this->permission );
		$dbo->bind( ':userid', $this->id );
		$updatequery = $dbo->runQuery();
		
		if ( $dbo->hasError( $updatequery ) ) {
			$dbo->submitErrorLog( $updatequery, 'CF_User::commit()' );
			throw new Exception( 'An error occurred while editing the user.', CF_MSG_ERROR );
		}
		
		return true;
	}
	
	// Changes and commits the current state of this object with the passed-in array of values
	public function update( array $options ) {
		
		foreach( $options as $property => $value ) {
			switch( $property ) {
				case 'firstname':
				case 'lastname':
				case 'permission':
					$this->$property = $value;
					break;
			}
		}
		
		return $this->commit();
	}
	
	// Gets the username of a user according to the specified user id
	public static function getUsername( $id ) {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		username
			FROM		' . CF_USERS_TABLE . '
			WHERE		id = "' . $dbo->sqlsafe( $id ) . '"'
		;
		$userquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $userquery ) ) {
			$dbo->submitErrorLog( $userquery, 'CF_User::getUsername()' );
			throw new Exception( 'An error occurred while retriving the specified user information.', CF_MSG_ERROR );
			return false;
		}
		
		$username = ($dbo->num_rows( $userquery ) == 1) ? $dbo->field( 0, 'username', $userquery ) : false;
		$dbo->free( $userquery );
		return $username;
	}
	
	// Checks to see if a user exists using a specified user id or username
	public static function exists( $criteria ) {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		id
			FROM		' . CF_USERS_TABLE . '
			WHERE		username = "' . $dbo->sqlsafe( $criteria ) . '"
			OR			id = "' . $dbo->sqlsafe( $criteria ) . '"'
		;
		$userquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $userquery ) ) {
			$dbo->submitErrorLog( $catquery, 'CF_User::exists()' );
			throw new Exception( 'An error occurred while checking whether or not the specified user exists.', CF_MSG_ERROR );
			return false;
		}
		
		$exists = ($dbo->num_rows( $userquery ) == 1);
		$dbo->free( $userquery );
		return $exists;
	}
	
	// Handles user property access
	public function __get( $property ) {
		if ( array_key_exists( $property, get_object_vars( $this ) ) ) {
			return $this->$property;
		} else {
			return null;
		}
	}
	
	// Sets the value of a property (if existent, and only if property is mutable)
	public function __set( $property, $value ) {
		if ( array_key_exists( $property, get_object_vars( $this ) ) ) {
			// Change value only if property is mutable and handle triggers for property dependencies
			switch( $property ) {
				case 'firstname':
				case 'lastname':
					$this->$property = $value;
					$this->fullname = $this->firstname . ' ' . $this->lastname;
					break;
				case 'permission':
					$this->$property = $value;
					$this->needspermission = ($this->permission == 1) ? 1 : 0;
					break;
				default:
					break;
			}
		} else {
			throw new Exception( 'Trying to set non-existent property', 3 );
		}
	}
	
	// Returns the session ID stored in the database for the current user (if online)
	private function _session_id() {
		$dbo =& CF_Factory::getDBO();
		
		$query		=	'
			SELECT		sessionID
			FROM		' . CF_SESSION_TABLE . '
			WHERE		user_id = "' . $dbo->sqlsafe( $this->id )  . '"'
		;
		$sessionquery = $dbo->query( $query );

		if ( $dbo->hasError( $sessionquery ) ) {
			$dbo->submitErrorLog( $sessionquery, 'CF_User::isOnline()' );
			return false;
		}
		
		if ( $dbo->num_rows() ) {
			$sessionID = $dbo->field( 0, 'sessionID', $sessionquery );
			$dbo->free( $sessionquery );
			return $sessionID;
		} else {
			return false;
		}
	}
	
	public static function _validEmail( $email ) {
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex) {
		  $isValid = false;
	   } else {
		  $domain = substr($email, $atIndex+1);
		  $local = substr($email, 0, $atIndex);
		  $localLen = strlen($local);
		  $domainLen = strlen($domain);
		  if ($localLen < 1 || $localLen > 64)
		  {
			 // local part length exceeded
			 $isValid = false;
		  }
		  else if ($domainLen < 1 || $domainLen > 255)
		  {
			 // domain part length exceeded
			 $isValid = false;
		  }
		  else if ($local[0] == '.' || $local[$localLen-1] == '.')
		  {
			 // local part starts or ends with '.'
			 $isValid = false;
		  }
		  else if (preg_match('/\\.\\./', $local))
		  {
			 // local part has two consecutive dots
			 $isValid = false;
		  }
		  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		  {
			 // character not valid in domain part
			 $isValid = false;
		  }
		  else if (preg_match('/\\.\\./', $domain))
		  {
			 // domain part has two consecutive dots
			 $isValid = false;
		  }
		  else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
		  {
			 // character not valid in local part unless 
			 // local part is quoted
			 if (!preg_match('/^"(\\\\"|[^"])+"$/',
				 str_replace("\\\\","",$local)))
			 {
				$isValid = false;
			 }
		  }
		  if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		  {
			 // domain not found in DNS
			 $isValid = false;
		  }
	   }
	   return $isValid;
	}
}

/*
class CF_Basic extends CF_User {
	
	public function create() {
		echo 'Created User';
	}
	public function delete() {
		echo 'Deleted User';
	}
	public function get() {
		echo 'Got User';
	}
	
}

class CF_Admin extends CF_User {
	
	public function create() {
		echo 'Created Admin';
	}
	public function delete() {
		echo 'Deleted Admin';
	}
	public function get() {
		echo 'Got Admin';
	}
	
}
*/

?>