#!/usr/bin/php

<?php

require_once("config.php");
$mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldb);

class KVBData {
	
	private $workdir = "/var/kvb/";
	private $pdir = "processed/";
	private $cdir = "crawler/";
	private $processCount = 0;
	
	private $linien = array();
	
	public function moveFile($filename)
	{
		if (!file_exists($this->workdir.$this->pdir.$filename))
		{
			$content = file_get_contents($this->workdir.$this->cdir.$filename);
			$fp = fopen($this->workdir.$this->pdir.$filename, "w+");
			fputs($fp, $content);
			fclose($fp);
			unlink($this->workdir.$this->cdir.$filename);
		}
	}
	public function extractContent($content, $startafter, $endbefore, $offset=0)
	{
		$startlen = strlen($startafter);
		$endlen = strlen($endbefore);
		
		$flagStart = false;
		$flagRun = true;
		
		$index = 0;
		$found = 0;
		
		$parsecontent = "";
		
		while($flagRun)
		{
			if (!$flagStart)
			{
				if (substr($content, $index, $startlen) == $startafter)
				{
					$index = $index + $startlen;
					if ($found < $offset)
					{
						$found++;
					} else {
						$flagStart = true;
					}
				} else {
					$index++;
				}
			} else {
				if (substr($content, $index, $endlen) == $endbefore)
				{
					$flagRun = false;
				} else {
					$parsecontent .= substr($content, $index, 1);
					$index++;
				}
			}
			if ($index >= strlen($content))
			{
				$flagRun = false;
			}
		}
		if ($parsecontent != "")
		{
			return $parsecontent;
		}
	}
	
	public function extractTime($string)
	{
		$split = explode("_", $string);
		$datesplit = explode("-", $split[0]);
		$timesplit = explode("-", $split[1]);
		
		$date = $datesplit[2].".".$datesplit[1].".".$datesplit[0];
		$time = $timesplit[0].":".substr($timesplit[1], 0, 2);
		
		return strtotime($date." ".$time);
		
	}
	public function processFile($filename)
	{
		$startstring = "<table class=\"table table-striped\">";
		$endstring = "</table>";
		
		$crawltime = $this->extractTime($filename);
		$fcontent = file_get_contents($this->workdir.$this->cdir.$filename);
		
		$table = $this->extractContent($fcontent, $startstring, $endstring);
		$skip = 0;
		while($list = $this->extractContent($table, "<td>", "</tr>", $skip))
		{
			$skip2 = 0;

			while($linie = $this->extractContent($list, "<li style=\"margin-right:5px;\"><span class=\"number red-text\">", "</span></li>", $skip2))
			{
				
				$linie = trim($linie);
				if (!isset($this->linien[strval($linie)]))
				{
					echo "Missing: ".$linie."\n";
					$this->addLinie($linie, $crawltime);
					
				}
				$array[$skip2] = $this->linien[strval($linie)]; //Index der Linie in SQL-DB
				$skip2++;
			}
			$message = trim($this->extractContent($list, "</ul>", "</td>"));
			
			for($x = 0; $x < sizeof($array); $x++)
			{
				$mid = $this->storeMessage($array[$x], $message, $crawltime);
				
			}
			
			unset($array);
			$skip++;
		}
		
		unset($fcontent);
		$this->moveFile($filename);
	}
	public function storeMessage($linie, $message, $zeit) {
		global $mysqli;
		$hash = md5($message);
		
		$result = $mysqli->query("SELECT * FROM meldungen WHERE linie='".$linie."' AND hash='".$hash."'");
		if ($result->num_rows > 0)
		{
			$row = $result->fetch_assoc(); $result->close();
			$update = $mysqli->query("UDPDATE meldungen SET endzeit='".$zeit."' WHERE id='".$row['id']."'");
			$return = $row['id'];
		} else {
			$insert = $mysqli->query("INSERT INTO meldungen VALUES('0', '".$linie."', '".$zeit."', '".$zeit."', '".$message."', '".$hash."')");
			$return = $mysqli->insert_id;
		}
		$update = $mysqli->query("UPDATE linien SET letztemeldung='".$zeit."' WHERE id='".$linie."'");
		return $return;
	}
	public function addLinie($linie, $zeit)
	{
		global $mysqli;
		$nummer = intval($linie);
		$insert = $mysqli->query("INSERT INTO linien VALUES(NULL, '".$nummer."', '".$zeit."', '".$zeit."')");
		$this->linien[strval($linie)] = $mysqli->insert_id;
		echo $mysqli->error."\n";
	}
	public function linesFetch()
	{
		global $mysqli;
		$result = $mysqli->query("SELECT * FROM linien");
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$this->linien[strval($row['nummer'])] = $row['id'];
			}
			$result->close();
		}
	}
	public function __construct()
	{
		$this->linesFetch();
		foreach(glob($this->workdir.$this->cdir."*.html") AS $index => $file)
		{
			$ex = explode("/", $file);
			$filename = $ex[sizeof($ex)-1];
			$this->processFile($filename);
			$this->processCount++;
		}
	}
}

$KVB = new KVBData();


?>