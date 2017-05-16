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
	<title>Update Toon from API</title>

	<!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">   
	<link href="css/custom.css" rel="stylesheet">    
	
	<!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>

<?php 
require_once 'database/Db.class.php';

$db = new Db();

$toons = $db->query("SELECT * FROM toons");
?>

<body>
	<div id="wrapper">
		<?php include('navbar.php'); ?>
		
		<div class="container" id="page-wrapper">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header text-center text-white">Update Toon Info from API's</h3>
				</div> <!-- /.col-lg-12 -->
				<div class="container col-5">
					<form class="form" role="form" action="updateToons.php" method="POST" role="form">
						<div class="form-group">
							<label for="toonName" class="col-xl-6 text-white"><strong>Toon Name:</strong></label>
							<select class="form-control col-xl-12" id="toonName" name="toonName">
								<option>All</option>
								<?php 
								for ($x = 0; $x < sizeOf($toons); $x++) { ?>
									<option> <?php echo ($toons[$x]['ToonName']); ?> </option>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<label for="updateWhat" class="col-xl-8 text-white"><strong>What to Update:</strong></label>
							<select class="form-control col-xl-12" id="updateWhat" name="updateWhat">
								<option>All</option>
								<option>Gear</option>
								<option>Progress</option>
								<option>Talents</option>
								<option>Stats</option>
								<option>Class</option>
								<option>Misc Info</option>
							</select>
						</div>
						<div class="form-group row">
							<div class="mx-sm-5">
								<input type="submit" name="submit" id="btnSubmit" class="btn btn-inverse btn-success" value="Update Toon(s)" />
							</div>
							<div class="mx-sm-5">
								<a href="updateGuild.php" class="btn btn-inverse btn-success" role="button" aria-disabled="true">Update Guild</a>
							</div>
						</div>
					</form>
				</div>
			</div> <!-- /.row -->
			<div class="row">
				<div class="col-lg-12">
					
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