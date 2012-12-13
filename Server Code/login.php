<?php
require_once realpath('./cf/config.php');

header( 'Content-Type: application/json' );

if ( !isset( $user ) ) {
	if ( !empty( $_POST ) && !empty( $_POST['username'] ) && !empty( $_POST['password'] ) ) {
		
		try {
			// Authenticate the user
			if ( !isset( $_POST['firstname'] ) || empty( $_POST['firstname'] ) || !isset( $_POST['lastname'] ) || empty( $_POST['lastname'] ) ) {
				$response = CF_User::authenticate( $_POST['username'], $_POST['password'] );
			} else {
				$response = CF_User::authenticate( $_POST['username'], $_POST['password'], $_POST['firstname'], $_POST['lastname'], $_POST['zip_code'], $_POST['phone'] );
			}
		} catch ( Exception $e ) {
			echo json_encode( array( 'response' => 'error', 'error' => $e->getMessage() ) );
			exit;
		}
		
		// Handle the response appropriately
		switch( $response ) {
			// Authentication sucessful
			case CF_AUTH_SUCCESS:
				// Log the user in
				$specified_user = new CF_User( $_POST['username'] );
				$login_response = $specified_user->login();
				
				// Handle the response appropriately
				switch( $login_response ) {
					// Login successful
					case CF_LOGIN_SUCCESS:
						echo json_encode( array( 
							'response' => 'success', 
							'userInfo' => array( 
								'username' => $specified_user->username,
								'firstname' => $specified_user->firstname,
								'lastname' => $specified_user->lastname,
								'zip_code' => $specified_user->zip_code
							), 
							'content' => '<p class="center">Thanks for being awesome!</p><input type="button" value="Log out" class="stitch-button" onclick="location.href=\'./logout\';" />' 
						) );
						break;
					
					// Login unsuccessful (SQL error)
					case CF_LOGIN_ERR_SQL:
						echo json_encode( array( 'response' => 'error' ) );
						break;
				}
				
				break;
			
			// New user
			case CF_AUTH_NEWUSER:
				echo json_encode( array( 'response' => 'newuser' ) );
				break;
			
			// Incorrect username/password
			case CF_AUTH_INVALID:
				echo json_encode( array( 'response' => 'failure' ) );
				break;
			
			// SQL Error
			case CF_AUTH_ERR_SQL:
			
			// LDAP Error
			case CF_AUTH_ERR_LDAP:
			
			// Generic Error
			default:
				echo json_encode( array( 'response' => 'error' ) );
				break;
		}
	} else {
		echo json_encode( array( 'response' => 'failure' ) );
	}
} else {
	if ( $user->loggedIn() ) {
		echo json_encode( array( 'response' => 'multiple' ) );
	}
}
