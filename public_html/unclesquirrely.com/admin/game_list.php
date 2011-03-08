<script type = "text/javascript" src = "../jquery.js"></script>
<script type = "text/javascript">
function excludeTitle( asinNum ){
	divOb = document.getElementById( asinNum );
	$(document).ready( function(){
		$('#' + asinNum ).hide( 'slow' );
		ajaxResults = $.ajax( { url:"exclude_game.php?asin=" + asinNum, async:false } );
	} );
}
</script>

<?php
require( "../../../db_connect.php" );

$platform = $_GET['platform'];

$maxPrice = 50;
if( $platform == 'wii' ){
	$maxPrice = 40;
}

$query = "select * " .
"from games " .
"where price > 0 and price < $maxPrice " .
"and lowest_price > 0 and lowest_price < price and platform = '$platform' " .
"and release_date <= now() " .
"union select * " . // union select is repeat of query above to grab the 4 or 5 edge cases where the list price is set to zero
"from games " .
"where price = 0 and platform = '$platform' " . 
"and release_date <= now() " . 
" order by game_title";


mysql_connect( $host, $user, $pw ) or die( "could not connect to the database" );
mysql_select_db( $db ) or die( "could not connect to the database." );
$rs = mysql_query( $query );
$rec = mysql_fetch_assoc( $rs );
?>

<?php
while( $rec = mysql_fetch_assoc( $rs ) ){
?>
<div id='<?php echo( $rec['asin'] ); ?>' onclick='removeRecord( '<?php echo( $rec['asin'] );?>' )'>
<table>
<tr>
<td><button name='exclude' value='exclude' onclick="excludeTitle( '<?php echo( $rec['asin'] );?>' )">exclude</button></td>
<td><?php echo( $rec['asin'] );?></td>
<td>
<?php echo( $rec['game_title'] );?><br />
<img src = '<?php echo( $rec['item_image'] );?>' />
</td>
</tr>
</table>
</div>
<?php
}
?>
