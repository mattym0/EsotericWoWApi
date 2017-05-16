<?php
include('session.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" href="wow.png">
	<title>Add Toon to DB</title>

	<!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">   
	<link href="css/custom.css" rel="stylesheet">  
	
	<!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>

<?php 

require_once 'database/Db.class.php';

$db = new Db();

$error = '';
$newLoad = false;
$toonName = '';
$serverName = '';
$roleName = '';
$serverRegion = '';
$raidTeamName = '';
$toonOwner = '';

$url = "$_SERVER[REQUEST_URI]";
$url = parse_url($url);
if (isSet($url['query'])) {
	$url = explode('&',$url['query']);	
	for ($x = 0; $x < sizeOf($url); $x++) {
		$parsedURL = explode('=',$url[$x]);
		if ($parsedURL[0] === 'toonName'){
			$toonName = rawurldecode($parsedURL[1]);
                } else if ($parsedURL[0] === 'serverName') {
			$serverName = rawurldecode($parsedURL[1]);
                } else if ($parsedURL[0] === 'roleName') {
			$roleName = rawurldecode($parsedURL[1]);
                } else if ($parsedURL[0] === 'serverRegion') {
			$serverRegion = rawurldecode($parsedURL[1]);
                } else if ($parsedURL[0] === 'raidTeamName') {
			$raidTeamName = rawurldecode($parsedURL[1]);
                } else if ($parsedURL[0] === 'toonOwnerName') {
			$toonOwner = rawurldecode($parsedURL[1]);
                }
	}
	if (empty($toonName) || empty($serverName))
		$error = 'Need to supply Toon Name & Server Name';
} else
	$newLoad = true;


if (!$error && !$newLoad) {
	$db->bind("ServerName",$serverName);
	$db->bind("ServerRegion",$serverRegion);
	$serverId = $db->single("SELECT ServerId from servers WHERE ServerName = :ServerName AND ServerRegion = :ServerRegion");

	if (empty($serverId)) {
		$db->bind("ServerName",$serverName);
		$db->bind("ServerRegion",$serverRegion);
		$rowsInserted = $db->query("INSERT INTO servers (`ServerName`,`ServerRegion`) VALUES(:ServerName,:ServerRegion)");
		
		if ($rowsInserted === 1) {
			$db->bind("ServerName",$serverName);
			$db->bind("ServerRegion",$serverRegion);
			$serverId = $db->single("SELECT ServerId from servers WHERE ServerName = :ServerName AND ServerRegion = :ServerRegion");
		} else
			$error = 'Error in retrieiving Server: '.$serverName.'-'.$serverRegion.' from or adding Server: '.$serverName.'-'.$serverRegion.' to Database';
	}

	$db->bind("RoleName",$roleName);
	$roleId = $db->single("SELECT RoleId from roles WHERE RoleName = :RoleName");

	if (empty($roleId)) 
		$error = 'Error in retrieiving Role: '.$roleName.' from Database';

	if (empty($raidTeamName)) {
		$raidTeamId = 0;
	} else {
		$db->bind("RaidTeamName",$raidTeamName);
		$raidTeamId = $db->single("SELECT RaidTeamId from raidteams WHERE RaidTeamName = :RaidTeamName");

		if (empty($raidTeamId)) {
			$db->bind("RaidTeamName",$raidTeamName);
			$rowsInserted = $db->query("INSERT INTO raidteams (RaidTeamName) VALUE(:RaidTeamName)");
			if ($rowsInserted === 1) {
				$db->bind("RaidTeamName",$raidTeamName);
				$raidTeamId = $db->single("SELECT RaidTeamId from raidteams WHERE RaidTeamName = :RaidTeamName");
			} else
				$error = 'Error in retrieiving Raid Team: '.$raidTeamName.' from or adding Raid Team: '.$raidTeamName.' to Database';
		}
	}
	if (empty($toonOwner)) {
		$toonOwnerId = 0;
	} else {
		$db->bind("ToonOwner",$toonOwner);
		$toonOwnerId = $db->single("SELECT ToonOwnerId from toonowners WHERE ToonOwner = :ToonOwner");

		if (empty($toonOwnerId)) {
			$db->bind("ToonOwner",$toonOwner);
			$rowsInserted = $db->query("INSERT INTO toonowners (ToonOwner) VALUE(:ToonOwner)");
			
			if ($rowsInserted === 1) {
				$db->bind("ToonOwner",$toonOwner);
				$toonOwnerId = $db->single("SELECT ToonOwnerId from toonowners WHERE ToonOwner = :ToonOwner");
			} else
				$error = 'Error in retrieiving Toon Owner: '.$toonOwner.' from or adding Toon Owner: '.$toonOwner.' to Database';
		}
	}
}
?>

<body>
	<div id="wrapper">
		<?php include('navbar.php'); ?>
		
		<div class="container" id="page-wrapper">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header text-center text-white">Insert Toon into Database</h3>
				</div> <!-- /.col-lg-12 -->
			
				<div class="container col-6">
					<form class="form" role="form">
						<div class="row col-xl-12">
							<div class="form-group col-xl-6">
                                                            <label for="toonName" class="text-white"><strong>Toon Name:</strong></label>
								<input type="text" class="form-control col-xl-12" id="toonName" name="toonName" 
									placeholder="Enter Toon Name" <?php if ($toonName) echo ('value="'.$toonName.'"'); ?> >  
							</div>
							<div class="form-group col-xl-6">
                                                            <label for="serverName" class="text-white"><strong>Server Name:</strong></label>
								<input type="text" class="form-control col-xl-12" id="serverName" name="serverName" 
									placeholder="Enter Server Name" <?php if ($serverName) echo ('value="'.$serverName.'"'); ?> >							
							</div>
						</div>
						<div class="row col-xl-12">
							<div class="form-group col-xl-6">
                                                            <label for="roleName" class="text-white"><strong>Role:</strong></label>
								<select class="form-control col-xl-12" id="roleName" name="roleName">
									<option>Tank</option>
									<option <?php if ($roleName === 'Healer') echo('selected');?> >Healer</option>
									<option <?php if ($roleName === 'Melee') echo('selected');?> >Melee</option>
									<option <?php if ($roleName === 'Ranged') echo('selected');?> >Ranged</option>
								</select>
							</div>
							<div class="form-group col-xl-6">
                                                            <label for="serverRegion" class="text-white"><strong>Server Region:</strong></label>
								<select class="form-control col-xl-12" id="serverRegion" name="serverRegion">
									<option>US</option>
									<option <?php if ($serverRegion === 'EU') echo('selected');?> >EU</option>
								</select>
							</div>
						</div>
						<div class="row col-xl-12">
							<div class="form-group col-xl-6">
                                                            <label for="raidTeamName" class="text-white"><strong>Raid Team Name:</strong></label>
								<input type="text" class="form-control col-xl-12" id="raidTeamName" name="raidTeamName" 
									placeholder="Enter Raid Team Name" <?php if ($raidTeamName) echo ('value="'.$raidTeamName.'"');?> >
							</div>
							<div class="form-group col-xl-6">
                                                            <label for="toonOwnerName" class="text-white"><strong>Toon Owner Name:</strong></label>
								<input type="text" class="form-control col-xl-12" id="toonOwnerName" name="toonOwnerName" 
									placeholder="Enter Toon Owner Name" <?php if ($toonOwner) echo ('value="'.$toonOwner.'"'); ?> >
							</div>
						</div>
						<div  id="submit" style="margin-top:20px" class="form-group col-xl-12">
							<input type="submit" name="submit" id="btnSubmit" class="btn btn-inverse btn-success" value="Add/Update Toon" />
						</div>
					</form>
				</div>
			</div>
			<div class="row">
				<div class="container col-4">
					<div class="col-lg-12">
						<?php
						if ($error) { ?>
							<h4 style="margin-left:20px" class="page-footer"><i class="fa fa-times" aria-hidden="true"></i><?php echo ($error);?></h4>
						<?php	
						} else if ($newLoad) { ?>
							<h4 style="margin-left:20px" class="page-footer"></h4>
						<?php 
						} else {
							$db->bindMore(array("RoleId"=>$roleId,"RaidTeamId"=>$raidTeamId,"ToonOwnerId"=>$toonOwnerId,"ToonName"=>$toonName,"ServerId"=>$serverId));
							$stmt = $db->query("UPDATE toons SET 
							`RoleId` = :RoleId,
							`RaidTeamId` = :RaidTeamId,
							`ToonOwnerId` = :ToonOwnerId WHERE
							`ToonName` = :ToonName AND
							`ServerId` = :ServerId");
							if ($stmt === 0) {
								$db->bindMore(array("RoleId"=>$roleId,"RaidTeamId"=>$raidTeamId,"ToonOwnerId"=>$toonOwnerId,"ToonName"=>$toonName,"ServerId"=>$serverId));
								$stmt = $db->query("INSERT INTO toons
									(`ToonName`, 
									`ServerId`,
									`RoleId`,
									`RaidTeamId`,
									`ToonOwnerId`) VALUES
									(:ToonName,
									:ServerId,
									:RoleId,
									:RaidTeamId,
									:ToonOwnerId)");
								if ($stmt === 1) {?>
									<h4 style="margin-left:20px" class="page-footer"><i class="fa fa-check" aria-hidden="true"></i>Toon Added!</h4>
								<?php 
								} else { ?>
									<h4 style="margin-left:20px" class="page-footer"><i class="fa fa-times" aria-hidden="true"></i>Toon Not Added. Should check logic</h4>
								<?php 
								} 
							} else if ($stmt === 1) { ?>
								<h4 style="margin-left:20px" class="page-footer"><i class="fa fa-check" aria-hidden="true"></i>Toon Updated!</h4>
							<?php 
							} else { ?>
								<h4 style="margin-left:20px" class="page-footer"><i class="fa fa-times" aria-hidden="true"></i>Toon Not Added/Updated. Should check logic</h4>
						<?php
							}						
						} ?>
					</div>
				</div>
			</div> <!-- /.row -->
		</div>
	</div>
	
	<!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="js/bootstrap.min.js"></script>

</body>
</html>