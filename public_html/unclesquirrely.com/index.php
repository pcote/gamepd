<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Uncle Squirrely - The Second Run Theatre of Video Games</title>
<meta name="keywords" content= "cheap games, video games, wii, Nintendo, Playstation 3, PS3, XBox 360, console games" >

<script type = "text/javascript" src = "jquery-1.5.min.js"></script>
<script type = "text/javascript" src = "jquery.cookie.js"></script>
<script type = "text/javascript" src = "colorbox/colorbox/jquery.colorbox-min.js"></script>
<script type = "text/javascript" src = "gamepd.js"></script>

<link rel = "stylesheet" type="text/css" href="layout.css" />
<link rel = "stylesheet" type = "text/css" href = "colorbox.css" />
<style type = "text/css">
#side_image{background-image: url("images/Acorn.png");}
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


</head>

<body>


<div id="title"><img src = "images/us_logo.png" /></div>

<br />

<!-- menu -->
<div id = "menu">
<a id="ps3Nav" href = "javascript:void{0};">Playstation 3</a>&nbsp;&nbsp;&nbsp;
<a id="xbox360Nav" href = "javascript:void{0};">XBox 360</a>&nbsp;&nbsp;&nbsp;
<a id="wiiNav" href = "javascript:void{0};">Wii</a><br />

<a id="latestDropNav" href = "javascript:void{0};">Show Latest Drops</a>&nbsp;&nbsp;&nbsp;
<a id="cheapestNav" href = "javascript:void{0};">Show Cheapest</a> &nbsp;&nbsp;&nbsp;
<a id="alphabetizeNav" href = "javascript:void{0};">Alphabetize Them</a> &nbsp;&nbsp;&nbsp;
<a id="newestNav" href = "javascript:void{0};">Show Newest</a> &nbsp;&nbsp;&nbsp;
<a id="aboutNav" href = "about.html">About This Site</a>
</div>

<div id = "main_container">
<div id="previousNav" style="visibility:visible;"><a href = "javascript:void{0};"> &lt;&lt;</a>  &nbsp;&nbsp;</div>

<!-- new div layout to be populated by json data via jquery -->
<div id = "game_container">

<div id = "col1">
	<div id = "game0"></div>
	<div id = "game5"></div>
</div>
<div id = "col2">
	<div id = "game1"></div>
	<div id = "game6"></div>
</div>
<div id = "col3">
	<div id = "game2"></div>
	<div id = "game7"></div>
</div>
<div id = "col4">
	<div id = "game3"></div>
	<div id = "game8"></div>
</div>
<div id = "col5">
	<div id = "game4"></div>
	<div id = "game9"></div>
</div>


</div> <!-- end of main_container -->

<div id="nextNav"><a href = "javascript:void{0};"> &gt;&gt; </a></div>

</div> <!-- end of main container --> 


<!-- No more layout html.  All form stuff from here on down.  -->
<form name = "formCurrentState" method="post">
	<input type = "hidden"  name = "platform" id="platform"  />
	<input type = "hidden"  name = "order"  id="order"  />
	<input type = "hidden" name = "pagenum" id="pagenum" />
	<input type = "hidden" name = "pagemax" id="pagemax" />
</form>
</body>
</html>
