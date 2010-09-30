<html>
<head><title>Game Price Drop</title>
<script type = "text/javascript" src = "jquery.js"></script>
<script type="text/javascript">


function nextPage( pageDirection ){
	pageNum = (document.formCurrentState.hiddenPageNum.value) * 1
	
	var startRec = 0;
	var endRec = 10;

	if( pageDirection == 'next' ){
		document.formCurrentState.hiddenPageNum.value = pageNum + 1;
	}
	else if( pageDirection == 'previous' ){
		document.formCurrentState.hiddenPageNum.value = pageNum - 1;
	}

	loadGameData( 'current', 'current' );
	
}


function loadGameData( platform, order ){
	
	
	if( platform != order )
		document.formCurrentState.hiddenPageNum.value = 1;
		
	if( platform == 'current' )
		platform = document.formCurrentState.hiddenPlatform.value;
	if( order == 'current' )
		order =  document.formCurrentState.hiddenOrder.value;
	
	var requrl = "game_table.php?platform=" + platform + "&order=" + order + "&pagenum=" + document.formCurrentState.hiddenPageNum.value;
	$(document).ready( function(){
		$("div").load( requrl );
	} );

	document.formCurrentState.hiddenPlatform.value = platform;
	document.formCurrentState.hiddenOrder.value = order;
	
}
</script>

</head>
<body onload="loadGameData( 'ps3', 'last_updated' )">

<h1 align="center">Game Price Drop Test Page</h1>
<center>
<a href = "javascript:loadGameData( 'ps3', 'current' )">Playstation 3</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'xbox360', 'current' )">XBox 360</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'wii', 'current' )">Wii</a><br />

<a href = "javascript:loadGameData( 'current', 'last_updated' )">Show Me Most Recent Updates</a>&nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'cheap' )">Show Me Cheapest</a> &nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'alpha' )">Alphabetize Them</a> &nbsp;&nbsp;&nbsp;
<a href = "javascript:loadGameData( 'current', 'release' )">Newest</a>
</center>
<center>

<div id = "gametableid">

</div>
<br />

</center>
<!-- navigation -->
<center>
	<a href = "javascript:nextPage(  'previous' )"> &lt;&lt; Previous</a>  &nbsp;&nbsp;
	<a href = "javascript:nextPage( 'next' )"> Next &gt;&gt; </a>
</center>
<form name = "formCurrentState">
	<input type = "hidden" name = "hiddenPlatform" value = "ps3"/>
	<input type = "hidden" name = "hiddenOrder" value = "last_updated"/>
	<input type = "hidden" name = "hiddenPageNum" value = "1" />
</form>
</body>
</html>
