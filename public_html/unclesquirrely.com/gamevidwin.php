<script type = "text/javascript">
	function loadNewVideo( platform, title ){
		// TODO: stub needs implementing... badly.
		alert( "stub.  PLATFORM: " + platform + " TITLE: " + title  );
	}
</script>

<?php

// TODO: This whole thing virtually screams "split me up".  I'll have to get to that.
$title = $_GET['title'];

// make platform arguments a little nicer for the youtube search.
$platform = $_GET['platform'];
$platformTerm = "Playstation 3";
if( $platform == "wii" )
	$platformTerm = "Nintendo Wii";
else if( $platform == "xbox360" )
	$platformTerm = "XBox 360";


// search step results in atom feed from they shouldyoutube.
$searchTerm = "$platformTerm $title";
$xmlURL = "http://gdata.youtube.com/feeds/api/videos?q=" . urlencode( "$platformTerm $title" ); 
$fileHandle = fopen( $xmlURL, "r" );
$arr = file( $xmlURL );
fclose( $fileHandle );
$arrString = implode( "", $arr );

// earch entry has several URLs per "entry" element in the dom tree.
// The first one is usually appropriate for the video so an array of those are collected from the xml.
$xml = new SimpleXMLElement( $arrString );
$urlList = array();
foreach( $xml->entry as $curEntry ){
	$urlLink = $curEntry->link[0]['href'];
	$urlList[] =  $urlLink;
}

// pick a video at randeom from the collected url list.
srand( (double)microtime() * 1000000 );
$randomNumber = rand( 0, count( $urlList ) - 1 );
$chosenURL = $urlList[ $randomNumber ];


// Isolate and pulls the video ID and plugs it into a new URL.
// This is done because the initial url has the right video id but the url format doesn't play well with thickbox for some reason.
$urlArray = explode( "?", $chosenURL );
$argArray = explode( "&", $urlArray[1] );

$videoID = "";
for( $i = 0; $i < count( $argArray ); $i++ ){
	if( eregi( "^v=", $argArray[$i] ) )
		$vidIDPair = explode( "=", $argArray[$i] );
		$videoID = $vidIDPair[1];
}


$embeddedPlayerURL = "http://www.youtube.com/v/$videoID?enablejsapi=1&version=3&fs=1&autoplay=1";
?>
<html>
<head>
<title>Game Video Window</title>
</head>
<body onload = "loadNewVideo( '<?php echo( $platform ); ?>', '<?php echo( urlencode( $title ) ); ?>' )">

<object width="425" height="344">
<embed src="<?php echo( $embeddedPlayerURL ); ?>"
  type="application/x-shockwave-flash"
  allowfullscreen="false"
  allowscriptaccess="always"
  rel="0"
  border="0"
  width="425" height="344">
</embed>
</object>
<br />
<center><a onclick = "loadNewVideo( '<?php echo( $platform ); ?>', '<?php echo( urlencode( $title ) ); ?>' )" href = "gamevidwin.php?platform='<?php echo( $platform );?>'&title='<?php echo( urlencode( $title ) ); ?>'"><font size = "2">bored now</font> <b>NEXT!</b></a></center>

</body>

</html>
