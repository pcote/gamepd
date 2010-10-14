<?php
require( "../../db_connect.php" );

// generate a page count based on platform.
$platform = $_GET['platform'];
if( $platform == null ){
	$platform = "ps3";
}

//db connection
$dbc = mysql_connect( $host, $user, $pw );
mysql_select_db( $db ) or die( "failed to connect to the database: $db" );

$maxPrice = 50;
if( $platform == 'wii' ){
	$maxPrice = 40;
}

$query = "select count(*) " .
"from games as g " .
"where price > 0 and price < $maxPrice " . 
"and lowest_price > 0 and lowest_price < price and platform = '$platform' " . 
"and release_date <= now()";



$rs = mysql_query( $query );
$arrData = mysql_fetch_array( $rs );
$gameCount = $arrData[0];
$pageCount = ceil($gameCount / 10);
echo( $pageCount );
?>
