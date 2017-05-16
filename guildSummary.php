<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" href="wow.png">
	<title>Raid Team Summary</title>

	<!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="css/responsive.bootstrap4.min.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet"> 
        
        <!-- Custom Fonts -->
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>

<?php
    require 'functions/convertAp.php'; // Include function
	
	$queryMains = false;
	$queryAlts = false;
	$queryAFK = false;
	$timeframe = '';

	$url = "$_SERVER[QUERY_STRING]";

	if ($url) {
		$url = explode('&',$url);

		for ($x = 0; $x < sizeOf($url); $x++) {
			$parsedURL = explode('=',$url[$x]);
			if ($parsedURL[0] === 'timeFrame')
				$timeFrame = $parsedURL[1];
			if ($parsedURL[1] === 'Mains')
				$queryMains = true;
			else if ($parsedURL[1] === 'Alts')
				$queryAlts = true;
			else if ($parsedURL[1] === 'Afk')
				$queryAFK = true;
		}
	}
	if (empty($timeFrame))
		$timeFrame = 'ThisWeek';
	if (!$queryMains && !$queryAlts && !$queryAFK)
		$queryMains = true; // Default mains on or we have nothing to display
?>

<body>
	<div id="wrapper">
		<?php include('navbar.php'); ?>
		
		<?php 
		require 'database/Db.class.php'; // Include DB Class

		$db = new Db(); // New Connection to the DB
					
		if ($queryMains && $queryAlts && $queryAFK) {
			$toons = $db->query("SELECT * FROM toons");
		} else if ($queryMains && $queryAlts){
			$db->bindMore(array("RaidTeamName"=>"Mains","RaidTeamName2"=>"Alts"));
			$toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			WHERE `RaidTeamName` = :RaidTeamName OR `RaidTeamName` = :RaidTeamName2"); // Get all toons
		} else if ($queryMains && $queryAFK){
			$db->bindMore(array("RaidTeamName"=>"Mains","RaidTeamName2"=>"Afk"));
			$toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			WHERE `RaidTeamName` = :RaidTeamName OR `RaidTeamName` = :RaidTeamName2"); // Get all toons
		} else if ($queryAlts && $queryAFK){
			$db->bindMore(array("RaidTeamName"=>"Alts","RaidTeamName2"=>"Afk"));
			$toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			WHERE `RaidTeamName` = :RaidTeamName OR `RaidTeamName` = :RaidTeamName2"); // Get all toons
		} else if ($queryMains){
			$db->bind("RaidTeamName","Mains");
			$toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			WHERE `RaidTeamName` = :RaidTeamName"); // Get all toons
		} else if ($queryAFK){
			$db->bind("RaidTeamName","Afk");
			$toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			WHERE `RaidTeamName` = :RaidTeamName"); // Get all toons				
		} else if ($queryAlts){
			$db->bind("RaidTeamName","Alts");
			$toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			WHERE `RaidTeamName` = :RaidTeamName"); // Get all toons			
		} 
		
		if(strtotime('Tuesday this week 11:00') > time()) {
			$lastReset = strtotime('Tuesday last week 11:00'); // Set the reset time for realms (11:00AM EDT, Tuesdays)
			$twoResetsAgo = strtotime('Tuesday last week 11:00 - 1 week');
		} else {
			$lastReset = strtotime('Tuesday this week 11:00'); // Set the reset time for realms (11:00AM EDT, Tuesdays)
			$twoResetsAgo = strtotime('Tuesday this week 11:00 - 1 week');
		}
		
		
		$firstDayofThisMonth = strtotime('first day of this month');
		$firstDayofLastMonth = strtotime('first day of last month');
		
		
		$gearSlot = $db->column("SELECT `GearSlotName` FROM `gearSlots`");
		
		$allToons = array(); // Declare array for use later
		$totalIlvl = 0;
		$totalArtifactPower = 0;
		$totalMythics = 0;
		$totalArtifactLevel = 0;
		$totalWorldQuests = 0;

		for ($i = 0; $i < sizeOf($toons); $i ++) { // Loop through all toons
			// Reset values
			$allToons[$i]['ToonName'] = $toons[$i]['ToonName'];
			$allToons[$i]['WorldQuests'] = 0;
			$allToons[$i]['Mythics']  = 0;
			$allToons[$i]['MythicPlus']  = 0;
			$allToons[$i]['ArtifactPower']  = 0;
			$allToons[$i]['ArtifactLevel'] = 0;
			$allToons[$i]['AverageIlvl'] = 0;
		
			for ($x = 0; $x < sizeOf($gearSlot); $x++) {
				$currentSlot = $gearSlot[$x];
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"ItemSlot"=>$currentSlot));
				$currentGear = $db->row('
					SELECT `ItemLevel`
					FROM `toonitems` 
					WHERE `ToonId` = :ToonId  AND `ItemSlot` = :ItemSlot');	
				if ($currentSlot === 'mainHand' && $currentGear)
					$mainHandIlvl = $currentGear['ItemLevel'];
				if ($currentSlot === 'offHand' && !$currentGear) 
					$allToons[$i]['AverageIlvl'] += $mainHandIlvl;
				else
					$allToons[$i]['AverageIlvl'] += $currentGear['ItemLevel'];
			}
			$allToons[$i]['AverageIlvl'] /= 16;
			$totalIlvl += $allToons[$i]['AverageIlvl'];
			$allToons[$i]['AverageIlvl'] = round($allToons[$i]['AverageIlvl'], 2);
			
			if ($timeFrame === 'ThisWeek') {
				$db->bind("ToonId",$toons[$i]['ToonId']);
				$allToons[$i]['ArtifactLevel'] = $db->single('
				SELECT `ArtifactLevel` 
				FROM `progression` 
				WHERE ToonId = :ToonId 
				ORDER BY ArtifactLevel DESC limit 1');
				
				$totalArtifactLevel += $allToons[$i]['ArtifactLevel'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$lastReset));
				$allToons[$i]['ArtifactPower'] = $db->single('
				SELECT 
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalArtifactPower += $allToons[$i]['ArtifactPower'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$lastReset));
				$allToons[$i]['WorldQuests'] = $db->single('
				SELECT 
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalWorldQuests += $allToons[$i]['WorldQuests'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$lastReset));
				$allToons[$i]['Mythics'] = $db->single('
				SELECT 
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalMythics += $allToons[$i]['Mythics'];
				
				$db->bindMore(array(
				"ToonId"=>$toons[$i]['ToonId'],
				"ToonId2"=>$toons[$i]['ToonId'],
				"ToonId3"=>$toons[$i]['ToonId'],
				"ToonId4"=>$toons[$i]['ToonId'],
				"ToonId5"=>$toons[$i]['ToonId'],
				"ToonId6"=>$toons[$i]['ToonId'],
				"ToonId7"=>$toons[$i]['ToonId'],
				"ToonId8"=>$toons[$i]['ToonId'],
				"Time"=>$lastReset,
				"Time2"=>$lastReset,
				"Time3"=>$lastReset,
				"Time4"=>$lastReset,
				"Time5"=>$lastReset,
				"Time6"=>$lastReset,
				"Time7"=>$lastReset,
				"Time8"=>$lastReset));
				$allToons[$i]['MythicPlus'] = $db->single('
				SELECT 	
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME(:Time) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId2 and InsertDate > FROM_UNIXTIME(:Time2) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId3 and InsertDate > FROM_UNIXTIME(:Time3) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId4 and InsertDate > FROM_UNIXTIME(:Time4) ORDER BY InsertDate DESC limit 1))
				-
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId5 and InsertDate < FROM_UNIXTIME(:Time5) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId6 and InsertDate < FROM_UNIXTIME(:Time6) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId7 and InsertDate < FROM_UNIXTIME(:Time7) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId8 and InsertDate < FROM_UNIXTIME(:Time8) ORDER BY InsertDate DESC limit 1)) 
				AS Difference');
				$header = 'Summary for Week Starting '.date('l, F j, Y @ g:i A', $lastReset);
			} else if ($timeFrame === 'LastWeek') {
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset));
				$allToons[$i]['ArtifactLevel'] = $db->single('
				SELECT `ArtifactLevel` 
				FROM `progression` 
				WHERE ToonId = :ToonId AND InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY ArtifactLevel DESC limit 1');
				
				$totalArtifactLevel += $allToons[$i]['ArtifactLevel'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$twoResetsAgo));
				$allToons[$i]['ArtifactPower'] = $db->single('
				SELECT 
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalArtifactPower += $allToons[$i]['ArtifactPower'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$twoResetsAgo));
				$allToons[$i]['WorldQuests'] = $db->single('
				SELECT 
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalWorldQuests += $allToons[$i]['WorldQuests'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$lastReset,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$twoResetsAgo));
				$allToons[$i]['Mythics'] = $db->single('
				SELECT 
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalMythics += $allToons[$i]['Mythics'];
				
				$db->bindMore(array(
				"ToonId"=>$toons[$i]['ToonId'],
				"ToonId2"=>$toons[$i]['ToonId'],
				"ToonId3"=>$toons[$i]['ToonId'],
				"ToonId4"=>$toons[$i]['ToonId'],
				"ToonId5"=>$toons[$i]['ToonId'],
				"ToonId6"=>$toons[$i]['ToonId'],
				"ToonId7"=>$toons[$i]['ToonId'],
				"ToonId8"=>$toons[$i]['ToonId'],
				"Time"=>$lastReset,
				"Time2"=>$lastReset,
				"Time3"=>$lastReset,
				"Time4"=>$lastReset,
				"Time5"=>$twoResetsAgo,
				"Time6"=>$twoResetsAgo,
				"Time7"=>$twoResetsAgo,
				"Time8"=>$twoResetsAgo));
				$allToons[$i]['MythicPlus'] = $db->single('
				SELECT 	
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME(:Time) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME(:Time2) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId3 and InsertDate < FROM_UNIXTIME(:Time3) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId4 and InsertDate < FROM_UNIXTIME(:Time4) ORDER BY InsertDate DESC limit 1))
				-
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId5 and InsertDate < FROM_UNIXTIME(:Time5) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId6 and InsertDate < FROM_UNIXTIME(:Time6) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId7 and InsertDate < FROM_UNIXTIME(:Time7) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId8 and InsertDate < FROM_UNIXTIME(:Time8) ORDER BY InsertDate DESC limit 1)) 
				AS Difference');
				$header = 'Summary for Last Week ('.date('l, F j, Y', $twoResetsAgo).' - '.date('l, F j, Y', $lastReset).')';
			} else if ($timeFrame === 'ThisMonth') {
				$db->bind("ToonId",$toons[$i]['ToonId']);
				$allToons[$i]['ArtifactLevel'] = $db->single('
				SELECT `ArtifactLevel` 
				FROM `progression` 
				WHERE ToonId = :ToonId 
				ORDER BY ArtifactLevel DESC limit 1');
				
				$totalArtifactLevel += $allToons[$i]['ArtifactLevel'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$firstDayofThisMonth));
				$allToons[$i]['ArtifactPower'] = $db->single('
				SELECT 
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalArtifactPower += $allToons[$i]['ArtifactPower'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$firstDayofThisMonth));
				$allToons[$i]['WorldQuests'] = $db->single('
				SELECT 
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalWorldQuests += $allToons[$i]['WorldQuests'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$firstDayofThisMonth));
				$allToons[$i]['Mythics'] = $db->single('
				SELECT 
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalMythics += $allToons[$i]['Mythics'];
				
				$db->bindMore(array(
				"ToonId"=>$toons[$i]['ToonId'],
				"ToonId2"=>$toons[$i]['ToonId'],
				"ToonId3"=>$toons[$i]['ToonId'],
				"ToonId4"=>$toons[$i]['ToonId'],
				"ToonId5"=>$toons[$i]['ToonId'],
				"ToonId6"=>$toons[$i]['ToonId'],
				"ToonId7"=>$toons[$i]['ToonId'],
				"ToonId8"=>$toons[$i]['ToonId'],
				"Time"=>$firstDayofThisMonth,
				"Time2"=>$firstDayofThisMonth,
				"Time3"=>$firstDayofThisMonth,
				"Time4"=>$firstDayofThisMonth,
				"Time5"=>$firstDayofThisMonth,
				"Time6"=>$firstDayofThisMonth,
				"Time7"=>$firstDayofThisMonth,
				"Time8"=>$firstDayofThisMonth));
				$allToons[$i]['MythicPlus'] = $db->single('
				SELECT 	
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId and InsertDate > FROM_UNIXTIME(:Time) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId2 and InsertDate > FROM_UNIXTIME(:Time2) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId3 and InsertDate > FROM_UNIXTIME(:Time3) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId4 and InsertDate > FROM_UNIXTIME(:Time4) ORDER BY InsertDate DESC limit 1))
				-
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId5 and InsertDate < FROM_UNIXTIME(:Time5) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId6 and InsertDate < FROM_UNIXTIME(:Time6) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId7 and InsertDate < FROM_UNIXTIME(:Time7) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId8 and InsertDate < FROM_UNIXTIME(:Time8) ORDER BY InsertDate DESC limit 1)) 
				AS Difference');
				$header = 'Summary for This Month ('.date('F Y', $firstDayofThisMonth).')';
			} else if ($timeFrame === 'LastMonth') {
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth));
				$allToons[$i]['ArtifactLevel'] = $db->single('
				SELECT `ArtifactLevel` 
				FROM `progression` 
				WHERE ToonId = :ToonId AND InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY ArtifactLevel DESC limit 1');
				
				$totalArtifactLevel += $allToons[$i]['ArtifactLevel'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$firstDayofLastMonth));
				$allToons[$i]['ArtifactPower'] = $db->single('
				SELECT 
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalArtifactPower += $allToons[$i]['ArtifactPower'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$firstDayofLastMonth));
				$allToons[$i]['WorldQuests'] = $db->single('
				SELECT 
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalWorldQuests += $allToons[$i]['WorldQuests'];
				
				$db->bindMore(array("ToonId"=>$toons[$i]['ToonId'],"Time"=>$firstDayofThisMonth,"ToonId2"=>$toons[$i]['ToonId'],"Time2"=>$firstDayofLastMonth));
				$allToons[$i]['Mythics'] = $db->single('
				SELECT 
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME( :Time )
				ORDER BY InsertDate DESC limit 1) -
				(SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME( :Time2 )
				ORDER BY InsertDate DESC limit 1) AS Difference');
				
				$totalMythics += $allToons[$i]['Mythics'];
				
				$db->bindMore(array(
				"ToonId"=>$toons[$i]['ToonId'],
				"ToonId2"=>$toons[$i]['ToonId'],
				"ToonId3"=>$toons[$i]['ToonId'],
				"ToonId4"=>$toons[$i]['ToonId'],
				"ToonId5"=>$toons[$i]['ToonId'],
				"ToonId6"=>$toons[$i]['ToonId'],
				"ToonId7"=>$toons[$i]['ToonId'],
				"ToonId8"=>$toons[$i]['ToonId'],
				"Time"=>$firstDayofThisMonth,
				"Time2"=>$firstDayofThisMonth,
				"Time3"=>$firstDayofThisMonth,
				"Time4"=>$firstDayofThisMonth,
				"Time5"=>$firstDayofLastMonth,
				"Time6"=>$firstDayofLastMonth,
				"Time7"=>$firstDayofLastMonth,
				"Time8"=>$firstDayofLastMonth));
				$allToons[$i]['MythicPlus'] = $db->single('
				SELECT 	
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId and InsertDate < FROM_UNIXTIME(:Time) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId2 and InsertDate < FROM_UNIXTIME(:Time2) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId3 and InsertDate < FROM_UNIXTIME(:Time3) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId4 and InsertDate < FROM_UNIXTIME(:Time4) ORDER BY InsertDate DESC limit 1))
				-
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId5 and InsertDate < FROM_UNIXTIME(:Time5) ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId6 and InsertDate < FROM_UNIXTIME(:Time6) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId7 and InsertDate < FROM_UNIXTIME(:Time7) ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId8 and InsertDate < FROM_UNIXTIME(:Time8) ORDER BY InsertDate DESC limit 1)) 
				AS Difference');
				$header = 'Summary for Last Month ('.date('F Y', $firstDayofLastMonth).')';
			} else if ($timeFrame === 'AllTime') {
				$db->bind("ToonId",$toons[$i]['ToonId']);
				$allToons[$i]['ArtifactLevel'] = $db->single('
				SELECT `ArtifactLevel` 
				FROM `progression` 
				WHERE ToonId = :ToonId
				ORDER BY ArtifactLevel DESC limit 1');
				
				$totalArtifactLevel += $allToons[$i]['ArtifactLevel'];
				
				$db->bind("ToonId",$toons[$i]['ToonId']);
				$allToons[$i]['ArtifactPower'] = $db->single('
				SELECT `ArtifactPower`
				FROM `progression`
				WHERE ToonId = :ToonId
				ORDER BY InsertDate DESC limit 1');
				
				$totalArtifactPower += $allToons[$i]['ArtifactPower'];
				
				$db->bind("ToonId",$toons[$i]['ToonId']);
				$allToons[$i]['WorldQuests'] = $db->single('
				SELECT `WorldQuests`
				FROM `progression`
				WHERE ToonId = :ToonId
				ORDER BY InsertDate DESC limit 1');
				
				$totalWorldQuests += $allToons[$i]['WorldQuests'];
				
				$db->bind("ToonId",$toons[$i]['ToonId']);
				$allToons[$i]['Mythics'] = $db->single('
				SELECT `Mythics`
				FROM `progression`
				WHERE ToonId = :ToonId
				ORDER BY InsertDate DESC limit 1');
				
				$totalMythics += $allToons[$i]['Mythics'];
				
				$db->bindMore(array(
				"ToonId"=>$toons[$i]['ToonId'],
				"ToonId2"=>$toons[$i]['ToonId'],
				"ToonId3"=>$toons[$i]['ToonId'],
				"ToonId4"=>$toons[$i]['ToonId']));
				$allToons[$i]['MythicPlus'] = $db->single('
                                SELECT
				((SELECT `Mythic+2` FROM `progression` WHERE ToonId = :ToonId ORDER BY InsertDate DESC limit 1) + 
				(SELECT `Mythic+5` FROM `progression` WHERE ToonId = :ToonId2 ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+10` FROM `progression` WHERE ToonId = :ToonId3 ORDER BY InsertDate DESC limit 1) +
				(SELECT `Mythic+15` FROM `progression` WHERE ToonId = :ToonId4 ORDER BY InsertDate DESC limit 1))');
				$header = 'Summary of All Time Guild Progress';
			} 
		}
		$totalArtifactLevel = $totalArtifactLevel/sizeOf($allToons);
		$totalArtifactLevel = round($totalArtifactLevel, 2);
		$totalIlvl = $totalIlvl/sizeOf($allToons);
		$totalIlvl = round($totalIlvl, 2); ?>
	
		<div class="container" id="page-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <h3 class="page-header text-center text-white"><?php echo ($header); ?></h3>
                        </div> <!-- /.col-lg-12 -->
                    </div> <!-- /.row -->
			
                    <div class="card-group">
                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Average Item Level</h6>
                            <div class="card-block">
                                <div class="card-text">
                                    <div class="table text-center mb-0">
                                        <?php echo ($totalIlvl); ?>
                                    </div> <!-- /.table-responsive -->
                                </div> <!-- /.panel-body -->
                            </div>
                        </div> <!-- /.col-lg-2 -->	

                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Average Artifact Traits</h6>
                            <div class="card-block">
                                <div class="card-text">
                                    <div class="table text-center mb-0">
                                        <?php echo (number_format($totalArtifactLevel)); ?>
                                    </div> <!-- /.table-responsive -->
                                </div> <!-- /.panel-body -->
                            </div>
                        </div> <!-- /.col-lg-2 -->	

                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Artifact Power Obtained</h6>
                            <div class="card-block">
                                <div class="card-text">
                                    <div class="table text-center mb-0">
                                        <?php echo (number_format($totalArtifactPower)); ?>
                                    </div> <!-- /.table-responsive -->
                                </div> <!-- /.panel-body -->
                            </div>
                        </div> <!-- /.col-lg-2 -->

                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">World Quests Completed</h6>
                            <div class="card-block">
                                <div class="card-text">
                                    <div class="table text-center mb-0">
                                        <?php echo (number_format($totalWorldQuests)); ?>
                                    </div> <!-- /.table-responsive -->
                                </div> <!-- /.panel-body -->
                            </div>
                        </div> <!-- /.col-lg-2 -->

                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Mythics Completed</h6>
                            <div class="card-block">
                                <div class="card-text">
                                    <div class="table text-center mb-0">
                                        <?php echo (number_format($totalMythics)); ?>
                                    </div> <!-- /.table-responsive -->
                                </div> <!-- /.panel-body -->
                            </div>
                        </div> <!-- /.col-lg-2 -->	

                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Amount of Members</h6>
                            <div class="card-block">
                                <div class="card-text">
                                    <div class="table text-center mb-0">
                                        <?php echo (sizeOf($allToons)); ?>
                                    </div> <!-- /.table-responsive -->
                                </div> <!-- /.panel-body -->
                            </div>
                        </div> <!-- /.col-lg-2 -->
                    </div>
				
                        <div class="card">
                            <h4 class="card-header-sm text-center bg-darkgreen text-white">Raid Team Summary</h4>
                            <div class="card-text">   
                                <table id="raidSummary" class="table table-sm table-hover mb-0 dt-responsive" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="bg-faded text-center">Toon Name</th>
                                            <th class="bg-faded text-center">Ilvl</th>
                                            <th class="bg-faded text-center">Artifact Level</th>
                                            <th class="bg-faded text-center">Artifact Power</th>
                                            <th class="bg-faded text-center">World Quests</th>
                                            <th class="bg-faded text-center">Mythics</th>
                                            <th class="bg-faded text-center">Mythic+</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        for ($i = 0; $i < sizeOf($allToons); $i++) { ?>
                                            <tr>
                                                <td><?php echo $allToons[$i]['ToonName']; ?></td>
                                                <td class="text-center"><?php echo number_format($allToons[$i]['AverageIlvl']); ?></td>
                                                <td class="text-center"><?php echo number_format($allToons[$i]['ArtifactLevel']); ?></td>
                                                <td class="text-center"><?php echo number_format($allToons[$i]['ArtifactPower']); ?></td>
                                                <td class="text-center"><?php echo number_format($allToons[$i]['WorldQuests']); ?></td>
                                                <td class="text-center"><?php echo number_format($allToons[$i]['Mythics']); ?></td>
                                                <td class="text-center"><?php echo number_format($allToons[$i]['MythicPlus']); ?></td>
                                            </tr>
                                        <?php 
                                        } ?>
                                    </tbody>
                                </table>
                            </div> <!-- /.panel -->
                        </div> <!-- /.col-lg-2 -->

		</div> <!-- /#page-wrapper -->
	</div> <!-- /#wrapper -->

	<!-- jQuery -->
    <script src="js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
	
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap4.min.js"></script>
    <script src="js/dataTables.responsive.min.js"></script>
    <script src="js/responsive.bootstrap4.min.js"></script>
    


    <script> 
    $(document).ready(function() {
        $('#raidSummary').DataTable( {
            lengthMenu: [ [ 25, 50, -1 ], [ 25, 50, 'All' ] ],
            responsive: true,
            scrollX: true,
            order: [[ 3, "desc" ]]
        } );
    } );
    </script>


</body>
</html>