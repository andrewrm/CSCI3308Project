<?php
require_once realpath('./cf/config.php');

if ( !empty( $_POST ) ) {
	try {
		CF_Offer::add( $_POST );
		echo json_encode( array( 'response' => 'success' ) );
	} catch ( Exception $e ) {
		echo json_encode( array( 'response' => 'error', 'error' => $e->getMessage() ) );
	}
} else {
	echo json_encode( array( 'response' => 'invalid' ) );
}

?>