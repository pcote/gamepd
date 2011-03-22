<?php 
require( "read_database.php" );
 ?>

<link media="screen" rel="stylesheet" href="colorbox.css" />

<script type = "text/javascript" src = "jquery.colorbox.js"></script>

<table border="1px" width="100%" cellspacing=20>
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
	$releaseDate = $game[ 'release_date' ];


	// hack for those cases where we have a regular list price but not a lowest.
	if( $lowestPrice < 0 )
		$lowestPrice = $price;
	$reviewScore = $game['score'];
	$reviewArticleLink = $game['article_link'];
	$lastUpdate = $game[ 'last_updated' ];
?>

<td>
<?php
echo( "<font color = '#444444'>" );
echo( "<center><b>$title</b></center>" );
$order = "last_updated";
if( isset( $_POST['order' ] ) )
{
	$order = $_POST['order'];
}

if( $order == 'release' )
	echo( "<center><small>Release Date:<br /> $releaseDate</small></center> <br />" );
else
	echo( "<center><small>Last Known Price Update:<br /> $lastUpdate</small></center> <br />" );

if( $imageLink == "NoImage" ){
	echo( "<center><img src='image/no_image.jpg' /></center><br />" );
}
else{
	echo( "<center><img src = '$imageLink' /></center><br />" );
}
echo("</font>" );
$printedListPrice = number_format( $price, 2 );
if( $price > 0 ){
	$printedListPrice = "\$$printedListPrice";
}
else{
	$printedListPrice = "Uknown";
}
$lowestPrice = number_format( $lowestPrice, 2 );
echo( "<center><font color = 'red'>list price $printedListPrice</font></center>" );
echo( "<center><font color = 'red'>from \$$lowestPrice</font></center>" );
if( $reviewScore != Null ){
?>
	<center><b>Review Score: <a class="review" href = 'review_page.php?asin=<?php echo( $asin ); ?>'><?php echo( $reviewScore );?></a></b>
<?php
}
?>
<center><a href = "<?php echo( $detailPageLink )?>">Buy Today!</a></center>
<center><a class="chaostv" href='gamevidwin.php?platform=<?php echo($platform);?>&title=<?php echo(urlencode($title));?>'><font size="2">Watch on Chaos TV</font></a></center><br />
</td>
<?php
	$curCell = $curCell + 1;
}
?>
</table>
