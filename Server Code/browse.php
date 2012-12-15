<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/cf/config.php';


try {
	
	$cm = new CF_CocktailManager();
	
	if ( isset( $_GET['c'] ) && !empty( $_GET['c'] ) ) {
		$cocktails = $cm->populate( $_GET['c'] );
		$categories = CF_CocktailManager::getCategories();
		foreach ( $categories as $category ) {
			if ( $category[0] == $_GET['c'] )
				$categoryName = $category[1];
		}
	} else {
		$cocktails = $cm->populate();
	}
	
} catch( Exception $e ) {
	die( $e->getMessage() );
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>
Browse Drinks :: Inebriation Proclamation
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
	if ( $dbo->num_rows( $cocktails ) > 0 ) {
?>
		<table id="ResultsTable" class="ClassyFontClass">
			<thead>
				<tr>
					<th>
						Available Drinks<?php if ( isset( $_GET['c'] ) ) echo ' (Category: ' . $categoryName . ')'; ?>
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
<?php
	} else {
?>
		<div id="ResultsTableDiv">
			There are no drinks in the database.
		</div>
<?php
	}
}
?>
	<?php include_once './includes/footer.inc.php'; ?>