<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {

	try {
		$cm = new CF_CocktailManager();
		$cocktails = $cm->search( $_GET['s'] );
		
		if ( $dbo->num_rows( $cocktails ) > 0 ) {
			echo '<pre>';
			while ( $row = $dbo->getResultObject( $cocktails )->fetch_object() ) {
				echo $row->name . "\n";
			}
			//echo $cocktail;
			echo '</pre>';
		} else {
			echo 'No search results found.';
		}
		
	} catch( Exception $e ) {
		die( $e->getMessage() );
	}

} else {
	echo 'No search term specified.';
}

?>