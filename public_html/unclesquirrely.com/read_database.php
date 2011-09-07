<?php
class GameGetter
{

	private $orderClause = "";
	private $query = "";
	
	public function getGameData()
	{
		
		require( "../../db_connect.php" );
		// TODO: Clarification of rules for what's allowed on the pages needed here.
		// union select is repeat of query above to grab the 4 or 5 edge cases where the list price is set to zero (not actually free)
		$query = <<<EOD
		select 
		g.asin, g.game_title, g.price, g.last_updated, g.item_image, g.item_page, g.lowest_price, g.platform, g.release_date,
		gr.score, gr.article_link, gr.review_content

		from games as g
		left join game_reviews gr
		on g.asin = gr.asin

		where price > 0 and price < $this->maxPrice
		and lowest_price > 0 and lowest_price <= price and platform = '$this->platform'
		and release_date <= now()

		union 

		select g.asin, g.game_title, g.price, g.last_updated, g.item_image, g.item_page, g.lowest_price, g.platform, g.release_date,
		gr.score, gr.article_link, gr.review_content

		from games as g
		left join game_reviews gr
		on g.asin = gr.asin

		where price = 0 and platform = '$this->platform'
		and release_date <= now() 
EOD;
		
		$query = $query . " " . $this->orderClause;
		
		$query = $query . " limit $this->lowerLimit,10";

		$this->query = $query;
		$dbc = mysql_connect( $host, $user, $pw ) or die( "cannot connect to host: " . $host . " for user: " . $user );
		mysql_select_db( $db ) or die( "failed to connect to database $db" );
		
		$rs = mysql_query( $this->query );

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

		$expireDate = time() + 60 * 60 * 24 * 30; // cookie expires 30 days from now.
		setcookie( "platform", $platform, $expireDate );
		setcookie( "order", $orderType, $expireDate );
		setcookie( "pagenum", $pageNum, $expireDate );
		
	}

	public function __tostring()
	{
		return "GameGetter object instance...<br /> platform: $this->platform"; 
	}

	// exists for the sake of performance testing.
	public function getQuery(){
		return $this->query;
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


?>
