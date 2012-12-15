<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

$measures = CF_CocktailManager::getMeasures();

if ( !empty( $_POST ) ) {

	try {
		
		$_POST['picture'] = '';
		if ( isset( $_FILES['picture'] ) ) {
			$_POST['picture'] = $_FILES['picture'];
		}
		
		$cocktail = CF_Cocktail::add( $_POST );
		CF::redirect( CF_WEBROOTPAGE );
		
		
	} catch( Exception $e ) {
		die( $e->getMessage() );
	}

}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>
Add a Drink
</title>
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
		<div id="AddDrinkArea">
		<!--Begin Add Drink Form-->
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
		<fieldset id="AddDrinkFieldset">
				<!--<div id="AddDrinkBanner">
					<h3 id="AddDrinkHeader">
						Add a Drink
					</h3>
				</div>-->
				<div id="DrinkNameAndPhoto">
				<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
				<div id="DrinkNameAndPhotoLeft">
				<input id="NameText" type="text" name="name" placeholder="Name" /><br />
				</div>
				<div id="DrinkNameAndPhotoRightCenter">
				<div id="DrinkNameCenter">
				<label id="CategoryLabel" for="category">Category:</label><select name="category" id="category">
<?php
	foreach ( $categories as $category ) {
?>
				<option value="<?php echo $category[0]; ?>"><?php echo $category[1]; ?></option>
<?php
	}
?>
			</select><br />
				</div>
				<div id="DrinkNameRight">
			<input type="file" name="picture" id="picture" value="" />
				</div>
				</div>
				</div><!-- DrinkNameAndPhoto -->
				<h4 id="Ingradientsh4">Ingredients:</h4>
				<ul id="ingredients-list">
					<li>
						<input type="text" name="quantities[]" size="2" />
						<select id="MeasureSelect" name="measures[]">
<?php
	foreach ( $measures as $measure ) {
?>
						<option value="<?php echo $measure[0]; ?>"><?php echo $measure[1]; ?></option>
<?php
	}
?>
					</select>
						<input type="text" name="ingredients[]" />
						<a href="#" class="remove">X</a>
					</li>
				</ul>
				<a href="#" id="add-ingredient">Add ingredient</a>
				<h4 id="Directionsh4">Directions:</h4>
				<ol id="directions-list">
					<li>
					<input type="text" name="directions[]" />
						<a href="#" class="remove">X</a>
					</li>
				</ol>
				<a href="#" id="add-direction">Add direction</a><br /><br />
				<input id="SubmitButton" type="submit" value="Save" /><input id="CancelButton" type="button" value="Cancel" onclick="location.href='./';" />
			</fieldset>
			</form>
		<!--End AddDrink Form-->
		</div>
<?php include_once './includes/footer.inc.php'; ?>