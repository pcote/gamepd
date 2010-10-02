<?php require( "read_database.php" ); ?>



<table border="1px" width="75%" cellspacing=20 >
<tr>
<?php

//variables to enforce keeping records from going more than 5 wide on the page.
$ROW_SIZE = 5;
$curCell = 0; 

for( $gameIndx = 0; $gameIndx < count( $gameList ); $gameIndx++ ){

	if( $curCell >= $ROW_SIZE ){
		$curCell = 0;
		echo( "</tr>\n<tr>" );
	}
	$game = $gameList[ $gameIndx ];
	$asin = $game['asin'];
	$title = $game['game_title'];
	$price = $game['price'];
	$imageLink = $game['item_image'];
	$detailPageLink = $game['item_page'];
	$lowestPrice = $game['lowest_price'];

	// hack for those cases where we have a regular list price but not a lowest.
	if( $lowestPrice < 0 )
		$lowestPrice = $price;
	$reviewScore = $game['score'];
	$reviewArticleLink = $game['article_link'];
	$lastUpdate = $game[ 'last_updated' ];
?>

<td>
<?php
echo( "<center><b>$title</b></center>" );
echo( "<center><small>Last Known Price Update:<br /> $lastUpdate</small></center> <br />" );

if( $imageLink == "NoImage" ){
	echo( "<center><a href = '$detailPageLink'><img src='image/no_image.jpg' /></a></center><br />" );
}
else{
	echo( "<center><a href = '$detailPageLink'><img src = '$imageLink' /></a></center><br />" );
}
$price = number_format( $price, 2 );
$lowestPrice = number_format( $lowestPrice, 2 );
echo( "<center><font color = 'red'>list price \$$price</font></center>" );
echo( "<center><font color = 'red'>from \$$lowestPrice</font></center>" );
if( $reviewScore != Null ){
	echo( "<center><b>Review Score: <a href = '$reviewArticleLink'>$reviewScore</a></b></center>" );
}
?>
<br />
</td>
<?php
	$curCell = $curCell + 1;
}
?>
</table>



