<?php
$asin = $_GET['asin'];
$reason = "None given"; // fine for the short term but this should probably have something better at some point.
$query = "insert into game_exclusions values( '" . $asin . "', '" . $reason . "')";

include( "../../../admin_connect.php" );
// override needed since the default public user can't delete stuff in ANY database.

mysql_connect( $host, $user, $pw ) or die( "could not connect to the database." );

mysql_select_db( $db ) or die( "could not select that database" );
mysql_query( $query );

$deleteQuery = "delete from games where asin = '" . $asin . "'"; 
$retVal = mysql_query( $deleteQuery );
if( !$retVal ){
	echo( mysql_error() );
}
?>
