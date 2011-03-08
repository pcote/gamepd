<?php
require( "../../db_connect.php" );
$asin = $_GET['asin'];
$query = <<<QUERYTEXT
select g.asin, g.game_title, g.platform, g.item_image, g.item_page, gr.review_content, gr.score
from games as g
join game_reviews as gr on g.asin = gr.asin
QUERYTEXT;

$query = $query . " where g.asin = '" . $asin . "'";
mysql_connect( $host, $user, $pw ) or die( "could not connect" );
$db = mysql_select_db( $db ) or die( "could not select the database." );
$rs = mysql_query( $query ) or die( "could not do the review page query" );

$gameRec = mysql_fetch_assoc( $rs ) or die( "can't get the array association from the query" );
?>
<html>
<head></head>
<body>
<?php
/*
echo( $gameRec['asin'] . "<br />" );
echo( $gameRec['game_title'] . "<br />" );
echo( $gameRec['platform'] . "<br />" );
echo( $gameRec['item_image'] . "<br />" );
echo( $gameRec['item_page'] . "<br />" );
*/
?>
<table>
<tr><td align='center'>
<a href='<?php echo( $gameRec['item_page'] );?>'><img src='<?php echo( $gameRec[ 'item_image' ] );?>' alt=''></img></a><br />
<b>Title: </b> <?php echo( $gameRec[ 'game_title' ] );?><br />
<b>System:</b> <?php echo( $gameRec['platform'] ); ?><br />
<b>Rating: </b> <?php echo( $gameRec[ 'score' ] . " stars" ); ?><br />
</td></tr>
<tr><td>
<?php
echo( $gameRec['review_content'] . "<br />" );
?>
</td></tr>
</table>
</body>
</html>
