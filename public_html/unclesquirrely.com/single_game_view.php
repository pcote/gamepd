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
	echo( "<center><img src='images/no_image.jpg' /></center><br />" );
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

