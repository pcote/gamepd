
<?php

//variables to enforce keeping records from going more than 5 wide on the page.


for( $gameIndx = 0; $gameIndx < count( $gameList ); $gameIndx++ ){

	$game = $gameList[ $gameIndx ];
	$asin = $game['asin'];
	$title = $game['game_title'];
	$price = $game['price'];
	$imageLink = $game['item_image'];
	$detailPageLink = $game['item_page'];
	$lowestPrice = $game['lowest_price'];
	$releaseDate = $game[ 'release_date' ];


	// hack for those cases where we have a regular list price but not a lowest.
	if( $lowestPrice < 0 )
		$lowestPrice = $price;
	$reviewScore = $game['score'];
	$reviewArticleLink = $game['article_link'];
	$lastUpdate = $game[ 'last_updated' ];
?>

<div id = "game<?php echo( $gameIndx );?>">
<?php require( "single_game_view.php" ); ?>
</div>
<?php
}
?>
</table>
