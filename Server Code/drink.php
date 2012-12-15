<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {

	try {
		
		$cocktail = new CF_Cocktail( $_GET['id'] );
		$ingredients = $cocktail->getIngredients();
		$directions = $cocktail->getDirections();
		$rating_info = $cocktail->getRating();
		$picture = $cocktail->getPicture();
		
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
	<title><?php echo htmlentities( $cocktail->getName(), ENT_QUOTES ); ?> :: Inebriation Proclamation</title>
	<script type="text/javascript" src="./js/jquery.min.js"></script>
	<script type="text/javascript" src="./js/jquery-ui.custom.min.js"></script>
	<script type="text/javascript" src="./js/default.js"></script>
	<link rel="stylesheet" type="text/css" href="./css/custom-theme/jquery-ui.custom.min.css" />
	<link rel="stylesheet" type="text/css" href="./css/default.css" />
</head>
<body class="PageBody">
	<div id="TheTop">
	<?php include_once './includes/left_panel.inc.php'; ?>
	<?php include_once './includes/header.inc.php'; ?>
			<div id="DrinkInformation">
			<div id="DrinkInformationTop">
			<div id="DrinkInformationLeftColumn">
				<img id="DrinkInformationPhoto" alt="Bevery!" src="./img/cocktails/<?php echo htmlentities( (!empty($picture)) ? $picture : '_404.jpg', ENT_QUOTES ); ?>" />
			</div>
			<div id="DrinkInformationRightColumn">
				<div id="DrinkInformationRightColumnL1">
					<h2><?php echo $cocktail->getName(); ?></h2>
					<h3>Ingredients:</h3>
					<div id="IngredientsList">
						<ul>
<?php
foreach ( $ingredients as $ingredient ) {
?>
							<li><?php echo $ingredient; ?></li>
<?php
}
?>
						</ul>
					</div>
					<h3 class="DrinkTitle">Directions:</h3>
					<div>
						<ol>
<?php
foreach ( $directions as $direction ) {
?>
							<li><?php echo $direction; ?></li>
<?php
}
?>
						</ol>
					</div>
				</div>
				<div id="DrinkInformationRightColumnR1">
					<div id="NewDrinkRating">
						<div id="NewDrinkRatingStars">
							<ul class="star-rating small-star">
								<li class="current-rating" style="width: <?php echo round( $rating_info[0] / 5 * 100 ); ?>%">Currently 2.5/5 Stars.</li>
								<li><a href="./rate?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>&amp;rating=1" title="1 star out of 5" class="one-star">1</a></li>
								<li><a href="./rate?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>&amp;rating=2" title="2 stars out of 5" class="two-stars">2</a></li>
								<li><a href="./rate?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>&amp;rating=3" title="3 stars out of 5" class="three-stars">3</a></li>
								<li><a href="./rate?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>&amp;rating=4" title="4 stars out of 5" class="four-stars">4</a></li>
								<li><a href="./rate?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>&amp;rating=5" title="5 stars out of 5" class="five-stars">5</a></li>
							</ul>
						</div>
					</div>
					<div id="AverageDrinkRating">
					</div>
				</div>
			</div><!--End Top-->
		</div>
		<div id="DrinkInformationBottom">
			<div id="DrinkButtonRow">
				<a href="./map?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>" id="DrinkLocationsButton1">Find this Drink</a>
				<a href="./tag?id=<?php echo htmlentities( $_GET['id'], ENT_QUOTES ); ?>" id="DrinkLocationsButton2">Tag a Bar</a>
			</div>
		</div>
	</div>
	<?php include_once './includes/footer.inc.php'; ?>