<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

$query	=	'
	SELECT		id
	FROM		' . CF_COCKTAILS_TABLE . '
	WHERE		id >= (
		SELECT 	FLOOR( MAX(id) * RAND())
		FROM 	' . CF_COCKTAILS_TABLE . '
	)
	ORDER BY	id
	LIMIT		1'
;
$randquery = $dbo->query( $query );

// If an error occurred, append to the error log and return with an error code
if ( $dbo->hasError( $randquery ) ) {
	$dbo->submitErrorLog( $randquery, 'CF_BarManager::populate()' );
	throw new Exception( 'Could not load the bar information!', CF_MSG_ERROR );
} elseif ( $dbo->num_rows( $randquery ) == 1 ) {
	CF::redirect( CF_WEBROOTPAGE . 'drink?id=' . $dbo->getResultObject( $randquery )->fetch_row()[0] );
}