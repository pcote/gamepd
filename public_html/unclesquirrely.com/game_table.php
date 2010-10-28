<?php require( "read_database.php" ); ?>

<script type = "text/javascript" src = "jquery.js"></script>
<script type = "text/javascript" src = "thickbox.js"></script>
<link rel = "stylesheet" href="thickbox.css" type = "text/css" media = "screen" />

<table border="1px" width="75%" cellspacing=20>
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
$order = $_GET['order'];

if( $order == 'release' )
	echo( "<center><small>Release Date:<br /> $releaseDate</small></center> <br />" );
else
	echo( "<center><small>Last Known Price Update:<br /> $lastUpdate</small></center> <br />" );

if( $imageLink == "NoImage" ){
	echo( "<center><a href = '$detailPageLink'><img src='image/no_image.jpg' /></a></center><br />" );
}
else{
	echo( "<center><a href = '$detailPageLink'><img src = '$imageLink' /></a></center><br />" );
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
	echo( "<center><b>Review Score: <a href = '$reviewArticleLink'>$reviewScore</a></b></center>" );
}
?>

<center><a class = 'thickbox' href='gamevidwin.php?platform=<?php echo($platform);?>&title=<?php echo(urlencode($title));?>&KeepThis=true&TB_iframe=true&height=400&width=410'><font size="2">Chaos TV</font></a></center><br />
</td>
<?php
	$curCell = $curCell + 1;
}
?>
</table>



