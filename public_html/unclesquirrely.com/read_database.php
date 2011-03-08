<!-- START OF DATA SECTION -->
<?php
require( "../../db_connect.php" );

// defaults.
$platform = "wii";
$orderType = "last_updated";
$pageNum = 1;

if( isset( $_GET['platform'] ) ){
	$platform = $_GET['platform'];
}

if( isset( $_GET['order'] ) ){
	$orderType = $_GET['order'];
}

if( isset( $_GET['pagenum'] ) ){
	$pageNum = $_GET['pagenum'];
}

$maxPrice = 50;
if( $platform == 'wii' ){
	$maxPrice = 40;
}

// TODO: Clarification of rules for what's allowed on the pages needed here.
$query = "select * " .
"from games as g " .
"left join game_reviews gr " .
"on g.asin = gr.asin " .
"where price > 0 and price < $maxPrice " .
"and lowest_price > 0 and lowest_price <= price and platform = '$platform' " .
"and release_date <= now() " .
"union select * " . // union select is repeat of query above to grab the 4 or 5 edge cases where the list price is set to zero (not actually free)
"from games as g " .
"left join game_reviews gr " .
"on g.asin = gr.asin " .
"where price = 0 and platform = '$platform' " . 
"and release_date <= now()";


// calculate the order
if( $orderType == 'alpha' ){
	$query = $query . " order by game_title";
}
else if( $orderType == 'cheap' ){
	$query = $query . " order by price";
}
else if( $orderType == 'release' ){
	$query = $query . " order by release_date desc";
}

else{
	$query = $query . " order by last_updated desc";
}

//calculate the range
$lowerLimit = ($pageNum - 1) * 10;
$query = $query . " limit $lowerLimit,10";
$dbc = mysql_connect( $host, $user, $pw ) or die( "cannot connect to host: " . $host . " for user: " . $user );
mysql_select_db( $db ) or die( "failed to connect to database $db" );
$rs = mysql_query( $query );

$gameList = array();

$gameCount = 0;
while( $row = mysql_fetch_assoc( $rs ) ){
	$gameRec = array();
	$gameRec['asin'] = $row['asin'];
	$gameRec['game_title'] = $row[ 'game_title' ];
	$gameRec[ 'price' ] = $row[ 'price' ];
	$gameRec[ 'item_image' ] = $row[ 'item_image' ];
	$gameRec['item_page'] = $row['item_page'];
	$gameRec[ 'lowest_price' ] = $row[ 'lowest_price' ];
	$gameRec[ 'score' ] = $row[ 'score' ];
	$gameRec[ 'article_link' ] = $row[ 'article_link' ];
	
	$formattedDate = split( " ", $row[ 'last_updated'] );
	$formattedDate = $formattedDate[0];
	$gameRec[ 'last_updated' ] = $formattedDate; 
	
	$formattedReleaseDate = split( " ", $row['release_date'] );
	$formattedReleaseDate = $formattedReleaseDate[0];
	$gameRec['release_date'] = $formattedReleaseDate;

	$gameList[] = $gameRec;
	$gameCount = $gameCount + 1;
}

mysql_close( $dbc );
?>

<!-- END OF DATA SECTION -->
