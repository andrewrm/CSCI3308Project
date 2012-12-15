<?php

$categories = CF_CocktailManager::getCategories();

?>
		<div id="LeftPanel">
			<ul id="LeftPanelMenu" class="ClassyFontClass">
				<li>
					<a href="./">Home</a>
				</li>
				<li>
					<a href="./browse">Drinks</a>
					<ul class="LeftPanelSubMenu ClassyFontClass">
<?php
	foreach ( $categories as $category ) {
?>
						<li><a href="./browse?c=<?php echo $category[0]; ?>"><?php echo $category[1]; ?></a></li>
<?php
	}
?>
					</ul>
				</li>
				<li>
					<a href="./add">Add a Drink</a>
				</li>
				<li>
					<a href="./random">Random Drink</a>
				</li>
			</ul>
		</div>