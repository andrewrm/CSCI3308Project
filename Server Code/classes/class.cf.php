<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );

final class CF {
	
	// Gets (creates if non-existent) a reference to a new database object
	public static function &getInstance() {
		static $instance;
		
		if ( !is_object( $instance ) ) {
			$class = __CLASS__;
			$instance = new $class();
			unset( $class );
		}
		
		return $instance;
	}
	
	// Adds a message to the system message queue
	/* 
	   Message Types:
	   
		Template:
		Message Constant (Legacy Message Type) - Message Description
	   
		CF_MSG_ERROR ("error") - Error Message
		CF_MSG_WARNING ("warning") - Warning/Caution Message
		CF_MSG_ANNOUNCEMENT ("announcement") - Informational Message
		CF_MSG_SUCCESS ("success") - Success Message
		
	*/
	public function enqueueMessage( $message, $type, $session ) {
		$dbo =& CF_Factory::getDBO();
		
		// Map message string types to message codes (if applicable)
		// Note: This is just for backwards compatibility; message 
		//       constants should be used everywhere.
		if ( !is_numeric( $type ) || ($type < CF_MSG_ERROR || $type > CF_MSG_SUCCESS) ) {
			switch( $type ) {
				case 'error':
					$type = CF_MSG_ERROR;
					break;
				case 'warning':
					$type = CF_MSG_WARNING;
					break;
				case 'announcement':
					$type = CF_MSG_ANNOUNCEMENT;
					break;
				case 'success':
					$type = CF_MSG_SUCCESS;
					break;
				default:
					$type = CF_MSG_WARNING;
					break;
			}
		}
		
		// Make sure such a message doesn't already exist
		$query	=	'
			SELECT		sessionID
			FROM		' . CF_MESSAGE_TABLE . '
			WHERE		sessionID = :session
			AND			type = :type
			AND			message = :msg'
		;
		$dbo->createQuery( $query );
		$dbo->bind( ':session', $session );
		$dbo->bind( ':type', $type );
		$dbo->bind( ':msg', $message );
		$duplicatequery = $dbo->runQuery();
		
		try {
			if ( $dbo->hasStopFlag() ) {
				$dbo->submitErrorLog( $duplicatequery, 'CF::enqueueMessage() - Error finding duplicates' );
				throw new Exception( 'DB query failed' );
			} else {
				if ( $dbo->num_rows( $duplicatequery ) != 0 ) {
					return true;
				}
			}
			
			// Find out what the highest ID is (if no rows exist, then return 1)
			$query = '
				SELECT	IFNULL( MAX(id) + 1, 1 ) AS maxid
				FROM	' . CF_MESSAGE_TABLE
			;
			$lastidquery = $dbo->query( $query );
			
			if ( $dbo->hasError( $lastidquery ) ) { // If not, carp about it
				$dbo->submitErrorLog( $lastidquery, 'CF::enqueueMessage() - Error finding last id' );
				//echo 'An error occurred while trying to add a message to the messaging system.';
				throw new Exception( 'DB query failed' );
			}
			
			$maxid = $dbo->field( 0, 'maxid', $lastidquery );
			
			$query	=	'
				INSERT INTO		' . CF_MESSAGE_TABLE . '
				( id, message, type, sessionID )
				VALUES
				( :id, :message, :type, :session )'
			;
			$dbo->createQuery( $query );
			$dbo->bind( ':id', $maxid );
			$dbo->bind( ':message', $message );
			$dbo->bind( ':type', $type );
			$dbo->bind( ':session', $session );
			$response = $dbo->runQuery();
			
			if ( $dbo->hasStopFlag() ) {
				$dbo->submitErrorLog( $response, 'CF::enqueueMessage() - Error enqueuing system message' );
				echo 'An error occurred while trying to add a message to the messaging system.';
				throw new Exception( 'DB query failed' );
			}
		} catch( Exception $e ) { // If the database errors out, fall back to sessions for message propagation
			if ( !isset( $_SESSION['messages'] ) || empty( $_SESSION['messages'] ) ) {
				$_SESSION['messages'][$type][] = $message;
			} elseif ( !in_array( $message, $_SESSION['messages'][$type] ) ) {
				$_SESSION['messages'][$type][] = $message;
			}
		}
		
		return true;
	}

	// Retrieves the message queue for this session
	public function getMessages( $session = null ) {
		$dbo =& CF_Factory::getDBO();
		$session = $_SESSION['sessionID'];
		
		$query	=	'
			SELECT		message, type
			FROM		' . CF_MESSAGE_TABLE . '
			WHERE		sessionID = :session'
		;
		$dbo->createQuery( $query );
		$dbo->bind( ':session', $session );
		$response = $dbo->runQuery();
		
		if ( $dbo->hasStopFlag() ) {
			$dbo->submitErrorLog( $response, 'CF::getMessages() - Error retrieving messages' );
			return array( 
				'error' => array( 'An error occurred while retrieving the system messages. Please try again later.' )
			);
		} else {
			$messages = array();
			if ( isset( $_SESSION['messages'] ) && !empty( $_SESSION['messages'] ) ) {
				foreach( $_SESSION['messages'] as $message_type => $message_cue ) {
					foreach( $message_cue as $message ) {
						$messages[$message_type][] = $message;
					}
				}
				unset( $_SESSION['messages'] );
			}
			for( $a = 0; $a < $dbo->num_rows( $response ); $a++ ) {
				// Map message codes back to message string types
				switch( $dbo->field( $a, 'type', $response ) ) {
					case CF_MSG_ERROR:
					case CF_MSG_WARNING:
					case CF_MSG_ANNOUNCEMENT:
					case CF_MSG_SUCCESS:
						$type = $dbo->field( $a, 'type', $response );
						break;
					default:
						$type = CF_MSG_WARNING;
						break;
				}
				$messages[$type][] = $dbo->field( $a, 'message', $response );
			}
			
			$query	=	'
				DELETE FROM		' . CF_MESSAGE_TABLE . '
				WHERE			sessionID = :session'
			;
			$dbo->createQuery( $query );
			$dbo->bind( ':session', $session );
			$response = $dbo->runQuery();
			
			if ( $dbo->hasStopFlag() ) {
				$dbo->submitErrorLog( $response, 'CF::getMessages() - Error deleting retrieved messages' );
				return array( 
					'error' => array(
						0 => 'An error occurred while retrieving the system messages. Please try again later.'
					)
				);
			}
		}
		
		return $messages;
		
	}
	
	// Returns a self-pointing URL
	// Note: This is a more secure alternative to using the $_SERVER['PHP_SELF'] variable.
	public static function self() {
		$s = empty( $_SERVER['HTTPS'] ) ? '' : ( ($_SERVER['HTTPS'] == 'on') ? 's' : '' );
		$port = ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? '' : (':' . $_SERVER['SERVER_PORT']);
		
		return 'http' . $s . '://' . $_SERVER['SERVER_NAME'] . ( !empty( $port ) ? ':' . $port : '' ) . $_SERVER['REQUEST_URI'];
	}
	
	// Checks whether the site is live or not
	public static function isLive() {
		return CF_STATUS == CF_ST_LIVE;
	}
	
	// Redirects the browser to a specified URL
	public static function redirect( $location ) {
		// Make sure the HTTP headers have not been sent to the browser
		if ( !headers_sent() ) {
			header( 'Location: ' . $location );
		} else { // If they have, use JavaScript to redirect
?>
				<script type="text/javascript">
					location.href = "<?php echo $location; ?>";
				</script>
<?php
		}
		exit;
	}
	
	// Generates random character sequences of specified length
	public static function generateKey( $length = 9 ) {
		$key = '';
		static $set = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$set_length = strlen( $set );
		while( $length > 0 ) {
			$key .= substr( $set, mt_rand( 0, $set_length - 1 ), 1 );
			--$length;
		}
		return $key;
	}
	
}
