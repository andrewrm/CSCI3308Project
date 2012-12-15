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
	<?php include_once './includes/left_panel.inc.php'; ?>
	<?php include_once './includes/header.inc.php'; ?>
<?php
if ( isset( $cocktails ) ) {
?>
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
	if ( $dbo->num_rows( $cocktails ) > 0 ) {
?>
		
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
	} else {
?>
				<tr>
					<td>
						Your search returned no results.
					</td>
				</tr>

<?php
	}
?>
			</tbody>
		</table>
<?php
} else {
?>
		<div id="SearchBoxAndFriends">
			<div id="SearchBoxWineGlass">
			</div>
			<div id="SearchBoxDiv">
				<form name="LoginSearch" action="./" method="get" id="LoginSearch" accept-charset="utf-8">
					<img id="SearchWineImage" alt="wineglass" src="./img/inebpro3_small.jpg" />
					<input type="text" name="s" id="LoginTextBox">
					<input type="submit" value="Search" id="LoginTextButton">
				</form>
			</div>
		</div>
<?php
}
?>
	<?php include_once './includes/footer.inc.php'; ?>