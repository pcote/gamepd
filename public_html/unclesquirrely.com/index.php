<html>
<head>
<title>Uncle Squirrely - The Second Run Theatre of Video Games</title>
<meta name="keywords" content= "cheap games, video games, wii, Nintendo, Playstation 3, PS3, XBox 360, console games" >
<script type = "text/javascript" src = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>


<style type = "text/css">
#side_image{background-image: url("image/Acorn.png");}
a {text-decoration: underline;color: #EB1D1D;}
a:hover {text-decoration: none;}
</style>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-10592193-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<script type="text/javascript">

var rightKey = 39;
var leftKey = 37;


function nextPage( pageDirection ){
	var pageNum = (document.formCurrentState.pagenum.value) * 1;
	var maxPage = (document.getElementById( "pagemax" ).value) * 1;
	
	var startRec = 0;
	var endRec = 10;
	
	var leftArrow = document.getElementById( "leftArrow" );
	var rightArrow = document.getElementById( "rightArrow" );

	if( pageDirection == 'next' ){
		leftArrow.style.visibility = "visible";
		currentPage = document.getElementById( "pagenum" ).value * 1;
		
		if( currentPage <= maxPage )
			document.formCurrentState.pagenum.value = pageNum + 1;
	}

	else if( pageDirection == 'previous' ){
		document.formCurrentState.pagenum.value = pageNum - 1;
	}

	// safety check to ensure you don't go off into zeros and negative land with the paging.
	if( document.formCurrentState.pagenum.value == '0' ){https://mail.google.com/mail/?ui=2&shva=1#inbox
		document.formCurrentState.pagenum.value = 1;
		
	}
	

	loadGameData( 'current', 'current' );
	
}

function keyTest( evt ){
	if( evt.which == rightKey )
			nextPage( 'next' );
	else if( evt.which == leftKey )
		nextPage( 'previous' );
}

function getPageCount( platform ){
	var pageCount = 1;
	var requrl = "page_count.php?platform="+platform;
	$(document).ready(
		function(){ /* went down to ajax because there was no specific component to select at jquery level */
			ajaxResults = $.ajax( { url:requrl, async:false} );
			$(":input[name=pagemax]").val( ajaxResults.responseText );
		}
	);

}

function loadGameData( platform, order ){
	
	if( platform != order )
		document.formCurrentState.pagenum.value = 1;
		
	if( platform == 'current' )
		platform = document.formCurrentState.platform.value;
	if( order == 'current' )
		order =  document.formCurrentState.order.value;

	
	getPageCount(platform);

	document.formCurrentState.platform.value = platform;
	document.formCurrentState.order.value = order;
	document.formCurrentState.submit();
	
}

function loadYoutubeVideo( gameTitle, linkID ){

	var linkElem = document.getElementById( linkID );
	var platform = document.formCurrentState.platform.value;
	var reqURL = "get_random_video.php?platform=" + platform + "&title=" + escape( gameTitle );
	var youtubeAddress = "";

	$(document).ready(
		function(){
			ajaxResults = $.ajax( { url:reqURL, async:false} );
			// note: youtube frame should be height 400 width 410.  It's not seen here since the old setup was based on thickbox
			// which we're no longer using.
			youtubeAddress = ajaxResults.responseText;
			linkElem.href= youtubeAddress;
			window.location = youtubeAddress;
		}
	);


}

function displayArrows(){
	leftArrow = document.getElementById( "leftArrow" );
	rightArrow = document.getElementById( "rightArrow" );
	currentPage = document.getElementById( "pagenum" ).value;
	maxPage = document.getElementById( "pagemax" ).value;
	if( currentPage == "1" ){
		leftArrow.style.visibility = "hidden";
		rightArrow.style.visibility = "visible";
	}
	else if( currentPage == maxPage ){
		leftArrow.style.visibility = "visible";
		rightArrow.style.visibility = "hidden";
	}
	else{
		leftArrow.style.visibility = "visible";
		rightArrow.style.visibility = "visible";
	}
		
}

document.onkeydown=keyTest;

</script>
</head>

<body onload="displayArrows()">
<center>

<table width="80%">
<tr>
<td width="10%">&nbsp;</td>
<td align='center' width="80%"><div id="title"><img src = "image/us_logo.png" /></div></td>
<td width="10%" align="right" valign="top"><a class="about" href = "about.html">About This Site</a></td>
</tr>
</table>
<br />
<table width="80%">
<tr><td id="blank_id" align="center">
<a href = "javascript:loadGameData( 'ps3', 'current' )">Playstation 3</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'xbox360', 'current' )">XBox 360</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'wii', 'current' )">Wii</a><br />

<a href = "javascript:loadGameData( 'current', 'last_updated' )">Show Latest Drops</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'cheap' )">Show Cheapest</a> &nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'alpha' )">Alphabetize Them</a> &nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'release' )">Show Newest</a>
</td></tr>
</table>

</center>

<center>
<table width="80%">
<tr>
<td align='right' width="10%" id="side_image">
<div id="leftArrow" style="visibility:hidden;"><a href = "javascript:nextPage(  'previous' )" onKeydown="keyTest"> &lt;&lt;</a>  &nbsp;&nbsp;</div>
</td>
<td align='center'><div id = "gametableid"><?php require( "game_table.php" );?></div></td>

<td align='left' width="10%" id="side_image">
<div id="rightArrow"><a href = "javascript:nextPage( 'next' )"> &gt;&gt; </a></div>
</td>
</table>
</center>

<form name = "formCurrentState" method="post">
	<input type = "hidden" name = "platform" value = "<?php echo( $platform );?>" />
	<input type = "hidden" name = "order" value = "<?php echo( $order ); ?>" />
	<input type = "hidden" name = "pagenum" id="pagenum" value = "<?php echo( $pageNum ); ?>" />
	<input type = "hidden" name = "pagemax" id="pagemax" value = "<?php include( "page_count.php" );?>"/>
</form>
</body>
</html>
