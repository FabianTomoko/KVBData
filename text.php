<?php

require_once("config.php");
$mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldb);

$buttonCaption = "String to Lower";

$result = $mysqli->query("SELECT meldung FROM meldungen ORDER BY meldung");
while($row = $result->fetch_assoc())
{
	if (isset($_GET['cut']) && $_GET['cut'] == "yes") { 
		// Abschneiden der spezfisischen Zusatzinformationen
		$text = substr($row['meldung'], 0, strpos($row['meldung'], "*"));
		if (isset($_GET['deeper']) && $_GET['deeper'] == "yes") { 
			// Entfernen von (H) Haltestellen Informationen
			if (strpos($text, "(H)"))
			{
				$text = substr($text, 0, strpos($text, "(H)"));
			}
			/**
			 * Abschneiden von weiterem überflüssigem Text
			 */
			if (strpos($text, "im Bereich"))
			{
				$text = substr($text, 0, strpos($text, "im Bereich"));
			}
			$text = str_replace("ca,", "ca.", $text);
			if (strpos($text, "ca."))
			{
				$text = substr($text, 0, strpos($text, "ca."));
			}
			$text = str_replace("Der", "", $text);
			$text = str_replace("Die", "", $text);
			$text = str_replace("Das", "", $text);
		}
		
	} else {
		$text = $row['meldung'];
	}
	if (isset($_POST['tolower']) && $_POST['tolower'] == "String to Lower")
	{
		$text = strtolower($text);
		$buttonCaption = "Normale Schreibweise";
	}
	$text = trim($text);
	$md5 = md5($text);
	// Prüfen anhand des Hashes ob Text bereits so vorhanden
	if (!isset($array[$md5]))
	{
		$array[$md5] = $text; // Text samt Hash speichern
	}

}
$result->close();
echo "<a href=\"text.php\">Kompletter Text</a>&nbsp;&nbsp;<a href=\"text.php?cut=yes\">Beschneiden</a>&nbsp;&nbsp;<a href=\"text.php?cut=yes&deeper=yes\">Beschneiden und reduzieren</a><br><br><form method=\"post\"><input type=\"submit\" name=\"tolower\" value=\"".$buttonCaption."\"></form><hr><br>";
foreach($array as $k => $v)
{
	echo $v."<br>";
}
?>