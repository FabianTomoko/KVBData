<?php

require_once("config.php");
$mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldb);

$result = $mysqli->query("SELECT meldung FROM meldungen ORDER BY meldung");
while($row = $result->fetch_assoc())
{
	if (isset($_GET['cut']) && $_GET['cut'] == "yes") { 
		$text = substr($row['meldung'], 0, strpos($row['meldung'], "*"));
		if (isset($_GET['deeper']) && $_GET['deeper'] == "yes") { 
			if (strpos($text, "(H)"))
			{
				$text = substr($text, 0, strpos($text, "(H)"));
			}
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
	$md5 = md5($text);
	if (!isset($array[$md5]))
	{
		$array[$md5] = $text;
	}
	//echo substr($row['meldung'], 0, strpos($row['meldung'], "*"))."<br>";
	//echo $row['meldung']."<br>";


}
$result->close();
echo "<a href=\"text.php\">Kompletter Text</a>&nbsp;&nbsp;<a href=\"text.php?cut=yes\">Beschneiden</a>&nbsp;&nbsp;<a href=\"text.php?cut=yes&deeper=yes\">Beschneiden und reduzieren</a><br><br>";
foreach($array as $k => $v)
{
	echo $v."<br>";
}
?>