var changePlatform = function( platform ){
	$("#platform").val( platform );
	$("#pagenum").val( "1" );
	setPageMax( platform );  // imperative wrapper to functional code... ick
}

var changeOrder = function( orderArg ){
	$("#order").val( orderArg );
	$("#pagenum").val( "1" );
}

var changePage = function( pageDirection ){

	//actual changing of the page ( if applicable )
	var pageNum =  new Number( $("#pagenum").val()).valueOf();
	var pageMax = new Number( $("#pagemax").val() ).valueOf();
	if( pageDirection == 'next' && pageNum < pageMax ){
		pageNum = pageNum + 1;
	}
	else if( pageDirection == 'previous' && pageNum > 1 ){
		pageNum = pageNum - 1;
	}

	$("#pagenum").val( new Number( pageNum ).toString() );

	// set arrow visibility
	if( pageNum == pageMax ){
		$("#nextNav").hide();
	}
	else if( pageNum == 1 ){
		$("#previousNav").hide();
	}
	else{
		$("#nextNav").show();
		$("#previousNav").show();
	}

}

var setPageMax = function( platform ){
	var pageMaxHelper = function( mPage ){
		$("#pagemax").val( mPage );
	}

	$.ajax( {url: "page_count.php?platform="+platform, success: pageMaxHelper } );
}

var loadPage = function( changeAction, changeArg ){
	return function(){
		changeAction( changeArg );

		var order = $("#order").val();
		var platform = $("#platform").val();
		var pagenum = $("#pagenum").val();
		var loadURL = "games.php?platform="+platform+"&pagenum="+pagenum+"&order="+order;
		$.ajax( { url: loadURL, success: populateDivs } );
	}
}

//arrow navigation to next and previous page with left and right arrows.
arrowNav = function( event ){
	RIGHT_ARROW = 39;
	LEFT_ARROW = 37;
	if( event.which == RIGHT_ARROW )
		loadPage( changePage, "next" )();
	else if( event.which == LEFT_ARROW )
		loadPage( changePage, "previous" )();
}


var populateDivs = function( jsonData ){
	var gameRecords = $.parseJSON( jsonData );
	var i = 0;
	while( i < gameRecords.length ){
		var gameDiv = "#game" + String( i );
		var gameRec = gameRecords[i];
		var asin = gameRec.asin;
		var platform = gameRec.platform;
		var gameTitle = gameRec.game_title;
		var price = gameRec.price;
		var item_image = gameRec.item_image;
		var item_page = gameRec.item_page;
		var lowest_price = gameRec.lowest_price;
		var article_link = gameRec.article_link;
		var last_updated = gameRec.last_updated;
		var release_date = gameRec.release_date;
		var score = gameRec.score;
		var gamehtml = "<b>" + gameRec.game_title + "</b><br />";
		gamehtml = gamehtml + "Release Date: " + release_date + "<br />";
		
		if( item_image == "NoImage" ){
			gamehtml += "<img src = 'images/no_image.jpg' />";
		}
		else{
			gamehtml += "<img src = '" + item_image + "' /><br /><br />";
		}

		gamehtml += "$" + String( gameRec.price ) + "<br />";


		if( score != null ){
			gamehtml += "<b>Review Score: " + String( score ) + "</b><br />";
		}

		gamehtml += "<a href = '" + item_page + "'>Buy Today!</a><br />";
		var chaosString = "<a class='chaostv' href = 'gamevidwin.php?platform=" + platform + "&title=" + encodeURI( gameTitle ) + "'>Watch On Chaos TV</a>";
		gamehtml += chaosString;

		$( gameDiv ).html( gamehtml );
		i++;
	}

	$(".chaostv").colorbox({iframe:true, innerWidth:450, innerHeight:400});
}

$(document).ready( function(){
	//default form variable setup.
	$("#pagenum").val("1");
	$("#platform").val("wii");
	$("#order").val("last_updated");
	//override the defaults if there are cookies available.
	var cookiePlatform = $.cookie( 'platform' );
	var cookieOrder = $.cookie( 'order' );

	if( cookiePlatform != null ){
		$("#platform").val( cookiePlatform );
		$("#order").val( cookieOrder );
	}

	setPageMax( $( "#platform" ).val() );
	var loadURL = "games.php?platform=" + $("#platform").val() + "&pagenum=1&order=" + $("#order" ).val();
	$.ajax( { url : loadURL, success : populateDivs } );

	// setup the page update callbacks
	$("#ps3Nav").click( loadPage( changePlatform, "ps3" ) );
	$("#wiiNav").click( loadPage( changePlatform, "wii" ) );
	$("#xbox360Nav").click( loadPage( changePlatform, "xbox360" ) );
	$("#latestDropNav").click( loadPage( changeOrder, "last_updated" ) );
	$("#cheapestNav").click( loadPage( changeOrder, "cheap" ) );
	$("#alphabetizeNav").click( loadPage( changeOrder, "alpha" ) );
	$("#newestNav").click( loadPage( changeOrder, "release" ) );

	$("#nextNav").click( loadPage( changePage, "next" ) );
	$("#previousNav").click( loadPage( changePage, "previous" ) );
	$( "#previousNav" ).hide(); // we're on page 1 at the start so know need to have a go backwards option.
	$(this).keydown( arrowNav );

	// lightbox setups.
	$("#aboutNav").colorbox( { height:500, width:500 } );
	$(".chaostv").colorbox({iframe:true, innerWidth:425, innerHeight:344});

});
