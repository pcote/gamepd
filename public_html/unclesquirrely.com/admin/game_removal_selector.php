<?php
require( "../../../db_connect.php" );
?>
<html>
<head>
<title>Game Removal Selector</title>
<script type = "text/javascript">
	function reviseGameList(){
		platformForm = document.getElementById( "chosenPlatform" );
		
	}
</script>
</head>
<body>

<form name="system_select" id="system_select" onchange="testScript()">
	<select name = "chosenPlatform" id="chosenPlatform">
		<option value = "" default>(please select)</option>
		<option value = "wii">wii</option>
		<option value = "ps3">ps3</option>
		<option value = "xbox360">xbox360</option>
	</select>
</form>
<br />

</body>
</html>

