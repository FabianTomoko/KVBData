<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KVB St&ouml;rungsdaten</title>
  <link href="jquery-ui/jquery-ui.css" rel="stylesheet">
  <style>
  	li {
  		color: #FFF;
  		background-color: #000000;
  	}
  	li:hover {
  		color: #000;
  		background-color: #CEDAED;
  	}
	.specialp {
		padding-top: 0px;
		margin-top: 2px;

	}

	.smalltext {
		font-size: 8pt;
		margin-bottom: 0px;
		padding-bottom: 0px;
	}
  </style>
  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
  <script src="jquery-ui/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#accordion" ).accordion({
    	collapsible: true,
    	active: false
    });
    $( "#tabs" ).tabs();
  } );
  </script>
</head>
<body style="background-color: #000000; color: #FFF;">
<?php
require_once("config.php");
$mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldb);

$result = $mysqli->query("SELECT * FROM linien ORDER BY nummer ASC");
$index = 0;

$topLinie = "";
$top = 0;

$faulLinie = "";
$faul = 0;
$chk = "Folgende Fahrt entf";
$gStoerungen = 0;
$gSingle = 0;
$gMulti = 0;
$gAusfall = 0;
while($linie = $result->fetch_assoc())
{
	$linien[$index] = array('linie' => $linie['nummer'], 'anfang' => strftime('%d.%m.%Y', $linie['erfasst']), 'letzte' => strftime('%d.%m.%Y', $linie['letztemeldung']));
	//$array[$index] = "<fieldset><legend>Linie: ".$linie['nummer']."</legend>";
	//$array[$index] .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	$array[$index] = "<h3>Linie: ".$linie['nummer']."</h3><div>";
	$array[$index] .= "<ul>";
	$stoerungen = 0;
	$faulheit = 0;
	$result2 = $mysqli->query("SELECT * FROM meldungen WHERE linie='".$linie['id']."'");
	while($meldung = $result2->fetch_assoc())
	{
		$stoerungen++;
		if ($meldung['startzeit'] != $meldung['endzeit'])
		{
			if (substr($meldung['meldung'], 0, strlen($chk)) == $chk)
			{
				$amz = "Am&nbsp;";
				$endz = "";
				$gSingle++;
				$gAusfall++;
				$faulheit++;
			} else {
				$amz = "Von&nbsp";
				$endz = "bis ".strftime("%d.%m.%Y - %H:%M", $meldung['endzeit']);
				$gMulti++;
			}
		} else {
			$amz = "Am&nbsp;";
			$endz = "";
			$gSingle++;
		}
		//$array[$index] .= "<tr><td>".$amz.strftime("%d.%m.%Y - %H:%M", $meldung['startzeit'])."</td><td>&nbsp;".$endz."</td><td style=\"width:10px; position: relative;\">&nbsp;</td><td>".utf8_encode($meldung['meldung'])."</td></tr>";
		$array[$index] .= "<li>".$amz.strftime("%d.%m.%Y - %H:%M", $meldung['startzeit'])."&nbsp;".$endz."&nbsp;-&nbsp;".$meldung['meldung']."</li>";
		
	}
	$result2->close();
	//$array[$index] .= "</table><br><b>Gesamt: ".$stoerungen."</b></fieldset>";
	$array[$index] .= "</ul>Gesamt: ".$stoerungen."</div>";
	$gStoerungen = $gStoerungen+$stoerungen;
	$linien[$index]['stoerungen'] = $stoerungen;
	if ($stoerungen > $top)
	{
		$top = $stoerungen;
		$topLinie = "Linie ".$linie['nummer'];
	}
	if ($faulheit > $faul)
	{
		$faulLinie = "Linie ".$linie['nummer'];
		$faul = $faulheit;
	}
	$index++;
}
$result->close();

echo "<div id=\"tabs\"><ul>";
echo "<li><a href=\"#tabs-1\">Zahlen</a></li>";
echo "<li><a href=\"#tabs-2\">Letzte Meldungen</a></li>";
echo "<li><a href=\"#tabs-3\">Hinweise</a></li>";
echo "</ul><div id=\"tabs-1\">";
echo "<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\">";
echo "<tr><td><b>Gesamt St&ouml;rungen:</b></td><td>".$gStoerungen." seit dem 13. November 2018</td></tr>";
echo "<tr><td><b>Schlimmste Linie:</b></td><td>".$topLinie." mit ".$top."&nbsp;St&ouml;rungen seit dem 13. November 2018</td></tr>";
echo "<tr><td><b>Ausgefallene Fahrten (insgesamt):</b></td><td>".$gAusfall."</td></tr>";
echo "<tr><td><b>F&auml;hrt am wenigsten:</b></td><td>".$faulLinie." - ".$faul." Fahrtausf&auml;lle</td></tr>";
//echo "<tr><td><b>Bahn&uuml;bergreifende St&ouml;rungen:</b></td><td>".$gMultu."</td></tr>";
echo "</table></div><div id=\"tabs-2\">";

$result = $mysqli->query("SELECT * FROM meldungen ORDER BY startzeit DESC LIMIT 0,10");
while($row = $result->fetch_assoc())
{
	$lres = $mysqli->query("SELECT * FROM linien WHERE id='".$row['linie']."'");
	$linie = $lres->fetch_assoc(); $lres->close();
	echo "<p class=\"smalltext\">".strftime("%d.%m.%Y - %H:%S", $row['startzeit'])."</p><p class=\"specialp\">Linie ".$linie['nummer']." - ".$row['meldung']."</p>";
}
$result->close();
/**
echo "<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\">";
for($x = 0; $x < sizeof($linien); $x++)
{
	echo "<tr><td>Linie ".$linien['linie']."<td><td>Erste Meldung: ".$linien[$x]['anfang']." - Letzte Meldung: ".$linien[$x]['letzte']."Gesamt: ".$linien[$x]['stoerungen']."</td></tr>";
}
echo "</table><hr>";
*/
echo "</div><div id=\"tabs-3\"><p>Die Quelle der Daten sind die ver&ouml;ffentlichten Betriebsst&ouml;rungen der KVB, die ich von deren Homepage herunterlade, speichere und verarbeite. Die Datenverarbeitung befindet sich noch in der Entwicklung. Fehler sind somit wahrscheinlich. Bei Fragen meldet euch unter <a href=\"https://www.facebook.com/kommtvielleichtbald\">KVB - KommtVielleichtBald</a></p></div></div><div id=\"accordion\">";
for($x = 0; $x < sizeof($array); $x++)
{
	echo $array[$x];
}
echo "</div>";
?>
</body></html>