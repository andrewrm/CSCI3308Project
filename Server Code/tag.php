<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
	
	try {
	
		$bm = new CF_BarManager();
		
		// If bar ID is set, tag bar and redirect
		if ( isset( $_GET['bar_id'] ) && !empty( $_GET['bar_id'] ) && is_numeric( $_GET['bar_id'] ) ) {
				$bar_tagged = $bm->tagBarWithDrink( $_GET['id'], $_GET['bar_id'] );
				CF::redirect( dirname( $_SERVER['REQUEST_URI'] ) . '/drink?id=' . $_GET['id'] );
		}
		
		// Else, populate bars
		$cocktail = new CF_Cocktail( $_GET['id'] );
		$available_bar_list = array();
		$available_bar_list_copy = array();
		$available_bars = $bm->populate();
		$tagged_bar_list = array();
		$bar_list = array();
		$bars = $bm->findBarsWithDrink( $_GET['id'] );
		
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
	<title>Tag a bar with <?php echo htmlentities( $cocktail->getName(), ENT_QUOTES ); ?> :: Inebriation Proclamation</title>
	<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=AIzaSyBWjNub4F1RS3n0V4421RYBeNI9kCJnNSk&amp;sensor=false"></script>
	<script type="text/javascript" src="./js/jquery.min.js"></script>
	<script type="text/javascript" src="./js/jquery-ui.custom.min.js"></script>
	<script type="text/javascript" src="./js/default.js"></script>
	<script type="text/javascript" src="./js/map.js"></script>
	<script type="text/javascript" src="./js/tag.js"></script>
	<script type="text/javascript">
			var bars = <?php
		while ( $row1 = $dbo->getResultObject( $bars )->fetch_object() ) {
			$bar_list[] = $row1;
			$tagged_bar_list[] = $row1->id;
		}
		echo json_encode( $bar_list );
?>;
			var available_bars = <?php
		while ( $row2 = $dbo->getResultObject( $available_bars )->fetch_object() ) {
			$available_bar_list_copy[] = array( 'value' => $row2->id, 'label' => $row2->name );
		}
		$count = count( $available_bar_list_copy );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( in_array( $available_bar_list_copy[$i]['value'], $tagged_bar_list ) )
				unset( $available_bar_list_copy[$i] );
		}
		foreach ( $available_bar_list_copy as $bar ) {
			$available_bar_list[] = $bar;
		}
		echo json_encode( $available_bar_list );
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
		<div class="ui-widget tag_bars">
			<form action="./tag" method="get" accept-charset="utf-8">
				<fieldset>
					<label for="tags">Bar Tags: </label>
					<input type="hidden" name="id" value="<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>" />
					<input type="hidden" name="bar_id" id="tagged-bar" value="" />
					<input type="text" id="tags" />
					<input type="submit" value="Tag Bar" />
				</fieldset>
			</form>
		</div>
		<div id="MapAndFriends">
			<div id="map"></div>
			<a href="./drink?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>" id="DrinkLocationsButton1">Back to Drink</a>
		</div>
<?php include_once './includes/footer.inc.php'; ?>