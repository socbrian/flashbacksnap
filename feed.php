<?php

include_once ('src/snapchat.php'); 

// Log in:
$snapchat = new Snapchat('flashbacksnap', 'Brianisfat1');

//get feed
$snaps = $snapchat->getSnaps();  

//Setup database connecton
$servername = "localhost";
$username = "snapchat";
$password = "VYPG2ym4t2EDsKwt";
$dbname = "flashbacksnap";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully <br>";

echo count($GLOBALS['snaps']);
echo '<br>';
//print_r(array_values($snaps));

    
foreach ($snaps as $snaps) {
	//Create a table in the database if user is new
	//setup sql
	$createtable = "CREATE TABLE IF NOT EXISTS ". $snaps->sender ." (
	  `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
	  `time` INTEGER NULL DEFAULT NULL COMMENT 'how long the snap is',
	  `sender` MEDIUMTEXT NULL DEFAULT NULL COMMENT 'who the sender was, should be same as table',
	  `sent` MEDIUMTEXT NULL DEFAULT NULL COMMENT 'what date and time was the snap sent',
	  `path` MEDIUMTEXT NULL DEFAULT NULL COMMENT 'where the media is saved',
	  PRIMARY KEY (`id`)
	)";
	//run the sql and spit out error
	$result = $conn->query($createtable);
	if (!$result) {
		die('Invalid query: ' . mysql_error());
	}
	
	//create new media folder if none exists 
	if (!file_exists('./media/'. $snaps->sender .'')) {
		mkdir('./media/'. $snaps->sender .'', 0777, true);
	}
	
	//download the snap and save
	if ($snaps->media_type == 0) {
		$data = $snapchat->getMedia($snaps->id);
		$media = './media/'. $snaps->sender .'/' . $snaps->sent . '.png';
		file_put_contents($media, $data);
		echo 'picture saved <br>';
	} else if ($snaps->media_type == 1) {
		$data = $snapchat->getMedia($snaps->id);
		$media = './media/'. $snaps->sender .'/' . $snaps->sent . '.mp4';
		file_put_contents($media, $data);
		echo 'video saved <br>';
	} else {
		echo '<td>no media</td>';
	} 
	
	//enter data into the database
	$instertdata = "INSERT INTO ". $snaps->sender ." (`time`,`sender`,`sent`,`path`) VALUES ('". $snaps->time ."','". $snaps->sender . "','". $snaps->sent ."','". $media ."')";
	//run the sql to intesent data into database
	$result = $conn->query($instertdata);
	if (!$result) {
		die('Invalid query: ' . mysql_error());
	}

	// Mark the snap as viewed:
	$snapchat->markSnapViewed('$snaps->id');

	echo 'done <br>';
} 

// clear the feed
$snapchat->clearFeed();

// Log out:
$snapchat->logout(); 
?>
