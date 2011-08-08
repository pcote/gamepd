<?
require( "read_database.php" );
$gameGetter = null;

if( isset( $_GET['platform'] ) and isset( $_GET['order'] ) and isset( $_GET['pagenum'] ) )
{

	$platform = $_GET['platform'];
	$orderType = $_GET['order'];
	$pageNum = $_GET['pagenum'];
	
	$gameGetter = new GameGetter( $platform, $orderType, $pageNum );
}
else
{
	$gameGetter = new GameGetter();
}


$gameList = $gameGetter->getGameData();
$jsonList = json_encode( $gameList );
// echo( $jsonList );
require( "game_table.php" );
?>
