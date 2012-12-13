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
	<div id="LeftPanel">
		<div id="LeftPanelList">
			<ul id="LeftPanelMenu" class="ClassyFontClass">
				<li>
					<a href="#">Drink Categories</a>
					<ul class="LeftPanelSubMenu ClassyFontClass">
						<li><div class="liText">Beer     </div></li>
						<li><div class="liText">Wine</div></li>
						<li><div class="liText">Spirits</div></li>
					</ul>
				</li>
				<li>
					<a href="#">Add a Drink</a>
				</li>
				<li>
					<a href="#">Write a Review</a>
				</li>
				<li>
					<a href="#">Random Drink</a>
				</li>
				<li>
					<a href="#">Add Bar</a>
				</li>
			</ul>
		</div>
	</div>
	<div id="LoginBox">
		<div id="LoginLogo">
			<img src="img/InebLogo1.jpg" alt="Logo" id="SiteLogo"/>
		</div>
		<div id="LoginSearchDiv">
		</div>
		<em id="LoginQuote">"Four shots and seven beers ago..."</em>
			<div id="DrinkInformation">
			<div id="DrinkInformationTop">
			<div id="DrinkInformationLeftColumn">
				<img id="DrinkInformationPhoto" alt="Bevery!" src="./img/cocktails/<?php echo htmlentities( (!empty($picture)) ? $picture : '_404.jpg', ENT_QUOTES ); ?>" />
			</div>
			<div id="DrinkInformationRightColumn">
				<div id="DrinkInformationRightColumnL1">
					<h3 class="DrinkTitle">Ingredients:</h3>
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
								<li class="current-rating" style="width:50%">Currently 2.5/5 Stars.</li>
								<li><a href="#" title="1 star out of 5" class="one-star">1</a></li>
								<li><a href="#" title="2 stars out of 5" class="two-stars">2</a></li>
								<li><a href="#" title="3 stars out of 5" class="three-stars">3</a></li>
								<li><a href="#" title="4 stars out of 5" class="four-stars">4</a></li>
								<li><a href="#" title="5 stars out of 5" class="five-stars">5</a></li>
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
				<div id="DrinkLocationsButton1">Locations</div>
				<div id="DrinkLocationsButton2">Tag a Location</div>
			</div>
		</div>
	</div>
	</div> <!-- The TOP -->
	<div id="Footer">
		Totally valid HTML and CSS. We Swear.
	</div>
</body>
</html>