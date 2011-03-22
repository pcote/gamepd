<?php
class GameGetter
{

	private $orderClause = "";
	
	public function getGameData()
	{
		
		require( "../../db_connect.php" );
		// TODO: Clarification of rules for what's allowed on the pages needed here.
		$query = "select * " .
		"from games as g " .
		"left join game_reviews gr " .
		"on g.asin = gr.asin " .
		"where price > 0 and price < $this->maxPrice " .
		"and lowest_price > 0 and lowest_price <= price and platform = '$this->platform' " .
		"and release_date <= now() " .
		"union select * " . // union select is repeat of query above to grab the 4 or 5 edge cases where the list price is set to zero (not actually free)
		"from games as g " .
		"left join game_reviews gr " .
		"on g.asin = gr.asin " .
		"where price = 0 and platform = '$this->platform' " . 
		"and release_date <= now() ";
		
		$query = $query . " " . $this->orderClause;
		
		$query = $query . " limit $this->lowerLimit,10";
		
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
		return $gameList;
	}

	public function __construct( $platform = "wii", $orderType = "last_updated", $pageNum = 1 )
	{
		$this->setPlatform( $platform );
		$this->setOrder( $orderType );
		$this->setPageNum( $pageNum );
	}

	public function __tostring()
	{
		return "GameGetter object instance...<br /> platform: $this->platform"; 
	}

	private function setPageNum( $pageNum )
	{
		$this->pageNum = $pageNum;
		$this->lowerLimit = ($pageNum - 1 ) * 10;
	}


	private function setOrder( $orderType )
	{
		if( $orderType == 'cheap' ){
			$this->orderClause = "order by price";
		}
		elseif( $orderType == 'alpha' ){
			$this->orderClause = "order by game_title"; 
		}
		elseif( $orderType == 'release' ){
			$this->orderClause = "order by release_date desc";
		}
		else{
			$this->orderClause = "order by last_updated desc";
		}
		//echo( "<br /><br />new order clause is... $this->orderClause" );
	}

	// note: platform determines the max allowable price.
	private function setPlatform( $platform ){
		$this->platform = $platform;
		if( $platform == 'wii' )
		{
			$this->maxPrice = 40;
		}

		else
		{
			$this->maxPrice = 50;
		}
	}

	

}

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
?>
