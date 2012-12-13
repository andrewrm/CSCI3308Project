<?php
require_once realpath( './cf/config.php' );

// Log the user out
if ( isset ( $user ) )
	$user->logout();

CF::redirect( CF_WEBROOTPAGE );

?>