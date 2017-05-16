<?php ini_set('max_execution_time', 300); 
?>

<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Update Toons DB Info</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">   
	<link href="css/custom.css" rel="stylesheet">   
	
	<!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>
<body>
	<?php
	require_once 'wow/region.php';
	require_once 'wow/wow.php';
	require_once 'classes/toon.php';
	require_once 'classes/gear.php';
	require_once 'database/Db.class.php';
	
	function unique_multidim_array($array, $key) { 
		$temp_array = array(); 
		$i = 0; 
		$key_array = array(); 
		
		foreach($array as $val) { 
			if (!in_array($val[$key], $key_array)) { 
				$key_array[$i] = $val[$key]; 
				$temp_array[$i] = $val; 
			} 
			$i++; 
		} 
		return $temp_array; 
	} 
	
	$toonName = '';
	if (isSet($_POST['updateWhat']))
		$updateWhat=$_POST['updateWhat'];
	if (isSet($_POST['toonName']))
		$toonName=$_POST['toonName'];
	
	$updateEverything = false;;
	
	if (empty($updateWhat)) {
		$updateWhat = false;
		$updateEverything = true;
	}
	if ($updateWhat === 'All')
		$updateEverything = true;
	if ($toonName === 'All')
		$toonName = ''; //Sets values to null as we just want to pull all of it anyway
		
	$db = new Db();

	$region = new jpWoWRegion('us', 'en_US');
	$wow = new jpWoW($region);
	$wow->setApiKey('yw3fgnneneg7jcffp5zaekyewdzqw48m');
	
	$tertiaryStats = array('40','41','42','43');
	?>
	
	<div id="wrapper">
		<?php include('navbar.php'); ?>
		
		<div class="container-fluid" id="page-wrapper">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="text-center page-header">The Following Were Processed:</h3>
					<?php
					if ($toonName) {
						$db->bind("ToonName",$toonName);
						$toons = $db->query("SELECT * FROM toons WHERE `ToonName` = :ToonName ");
					} else
						$toons = $db->query("SELECT * FROM toons");
											
					for ($i = 0; $i < sizeOf($toons); $i++) {
						echo ('<h5 class="text-center">'.$toons[$i]['ToonName'].' (Toon ID '.$toons[$i]['ToonId'].'): ');
						$toon = new Toon();
						$server = $db->single("SELECT ServerName FROM servers WHERE ServerId = :id ", array('id' => $toons[$i]['ServerId']));			
						
						$toonApiReturn = $wow->getCharacterSpecificFields($toons[$i]['ToonName'], $server, array ('achievements','audit','items','talents','stats','mounts','pets','reputation'));
						
						$toon->name = $toons[$i]['ToonName'];
						$toon->nameId = $toons[$i]['ToonId'];
						$toon->level = $toonApiReturn['level'];
						
						for ($x = 0; $x < sizeOf($toonApiReturn['talents']); $x++) {
							if (isSet($toonApiReturn['talents'][$x]['selected']))
								$toon->spec = $toonApiReturn['talents'][$x]['spec']['name'];
						}	
					
						
						if ($updateWhat === 'Talents' OR $updateEverything) {
							for ($x = 0; $x < sizeOf($toonApiReturn['talents']); $x++) {
								if (isSet($toonApiReturn['talents'][$x]['selected'])) {
									$toon->spec = $toonApiReturn['talents'][$x]['spec']['name'];
									for ($y = 0; $y < sizeOf($toonApiReturn['talents'][$x]['talents']); $y++) {
										$currentTier = $toonApiReturn['talents'][$x]['talents'][$y]['tier'];
										$toon->talents[$currentTier]['name'] = $toonApiReturn['talents'][$x]['talents'][$y]['spell']['name'];
										$toon->talents[$currentTier]['id'] = $toonApiReturn['talents'][$x]['talents'][$y]['spell']['id'];
										$toon->talents[$currentTier]['column'] = $toonApiReturn['talents'][$x]['talents'][$y]['column'];
										$toon->talents[$currentTier]['icon'] =  $toonApiReturn['talents'][$x]['talents'][$y]['spell']['icon'];
									}
									$rowsAffected = $db->query("UPDATE talents SET 
									`Tier1` = :Tier1,`Tier2` = :Tier2,`Tier3` = :Tier3,`Tier4` = :Tier4,`Tier5` = :Tier5,`Tier6` = :Tier6,`Tier7` = :Tier7 WHERE		
									`ToonId` = :ToonId AND `ToonSpec` = :ToonSpec", array(
									"ToonId"=>$toon->nameId,
									"ToonSpec"=>$toon->spec,
									"Tier1"=>json_encode($toon->talents[0]),
									"Tier2"=>json_encode($toon->talents[1]),
									"Tier3"=>json_encode($toon->talents[2]),
									"Tier4"=>json_encode($toon->talents[3]),
									"Tier5"=>json_encode($toon->talents[4]),
									"Tier6"=>json_encode($toon->talents[5]),
									"Tier7"=>json_encode($toon->talents[6])));
									if ($rowsAffected === 0) {
										$rowsAffected = $db->query("INSERT INTO talents
										(`ToonId`,`ToonSpec`,`Tier1`,`Tier2`,`Tier3`,`Tier4`,`Tier5`,`Tier6`,`Tier7`) VALUES
										(:ToonId,:ToonSpec,:Tier1,:Tier2,:Tier3,:Tier4,:Tier5,:Tier6,:Tier7)", array(
										"ToonId"=>$toon->nameId,
										"ToonSpec"=>$toon->spec,
										"Tier1"=>json_encode($toon->talents[0]),
										"Tier2"=>json_encode($toon->talents[1]),
										"Tier3"=>json_encode($toon->talents[2]),
										"Tier4"=>json_encode($toon->talents[3]),
										"Tier5"=>json_encode($toon->talents[4]),
										"Tier6"=>json_encode($toon->talents[5]),
										"Tier7"=>json_encode($toon->talents[6])));
									}
								}
							}
							echo (' Talents <i class="fa fa-check" aria-hidden="true"></i>');
						}
						if ($updateWhat === 'Misc Info' OR $updateEverything) {
							$toon->achievementPoints = $toonApiReturn['achievementPoints'];
							$toon->mounts = $toonApiReturn['mounts']['numCollected'];
							$toon->uniquePets = sizeOf(unique_multidim_array($toonApiReturn['pets']['collected'],'creatureId'));
							for ($x = 0; $x < sizeOf($toonApiReturn['pets']['collected']); $x++) {
								if ($toonApiReturn['pets']['collected'][$x]['stats']['level'] === 25)
									$toon->maxLevelPets ++;
							}
							// 2045 Armies of Legionfall 1948 Valarjar 1900 Court of Farondis 1894 The Wardens 1883 Dreamweavers 1859 Nightfallen 1828 Highmountain Tribe
							for ($x = 0; $x < sizeOf($toonApiReturn['reputation']); $x++) {
								if ($toonApiReturn['reputation'][$x]['standing'] === 7)
									$toon->exaltedReps ++;
								
								switch ($toonApiReturn['reputation'][$x]['id']) {
									case(2045): // Armies of Legionfall
										$AoL = json_encode($toonApiReturn['reputation'][$x]);
										break;
										
									case(1948): // Valarjar
										$Valarjar = json_encode($toonApiReturn['reputation'][$x]);
										break;
										
									case(1900): // Court of Farondis
										$CoF = json_encode($toonApiReturn['reputation'][$x]);
										break;
										
									case(1894): // The Wardens
										$Wardens = json_encode($toonApiReturn['reputation'][$x]);
										break;
										
									case(1883): // Dreamweavers
										$Dreamweavers = json_encode($toonApiReturn['reputation'][$x]);
										break;
										
									case(1859): // The Nightfallen
										$Nightfallen = json_encode($toonApiReturn['reputation'][$x]);
										break;
										
									case(1828): // Highmountain Tribe
										$HT = json_encode($toonApiReturn['reputation'][$x]);
										break;
									
									default:
										break;
								}
							}
							$rowsAffected = $db->query("UPDATE reputation SET 
							`AoL` = :AoL,`Valarjar` = :Valarjar,`CoF` = :CoF,`Wardens` = :Wardens,`Dreamweavers` = :Dreamweavers,`Nightfallen` = :Nightfallen,`HT` = :HT WHERE		
							`ToonId` = :ToonId", array(
							"ToonId"=>$toon->nameId,
							"AoL"=>$AoL,
							"Valarjar"=>$Valarjar,
							"CoF"=>$CoF,
							"Wardens"=>$Wardens,
							"Dreamweavers"=>$Dreamweavers,
							"Nightfallen"=>$Nightfallen,
							"HT"=>$HT));
							if ($rowsAffected === 0) {
								$rowsAffected = $db->query("INSERT INTO reputation
								(`ToonId`,`AoL`,`Valarjar`,`CoF`,`Wardens`,`Dreamweavers`,`Nightfallen`,`HT`) VALUES
								(:ToonId,:AoL,:Valarjar,:CoF,:Wardens,:Dreamweavers,:Nightfallen,:HT)", array(
								"ToonId"=>$toon->nameId,
								"AoL"=>$AoL,
								"Valarjar"=>$Valarjar,
								"CoF"=>$CoF,
								"Wardens"=>$Wardens,
								"Dreamweavers"=>$Dreamweavers,
								"Nightfallen"=>$Nightfallen,
								"HT"=>$HT));
							}
							
							$rowsAffected = $db->query("UPDATE toonmisc SET 
                                                        `ToonLevel` = :ToonLevel,`AchievementPoints` = :AchievementPoints,`Mounts` = :Mounts,`UniquePets` = :UniquePets,`MaxLevelPets` = :MaxLevelPets,`ExaltedReps` = :ExaltedReps WHERE		
							`ToonId` = :ToonId", array(
							"ToonId"=>$toon->nameId,
                                                        "ToonLevel"=>$toon->level,
							"AchievementPoints"=>$toon->achievementPoints,
							"Mounts"=>$toon->mounts,
							"UniquePets"=>$toon->uniquePets,
							"MaxLevelPets"=>$toon->maxLevelPets,
							"ExaltedReps"=>$toon->exaltedReps));
							if ($rowsAffected === 0) {
								$rowsAffected = $db->query("INSERT INTO toonmisc
								(`ToonId`,`ToonLevel`,`AchievementPoints`,`Mounts`,`UniquePets`,`MaxLevelPets`,`ExaltedReps`) VALUES
								(:ToonId,:AchievementPoints,:Mounts,:UniquePets,:MaxLevelPets,:ExaltedReps)", array(
								"ToonId"=>$toon->nameId,
                                                                "ToonLevel"=>$toon->level,
								"AchievementPoints"=>$toon->achievementPoints,
								"Mounts"=>$toon->mounts,
								"UniquePets"=>$toon->uniquePets,
								"MaxLevelPets"=>$toon->maxLevelPets,
								"ExaltedReps"=>$toon->exaltedReps));
							}
							echo (' Misc Info <i class="fa fa-check" aria-hidden="true"></i>');
						}
						if ($updateWhat === 'Stats' OR $updateEverything) {
							for ($x = 0; $x < sizeOf($toonApiReturn['talents']); $x++) {
								if (isSet($toonApiReturn['talents'][$x]['selected'])) 
									$toon->spec = $toonApiReturn['talents'][$x]['spec']['name'];		
							}		
							$toon->health = $toonApiReturn['stats']['health'];
							
							$toon->strength = $toonApiReturn['stats']['str'];
							$toon->agility = $toonApiReturn['stats']['agi'];
							$toon->intellect = $toonApiReturn['stats']['int'];
							$toon->stamina = $toonApiReturn['stats']['sta'];
							
							$toon->criticalStrike = $toonApiReturn['stats']['crit'];
							$toon->critRating = $toonApiReturn['stats']['critRating'];
							$toon->haste = $toonApiReturn['stats']['haste'];
							$toon->hasteRating = $toonApiReturn['stats']['hasteRating'];
							$toon->mastery = $toonApiReturn['stats']['mastery'];
							$toon->masteryRating = $toonApiReturn['stats']['masteryRating'];
							$toon->versatility = $toonApiReturn['stats']['versatility'];

							$rowsAffected = $db->query("UPDATE toonstats SET 
							`Health` = :Health,`Strength` = :Strength,`Agility` = :Agility,`Intellect` = :Intellect,`Stamina` = :Stamina,`CriticalStrike` = :CriticalStrike,`CritRating` = :CritRating,
							`Haste` = :Haste,`HasteRating` = :HasteRating,`Mastery` = :Mastery,`MasteryRating` = :MasteryRating,`Versatility` = :Versatility WHERE		
							`ToonId` = :ToonId AND `ToonSpec` = :ToonSpec", array(
							"ToonId"=>$toon->nameId,
							"ToonSpec"=>$toon->spec,
							"Health"=>$toon->health,
							"Strength"=>$toon->strength,
							"Agility"=>$toon->agility,
							"Intellect"=>$toon->intellect,
							"Stamina"=>$toon->stamina,
							"CriticalStrike"=>$toon->criticalStrike,
							"CritRating"=>$toon->critRating,
							"Haste"=>$toon->haste,
							"HasteRating"=>$toon->hasteRating,
							"Mastery"=>$toon->mastery,
							"MasteryRating"=>$toon->masteryRating,
							"Versatility"=>$toon->versatility
							));

							if ($rowsAffected === 0) {
								$rowsAffected = $db->query("INSERT INTO toonstats
								(`ToonId`,`ToonSpec`,`Health`,`Strength`,`Agility`,`Intellect`,`Stamina`,`CriticalStrike`,`CritRating`,`Haste`,`HasteRating`,`Mastery`,`MasteryRating`,`Versatility`) VALUES
								(:ToonId,:ToonSpec,:Health,:Strength,:Agility,:Intellect,:Stamina,:CriticalStrike,:CritRating,:Haste,:HasteRating,:Mastery,:MasteryRating,:Versatility)", array(
								"ToonId"=>$toon->nameId,
								"ToonSpec"=>$toon->spec,
								"Health"=>$toon->health,
								"Strength"=>$toon->strength,
								"Agility"=>$toon->agility,
								"Intellect"=>$toon->intellect,
								"Stamina"=>$toon->stamina,
								"CriticalStrike"=>$toon->criticalStrike,
								"CritRating"=>$toon->critRating,
								"Haste"=>$toon->haste,
								"HasteRating"=>$toon->hasteRating,
								"Mastery"=>$toon->mastery,
								"MasteryRating"=>$toon->masteryRating,
								"Versatility"=>$toon->versatility
							));
							}
							
							echo (' Stats <i class="fa fa-check" aria-hidden="true"></i>');
						}
						if ($updateWhat === 'Progress' OR $updateEverything) {
							$toon->level = $toonApiReturn['level'];
							$gearSlot = array('mainHand','offHand');
							
							for ($x = 0; $x < sizeOf($gearSlot); $x++) {
								$currentSlot = $gearSlot[$x];
								if (isset($toonApiReturn['items'][$currentSlot]['artifactTraits']['0'])) {
									for ($y=0; $y < sizeOf($toonApiReturn['items'][$currentSlot]['artifactTraits']); $y++) {
										$toon->artifactLevel += $toonApiReturn['items'][$currentSlot]['artifactTraits'][$y]['rank'];
									}
									for ($y=0; $y < sizeOf($toonApiReturn['items'][$currentSlot]['relics']); $y++) {
										if (isset($toonApiReturn['items'][$currentSlot]['relics'][$y]['itemId']))
											$toon->artifactLevel -= 1;
									}
								}
							}
							if ($toon->level === 110) {
								for ($x=0; $x < sizeOf($toonApiReturn['achievements']['criteria']); $x++) {
									switch($toonApiReturn['achievements']['criteria'][$x]) {
										case(30103):
											$toon->artifactPower = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
										
										case(31466):
											$toon->artifactKnowledge = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
										
										case(33096):
											$toon->mythic2s = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
										
										case(33097):
											$toon->mythic5s = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
										
										case(33098):
											$toon->mythic10s = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
										
										case(32028):
											$toon->mythic15s = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31382): // Mythic EoA 31382
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$EoA = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31384): // Mythic DT 31384
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$DT = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31407): // Mythic NL 31407
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$NL = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31388): // Mythic HoV 31388
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$HoV = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31412): // Mythic AoVH 31412
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$AoVH = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31415): // Mythic VotW 31415
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$VotW = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31416): // Mythic BRH 31416
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$BRH = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31418): // Mythic MoS 31418
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$MoS = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31420): // Mythic Arc 31420
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$Arcway = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(31841): // Mythic CoS 31841
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$CoS = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(34850): // Mythic RtK 34850
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$RtK = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(36216): // Mythic CoEN 36216
											$toon->mythics += $toonApiReturn['achievements']['criteriaQuantity'][$x];
											$CoEN = $toonApiReturn['achievements']['criteriaQuantity'][$x];
											break;
											
										case(33094): // World Quests completed
											$toon->worldQuests = $toonApiReturn['achievements']['criteriaQuantity'][$x];
										
										default:
											break;
									}
								}

								$toon->mythic2s -= $toon->mythic5s - $toon->mythic10s - $toon->mythic15s;
								$toon->mythic5s -= $toon->mythic10s - $toon->mythic15s;
								$toon->mythic10s -= $toon->mythic15s;
								
								$stmt = $db->query("UPDATE dungeonprogress SET 
								`EoA` = :EoA,`DT`= :DT,`NL` = :NL,`HoV` = :HoV,`AoVH` = :AoVH,`VotW` = :VotW,`BRH`= :BRH,`MoS` = :MoS,`Arcway` = :Arcway,
								`CoS` = :CoS,`RtK` = :RtK,`CoEN` = :CoEN WHERE
								`ToonId` = :ToonId", array(
								"ToonId"=>$toon->nameId,
								"EoA"=>$EoA,
								"DT"=>$DT,
								"NL"=>$NL,
								"HoV"=>$HoV,
								"AoVH"=>$AoVH,
								"VotW"=>$VotW,
								"BRH"=>$BRH,
								"MoS"=>$MoS,
								"Arcway"=>$Arcway,
								"CoS"=>$CoS,
								"RtK"=>$RtK,
								"CoEN"=>$CoEN));
								if ($stmt === 0) {
									$stmt = $db->query("INSERT INTO dungeonprogress
									(`ToonId`,`EoA`,`DT`,`NL`,`HoV`,`AoVH`,`VotW`,`BRH`,`MoS`,`Arcway`,`CoS`,`RtK`,`CoEN`) VALUES
									(:ToonId,:EoA,:DT,:NL,:HoV,:AoVH,:VotW,:BRH,:MoS,:Arcway,:CoS,:RtK,:CoEN)",array(
									"ToonId"=>$toon->nameId,
									"EoA"=>$EoA,
									"DT"=>$DT,
									"NL"=>$NL,
									"HoV"=>$HoV,
									"AoVH"=>$AoVH,
									"VotW"=>$VotW,
									"BRH"=>$BRH,
									"MoS"=>$MoS,
									"Arcway"=>$Arcway,
									"CoS"=>$CoS,
									"RtK"=>$RtK,
									"CoEN"=>$CoEN));
								}
											
								$stmt = $db->query("INSERT INTO progression
									(`ToonId`,`ToonSpec`,`ArtifactPower`,`ArtifactLevel`,`ArtifactKnowledge`,`Mythics`,`Mythic+2`,`Mythic+5`,`Mythic+10`,`Mythic+15`,`WorldQuests`) VALUES 
									(:ToonId,:ToonSpec,:ArtifactPower,:ArtifactLevel,:ArtifactKnowledge,:Mythics,:Mythic2s,:Mythic5s,:Mythic10s,:Mythic15s,:WorldQuests)", array(
									"ToonId"=>$toon->nameId,
									"ToonSpec"=>$toon->spec,
									"ArtifactPower"=>$toon->artifactPower,
									"ArtifactLevel"=>$toon->artifactLevel,
									"ArtifactKnowledge"=>$toon->artifactKnowledge,
									"Mythics"=>$toon->mythics,
									"Mythic2s"=>$toon->mythic2s,
									"Mythic5s"=>$toon->mythic5s,
									"Mythic10s"=>$toon->mythic10s,
									"Mythic15s"=>$toon->mythic15s,
									"WorldQuests"=>$toon->worldQuests));

									echo (' Progress <i class="fa fa-check" aria-hidden="true"></i>');
							} else {
								echo (' Progress <i class="fa fa-times" aria-hidden="true"></i> (Level='.$toon->level.')');
							}
						}	
						if ($updateWhat === 'Gear' OR $updateEverything) {
							$gearSlot = $db->column("SELECT `GearSlotName` FROM `gearSlots`");
							for ($x = 0; $x < sizeOf($gearSlot); $x++) {
								$currentSlot = $gearSlot[$x];
								if (isset($toonApiReturn['items'][$currentSlot]['id'])) {
									$toon->$currentSlot->id = $toonApiReturn['items'][$currentSlot]['id'];
									$toon->$currentSlot->name = $toonApiReturn['items'][$currentSlot]['name'];
									$toon->$currentSlot->name = $toon->$currentSlot->name;
									$toon->$currentSlot->quality = $toonApiReturn['items'][$currentSlot]['quality'];
									$toon->$currentSlot->itemLevel = $toonApiReturn['items'][$currentSlot]['itemLevel'];
									$toon->$currentSlot->bonusLists = $toonApiReturn['items'][$currentSlot]['bonusLists'];
									if (in_array('1808', $toon->$currentSlot->bonusLists)) {
										if (isset($toonApiReturn['items'][$currentSlot]['tooltipParams']['gem0'])) 
											$toon->$currentSlot->gems = $toonApiReturn['items'][$currentSlot]['tooltipParams']['gem0'];
										else
											$toon->$currentSlot->gems = '-1';
									}
									// Extra check of rings and shoulders due to jewelcrafting items coming with a socket but not having the bonus list for a socket
									if ($currentSlot === 'neck' && in_array(1, $toonApiReturn['audit']['itemsWithEmptySockets']))
										$toon->$currentSlot->gems = '-1';
									else if ($currentSlot === 'finger1' && in_array(10, $toonApiReturn['audit']['itemsWithEmptySockets']))
										$toon->$currentSlot->gems = '-1';
									else if ($currentSlot === 'finger2' && in_array(11, $toonApiReturn['audit']['itemsWithEmptySockets']))
										$toon->$currentSlot->gems = '-1';
									$toon->$currentSlot->primaryStats = json_encode($toonApiReturn['items'][$currentSlot]['stats']);
									if (isset($toonApiReturn['items'][$currentSlot]['tooltipParams']['set']))
										$toon->$currentSlot->set = 'yes';
									if (isset($toonApiReturn['items'][$currentSlot]['tooltipParams']['enchant']))
										$toon->$currentSlot->enchant = $toonApiReturn['items'][$currentSlot]['tooltipParams']['enchant'];
									$toon->$currentSlot->slot = $currentSlot;
									for ($y = 0; $y < sizeOf($tertiaryStats); $y++) {
										if (in_array($tertiaryStats[$y], $toon->$currentSlot->bonusLists))
											$toon->$currentSlot->tertiaryStats = $tertiaryStats[$y];
									}
									
									if (isset($toonApiReturn['items'][$currentSlot]['artifactTraits']['0'])) {
										$gems = array ("gem0"=>0,"gem1"=>0,"gem2"=>0);
										if (isSet($toonApiReturn['items'][$currentSlot]['tooltipParams']['gem0']))
											$gems['gem0'] = $toonApiReturn['items'][$currentSlot]['tooltipParams']['gem0'];
										if (isSet($toonApiReturn['items'][$currentSlot]['tooltipParams']['gem1']))
											$gems['gem1'] = $toonApiReturn['items'][$currentSlot]['tooltipParams']['gem1'];
										if (isSet($toonApiReturn['items'][$currentSlot]['tooltipParams']['gem2']))
											$gems['gem2'] = $toonApiReturn['items'][$currentSlot]['tooltipParams']['gem2'];
										$toon->$currentSlot->gems = json_encode($gems);
										for ($y=0; $y < sizeOf($toonApiReturn['items'][$currentSlot]['artifactTraits']); $y++) {
											$toon->artifactLevel += $toonApiReturn['items'][$currentSlot]['artifactTraits'][$y]['rank'];
										}
										for ($y=0; $y < sizeOf($toonApiReturn['items'][$currentSlot]['relics']); $y++) {
											if (isset($toonApiReturn['items'][$currentSlot]['relics'][$y]['itemId']))
												$toon->artifactLevel -= 1;
										}
									}
												
									$db->bindMore(array("ItemName"=>$toon->$currentSlot->name,"ItemId"=>$toon->$currentSlot->id));
									$stmt = $db->query("UPDATE items SET 
									`ItemName` = :ItemName WHERE
									`ItemId` = :ItemId");
									if ($stmt === 0) {
										$db->bindMore(array("ItemName"=>$toon->$currentSlot->name,"ItemId"=>$toon->$currentSlot->id));
										$stmt = $db->query("INSERT INTO items
										(`ItemId`,`ItemName`) VALUES
										(:ItemId,:ItemName)");
									}
									$stmt = $db->query("UPDATE toonitems SET 
									`ItemId` = :ItemId,`ItemLevel` = :ItemLevel,`ItemQuality` = :ItemQuality,`PrimaryStats` = :PrimaryStats,`Gems` = :Gems,`Enchant` = :Enchant,`TertiaryStats` = :TertiaryStats WHERE
									`ToonId` = :ToonId AND `ToonSpec` = :ToonSpec AND `ItemSlot` = :ItemSlot", array(
									"ItemId"=>$toon->$currentSlot->id,
									"ItemLevel"=>$toon->$currentSlot->itemLevel,
									"ItemQuality"=>$toon->$currentSlot->quality,
									"PrimaryStats"=>$toon->$currentSlot->primaryStats,
									"Gems"=>$toon->$currentSlot->gems,
									"Enchant"=>$toon->$currentSlot->enchant,
									"TertiaryStats"=>$toon->$currentSlot->tertiaryStats,
									"ToonId"=>$toon->nameId,
									"ToonSpec"=>$toon->spec,
									"ItemSlot"=>$toon->$currentSlot->slot));
									if ($stmt === 0)
										$stmt = $db->query("INSERT INTO toonitems
										(`ToonId`,`ToonSpec`,`ItemId`,`ItemSlot`,`ItemLevel`,`ItemQuality`,`PrimaryStats`,`Gems`,`Enchant`,`TertiaryStats`) VALUES
										(:ToonId,:ToonSpec,:ItemId,:ItemSlot,:ItemLevel,:ItemQuality,:PrimaryStats,:Gems,:Enchant,:TertiaryStats)",array(
										"ItemId"=>$toon->$currentSlot->id,
										"ItemLevel"=>$toon->$currentSlot->itemLevel,
										"ItemQuality"=>$toon->$currentSlot->quality,
										"PrimaryStats"=>$toon->$currentSlot->primaryStats,
										"Gems"=>$toon->$currentSlot->gems,
										"Enchant"=>$toon->$currentSlot->enchant,
										"TertiaryStats"=>$toon->$currentSlot->tertiaryStats,
										"ToonId"=>$toon->nameId,
										"ToonSpec"=>$toon->spec,
										"ItemSlot"=>$toon->$currentSlot->slot));
								}
							}
							echo (' Gear <i class="fa fa-check" aria-hidden="true"></i>');
						}
						if ($updateWhat === 'Class' OR $updateEverything) {
							
							$toon->classId = $toonApiReturn['class'];
							
							$db->bind("ClassId",$toon->classId);
												
							$db->bindMore(array("ToonId"=>$toon->nameId,"ClassId"=>$toon->classId));
							$stmt = $db->query("UPDATE toonclass SET 
							`ClassId` = :ClassId WHERE
							`ToonId` = :ToonId");
							if ($stmt === 0) {
								$db->bindMore(array("ToonId"=>$toon->nameId,"ClassId"=>$toon->classId));
								$stmt = $db->query("INSERT INTO toonclass
								(`ToonId`,`ClassId`) VALUES
								(:ToonId,:ClassId)");
							}
							echo (' Class Info <i class="fa fa-check" aria-hidden="true"></i>');
						}
						echo ('</h5>');
					} ?>
				</div> <!-- /.col-lg-12 -->
			</div> <!-- /.row -->
		</div>
	</div>
	<!-- jQuery -->
    <script src="js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
