<html>
<head>
<title>Game Removal Selector</title>
<script type = "text/javascript" src="jquery.js"></script>

<script type = "text/javascript">
	function reviseGameList(){
		chosenPlatform = document.getElementById( "chosenPlatform" ).value;
		var reqURL = "game_list.php?platform=" + chosenPlatform;
		$(document).ready(  function(){		
			$('#gamediv').load( reqURL );
		});
	}

	function excludeTitle( asinNum ){
		divOb = document.getElementById( asinNum );
	}
</script>
</head>
<body>

<form name="system_select" id="system_select" onchange="reviseGameList()">
	<select name = "chosenPlatform" id="chosenPlatform">
		<option value = "" default>(please select)</option>
		<option value = "wii">wii</option>
		<option value = "ps3">ps3</option>
		<option value = "xbox360">xbox360</option>
	</select>
</form>
<br />
<br />
<br />
<div id='gamediv'></div>
</body>
</html>

