<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

$categories = CF_CocktailManager::getCategories();
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
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>Add Cocktail</title>
	<script type="text/javascript" src="./js/jquery.js"></script>
	<script type="text/javascript" src="./js/jquery-ui.custom.js"></script>
	<script type="text/javascript" src="./js/scripts.js"></script>
</head>
<body>
	<h1><a href="./">Cocktail Database</a></h1>
	<h2>Add Cocktail</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data" accept-charset="utf-8">
		<fieldset>
			<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
			<input type="text" name="name" placeholder="Name" /><br />
			<label for="category">Category:</label><select name="category" id="category">
<?php
	foreach ( $categories as $category ) {
?>
				<option value="<?php echo $category[0]; ?>"><?php echo $category[1]; ?></option>
<?php
	}
?>
			</select><br />
			<img src="./img/cocktails/_404.jpg" alt="Unknown" /><br />
			<input type="file" name="picture" id="picture" value="" />
			<h4>Ingredients:</h4>
			<ul id="ingredients-list">
				<li>
					<input type="text" name="quantities[]" size="2" />
					<select name="measures[]">
<?php
	foreach ( $measures as $measure ) {
?>
						<option value="<?php echo $measure[0]; ?>"><?php echo $measure[1]; ?></option>
<?php
	}
?>
					</select>
					<input type="text" name="ingredients[]" />
					<a href="#" id="remove">X</a>
				</li>
			</ul>
			<a href="#" id="add-ingredient">Add ingredient</a>
			<h4>Directions:</h4>
			<ol id="directions-list">
				<li>
					<input type="text" name="directions[]" />
					<a href="#" id="remove">X</a>
				</li>
			</ol>
			<a href="#" id="add-direction">Add direction</a><br /><br />
			<input type="submit" value="Save" /><input type="button" value="Cancel" onclick="location.href='./';" />
		</fieldset>
	</form>
</body>
</html>