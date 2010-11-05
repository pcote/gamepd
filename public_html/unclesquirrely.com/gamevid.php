
<?php
function getVideoID( $urlArg ){
	$urlArray = explode( "?", $urlArg );
	$argArray = explode( "&", $urlArray[1] );

	$videoID = "";
	for( $i = 0; $i < count( $argArray ); $i++ ){
		if( eregi( "^v=", $argArray[$i] ) )
			$vidIDPair = explode( "=", $argArray[$i] );
		$videoID = $vidIDPair[1];

	}

	return $videoID;
}

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


//convert to a comma sepped list of video ids
$commaString = "";
foreach( $urlList as $curURL ){
	$thisVidID = getVideoID( $curURL );
	$commaString = $commaString . $thisVidID . ",";
}

$urlList = substr( $commaString, 0, strlen( $commaString ) - 1 );

echo( $urlList );
?>



