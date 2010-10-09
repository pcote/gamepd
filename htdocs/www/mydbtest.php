<html>
<head><title>Game Price Drop</title>
<link type = "text/css" rel="stylesheet" href = "css/blemish/style.css"></style>
<script type = "text/javascript" src = "jquery.js"></script>
<script type="text/javascript">

var rightKey = 39;
var leftKey = 37;

function nextPage( pageDirection ){
	pageNum = (document.formCurrentState.hiddenPageNum.value) * 1;
	
	var startRec = 0;
	var endRec = 10;

	if( pageDirection == 'next' ){
		currentPage = document.getElementById( "hiddenPageNum" ).value * 1;
		maxPage = document.getElementById( "hiddenPageMax" ).value * 1;
		if( currentPage <= maxPage )
			document.formCurrentState.hiddenPageNum.value = pageNum + 1;
	}
	else if( pageDirection == 'previous' ){
		document.formCurrentState.hiddenPageNum.value = pageNum - 1;
	}

	// safety check to ensure you don't go off into zeros and negative land with the paging.
	if( document.formCurrentState.hiddenPageNum.value == '0' )
		document.formCurrentState.hiddenPageNum.value = 1;

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
		function(){
			ajaxResults = $.ajax( { url:requrl, async:false} );
			$(":input[name=hiddenPageMax]").val( ajaxResults.responseText );
		}
	);

}

function loadGameData( platform, order ){
	
	
	if( platform != order )
		document.formCurrentState.hiddenPageNum.value = 1;
		
	if( platform == 'current' )
		platform = document.formCurrentState.hiddenPlatform.value;
	if( order == 'current' )
		order =  document.formCurrentState.hiddenOrder.value;

	
	getPageCount(platform);
	
	var requrl = "game_table.php?platform=" + platform + "&order=" + order + "&pagenum=" + document.formCurrentState.hiddenPageNum.value;
	$(document).ready( function(){
		$('#gametableid').load( requrl );
	} );

	document.formCurrentState.hiddenPlatform.value = platform;
	document.formCurrentState.hiddenOrder.value = order;
	
}

document.onkeydown=keyTest;
</script>

</head>
<body onload="loadGameData( 'wii', 'last_updated' )">

<center><font size="5" align="center">Game Price Drop</font><br />
<a href = "javascript:loadGameData( 'ps3', 'current' )">Playstation 3</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'xbox360', 'current' )">XBox 360</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'wii', 'current' )">Wii</a><br />

<a href = "javascript:loadGameData( 'current', 'last_updated' )">Show Me Latest Drops</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'cheap' )">Show Me Cheapest</a> &nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'alpha' )">Alphabetize Them</a> &nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'release' )">Show Me Newest</a>
</center>
<center>
<table width="80%">
<tr>
<td align='right' width="5%"><a href = "javascript:nextPage(  'previous' )" onKeydown="keyTest"> &lt;&lt;</a>  &nbsp;&nbsp;</td>
<td align='center'><div id = "gametableid"></div></td>
<td align='left' width="5%"><a href = "javascript:nextPage( 'next' )"> &gt;&gt; </a></td>
</table>
</center>

<form name = "formCurrentState">
	<input type = "hidden" name = "hiddenPlatform" value = "ps3"/>
	<input type = "hidden" name = "hiddenOrder" value = "last_updated"/>
	<input type = "hidden" name = "hiddenPageNum" id="hiddenPageNum" value = "1" />
	<input type = "hidden" name = "hiddenPageMax" id="hiddenPageMax" />
</form>
</body>
</html>
