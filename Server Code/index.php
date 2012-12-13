<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';

if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {

	try {
		
		$cm = new CF_CocktailManager();
		$cocktails = $cm->search( $_GET['s'] );
		
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
<?php echo (isset( $cocktails )) ? htmlentities( $_GET['s'], ENT_QUOTES ) . ' :: Inebriation Proclamation' : 'Welcome to The Inebriation Proclamation'; ?>
</title>
	<link href="./css/custom-theme/jquery-ui-1.9.1.custom.css" rel="stylesheet">
	<script src="./js/jquery.min.js" type="text/javascript"></script>
	<script src="./js/jquery-ui.custom.min.js" type="text/javascript"></script>
	<script src="./js/default.js" type="text/javascript"></script>
	<link href="./css/default.css" rel="stylesheet" type="text/css">
</head>
<body class="PageBody">
	<div id="TheTop">
	<div id="LeftPanel">
		<div id="LeftPanelList">
			<ul id="LeftPanelMenu" class="ClassyFontClass">
				<li>
					<a href="#">Drink Categories</a>
					<ul class="LeftPanelSubMenu ClassyFontClass">
						<li><div class="liText">Beer</div></li>
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
		<i id="LoginQuote">"Four shots and seven beers ago..."</i>
<?php
if ( isset( $cocktails ) ) {
	if ( $dbo->num_rows( $cocktails ) > 0 ) {
?>
		<div id="ResultsTableDiv">
			<table id="ResultsTable" class="ClassyFontClass">
				<thead>
					<tr>
						<th>
							Search Results
						</th>
					</tr>
				</thead>
				<tbody>
<?php
		while ( $row = $dbo->getResultObject( $cocktails )->fetch_object() ) {
?>
					<tr>
						<td>
							<a href="./drink?id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a>
						</td>
					</tr>
<?php
		}
?>
				</tbody>
			</table>
		</div>
<?php
	} else {
?>
		<div id="ResultsTableDiv">
			Your search returned no results.
		</div>
<?php
	}
} else {
?>
		<div id="SearchBoxAndFriends">
			<div id="SearchBoxWineGlass">
				<img id="SearchWineImage" alt="wineglass" src="img/inebpro3.jpg" />
			</div>
			<div id="SearchBoxDiv">
				<form name="LoginSearch" action="./" method="get" id="LoginSearch" accept-charset="utf-8">
					<input type="text" name="s" id="LoginTextBox">
					<input type="submit" value="Search" id="LoginTextButton">
				</form>
			</div>
		</div>
<?php
}
?>
	</div>
	<div id="Footer">
		Totally valid HTML and CSS. We Swear.
	</div>
	</div> <!-- The TOP -->
</body>
</html>