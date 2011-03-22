<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Uncle Squirrely - The Second Run Theatre of Video Games</title>
<meta name="keywords" content= "cheap games, video games, wii, Nintendo, Playstation 3, PS3, XBox 360, console games" >
<link media="screen" rel="stylesheet" href="colorbox.css" />
<script type = "text/javascript" src = "jquery-1.5.min.js"></script>
<script type = "text/javascript" src = "jquery.colorbox.js"></script>

<style type = "text/css">
#side_image{background-image: url("image/Acorn.png");}
a {text-decoration: underline;color: #EB1D1D;}
a:hover {text-decoration: none;}
</style>

<!-- Analytics monitoring.  Nothing to see here. -->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-10592193-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); 
    ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; 
    s.parentNode.insertBefore(ga, s);
  })();

</script>

<script type="text/javascript">


var changePlatform = function( platform ){
	$("#platform").val( platform );
	$("#pagenum").val( "1" );
	setPageMax( platform );  // imperative wrapper to functional code... ick
}

var changeOrder = function( orderArg ){
	$("#order").val( orderArg );
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
		var loadURL = "game_table.php?platform="+platform+"&pagenum="+pagenum+"&order="+order;
		$("#gametableid" ).load( loadURL );
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
// it doesn't do anything.  loadPage returns a function.  click takes a function as an argument.  Weird.
$(document).ready( function(){
	//default form variable setup.
	$("#platform").val("wii");
	$("#order").val("last_updated");
	$("#pagenum").val("1");
	setPageMax( "wii" ); // TODO: tech debt.  not a very "functional" way to set the pagemax variable.

	var loadURL = "game_table.php?platform=wii&pagenum=1&order=last_updated";
	$("#gametableid").load( loadURL );

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
	$(".about").colorbox( { width:"50%", height:"50%", iframe:true} );
	$(".chaostv").colorbox( {iframe:true, innerWidth:450, innerHeight:425} );
});



</script>
</head>

<body>
<center>

<table width="80%">
<tr>
<td width="10%">&nbsp;</td>
<td align='center' width="80%"><div id="title"><img src = "image/us_logo.png" /></div></td>
<td width="10%" align="right" valign="top"><a class="about" id="navAbout" href = "about.html">About This Site</a></td>
</tr>
</table>
<br />
<table width="80%">
<tr><td id="blank_id" align="center">
<a id="ps3Nav" href = "javascript:void{0};">Playstation 3</a>&nbsp;&nbsp;&nbsp;
<a id="xbox360Nav" href = "javascript:void{0};">XBox 360</a>&nbsp;&nbsp;&nbsp;
<a id="wiiNav" href = "javascript:void{0};">Wii</a><br />

<a id="latestDropNav" href = "javascript:void{0};">Show Latest Drops</a>&nbsp;&nbsp;&nbsp;
<a id="cheapestNav" href = "javascript:void{0};">Show Cheapest</a> &nbsp;&nbsp;&nbsp;
<a id="alphabetizeNav" href = "javascript:void{0};">Alphabetize Them</a> &nbsp;&nbsp;&nbsp;
<a id="newestNav" href = "javascript:void{0};">Show Newest</a>
</td></tr>
</table>

</center>

<center>
<table width="80%">
<tr>
<td align='right' width="10%" id="side_image">
<div id="previousNav" style="visibility:visible;"><a href = "javascript:void{0};"> &lt;&lt;</a>  &nbsp;&nbsp;</div>
</td>
<td align='center'><div id = "gametableid"></div></td>

<td align='left' width="10%" id="side_image">
<div id="nextNav"><a href = "javascript:void{0};"> &gt;&gt; </a></div>
</td>
</table>
</center>

<form name = "formCurrentState" method="post">
	<input type = "hidden"  name = "" id="platform"  />
	<input type = "hidden"  name = "order"  id="order"  />
	<input type = "hidden" name = "pagenum" id="pagenum" />
	<input type = "hidden" name = "pagemax" id="pagemax" />
</form>
</body>
</html>
