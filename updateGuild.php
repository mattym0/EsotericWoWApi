<?php ini_set('max_execution_time', 300); 
?>

<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Update Guild DB Info</title>

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
	
	$guildApiReturn = $wow->getGuildAllFields('Esoteric', 'Area-52');
								
	for ($x = 0; $x < (sizeOf($guildApiReturn['members'])); $x++) {
		$toonRank = $guildApiReturn['members'][$x]['rank'];
		$toonName = $guildApiReturn['members'][$x]['character']['name'];
		$toonClass = $guildApiReturn['members'][$x]['character']['class'];
		$toonRace = $guildApiReturn['members'][$x]['character']['race'];
		$toonGender = $guildApiReturn['members'][$x]['character']['gender'];
		$toonLevel = $guildApiReturn['members'][$x]['character']['level'];
		if (isSet($guildApiReturn['members'][$x]['character']['spec']))
			$toonSpec = $guildApiReturn['members'][$x]['character']['spec']['name'];
		else
			$toonSpec = 'Unknown';
		$db->Bind("ToonName",$toonName);
		$toonId = $db->single("SELECT ToonId FROM toons WHERE `ToonName` = :ToonName");
		if (empty($toonId))
			$toonId = 0;
								
		$rowsAffected = $db->query("UPDATE guildtoons SET 
		`ToonClass` = :ToonClass,`ToonRace` = :ToonRace,`ToonGender` = :ToonGender,`ToonLevel` = :ToonLevel,`ToonSpec` = :ToonSpec,`ToonRank` = :ToonRank, `ToonId` = :ToonId WHERE		
		`ToonName` = :ToonName", array(
		"ToonName"=>$toonName,
		"ToonClass"=>$toonClass,
		"ToonRace"=>$toonRace,
		"ToonGender"=>$toonGender,
		"ToonLevel"=>$toonLevel,
		"ToonSpec"=>$toonSpec,
		"ToonRank"=>$toonRank,
		"ToonId"=>$toonId));
		if ($rowsAffected === 0) {
			$rowsAffected = $db->query("INSERT INTO guildtoons
			(`ToonName`,`ToonClass`,`ToonRace`,`ToonGender`,`ToonLevel`,`ToonSpec`,`ToonRank`,`ToonId`) VALUES
			(:ToonName,:ToonClass,:ToonRace,:ToonGender,:ToonLevel,:ToonSpec,:ToonRank,:ToonId)", array(
			"ToonName"=>$toonName,
			"ToonClass"=>$toonClass,
			"ToonRace"=>$toonRace,
			"ToonGender"=>$toonGender,
			"ToonLevel"=>$toonLevel,
			"ToonSpec"=>$toonSpec,
			"ToonRank"=>$toonRank,
			"ToonId"=>$toonId));
		} 
	}

	?>
	
	<div id="wrapper">
		<?php include('navbar.php'); ?>
		
		<div class="container-fluid" id="page-wrapper">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="text-center page-header">The Following Were Processed:</h3>
					<h5 class="text-center">Guild Processed</h5>
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
