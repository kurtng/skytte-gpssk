<?php
$now = time();
$then = gmstrftime("%a %d %b %Y %H:%M:%S GMT", $now);
header("Expires: $then");
?>
<html>
<head>
<STYLE>
@import url(gunnar2.css);
</STYLE>

</head>
<body class="header" topmargin="10" bottommargin="0">
<div style="position:absolute;left:15px;top:30px;font-size:10px;">
v1.11
</div>
<center>
<h1 class="header">Pistolskytte</h1>

</center>
<div style="position:absolute;right:15px;top:0px;font-size:10px;">
<img width="60" height="60" src="./images/gpssk.gif"/>
</div>
</body>
</html>
