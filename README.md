# KVBData
Parsing disorder-data crawled from Koelner Verkehrsbetriebe

File kvb.php is the command-line cronjob script. It reads the html files, which are crawled with wget and inserts entries into the database. 
File data.php is a simple and frontend to basic-view the data
File text.php is used to analyze the messages. The purpose is to create an automation to categorize the disorder-messages
