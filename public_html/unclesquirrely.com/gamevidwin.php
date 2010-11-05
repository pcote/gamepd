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

$videoIDs = substr( $commaString, 0, strlen( $commaString ) - 1 );

?>


<html>
  <head>
    <title>Game Video Window</title>
    <style type="text/css">
      #videoDiv {
        margin-right: 3px;
      }
      #videoInfo {
        margin-left: 3px;
      }
    </style>
    <script src = "jquery.js" type = "text/javascript"></script>
    <script src="http://www.google.com/jsapi" type="text/javascript"></script>
    <script type="text/javascript">
      google.load("swfobject", "2.1");
    </script>    
    <script type="text/javascript">
      /*
       * Change out the video that is playing
       */
      
      // Update a particular HTML element with a new value
      function updateHTML(elmId, value) {
        document.getElementById(elmId).innerHTML = value;
      }
      function getRandomVideo(){
	var optionList = document.frmVidChoices.hiddenVideoIds.value;
	optionArray = optionList.split( "," );
	var randomIndex = Math.floor( Math.random()*optionArray.length );
	videoID = optionArray[ randomIndex ];
	return videoID;
      }
      // Loads the selected video into the player.
      function loadVideo() {
        //var selectBox = document.getElementById("videoSelection");
        //var videoID = selectBox.options[selectBox.selectedIndex].value
	var optionList = document.frmVidChoices.hiddenVideoIds.value;
	optionArray = optionList.split( "," );
	var randomIndex = Math.floor( Math.random()*optionArray.length );
	videoID = optionArray[ randomIndex ];
	 
        
        if(ytplayer) {
          ytplayer.loadVideoById(videoID);
        }
      }
      
      // This function is called when an error is thrown by the player
      function onPlayerError(errorCode) {
        alert("An error occured of type:" + errorCode);
      }
      
      // This function is automatically called by the player once it loads
      function onYouTubePlayerReady(playerId) {
        ytplayer = document.getElementById("ytPlayer");
        ytplayer.addEventListener("onError", "onPlayerError");
	ytplayer.playVideo();
      }
      
      // The "main method" of this sample. Called when someone clicks "Run".
      function loadPlayer() {
        // The video to load
        var videoID = getRandomVideo();
        // Lets Flash from another domain call JavaScript
        var params = { allowScriptAccess: "always", autoplay:1 };
        // The element id of the Flash embed
        var atts = { id: "ytPlayer" };
        // All of the magic handled by SWFObject (http://code.google.com/p/swfobject/)
        swfobject.embedSWF("http://www.youtube.com/v/" + videoID +
                           "&enablejsapi=1&playerapiid=player1",
                           "videoDiv", "425", "344", "8", null, null, params, atts);
	
      }
      function _run() {
        loadPlayer();
      }
      google.setOnLoadCallback(_run);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;" onload="loadVideo();">
    <table>
    <tr></tr>
    <tr><td>
    <td><div id="videoDiv">Loading...</div></td>
    </tr>
    </table>
	<center><a href = "javascript:void(0);" onclick="loadVideo();"><font size = "2">bored now</font> <b>NEXT!!!</b></a></center>
	
	<form name = "frmVidChoices">
		<input type = "hidden" name = "hiddenVideoIds" value = "<?php echo( $videoIDs );?>" />
	</form>
  </body>
</html>

