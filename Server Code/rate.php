<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) && is_numeric( $_GET['id'] ) && isset( $_GET['rating'] ) && !empty( $_GET['rating'] ) && is_numeric( $_GET['rating'] ) ) {

	try {
		
		$cocktail = new CF_Cocktail( $_GET['id'] );
		$cocktail->rate( $_GET['rating'] );
		$cocktail->commit();
		CF::redirect( dirname( $_SERVER['REQUEST_URI'] ) . '/drink?id=' . $_GET['id'] );
		
		
	} catch( Exception $e ) {
		die( $e->getMessage() );
		CF::redirect( CF_WEBROOTPAGE );
	}

} else {
	CF::redirect( CF_WEBROOTPAGE );
}

