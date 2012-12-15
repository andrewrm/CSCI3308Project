<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {

	try {
		
		$cocktail = new CF_Cocktail( $_GET['id'] );
		$bm = new CF_BarManager();
		$bars = $bm->findBarsWithDrink( $_GET['id'] );
		$bar_list = array();
		
	} catch( Exception $e ) {
		die( $e->getMessage() );
		CF::redirect( CF_WEBROOTPAGE );
	}

} else {
	CF::redirect( CF_WEBROOTPAGE );
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Find <?php echo htmlentities( $cocktail->getName(), ENT_QUOTES ); ?> :: Inebriation Proclamation</title>
	<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=AIzaSyBWjNub4F1RS3n0V4421RYBeNI9kCJnNSk&amp;sensor=false"></script>
	<script type="text/javascript" src="./js/jquery.min.js"></script>
	<script type="text/javascript" src="./js/jquery-ui.custom.min.js"></script>
	<script type="text/javascript" src="./js/default.js"></script>
	<script type="text/javascript" src="./js/map.js"></script>
	<script type="text/javascript">
			var bars = <?php
		while ( $row = $dbo->getResultObject( $bars )->fetch_object() ) {
			$bar_list[] = $row;
		}
		echo json_encode( $bar_list );
?>;
		</script>
	<link rel="stylesheet" type="text/css" href="./css/custom-theme/jquery-ui.custom.min.css" />
	<link rel="stylesheet" type="text/css" href="./css/default.css" />
</head>
<body class="PageBody">
	<div id="TheTop">
<?php include_once './includes/left_panel.inc.php'; ?>
<?php include_once './includes/header.inc.php'; ?>
		<h3>Locations in Boulder serving <?php echo htmlentities( $cocktail->getName(), ENT_QUOTES ); ?></h3>
		<div id="MapAndFriends">
			<div id="map"></div>
			<a href="./drink?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>" id="DrinkLocationsButton1">Back to Drink</a>
		</div>
<?php include_once './includes/footer.inc.php'; ?>